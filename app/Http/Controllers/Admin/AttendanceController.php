<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SalaryPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Access denied. Only administrators can manage attendance & payroll.');
    }

    /**
     * Daily attendance sheet view.
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $dateStr = $request->get('date', now()->toDateString());
        try {
            $date = Carbon::parse($dateStr);
        } catch (\Exception $e) {
            $date = Carbon::today();
        }

        $employees = User::where('role', 'cashier')
            ->orderBy('is_active', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        $existingAttendances = Attendance::whereDate('date', $date->toDateString())
            ->get()
            ->keyBy('user_id');

        $stats = [
            'total_employees' => $employees->where('is_active', true)->count(),
            'present'         => $existingAttendances->where('status', 'present')->count(),
            'absent'          => $existingAttendances->where('status', 'absent')->count(),
            'half_day'        => $existingAttendances->where('status', 'half_day')->count(),
            'leave'           => $existingAttendances->where('status', 'leave')->count(),
            'holiday'         => $existingAttendances->where('status', 'holiday')->count(),
            'unmarked'        => max(0, $employees->where('is_active', true)->count() - $existingAttendances->count()),
        ];

        return view('admin.attendance.index', compact('employees', 'date', 'existingAttendances', 'stats'));
    }

    /**
     * Store / update daily attendance.
     */
    public function mark(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'date'                    => ['required', 'date'],
            'attendances'             => ['required', 'array'],
            'attendances.*.status'    => ['required', 'in:present,absent,half_day,leave,holiday'],
            'attendances.*.check_in'  => ['nullable', 'string'],
            'attendances.*.check_out' => ['nullable', 'string'],
            'attendances.*.notes'     => ['nullable', 'string', 'max:255'],
        ]);

        $date = Carbon::parse($data['date'])->toDateString();

        foreach ($data['attendances'] as $userId => $item) {
            Attendance::updateOrCreate(
                [
                    'user_id' => $userId,
                    'date'    => $date,
                ],
                [
                    'status'    => $item['status'],
                    'check_in'  => !empty($item['check_in']) ? $item['check_in'] : null,
                    'check_out' => !empty($item['check_out']) ? $item['check_out'] : null,
                    'notes'     => $item['notes'] ?? null,
                    'marked_by' => auth()->id(),
                ]
            );
        }

        return back()->with('success', "Attendance for " . Carbon::parse($date)->format('d M Y') . " saved successfully.");
    }

    /**
     * Monthly payroll summary and auto-calculation view.
     */
    public function payroll(Request $request)
    {
        $this->authorizeAdmin();

        $monthYearStr = $request->get('month', now()->format('Y-m'));
        try {
            $parsedDate = Carbon::createFromFormat('Y-m', $monthYearStr);
        } catch (\Exception $e) {
            $parsedDate = Carbon::today();
            $monthYearStr = $parsedDate->format('Y-m');
        }

        $year = (int) $parsedDate->year;
        $month = (int) $parsedDate->month;

        $employees = User::where('role', 'cashier')
            ->orderBy('is_active', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        $payrolls = $employees->map(function ($emp) use ($year, $month) {
            return $emp->calculateMonthlyPayroll($year, $month);
        });

        $totals = [
            'total_base_salary' => (float) $payrolls->sum('base_salary'),
            'total_deductions'  => (float) $payrolls->sum('deduction_amount'),
            'total_net_payable' => (float) $payrolls->sum('net_payable'),
            'total_paid'        => (float) $payrolls->where('is_paid', true)->sum('net_payable'),
            'total_pending'     => (float) $payrolls->where('is_paid', false)->sum('net_payable'),
            'paid_count'        => $payrolls->where('is_paid', true)->count(),
            'pending_count'     => $payrolls->where('is_paid', false)->count(),
        ];

        return view('admin.attendance.payroll', compact('payrolls', 'totals', 'monthYearStr', 'parsedDate'));
    }

    /**
     * Record salary payout for an employee.
     */
    public function disburseSalary(Request $request, User $user)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'month_year'       => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'base_salary'      => ['required', 'numeric', 'min:0'],
            'deduction_amount' => ['required', 'numeric', 'min:0'],
            'bonus_amount'     => ['nullable', 'numeric', 'min:0'],
            'payment_date'     => ['required', 'date'],
            'payment_method'   => ['required', 'in:cash,bank,cheque,other'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        [$year, $month] = explode('-', $data['month_year']);
        $calc = $user->calculateMonthlyPayroll((int)$year, (int)$month);

        $baseSalary = (float) $data['base_salary'];
        $deductions = (float) $data['deduction_amount'];
        $bonus      = (float) ($data['bonus_amount'] ?? 0.0);
        $netPayable = max(0.0, round($baseSalary - $deductions + $bonus, 2));

        $payment = SalaryPayment::updateOrCreate(
            [
                'user_id'    => $user->id,
                'month_year' => $data['month_year'],
            ],
            [
                'base_salary'      => $baseSalary,
                'total_days'       => $calc['total_days'],
                'present_days'     => $calc['effective_present'],
                'absent_days'      => $calc['unpaid_absences'],
                'allowed_leaves'   => $calc['allowed_leaves'],
                'excess_absences'  => $calc['excess_absences'],
                'deduction_amount' => $deductions,
                'bonus_amount'     => $bonus,
                'net_payable'      => $netPayable,
                'payment_date'     => $data['payment_date'],
                'payment_method'   => $data['payment_method'],
                'status'           => 'paid',
                'notes'            => $data['notes'] ?? null,
                'paid_by'          => auth()->id(),
            ]
        );

        return back()->with('success', "Salary payment of PKR " . number_format($netPayable, 2) . " recorded for {$user->name} ({$data['month_year']}).");
    }

    /**
     * Printable salary disbursement voucher/slip.
     */
    public function salarySlip(SalaryPayment $payment)
    {
        $this->authorizeAdmin();

        $payment->load(['user', 'paidBy']);
        $employee = $payment->user;
        [$year, $month] = explode('-', $payment->month_year);
        $payroll = $employee->calculateMonthlyPayroll((int)$year, (int)$month);
        $monthName = Carbon::createFromDate((int)$year, (int)$month, 1)->format('F Y');

        return view('admin.attendance.slip', compact('payment', 'employee', 'payroll', 'monthName'));
    }
}

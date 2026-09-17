<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'salary',
        'allowed_leaves',
        'phone',
        'designation',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'salary'            => 'decimal:2',
            'allowed_leaves'    => 'integer',
        ];
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a cashier (employee).
     */
    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    /**
     * Calculate monthly attendance summary and net salary for a given month/year.
     */
    public function calculateMonthlyPayroll(int $year, int $month): array
    {
        $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfDay();
        $totalDaysInMonth = $startDate->daysInMonth;
        $monthYearStr = $startDate->format('Y-m');

        // Fetch attendances for this month
        $attendances = $this->attendances()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $presentCount  = $attendances->where('status', 'present')->count();
        $halfDayCount  = $attendances->where('status', 'half_day')->count();
        $leaveCount    = $attendances->where('status', 'leave')->count();
        $holidayCount  = $attendances->where('status', 'holiday')->count();
        $absentCount   = $attendances->where('status', 'absent')->count();

        // Effective present days
        $effectivePresent = $presentCount + ($halfDayCount * 0.5) + $holidayCount;
        
        // Unpaid absences (unexcused absences + 0.5 for each half day)
        $unpaidAbsences = (float) $absentCount + ($halfDayCount * 0.5);

        // Allowed leaves threshold per month
        $allowedLeaves = $this->allowed_leaves ?? 4;
        
        // Excess absences beyond allowed threshold
        $excessAbsences = max(0.0, $unpaidAbsences - $allowedLeaves);

        // Base salary & daily rate
        $baseSalary = (float) ($this->salary ?? 0.0);
        $dailyRate  = $totalDaysInMonth > 0 ? round($baseSalary / $totalDaysInMonth, 2) : 0.0;

        // Salary deduction
        $deductionAmount = round($excessAbsences * $dailyRate, 2);
        $netPayable = max(0.0, round($baseSalary - $deductionAmount, 2));

        // Check if salary already paid for this month
        $payment = $this->salaryPayments()
            ->where('month_year', $monthYearStr)
            ->first();

        return [
            'user'             => $this,
            'month_year'       => $monthYearStr,
            'total_days'       => $totalDaysInMonth,
            'present_count'    => $presentCount,
            'half_day_count'   => $halfDayCount,
            'leave_count'      => $leaveCount,
            'holiday_count'    => $holidayCount,
            'absent_count'     => $absentCount,
            'effective_present'=> $effectivePresent,
            'unpaid_absences'  => $unpaidAbsences,
            'allowed_leaves'   => $allowedLeaves,
            'excess_absences'  => $excessAbsences,
            'base_salary'      => $baseSalary,
            'daily_rate'       => $dailyRate,
            'deduction_amount' => $deductionAmount,
            'net_payable'      => $netPayable,
            'is_paid'          => $payment !== null && $payment->status === 'paid',
            'payment'          => $payment,
        ];
    }
}

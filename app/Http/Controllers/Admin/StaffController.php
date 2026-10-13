<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAdjustment;
use App\Models\EmployeePayment;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Check that only admins can access staff management.
     */
    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Access denied. Only administrators can manage staff.');
    }

    /**
     * Display the staff management page (Employees & Cashiers).
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $search = trim($request->get('search', ''));

        $employeesQuery = Employee::with([
            'sales' => fn($q) => $q->where('payment_method', 'credit')->where('status', '!=', 'voided'),
            'payments',
            'adjustments' => fn($q) => $q->with('user:id,name')->latest(),
        ]);
        $cashiersQuery  = User::where('role', 'cashier');
        $adminsQuery    = User::where('role', 'admin');

        if (!empty($search)) {
            $employeesQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });

            $cashiersQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });

            $adminsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $employees = $employeesQuery->orderBy('id', 'asc')->get();
        $cashiers  = $cashiersQuery->latest()->get();
        $admins    = $adminsQuery->latest()->get();

        // Calculate overall store stats
        $allEmployees = Employee::with([
            'sales' => fn($q) => $q->where('payment_method', 'credit')->where('status', '!=', 'voided'),
            'payments',
            'adjustments',
        ])->get();
        $totalCredit  = $allEmployees->sum(fn($e) => $e->total_credit);
        $totalPaid    = $allEmployees->sum(fn($e) => $e->total_paid);
        $totalPending = $allEmployees->sum(fn($e) => $e->pending_payment);

        $stats = [
            'total_employees' => $allEmployees->count(),
            'active_employees'=> $allEmployees->where('is_active', true)->count(),
            'total_credit'    => $totalCredit,
            'total_paid'      => $totalPaid,
            'total_pending'   => $totalPending,
            'total_cashiers'  => User::where('role', 'cashier')->count(),
            'total_admins'    => User::where('role', 'admin')->count(),
        ];

        return view('admin.staff.index', compact('employees', 'cashiers', 'admins', 'stats', 'search'));
    }

    /**
     * Store a new employee.
     */
    public function storeEmployee(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:150'],
            'phone'           => ['required', 'string', 'max:30'],
            'address'         => ['required', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $employee = Employee::create([
            'name'            => $data['name'],
            'phone'           => $data['phone'],
            'address'         => $data['address'],
            'opening_balance' => $data['opening_balance'] ?? 0.00,
            'is_active'       => true,
            'notes'           => $data['notes'] ?? null,
        ]);

        return back()->with('success', "Customer {$employee->name} added successfully.");
    }

    /**
     * Update employee details.
     */
    public function updateEmployee(Request $request, Employee $employee)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:150'],
            'phone'           => ['required', 'string', 'max:30'],
            'address'         => ['required', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'is_active'       => ['required', 'boolean'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $employee->update($data);

        return back()->with('success', "Customer {$employee->name} updated successfully.");
    }

    /**
     * Delete or deactivate an employee.
     */
    public function destroyEmployee(Employee $employee)
    {
        $this->authorizeAdmin();

        if ($employee->sales()->exists() || $employee->payments()->exists()) {
            $employee->update(['is_active' => false]);
            return back()->with('success', "Customer {$employee->name} has transaction history and has been deactivated instead of deleted.");
        }

        $name = $employee->name;
        $employee->delete();

        return back()->with('success', "Customer {$name} deleted successfully.");
    }

    /**
     * Record a payment by an employee to clear their pending credit due.
     */
    public function recordPayment(Request $request, Employee $employee)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,bank,salary_deduction'],
            'payment_date'   => ['required', 'date'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        EmployeePayment::create([
            'employee_id'    => $employee->id,
            'amount'         => $data['amount'],
            'payment_method' => $data['payment_method'],
            'payment_date'   => $data['payment_date'],
            'user_id'        => auth()->id(),
            'notes'          => $data['notes'] ?? null,
        ]);

        $employee->reconcileCreditSales();

        return back()->with('success', "Payment of PKR " . number_format($data['amount'], 2) . " recorded for {$employee->name}. Pending dues updated.");
    }

    /**
     * Update an existing customer payment record.
     */
    public function updatePayment(Request $request, EmployeePayment $payment)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,bank,salary_deduction'],
            'payment_date'   => ['required', 'date'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $payment->update([
            'amount'         => $data['amount'],
            'payment_method' => $data['payment_method'],
            'payment_date'   => $data['payment_date'],
            'notes'          => $data['notes'] ?? null,
        ]);

        $payment->employee?->reconcileCreditSales();

        return back()->with('success', "Payment record updated successfully.");
    }

    /**
     * Delete / undo a customer payment.
     */
    public function destroyPayment(EmployeePayment $payment)
    {
        $this->authorizeAdmin();

        $employee = $payment->employee;
        $amount = number_format($payment->amount, 2);
        $empName = $employee?->name ?? 'Customer';

        $payment->delete();

        if ($employee) {
            $employee->refresh();
            $employee->reconcileCreditSales();
        }

        return back()->with('success', "Payment of PKR {$amount} for {$empName} has been deleted and due restored.");
    }

    /**
     * Adjust/correct a customer's credit due balance directly (e.g. for mistakes/waivers)
     * with an option not to affect store sales and ledgers.
     */
    public function adjustDue(Request $request, Employee $employee)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'new_due'      => ['required', 'numeric', 'min:0'],
            'reason'       => ['required', 'string', 'max:500'],
            'affect_sales' => ['nullable', 'boolean'],
        ]);

        $previousDue = (float) $employee->pending_payment;
        $newDue      = (float) $data['new_due'];
        $delta       = round($newDue - $previousDue, 2);

        if (abs($delta) < 0.001) {
            return back()->withErrors(['error' => "New due (PKR " . number_format($newDue, 2) . ") is identical to current due. No adjustment needed."]);
        }

        $adjustment = EmployeeAdjustment::create([
            'employee_id'  => $employee->id,
            'user_id'      => auth()->id(),
            'previous_due' => $previousDue,
            'new_due'      => $newDue,
            'amount'       => $delta,
            'reason'       => $data['reason'],
            'affect_sales' => $request->boolean('affect_sales', false),
            'is_active'    => true,
        ]);

        $employee->refresh();
        $employee->reconcileCreditSales();

        $actionWord = $delta < 0 ? 'reduced' : 'increased';
        $deltaFormatted = number_format(abs($delta), 2);
        $storeImpactMsg = $adjustment->affect_sales ? 'Store sales updated.' : 'Store sales and ledgers were not affected.';

        return back()->with('success', "Due balance for {$employee->name} {$actionWord} by PKR {$deltaFormatted} (New Due: PKR " . number_format($newDue, 2) . "). {$storeImpactMsg}");
    }

    /**
     * Undo or Redo a previously recorded customer due adjustment.
     */
    public function toggleUndoAdjustment(EmployeeAdjustment $adjustment)
    {
        $this->authorizeAdmin();

        $employee = $adjustment->employee;
        $currentlyActive = $adjustment->is_active;

        $adjustment->update([
            'is_active' => !$currentlyActive,
        ]);

        if ($employee) {
            $employee->refresh();
            $employee->reconcileCreditSales();
        }

        $amountFormatted = number_format(abs($adjustment->amount), 2);
        $empName = $employee?->name ?? 'Customer';

        if ($currentlyActive) {
            return back()->with('success', "Adjustment of PKR {$amountFormatted} for {$empName} has been UNDONE. Balance restored to PKR " . number_format($employee->pending_payment, 2) . ".");
        } else {
            return back()->with('success', "Adjustment of PKR {$amountFormatted} for {$empName} has been REDONE. Balance updated to PKR " . number_format($employee->pending_payment, 2) . ".");
        }
    }

    /**
     * Permanently delete an adjustment record.
     */
    public function destroyAdjustment(EmployeeAdjustment $adjustment)
    {
        $this->authorizeAdmin();

        $employee = $adjustment->employee;
        $adjustment->delete();

        if ($employee) {
            $employee->refresh();
            $employee->reconcileCreditSales();
        }

        return back()->with('success', "Due adjustment record deleted.");
    }

    /**
     * View employee credit statement & ledger.
     */
    public function employeeLedger(Employee $employee)
    {
        $this->authorizeAdmin();

        $employee->reconcileCreditSales();

        $creditSales = $employee->sales()
            ->where('payment_method', 'credit')
            ->where('status', '!=', 'voided')
            ->with(['items', 'user:id,name'])
            ->latest()
            ->get();

        $payments = $employee->payments()
            ->with('user:id,name')
            ->latest()
            ->get();

        $adjustments = $employee->adjustments()
            ->with('user:id,name')
            ->latest()
            ->get();

        return view('admin.staff.ledger', compact('employee', 'creditSales', 'payments', 'adjustments'));
    }

    /**
     * Add a new employee user (POS operator).
     */
    public function storeCashier(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'email'          => ['required', 'email', 'unique:users,email', 'max:150'],
            'password'       => ['required', 'string', 'min:6'],
            'salary'         => ['nullable', 'numeric', 'min:0'],
            'allowed_leaves' => ['nullable', 'integer', 'min:0', 'max:31'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'designation'    => ['nullable', 'string', 'max:100'],
        ]);

        $cashier = User::create([
            'name'           => $data['name'],
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),
            'role'           => 'cashier',
            'salary'         => $data['salary'] ?? 0.00,
            'allowed_leaves' => $data['allowed_leaves'] ?? 4,
            'phone'          => $data['phone'] ?? null,
            'designation'    => $data['designation'] ?? null,
            'is_active'      => true,
        ]);

        return back()->with('success', "Employee {$cashier->name} added successfully.");
    }

    /**
     * Update employee details.
     */
    public function updateCashier(Request $request, User $user)
    {
        $this->authorizeAdmin();

        abort_unless($user->role === 'cashier', 400, 'Only employee accounts can be edited here.');

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'email'          => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id), 'max:150'],
            'password'       => ['nullable', 'string', 'min:6'],
            'salary'         => ['nullable', 'numeric', 'min:0'],
            'allowed_leaves' => ['nullable', 'integer', 'min:0', 'max:31'],
            'phone'          => ['nullable', 'string', 'max:30'],
            'designation'    => ['nullable', 'string', 'max:100'],
            'is_active'      => ['required', 'boolean'],
        ]);

        $updateData = [
            'name'           => $data['name'],
            'email'          => $data['email'],
            'salary'         => $data['salary'] ?? 0.00,
            'allowed_leaves' => $data['allowed_leaves'] ?? 4,
            'phone'          => $data['phone'] ?? null,
            'designation'    => $data['designation'] ?? null,
            'is_active'      => $data['is_active'],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return back()->with('success', "Employee {$user->name} updated successfully.");
    }

    /**
     * Delete an employee.
     */
    public function destroyCashier(User $user)
    {
        $this->authorizeAdmin();

        abort_if($user->id === auth()->id(), 400, 'You cannot delete your own account.');
        abort_unless($user->role === 'cashier', 400, 'Only employee accounts can be deleted here.');

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Employee {$name} removed successfully.");
    }

    /**
     * Add a new administrator user.
     */
    public function storeAdmin(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'unique:users,email', 'max:150'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $admin = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        return back()->with('success', "Administrator {$admin->name} added successfully.");
    }

    /**
     * Update administrator details.
     */
    public function updateAdmin(Request $request, User $user)
    {
        $this->authorizeAdmin();

        abort_unless($user->role === 'admin', 400, 'Only administrator accounts can be edited here.');

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id), 'max:150'],
            'password'  => ['nullable', 'string', 'min:6'],
            'is_active' => ['required', 'boolean'],
        ]);

        // Prevent deactivating own account if only 1 active admin
        if (!$data['is_active'] && $user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot deactivate your own currently logged in administrator account.']);
        }

        $updateData = [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'is_active' => $data['is_active'],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return back()->with('success', "Administrator {$user->name} updated successfully.");
    }

    /**
     * Delete an administrator.
     */
    public function destroyAdmin(User $user)
    {
        $this->authorizeAdmin();

        abort_if($user->id === auth()->id(), 400, 'You cannot delete your own account.');
        abort_unless($user->role === 'admin', 400, 'Only administrator accounts can be deleted here.');

        $totalAdmins = User::where('role', 'admin')->count();
        if ($totalAdmins <= 1) {
            return back()->withErrors(['error' => 'Cannot delete the last remaining administrator account.']);
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Administrator {$name} removed successfully.");
    }
}

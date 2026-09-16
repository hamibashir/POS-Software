<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeePayment;
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

        $employeesQuery = Employee::with(['sales' => fn($q) => $q->where('payment_method', 'credit')->where('status', 'completed'), 'payments']);
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
        $allEmployees = Employee::with(['sales' => fn($q) => $q->where('payment_method', 'credit')->where('status', 'completed'), 'payments'])->get();
        $totalCredit  = $allEmployees->sum(fn($e) => $e->total_credit);
        $totalPaid    = $allEmployees->sum(fn($e) => $e->total_paid);
        $totalPending = max(0, $totalCredit - $totalPaid);

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
            'name'    => ['required', 'string', 'max:150'],
            'phone'   => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:255'],
            'notes'   => ['nullable', 'string', 'max:500'],
        ]);

        $employee = Employee::create([
            'name'      => $data['name'],
            'phone'     => $data['phone'],
            'address'   => $data['address'],
            'is_active' => true,
            'notes'     => $data['notes'] ?? null,
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
            'name'      => ['required', 'string', 'max:150'],
            'phone'     => ['required', 'string', 'max:30'],
            'address'   => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'notes'     => ['nullable', 'string', 'max:500'],
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

        return back()->with('success', "Payment of PKR " . number_format($data['amount'], 2) . " recorded for {$employee->name}. Pending dues updated.");
    }

    /**
     * View employee credit statement & ledger.
     */
    public function employeeLedger(Employee $employee)
    {
        $this->authorizeAdmin();

        $creditSales = $employee->sales()
            ->where('payment_method', 'credit')
            ->where('status', 'completed')
            ->with(['items', 'user:id,name'])
            ->latest()
            ->get();

        $payments = $employee->payments()
            ->with('user:id,name')
            ->latest()
            ->get();

        return view('admin.staff.ledger', compact('employee', 'creditSales', 'payments'));
    }

    /**
     * Add a new cashier user.
     */
    public function storeCashier(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'unique:users,email', 'max:150'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $cashier = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => 'cashier',
            'is_active' => true,
        ]);

        return back()->with('success', "Cashier {$cashier->name} added successfully.");
    }

    /**
     * Update cashier details.
     */
    public function updateCashier(Request $request, User $user)
    {
        $this->authorizeAdmin();

        abort_unless($user->role === 'cashier', 400, 'Only cashier accounts can be edited here.');

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id), 'max:150'],
            'password'  => ['nullable', 'string', 'min:6'],
            'is_active' => ['required', 'boolean'],
        ]);

        $updateData = [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'is_active' => $data['is_active'],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return back()->with('success', "Cashier {$user->name} updated successfully.");
    }

    /**
     * Delete a cashier.
     */
    public function destroyCashier(User $user)
    {
        $this->authorizeAdmin();

        abort_if($user->id === auth()->id(), 400, 'You cannot delete your own account.');
        abort_unless($user->role === 'cashier', 400, 'Only cashier accounts can be deleted here.');

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Cashier {$name} removed successfully.");
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

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\PurchaseReturnController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Cashier\PosController;
use App\Http\Controllers\Catalog\CatalogController;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Categories
        Route::resource('categories', CategoryController::class)->except(['create', 'edit']);
        Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])
            ->name('categories.toggle-status');

        // Products — generate-sku must be BEFORE resource to avoid wildcard conflict
        Route::get('products/generate-sku', [ProductController::class, 'generateSku'])
            ->name('products.generate-sku');
        Route::resource('products', ProductController::class);
        Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
            ->name('products.toggle-status');

        // Sales
        Route::get('sales',          [SalesController::class, 'index'])->name('sales.index');
        Route::get('sales/{sale}',   [SalesController::class, 'show'])->name('sales.show');

        // Purchases
        Route::get('purchases',              [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('purchases/create',       [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('purchases',             [PurchaseController::class, 'store'])->name('purchases.store');
        Route::get('purchases/{purchase}',   [PurchaseController::class, 'show'])->name('purchases.show');

        // Stock Returns
        Route::get('purchase-returns',                   [PurchaseReturnController::class, 'index'])->name('purchase-returns.index');
        Route::get('purchase-returns/create',            [PurchaseReturnController::class, 'create'])->name('purchase-returns.create');
        Route::post('purchase-returns',                  [PurchaseReturnController::class, 'store'])->name('purchase-returns.store');
        Route::get('purchase-returns/{purchaseReturn}',  [PurchaseReturnController::class, 'show'])->name('purchase-returns.show');

        // Stock Adjustments
        Route::get('stock-adjustments',        [StockAdjustmentController::class, 'index'])->name('stock.index');
        Route::get('stock-adjustments/create', [StockAdjustmentController::class, 'create'])->name('stock.create');
        Route::post('stock-adjustments',       [StockAdjustmentController::class, 'store'])->name('stock.store');

        // Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

        // Daily Expenditures
        Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'update', 'destroy']);

        // Suppliers & Payables - Admin only
        Route::get('suppliers',                          [SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('suppliers',                         [SupplierController::class, 'store'])->name('suppliers.store');
        Route::put('suppliers/{supplier}',               [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('suppliers/{supplier}',            [SupplierController::class, 'destroy'])->name('suppliers.destroy');
        Route::post('suppliers/{supplier}/payments',     [SupplierController::class, 'recordPayment'])->name('suppliers.payments');
        Route::get('suppliers/{supplier}/ledger',        [SupplierController::class, 'ledger'])->name('suppliers.ledger');

        // Staff Management (Employees, Cashiers & Administrators) - Admin only
        Route::get('staff',                                    [StaffController::class, 'index'])->name('staff.index');
        Route::post('staff/employees',                         [StaffController::class, 'storeEmployee'])->name('staff.employees.store');
        Route::put('staff/employees/{employee}',               [StaffController::class, 'updateEmployee'])->name('staff.employees.update');
        Route::delete('staff/employees/{employee}',            [StaffController::class, 'destroyEmployee'])->name('staff.employees.destroy');
        Route::post('staff/employees/{employee}/payments',     [StaffController::class, 'recordPayment'])->name('staff.employees.payments');
        Route::get('staff/employees/{employee}/ledger',        [StaffController::class, 'employeeLedger'])->name('staff.employees.ledger');
        Route::post('staff/cashiers',                          [StaffController::class, 'storeCashier'])->name('staff.cashiers.store');
        Route::put('staff/cashiers/{user}',                    [StaffController::class, 'updateCashier'])->name('staff.cashiers.update');
        Route::delete('staff/cashiers/{user}',                 [StaffController::class, 'destroyCashier'])->name('staff.cashiers.destroy');
        Route::post('staff/admins',                            [StaffController::class, 'storeAdmin'])->name('staff.admins.store');
        Route::put('staff/admins/{user}',                      [StaffController::class, 'updateAdmin'])->name('staff.admins.update');
        Route::delete('staff/admins/{user}',                   [StaffController::class, 'destroyAdmin'])->name('staff.admins.destroy');

        // Attendance & Monthly Payroll - Admin only
        Route::get('attendance',                               [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('attendance',                              [AttendanceController::class, 'mark'])->name('attendance.mark');
        Route::get('attendance/payroll',                       [AttendanceController::class, 'payroll'])->name('attendance.payroll');
        Route::post('attendance/payroll/{user}/pay',           [AttendanceController::class, 'disburseSalary'])->name('attendance.payroll.pay');
        Route::get('attendance/payroll/slip/{payment}',        [AttendanceController::class, 'salarySlip'])->name('attendance.payroll.slip');
    });

/*
|--------------------------------------------------------------------------
| Cashier Routes (accessible by both admin and cashier)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'cashier'])
    ->prefix('cashier')
    ->name('cashier.')
    ->group(function () {
        Route::get('/pos',                       [PosController::class, 'index'])->name('pos');
        Route::get('/pos/search',                 [PosController::class, 'searchProducts'])->name('pos.search');
        Route::post('/pos/complete-sale',         [PosController::class, 'completeSale'])->name('pos.complete-sale');
        Route::get('/pos/receipt/{sale}',         [PosController::class, 'receipt'])->name('pos.receipt');
        Route::get('/pos/suppliers',              [PosController::class, 'getSuppliers'])->name('pos.suppliers');
        Route::get('/pos/suppliers/{supplier}/low-stock', [PosController::class, 'getSupplierLowStock'])->name('pos.supplier-low-stock');
        Route::post('/pos/supplier-payments',     [PosController::class, 'recordSupplierPayment'])->name('pos.supplier-payments');
        Route::post('/pos/employee-payments',     [PosController::class, 'recordEmployeePayment'])->name('pos.employee-payments');
        Route::get('/pos/employee-receipt/{payment}', [PosController::class, 'employeePaymentReceipt'])->name('pos.employee-receipt');
        Route::get('/pos/lookup-sale',            [PosController::class, 'lookupSale'])->name('pos.lookup-sale');
        Route::post('/pos/process-return',        [PosController::class, 'processReturn'])->name('pos.process-return');
        Route::get('/pos/return-receipt/{saleReturn}', [PosController::class, 'returnReceipt'])->name('pos.return-receipt');
    });

/*
|--------------------------------------------------------------------------
| Public Catalog Routes (no auth required)
|--------------------------------------------------------------------------
*/

Route::get('/',             [CatalogController::class, 'home'])    ->name('catalog.home');
Route::get('/search',       [CatalogController::class, 'search'])  ->name('catalog.search');
Route::get('/c/{slug}',     [CatalogController::class, 'category'])->name('catalog.category');
Route::get('/p/{slug}',     [CatalogController::class, 'product']) ->name('catalog.product');

/*
|--------------------------------------------------------------------------
| Staff redirect (logged-in users going to /staff)
|--------------------------------------------------------------------------
*/

Route::get('/staff', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('cashier.pos');
    }
    return redirect()->route('login');
})->name('staff');


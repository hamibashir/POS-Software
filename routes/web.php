<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\PurchaseReturnController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\ReportController;
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
        Route::get('/pos',                [PosController::class, 'index'])->name('pos');
        Route::get('/pos/search',          [PosController::class, 'searchProducts'])->name('pos.search');
        Route::post('/pos/complete-sale',  [PosController::class, 'completeSale'])->name('pos.complete-sale');
        Route::get('/pos/receipt/{sale}',  [PosController::class, 'receipt'])->name('pos.receipt');
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


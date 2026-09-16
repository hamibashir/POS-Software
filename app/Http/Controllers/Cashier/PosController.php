<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeePayment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\SaleReturnService;
use App\Services\SaleService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function __construct(
        protected SaleService $saleService,
        protected SaleReturnService $saleReturnService
    ) {
    }

    /**
     * Show a printable receipt for a completed sale.
     */
    public function receipt(Sale $sale)
    {
        // Only allow viewing receipts for completed sales
        abort_unless($sale->status === 'completed', 404);

        $sale->load(['items', 'user', 'employee']);
        return view('cashier.receipt', compact('sale'));
    }

    /**
     * Render the POS interface.
     */
    public function index()
    {
        $employees = Employee::where('is_active', true)
            ->with(['sales' => fn($q) => $q->where('payment_method', 'credit')->where('status', 'completed'), 'payments'])
            ->orderBy('name')
            ->get()
            ->map(fn($e) => [
                'id'              => $e->id,
                'name'            => $e->name,
                'phone'           => $e->phone,
                'address'         => $e->address,
                'pending_payment' => $e->pending_payment,
            ]);

        $suppliers = Supplier::where('is_active', true)
            ->with(['purchases', 'returns', 'payments'])
            ->orderBy('name')
            ->get()
            ->map(fn($s) => [
                'id'              => $s->id,
                'name'            => $s->name,
                'company_name'    => $s->company_name,
                'phone'           => $s->phone,
                'pending_balance' => (float) $s->pending_balance,
            ]);

        return view('cashier.pos', compact('employees', 'suppliers'));
    }

    /**
     * AJAX: Search products by name, SKU, or barcode.
     * Returns JSON array of matching active products with sufficient stock.
     */
    public function searchProducts(Request $request)
    {
        $query      = $request->get('q', '');
        $categoryId = $request->get('category_id');

        $products = Product::where('is_active', true)
            ->when($query, function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('name',    'like', "%{$query}%")
                          ->orWhere('sku',     'like', "%{$query}%")
                          ->orWhere('barcode', $query);
                    if (is_numeric($query)) {
                        $inner->orWhere('id', (int)$query);
                    }
                });
            })
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->with('category:id,name')
            ->select(['id', 'name', 'sku', 'barcode', 'sale_price', 'cost_price',
                      'stock_quantity', 'low_stock_threshold', 'unit', 'category_id', 'image'])
            ->orderByRaw("CASE WHEN id = ? THEN 0 WHEN barcode = ? THEN 1 WHEN sku = ? THEN 2 ELSE 3 END", [
                is_numeric($query) ? (int)$query : 0,
                $query,
                $query
            ])
            ->orderBy('id', 'asc')
            ->limit(40)
            ->get()
            ->map(fn($p) => [
                'id'                  => $p->id,
                'name'                => $p->name,
                'sku'                 => $p->sku,
                'barcode'             => $p->barcode,
                'sale_price'          => (float) $p->sale_price,
                'cost_price'          => (float) $p->cost_price,
                'stock_quantity'      => $p->stock_quantity,
                'low_stock_threshold' => $p->low_stock_threshold ?? 5,
                'unit'                => $p->unit,
                'category'            => $p->category?->name,
                'image_url'           => $p->image ? asset('storage/' . $p->image) : asset('images/no-image.png'),
            ]);

        return response()->json($products);
    }

    /**
     * POST: Process and complete a sale.
     * Accepts JSON body with cart items and sale details.
     */
    public function completeSale(Request $request)
    {
        $data = $request->validate([
            'cart'                    => ['required', 'array', 'min:1'],
            'cart.*.product_id'       => ['required', 'integer', 'exists:products,id'],
            'cart.*.quantity'         => ['required', 'integer', 'min:1'],
            'cart.*.unit_price'       => ['required', 'numeric', 'min:0'],
            'cart.*.discount_amount'  => ['nullable', 'numeric', 'min:0'],
            'payment_method'          => ['required', 'in:cash,card,credit'],
            'paid_amount'             => ['required', 'numeric', 'min:0'],
            'discount_amount'         => ['nullable', 'numeric', 'min:0'],
            'customer_name'           => ['nullable', 'string', 'max:150'],
            'customer_phone'          => ['nullable', 'string', 'max:30'],
            'employee_id'             => ['nullable', 'required_if:payment_method,credit', 'integer', 'exists:employees,id'],
            'notes'                   => ['nullable', 'string', 'max:500'],
        ]);

        try {
            if ($data['payment_method'] === 'credit') {
                $employee = Employee::where('is_active', true)->findOrFail($data['employee_id']);
                $data['customer_name']  = $employee->name;
                $data['customer_phone'] = $employee->phone;
                $data['paid_amount']    = 0;
            }

            $sale = $this->saleService->complete(
                saleData: [
                    'payment_method'  => $data['payment_method'],
                    'paid_amount'     => $data['paid_amount'],
                    'discount_amount' => $data['discount_amount'] ?? 0,
                    'tax_amount'      => 0,
                    'customer_name'   => $data['customer_name'] ?? 'Walk-in Customer',
                    'customer_phone'  => $data['customer_phone'] ?? null,
                    'employee_id'     => $data['employee_id'] ?? null,
                    'notes'           => $data['notes'] ?? null,
                ],
                cartItems: array_map(fn($item) => [
                    'product_id'      => $item['product_id'],
                    'quantity'        => $item['quantity'],
                    'unit_price'      => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                ], $data['cart']),
                cashierId: auth()->id()
            );

            return response()->json([
                'success'        => true,
                'sale_id'        => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'total_amount'   => number_format($sale->total_amount, 2),
                'paid_amount'    => number_format($sale->paid_amount, 2),
                'change_amount'  => number_format($sale->change_amount, 2),
                'items_count'    => $sale->items->count(),
                'receipt_url'    => route('cashier.pos.receipt', $sale->id),
                'message'        => "Sale {$sale->invoice_number} completed successfully!",
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    /**
     * AJAX: Get list of active suppliers with pending balances for POS.
     */
    public function getSuppliers()
    {
        $suppliers = Supplier::where('is_active', true)
            ->with(['purchases', 'returns', 'payments'])
            ->orderBy('name')
            ->get()
            ->map(fn($s) => [
                'id'              => $s->id,
                'name'            => $s->name,
                'company_name'    => $s->company_name,
                'phone'           => $s->phone,
                'pending_balance' => (float) $s->pending_balance,
            ]);

        return response()->json($suppliers);
    }

    /**
     * AJAX: Get low stock or out-of-stock items for a specific supplier.
     */
    public function getSupplierLowStock(Supplier $supplier)
    {
        // 1. Fetch product IDs linked to purchases from this supplier
        $purchasesQuery = Purchase::where(function ($q) use ($supplier) {
            $q->where('supplier_id', $supplier->id);
            if (!empty($supplier->name)) {
                $q->orWhere('supplier_name', 'like', "%{$supplier->name}%");
            }
        });

        $productIds = PurchaseItem::whereIn('purchase_id', $purchasesQuery->pluck('id'))
            ->pluck('product_id')
            ->filter()
            ->unique();

        // 2. Fetch products that are low in stock or out of stock
        $lowStockProducts = Product::whereIn('id', $productIds)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                  ->orWhere('stock_quantity', '<=', 0);
            })
            ->with('category:id,name')
            ->orderBy('stock_quantity', 'asc')
            ->get()
            ->map(fn($p) => [
                'id'                  => $p->id,
                'name'                => $p->name,
                'sku'                 => $p->sku,
                'unit'                => $p->unit,
                'category'            => $p->category?->name ?? 'General',
                'stock_quantity'      => $p->stock_quantity,
                'low_stock_threshold' => $p->low_stock_threshold ?? 5,
                'is_out_of_stock'     => $p->stock_quantity <= 0,
                'cost_price'          => (float) $p->cost_price,
                'sale_price'          => (float) $p->sale_price,
            ]);

        return response()->json([
            'success'       => true,
            'supplier_id'   => $supplier->id,
            'supplier_name' => $supplier->name,
            'count'         => $lowStockProducts->count(),
            'items'         => $lowStockProducts,
        ]);
    }

    /**
     * AJAX: Record a payment made from the POS counter to a supplier.
     */
    public function recordSupplierPayment(Request $request)
    {
        $data = $request->validate([
            'supplier_id'      => ['required', 'integer', 'exists:suppliers,id'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'payment_method'   => ['required', 'in:cash,bank,cheque,online'],
            'reference_number' => ['nullable', 'string', 'max:60'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        $supplier = Supplier::findOrFail($data['supplier_id']);

        $payment = SupplierPayment::create([
            'supplier_id'      => $supplier->id,
            'amount'           => $data['amount'],
            'payment_method'   => $data['payment_method'],
            'payment_date'     => now()->toDateString(),
            'user_id'          => auth()->id(),
            'reference_number' => $data['reference_number'] ?? null,
            'notes'            => $data['notes'] ?? 'Paid at POS Counter',
        ]);

        $supplier->refresh();
        $newBalance = $supplier->pending_balance;

        return response()->json([
            'success'         => true,
            'payment_id'      => $payment->id,
            'supplier_id'     => $supplier->id,
            'supplier_name'   => $supplier->name,
            'amount_paid'     => number_format($data['amount'], 2),
            'new_balance'     => number_format($newBalance, 2),
            'raw_new_balance' => (float) $newBalance,
            'message'         => "Payment of PKR " . number_format($data['amount'], 2) . " cleared for '{$supplier->name}'. Remaining Balance: PKR " . number_format($newBalance, 2),
        ]);
    }

    /**
     * AJAX: Record a payment returned by an employee at the POS counter to clear pending credit dues.
     */
    public function recordEmployeePayment(Request $request)
    {
        $data = $request->validate([
            'employee_id'    => ['required', 'integer', 'exists:employees,id'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,bank,salary_deduction,online'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $employee = Employee::where('is_active', true)->findOrFail($data['employee_id']);

        $payment = EmployeePayment::create([
            'employee_id'    => $employee->id,
            'amount'         => $data['amount'],
            'payment_method' => $data['payment_method'],
            'payment_date'   => now()->toDateString(),
            'user_id'        => auth()->id(),
            'notes'          => $data['notes'] ?? 'Received & Cleared at POS Counter',
        ]);

        $employee->refresh();
        $newBalance = $employee->pending_payment;

        return response()->json([
            'success'         => true,
            'payment_id'      => $payment->id,
            'employee_id'     => $employee->id,
            'employee_name'   => $employee->name,
            'amount_paid'     => number_format($data['amount'], 2),
            'new_balance'     => number_format($newBalance, 2),
            'raw_new_balance' => (float) $newBalance,
            'receipt_url'     => route('cashier.pos.employee-receipt', $payment->id),
            'message'         => "Payment of PKR " . number_format($data['amount'], 2) . " received from '{$employee->name}'. Remaining Due: PKR " . number_format($newBalance, 2),
        ]);
    }

    /**
     * Show a printable receipt for an employee clearance payment.
     */
     public function employeePaymentReceipt(EmployeePayment $payment)
     {
         $payment->load(['employee', 'user']);
         return view('cashier.employee-receipt', compact('payment'));
     }

    /**
     * AJAX: Look up a sale by invoice number or search query to load sold items and original sales rates.
     */
    public function lookupSale(Request $request)
    {
        $query = trim($request->get('invoice', $request->get('q', '')));

        if (empty($query)) {
            return response()->json(['success' => false, 'message' => 'Please provide an invoice number.'], 422);
        }

        $sale = Sale::with(['items.product', 'items.returnItems', 'employee', 'user'])
            ->where('invoice_number', $query)
            ->orWhere('invoice_number', 'like', "%{$query}%")
            ->latest()
            ->first();

        if (!$sale) {
            return response()->json(['success' => false, 'message' => "Sale invoice '{$query}' not found."], 404);
        }

        $items = $sale->items->map(function ($item) {
            $returnedQty   = (int) $item->returnItems->sum('quantity');
            $returnableQty = max(0, $item->quantity - $returnedQty);

            return [
                'sale_item_id'        => $item->id,
                'product_id'          => $item->product_id,
                'product_name'        => $item->product_name,
                'product_sku'         => $item->product_sku,
                'product_unit'        => $item->product_unit,
                'quantity_sold'       => $item->quantity,
                'quantity_returned'   => $returnedQty,
                'quantity_returnable' => $returnableQty,
                'unit_price'          => (float) $item->unit_price, // Exact rate sold at
                'cost_price'          => (float) $item->cost_price,
                'total_price'         => (float) $item->total_price,
                'current_stock'       => $item->product?->stock_quantity ?? 0,
            ];
        });

        return response()->json([
            'success' => true,
            'sale'    => [
                'id'             => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'customer_name'  => $sale->customer_name,
                'customer_phone' => $sale->customer_phone,
                'employee_id'    => $sale->employee_id,
                'employee_name'  => $sale->employee?->name,
                'employee_due'   => $sale->employee ? (float) $sale->employee->pending_payment : null,
                'total_amount'   => (float) $sale->total_amount,
                'paid_amount'    => (float) $sale->paid_amount,
                'payment_method' => $sale->payment_method,
                'created_at'     => $sale->created_at->format('d M Y, g:i A'),
                'cashier_name'   => $sale->user?->name ?? 'N/A',
                'items'          => $items,
            ],
        ]);
    }

    /**
     * AJAX: Process a customer sale return and restore items back into stock.
     */
    public function processReturn(Request $request)
    {
        $data = $request->validate([
            'sale_id'             => ['nullable', 'integer', 'exists:sales,id'],
            'customer_name'       => ['nullable', 'string', 'max:150'],
            'customer_phone'      => ['nullable', 'string', 'max:30'],
            'employee_id'         => ['nullable', 'integer', 'exists:employees,id'],
            'refund_method'       => ['required', 'in:cash,card,credit_adjustment'],
            'refund_amount'       => ['nullable', 'numeric', 'min:0'],
            'reason'              => ['nullable', 'string', 'max:150'],
            'notes'               => ['nullable', 'string', 'max:500'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'integer', 'exists:products,id'],
            'items.*.sale_item_id' => ['nullable', 'integer', 'exists:sale_items,id'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
            'items.*.unit_price'   => ['required', 'numeric', 'min:0'],
            'items.*.reason'       => ['nullable', 'string', 'max:150'],
        ]);

        try {
            $saleReturn = $this->saleReturnService->processReturn(
                returnData: $data,
                items: $data['items'],
                cashierId: auth()->id()
            );

            // Fetch updated stock for returned products to sync POS UI
            $productIds = collect($data['items'])->pluck('product_id')->unique();
            $updatedProducts = Product::whereIn('id', $productIds)
                ->get(['id', 'name', 'sku', 'stock_quantity'])
                ->map(fn($p) => [
                    'id'             => $p->id,
                    'name'           => $p->name,
                    'sku'            => $p->sku,
                    'stock_quantity' => $p->stock_quantity,
                ]);

            return response()->json([
                'success'             => true,
                'return_id'           => $saleReturn->id,
                'return_number'       => $saleReturn->return_number,
                'total_return_amount' => number_format($saleReturn->total_return_amount, 2),
                'refund_amount'       => number_format($saleReturn->refund_amount, 2),
                'refund_method'       => $saleReturn->refund_method,
                'receipt_url'         => route('cashier.pos.return-receipt', $saleReturn->id),
                'updated_products'    => $updatedProducts,
                'message'             => "Customer Return {$saleReturn->return_number} processed! {$saleReturn->items->sum('quantity')} item(s) returned back to stock.",
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'An error occurred while processing the return.'], 500);
        }
    }

    /**
     * Show a printable receipt/voucher for a customer sale return.
     */
    public function returnReceipt(SaleReturn $saleReturn)
    {
        $saleReturn->load(['items.product', 'sale.items', 'employee', 'user']);
        return view('cashier.return-receipt', compact('saleReturn'));
    }
}

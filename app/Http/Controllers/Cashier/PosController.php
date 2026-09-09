<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\SaleService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function __construct(protected SaleService $saleService)
    {
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
                });
            })
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->with('category:id,name')
            ->select(['id', 'name', 'sku', 'barcode', 'sale_price', 'cost_price',
                      'stock_quantity', 'low_stock_threshold', 'unit', 'category_id', 'image'])
            ->orderByRaw("CASE WHEN barcode = ? THEN 0 ELSE 1 END", [$query])
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
}

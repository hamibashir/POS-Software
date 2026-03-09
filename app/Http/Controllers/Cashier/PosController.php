<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
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

        $sale->load(['items', 'user']);
        return view('cashier.receipt', compact('sale'));
    }

    /**
     * Render the POS interface.
     */
    public function index()
    {
        return view('cashier.pos');
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
            'payment_method'          => ['required', 'in:cash,card'],
            'paid_amount'             => ['required', 'numeric', 'min:0'],
            'discount_amount'         => ['nullable', 'numeric', 'min:0'],
            'customer_name'           => ['nullable', 'string', 'max:150'],
            'customer_phone'          => ['nullable', 'string', 'max:30'],
            'notes'                   => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $sale = $this->saleService->complete(
                saleData: [
                    'payment_method'  => $data['payment_method'],
                    'paid_amount'     => $data['paid_amount'],
                    'discount_amount' => $data['discount_amount'] ?? 0,
                    'tax_amount'      => 0,
                    'customer_name'   => $data['customer_name'] ?? 'Walk-in Customer',
                    'customer_phone'  => $data['customer_phone'] ?? null,
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
}

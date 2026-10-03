<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\SaleItem;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService)
    {
    }

    /**
     * Display paginated product list with search and filters.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'supplier'])
            ->orderBy('id', 'asc');

        // Search by name, SKU, or barcode
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->get('supplier_id'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        // Filter by stock
        if ($request->filled('stock')) {
            match ($request->get('stock')) {
                'low'      => $query->lowStock(),
                'out'      => $query->outOfStock(),
                'in_stock' => $query->where('stock_quantity', '>', 0),
                default    => null,
            };
        }

        $products   = $query->paginate(15)->withQueryString();
        $categories = Category::active()->orderBy('name')->get();
        $suppliers  = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name', 'company_name']);

        return view('admin.products.index', compact('products', 'categories', 'suppliers'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $categories   = Category::active()->orderBy('name')->get();
        $suppliers    = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name', 'company_name']);
        $suggestedSku = $this->productService->generateSku('');
        $units        = $this->unitOptions();

        return view('admin.products.create', compact('categories', 'suppliers', 'suggestedSku', 'units'));
    }

    /**
     * Store a new product.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $product = $this->productService->create($data, $request->file('image'));

        return redirect()->route('admin.products.index')
            ->with('success', "Product \"{$product->name}\" created successfully.");
    }

    /**
     * Show product details, sales history, stock movements audit trail, and purchase/return logs.
     */
    public function show(Request $request, Product $product)
    {
        $product->load(['category', 'supplier']);

        // 1. Stock movements history (chronological audit log)
        $stockMovements = StockMovement::with(['user', 'sale', 'purchase'])
            ->where('product_id', $product->id)
            ->orderBy('id', 'desc')
            ->get();

        // 2. Detailed sales breakdown
        $saleItems = SaleItem::with(['sale.user', 'sale.employee'])
            ->where('product_id', $product->id)
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->select('sale_items.*')
            ->orderBy('sales.created_at', 'desc')
            ->get();

        // 3. Customer returns breakdown
        $returnItems = SaleReturnItem::with(['saleReturn.user', 'saleReturn.employee'])
            ->where('product_id', $product->id)
            ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
            ->select('sale_return_items.*')
            ->orderBy('sale_returns.created_at', 'desc')
            ->get();

        // 4. Purchase invoices breakdown
        $purchaseItems = PurchaseItem::with(['purchase.supplier', 'purchase.user'])
            ->where('product_id', $product->id)
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->select('purchase_items.*')
            ->orderBy('purchases.received_at', 'desc')
            ->get();

        // 5. Aggregated Financial & Quantity Stats
        $totalSoldQty       = (int) $saleItems->sum('quantity');
        $totalSalesRevenue  = (float) $saleItems->sum('total_price');
        $totalReturnedQty   = (int) $returnItems->sum('quantity');
        $totalReturnAmount  = (float) $returnItems->sum('total_price');
        $netSoldQty         = max(0, $totalSoldQty - $totalReturnedQty);
        $netSalesRevenue    = max(0, $totalSalesRevenue - $totalReturnAmount);

        $totalPurchasedQty  = (int) $purchaseItems->sum('quantity');
        $totalPurchaseCost  = (float) $purchaseItems->sum('total_cost');

        $totalCogsSold      = $netSoldQty * (float) $product->cost_price;
        $totalGrossProfit   = $netSalesRevenue - $totalCogsSold;
        $profitMarginPct    = $netSalesRevenue > 0 ? round(($totalGrossProfit / $netSalesRevenue) * 100, 1) : 0;

        $stats = [
            'total_sold_qty'      => $totalSoldQty,
            'total_sales_revenue' => $totalSalesRevenue,
            'total_returned_qty'  => $totalReturnedQty,
            'total_return_amount' => $totalReturnAmount,
            'net_sold_qty'        => $netSoldQty,
            'net_sales_revenue'   => $netSalesRevenue,
            'total_purchased_qty' => $totalPurchasedQty,
            'total_purchase_cost' => $totalPurchaseCost,
            'total_gross_profit'  => $totalGrossProfit,
            'profit_margin_pct'   => $profitMarginPct,
        ];

        // Return JSON for AJAX quick-modal on products index
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'        => true,
                'product'        => [
                    'id'                  => $product->id,
                    'name'                => $product->name,
                    'sku'                 => $product->sku,
                    'barcode'             => $product->barcode,
                    'unit'                => $product->unit,
                    'category'            => $product->category?->name ?? 'Uncategorized',
                    'supplier'            => $product->supplier?->name ?? ($product->supplier_name ?? '—'),
                    'cost_price'          => (float) $product->cost_price,
                    'sale_price'          => (float) $product->sale_price,
                    'stock_quantity'      => (int) $product->stock_quantity,
                    'low_stock_threshold' => (int) $product->low_stock_threshold,
                    'stock_status'        => $product->stock_status,
                    'stock_status_label'  => $product->stock_status_label,
                    'image'               => $product->image ? Storage::url($product->image) : null,
                    'is_active'           => (bool) $product->is_active,
                ],
                'stats'          => $stats,
                'stockMovements' => $stockMovements->map(fn($m) => [
                    'id'           => $m->id,
                    'created_at'   => $m->created_at->format('M d, Y h:i A'),
                    'type'         => strtoupper(str_replace('_', ' ', $m->type)),
                    'raw_type'     => $m->type,
                    'quantity'     => ($m->quantity > 0 ? '+' : '') . $m->quantity,
                    'raw_qty'      => $m->quantity,
                    'stock_before' => $m->stock_before,
                    'stock_after'  => $m->stock_after,
                    'user_name'    => $m->user?->name ?? 'System',
                    'notes'        => $m->notes ?: '—',
                ]),
                'saleItems'      => $saleItems->map(fn($item) => [
                    'invoice_number' => $item->sale?->invoice_number ?? ('INV-#' . $item->sale_id),
                    'sale_id'        => $item->sale_id,
                    'date'           => $item->sale?->created_at ? $item->sale->created_at->format('M d, Y h:i A') : '—',
                    'customer'       => $item->sale?->employee?->name ?? 'Walk-in Customer',
                    'cashier'        => $item->sale?->user?->name ?? 'System',
                    'payment_method' => ucfirst($item->sale?->payment_method ?? 'Cash'),
                    'quantity'       => $item->quantity,
                    'unit_price'     => (float) $item->unit_price,
                    'total_price'    => (float) $item->total_price,
                ]),
                'returnItems'    => $returnItems->map(fn($ret) => [
                    'return_number'  => $ret->saleReturn?->return_number ?? ('RET-#' . $ret->sale_return_id),
                    'date'           => $ret->saleReturn?->returned_at ? \Carbon\Carbon::parse($ret->saleReturn->returned_at)->format('M d, Y h:i A') : '—',
                    'customer'       => $ret->saleReturn?->employee?->name ?? 'Walk-in Customer',
                    'refund_method'  => ucfirst(str_replace('_', ' ', $ret->saleReturn?->refund_method ?? 'cash')),
                    'quantity'       => $ret->quantity,
                    'unit_price'     => (float) $ret->unit_price,
                    'total_price'    => (float) $ret->total_price,
                    'reason'         => $ret->reason ?: ($ret->saleReturn?->reason ?: '—'),
                    'cashier'        => $ret->saleReturn?->user?->name ?? 'Cashier',
                ]),
                'purchaseItems'  => $purchaseItems->map(fn($pu) => [
                    'reference_number' => $pu->purchase?->reference_number ?? ('PO-#' . $pu->purchase_id),
                    'purchase_id'      => $pu->purchase_id,
                    'date'             => $pu->purchase?->received_at ? \Carbon\Carbon::parse($pu->purchase->received_at)->format('M d, Y') : '—',
                    'supplier'         => $pu->purchase?->supplier?->name ?? ($pu->purchase?->supplier_name ?? '—'),
                    'quantity'         => $pu->quantity,
                    'unit_cost'        => (float) $pu->unit_cost,
                    'total_cost'       => (float) $pu->total_cost,
                    'user_name'        => $pu->purchase?->user?->name ?? 'Admin',
                ]),
            ]);
        }

        return view('admin.products.show', compact(
            'product',
            'stockMovements',
            'saleItems',
            'returnItems',
            'purchaseItems',
            'stats'
        ));
    }

    /**
     * Show edit form.
     */
    public function edit(Product $product)
    {
        $categories = Category::active()->orderBy('name')->get();
        $suppliers  = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name', 'company_name']);
        $units      = $this->unitOptions();

        return view('admin.products.edit', compact('product', 'categories', 'suppliers', 'units'));
    }

    /**
     * Update an existing product.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $this->productService->update(
            $product,
            $data,
            $request->file('image'),
            $request->boolean('remove_image')
        );

        return redirect()->route('admin.products.index', $request->query())
            ->with('success', "Product \"{$product->name}\" updated successfully.");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(Request $request, Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        $status = $product->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.products.index', $request->query())
            ->with('success', "Product \"{$product->name}\" {$status}.");
    }

    /**
     * Soft-delete a product.
     */
    public function destroy(Request $request, Product $product)
    {
        $name = $product->name;
        $this->productService->delete($product);

        return redirect()->route('admin.products.index', $request->query())
            ->with('success', "Product \"{$name}\" deleted.");
    }

    /**
     * Quick stock adjustment directly from the products list.
     */
    public function quickStockUpdate(Request $request, Product $product)
    {
        $request->validate([
            'stock_quantity' => 'nullable|integer',
            'add_quantity'   => 'nullable|integer',
            'notes'          => 'nullable|string|max:255',
        ]);

        $stockBefore = (int) $product->stock_quantity;

        if ($request->filled('add_quantity')) {
            $add = (int) $request->input('add_quantity');
            $stockAfter = $stockBefore + $add;
        } elseif ($request->has('stock_quantity') && $request->input('stock_quantity') !== null && $request->input('stock_quantity') !== '') {
            $stockAfter = (int) $request->input('stock_quantity');
        } else {
            $stockAfter = $stockBefore;
        }

        $diff = $stockAfter - $stockBefore;

        if ($diff !== 0) {
            \App\Models\StockMovement::create([
                'product_id'   => $product->id,
                'type'         => $diff > 0 ? 'adjustment_in' : 'adjustment_out',
                'quantity'     => abs($diff),
                'stock_before' => $stockBefore,
                'stock_after'  => $stockAfter,
                'user_id'      => auth()->id(),
                'notes'        => $request->input('notes') ?: 'Quick stock adjustment from product list',
            ]);

            $product->update(['stock_quantity' => $stockAfter]);
        }

        $product->refresh();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'            => true,
                'product_id'         => $product->id,
                'stock_quantity'     => (int) $product->stock_quantity,
                'stock_status'       => $product->stock_status,
                'stock_status_label' => $product->stock_status_label,
                'badge_class'        => $product->stock_status === 'in_stock' ? 'stock-in' : ($product->stock_status === 'low_stock' ? 'stock-low' : 'stock-out'),
                'message'            => "Stock for \"{$product->name}\" updated to {$product->stock_quantity}.",
            ]);
        }

        return redirect()->route('admin.products.index', $request->query())
            ->with('success', "Stock for \"{$product->name}\" updated to {$product->stock_quantity}.");
    }

    /**
     * Update product image directly from products table (AJAX or standard POST).
     */
    public function quickImageUpdate(Request $request, Product $product)
    {
        $request->validate([
            'image'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'image_base64' => ['nullable', 'string'],
            'image_url'    => ['nullable', 'url'],
            'remove_image' => ['nullable'],
        ]);

        $removeImage = $request->boolean('remove_image');
        $data = [];

        if ($request->filled('image_base64')) {
            $data['image_base64'] = $request->input('image_base64');
        }
        if ($request->filled('image_url')) {
            $data['image_url'] = $request->input('image_url');
        }

        $this->productService->processImage($data, $request->file('image'), $product, $removeImage);

        if (array_key_exists('image', $data) || $removeImage) {
            $product->update(['image' => $data['image'] ?? null]);
        }

        $product->refresh();

        $imageUrl = $product->image ? Storage::url($product->image) : asset('images/no-image.png');
        $hasImage = (bool) $product->image;

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'product_id' => $product->id,
                'image_url'  => $imageUrl,
                'has_image'  => $hasImage,
                'message'    => $hasImage ? "Image for \"{$product->name}\" updated successfully." : "Image removed for \"{$product->name}\".",
            ]);
        }

        return redirect()->route('admin.products.index', $request->query())
            ->with('success', $hasImage ? "Image for \"{$product->name}\" updated successfully." : "Image removed for \"{$product->name}\".");
    }

    /**
     * API: Generate a unique SKU for a given product name (used via AJAX).
     */
    public function generateSku(Request $request)
    {
        $name = $request->get('name', '');
        return response()->json(['sku' => $this->productService->generateSku($name)]);
    }

    /**
     * Return unit options list.
     */
    private function unitOptions(): array
    {
        return [
            'pc'     => 'Piece (pc)',
            'kg'     => 'Kilogram (kg)',
            'meter'  => 'Meter (m)',
            'feet'   => 'Feet (ft)',
            'box'    => 'Box',
            'liter'  => 'Liter (L)',
            'pair'   => 'Pair',
            'set'    => 'Set',
            'roll'   => 'Roll',
            'sheet'  => 'Sheet',
            'bag'    => 'Bag',
        ];
    }
}

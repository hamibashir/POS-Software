<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
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
     * Show product details or redirect to edit.
     */
    public function show(Product $product)
    {
        return redirect()->route('admin.products.edit', $product);
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

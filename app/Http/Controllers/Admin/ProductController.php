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

        return redirect()->route('admin.products.index')
            ->with('success', "Product \"{$product->name}\" updated successfully.");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        $status = $product->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.products.index')
            ->with('success', "Product \"{$product->name}\" {$status}.");
    }

    /**
     * Soft-delete a product.
     */
    public function destroy(Product $product)
    {
        $name = $product->name;
        $this->productService->delete($product);

        return redirect()->route('admin.products.index')
            ->with('success', "Product \"{$name}\" deleted.");
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

<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CatalogController extends Controller
{
    // ─── Home Page ───────────────────────────────────────────────

    public function home()
    {
        $categories = Cache::remember('catalog.categories', 300, function () {
            return Category::active()
                ->whereHas('products', fn($q) => $q->inCatalog())
                ->withCount(['products' => fn($q) => $q->inCatalog()])
                ->orderBy('name')
                ->get();
        });

        $featured = Product::inCatalog()
            ->with('category')
            ->orderBy('id', 'asc')
            ->limit(8)
            ->get();

        return view('catalog.home', compact('categories', 'featured'));
    }

    // ─── Category Page ───────────────────────────────────────────

    public function category(Request $request, string $slug)
    {
        $category = Category::active()->where('slug', $slug)->firstOrFail();

        $products = Product::inCatalog()
            ->where('category_id', $category->id)
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->q;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('sku', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        $categories = Category::active()
            ->whereHas('products', fn($q) => $q->inCatalog())
            ->withCount(['products' => fn($q) => $q->inCatalog()])
            ->orderBy('name')
            ->get();

        return view('catalog.category', compact('category', 'products', 'categories'));
    }

    // ─── Product Detail Page ─────────────────────────────────────

    public function product(string $slug)
    {
        $product = Product::inCatalog()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Product::inCatalog()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return view('catalog.product', compact('product', 'related'));
    }

    // ─── Search ──────────────────────────────────────────────────

    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));

        $products = Product::inCatalog()
            ->with('category')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('name', 'LIKE', "%{$q}%")
                       ->orWhere('sku',  'LIKE', "%{$q}%")
                       ->orWhereHas('category', fn($c) => $c->where('name', 'LIKE', "%{$q}%"));
                });
            })
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        $categories = Category::active()
            ->whereHas('products', fn($q) => $q->inCatalog())
            ->withCount(['products' => fn($q) => $q->inCatalog()])
            ->orderBy('name')
            ->get();

        return view('catalog.search', compact('products', 'q', 'categories'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    /**
     * List all manual stock adjustments.
     */
    public function index(Request $request)
    {
        $query = StockMovement::with(['product:id,name,sku', 'user:id,name'])
            ->latest();

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        } else {
            $query->whereIn('type', ['adjustment_in', 'adjustment_out', 'return']);
        }

        if ($search = $request->get('search')) {
            $query->whereHas('product', fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku',  'like', "%{$search}%")
            );
        }
        if ($from = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $movements = $query->paginate(25)->withQueryString();

        return view('admin.stock.index', compact('movements'));
    }

    /**
     * Show the adjustment form.
     */
    public function create()
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'unit', 'stock_quantity']);

        return view('admin.stock.create', compact('products'));
    }

    /**
     * Save the adjustment and update stock.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'type'       => ['required', 'in:adjustment_in,adjustment_out'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'notes'      => ['required', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($data) {
                $product     = Product::lockForUpdate()->findOrFail($data['product_id']);
                $stockBefore = $product->stock_quantity;

                if ($data['type'] === 'adjustment_out' && $data['quantity'] > $stockBefore) {
                    throw new \RuntimeException(
                        "Cannot remove {$data['quantity']} units — only {$stockBefore} in stock."
                    );
                }

                $delta      = $data['type'] === 'adjustment_in' ? $data['quantity'] : -$data['quantity'];
                $stockAfter = $stockBefore + $delta;

                $product->update(['stock_quantity' => $stockAfter]);

                StockMovement::create([
                    'product_id'   => $product->id,
                    'type'         => $data['type'],
                    'quantity'     => $data['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockAfter,
                    'user_id'      => auth()->id(),
                    'notes'        => $data['notes'],
                ]);
            });

            return redirect()
                ->route('admin.stock.index')
                ->with('success', 'Stock adjustment saved successfully.');

        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}

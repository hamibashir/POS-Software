<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(protected PurchaseService $purchaseService) {}

    /**
     * List all purchases.
     */
    public function index(Request $request)
    {
        $query = Purchase::with('user:id,name')
            ->withCount('items')
            ->latest();

        if ($search = $request->get('search')) {
            $query->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%");
        }
        if ($from = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $purchases = $query->paginate(20)->withQueryString();

        $stats = [
            'today_purchases' => Purchase::whereDate('created_at', today())->count(),
            'today_cost'      => Purchase::whereDate('created_at', today())->sum('total_amount'),
            'total_purchases' => Purchase::count(),
            'total_cost'      => Purchase::sum('total_amount'),
        ];

        return view('admin.purchases.index', compact('purchases', 'stats'));
    }

    /**
     * Create form — pass all active products as JSON for the JS rows.
     */
    public function create()
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'unit', 'cost_price', 'stock_quantity']);

        return view('admin.purchases.create', compact('products'));
    }

    /**
     * Persist the purchase.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_name'         => ['required', 'string', 'max:150'],
            'supplier_phone'        => ['nullable', 'string', 'max:30'],
            'payment_method'        => ['required', 'in:cash,card,credit'],
            'received_at'           => ['nullable', 'date'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1'],
            'items.*.unit_cost'     => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $purchase = $this->purchaseService->create(
                purchaseData: [
                    'supplier_name'  => $data['supplier_name'],
                    'supplier_phone' => $data['supplier_phone'] ?? null,
                    'payment_method' => $data['payment_method'],
                    'received_at'    => $data['received_at'] ?? null,
                    'notes'          => $data['notes'] ?? null,
                ],
                items:   $data['items'],
                userId:  auth()->id()
            );

            return redirect()
                ->route('admin.purchases.show', $purchase)
                ->with('success', "Purchase {$purchase->reference_number} recorded. Stock updated for {$purchase->items->count()} product(s).");

        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to save purchase: ' . $e->getMessage()]);
        }
    }

    /**
     * Purchase detail view.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load(['items', 'user:id,name']);
        return view('admin.purchases.show', compact('purchase'));
    }
}

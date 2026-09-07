<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Services\PurchaseReturnService;
use Illuminate\Http\Request;

class PurchaseReturnController extends Controller
{
    public function __construct(protected PurchaseReturnService $purchaseReturnService) {}

    /**
     * List all purchase/stock returns.
     */
    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['user:id,name', 'purchase:id,reference_number'])
            ->withCount('items')
            ->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%");
            });
        }

        if ($from = $request->get('date_from')) {
            $query->whereDate('returned_at', '>=', $from);
        }

        if ($to = $request->get('date_to')) {
            $query->whereDate('returned_at', '<=', $to);
        }

        if ($method = $request->get('refund_method')) {
            $query->where('refund_method', $method);
        }

        if ($status = $request->get('refund_status')) {
            $query->where('refund_status', $status);
        }

        $returns = $query->paginate(20)->withQueryString();

        $stats = [
            'total_returns'       => PurchaseReturn::count(),
            'total_return_amount' => (float) PurchaseReturn::sum('total_return_amount'),
            'total_refunded'      => (float) PurchaseReturn::sum('refund_amount'),
            'today_returns'       => PurchaseReturn::whereDate('returned_at', today())->count(),
            'today_refunded'      => (float) PurchaseReturn::whereDate('returned_at', today())->sum('refund_amount'),
        ];

        return view('admin.purchase-returns.index', compact('returns', 'stats'));
    }

    /**
     * Create a stock return.
     */
    public function create(Request $request)
    {
        $selectedPurchase = null;
        if ($purchaseId = $request->get('purchase_id')) {
            $selectedPurchase = Purchase::with(['items.product', 'returns.items'])->find($purchaseId);
        }

        $recentPurchases = Purchase::with('items')
            ->latest()
            ->take(50)
            ->get(['id', 'reference_number', 'supplier_name', 'supplier_phone', 'total_amount', 'created_at']);

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'unit', 'cost_price', 'stock_quantity']);

        return view('admin.purchase-returns.create', compact('selectedPurchase', 'recentPurchases', 'products'));
    }

    /**
     * Save the stock return.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_name'              => ['required', 'string', 'max:150'],
            'supplier_phone'             => ['nullable', 'string', 'max:30'],
            'purchase_id'                => ['nullable', 'integer', 'exists:purchases,id'],
            'refund_method'              => ['required', 'in:cash,card,credit'],
            'refund_status'              => ['required', 'in:completed,pending'],
            'refund_amount'              => ['nullable', 'numeric', 'min:0'],
            'returned_at'                => ['nullable', 'date'],
            'reason'                     => ['nullable', 'string', 'max:150'],
            'notes'                      => ['nullable', 'string', 'max:1000'],
            'items'                      => ['required', 'array', 'min:1'],
            'items.*.product_id'         => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'           => ['required', 'integer', 'min:1'],
            'items.*.unit_cost'          => ['required', 'numeric', 'min:0'],
            'items.*.reason'             => ['nullable', 'string', 'max:150'],
            'items.*.purchase_item_id'   => ['nullable', 'integer'],
        ]);

        try {
            $purchaseReturn = $this->purchaseReturnService->create(
                returnData: [
                    'supplier_name'  => $data['supplier_name'],
                    'supplier_phone' => $data['supplier_phone'] ?? null,
                    'purchase_id'    => $data['purchase_id'] ?? null,
                    'refund_method'  => $data['refund_method'],
                    'refund_status'  => $data['refund_status'],
                    'refund_amount'  => $data['refund_amount'] ?? null,
                    'returned_at'    => $data['returned_at'] ?? now()->toDateString(),
                    'reason'         => $data['reason'] ?? null,
                    'notes'          => $data['notes'] ?? null,
                ],
                items:  $data['items'],
                userId: auth()->id()
            );

            return redirect()
                ->route('admin.purchase-returns.show', $purchaseReturn)
                ->with('success', "Stock return {$purchaseReturn->reference_number} recorded successfully. Inventory and history updated.");

        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to process stock return: ' . $e->getMessage()]);
        }
    }

    /**
     * Show return details / receipt slip.
     */
    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['items.product', 'user:id,name', 'purchase']);
        return view('admin.purchase-returns.show', compact('purchaseReturn'));
    }
}

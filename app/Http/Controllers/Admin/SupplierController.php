<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Administrator privileges required.');
        }
    }

    /**
     * List all suppliers with stats and quick actions.
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $query = Supplier::with(['purchases', 'returns', 'payments'])->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->get('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->get('status') === 'inactive') {
            $query->where('is_active', false);
        }

        $allSuppliers = $query->get();

        // Filter by balance if requested
        if ($request->get('balance') === 'due') {
            $allSuppliers = $allSuppliers->filter(fn($s) => $s->pending_balance > 0);
        } elseif ($request->get('balance') === 'cleared') {
            $allSuppliers = $allSuppliers->filter(fn($s) => $s->pending_balance <= 0);
        }

        // Summary Stats across entire database
        $statsSuppliers = Supplier::with(['purchases', 'returns', 'payments'])->get();
        $stats = [
            'total_suppliers'    => $statsSuppliers->count(),
            'active_suppliers'   => $statsSuppliers->where('is_active', true)->count(),
            'total_invoiced'     => $statsSuppliers->sum(fn($s) => $s->total_purchases + (float)$s->opening_balance),
            'total_paid'         => $statsSuppliers->sum(fn($s) => $s->total_paid),
            'total_pending_dues' => $statsSuppliers->sum(fn($s) => $s->pending_balance),
        ];

        // Paginate collection
        $page = (int) $request->get('page', 1);
        $perPage = 20;
        $suppliers = new \Illuminate\Pagination\LengthAwarePaginator(
            $allSuppliers->forPage($page, $perPage)->values(),
            $allSuppliers->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.suppliers.index', compact('suppliers', 'stats'));
    }

    /**
     * Store a new supplier.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:150'],
            'company_name'    => ['nullable', 'string', 'max:150'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'email'           => ['nullable', 'email', 'max:100'],
            'address'         => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $supplier = Supplier::create([
            'name'            => $data['name'],
            'company_name'    => $data['company_name'] ?? null,
            'phone'           => $data['phone'] ?? null,
            'email'           => $data['email'] ?? null,
            'address'         => $data['address'] ?? null,
            'opening_balance' => $data['opening_balance'] ?? 0.00,
            'is_active'       => true,
            'notes'           => $data['notes'] ?? null,
        ]);

        return back()->with('success', "Supplier '{$supplier->name}' added successfully.");
    }

    /**
     * Update supplier details.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:150'],
            'company_name'    => ['nullable', 'string', 'max:150'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'email'           => ['nullable', 'email', 'max:100'],
            'address'         => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'is_active'       => ['required', 'boolean'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $supplier->update($data);

        return back()->with('success', "Supplier '{$supplier->name}' updated successfully.");
    }

    /**
     * Deactivate or delete supplier.
     */
    public function destroy(Supplier $supplier)
    {
        $this->authorizeAdmin();

        if ($supplier->purchases()->exists() || $supplier->payments()->exists() || $supplier->returns()->exists()) {
            $supplier->update(['is_active' => false]);
            return back()->with('success', "Supplier '{$supplier->name}' has transaction records and has been deactivated instead of deleted.");
        }

        $name = $supplier->name;
        $supplier->delete();

        return back()->with('success', "Supplier '{$name}' deleted successfully.");
    }

    /**
     * Record a payment made to clear supplier balance.
     */
    public function recordPayment(Request $request, Supplier $supplier)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'payment_method'   => ['required', 'in:cash,bank,cheque,online'],
            'payment_date'     => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:60'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        $payment = SupplierPayment::create([
            'supplier_id'      => $supplier->id,
            'amount'           => $data['amount'],
            'payment_method'   => $data['payment_method'],
            'payment_date'     => $data['payment_date'],
            'user_id'          => auth()->id(),
            'reference_number' => $data['reference_number'] ?? null,
            'notes'            => $data['notes'] ?? null,
        ]);

        $supplier->refresh();
        $newBalance = number_format($supplier->pending_balance, 2);

        return back()->with('success', "Payment of PKR " . number_format($data['amount'], 2) . " recorded for {$supplier->name}. Remaining pending balance: PKR {$newBalance}.");
    }

    /**
     * Detailed supplier financial ledger statement.
     */
    public function ledger(Supplier $supplier)
    {
        $this->authorizeAdmin();

        $supplier->load(['purchases.items', 'returns.items', 'payments.user']);

        // Build unified chronological timeline
        $entries = collect();

        // 1. Opening balance
        if ((float) $supplier->opening_balance > 0) {
            $entries->push([
                'date'        => $supplier->created_at,
                'type'        => 'opening_balance',
                'ref'         => 'OB-' . $supplier->id,
                'description' => 'Opening Balance',
                'debit'       => (float) $supplier->opening_balance, // Store owes supplier
                'credit'      => 0.0,
                'method'      => '—',
                'user'        => 'System',
            ]);
        }

        // 2. Purchases (Debit: increases store liability / money owed to supplier)
        foreach ($supplier->purchases as $purchase) {
            $entries->push([
                'date'        => $purchase->received_at ?? $purchase->created_at,
                'type'        => 'purchase',
                'ref'         => $purchase->reference_number,
                'description' => "Purchase ({$purchase->items->count()} item(s))",
                'debit'       => (float) $purchase->total_amount,
                'credit'      => 0.0,
                'method'      => ucfirst($purchase->payment_method ?? 'cash'),
                'user'        => $purchase->user?->name ?? 'Admin',
                'link'        => route('admin.purchases.show', $purchase),
            ]);
        }

        // 3. Purchase Returns (Credit: decreases money owed to supplier)
        foreach ($supplier->returns as $ret) {
            $entries->push([
                'date'        => $ret->returned_at ?? $ret->created_at,
                'type'        => 'return',
                'ref'         => $ret->reference_number,
                'description' => "Return / Refund ({$ret->reason})",
                'debit'       => 0.0,
                'credit'      => (float) $ret->total_return_amount,
                'method'      => ucfirst($ret->refund_method ?? 'credit'),
                'user'        => $ret->user?->name ?? 'Admin',
                'link'        => route('admin.purchase-returns.show', $ret),
            ]);
        }

        // 4. Payments (Credit: payments made decrease money owed to supplier)
        foreach ($supplier->payments as $pay) {
            $entries->push([
                'date'        => $pay->payment_date ? \Carbon\Carbon::parse($pay->payment_date) : $pay->created_at,
                'type'        => 'payment',
                'ref'         => $pay->reference_number ?? ('PAY-' . $pay->id),
                'description' => 'Payment Cleared' . ($pay->notes ? " ({$pay->notes})" : ''),
                'debit'       => 0.0,
                'credit'      => (float) $pay->amount,
                'method'      => ucfirst($pay->payment_method ?? 'cash'),
                'user'        => $pay->user?->name ?? 'Staff',
                'link'        => null,
            ]);
        }

        // Sort chronologically ascending to compute running balance
        $sortedEntries = $entries->sortBy(fn($e) => $e['date'] instanceof \Carbon\Carbon ? $e['date']->timestamp : strtotime($e['date']))->values();

        $runningBalance = 0.0;
        $ledgerRows = $sortedEntries->map(function ($row) use (&$runningBalance) {
            $runningBalance += ($row['debit'] - $row['credit']);
            $row['balance'] = $runningBalance;
            return $row;
        });

        return view('admin.suppliers.ledger', compact('supplier', 'ledgerRows'));
    }
}

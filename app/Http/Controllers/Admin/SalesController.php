<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    /**
     * Sales listing with filters.
     */
    public function index(Request $request)
    {
        $query = Sale::with(['user:id,name', 'items'])
            ->withCount('items')
            ->latest();

        // — Search by invoice number or customer
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        // — Filter by date (from)
        if ($from = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        // — Filter by date (to)
        if ($to = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        // — Filter by payment method
        if ($payment = $request->get('payment_method')) {
            $query->where('payment_method', $payment);
        }

        // — Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $sales = $query->paginate(20)->withQueryString();

        // Summary stats (for the filtered query — without pagination)
        $statsQuery = Sale::where('status', 'completed');
        if ($from)    { $statsQuery->whereDate('created_at', '>=', $from); }
        if ($to)      { $statsQuery->whereDate('created_at', '<=', $to); }
        if ($payment) { $statsQuery->where('payment_method', $payment); }

        $todayDirectRevenue = (float) Sale::where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum(DB::raw("CASE WHEN payment_method != 'credit' THEN total_amount ELSE paid_amount END"));

        $todayCreditCleared = (float) \App\Models\EmployeePayment::whereDate(
            DB::raw('COALESCE(payment_date, created_at)'), today()
        )->sum('amount');

        $stats = [
            'total_sales'    => $statsQuery->count(),
            'total_revenue'  => $statsQuery->sum('total_amount'),
            'today_sales'    => Sale::where('status', 'completed')->whereDate('created_at', today())->count(),
            'today_revenue'  => $todayDirectRevenue + $todayCreditCleared,
        ];

        return view('admin.sales.index', compact('sales', 'stats'));
    }

    /**
     * Sale detail view.
     */
    public function show(Sale $sale)
    {
        $sale->load(['items', 'user:id,name']);
        return view('admin.sales.show', compact('sale'));
    }
}

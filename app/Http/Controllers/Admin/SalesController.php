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
        $query = Sale::with(['user:id,name', 'items', 'returns', 'employee'])
            ->withCount('items')
            ->latest();

        // — Search by invoice number or customer
        $searchClosure = null;
        if ($search = $request->get('search')) {
            $searchClosure = function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            };
            $query->where($searchClosure);
        }

        // — Filter by date: By default show only today's sales unless a date/range or all_dates is explicitly requested
        $isDefaultView = !$request->has('date_from') && !$request->has('date_to') && !$request->boolean('all_dates');

        if ($isDefaultView) {
            $from = today()->toDateString();
            $to   = today()->toDateString();
        } else {
            $from = $request->filled('date_from') ? $request->get('date_from') : null;
            $to   = $request->filled('date_to') ? $request->get('date_to') : null;
        }

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
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
        $statsQuery = Sale::where('status', '!=', 'voided');
        if ($searchClosure) { $statsQuery->where($searchClosure); }
        if ($from)          { $statsQuery->whereDate('created_at', '>=', $from); }
        if ($to)            { $statsQuery->whereDate('created_at', '<=', $to); }
        if ($payment)       { $statsQuery->where('payment_method', $payment); }
        if ($status)        {
            $statsQuery->where('status', $status);
        }

        // Filtered revenue calculation (Sales total_amount - Returns)
        $filteredDirectRevenue = (float) (clone $statsQuery)->sum('total_amount');

        $filteredReturns = 0.0;
        if (!$payment || $payment !== 'credit') {
            $returnsQuery = DB::table('sale_returns');
            if ($from) {
                $returnsQuery->whereDate(DB::raw('COALESCE(returned_at, created_at)'), '>=', $from);
            }
            if ($to) {
                $returnsQuery->whereDate(DB::raw('COALESCE(returned_at, created_at)'), '<=', $to);
            }
            $filteredReturns = (float) $returnsQuery->sum('total_return_amount');
        }

        $filteredRevenue = max(0, $filteredDirectRevenue - $filteredReturns);

        $todayDirectRevenue = (float) Sale::where('status', '!=', 'voided')
            ->whereDate('created_at', today())
            ->sum('total_amount');

        $todayReturns = (float) DB::table('sale_returns')
            ->whereDate(DB::raw('COALESCE(returned_at, created_at)'), today())
            ->sum('total_return_amount');

        $todayRevenue = max(0, $todayDirectRevenue - $todayReturns);

        $stats = [
            'total_sales'      => $statsQuery->count(),
            'total_revenue'    => $filteredRevenue,
            'filtered_returns' => $filteredReturns,
            'today_sales'      => Sale::where('status', '!=', 'voided')->whereDate('created_at', today())->count(),
            'today_revenue'    => $todayRevenue,
            'today_returns'    => $todayReturns,
        ];

        return view('admin.sales.index', compact('sales', 'stats', 'from', 'to', 'isDefaultView'));
    }

    /**
     * Sale detail view.
     */
    public function show(Sale $sale)
    {
        $sale->load(['items.returnItems', 'user:id,name', 'employee', 'returns.items', 'returns.user']);
        return view('admin.sales.show', compact('sale'));
    }
}

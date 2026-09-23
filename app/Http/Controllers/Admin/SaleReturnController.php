<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaleReturn;
use Illuminate\Http\Request;

class SaleReturnController extends Controller
{
    /**
     * Display a listing of customer returns with filtering and financial stats.
     */
    public function index(Request $request)
    {
        $query = SaleReturn::with(['user:id,name', 'employee:id,name', 'sale:id,invoice_number', 'items'])
            ->withCount('items')
            ->latest();

        // Search by Return Number, Customer Name/Phone, or Original Invoice Number
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhereHas('sale', function ($sq) use ($search) {
                      $sq->where('invoice_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by Date (From)
        if ($from = $request->get('date_from')) {
            $query->whereDate('returned_at', '>=', $from);
        }

        // Filter by Date (To)
        if ($to = $request->get('date_to')) {
            $query->whereDate('returned_at', '<=', $to);
        }

        // Filter by Refund Method
        if ($method = $request->get('refund_method')) {
            $query->where('refund_method', $method);
        }

        // Filter by Reason
        if ($reason = $request->get('reason')) {
            $query->where('reason', $reason);
        }

        $returns = $query->paginate(20)->withQueryString();

        // Financial Stats (calculated for current filtered query without pagination)
        $statsQuery = SaleReturn::query();
        if ($from)   { $statsQuery->whereDate('returned_at', '>=', $from); }
        if ($to)     { $statsQuery->whereDate('returned_at', '<=', $to); }
        if ($method) { $statsQuery->where('refund_method', $method); }
        if ($reason) { $statsQuery->where('reason', $reason); }

        $stats = [
            'total_returns'      => $statsQuery->count(),
            'total_refund_amt'   => (float) (clone $statsQuery)->sum('total_return_amount'),
            'today_returns'      => SaleReturn::whereDate('returned_at', today())->count(),
            'today_refund_amt'   => (float) SaleReturn::whereDate('returned_at', today())->sum('total_return_amount'),
        ];

        return view('admin.sale-returns.index', compact('returns', 'stats'));
    }

    /**
     * Display the specified customer return details.
     */
    public function show(SaleReturn $saleReturn)
    {
        $saleReturn->load([
            'items.product',
            'sale.items',
            'employee',
            'user:id,name,role'
        ]);

        return view('admin.sale-returns.show', compact('saleReturn'));
    }
}

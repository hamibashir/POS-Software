<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'daily');

        $data = match($tab) {
            'daily'    => $this->dailySales($request),
            'range'    => $this->dateRange($request),
            'lowstock' => $this->lowStock($request),
            'topsell'  => $this->topSelling($request),
            default    => $this->dailySales($request),
        };

        return view('admin.reports.index', array_merge(['tab' => $tab], $data));
    }

    /* ── Daily Sales (last 30 days) ─────────────────────── */
    private function dailySales(Request $request): array
    {
        $days = (int) $request->get('days', 30);
        $days = in_array($days, [7, 14, 30, 60, 90]) ? $days : 30;

        $rows = DB::table('sales')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('SUM(paid_amount) as collected'),
                DB::raw('AVG(total_amount) as avg_sale')
            )
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderByDesc('date')
            ->get();

        $totals = [
            'transactions' => $rows->sum('transactions'),
            'revenue'      => $rows->sum('revenue'),
            'avg_sale'     => $rows->avg('avg_sale') ?? 0,
        ];

        return compact('rows', 'totals', 'days');
    }

    /* ── Date-range report ───────────────────────────────── */
    private function dateRange(Request $request): array
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $summary = DB::table('sales')
            ->select(
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('SUM(discount_amount) as discounts'),
                DB::raw('SUM(tax_amount) as taxes'),
                DB::raw('SUM(change_amount) as change_given'),
                DB::raw('AVG(total_amount) as avg_sale'),
                DB::raw("SUM(CASE WHEN payment_method='cash' THEN total_amount ELSE 0 END) as cash_revenue"),
                DB::raw("SUM(CASE WHEN payment_method='card' THEN total_amount ELSE 0 END) as card_revenue")
            )
            ->where('status', 'completed')
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->first();

        $byDay = DB::table('sales')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->where('status', 'completed')
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->groupBy('date')
            ->orderByDesc('date')
            ->get();

        return compact('summary', 'byDay', 'from', 'to');
    }

    /* ── Low stock report ────────────────────────────────── */
    private function lowStock(Request $request): array
    {
        $threshold = (int) $request->get('threshold', 10);
        $threshold = max(1, min(100, $threshold));

        $products = Product::with('category:id,name')
            ->where('is_active', true)
            ->where('stock_quantity', '<=', $threshold)
            ->orderBy('stock_quantity')
            ->get(['id', 'name', 'sku', 'unit', 'stock_quantity', 'category_id', 'cost_price', 'selling_price']);

        return compact('products', 'threshold');
    }

    /* ── Top selling products ────────────────────────────── */
    private function topSelling(Request $request): array
    {
        $from  = $request->get('from', now()->startOfMonth()->toDateString());
        $to    = $request->get('to',   now()->toDateString());
        $limit = (int) $request->get('limit', 10);
        $limit = in_array($limit, [5, 10, 20, 50]) ? $limit : 10;

        $products = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->select(
                'sale_items.product_id',
                'sale_items.product_name',
                'sale_items.product_sku',
                'sale_items.product_unit',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT sales.id) as order_count'),
                DB::raw('AVG(sale_items.unit_price) as avg_price')
            )
            ->where('sales.status', 'completed')
            ->whereBetween(DB::raw('DATE(sales.created_at)'), [$from, $to])
            ->groupBy('sale_items.product_id', 'sale_items.product_name', 'sale_items.product_sku', 'sale_items.product_unit')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();

        return compact('products', 'from', 'to', 'limit');
    }
}

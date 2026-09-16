<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
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

        $startDate = now()->subDays($days)->startOfDay();

        // 1. Direct Sales by Day (Cash, Card, and upfront paid portion on Credit sales)
        $salesData = DB::table('sales')
            ->leftJoin(DB::raw('(
                SELECT sale_id, SUM(quantity * cost_price) as cogs
                FROM sale_items
                GROUP BY sale_id
            ) as cogs_table'), 'cogs_table.sale_id', '=', 'sales.id')
            ->select(
                DB::raw('DATE(sales.created_at) as date'),
                DB::raw('COUNT(sales.id) as transactions'),
                DB::raw("SUM(CASE WHEN sales.payment_method != 'credit' THEN sales.total_amount ELSE sales.paid_amount END) as direct_revenue"),
                DB::raw('SUM(sales.paid_amount) as collected'),
                DB::raw('SUM(sales.discount_amount) as discounts'),
                DB::raw('AVG(sales.total_amount) as avg_sale'),
                DB::raw('SUM(COALESCE(cogs_table.cogs, 0)) as cogs')
            )
            ->where('sales.status', 'completed')
            ->where('sales.created_at', '>=', $startDate)
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // 2. Customer Credit Payments cleared on each day
        $creditClearedData = DB::table('employee_payments')
            ->select(
                DB::raw('DATE(COALESCE(payment_date, created_at)) as date'),
                DB::raw('SUM(amount) as credit_cleared'),
                DB::raw('COUNT(id) as payment_count')
            )
            ->where(DB::raw('DATE(COALESCE(payment_date, created_at))'), '>=', $startDate->toDateString())
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // 3. Operating Expenses by Day
        $expensesData = DB::table('expenses')
            ->select(
                DB::raw('DATE(expense_date) as date'),
                DB::raw('SUM(amount) as total_expense')
            )
            ->where('expense_date', '>=', $startDate->toDateString())
            ->groupBy('date')
            ->pluck('total_expense', 'date');

        // Merge all active dates
        $allDates = $salesData->keys()
            ->merge($creditClearedData->keys())
            ->merge($expensesData->keys())
            ->unique()
            ->sortDesc()
            ->values();

        $rows = $allDates->map(function ($date) use ($salesData, $creditClearedData, $expensesData) {
            $saleRow       = $salesData[$date] ?? null;
            $creditRow     = $creditClearedData[$date] ?? null;
            $directRevenue = $saleRow ? (float) $saleRow->direct_revenue : 0.0;
            $creditCleared = $creditRow ? (float) $creditRow->credit_cleared : 0.0;
            $revenue       = $directRevenue + $creditCleared;
            $transactions  = ($saleRow ? (int) $saleRow->transactions : 0) + ($creditRow ? (int) $creditRow->payment_count : 0);
            $cogs          = $saleRow ? (float) $saleRow->cogs : 0.0;
            $expense       = (float) ($expensesData[$date] ?? 0);
            $grossProfit   = $revenue - $cogs;
            $netProfit     = $grossProfit - $expense;
            $grossMargin   = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 1) : 0;
            $netMargin     = $revenue > 0 ? round(($netProfit / $revenue) * 100, 1) : 0;

            return (object) [
                'date'           => $date,
                'transactions'   => $transactions,
                'direct_revenue' => $directRevenue,
                'credit_cleared' => $creditCleared,
                'revenue'        => $revenue,
                'cogs'           => $cogs,
                'expense'        => $expense,
                'gross_profit'   => $grossProfit,
                'net_profit'     => $netProfit,
                'gross_margin'   => $grossMargin,
                'net_margin'     => $netMargin,
                'avg_sale'       => $saleRow ? (float) $saleRow->avg_sale : 0.0,
            ];
        })->filter(fn($r) => $r->revenue > 0 || $r->transactions > 0 || $r->expense > 0)->values();

        $totalRevenue     = (float) $rows->sum('revenue');
        $totalCogs        = (float) $rows->sum('cogs');
        $totalExpense     = (float) $rows->sum('expense');
        $totalGrossProfit = $totalRevenue - $totalCogs;
        $totalNetProfit   = $totalGrossProfit - $totalExpense;
        $totalGrossMargin = $totalRevenue > 0 ? round(($totalGrossProfit / $totalRevenue) * 100, 1) : 0;
        $totalNetMargin   = $totalRevenue > 0 ? round(($totalNetProfit / $totalRevenue) * 100, 1) : 0;

        $totals = [
            'transactions' => $rows->sum('transactions'),
            'revenue'      => $totalRevenue,
            'cogs'         => $totalCogs,
            'expense'      => $totalExpense,
            'gross_profit' => $totalGrossProfit,
            'net_profit'   => $totalNetProfit,
            'gross_margin' => $totalGrossMargin,
            'net_margin'   => $totalNetMargin,
            'avg_sale'     => $rows->where('avg_sale', '>', 0)->avg('avg_sale') ?? 0,
        ];

        return compact('rows', 'totals', 'days');
    }

    /* ── Date-range report ───────────────────────────────── */
    private function dateRange(Request $request): array
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $summary = DB::table('sales')
            ->leftJoin(DB::raw('(
                SELECT sale_id, SUM(quantity * cost_price) as cogs
                FROM sale_items
                GROUP BY sale_id
            ) as cogs_table'), 'cogs_table.sale_id', '=', 'sales.id')
            ->select(
                DB::raw('COUNT(sales.id) as transactions'),
                DB::raw("SUM(CASE WHEN sales.payment_method != 'credit' THEN sales.total_amount ELSE sales.paid_amount END) as direct_revenue"),
                DB::raw('SUM(sales.discount_amount) as discounts'),
                DB::raw('SUM(sales.tax_amount) as taxes'),
                DB::raw('SUM(sales.change_amount) as change_given'),
                DB::raw('AVG(sales.total_amount) as avg_sale'),
                DB::raw('SUM(COALESCE(cogs_table.cogs, 0)) as cogs'),
                DB::raw("SUM(CASE WHEN sales.payment_method='cash' THEN sales.total_amount ELSE 0 END) as cash_revenue"),
                DB::raw("SUM(CASE WHEN sales.payment_method='card' THEN sales.total_amount ELSE 0 END) as card_revenue"),
                DB::raw("SUM(CASE WHEN sales.payment_method='credit' THEN sales.paid_amount ELSE 0 END) as credit_upfront")
            )
            ->where('sales.status', 'completed')
            ->whereBetween(DB::raw('DATE(sales.created_at)'), [$from, $to])
            ->first();

        $creditClearedTotal = (float) DB::table('employee_payments')
            ->whereBetween(DB::raw('DATE(COALESCE(payment_date, created_at))'), [$from, $to])
            ->sum('amount');

        $creditClearedCount = (int) DB::table('employee_payments')
            ->whereBetween(DB::raw('DATE(COALESCE(payment_date, created_at))'), [$from, $to])
            ->count();

        $totalExpenses = (float) DB::table('expenses')
            ->whereBetween(DB::raw('DATE(expense_date)'), [$from, $to])
            ->sum('amount');

        if ($summary) {
            $directRevenue           = (float) ($summary->direct_revenue ?? 0);
            $summary->revenue        = $directRevenue + $creditClearedTotal;
            $summary->credit_revenue = (float) ($summary->credit_upfront ?? 0) + $creditClearedTotal;
            $summary->transactions   = (int) ($summary->transactions ?? 0) + $creditClearedCount;
            $summary->cogs           = (float) ($summary->cogs ?? 0);
            $summary->expense        = $totalExpenses;
            $summary->gross_profit   = (float) $summary->revenue - $summary->cogs;
            $summary->net_profit     = $summary->gross_profit - $totalExpenses;
            $summary->gross_margin   = $summary->revenue > 0 ? round(($summary->gross_profit / (float)$summary->revenue) * 100, 1) : 0;
            $summary->net_margin     = $summary->revenue > 0 ? round(($summary->net_profit / (float)$summary->revenue) * 100, 1) : 0;
        }

        $expensesByDay = DB::table('expenses')
            ->select(
                DB::raw('DATE(expense_date) as date'),
                DB::raw('SUM(amount) as total_expense')
            )
            ->whereBetween(DB::raw('DATE(expense_date)'), [$from, $to])
            ->groupBy('date')
            ->pluck('total_expense', 'date');

        $salesByDay = DB::table('sales')
            ->leftJoin(DB::raw('(
                SELECT sale_id, SUM(quantity * cost_price) as cogs
                FROM sale_items
                GROUP BY sale_id
            ) as cogs_table'), 'cogs_table.sale_id', '=', 'sales.id')
            ->select(
                DB::raw('DATE(sales.created_at) as date'),
                DB::raw('COUNT(sales.id) as transactions'),
                DB::raw("SUM(CASE WHEN sales.payment_method != 'credit' THEN sales.total_amount ELSE sales.paid_amount END) as direct_revenue"),
                DB::raw('SUM(COALESCE(cogs_table.cogs, 0)) as cogs')
            )
            ->where('sales.status', 'completed')
            ->whereBetween(DB::raw('DATE(sales.created_at)'), [$from, $to])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $creditClearedByDay = DB::table('employee_payments')
            ->select(
                DB::raw('DATE(COALESCE(payment_date, created_at)) as date'),
                DB::raw('SUM(amount) as credit_cleared'),
                DB::raw('COUNT(id) as payment_count')
            )
            ->whereBetween(DB::raw('DATE(COALESCE(payment_date, created_at))'), [$from, $to])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $allRangeDates = $salesByDay->keys()
            ->merge($creditClearedByDay->keys())
            ->merge($expensesByDay->keys())
            ->unique()
            ->sortDesc()
            ->values();

        $byDay = $allRangeDates->map(function ($date) use ($salesByDay, $creditClearedByDay, $expensesByDay) {
            $saleRow       = $salesByDay[$date] ?? null;
            $creditRow     = $creditClearedByDay[$date] ?? null;
            $directRevenue = $saleRow ? (float) $saleRow->direct_revenue : 0.0;
            $creditCleared = $creditRow ? (float) $creditRow->credit_cleared : 0.0;
            $revenue       = $directRevenue + $creditCleared;
            $transactions  = ($saleRow ? (int) $saleRow->transactions : 0) + ($creditRow ? (int) $creditRow->payment_count : 0);
            $cogs          = $saleRow ? (float) $saleRow->cogs : 0.0;
            $expense       = (float) ($expensesByDay[$date] ?? 0);
            $gross         = $revenue - $cogs;
            $net           = $gross - $expense;

            return (object) [
                'date'         => $date,
                'transactions' => $transactions,
                'revenue'      => $revenue,
                'cogs'         => $cogs,
                'expense'      => $expense,
                'gross_profit' => $gross,
                'net_profit'   => $net,
                'gross_margin' => $revenue > 0 ? round(($gross / $revenue) * 100, 1) : 0,
                'net_margin'   => $revenue > 0 ? round(($net / $revenue) * 100, 1) : 0,
            ];
        })->filter(fn($r) => $r->revenue > 0 || $r->transactions > 0 || $r->expense > 0)->values();

        return compact('summary', 'byDay', 'from', 'to', 'totalExpenses');
    }

    /* ── Low stock report ────────────────────────────────── */
    private function lowStock(Request $request): array
    {
        $threshold   = $request->filled('threshold') ? max(1, min(500, (int) $request->get('threshold'))) : 10;
        $categoryId  = $request->get('category_id');
        $stockFilter = $request->get('stock_filter', 'all'); // 'all', 'low', 'out', 'threshold'
        $search      = $request->get('search');

        $categories = Category::orderBy('name')->get();

        $query = Product::with('category:id,name')
            ->where('is_active', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($stockFilter === 'out') {
            $query->where('stock_quantity', '<=', 0);
        } elseif ($stockFilter === 'low') {
            $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                  ->where('stock_quantity', '>', 0);
        } elseif ($stockFilter === 'threshold') {
            $query->where('stock_quantity', '<=', $threshold);
        } else {
            // Default 'all': items at or below low_stock_threshold OR stock_quantity <= 0 OR below threshold
            $query->where(function ($q) use ($threshold) {
                $q->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                  ->orWhere('stock_quantity', '<=', $threshold);
            });
        }

        $products = $query->orderBy('stock_quantity', 'asc')
            ->get(['id', 'name', 'sku', 'barcode', 'unit', 'stock_quantity', 'low_stock_threshold', 'category_id', 'cost_price', 'sale_price']);

        $outOfStockCount = Product::active()->where('stock_quantity', '<=', 0)->count();
        $lowStockCount   = Product::active()->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->where('stock_quantity', '>', 0)->count();

        return compact('products', 'threshold', 'categories', 'categoryId', 'stockFilter', 'search', 'outOfStockCount', 'lowStockCount');
    }

    /* ── Top selling products ────────────────────────────── */
    private function topSelling(Request $request): array
    {
        $from       = $request->get('from', now()->startOfMonth()->toDateString());
        $to         = $request->get('to',   now()->toDateString());
        $limit      = (int) $request->get('limit', 10);
        $limit      = in_array($limit, [5, 10, 20, 50, 100]) ? $limit : 10;
        $categoryId = $request->get('category_id');

        $categories = Category::orderBy('name')->get();

        // 1. Overall top selling products (filtered by category if selected)
        $products = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->select(
                'sale_items.product_id',
                'sale_items.product_name',
                'sale_items.product_sku',
                'sale_items.product_unit',
                DB::raw("COALESCE(categories.name, 'Uncategorized') as category_name"),
                'categories.id as category_id',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * sale_items.cost_price) as total_cost'),
                DB::raw('SUM(sale_items.total_price) - SUM(sale_items.quantity * sale_items.cost_price) as gross_profit'),
                DB::raw('COUNT(DISTINCT sales.id) as order_count'),
                DB::raw('AVG(sale_items.unit_price) as avg_price'),
                DB::raw('AVG(sale_items.cost_price) as avg_cost')
            )
            ->where('sales.status', 'completed')
            ->whereBetween(DB::raw('DATE(sales.created_at)'), [$from, $to])
            ->when($categoryId, fn($q) => $q->where('products.category_id', $categoryId))
            ->groupBy(
                'sale_items.product_id',
                'sale_items.product_name',
                'sale_items.product_sku',
                'sale_items.product_unit',
                'categories.id',
                'categories.name'
            )
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get()
            ->map(function ($p) {
                $rev = (float) $p->total_revenue;
                $profit = (float) $p->gross_profit;
                $p->profit_margin = $rev > 0 ? round(($profit / $rev) * 100, 1) : 0;
                return $p;
            });

        // 2. Category-wise Performance Breakdown Summary
        $categoryBreakdown = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->select(
                DB::raw("COALESCE(categories.name, 'Uncategorized') as category_name"),
                'categories.id as category_id',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * sale_items.cost_price) as total_cost'),
                DB::raw('SUM(sale_items.total_price) - SUM(sale_items.quantity * sale_items.cost_price) as gross_profit'),
                DB::raw('COUNT(DISTINCT sales.id) as order_count'),
                DB::raw('COUNT(DISTINCT sale_items.product_id) as total_products_sold')
            )
            ->where('sales.status', 'completed')
            ->whereBetween(DB::raw('DATE(sales.created_at)'), [$from, $to])
            ->when($categoryId, fn($q) => $q->where('products.category_id', $categoryId))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_qty')
            ->get()
            ->map(function ($c) {
                $rev = (float) $c->total_revenue;
                $profit = (float) $c->gross_profit;
                $c->profit_margin = $rev > 0 ? round(($profit / $rev) * 100, 1) : 0;
                return $c;
            });

        // 3. Category-wise Top Products Grouping
        $categoryWiseProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->select(
                DB::raw("COALESCE(categories.name, 'Uncategorized') as category_name"),
                'categories.id as category_id',
                'sale_items.product_id',
                'sale_items.product_name',
                'sale_items.product_sku',
                'sale_items.product_unit',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * sale_items.cost_price) as total_cost'),
                DB::raw('SUM(sale_items.total_price) - SUM(sale_items.quantity * sale_items.cost_price) as gross_profit'),
                DB::raw('COUNT(DISTINCT sales.id) as order_count'),
                DB::raw('AVG(sale_items.unit_price) as avg_price')
            )
            ->where('sales.status', 'completed')
            ->whereBetween(DB::raw('DATE(sales.created_at)'), [$from, $to])
            ->when($categoryId, fn($q) => $q->where('products.category_id', $categoryId))
            ->groupBy(
                'categories.id',
                'categories.name',
                'sale_items.product_id',
                'sale_items.product_name',
                'sale_items.product_sku',
                'sale_items.product_unit'
            )
            ->orderByDesc('total_qty')
            ->get()
            ->map(function ($p) {
                $rev = (float) $p->total_revenue;
                $profit = (float) $p->gross_profit;
                $p->profit_margin = $rev > 0 ? round(($profit / $rev) * 100, 1) : 0;
                return $p;
            })
            ->groupBy('category_name');

        return compact('products', 'from', 'to', 'limit', 'categories', 'categoryId', 'categoryBreakdown', 'categoryWiseProducts');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'daily');

        $data = match($tab) {
            'daily'                  => $this->dailySales($request),
            'range'                  => $this->dateRange($request),
            'stock', 'inventory'     => $this->stockValuation($request),
            'lowstock'               => $this->lowStock($request),
            'topsell'                => $this->topSelling($request),
            'suppliers'              => $this->supplierPayments($request),
            default                  => $this->dailySales($request),
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

        // 3. Customer Product Returns by Day
        $returnsData = DB::table('sale_returns')
            ->leftJoin(DB::raw('(
                SELECT sale_return_id, SUM(sale_return_items.quantity * products.cost_price) as returned_cogs
                FROM sale_return_items
                JOIN products ON products.id = sale_return_items.product_id
                GROUP BY sale_return_id
            ) as ret_cogs'), 'ret_cogs.sale_return_id', '=', 'sale_returns.id')
            ->select(
                DB::raw('DATE(COALESCE(sale_returns.returned_at, sale_returns.created_at)) as date'),
                DB::raw('SUM(sale_returns.total_return_amount) as total_returns'),
                DB::raw('SUM(COALESCE(ret_cogs.returned_cogs, 0)) as returned_cogs'),
                DB::raw('COUNT(sale_returns.id) as return_count')
            )
            ->where(DB::raw('DATE(COALESCE(sale_returns.returned_at, sale_returns.created_at))'), '>=', $startDate->toDateString())
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // 4. Operating Expenses by Day
        $expensesData = DB::table('expenses')
            ->select(
                DB::raw('DATE(expense_date) as date'),
                DB::raw('SUM(amount) as total_expense')
            )
            ->where('expense_date', '>=', $startDate->toDateString())
            ->groupBy('date')
            ->pluck('total_expense', 'date');

        // 5. Supplier Payments by Day (Cash outflow paid to suppliers)
        $supplierPaymentsData = DB::table('supplier_payments')
            ->select(
                DB::raw('DATE(COALESCE(payment_date, created_at)) as date'),
                DB::raw('SUM(amount) as supplier_paid'),
                DB::raw('COUNT(id) as payment_count')
            )
            ->where(DB::raw('DATE(COALESCE(payment_date, created_at))'), '>=', $startDate->toDateString())
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Merge all active dates
        $allDates = $salesData->keys()
            ->merge($creditClearedData->keys())
            ->merge($returnsData->keys())
            ->merge($expensesData->keys())
            ->merge($supplierPaymentsData->keys())
            ->unique()
            ->sortDesc()
            ->values();

        $rows = $allDates->map(function ($date) use ($salesData, $creditClearedData, $returnsData, $expensesData, $supplierPaymentsData) {
            $saleRow       = $salesData[$date] ?? null;
            $creditRow     = $creditClearedData[$date] ?? null;
            $returnRow     = $returnsData[$date] ?? null;
            $supplierRow   = $supplierPaymentsData[$date] ?? null;

            $directRevenue = $saleRow ? (float) $saleRow->direct_revenue : 0.0;
            $creditCleared = $creditRow ? (float) $creditRow->credit_cleared : 0.0;
            $returnedAmt   = $returnRow ? (float) $returnRow->total_returns : 0.0;
            $returnedCogs  = $returnRow ? (float) $returnRow->returned_cogs : 0.0;
            $returnCount   = $returnRow ? (int) $returnRow->return_count : 0;
            $supplierPaid  = $supplierRow ? (float) $supplierRow->supplier_paid : 0.0;
            $supplierCount = $supplierRow ? (int) $supplierRow->payment_count : 0;

            $grossRevenue  = $directRevenue + $creditCleared;
            $revenue       = $grossRevenue - $returnedAmt;
            $transactions  = ($saleRow ? (int) $saleRow->transactions : 0) + ($creditRow ? (int) $creditRow->payment_count : 0) + $returnCount;
            $grossCogs     = $saleRow ? (float) $saleRow->cogs : 0.0;
            $cogs          = max(0, $grossCogs - $returnedCogs);
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
                'returns'        => $returnedAmt,
                'returned_cogs'  => $returnedCogs,
                'revenue'        => $revenue,
                'cogs'           => $cogs,
                'expense'        => $expense,
                'supplier_paid'  => $supplierPaid,
                'supplier_count' => $supplierCount,
                'gross_profit'   => $grossProfit,
                'net_profit'     => $netProfit,
                'gross_margin'   => $grossMargin,
                'net_margin'     => $netMargin,
                'avg_sale'       => $saleRow ? (float) $saleRow->avg_sale : 0.0,
            ];
        })->filter(fn($r) => $r->revenue > 0 || $r->transactions > 0 || $r->expense > 0 || $r->returns > 0 || $r->supplier_paid > 0)->values();

        $totalRevenue      = (float) $rows->sum('revenue');
        $totalReturns      = (float) $rows->sum('returns');
        $totalCogs         = (float) $rows->sum('cogs');
        $totalExpense      = (float) $rows->sum('expense');
        $totalSupplierPaid = (float) $rows->sum('supplier_paid');
        $totalGrossProfit  = $totalRevenue - $totalCogs;
        $totalNetProfit    = $totalGrossProfit - $totalExpense;
        $totalGrossMargin  = $totalRevenue > 0 ? round(($totalGrossProfit / $totalRevenue) * 100, 1) : 0;
        $totalNetMargin    = $totalRevenue > 0 ? round(($totalNetProfit / $totalRevenue) * 100, 1) : 0;

        $totals = [
            'transactions'  => $rows->sum('transactions'),
            'revenue'       => $totalRevenue,
            'returns'       => $totalReturns,
            'cogs'          => $totalCogs,
            'expense'       => $totalExpense,
            'supplier_paid' => $totalSupplierPaid,
            'gross_profit'  => $totalGrossProfit,
            'net_profit'    => $totalNetProfit,
            'gross_margin'  => $totalGrossMargin,
            'net_margin'    => $totalNetMargin,
            'avg_sale'      => $rows->where('avg_sale', '>', 0)->avg('avg_sale') ?? 0,
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

        $returnsTotal = (float) DB::table('sale_returns')
            ->whereBetween(DB::raw('DATE(COALESCE(returned_at, created_at))'), [$from, $to])
            ->sum('total_return_amount');

        $returnsCount = (int) DB::table('sale_returns')
            ->whereBetween(DB::raw('DATE(COALESCE(returned_at, created_at))'), [$from, $to])
            ->count();

        $returnedCogsTotal = (float) DB::table('sale_return_items')
            ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
            ->join('products', 'products.id', '=', 'sale_return_items.product_id')
            ->whereBetween(DB::raw('DATE(COALESCE(sale_returns.returned_at, sale_returns.created_at))'), [$from, $to])
            ->sum(DB::raw('sale_return_items.quantity * products.cost_price'));

        $totalExpenses = (float) DB::table('expenses')
            ->whereBetween(DB::raw('DATE(expense_date)'), [$from, $to])
            ->sum('amount');

        $supplierPaidTotal = (float) DB::table('supplier_payments')
            ->whereBetween(DB::raw('DATE(COALESCE(payment_date, created_at))'), [$from, $to])
            ->sum('amount');

        $supplierPaidCount = (int) DB::table('supplier_payments')
            ->whereBetween(DB::raw('DATE(COALESCE(payment_date, created_at))'), [$from, $to])
            ->count();

        if ($summary) {
            $directRevenue               = (float) ($summary->direct_revenue ?? 0);
            $summary->returns            = $returnsTotal;
            $summary->revenue            = ($directRevenue + $creditClearedTotal) - $returnsTotal;
            $summary->credit_revenue     = (float) ($summary->credit_upfront ?? 0) + $creditClearedTotal;
            $summary->transactions       = (int) ($summary->transactions ?? 0) + $creditClearedCount + $returnsCount;
            $grossCogs                   = (float) ($summary->cogs ?? 0);
            $summary->cogs               = max(0, $grossCogs - $returnedCogsTotal);
            $summary->expense            = $totalExpenses;
            $summary->supplier_paid      = $supplierPaidTotal;
            $summary->supplier_paid_cnt  = $supplierPaidCount;
            $summary->gross_profit       = (float) $summary->revenue - $summary->cogs;
            $summary->net_profit         = $summary->gross_profit - $totalExpenses;
            $summary->gross_margin       = $summary->revenue > 0 ? round(($summary->gross_profit / (float)$summary->revenue) * 100, 1) : 0;
            $summary->net_margin         = $summary->revenue > 0 ? round(($summary->net_profit / (float)$summary->revenue) * 100, 1) : 0;
        }

        $expensesByDay = DB::table('expenses')
            ->select(
                DB::raw('DATE(expense_date) as date'),
                DB::raw('SUM(amount) as total_expense')
            )
            ->whereBetween(DB::raw('DATE(expense_date)'), [$from, $to])
            ->groupBy('date')
            ->pluck('total_expense', 'date');

        $supplierPaymentsByDay = DB::table('supplier_payments')
            ->select(
                DB::raw('DATE(COALESCE(payment_date, created_at)) as date'),
                DB::raw('SUM(amount) as supplier_paid'),
                DB::raw('COUNT(id) as payment_count')
            )
            ->whereBetween(DB::raw('DATE(COALESCE(payment_date, created_at))'), [$from, $to])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

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

        $returnsByDay = DB::table('sale_returns')
            ->leftJoin(DB::raw('(
                SELECT sale_return_id, SUM(sale_return_items.quantity * products.cost_price) as returned_cogs
                FROM sale_return_items
                JOIN products ON products.id = sale_return_items.product_id
                GROUP BY sale_return_id
            ) as ret_cogs'), 'ret_cogs.sale_return_id', '=', 'sale_returns.id')
            ->select(
                DB::raw('DATE(COALESCE(sale_returns.returned_at, sale_returns.created_at)) as date'),
                DB::raw('SUM(sale_returns.total_return_amount) as total_returns'),
                DB::raw('SUM(COALESCE(ret_cogs.returned_cogs, 0)) as returned_cogs'),
                DB::raw('COUNT(sale_returns.id) as return_count')
            )
            ->whereBetween(DB::raw('DATE(COALESCE(sale_returns.returned_at, sale_returns.created_at))'), [$from, $to])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $allRangeDates = $salesByDay->keys()
            ->merge($creditClearedByDay->keys())
            ->merge($returnsByDay->keys())
            ->merge($expensesByDay->keys())
            ->merge($supplierPaymentsByDay->keys())
            ->unique()
            ->sortDesc()
            ->values();

        $byDay = $allRangeDates->map(function ($date) use ($salesByDay, $creditClearedByDay, $returnsByDay, $expensesByDay, $supplierPaymentsByDay) {
            $saleRow       = $salesByDay[$date] ?? null;
            $creditRow     = $creditClearedByDay[$date] ?? null;
            $returnRow     = $returnsByDay[$date] ?? null;
            $supplierRow   = $supplierPaymentsByDay[$date] ?? null;

            $directRevenue = $saleRow ? (float) $saleRow->direct_revenue : 0.0;
            $creditCleared = $creditRow ? (float) $creditRow->credit_cleared : 0.0;
            $returnedAmt   = $returnRow ? (float) $returnRow->total_returns : 0.0;
            $returnedCogs  = $returnRow ? (float) $returnRow->returned_cogs : 0.0;
            $returnCount   = $returnRow ? (int) $returnRow->return_count : 0;
            $supplierPaid  = $supplierRow ? (float) $supplierRow->supplier_paid : 0.0;
            $supplierCount = $supplierRow ? (int) $supplierRow->payment_count : 0;

            $revenue       = ($directRevenue + $creditCleared) - $returnedAmt;
            $transactions  = ($saleRow ? (int) $saleRow->transactions : 0) + ($creditRow ? (int) $creditRow->payment_count : 0) + $returnCount;
            $grossCogs     = $saleRow ? (float) $saleRow->cogs : 0.0;
            $cogs          = max(0, $grossCogs - $returnedCogs);
            $expense       = (float) ($expensesByDay[$date] ?? 0);
            $gross         = $revenue - $cogs;
            $net           = $gross - $expense;

            return (object) [
                'date'           => $date,
                'transactions'   => $transactions,
                'returns'        => $returnedAmt,
                'revenue'        => $revenue,
                'cogs'           => $cogs,
                'expense'        => $expense,
                'supplier_paid'  => $supplierPaid,
                'supplier_count' => $supplierCount,
                'gross_profit'   => $gross,
                'net_profit'     => $net,
                'gross_margin'   => $revenue > 0 ? round(($gross / $revenue) * 100, 1) : 0,
                'net_margin'     => $revenue > 0 ? round(($net / $revenue) * 100, 1) : 0,
            ];
        })->filter(fn($r) => $r->revenue > 0 || $r->transactions > 0 || $r->expense > 0 || $r->returns > 0 || $r->supplier_paid > 0)->values();

        return compact('summary', 'byDay', 'from', 'to', 'totalExpenses', 'supplierPaidTotal');
    }

    /* ── Stock Valuation & Total Available Inventory ──────── */
    private function stockValuation(Request $request): array
    {
        $categoryId  = $request->get('category_id');
        $supplierId  = $request->get('supplier_id');
        $stockFilter = $request->get('stock_filter', 'all'); // 'all', 'in_stock', 'low', 'out', 'negative'
        $search      = $request->get('search');
        $sortBy      = $request->get('sort', 'cost_value_desc'); // 'cost_value_desc', 'sale_value_desc', 'stock_desc', 'stock_asc', 'name_asc'
        $perPage     = (int) $request->get('per_page', 50);
        $perPage     = in_array($perPage, [25, 50, 100, 250, 500]) ? $perPage : 50;

        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::orderBy('name')->get();

        // 1. Overall Shop Stock Metrics (across all active products in the shop)
        $overallStats = Product::active()
            ->selectRaw("
                COUNT(id) as total_products,
                SUM(stock_quantity) as total_units_raw,
                SUM(CASE WHEN stock_quantity > 0 THEN stock_quantity ELSE 0 END) as total_available_units,
                SUM(CASE WHEN stock_quantity > 0 THEN stock_quantity * cost_price ELSE 0 END) as total_cost_value,
                SUM(CASE WHEN stock_quantity > 0 THEN stock_quantity * sale_price ELSE 0 END) as total_retail_value,
                SUM(CASE WHEN stock_quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock_count,
                SUM(CASE WHEN stock_quantity > 0 AND stock_quantity <= low_stock_threshold THEN 1 ELSE 0 END) as low_stock_count,
                SUM(CASE WHEN stock_quantity < 0 THEN 1 ELSE 0 END) as negative_stock_count
            ")
            ->first();

        $shopCostValue       = (float) ($overallStats->total_cost_value ?? 0);
        $shopRetailValue     = (float) ($overallStats->total_retail_value ?? 0);
        $shopProjectedProfit = max(0, $shopRetailValue - $shopCostValue);
        $shopProfitMargin    = $shopRetailValue > 0 ? round(($shopProjectedProfit / $shopRetailValue) * 100, 1) : 0;

        // 2. Category-wise Stock Breakdown
        $categoryBreakdown = DB::table('categories')
            ->leftJoin('products', function ($join) {
                $join->on('products.category_id', '=', 'categories.id')
                     ->where('products.is_active', true)
                     ->whereNull('products.deleted_at');
            })
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('COUNT(products.id) as product_count'),
                DB::raw('SUM(CASE WHEN products.stock_quantity > 0 THEN products.stock_quantity ELSE 0 END) as total_stock'),
                DB::raw('SUM(CASE WHEN products.stock_quantity > 0 THEN products.stock_quantity * products.cost_price ELSE 0 END) as cost_value'),
                DB::raw('SUM(CASE WHEN products.stock_quantity > 0 THEN products.stock_quantity * products.sale_price ELSE 0 END) as retail_value')
            )
            ->groupBy('categories.id', 'categories.name')
            ->having('product_count', '>', 0)
            ->orderByDesc('cost_value')
            ->get()
            ->map(function ($cat) use ($shopCostValue) {
                $cat->cost_value   = (float) $cat->cost_value;
                $cat->retail_value = (float) $cat->retail_value;
                $cat->profit       = max(0, $cat->retail_value - $cat->cost_value);
                $cat->margin       = $cat->retail_value > 0 ? round(($cat->profit / $cat->retail_value) * 100, 1) : 0;
                $cat->share_pct    = $shopCostValue > 0 ? round(($cat->cost_value / $shopCostValue) * 100, 1) : 0;
                return $cat;
            });

        // 3. Filtered Product Query for detailed list & pagination
        $query = Product::with(['category:id,name', 'supplier:id,name'])
            ->where('is_active', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($stockFilter === 'in_stock') {
            $query->where('stock_quantity', '>', 0);
        } elseif ($stockFilter === 'low') {
            $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                  ->where('stock_quantity', '>', 0);
        } elseif ($stockFilter === 'out') {
            $query->where('stock_quantity', '<=', 0);
        } elseif ($stockFilter === 'negative') {
            $query->where('stock_quantity', '<', 0);
        }

        // Filtered totals
        $filteredTotals = (clone $query)->selectRaw("
            COUNT(id) as count,
            SUM(CASE WHEN stock_quantity > 0 THEN stock_quantity ELSE 0 END) as units,
            SUM(CASE WHEN stock_quantity > 0 THEN stock_quantity * cost_price ELSE 0 END) as cost_value,
            SUM(CASE WHEN stock_quantity > 0 THEN stock_quantity * sale_price ELSE 0 END) as retail_value
        ")->first();

        // Apply Sorting
        match($sortBy) {
            'cost_value_desc' => $query->orderByRaw('(stock_quantity * cost_price) DESC'),
            'sale_value_desc' => $query->orderByRaw('(stock_quantity * sale_price) DESC'),
            'stock_desc'      => $query->orderBy('stock_quantity', 'desc'),
            'stock_asc'       => $query->orderBy('stock_quantity', 'asc'),
            'name_asc'        => $query->orderBy('name', 'asc'),
            default           => $query->orderByRaw('(stock_quantity * cost_price) DESC'),
        };

        $products = $query->paginate($perPage)->withQueryString();

        return compact(
            'overallStats',
            'shopCostValue',
            'shopRetailValue',
            'shopProjectedProfit',
            'shopProfitMargin',
            'categoryBreakdown',
            'products',
            'filteredTotals',
            'categories',
            'suppliers',
            'categoryId',
            'supplierId',
            'stockFilter',
            'search',
            'sortBy',
            'perPage'
        );
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

        // Overall shop stock summary
        $shopStockStats = Product::active()
            ->selectRaw("
                SUM(CASE WHEN stock_quantity > 0 THEN stock_quantity ELSE 0 END) as total_units,
                SUM(CASE WHEN stock_quantity > 0 THEN stock_quantity * cost_price ELSE 0 END) as total_cost_value,
                SUM(CASE WHEN stock_quantity > 0 THEN stock_quantity * sale_price ELSE 0 END) as total_retail_value
            ")
            ->first();

        $shopTotalUnits  = (float) ($shopStockStats->total_units ?? 0);
        $shopCostValue   = (float) ($shopStockStats->total_cost_value ?? 0);
        $shopRetailValue = (float) ($shopStockStats->total_retail_value ?? 0);

        return compact(
            'products', 'threshold', 'categories', 'categoryId', 'stockFilter',
            'search', 'outOfStockCount', 'lowStockCount',
            'shopTotalUnits', 'shopCostValue', 'shopRetailValue'
        );
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

        // Query product-level returned totals in date range
        $returnedProducts = DB::table('sale_return_items')
            ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
            ->select(
                'sale_return_items.product_id',
                DB::raw('SUM(sale_return_items.quantity) as returned_qty'),
                DB::raw('SUM(sale_return_items.total_price) as returned_revenue')
            )
            ->whereBetween(DB::raw('DATE(COALESCE(sale_returns.returned_at, sale_returns.created_at))'), [$from, $to])
            ->groupBy('sale_return_items.product_id')
            ->get()
            ->keyBy('product_id');

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
                DB::raw('SUM(sale_items.quantity) as gross_qty'),
                DB::raw('SUM(sale_items.total_price) as gross_revenue'),
                DB::raw('SUM(sale_items.quantity * sale_items.cost_price) as gross_cost'),
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
            ->get()
            ->map(function ($p) use ($returnedProducts) {
                $ret = $returnedProducts[$p->product_id] ?? null;
                $returnedQty = $ret ? (float)$ret->returned_qty : 0.0;
                $returnedRev = $ret ? (float)$ret->returned_revenue : 0.0;

                $p->total_qty     = max(0.0, (float)$p->gross_qty - $returnedQty);
                $p->total_revenue = max(0.0, (float)$p->gross_revenue - $returnedRev);
                $costPerUnit      = (float)$p->avg_cost;
                $p->total_cost    = $p->total_qty * $costPerUnit;
                $p->gross_profit  = $p->total_revenue - $p->total_cost;
                $p->profit_margin = $p->total_revenue > 0 ? round(($p->gross_profit / $p->total_revenue) * 100, 1) : 0;
                return $p;
            })
            ->sortByDesc('total_qty')
            ->take($limit)
            ->values();

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

    /* ── Supplier Payments & Purchases Report ─────────────── */
    private function supplierPayments(Request $request): array
    {
        $from       = $request->get('from', now()->startOfMonth()->toDateString());
        $to         = $request->get('to',   now()->toDateString());
        $supplierId = $request->filled('supplier_id') ? (int) $request->get('supplier_id') : null;
        $method     = $request->get('payment_method');

        $suppliersList = \App\Models\Supplier::orderBy('name')->get();

        // 1. Summary Stats in Date Range
        $purchasesQuery = DB::table('purchases')
            ->whereBetween(DB::raw('DATE(COALESCE(received_at, created_at))'), [$from, $to]);
        if ($supplierId) {
            $purchasesQuery->where('supplier_id', $supplierId);
        }
        $totalPurchases = (float) $purchasesQuery->sum('total_amount');
        $purchaseCount  = (int) $purchasesQuery->count();

        $paymentsQuery = DB::table('supplier_payments')
            ->whereBetween(DB::raw('DATE(COALESCE(payment_date, created_at))'), [$from, $to]);
        if ($supplierId) {
            $paymentsQuery->where('supplier_id', $supplierId);
        }
        if ($method) {
            $paymentsQuery->where('payment_method', $method);
        }
        $totalPaidAmount = (float) $paymentsQuery->sum('amount');
        $paymentCount    = (int) $paymentsQuery->count();

        $returnsQuery = DB::table('purchase_returns')
            ->whereBetween(DB::raw('DATE(COALESCE(returned_at, created_at))'), [$from, $to]);
        if ($supplierId) {
            $returnsQuery->where('supplier_id', $supplierId);
        }
        $totalReturns = (float) $returnsQuery->sum('total_return_amount');

        // All active suppliers pending balance overall
        $allSuppliers = \App\Models\Supplier::with(['purchases', 'returns', 'payments'])->get();
        $totalOutstandingPayables = (float) $allSuppliers->sum(fn($s) => $s->pending_balance);

        // 2. Supplier-wise Breakdown in Period
        $supplierBreakdown = $allSuppliers
            ->when($supplierId, fn($c) => $c->where('id', $supplierId))
            ->map(function ($s) use ($from, $to) {
                $periodPurchases = (float) $s->purchases()
                    ->whereBetween(DB::raw('DATE(COALESCE(received_at, created_at))'), [$from, $to])
                    ->sum('total_amount');

                $periodPaid = (float) $s->payments()
                    ->whereBetween(DB::raw('DATE(COALESCE(payment_date, created_at))'), [$from, $to])
                    ->sum('amount');

                $periodReturns = (float) $s->returns()
                    ->whereBetween(DB::raw('DATE(COALESCE(returned_at, created_at))'), [$from, $to])
                    ->sum('total_return_amount');

                $lastPayment = $s->payments()->first(); // latest payment due to model relationship ordering

                return (object) [
                    'id'                => $s->id,
                    'name'              => $s->name,
                    'company_name'      => $s->company_name,
                    'phone'             => $s->phone,
                    'opening_balance'   => (float) $s->opening_balance,
                    'period_purchases'  => $periodPurchases,
                    'period_paid'       => $periodPaid,
                    'period_returns'    => $periodReturns,
                    'pending_balance'   => $s->pending_balance,
                    'total_paid_all'    => $s->total_paid,
                    'last_payment_date' => $lastPayment?->payment_date,
                    'last_payment_amt'  => $lastPayment?->amount,
                ];
            })
            ->filter(fn($s) => $s->period_purchases > 0 || $s->period_paid > 0 || $s->period_returns > 0 || $s->pending_balance > 0)
            ->sortByDesc('period_paid')
            ->values();

        // 3. Payment Transactions Log in Period
        $paymentLogs = \App\Models\SupplierPayment::with(['supplier', 'user', 'purchase'])
            ->whereBetween(DB::raw('DATE(COALESCE(payment_date, created_at))'), [$from, $to])
            ->when($supplierId, fn($q) => $q->where('supplier_id', $supplierId))
            ->when($method, fn($q) => $q->where('payment_method', $method))
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();

        // 4. Payment Method Breakdown (Cash, Bank, Cheque, Online)
        $methodBreakdown = DB::table('supplier_payments')
            ->select('payment_method', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(id) as count'))
            ->whereBetween(DB::raw('DATE(COALESCE(payment_date, created_at))'), [$from, $to])
            ->when($supplierId, fn($q) => $q->where('supplier_id', $supplierId))
            ->groupBy('payment_method')
            ->get();

        return compact(
            'from', 'to', 'supplierId', 'method', 'suppliersList',
            'totalPurchases', 'purchaseCount', 'totalPaidAmount', 'paymentCount',
            'totalReturns', 'totalOutstandingPayables', 'supplierBreakdown',
            'paymentLogs', 'methodBreakdown'
        );
    }
}

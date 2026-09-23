<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // ── KPI Cards: Today ──────────────────────────────────────
        $todayDirectSales = (float) Sale::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum(DB::raw("CASE WHEN payment_method != 'credit' THEN total_amount ELSE paid_amount END"));

        $todayCreditCleared = (float) DB::table('employee_payments')
            ->whereDate(DB::raw('COALESCE(payment_date, created_at)'), $today)
            ->sum('amount');

        $todayReturns = (float) DB::table('sale_returns')
            ->whereDate(DB::raw('COALESCE(returned_at, created_at)'), $today)
            ->sum('total_return_amount');

        $todaySales = ($todayDirectSales + $todayCreditCleared) - $todayReturns;

        $todayGrossCogs = (float) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereDate('sales.created_at', $today)
            ->sum(DB::raw('sale_items.quantity * sale_items.cost_price'));

        $todayReturnedCogs = (float) DB::table('sale_return_items')
            ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
            ->join('products', 'products.id', '=', 'sale_return_items.product_id')
            ->whereDate(DB::raw('COALESCE(sale_returns.returned_at, sale_returns.created_at)'), $today)
            ->sum(DB::raw('sale_return_items.quantity * products.cost_price'));

        $todayCogs = max(0, $todayGrossCogs - $todayReturnedCogs);

        $todayExpenses = (float) Expense::whereDate('expense_date', $today)->sum('amount');
        $todayGrossProfit = $todaySales - $todayCogs;
        $todayNetProfit   = $todayGrossProfit - $todayExpenses;
        $todayMargin      = $todaySales > 0 ? round(($todayGrossProfit / $todaySales) * 100, 1) : 0;
        $todayNetMargin   = $todaySales > 0 ? round(($todayNetProfit / $todaySales) * 100, 1) : 0;

        $thisMonthExpenses = (float) Expense::whereMonth('expense_date', $today->month)
            ->whereYear('expense_date', $today->year)
            ->sum('amount');

        // ── Yesterday ─────────────────────────────────────────────
        $yesterday = $today->copy()->subDay();
        $yesterdayDirectSales = (float) Sale::whereDate('created_at', $yesterday)
            ->where('status', 'completed')
            ->sum(DB::raw("CASE WHEN payment_method != 'credit' THEN total_amount ELSE paid_amount END"));
        $yesterdayCreditCleared = (float) DB::table('employee_payments')
            ->whereDate(DB::raw('COALESCE(payment_date, created_at)'), $yesterday)
            ->sum('amount');
        $yesterdayReturns = (float) DB::table('sale_returns')
            ->whereDate(DB::raw('COALESCE(returned_at, created_at)'), $yesterday)
            ->sum('total_return_amount');

        $yesterdaySales = ($yesterdayDirectSales + $yesterdayCreditCleared) - $yesterdayReturns;

        $yesterdayGrossCogs = (float) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereDate('sales.created_at', $yesterday)
            ->sum(DB::raw('sale_items.quantity * sale_items.cost_price'));

        $yesterdayReturnedCogs = (float) DB::table('sale_return_items')
            ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
            ->join('products', 'products.id', '=', 'sale_return_items.product_id')
            ->whereDate(DB::raw('COALESCE(sale_returns.returned_at, sale_returns.created_at)'), $yesterday)
            ->sum(DB::raw('sale_return_items.quantity * products.cost_price'));

        $yesterdayCogs = max(0, $yesterdayGrossCogs - $yesterdayReturnedCogs);
        $yesterdayExpenses = (float) Expense::whereDate('expense_date', $yesterday)->sum('amount');
        $yesterdayGrossProfit = $yesterdaySales - $yesterdayCogs;
        $yesterdayNetProfit = $yesterdayGrossProfit - $yesterdayExpenses;

        $todayVsYesterday = $yesterdaySales > 0
            ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100)
            : null;
        $profitVsYesterday = $yesterdayNetProfit > 0
            ? round((($todayNetProfit - $yesterdayNetProfit) / $yesterdayNetProfit) * 100)
            : null;

        $totalProducts = Product::active()->count();

        $lowStockCount = Product::active()
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0)
            ->count();

        // ── This Month ────────────────────────────────────────────
        $thisMonthDirectRevenue = (float) Sale::whereMonth('created_at', $today->month)
            ->whereYear('created_at', $today->year)
            ->where('status', 'completed')
            ->sum(DB::raw("CASE WHEN payment_method != 'credit' THEN total_amount ELSE paid_amount END"));
        $thisMonthCreditCleared = (float) DB::table('employee_payments')
            ->whereMonth(DB::raw('COALESCE(payment_date, created_at)'), $today->month)
            ->whereYear(DB::raw('COALESCE(payment_date, created_at)'), $today->year)
            ->sum('amount');
        $thisMonthReturns = (float) DB::table('sale_returns')
            ->whereMonth(DB::raw('COALESCE(returned_at, created_at)'), $today->month)
            ->whereYear(DB::raw('COALESCE(returned_at, created_at)'), $today->year)
            ->sum('total_return_amount');

        $thisMonthRevenue = ($thisMonthDirectRevenue + $thisMonthCreditCleared) - $thisMonthReturns;

        $thisMonthGrossCogs = (float) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereMonth('sales.created_at', $today->month)
            ->whereYear('sales.created_at', $today->year)
            ->sum(DB::raw('sale_items.quantity * sale_items.cost_price'));

        $thisMonthReturnedCogs = (float) DB::table('sale_return_items')
            ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
            ->join('products', 'products.id', '=', 'sale_return_items.product_id')
            ->whereMonth(DB::raw('COALESCE(sale_returns.returned_at, sale_returns.created_at)'), $today->month)
            ->whereYear(DB::raw('COALESCE(sale_returns.returned_at, sale_returns.created_at)'), $today->year)
            ->sum(DB::raw('sale_return_items.quantity * products.cost_price'));

        $thisMonthCogs = max(0, $thisMonthGrossCogs - $thisMonthReturnedCogs);

        $thisMonthGrossProfit = $thisMonthRevenue - $thisMonthCogs;
        $thisMonthNetProfit   = $thisMonthGrossProfit - $thisMonthExpenses;
        $thisMonthMargin      = $thisMonthRevenue > 0 ? round(($thisMonthGrossProfit / $thisMonthRevenue) * 100, 1) : 0;
        $thisMonthNetMargin   = $thisMonthRevenue > 0 ? round(($thisMonthNetProfit / $thisMonthRevenue) * 100, 1) : 0;

        // ── Last Month ────────────────────────────────────────────
        $lastMonth = $today->copy()->subMonth();
        $lastMonthDirectRevenue = (float) Sale::whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->where('status', 'completed')
            ->sum(DB::raw("CASE WHEN payment_method != 'credit' THEN total_amount ELSE paid_amount END"));
        $lastMonthCreditCleared = (float) DB::table('employee_payments')
            ->whereMonth(DB::raw('COALESCE(payment_date, created_at)'), $lastMonth->month)
            ->whereYear(DB::raw('COALESCE(payment_date, created_at)'), $lastMonth->year)
            ->sum('amount');
        $lastMonthReturns = (float) DB::table('sale_returns')
            ->whereMonth(DB::raw('COALESCE(returned_at, created_at)'), $lastMonth->month)
            ->whereYear(DB::raw('COALESCE(returned_at, created_at)'), $lastMonth->year)
            ->sum('total_return_amount');

        $lastMonthRevenue = ($lastMonthDirectRevenue + $lastMonthCreditCleared) - $lastMonthReturns;

        $lastMonthGrossCogs = (float) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereMonth('sales.created_at', $lastMonth->month)
            ->whereYear('sales.created_at', $lastMonth->year)
            ->sum(DB::raw('sale_items.quantity * sale_items.cost_price'));

        $lastMonthReturnedCogs = (float) DB::table('sale_return_items')
            ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
            ->join('products', 'products.id', '=', 'sale_return_items.product_id')
            ->whereMonth(DB::raw('COALESCE(sale_returns.returned_at, sale_returns.created_at)'), $lastMonth->month)
            ->whereYear(DB::raw('COALESCE(sale_returns.returned_at, sale_returns.created_at)'), $lastMonth->year)
            ->sum(DB::raw('sale_return_items.quantity * products.cost_price'));

        $lastMonthCogs = max(0, $lastMonthGrossCogs - $lastMonthReturnedCogs);
        $lastMonthExpenses = (float) Expense::whereMonth('expense_date', $lastMonth->month)
            ->whereYear('expense_date', $lastMonth->year)
            ->sum('amount');
        $lastMonthGrossProfit = $lastMonthRevenue - $lastMonthCogs;
        $lastMonthNetProfit = $lastMonthGrossProfit - $lastMonthExpenses;

        $monthVsLast = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100)
            : null;
        $monthProfitVsLast = $lastMonthNetProfit > 0
            ? round((($thisMonthNetProfit - $lastMonthNetProfit) / $lastMonthNetProfit) * 100)
            : null;

        // ── 7-Day Sales Chart ──────────────────────────────────────
        $last7Days = collect(range(6, 0))->map(fn($i) => $today->copy()->subDays($i));

        $salesByDay = Sale::selectRaw("DATE(created_at) as date, SUM(CASE WHEN payment_method != 'credit' THEN total_amount ELSE paid_amount END) as total")
            ->whereBetween('created_at', [
                $today->copy()->subDays(6)->startOfDay(),
                $today->copy()->endOfDay(),
            ])
            ->where('status', 'completed')
            ->groupBy('date')
            ->pluck('total', 'date');

        $creditClearedByDay = DB::table('employee_payments')
            ->selectRaw("DATE(COALESCE(payment_date, created_at)) as date, SUM(amount) as total")
            ->whereBetween(DB::raw('DATE(COALESCE(payment_date, created_at))'), [
                $today->copy()->subDays(6)->toDateString(),
                $today->toDateString(),
            ])
            ->groupBy('date')
            ->pluck('total', 'date');

        $returnsByDay = DB::table('sale_returns')
            ->selectRaw("DATE(COALESCE(returned_at, created_at)) as date, SUM(total_return_amount) as total")
            ->whereBetween(DB::raw('DATE(COALESCE(returned_at, created_at))'), [
                $today->copy()->subDays(6)->toDateString(),
                $today->toDateString(),
            ])
            ->groupBy('date')
            ->pluck('total', 'date');

        $chartLabels = $last7Days->map(fn($d) => $d->format('D'))->values();
        $chartData   = $last7Days->map(function ($d) use ($salesByDay, $creditClearedByDay, $returnsByDay) {
            $dateStr = $d->toDateString();
            $direct = (float) ($salesByDay[$dateStr] ?? 0);
            $credit = (float) ($creditClearedByDay[$dateStr] ?? 0);
            $ret    = (float) ($returnsByDay[$dateStr] ?? 0);
            return max(0, ($direct + $credit) - $ret);
        })->values();

        $weekTotal = $chartData->sum();

        $prevWeekDirect = (float) Sale::whereBetween('created_at', [
            $today->copy()->subDays(13)->startOfDay(),
            $today->copy()->subDays(7)->endOfDay(),
        ])->where('status', 'completed')
          ->sum(DB::raw("CASE WHEN payment_method != 'credit' THEN total_amount ELSE paid_amount END"));

        $prevWeekCredit = (float) DB::table('employee_payments')
            ->whereBetween(DB::raw('DATE(COALESCE(payment_date, created_at))'), [
                $today->copy()->subDays(13)->toDateString(),
                $today->copy()->subDays(7)->toDateString(),
            ])->sum('amount');

        $prevWeekReturns = (float) DB::table('sale_returns')
            ->whereBetween(DB::raw('DATE(COALESCE(returned_at, created_at))'), [
                $today->copy()->subDays(13)->toDateString(),
                $today->copy()->subDays(7)->toDateString(),
            ])->sum('total_return_amount');

        $prevWeekTotal = max(0, ($prevWeekDirect + $prevWeekCredit) - $prevWeekReturns);

        $weekVsPrev = $prevWeekTotal > 0
            ? round((($weekTotal - $prevWeekTotal) / $prevWeekTotal) * 100)
            : null;

        // ── Low Stock Products ─────────────────────────────────────
        $lowStockProducts = Product::active()
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity')
            ->limit(6)
            ->get();

        return view('admin.dashboard', compact(
            'todaySales', 'todayVsYesterday',
            'todayExpenses', 'todayCogs', 'todayGrossProfit', 'todayNetProfit', 'todayMargin', 'todayNetMargin', 'profitVsYesterday',
            'thisMonthExpenses', 'thisMonthCogs', 'thisMonthGrossProfit', 'thisMonthNetProfit', 'thisMonthMargin', 'thisMonthNetMargin', 'monthProfitVsLast',
            'totalProducts',
            'lowStockCount',
            'thisMonthRevenue', 'monthVsLast',
            'chartLabels', 'chartData', 'weekTotal', 'weekVsPrev',
            'lowStockProducts'
        ));
    }
}

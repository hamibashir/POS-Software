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

        // ── KPI Cards ─────────────────────────────────────────────
        $todaySales = Sale::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total_amount');

        $todayCogs = (float) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereDate('sales.created_at', $today)
            ->sum(DB::raw('sale_items.quantity * sale_items.cost_price'));

        $todayExpenses = (float) Expense::whereDate('expense_date', $today)->sum('amount');
        $todayGrossProfit = $todaySales - $todayCogs;
        $todayNetProfit   = $todayGrossProfit - $todayExpenses;
        $todayMargin      = $todaySales > 0 ? round(($todayGrossProfit / $todaySales) * 100, 1) : 0;
        $todayNetMargin   = $todaySales > 0 ? round(($todayNetProfit / $todaySales) * 100, 1) : 0;

        $thisMonthExpenses = (float) Expense::whereMonth('expense_date', $today->month)
            ->whereYear('expense_date', $today->year)
            ->sum('amount');

        $yesterdaySales = Sale::whereDate('created_at', $today->copy()->subDay())
            ->where('status', 'completed')
            ->sum('total_amount');
        $yesterdayCogs = (float) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereDate('sales.created_at', $today->copy()->subDay())
            ->sum(DB::raw('sale_items.quantity * sale_items.cost_price'));
        $yesterdayExpenses = (float) Expense::whereDate('expense_date', $today->copy()->subDay())->sum('amount');
        $yesterdayNetProfit = ($yesterdaySales - $yesterdayCogs) - $yesterdayExpenses;

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

        $thisMonthRevenue = Sale::whereMonth('created_at', $today->month)
            ->whereYear('created_at', $today->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        $thisMonthCogs = (float) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereMonth('sales.created_at', $today->month)
            ->whereYear('sales.created_at', $today->year)
            ->sum(DB::raw('sale_items.quantity * sale_items.cost_price'));

        $thisMonthGrossProfit = $thisMonthRevenue - $thisMonthCogs;
        $thisMonthNetProfit   = $thisMonthGrossProfit - $thisMonthExpenses;
        $thisMonthMargin      = $thisMonthRevenue > 0 ? round(($thisMonthGrossProfit / $thisMonthRevenue) * 100, 1) : 0;
        $thisMonthNetMargin   = $thisMonthRevenue > 0 ? round(($thisMonthNetProfit / $thisMonthRevenue) * 100, 1) : 0;

        $lastMonthRevenue = Sale::whereMonth('created_at', $today->copy()->subMonth()->month)
            ->whereYear('created_at', $today->copy()->subMonth()->year)
            ->where('status', 'completed')
            ->sum('total_amount');
        $lastMonthCogs = (float) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereMonth('sales.created_at', $today->copy()->subMonth()->month)
            ->whereYear('sales.created_at', $today->copy()->subMonth()->year)
            ->sum(DB::raw('sale_items.quantity * sale_items.cost_price'));
        $lastMonthExpenses = (float) Expense::whereMonth('expense_date', $today->copy()->subMonth()->month)
            ->whereYear('expense_date', $today->copy()->subMonth()->year)
            ->sum('amount');
        $lastMonthNetProfit = ($lastMonthRevenue - $lastMonthCogs) - $lastMonthExpenses;

        $monthVsLast = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100)
            : null;
        $monthProfitVsLast = $lastMonthNetProfit > 0
            ? round((($thisMonthNetProfit - $lastMonthNetProfit) / $lastMonthNetProfit) * 100)
            : null;

        // ── 7-Day Sales Chart ──────────────────────────────────────
        $last7Days = collect(range(6, 0))->map(fn($i) => $today->copy()->subDays($i));

        $salesByDay = Sale::selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->whereBetween('created_at', [
                $today->copy()->subDays(6)->startOfDay(),
                $today->copy()->endOfDay(),
            ])
            ->where('status', 'completed')
            ->groupBy('date')
            ->pluck('total', 'date');

        $chartLabels = $last7Days->map(fn($d) => $d->format('D'))->values();
        $chartData   = $last7Days->map(fn($d) => (float) ($salesByDay[$d->toDateString()] ?? 0))->values();

        $weekTotal = $chartData->sum();
        $prevWeekTotal = Sale::whereBetween('created_at', [
            $today->copy()->subDays(13)->startOfDay(),
            $today->copy()->subDays(7)->endOfDay(),
        ])->where('status', 'completed')->sum('total_amount');
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

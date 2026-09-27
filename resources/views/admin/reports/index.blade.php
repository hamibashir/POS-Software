@extends('layouts.admin')
@section('title', 'Reports')

@push('styles')
<style>
    .tab-nav { display:flex; gap:4px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:6px; margin-bottom:20px; }
    .tab-btn {
        flex:1; text-align:center; padding:9px 12px; border-radius:8px; font-size:13px;
        font-weight:600; color:#6b7280; text-decoration:none; transition:all .15s;
        display:flex; align-items:center; justify-content:center; gap:6px;
    }
    .tab-btn.active { background:var(--pos-primary); color:#fff; }
    .tab-btn:not(.active):hover { background:#f3f4f6; color:#111827; }

    /* Stat cards */
    .stat-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:14px; margin-bottom:20px; }
    .stat-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px 18px; }
    .stat-label { font-size:11px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.7px; }
    .stat-value { font-size:22px; font-weight:800; color:#111827; margin-top:4px; }
    .stat-sub   { font-size:11px; color:#9ca3af; margin-top:2px; }

    /* Report card */
    .report-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; margin-bottom:16px; }
    .report-card-header {
        background:#f9fafb; border-bottom:1px solid #e5e7eb; padding:12px 18px;
        font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:1px;
        display:flex; align-items:center; justify-content:space-between;
    }

    /* Filter bar */
    .rfilter { display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; margin-bottom:18px; }
    .rfilter .fg { display:flex; flex-direction:column; gap:4px; }
    .rfilter label { font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.6px; }

    /* Data table */
    .rpt-table { width:100%; border-collapse:collapse; }
    .rpt-table thead th { background:#f9fafb; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#6b7280; padding:10px 16px; border-bottom:1px solid #e5e7eb; }
    .rpt-table tbody td { padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f3f4f6; }
    .rpt-table tbody tr:last-child td { border-bottom:none; }
    .rpt-table tbody tr:hover { background:#fafafa; }

    /* Stock level bars */
    .stock-bar-wrap { display:flex; align-items:center; gap:8px; }
    .stock-bar { height:6px; border-radius:3px; flex:1; background:#fee2e2; }
    .stock-bar .fill { height:100%; border-radius:3px; background:#10b981; }

    /* rank badge */
    .rank { display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:50%; font-size:12px; font-weight:800; }
    .rank-1 { background:#fef9c3; color:#854d0e; }
    .rank-2 { background:#f3f4f6; color:#374151; }
    .rank-3 { background:#fef3c7; color:#92400e; }
    .rank-n { background:#f3f4f6; color:#9ca3af; }
</style>
@endpush

@section('content')

<div class="page-hero">
    <h1><i class="bi bi-bar-chart-line me-2" style="color:var(--pos-primary)"></i>Reports</h1>
    <p>Sales analytics, stock levels, and product performance.</p>
</div>

{{-- Tab navigation --}}
<div class="tab-nav">
    <a href="{{ route('admin.reports.index', ['tab'=>'daily']) }}"
       class="tab-btn {{ $tab==='daily' ? 'active' : '' }}">
        <i class="bi bi-calendar3"></i> Daily Sales
    </a>
    <a href="{{ route('admin.reports.index', ['tab'=>'range']) }}"
       class="tab-btn {{ $tab==='range' ? 'active' : '' }}">
        <i class="bi bi-calendar-range"></i> Date Range
    </a>
    <a href="{{ route('admin.reports.index', ['tab'=>'stock']) }}"
       class="tab-btn {{ in_array($tab, ['stock', 'inventory']) ? 'active' : '' }}">
        <i class="bi bi-boxes"></i> Stock & Shop Valuation
    </a>
    <a href="{{ route('admin.reports.index', ['tab'=>'lowstock']) }}"
       class="tab-btn {{ $tab==='lowstock' ? 'active' : '' }}">
        <i class="bi bi-exclamation-triangle"></i> Low Stock
    </a>
    <a href="{{ route('admin.reports.index', ['tab'=>'topsell']) }}"
       class="tab-btn {{ $tab==='topsell' ? 'active' : '' }}">
        <i class="bi bi-trophy"></i> Top Selling
    </a>
    <a href="{{ route('admin.reports.index', ['tab'=>'suppliers']) }}"
       class="tab-btn {{ $tab==='suppliers' ? 'active' : '' }}">
        <i class="bi bi-truck"></i> Supplier Payments
    </a>
</div>

{{-- ════════════════════════════════════════════════════════
     TAB 1 — DAILY SALES
═════════════════════════════════════════════════════════ --}}
@if($tab === 'daily')

    <form method="GET" action="{{ route('admin.reports.index') }}" class="rfilter">
        <input type="hidden" name="tab" value="daily">
        <div class="fg">
            <label>Period</label>
            <select name="days" class="pos-input" onchange="this.form.submit()" style="width:160px;">
                @foreach([7=>'Last 7 days',14=>'Last 14 days',30=>'Last 30 days',60=>'Last 60 days',90=>'Last 90 days'] as $val=>$lbl)
                    <option value="{{ $val }}" {{ $days==$val ? 'selected':'' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="stat-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr));">
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value" style="color:#0f766e;">{{ pkr($totals['revenue'], 2) }}</div>
            <div class="stat-sub">{{ number_format($totals['transactions']) }} transactions</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Cost of Goods (COGS)</div>
            <div class="stat-value" style="color:#475569;">{{ pkr($totals['cogs'], 2) }}</div>
            <div class="stat-sub">product acquisition cost</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Operating Expenses</div>
            <div class="stat-value text-danger">{{ pkr($totals['expense'], 2) }}</div>
            <div class="stat-sub">recorded daily expenses</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #059669;">
            <div class="stat-label" style="color:#059669;">Supplier Paid Amount</div>
            <div class="stat-value" style="color:#059669;">{{ pkr($totals['supplier_paid'] ?? 0, 2) }}</div>
            <div class="stat-sub">vendor payment clearances</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #0d9488;">
            <div class="stat-label" style="color:#0f766e;">Gross Profit</div>
            <div class="stat-value" style="color:{{ $totals['gross_profit'] >= 0 ? '#0d9488' : '#dc2626' }};">
                {{ pkr($totals['gross_profit'], 2) }}
            </div>
            <div class="stat-sub">{{ $totals['gross_margin'] }}% Gross Margin</div>
        </div>
        <div class="stat-card" style="border-left:4px solid {{ $totals['net_profit'] >= 0 ? '#10b981' : '#ef4444' }};">
            <div class="stat-label" style="color:{{ $totals['net_profit'] >= 0 ? '#047857' : '#b91c1c' }};">Net Profit</div>
            <div class="stat-value" style="color:{{ $totals['net_profit'] >= 0 ? '#059669' : '#dc2626' }};">
                {{ pkr($totals['net_profit'], 2) }}
            </div>
            <div class="stat-sub">{{ $totals['net_margin'] }}% Net Margin</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Avg Sale & Days</div>
            <div class="stat-value" style="font-size:18px;">{{ pkr($totals['avg_sale'], 2) }}</div>
            <div class="stat-sub">{{ $rows->count() }} active sales days</div>
        </div>
    </div>

    <div class="report-card">
        <div class="report-card-header">
            <span><i class="bi bi-calendar-check me-1 text-primary"></i> Daily Sales & Profit Breakdown</span>
            <span>{{ $rows->count() }} days</span>
        </div>
        @if($rows->isEmpty())
            <div style="padding:40px;text-align:center;color:#9ca3af;">No sales in this period.</div>
        @else
        <div style="overflow-x:auto;">
            <table class="rpt-table">
                <thead><tr>
                    <th>Date</th>
                    <th style="text-align:right">Txns</th>
                    <th style="text-align:right">Gross (Sales+Credit)</th>
                    <th style="text-align:right">Returns / Refunds</th>
                    <th style="text-align:right">Net Revenue</th>
                    <th style="text-align:right">COGS</th>
                    <th style="text-align:right">Expenses</th>
                    <th style="text-align:right">Supplier Paid</th>
                    <th style="text-align:right">Gross Profit</th>
                    <th style="text-align:right">Net Profit</th>
                    <th style="text-align:right">Net Margin</th>
                </tr></thead>
                <tbody>
                    @foreach($rows as $row)
                    <tr>
                        <td style="font-weight:600;">{{ \Carbon\Carbon::parse($row->date)->format('D, d M Y') }}</td>
                        <td style="text-align:right;color:#6b7280;">{{ $row->transactions }}</td>
                        <td style="text-align:right;color:#64748b;">{{ pkr($row->direct_revenue + $row->credit_cleared, 2) }}</td>
                        <td style="text-align:right;color:#b91c1c;font-weight:600;">{{ $row->returns > 0 ? '-' . pkr($row->returns, 2) : '—' }}</td>
                        <td style="text-align:right;font-weight:700;color:#0f766e;">{{ pkr($row->revenue, 2) }}</td>
                        <td style="text-align:right;color:#64748b;">{{ pkr($row->cogs, 2) }}</td>
                        <td style="text-align:right;color:#ef4444;">{{ $row->expense > 0 ? pkr($row->expense, 2) : '—' }}</td>
                        <td style="text-align:right;font-weight:600;color:#059669;">
                            @if(($row->supplier_paid ?? 0) > 0)
                                {{ pkr($row->supplier_paid, 2) }}
                                <div class="text-muted" style="font-size:10.5px;">{{ $row->supplier_count ?? 1 }} payment(s)</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="text-align:right;font-weight:700;color:{{ $row->gross_profit >= 0 ? '#0d9488' : '#dc2626' }};">
                            {{ pkr($row->gross_profit, 2) }}
                        </td>
                        <td style="text-align:right;font-weight:800;color:{{ $row->net_profit >= 0 ? '#059669' : '#dc2626' }};">
                            {{ pkr($row->net_profit, 2) }}
                        </td>
                        <td style="text-align:right;">
                            <span class="badge {{ $row->net_margin >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border" style="font-size:11px; font-weight:700;">
                                {{ $row->net_margin }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

{{-- ════════════════════════════════════════════════════════
     TAB 2 — DATE RANGE
═════════════════════════════════════════════════════════ --}}
@elseif($tab === 'range')

    <form method="GET" action="{{ route('admin.reports.index') }}" class="rfilter">
        <input type="hidden" name="tab" value="range">
        <div class="fg">
            <label>From</label>
            <input type="date" name="from" class="pos-input" value="{{ $from }}" style="width:150px;">
        </div>
        <div class="fg">
            <label>To</label>
            <input type="date" name="to" class="pos-input" value="{{ $to }}" style="width:150px;">
        </div>
        <div class="fg">
            <label>&nbsp;</label>
            <button type="submit" class="btn-pos"><i class="bi bi-funnel"></i> Apply</button>
        </div>
    </form>

    @if($summary)
    <div class="stat-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr));">
        <div class="stat-card">
            <div class="stat-label">Net Revenue</div>
            <div class="stat-value" style="color:#0f766e;">{{ pkr($summary->revenue,2) }}</div>
            <div class="stat-sub">{{ number_format($summary->transactions) }} transactions</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Customer Returns</div>
            <div class="stat-value text-danger">-{{ pkr($summary->returns ?? 0, 2) }}</div>
            <div class="stat-sub">deducted from gross revenue</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Cost of Goods (COGS)</div>
            <div class="stat-value" style="color:#475569;">{{ pkr($summary->cogs ?? 0,2) }}</div>
            <div class="stat-sub">net acquisition cost</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Operating Expenses</div>
            <div class="stat-value text-danger">{{ pkr($summary->expense ?? 0,2) }}</div>
            <div class="stat-sub">period operating costs</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #059669;">
            <div class="stat-label" style="color:#059669;">Supplier Paid Amount</div>
            <div class="stat-value" style="color:#059669;">{{ pkr($summary->supplier_paid ?? 0, 2) }}</div>
            <div class="stat-sub">{{ number_format($summary->supplier_paid_cnt ?? 0) }} payment clearances</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #0d9488;">
            <div class="stat-label" style="color:#0f766e;">Gross Profit</div>
            <div class="stat-value" style="color:{{ ($summary->gross_profit ?? 0) >= 0 ? '#0d9488' : '#dc2626' }};">
                {{ pkr($summary->gross_profit ?? 0,2) }}
            </div>
            <div class="stat-sub">{{ $summary->gross_margin ?? 0 }}% Gross Margin</div>
        </div>
        <div class="stat-card" style="border-left:4px solid {{ ($summary->net_profit ?? 0) >= 0 ? '#10b981' : '#ef4444' }};">
            <div class="stat-label" style="color:{{ ($summary->net_profit ?? 0) >= 0 ? '#047857' : '#b91c1c' }};">Net Profit</div>
            <div class="stat-value" style="color:{{ ($summary->net_profit ?? 0) >= 0 ? '#059669' : '#dc2626' }};">
                {{ pkr($summary->net_profit ?? 0,2) }}
            </div>
            <div class="stat-sub">{{ $summary->net_margin ?? 0 }}% Net Margin</div>
        </div>
    </div>
    @endif

    <div class="report-card">
        <div class="report-card-header">
            <span><i class="bi bi-calendar3-range me-1 text-primary"></i> Day-by-Day Sales & Profit Breakdown</span>
            <span>{{ $byDay->count() }} days</span>
        </div>
        @if($byDay->isEmpty())
            <div style="padding:40px;text-align:center;color:#9ca3af;">No sales in this date range.</div>
        @else
        <div style="overflow-x:auto;">
            <table class="rpt-table">
                <thead><tr>
                    <th>Date</th>
                    <th style="text-align:right">Txns</th>
                    <th style="text-align:right">Returns</th>
                    <th style="text-align:right">Net Revenue</th>
                    <th style="text-align:right">COGS</th>
                    <th style="text-align:right">Expenses</th>
                    <th style="text-align:right">Supplier Paid</th>
                    <th style="text-align:right">Gross Profit</th>
                    <th style="text-align:right">Net Profit</th>
                    <th style="text-align:right">Margin</th>
                </tr></thead>
                <tbody>
                    @foreach($byDay as $row)
                    <tr>
                        <td style="font-weight:600;">{{ \Carbon\Carbon::parse($row->date)->format('D, d M Y') }}</td>
                        <td style="text-align:right;color:#6b7280;">{{ $row->transactions }}</td>
                        <td style="text-align:right;color:#b91c1c;font-weight:600;">{{ $row->returns > 0 ? '-' . pkr($row->returns, 2) : '—' }}</td>
                        <td style="text-align:right;font-weight:700;color:#0f766e;">{{ pkr($row->revenue,2) }}</td>
                        <td style="text-align:right;color:#64748b;">{{ pkr($row->cogs,2) }}</td>
                        <td style="text-align:right;color:#ef4444;">{{ $row->expense > 0 ? pkr($row->expense,2) : '—' }}</td>
                        <td style="text-align:right;font-weight:600;color:#059669;">
                            @if(($row->supplier_paid ?? 0) > 0)
                                {{ pkr($row->supplier_paid, 2) }}
                                <div class="text-muted" style="font-size:10.5px;">{{ $row->supplier_count ?? 1 }} payment(s)</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="text-align:right;font-weight:700;color:{{ $row->gross_profit >= 0 ? '#0d9488' : '#dc2626' }};">
                            {{ pkr($row->gross_profit,2) }}
                        </td>
                        <td style="text-align:right;font-weight:800;color:{{ $row->net_profit >= 0 ? '#059669' : '#dc2626' }};">
                            {{ pkr($row->net_profit,2) }}
                        </td>
                        <td style="text-align:right;">
                            <span class="badge {{ $row->net_margin >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border" style="font-size:11px; font-weight:700;">
                                {{ $row->net_margin }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

{{-- ════════════════════════════════════════════════════════
     TAB 3 — STOCK VALUATION & TOTAL INVENTORY
═════════════════════════════════════════════════════════ --}}
@elseif($tab === 'stock' || $tab === 'inventory')

    {{-- Shop Stock Summary Stat Cards --}}
    <div class="stat-grid" style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); margin-bottom: 20px;">
        <div class="stat-card" style="border-left: 4px solid var(--pos-primary); background: linear-gradient(135deg, #ffffff 0%, #f0fdfa 100%);">
            <div class="stat-label" style="color: var(--pos-primary);"><i class="bi bi-box-seam me-1"></i> Total Available Stock</div>
            <div class="stat-value" style="color: var(--pos-primary);">{{ number_format($overallStats->total_available_units ?? 0) }} <span style="font-size:13px; font-weight:600; color:#64748b;">Units</span></div>
            <div class="stat-sub">{{ number_format($overallStats->total_products ?? 0) }} active products in inventory</div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #0284c7; background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);">
            <div class="stat-label" style="color: #0284c7;"><i class="bi bi-cash-stack me-1"></i> Shop Cost Valuation</div>
            <div class="stat-value" style="color: #0369a1;">{{ pkr($shopCostValue, 2) }}</div>
            <div class="stat-sub">Capital invested in available stock</div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #059669; background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);">
            <div class="stat-label" style="color: #059669;"><i class="bi bi-tag me-1"></i> Shop Retail Valuation</div>
            <div class="stat-value" style="color: #047857;">{{ pkr($shopRetailValue, 2) }}</div>
            <div class="stat-sub">Expected revenue at selling prices</div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #8b5cf6; background: linear-gradient(135deg, #ffffff 0%, #faf5ff 100%);">
            <div class="stat-label" style="color: #7c3aed;"><i class="bi bi-graph-up-arrow me-1"></i> Projected Stock Profit</div>
            <div class="stat-value" style="color: #6d28d9;">{{ pkr($shopProjectedProfit, 2) }}</div>
            <div class="stat-sub">Avg Margin: <strong style="color:#6d28d9;">{{ $shopProfitMargin }}%</strong></div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #f59e0b;">
            <div class="stat-label" style="color: #d97706;"><i class="bi bi-shield-exclamation me-1"></i> Inventory Alerts</div>
            <div class="stat-value" style="font-size: 18px; margin-top:6px;">
                <span class="badge bg-danger text-white me-1">{{ $overallStats->out_of_stock_count ?? 0 }} Out</span>
                <span class="badge bg-warning text-dark">{{ $overallStats->low_stock_count ?? 0 }} Low</span>
            </div>
            <div class="stat-sub" style="margin-top:6px;">Needs purchase replenishment</div>
        </div>
    </div>

    {{-- Category-wise Valuation Breakdown --}}
    @if(isset($categoryBreakdown) && $categoryBreakdown->isNotEmpty())
    <div class="report-card mb-4">
        <div class="report-card-header">
            <span><i class="bi bi-pie-chart-fill me-2" style="color:var(--pos-primary);"></i> Category-wise Stock Valuation Breakdown</span>
            <span style="font-size:12px; font-weight:600; color:#64748b;">{{ $categoryBreakdown->count() }} Categories</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th style="text-align:center;">Products / SKUs</th>
                        <th style="text-align:right;">Available Units</th>
                        <th style="text-align:right;">Total Cost Value</th>
                        <th style="text-align:right;">Total Retail Value</th>
                        <th style="text-align:right;">Projected Profit</th>
                        <th style="text-align:center;">Margin %</th>
                        <th style="text-align:center; width:130px;">Valuation Share</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totCatProducts = 0;
                        $totCatUnits = 0;
                        $totCatCost = 0;
                        $totCatRetail = 0;
                        $totCatProfit = 0;
                    @endphp
                    @foreach($categoryBreakdown as $cat)
                    @php
                        $totCatProducts += $cat->product_count;
                        $totCatUnits += $cat->total_stock;
                        $totCatCost += $cat->cost_value;
                        $totCatRetail += $cat->retail_value;
                        $totCatProfit += $cat->profit;
                    @endphp
                    <tr>
                        <td>
                            <strong style="color:#1e293b;">{{ $cat->name }}</strong>
                        </td>
                        <td style="text-align:center; color:#64748b; font-weight:600;">
                            {{ $cat->product_count }}
                        </td>
                        <td style="text-align:right; font-weight:700; color:#0f172a;">
                            {{ number_format($cat->total_stock) }}
                        </td>
                        <td style="text-align:right; font-weight:700; color:#0369a1;">
                            {{ pkr($cat->cost_value, 2) }}
                        </td>
                        <td style="text-align:right; font-weight:700; color:#047857;">
                            {{ pkr($cat->retail_value, 2) }}
                        </td>
                        <td style="text-align:right; font-weight:700; color:#6d28d9;">
                            {{ pkr($cat->profit, 2) }}
                        </td>
                        <td style="text-align:center;">
                            <span class="badge" style="background:#f3e8ff; color:#6b21a8; font-weight:700; font-size:11px;">
                                {{ $cat->margin }}%
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:6px; background:#e2e8f0; border-radius:3px;">
                                    <div class="progress-bar" style="width:{{ min(100, $cat->share_pct) }}%; background:var(--pos-primary); border-radius:3px;"></div>
                                </div>
                                <span style="font-size:11px; font-weight:700; color:#64748b; width:36px; text-align:right;">{{ $cat->share_pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f8fafc; font-weight:800; border-top:2px solid #cbd5e1;">
                        <td style="color:#0f172a;">TOTAL SUMMARY</td>
                        <td style="text-align:center; color:#0f172a;">{{ number_format($totCatProducts) }}</td>
                        <td style="text-align:right; color:#0f172a;">{{ number_format($totCatUnits) }}</td>
                        <td style="text-align:right; color:#0369a1;">{{ pkr($totCatCost, 2) }}</td>
                        <td style="text-align:right; color:#047857;">{{ pkr($totCatRetail, 2) }}</td>
                        <td style="text-align:right; color:#6d28d9;">{{ pkr($totCatProfit, 2) }}</td>
                        <td style="text-align:center;">
                            <span class="badge" style="background:#dbeafe; color:#1e40af; font-size:11px;">
                                {{ $totCatRetail > 0 ? round(($totCatProfit / $totCatRetail) * 100, 1) : 0 }}%
                            </span>
                        </td>
                        <td style="text-align:center; color:#64748b; font-size:11px;">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    {{-- Filter Bar for Product Inventory Details --}}
    <form method="GET" action="{{ route('admin.reports.index') }}" class="rfilter p-3 rounded-3 mb-3" style="background:#ffffff; border:1px solid #e2e8f0;">
        <input type="hidden" name="tab" value="stock">

        <div class="fg" style="min-width:200px; flex:1;">
            <label>Search Product / SKU / Barcode</label>
            <input type="text" name="search" class="pos-input" value="{{ $search }}" placeholder="Search name, SKU, barcode...">
        </div>

        <div class="fg" style="min-width:180px;">
            <label>Category</label>
            <select name="category_id" class="pos-input searchable-select" placeholder="All Categories / Search...">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="fg" style="min-width:180px;">
            <label>Supplier</label>
            <select name="supplier_id" class="pos-input searchable-select" placeholder="All Suppliers / Search...">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ (string)$supplierId === (string)$sup->id ? 'selected' : '' }}>
                        {{ $sup->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="fg" style="min-width:140px;">
            <label>Stock Status</label>
            <select name="stock_filter" class="pos-input" onchange="this.form.submit()">
                <option value="all"       {{ $stockFilter === 'all'       ? 'selected' : '' }}>All Items</option>
                <option value="in_stock"  {{ $stockFilter === 'in_stock'  ? 'selected' : '' }}>In Stock Only (>0)</option>
                <option value="low"       {{ $stockFilter === 'low'       ? 'selected' : '' }}>Low Stock (&le; Alert)</option>
                <option value="out"       {{ $stockFilter === 'out'       ? 'selected' : '' }}>Out of Stock (0)</option>
                <option value="negative"  {{ $stockFilter === 'negative'  ? 'selected' : '' }}>Negative Stock (&lt;0)</option>
            </select>
        </div>

        <div class="fg" style="min-width:150px;">
            <label>Sort By</label>
            <select name="sort" class="pos-input" onchange="this.form.submit()">
                <option value="cost_value_desc" {{ $sortBy === 'cost_value_desc' ? 'selected' : '' }}>Highest Cost Value</option>
                <option value="sale_value_desc" {{ $sortBy === 'sale_value_desc' ? 'selected' : '' }}>Highest Retail Value</option>
                <option value="stock_desc"      {{ $sortBy === 'stock_desc'      ? 'selected' : '' }}>Highest Stock Qty</option>
                <option value="stock_asc"       {{ $sortBy === 'stock_asc'       ? 'selected' : '' }}>Lowest Stock Qty</option>
                <option value="name_asc"        {{ $sortBy === 'name_asc'        ? 'selected' : '' }}>Product Name (A-Z)</option>
            </select>
        </div>

        <div class="fg" style="width:100px;">
            <label>Per Page</label>
            <select name="per_page" class="pos-input" onchange="this.form.submit()">
                @foreach([25, 50, 100, 250, 500] as $n)
                    <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>
        </div>

        <div class="fg">
            <label>&nbsp;</label>
            <div class="d-flex gap-2">
                <button type="submit" class="btn-pos"><i class="bi bi-funnel"></i> Filter</button>
                @if($search || $categoryId || $supplierId || $stockFilter !== 'all' || $sortBy !== 'cost_value_desc' || $perPage != 50)
                    <a href="{{ route('admin.reports.index', ['tab'=>'stock']) }}" class="btn-pos-outline" style="text-decoration:none; padding:8px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; color:#4b5563;">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                @endif
                <button type="button" class="btn-pos-outline" onclick="window.print()" style="padding:8px 12px; border:1px solid #0284c7; border-radius:8px; font-size:13px; color:#0284c7; background:#f0f9ff;">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>
    </form>

    {{-- Filtered Results Summary Banner --}}
    @if(isset($filteredTotals))
    <div class="p-3 mb-3 rounded-3 d-flex flex-wrap align-items-center justify-content-between gap-3" style="background:#f8fafc; border:1.5px solid #e2e8f0;">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div>
                <span class="text-muted small">Filtered Items:</span>
                <strong class="text-dark ms-1">{{ number_format($filteredTotals->count) }}</strong>
            </div>
            <div style="border-left:1px solid #cbd5e1; height:18px;"></div>
            <div>
                <span class="text-muted small">Available Units:</span>
                <strong class="text-primary ms-1">{{ number_format($filteredTotals->units ?? 0) }}</strong>
            </div>
            <div style="border-left:1px solid #cbd5e1; height:18px;"></div>
            <div>
                <span class="text-muted small">Total Cost Value:</span>
                <strong style="color:#0369a1;" class="ms-1">{{ pkr($filteredTotals->cost_value ?? 0, 2) }}</strong>
            </div>
            <div style="border-left:1px solid #cbd5e1; height:18px;"></div>
            <div>
                <span class="text-muted small">Total Retail Value:</span>
                <strong style="color:#047857;" class="ms-1">{{ pkr($filteredTotals->retail_value ?? 0, 2) }}</strong>
            </div>
        </div>
        <div class="text-muted small">
            Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }}
        </div>
    </div>
    @endif

    {{-- Product Valuation Table --}}
    @if($products->isEmpty())
        <div class="report-card">
            <div style="padding:60px; text-align:center; color:#9ca3af;">
                <i class="bi bi-box" style="font-size:48px; display:block; margin-bottom:12px; color:#9ca3af;"></i>
                <p style="font-size:16px; font-weight:700; color:#475569;">No products match your search criteria</p>
                <p style="font-size:13px; color:#64748b;">Try adjusting your category, supplier, or stock filters.</p>
            </div>
        </div>
    @else
    <div class="report-card">
        <div class="report-card-header">
            <span><i class="bi bi-list-check me-2" style="color:var(--pos-primary);"></i> Detailed Product Stock & Shop Valuation</span>
            <span style="font-size:12px; font-weight:700; color:var(--pos-primary);">Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Product & Category</th>
                        <th>SKU / Barcode</th>
                        <th>Supplier</th>
                        <th style="text-align:right;">Available Stock</th>
                        <th style="text-align:right;">Unit Cost</th>
                        <th style="text-align:right;">Unit Sale</th>
                        <th style="text-align:right;">Total Cost Value</th>
                        <th style="text-align:right;">Total Retail Value</th>
                        <th style="text-align:right;">Potential Profit</th>
                        <th style="text-align:center; width:100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $idx => $p)
                    @php
                        $qty = $p->stock_quantity;
                        $isOut = $qty <= 0;
                        $isLow = $qty > 0 && $qty <= $p->low_stock_threshold;
                        $costVal = max(0, $qty) * $p->cost_price;
                        $saleVal = max(0, $qty) * $p->sale_price;
                        $profitVal = max(0, $saleVal - $costVal);
                        $marginPct = $saleVal > 0 ? round(($profitVal / $saleVal) * 100, 1) : 0;
                        $rowNum = ($products->currentPage() - 1) * $products->perPage() + $idx + 1;
                    @endphp
                    <tr>
                        <td style="color:#94a3b8; font-size:12px; font-weight:600;">
                            {{ $rowNum }}
                        </td>
                        <td>
                            <div style="font-weight:700; color:#0f172a; font-size:13.5px;">{{ $p->name }}</div>
                            <div style="margin-top:2px;">
                                <span style="display:inline-block; padding:1px 6px; border-radius:10px; font-size:10.5px; font-weight:600; background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;">
                                    {{ $p->category?->name ?? 'Uncategorized' }}
                                </span>
                            </div>
                        </td>
                        <td style="font-family:monospace; font-size:11.5px; color:#475569;">
                            <div>{{ $p->sku ?: '—' }}</div>
                            @if($p->barcode && $p->barcode !== $p->sku)
                                <div style="color:#94a3b8; font-size:10.5px;">{{ $p->barcode }}</div>
                            @endif
                        </td>
                        <td style="font-size:12px; color:#334155;">
                            {{ $p->supplier?->name ?? '—' }}
                        </td>
                        <td style="text-align:right;">
                            @if($isOut)
                                <span class="badge bg-danger text-white" style="font-size:12px; font-weight:700;">
                                    {{ $qty }} {{ strtoupper($p->unit) }} (OUT)
                                </span>
                            @elseif($isLow)
                                <span class="badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; font-size:12px; font-weight:700;">
                                    {{ $qty }} {{ strtoupper($p->unit) }} (LOW)
                                </span>
                            @else
                                <span class="badge" style="background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; font-size:12.5px; font-weight:800;">
                                    {{ $qty }} {{ strtoupper($p->unit) }}
                                </span>
                            @endif
                        </td>
                        <td style="text-align:right; color:#475569; font-weight:600;">
                            {{ pkr($p->cost_price, 2) }}
                        </td>
                        <td style="text-align:right; color:#0f172a; font-weight:700;">
                            {{ pkr($p->sale_price, 2) }}
                        </td>
                        <td style="text-align:right; font-weight:800; color:#0369a1; background:#f0f9ff;">
                            {{ pkr($costVal, 2) }}
                        </td>
                        <td style="text-align:right; font-weight:800; color:#047857; background:#f0fdf4;">
                            {{ pkr($saleVal, 2) }}
                        </td>
                        <td style="text-align:right; font-weight:700; color:#6d28d9;">
                            <div>{{ pkr($profitVal, 2) }}</div>
                            <div style="font-size:10.5px; color:#8b5cf6;">{{ $marginPct }}% margin</div>
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('admin.stock.create') }}?product_id={{ $p->id }}" class="btn-pos-outline" style="font-size:11.5px; padding:3px 8px; text-decoration:none; border-radius:6px; display:inline-flex; align-items:center; gap:3px;">
                                <i class="bi bi-plus-circle"></i> Stock
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
        <div class="p-3 border-top d-flex justify-content-between align-items-center bg-light">
            <div class="text-muted small">
                Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results
            </div>
            <div>
                {{ $products->links() }}
            </div>
        </div>
        @endif
    </div>
    @endif

{{-- ════════════════════════════════════════════════════════
     TAB 4 — LOW STOCK
═════════════════════════════════════════════════════════ --}}
@elseif($tab === 'lowstock')

    {{-- Stat Cards --}}
    <div class="stat-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); margin-bottom:18px;">
        <div class="stat-card" style="border-left:4px solid #ef4444;">
            <div class="stat-label" style="color:#ef4444;">Out of Stock</div>
            <div class="stat-value" style="color:#ef4444;">{{ $outOfStockCount }}</div>
            <div class="stat-sub">products at 0 or negative stock</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #f59e0b;">
            <div class="stat-label" style="color:#d97706;">Low Stock Alert</div>
            <div class="stat-value" style="color:#d97706;">{{ $lowStockCount }}</div>
            <div class="stat-sub">products below alert threshold</div>
        </div>
        <div class="stat-card" style="border-left:4px solid var(--pos-primary);">
            <div class="stat-label" style="color:var(--pos-primary);">Total Shop Available Stock</div>
            <div class="stat-value" style="color:var(--pos-primary);">{{ number_format($shopTotalUnits ?? 0) }} <span style="font-size:12px;color:#64748b;">Units</span></div>
            <div class="stat-sub">Total inventory units in shop</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #0284c7;">
            <div class="stat-label" style="color:#0284c7;">Shop Stock Cost Value</div>
            <div class="stat-value" style="color:#0369a1;">{{ pkr($shopCostValue ?? 0, 2) }}</div>
            <div class="stat-sub">Total capital in stock</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #059669;">
            <div class="stat-label" style="color:#059669;">Shop Stock Retail Value</div>
            <div class="stat-value" style="color:#047857;">{{ pkr($shopRetailValue ?? 0, 2) }}</div>
            <div class="stat-sub">Expected revenue at retail</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('admin.reports.index') }}" class="rfilter">
        <input type="hidden" name="tab" value="lowstock">
        
        <div class="fg" style="min-width:200px; flex:1;">
            <label>Search Product / SKU</label>
            <input type="text" name="search" class="pos-input" value="{{ $search }}" placeholder="Search name or SKU...">
        </div>

        <div class="fg" style="min-width:180px;">
            <label>Category</label>
            <select name="category_id" class="pos-input searchable-select" placeholder="All Categories / Search...">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="fg" style="min-width:160px;">
            <label>Filter Type</label>
            <select name="stock_filter" class="pos-input" onchange="this.form.submit()">
                <option value="all"       {{ $stockFilter === 'all'       ? 'selected' : '' }}>All Low & Out of Stock</option>
                <option value="out"       {{ $stockFilter === 'out'       ? 'selected' : '' }}>Out of Stock Only (0)</option>
                <option value="low"       {{ $stockFilter === 'low'       ? 'selected' : '' }}>Low Stock Only (&le; Alert)</option>
                <option value="threshold" {{ $stockFilter === 'threshold' ? 'selected' : '' }}>Custom Threshold (&le;)</option>
            </select>
        </div>

        <div class="fg" style="width:110px;">
            <label>Threshold (&le;)</label>
            <input type="number" name="threshold" class="pos-input" value="{{ $threshold }}" min="1" max="500">
        </div>

        <div class="fg">
            <label>&nbsp;</label>
            <div class="d-flex gap-2">
                <button type="submit" class="btn-pos"><i class="bi bi-funnel"></i> Apply</button>
                @if($search || $categoryId || $stockFilter !== 'all' || $threshold != 10)
                    <a href="{{ route('admin.reports.index', ['tab'=>'lowstock']) }}" class="btn-pos-outline" style="text-decoration:none;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#4b5563;">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                @endif
            </div>
        </div>
    </form>

    @if($products->isEmpty())
        <div class="report-card">
            <div style="padding:60px;text-align:center;color:#9ca3af;">
                <i class="bi bi-check-circle" style="font-size:48px;display:block;margin-bottom:12px;color:#10b981;"></i>
                <p style="font-size:16px;font-weight:700;color:#065f46;">All product stocks look healthy!</p>
                <p style="font-size:13px;color:#6b7280;">No products match the selected low stock criteria.</p>
            </div>
        </div>
    @else
    <div class="report-card">
        <div class="report-card-header">
            <span><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Low Stock & Inventory Replenishment Alert</span>
            <span style="color:#ef4444;font-weight:700;">{{ $products->count() }} product(s) need attention</span>
        </div>
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th style="text-align:right">Current Stock</th>
                    <th style="text-align:right">Alert Min</th>
                    <th style="width:130px;">Stock Level</th>
                    <th style="text-align:right">Cost Price</th>
                    <th style="text-align:right">Sale Price</th>
                    <th style="text-align:center;width:130px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $p)
                @php 
                    $limitVal = max(1, $p->low_stock_threshold ?: $threshold);
                    $pct = max(0, min(100, round(($p->stock_quantity / $limitVal) * 100)));
                    $isOut = $p->stock_quantity <= 0;
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:700;color:#111827;">{{ $p->name }}</div>
                        <div style="font-size:11px;color:#9ca3af;font-family:monospace;">
                            {{ $p->sku }} @if($p->barcode)&bull; {{ $p->barcode }}@endif
                        </div>
                    </td>
                    <td>
                        <span style="display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600;background:#f3f4f6;color:#4b5563;border:1px solid #e5e7eb;">
                            {{ $p->category?->name ?? 'Uncategorized' }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <span style="font-size:15px;font-weight:800;color:{{ $isOut ? '#dc2626' : '#d97706' }};">
                            {{ $p->stock_quantity }}
                        </span>
                        <span style="font-size:11px;color:#9ca3af;">{{ strtoupper($p->unit) }}</span>
                        @if($isOut)
                            <span style="display:inline-block;font-size:10px;font-weight:800;background:#fee2e2;color:#dc2626;padding:2px 6px;border-radius:4px;margin-left:4px;">OUT</span>
                        @endif
                    </td>
                    <td style="text-align:right;color:#6b7280;font-weight:600;">
                        {{ $p->low_stock_threshold ?: $threshold }} {{ $p->unit }}
                    </td>
                    <td>
                        <div class="stock-bar-wrap">
                            <div class="stock-bar" style="background:#fee2e2;height:7px;border-radius:4px;overflow:hidden;flex:1;">
                                <div class="fill" style="width:{{ $pct }}%;height:100%;background:{{ $isOut ? '#ef4444' : ($pct < 40 ? '#f59e0b' : '#10b981') }};"></div>
                            </div>
                            <span style="font-size:11px;color:#6b7280;width:34px;text-align:right;font-weight:600;">{{ $pct }}%</span>
                        </div>
                    </td>
                    <td style="text-align:right;color:#6b7280;">{{ pkr($p->cost_price, 2) }}</td>
                    <td style="text-align:right;font-weight:700;color:#111827;">{{ pkr($p->sale_price, 2) }}</td>
                    <td style="text-align:center;">
                        <a href="{{ route('admin.stock.create') }}" class="btn-pos-outline" style="font-size:12px;padding:4px 10px;text-decoration:none;border-radius:6px;display:inline-flex;align-items:center;gap:4px;">
                            <i class="bi bi-plus-circle"></i> Restock
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

{{-- ════════════════════════════════════════════════════════
     TAB 4 — TOP SELLING
═════════════════════════════════════════════════════════ --}}
@elseif($tab === 'topsell')

    <form method="GET" action="{{ route('admin.reports.index') }}" class="rfilter">
        <input type="hidden" name="tab" value="topsell">
        <div class="fg">
            <label>From</label>
            <input type="date" name="from" class="pos-input" value="{{ $from }}" style="width:150px;">
        </div>
        <div class="fg">
            <label>To</label>
            <input type="date" name="to" class="pos-input" value="{{ $to }}" style="width:150px;">
        </div>
        <div class="fg">
            <label>Category</label>
            <select name="category_id" class="pos-input searchable-select" style="min-width:180px;" placeholder="All Categories / Search...">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="fg">
            <label>Show Top</label>
            <select name="limit" class="pos-input" style="width:100px;">
                @foreach([5,10,20,50,100] as $l)
                    <option value="{{ $l }}" {{ $limit==$l ? 'selected':'' }}>Top {{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="fg">
            <label>&nbsp;</label>
            <button type="submit" class="btn-pos"><i class="bi bi-funnel"></i> Apply</button>
        </div>
    </form>

    {{-- Category Performance Summary Breakdown --}}
    @if(isset($categoryBreakdown) && $categoryBreakdown->isNotEmpty())
    <div class="report-card" style="margin-bottom:20px;">
        <div class="report-card-header">
            <span><i class="bi bi-tags me-1 text-primary"></i> Category-Wise Sales & Profit Summary</span>
            <span style="color:#9ca3af;">{{ $from }} &mdash; {{ $to }}</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th style="text-align:right">Products Sold</th>
                        <th style="text-align:right">Units Sold</th>
                        <th style="text-align:right">Total Revenue</th>
                        <th style="text-align:right">Total Cost</th>
                        <th style="text-align:right">Gross Profit</th>
                        <th style="text-align:right">Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categoryBreakdown as $cb)
                    <tr>
                        <td style="font-weight:700;">
                            <span style="display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;">
                                <i class="bi bi-tag me-1"></i>{{ $cb->category_name }}
                            </span>
                        </td>
                        <td style="text-align:right;color:#6b7280;">{{ $cb->total_products_sold }} items</td>
                        <td style="text-align:right;font-weight:800;color:#111827;">{{ number_format($cb->total_qty) }}</td>
                        <td style="text-align:right;font-weight:700;color:#0f766e;">{{ pkr($cb->total_revenue, 2) }}</td>
                        <td style="text-align:right;color:#64748b;">{{ pkr($cb->total_cost, 2) }}</td>
                        <td style="text-align:right;font-weight:800;color:{{ $cb->gross_profit >= 0 ? '#059669' : '#dc2626' }};">
                            {{ pkr($cb->gross_profit, 2) }}
                        </td>
                        <td style="text-align:right;">
                            <span class="badge {{ $cb->profit_margin >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border" style="font-size:11px; font-weight:700;">
                                {{ $cb->profit_margin }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Main Top Products Table --}}
    <div class="report-card" style="margin-bottom:20px;">
        <div class="report-card-header">
            <span><i class="bi bi-trophy me-1 text-warning"></i> Top {{ $limit }} Products by Units Sold & Profit</span>
            <span style="color:#9ca3af;">{{ $from }} &mdash; {{ $to }}</span>
        </div>
        @if($products->isEmpty())
            <div style="padding:40px;text-align:center;color:#9ca3af;">No sales data in this period.</div>
        @else
        <div style="overflow-x:auto;">
            <table class="rpt-table">
                <thead><tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th style="text-align:right">Units Sold</th>
                    <th style="text-align:right">Avg Price</th>
                    <th style="text-align:right">Total Revenue</th>
                    <th style="text-align:right">Total Cost</th>
                    <th style="text-align:right">Gross Profit</th>
                    <th style="text-align:right">Margin</th>
                </tr></thead>
                <tbody>
                    @foreach($products as $i => $p)
                    <tr>
                        <td>
                            <span class="rank {{ $i===0 ? 'rank-1' : ($i===1 ? 'rank-2' : ($i===2 ? 'rank-3' : 'rank-n')) }}">
                                {{ $i+1 }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight:700;">{{ $p->product_name }}</div>
                            <div style="font-size:11px;color:#9ca3af;font-family:monospace;">{{ $p->product_sku }} &middot; {{ strtoupper($p->product_unit) }}</div>
                        </td>
                        <td>
                            <span style="display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;">
                                {{ $p->category_name }}
                            </span>
                        </td>
                        <td style="text-align:right;font-size:15px;font-weight:800;color:#111827;">{{ number_format($p->total_qty) }}</td>
                        <td style="text-align:right;color:#6b7280;">{{ pkr($p->avg_price,2) }}</td>
                        <td style="text-align:right;font-weight:700;color:#0f766e;">{{ pkr($p->total_revenue,2) }}</td>
                        <td style="text-align:right;color:#64748b;">{{ pkr($p->total_cost,2) }}</td>
                        <td style="text-align:right;font-weight:800;color:{{ $p->gross_profit >= 0 ? '#059669' : '#dc2626' }};">
                            {{ pkr($p->gross_profit,2) }}
                        </td>
                        <td style="text-align:right;">
                            <span class="badge {{ $p->profit_margin >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border" style="font-size:11px; font-weight:700;">
                                {{ $p->profit_margin }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Category-Wise Detailed Product Groupings --}}
    @if(isset($categoryWiseProducts) && $categoryWiseProducts->isNotEmpty())
        <div style="margin:24px 0 12px 0;">
            <h5 style="font-weight:800;color:#1e293b;display:flex;align-items:center;gap:8px;font-size:16px;">
                <i class="bi bi-grid text-primary"></i> Category-Wise Product Rankings & Margins
            </h5>
        </div>

        @foreach($categoryWiseProducts as $categoryName => $catProducts)
            @php
                $catRev = $catProducts->sum('total_revenue');
                $catCost = $catProducts->sum('total_cost');
                $catProfit = $catRev - $catCost;
                $catMargin = $catRev > 0 ? round(($catProfit / $catRev) * 100, 1) : 0;
            @endphp
            <div class="report-card" style="margin-bottom:16px;">
                <div class="report-card-header" style="background:#f8fafc; flex-wrap:wrap; gap:8px;">
                    <div style="font-weight:700;color:#1e293b;font-size:13px;display:flex;align-items:center;gap:8px;">
                        <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:700;background:#e0e7ff;color:#4338ca;border:1px solid #c7d2fe;">
                            <i class="bi bi-folder2-open me-1"></i>{{ $categoryName }}
                        </span>
                        <span style="font-size:12px;color:#64748b;font-weight:500;">({{ $catProducts->count() }} distinct products sold)</span>
                    </div>
                    <div style="font-weight:700;font-size:12px;display:flex;gap:12px;align-items:center;">
                        <span style="color:#0f766e;">Sales: {{ pkr($catRev, 2) }}</span>
                        <span style="color:#64748b;">Cost: {{ pkr($catCost, 2) }}</span>
                        <span style="color:{{ $catProfit >= 0 ? '#059669' : '#dc2626' }};">
                            Profit: {{ pkr($catProfit, 2) }} ({{ $catMargin }}%)
                        </span>
                    </div>
                </div>
                <div style="overflow-x:auto;">
                    <table class="rpt-table">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>Product</th>
                                <th style="text-align:right">Units Sold</th>
                                <th style="text-align:right">Avg Price</th>
                                <th style="text-align:right">Total Revenue</th>
                                <th style="text-align:right">Total Cost</th>
                                <th style="text-align:right">Gross Profit</th>
                                <th style="text-align:right">Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($catProducts as $idx => $cp)
                            <tr>
                                <td style="color:#9ca3af;font-weight:700;">{{ $idx + 1 }}</td>
                                <td>
                                    <div style="font-weight:700;">{{ $cp->product_name }}</div>
                                    <div style="font-size:11px;color:#9ca3af;font-family:monospace;">{{ $cp->product_sku }} &middot; {{ strtoupper($cp->product_unit) }}</div>
                                </td>
                                <td style="text-align:right;font-weight:800;color:#111827;">{{ number_format($cp->total_qty) }}</td>
                                <td style="text-align:right;color:#6b7280;">{{ pkr($cp->avg_price, 2) }}</td>
                                <td style="text-align:right;font-weight:700;color:#0f766e;">{{ pkr($cp->total_revenue, 2) }}</td>
                                <td style="text-align:right;color:#64748b;">{{ pkr($cp->total_cost, 2) }}</td>
                                <td style="text-align:right;font-weight:800;color:{{ $cp->gross_profit >= 0 ? '#059669' : '#dc2626' }};">
                                    {{ pkr($cp->gross_profit, 2) }}
                                </td>
                                <td style="text-align:right;">
                                    <span class="badge {{ $cp->profit_margin >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border" style="font-size:11px; font-weight:700;">
                                        {{ $cp->profit_margin }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif

@elseif($tab === 'suppliers')
    {{-- Supplier Payments & Purchases Filter Bar --}}
    <form method="GET" action="{{ route('admin.reports.index') }}" class="rpt-filter-bar">
        <input type="hidden" name="tab" value="suppliers">
        <div class="fg">
            <label>From Date</label>
            <input type="date" name="from" value="{{ $from }}" class="pos-input">
        </div>
        <div class="fg">
            <label>To Date</label>
            <input type="date" name="to" value="{{ $to }}" class="pos-input">
        </div>
        <div class="fg">
            <label>Supplier</label>
            <select name="supplier_id" class="pos-input searchable-select" style="min-width:200px;" placeholder="All Suppliers / Search...">
                <option value="">All Suppliers</option>
                @foreach($suppliersList as $sup)
                    @php
                        $compName = !empty($sup->company_name) && strcasecmp(trim($sup->company_name), trim($sup->name)) !== 0 ? " ({$sup->company_name})" : '';
                    @endphp
                    <option value="{{ $sup->id }}" {{ (string)$supplierId === (string)$sup->id ? 'selected' : '' }}>
                        {{ $sup->name }}{{ $compName }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="fg">
            <label>Payment Method</label>
            <select name="payment_method" class="pos-input" style="min-width:140px;">
                <option value="">All Methods</option>
                <option value="cash" {{ $method === 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="bank" {{ $method === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                <option value="cheque" {{ $method === 'cheque' ? 'selected' : '' }}>Cheque</option>
                <option value="online" {{ $method === 'online' ? 'selected' : '' }}>Online</option>
            </select>
        </div>
        <div class="fg">
            <label>&nbsp;</label>
            <div style="display:flex; gap:6px;">
                <button type="submit" class="btn-pos"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('admin.reports.index', ['tab' => 'suppliers']) }}" class="btn-pos btn-pos-secondary" style="text-decoration:none; display:inline-flex; align-items:center;">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Supplier KPI Stat Cards --}}
    <div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom:20px;">
        <div class="stat-card" style="border-left: 4px solid #3b82f6;">
            <div class="stat-card-title"><i class="bi bi-truck me-1 text-primary"></i> Total Purchases</div>
            <div class="stat-card-value text-primary" style="font-size:22px;">{{ pkr($totalPurchases, 2) }}</div>
            <div class="stat-card-sub">{{ $purchaseCount }} invoice(s) in selected range</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #059669; background: #f0fdf4;">
            <div class="stat-card-title"><i class="bi bi-cash-stack me-1 text-success"></i> Total Paid Amount</div>
            <div class="stat-card-value" style="font-size:22px; color:#059669;">{{ pkr($totalPaidAmount, 2) }}</div>
            <div class="stat-card-sub" style="color:#047857; font-weight:600;">{{ $paymentCount }} payment voucher(s) cleared</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #f59e0b;">
            <div class="stat-card-title"><i class="bi bi-arrow-return-left me-1 text-warning"></i> Purchase Returns</div>
            <div class="stat-card-value text-warning" style="font-size:22px;">{{ pkr($totalReturns, 2) }}</div>
            <div class="stat-card-sub">Stock returned to suppliers</div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #dc2626; background: #fef2f2;">
            <div class="stat-card-title"><i class="bi bi-clock-history me-1 text-danger"></i> Outstanding Payables</div>
            <div class="stat-card-value text-danger" style="font-size:22px;">{{ pkr($totalOutstandingPayables, 2) }}</div>
            <div class="stat-card-sub" style="color:#b91c1c; font-weight:600;">Total net payable balance</div>
        </div>
    </div>

    {{-- Payment Method Breakdown Badges --}}
    @if(isset($methodBreakdown) && $methodBreakdown->isNotEmpty())
    <div class="report-card" style="margin-bottom:20px; padding:12px 16px;">
        <div style="font-size:12px; font-weight:700; color:#64748b; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px;">
            <i class="bi bi-credit-card-2-front me-1 text-primary"></i> Payment Methods Summary ({{ $from }} &mdash; {{ $to }})
        </div>
        <div style="display:flex; flex-wrap:wrap; gap:12px;">
            @foreach($methodBreakdown as $mb)
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:8px 14px; display:flex; align-items:center; gap:10px;">
                    <span class="badge bg-primary-subtle text-primary border" style="font-size:11px; text-transform:uppercase; font-weight:700;">
                        {{ $mb->payment_method ?: 'Cash' }}
                    </span>
                    <span style="font-weight:800; color:#059669; font-size:13px;">{{ pkr($mb->total_amount, 2) }}</span>
                    <span style="font-size:11px; color:#64748b;">({{ $mb->count }} vouchers)</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Supplier-Wise Breakdown Table --}}
    <div class="report-card" style="margin-bottom:24px;">
        <div class="report-card-header">
            <span><i class="bi bi-buildings me-1 text-primary"></i> Supplier Summary Breakdown & Balances</span>
            <span style="color:#9ca3af;">{{ $from }} &mdash; {{ $to }}</span>
        </div>
        @if($supplierBreakdown->isEmpty())
            <div style="padding:40px;text-align:center;color:#9ca3af;">No supplier transactions found for this selection.</div>
        @else
        <div style="overflow-x:auto;">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Supplier / Company</th>
                        <th>Phone</th>
                        <th style="text-align:right">Period Purchases</th>
                        <th style="text-align:right">Paid Amount</th>
                        <th style="text-align:right">Period Returns</th>
                        <th style="text-align:right">Outstanding Balance</th>
                        <th>Last Payment</th>
                        <th style="text-align:center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($supplierBreakdown as $idx => $sb)
                    <tr>
                        <td style="color:#9ca3af; font-weight:700;">{{ $idx + 1 }}</td>
                        <td>
                            <div style="font-weight:700; color:#1e293b;">{{ $sb->name }}</div>
                            @if($sb->company_name)
                                <div style="font-size:11px; color:#64748b;">{{ $sb->company_name }}</div>
                            @endif
                        </td>
                        <td style="font-size:12px; color:#64748b;">{{ $sb->phone ?: '—' }}</td>
                        <td style="text-align:right; font-weight:600; color:#334155;">{{ pkr($sb->period_purchases, 2) }}</td>
                        <td style="text-align:right; font-weight:800; color:#059669; background:#f0fdf4;">
                            {{ pkr($sb->period_paid, 2) }}
                        </td>
                        <td style="text-align:right; color:#d97706;">{{ pkr($sb->period_returns, 2) }}</td>
                        <td style="text-align:right;">
                            @if($sb->pending_balance > 0)
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:12px; font-weight:800;">
                                    {{ pkr($sb->pending_balance, 2) }}
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:11px; font-weight:700;">
                                    Settled ({{ pkr(0, 2) }})
                                </span>
                            @endif
                        </td>
                        <td style="font-size:11px; color:#64748b;">
                            @if($sb->last_payment_date)
                                <div><i class="bi bi-calendar-event me-1"></i>{{ \Carbon\Carbon::parse($sb->last_payment_date)->format('M d, Y') }}</div>
                                <div style="font-weight:700; color:#059669;">{{ pkr($sb->last_payment_amt, 2) }}</div>
                            @else
                                <span style="color:#9ca3af;">None</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('admin.suppliers.ledger', $sb->id) }}" class="btn btn-sm btn-outline-primary" style="font-size:11px; padding:2px 8px; border-radius:6px;" title="View Supplier Ledger">
                                <i class="bi bi-journal-text me-1"></i>Ledger
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Detailed Payment Transactions Log --}}
    <div class="report-card" style="margin-bottom:20px;">
        <div class="report-card-header">
            <span><i class="bi bi-receipt me-1 text-success"></i> Supplier Payment Transactions Log</span>
            <span style="color:#9ca3af;">{{ $from }} &mdash; {{ $to }} ({{ $paymentLogs->count() }} records)</span>
        </div>
        @if($paymentLogs->isEmpty())
            <div style="padding:40px;text-align:center;color:#9ca3af;">No payment transactions recorded in this period.</div>
        @else
        <div style="overflow-x:auto;">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Voucher / Ref #</th>
                        <th>Method</th>
                        <th style="text-align:right">Paid Amount</th>
                        <th>Recorded By</th>
                        <th>Notes / Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentLogs as $log)
                    <tr>
                        <td style="font-size:12px; font-weight:600; color:#1e293b; white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($log->payment_date ?: $log->created_at)->format('M d, Y') }}
                            <div style="font-size:10px; color:#94a3b8;">{{ \Carbon\Carbon::parse($log->created_at)->format('h:i A') }}</div>
                        </td>
                        <td>
                            <div style="font-weight:700; color:#1e293b;">{{ $log->supplier?->name ?: '—' }}</div>
                            @if($log->supplier?->company_name)
                                <div style="font-size:11px; color:#64748b;">{{ $log->supplier->company_name }}</div>
                            @endif
                        </td>
                        <td style="font-family:monospace; font-size:12px; font-weight:600; color:#475569;">
                            {{ $log->reference_number ?: ('VCH-' . str_pad($log->id, 5, '0', STR_PAD_LEFT)) }}
                        </td>
                        <td>
                            @php
                                $m = strtolower($log->payment_method ?? 'cash');
                                $badgeCls = match($m) {
                                    'cash' => 'bg-success-subtle text-success',
                                    'bank' => 'bg-info-subtle text-info',
                                    'cheque' => 'bg-warning-subtle text-warning',
                                    'online' => 'bg-primary-subtle text-primary',
                                    default => 'bg-secondary-subtle text-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeCls }} border" style="font-size:11px; text-transform:uppercase; font-weight:700;">
                                {{ $log->payment_method ?: 'Cash' }}
                            </span>
                        </td>
                        <td style="text-align:right; font-weight:800; font-size:13px; color:#059669; background:#f0fdf4;">
                            {{ pkr($log->amount, 2) }}
                        </td>
                        <td style="font-size:12px; color:#475569;">
                            <i class="bi bi-person-circle me-1 text-muted"></i>{{ $log->user?->name ?: 'Admin / System' }}
                        </td>
                        <td style="font-size:11px; color:#64748b; max-width:250px;">
                            {{ $log->notes ?: '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

@endif

@endsection

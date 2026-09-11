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
    <a href="{{ route('admin.reports.index', ['tab'=>'lowstock']) }}"
       class="tab-btn {{ $tab==='lowstock' ? 'active' : '' }}">
        <i class="bi bi-exclamation-triangle"></i> Low Stock
    </a>
    <a href="{{ route('admin.reports.index', ['tab'=>'topsell']) }}"
       class="tab-btn {{ $tab==='topsell' ? 'active' : '' }}">
        <i class="bi bi-trophy"></i> Top Selling
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

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">Total Transactions</div>
            <div class="stat-value">{{ number_format($totals['transactions']) }}</div>
            <div class="stat-sub">in last {{ $days }} days</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">{{ pkr($totals['revenue'], 2) }}</div>
            <div class="stat-sub">completed sales</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Avg Sale Value</div>
            <div class="stat-value">{{ pkr($totals['avg_sale'], 2) }}</div>
            <div class="stat-sub">per transaction</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Days</div>
            <div class="stat-value">{{ $rows->count() }}</div>
            <div class="stat-sub">days with sales</div>
        </div>
    </div>

    <div class="report-card">
        <div class="report-card-header"><span>Sales by Day</span><span>{{ $rows->count() }} days</span></div>
        @if($rows->isEmpty())
            <div style="padding:40px;text-align:center;color:#9ca3af;">No sales in this period.</div>
        @else
        <table class="rpt-table">
            <thead><tr>
                <th>Date</th><th style="text-align:right">Transactions</th>
                <th style="text-align:right">Revenue</th><th style="text-align:right">Avg Sale</th>
            </tr></thead>
            <tbody>
                @foreach($rows as $row)
                <tr>
                    <td style="font-weight:600;">{{ \Carbon\Carbon::parse($row->date)->format('D, d M Y') }}</td>
                    <td style="text-align:right;">{{ $row->transactions }}</td>
                    <td style="text-align:right;font-weight:700;">{{ pkr($row->revenue,2) }}</td>
                    <td style="text-align:right;color:#6b7280;">{{ pkr($row->avg_sale,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
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
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">Transactions</div>
            <div class="stat-value">{{ number_format($summary->transactions) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">{{ pkr($summary->revenue,2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Cash Revenue</div>
            <div class="stat-value">{{ pkr($summary->cash_revenue,2) }}</div>
            <div class="stat-sub">💵 cash payments</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Card Revenue</div>
            <div class="stat-value">{{ pkr($summary->card_revenue,2) }}</div>
            <div class="stat-sub">💳 card payments</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Avg Sale</div>
            <div class="stat-value">{{ pkr($summary->avg_sale,2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Discounts Given</div>
            <div class="stat-value">{{ pkr($summary->discounts,2) }}</div>
        </div>
    </div>
    @endif

    <div class="report-card">
        <div class="report-card-header"><span>Day-by-Day Breakdown</span><span>{{ $byDay->count() }} days</span></div>
        @if($byDay->isEmpty())
            <div style="padding:40px;text-align:center;color:#9ca3af;">No sales in this date range.</div>
        @else
        <table class="rpt-table">
            <thead><tr>
                <th>Date</th><th style="text-align:right">Transactions</th><th style="text-align:right">Revenue</th>
            </tr></thead>
            <tbody>
                @foreach($byDay as $row)
                <tr>
                    <td style="font-weight:600;">{{ \Carbon\Carbon::parse($row->date)->format('D, d M Y') }}</td>
                    <td style="text-align:right;">{{ $row->transactions }}</td>
                    <td style="text-align:right;font-weight:700;">{{ pkr($row->revenue,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

{{-- ════════════════════════════════════════════════════════
     TAB 3 — LOW STOCK
═════════════════════════════════════════════════════════ --}}
@elseif($tab === 'lowstock')

    {{-- Stat Cards --}}
    <div class="stat-grid" style="margin-bottom:18px;">
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
        <div class="stat-card">
            <div class="stat-label">Items In Filter</div>
            <div class="stat-value">{{ $products->count() }}</div>
            <div class="stat-sub">products listed below</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('admin.reports.index') }}" class="rfilter">
        <input type="hidden" name="tab" value="lowstock">
        
        <div class="fg" style="min-width:200px; flex:1;">
            <label>Search Product / SKU</label>
            <input type="text" name="search" class="pos-input" value="{{ $search }}" placeholder="Search name or SKU...">
        </div>

        <div class="fg" style="min-width:160px;">
            <label>Category</label>
            <select name="category_id" class="pos-input">
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
            <select name="category_id" class="pos-input" style="min-width:180px;">
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
            <span><i class="bi bi-tags me-1 text-primary"></i> Category-Wise Sales Summary</span>
            <span style="color:#9ca3af;">{{ $from }} &mdash; {{ $to }}</span>
        </div>
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th style="text-align:right">Products Sold</th>
                    <th style="text-align:right">Units Sold</th>
                    <th style="text-align:right">Orders Count</th>
                    <th style="text-align:right">Total Revenue</th>
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
                    <td style="text-align:right;color:#6b7280;">{{ $cb->total_products_sold }} products</td>
                    <td style="text-align:right;font-weight:800;color:#111827;">{{ number_format($cb->total_qty) }}</td>
                    <td style="text-align:right;color:#6b7280;">{{ $cb->order_count }}</td>
                    <td style="text-align:right;font-weight:700;color:#065f46;">{{ pkr($cb->total_revenue, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Main Top Products Table --}}
    <div class="report-card" style="margin-bottom:20px;">
        <div class="report-card-header">
            <span><i class="bi bi-trophy me-1 text-warning"></i> Top {{ $limit }} Products by Units Sold</span>
            <span style="color:#9ca3af;">{{ $from }} &mdash; {{ $to }}</span>
        </div>
        @if($products->isEmpty())
            <div style="padding:40px;text-align:center;color:#9ca3af;">No sales data in this period.</div>
        @else
        <table class="rpt-table">
            <thead><tr>
                <th>#</th>
                <th>Product</th>
                <th>Category</th>
                <th style="text-align:right">Units Sold</th>
                <th style="text-align:right">Orders</th>
                <th style="text-align:right">Avg Price</th>
                <th style="text-align:right">Total Revenue</th>
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
                    <td style="text-align:right;font-size:16px;font-weight:800;color:#111827;">{{ number_format($p->total_qty) }}</td>
                    <td style="text-align:right;color:#6b7280;">{{ $p->order_count }}</td>
                    <td style="text-align:right;color:#6b7280;">{{ pkr($p->avg_price,2) }}</td>
                    <td style="text-align:right;font-weight:700;color:#065f46;">{{ pkr($p->total_revenue,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Category-Wise Detailed Product Groupings --}}
    @if(isset($categoryWiseProducts) && $categoryWiseProducts->isNotEmpty())
        <div style="margin:24px 0 12px 0;">
            <h5 style="font-weight:800;color:#1e293b;display:flex;align-items:center;gap:8px;font-size:16px;">
                <i class="bi bi-grid text-primary"></i> Category-Wise Product Rankings
            </h5>
        </div>

        @foreach($categoryWiseProducts as $categoryName => $catProducts)
            <div class="report-card" style="margin-bottom:16px;">
                <div class="report-card-header" style="background:#f8fafc;">
                    <div style="font-weight:700;color:#1e293b;font-size:13px;display:flex;align-items:center;gap:8px;">
                        <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:700;background:#e0e7ff;color:#4338ca;border:1px solid #c7d2fe;">
                            <i class="bi bi-folder2-open me-1"></i>{{ $categoryName }}
                        </span>
                        <span style="font-size:12px;color:#64748b;font-weight:500;">({{ $catProducts->count() }} distinct products sold)</span>
                    </div>
                    <span style="font-weight:700;color:#065f46;font-size:12px;">
                        Total Sales: {{ pkr($catProducts->sum('total_revenue'), 2) }}
                    </span>
                </div>
                <table class="rpt-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Product</th>
                            <th style="text-align:right">Units Sold</th>
                            <th style="text-align:right">Orders</th>
                            <th style="text-align:right">Avg Price</th>
                            <th style="text-align:right">Total Revenue</th>
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
                            <td style="text-align:right;color:#6b7280;">{{ $cp->order_count }}</td>
                            <td style="text-align:right;color:#6b7280;">{{ pkr($cp->avg_price, 2) }}</td>
                            <td style="text-align:right;font-weight:700;color:#065f46;">{{ pkr($cp->total_revenue, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

@endif

@endsection

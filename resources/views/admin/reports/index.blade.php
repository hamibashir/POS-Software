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

    <form method="GET" action="{{ route('admin.reports.index') }}" class="rfilter">
        <input type="hidden" name="tab" value="lowstock">
        <div class="fg">
            <label>Alert Threshold (≤)</label>
            <input type="number" name="threshold" class="pos-input" value="{{ $threshold }}" min="1" max="100" style="width:100px;">
        </div>
        <div class="fg">
            <label>&nbsp;</label>
            <button type="submit" class="btn-pos"><i class="bi bi-funnel"></i> Apply</button>
        </div>
    </form>

    @if($products->isEmpty())
        <div class="report-card">
            <div style="padding:60px;text-align:center;color:#9ca3af;">
                <i class="bi bi-check-circle" style="font-size:48px;display:block;margin-bottom:12px;color:#10b981;"></i>
                <p style="font-size:15px;font-weight:600;color:#065f46;">All products have sufficient stock!</p>
                <p>No products at or below {{ $threshold }} units.</p>
            </div>
        </div>
    @else
    <div style="margin-bottom:10px;">
        <span style="background:#fee2e2;color:#991b1b;border-radius:20px;padding:5px 14px;font-size:13px;font-weight:700;">
            ⚠ {{ $products->count() }} product(s) at or below {{ $threshold }} units
        </span>
    </div>
    <div class="report-card">
        <table class="rpt-table">
            <thead><tr>
                <th>Product</th><th>Category</th><th style="text-align:right">Stock</th>
                <th>Level</th><th style="text-align:right">Cost Price</th><th style="text-align:right">Sell Price</th>
            </tr></thead>
            <tbody>
                @foreach($products as $p)
                @php $pct = $threshold > 0 ? min(100, ($p->stock_quantity / $threshold) * 100) : 0; @endphp
                <tr>
                    <td>
                        <div style="font-weight:700;">{{ $p->name }}</div>
                        <div style="font-size:11px;color:#9ca3af;font-family:monospace;">{{ $p->sku }}</div>
                    </td>
                    <td style="color:#6b7280;">{{ $p->category?->name ?? '—' }}</td>
                    <td style="text-align:right;">
                        <span style="font-size:16px;font-weight:800;color:{{ $p->stock_quantity == 0 ? '#991b1b' : '#b45309' }};">
                            {{ $p->stock_quantity }}
                        </span>
                        <span style="font-size:11px;color:#9ca3af;"> {{ strtoupper($p->unit) }}</span>
                    </td>
                    <td style="min-width:120px;">
                        <div class="stock-bar-wrap">
                            <div class="stock-bar">
                                <div class="fill" style="width:{{ $pct }}%;background:{{ $p->stock_quantity==0 ? '#ef4444' : ($pct<50 ? '#f59e0b' : '#10b981') }};"></div>
                            </div>
                            <span style="font-size:11px;color:#9ca3af;width:32px;">{{ round($pct) }}%</span>
                        </div>
                    </td>
                    <td style="text-align:right;color:#6b7280;">{{ pkr($p->cost_price,2) }}</td>
                    <td style="text-align:right;font-weight:700;">{{ pkr($p->selling_price,2) }}</td>
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
            <label>Show Top</label>
            <select name="limit" class="pos-input" style="width:100px;">
                @foreach([5,10,20,50] as $l)
                    <option value="{{ $l }}" {{ $limit==$l ? 'selected':'' }}>Top {{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="fg">
            <label>&nbsp;</label>
            <button type="submit" class="btn-pos"><i class="bi bi-funnel"></i> Apply</button>
        </div>
    </form>

    <div class="report-card">
        <div class="report-card-header">
            <span>Top {{ $limit }} Products by Units Sold</span>
            <span style="color:#9ca3af;">{{ $from }} — {{ $to }}</span>
        </div>
        @if($products->isEmpty())
            <div style="padding:40px;text-align:center;color:#9ca3af;">No sales data in this period.</div>
        @else
        <table class="rpt-table">
            <thead><tr>
                <th>#</th><th>Product</th><th style="text-align:right">Units Sold</th>
                <th style="text-align:right">Orders</th><th style="text-align:right">Avg Price</th>
                <th style="text-align:right">Total Revenue</th>
            </tr></thead>
            <tbody>
                @foreach($products as $i => $p)
                <tr>
                    <td>
                        <span class="rank {{ $i===0 ? 'rank-1' : ($i===1 ? 'rank-2' : ($i===2 ? 'rank-3' : 'rank-n')) }}">
                            {{ $i===0 ? '🥇' : ($i===1 ? '🥈' : ($i===2 ? '🥉' : $i+1)) }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight:700;">{{ $p->product_name }}</div>
                        <div style="font-size:11px;color:#9ca3af;font-family:monospace;">{{ $p->product_sku }} · {{ strtoupper($p->product_unit) }}</div>
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

@endif

@endsection

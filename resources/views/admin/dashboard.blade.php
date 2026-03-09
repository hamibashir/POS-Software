@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* ── Dashboard-specific ──────────────────────────── */
    .dash-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,.05);
        padding: 24px;
        position: relative;
        overflow: hidden;
        transition: box-shadow .2s;
    }
    .dash-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.09); }

    .dash-card .card-bg-icon {
        position: absolute; right: 0; top: 0;
        padding: 16px; opacity: .10;
        font-size: 64px; line-height: 1;
        transition: opacity .2s;
    }
    .dash-card:hover .card-bg-icon { opacity: .20; }

    .dash-card .label {
        font-size: 13px; font-weight: 500;
        color: #667c85; margin-bottom: 4px;
    }
    .dash-card .value {
        font-size: 26px; font-weight: 700;
        color: #121617; margin: 0;
        line-height: 1.2;
    }

    .badge-trend {
        display: inline-flex; align-items: center; gap: 2px;
        font-size: 11px; font-weight: 600;
        padding: 2px 8px; border-radius: 20px;
    }
    .badge-pos  { background: #dcfce7; color: #078836; }
    .badge-neg  { background: #fee2e2; color: #e73508; }
    .badge-warn { background: #fee2e2; color: #e73508; }

    /* ── Chart container ─────────────────────────────── */
    .chart-card {
        background: #fff; border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,.05);
        padding: 24px;
        display: flex; flex-direction: column;
    }
    .chart-card svg { flex: 1; }

    /* ── Low stock table card ────────────────────────── */
    .stock-card {
        background: #fff; border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,.05);
        display: flex; flex-direction: column;
        overflow: hidden;
    }
    .stock-card .s-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex; justify-content: space-between; align-items: center;
    }
    .stock-card .s-header h3 {
        font-size: 16px; font-weight: 700; color: #121617; margin: 0;
    }
    .stock-card table thead th {
        background: #f8fafc;
        font-size: 11px; font-weight: 600; text-transform: uppercase;
        letter-spacing: .6px; color: #667c85;
        padding: 10px 20px; border-bottom: 1px solid #f1f5f9;
    }
    .stock-card table tbody tr { transition: background .15s; }
    .stock-card table tbody tr:hover { background: #f8fafc; }
    .stock-card table tbody td {
        padding: 14px 20px; font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .stock-card table tbody tr:last-child td { border-bottom: none; }

    .stock-badge-red {
        display: inline-flex; align-items: center;
        background: #fee2e2; color: #b91c1c;
        font-size: 11px; font-weight: 600;
        padding: 3px 10px; border-radius: 20px;
    }
    .stock-badge-orange {
        display: inline-flex; align-items: center;
        background: #ffedd5; color: #c2410c;
        font-size: 11px; font-weight: 600;
        padding: 3px 10px; border-radius: 20px;
    }

    .day-label {
        font-size: 11px; font-weight: 600;
        color: #94a3b8; text-transform: uppercase; letter-spacing: .6px;
    }
</style>
@endpush

@section('content')

{{-- ── Page Header ──────────────────────────────────────── --}}
<div class="d-flex flex-column flex-sm-row sm-items-end justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 style="font-size:22px;font-weight:700;color:#121617;margin:0;">Dashboard Overview</h1>
        <p style="font-size:14px;color:#667c85;margin:4px 0 0;">Here's what's happening in your store today.</p>
    </div>
    <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:#667c85;
                background:#fff;padding:7px 14px;border-radius:8px;
                border:1px solid #e2e8f0;box-shadow:0 1px 2px rgba(0,0,0,.04);white-space:nowrap;">
        <i class="bi bi-calendar3" style="font-size:15px;"></i>
        <span>{{ now()->format('M d, Y') }}</span>
    </div>
</div>

{{-- ── KPI Cards ────────────────────────────────────────── --}}
<div class="row g-4 mb-4">

    {{-- Today's Sales --}}
    <div class="col-6 col-lg-3">
        <div class="dash-card">
            <div class="card-bg-icon text-primary"><i class="bi bi-cash-stack"></i></div>
            <p class="label">Today's Sales</p>
            <div class="d-flex align-items-baseline gap-2 flex-wrap">
                <p class="value">Rs. {{ number_format($todaySales, 0) }}</p>
                @if($todayVsYesterday !== null)
                <span class="badge-trend {{ $todayVsYesterday >= 0 ? 'badge-pos' : 'badge-neg' }}">
                    <i class="bi bi-arrow-{{ $todayVsYesterday >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($todayVsYesterday) }}%
                </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Total Products --}}
    <div class="col-6 col-lg-3">
        <div class="dash-card">
            <div class="card-bg-icon text-primary"><i class="bi bi-box-seam"></i></div>
            <p class="label">Total Products</p>
            <div class="d-flex align-items-baseline gap-2 flex-wrap">
                <p class="value">{{ number_format($totalProducts) }}</p>
            </div>
        </div>
    </div>

    {{-- Low Stock --}}
    <div class="col-6 col-lg-3">
        <div class="dash-card">
            <div class="card-bg-icon" style="color:#e73508;"><i class="bi bi-exclamation-triangle"></i></div>
            <p class="label">Low Stock Items</p>
            <div class="d-flex align-items-baseline gap-2 flex-wrap">
                <p class="value">{{ $lowStockCount }}</p>
                @if($lowStockCount > 0)
                <span class="badge-trend badge-warn">
                    <i class="bi bi-exclamation-circle"></i> Action needed
                </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Monthly Revenue --}}
    <div class="col-6 col-lg-3">
        <div class="dash-card">
            <div class="card-bg-icon text-primary"><i class="bi bi-graph-up"></i></div>
            <p class="label">Monthly Revenue</p>
            <div class="d-flex align-items-baseline gap-2 flex-wrap">
                <p class="value">Rs. {{ number_format($thisMonthRevenue, 0) }}</p>
                @if($monthVsLast !== null)
                <span class="badge-trend {{ $monthVsLast >= 0 ? 'badge-pos' : 'badge-neg' }}">
                    <i class="bi bi-arrow-{{ $monthVsLast >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($monthVsLast) }}%
                </span>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Chart + Low Stock Table ──────────────────────────── --}}
<div class="row g-4">

    {{-- Sales Chart --}}
    <div class="col-12 col-xl-8">
        <div class="chart-card" style="min-height:340px;">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h3 style="font-size:16px;font-weight:700;color:#121617;margin:0;">Sales Performance</h3>
                    <p style="font-size:13px;color:#667c85;margin:4px 0 0;">Revenue over the last 7 days</p>
                </div>
                <div class="text-end">
                    <p style="font-size:22px;font-weight:700;color:#121617;margin:0;">
                        Rs. {{ number_format($weekTotal, 0) }}
                    </p>
                    @if($weekVsPrev !== null)
                    <p style="font-size:12px;font-weight:600;color:{{ $weekVsPrev >= 0 ? '#078836' : '#e73508' }};margin:2px 0 0;display:flex;align-items:center;justify-content:flex-end;gap:4px;">
                        <i class="bi bi-arrow-{{ $weekVsPrev >= 0 ? 'up' : 'down' }}"></i>
                        {{ abs($weekVsPrev) }}% vs last week
                    </p>
                    @endif
                </div>
            </div>

            {{-- SVG Line Chart --}}
            @php
                $maxVal  = $chartData->max() ?: 1;
                $svgW    = 800;
                $svgH    = 260;
                $padL    = 10; $padR = 10; $padT = 20; $padB = 20;
                $points  = $chartData->values()->map(function($v, $i) use ($chartData, $maxVal, $svgW, $svgH, $padL, $padR, $padT, $padB) {
                    $n = $chartData->count();
                    $x = $padL + ($i / max($n - 1, 1)) * ($svgW - $padL - $padR);
                    $y = $padT + (1 - ($maxVal > 0 ? $v / $maxVal : 0)) * ($svgH - $padT - $padB);
                    return ['x' => round($x, 1), 'y' => round($y, 1), 'v' => $v];
                });

                // Build smooth cubic bezier path
                $pathD = ''; $areaD = '';
                foreach ($points as $i => $pt) {
                    if ($i === 0) { $pathD = "M{$pt['x']},{$pt['y']}"; continue; }
                    $prev = $points[$i - 1];
                    $cx1  = round($prev['x'] + ($pt['x'] - $prev['x']) / 3, 1);
                    $cy1  = $prev['y'];
                    $cx2  = round($pt['x'] - ($pt['x'] - $prev['x']) / 3, 1);
                    $cy2  = $pt['y'];
                    $pathD .= " C{$cx1},{$cy1} {$cx2},{$cy2} {$pt['x']},{$pt['y']}";
                }
                $last  = $points->last();
                $first = $points->first();
                $areaD = $pathD . " L{$last['x']},{$svgH} L{$first['x']},{$svgH} Z";
            @endphp

            <div style="flex:1;width:100%;min-height:200px;">
                <svg viewBox="0 0 {{ $svgW }} {{ $svgH }}" style="width:100%;height:220px;overflow:visible;">
                    <defs>
                        <linearGradient id="salesGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%"   stop-color="#1e6d8a" stop-opacity="0.18"/>
                            <stop offset="100%" stop-color="#1e6d8a" stop-opacity="0"/>
                        </linearGradient>
                    </defs>

                    {{-- Grid lines --}}
                    @foreach([0, 0.33, 0.66, 1] as $frac)
                    @php $gy = round(20 + $frac * 220); @endphp
                    <line x1="0" y1="{{ $gy }}" x2="{{ $svgW }}" y2="{{ $gy }}"
                          stroke="#e2e8f0" stroke-width="1"
                          @if($frac > 0) stroke-dasharray="4 4" @endif />
                    @endforeach

                    {{-- Area fill --}}
                    @if(strlen($areaD))
                    <path d="{{ $areaD }}" fill="url(#salesGrad)"/>
                    @endif

                    {{-- Line --}}
                    @if(strlen($pathD))
                    <path d="{{ $pathD }}" fill="none" stroke="#1e6d8a"
                          stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    @endif

                    {{-- Data points --}}
                    @foreach($points as $pt)
                    <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="4"
                            fill="#fff" stroke="#1e6d8a" stroke-width="2"/>
                    @endforeach
                </svg>
            </div>

            {{-- Day labels --}}
            <div class="d-flex justify-content-between px-1 mt-2">
                @foreach($chartLabels as $label)
                <span class="day-label">{{ $label }}</span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Low Stock Alert --}}
    <div class="col-12 col-xl-4">
        <div class="stock-card" style="height:100%;">
            <div class="s-header">
                <h3>Low Stock Alert</h3>
                <a href="{{ route('admin.reports.index') }}"
                   style="font-size:12px;font-weight:600;color:var(--pos-primary);text-decoration:none;">
                   View All
                </a>
            </div>
            <div style="overflow-x:auto;flex:1;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th style="text-align:left;">Product</th>
                            <th style="text-align:right;">Stock</th>
                            <th style="text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockProducts as $product)
                        <tr>
                            <td>
                                <div style="font-size:13px;font-weight:600;color:#121617;">{{ $product->name }}</div>
                                <div style="font-size:11px;color:#94a3b8;">SKU: {{ $product->sku }}</div>
                            </td>
                            <td style="text-align:right;">
                                @if($product->stock_quantity <= 3)
                                <span class="stock-badge-red">{{ $product->stock_quantity }} left</span>
                                @else
                                <span class="stock-badge-orange">{{ $product->stock_quantity }} left</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('admin.stock.create') }}"
                                   style="font-size:13px;font-weight:600;color:var(--pos-primary);text-decoration:none;">
                                   Restock
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align:center;padding:32px;color:#94a3b8;font-size:13px;">
                                <i class="bi bi-check-circle" style="font-size:28px;display:block;margin-bottom:8px;color:#d1fae5;"></i>
                                All products are well stocked!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection

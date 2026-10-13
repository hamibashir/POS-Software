@extends('layouts.admin')

@section('title', 'Sales History')

@push('styles')
<style>
    .stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 18px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: box-shadow .15s;
    }
    .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }
    .stat-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; flex-shrink: 0;
    }
    .stat-label { font-size: 12px; color: #9ca3af; font-weight: 500; text-transform: uppercase; letter-spacing: .6px; }
    .stat-value { font-size: 22px; font-weight: 800; color: #111827; line-height: 1.2; margin-top: 2px; }
    .stat-sub   { font-size: 11px; color: #9ca3af; margin-top: 2px; }

    .filter-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px 20px; margin-bottom:20px; }
    .filter-card form { display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; }
    .filter-card .fg { display:flex; flex-direction:column; gap:4px; }
    .filter-card label { font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.6px; }

    .inv-badge {
        font-family: 'JetBrains Mono', monospace;
        background: #eff6ff; color: #1d4ed8;
        border-radius: 6px; padding: 3px 8px; font-size: 12px;
    }
    .pay-cash   { background:#d1fae5; color:#065f46; }
    .pay-card   { background:#ede9fe; color:#5b21b6; }
    .pay-credit { background:#fef3c7; color:#92400e; }
    .pay-badge  { display:inline-flex; align-items:center; gap:4px; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }

    .status-completed { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
    .status-pending   { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
    .status-voided    { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
    .status-badge     { border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; display:inline-flex; align-items:center; gap:4px; }

    .total-amount { font-size:15px; font-weight:700; color:#111827; }
    .action-btns  { display:flex; gap:6px; }

    @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap');
</style>
@endpush

@section('content')

{{-- Page header --}}
<div class="page-hero d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h1 class="mb-0"><i class="bi bi-receipt me-2" style="color:var(--pos-primary)"></i>Sales History</h1>
            @if($isDefaultView || ($from === today()->toDateString() && $to === today()->toDateString()))
                <span class="badge bg-primary text-white" style="font-size:12px; font-weight:700; border-radius:8px; padding:4px 10px;">
                    <i class="bi bi-calendar-check me-1"></i> Today's Sales
                </span>
            @elseif($from && $to && $from === $to)
                <span class="badge bg-secondary text-white" style="font-size:12px; font-weight:700; border-radius:8px; padding:4px 10px;">
                    <i class="bi bi-calendar-date me-1"></i> Single Day: {{ \Carbon\Carbon::parse($from)->format('d M, Y') }}
                </span>
            @elseif($from || $to)
                <span class="badge bg-secondary text-white" style="font-size:12px; font-weight:700; border-radius:8px; padding:4px 10px;">
                    <i class="bi bi-calendar-range me-1"></i> Filtered Date Range
                </span>
            @else
                <span class="badge bg-dark text-white" style="font-size:12px; font-weight:700; border-radius:8px; padding:4px 10px;">
                    <i class="bi bi-infinity me-1"></i> All Time
                </span>
            @endif
        </div>
        <p class="text-muted mt-1 mb-0" style="font-size:13px;">
            @if($isDefaultView || ($from === today()->toDateString() && $to === today()->toDateString()))
                Showing transactions for today ({{ today()->format('d M, Y') }}). Use the date filters below to search previous dates or all records.
            @elseif($from && $to && $from === $to)
                Showing sales for {{ \Carbon\Carbon::parse($from)->format('d M, Y') }}.
            @elseif($from && $to)
                Showing sales from {{ \Carbon\Carbon::parse($from)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($to)->format('d M, Y') }}.
            @elseif($from)
                Showing sales from {{ \Carbon\Carbon::parse($from)->format('d M, Y') }} onwards.
            @elseif($to)
                Showing sales up to {{ \Carbon\Carbon::parse($to)->format('d M, Y') }}.
            @else
                Showing all historical sales records.
            @endif
        </p>
    </div>
    <a href="{{ route('cashier.pos') }}" class="btn-pos text-decoration-none">
        <i class="bi bi-plus-circle"></i> New Sale (POS)
    </a>
</div>

{{-- ── Summary stats ─────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dbeafe; color:#1d4ed8;"><i class="bi bi-calendar-day"></i></div>
            <div>
                <div class="stat-label">Today's Sales</div>
                <div class="stat-value">{{ $stats['today_sales'] }}</div>
                <div class="stat-sub">transactions today</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5; color:#065f46;"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="stat-label">Today's Revenue</div>
                <div class="stat-value">{{ pkr($stats['today_revenue'], 2) }}</div>
                <div class="stat-sub">collected today</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7; color:#92400e;"><i class="bi bi-graph-up"></i></div>
            <div>
                <div class="stat-label">Filtered Sales</div>
                <div class="stat-value">{{ $stats['total_sales'] }}</div>
                <div class="stat-sub">in displayed period</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ede9fe; color:#5b21b6;"><i class="bi bi-bar-chart"></i></div>
            <div>
                <div class="stat-label">Filtered Revenue</div>
                <div class="stat-value">{{ pkr($stats['total_revenue'], 2) }}</div>
                <div class="stat-sub">in displayed period</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Filters ────────────────────────────────────────────── --}}
<div class="filter-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
        <div class="d-flex align-items-center gap-1 flex-wrap">
            <span style="font-size:11.5px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px; margin-right:4px;">Quick Dates:</span>
            <a href="{{ route('admin.sales.index') }}" 
               class="btn btn-sm {{ ($isDefaultView || ($from === today()->toDateString() && $to === today()->toDateString())) ? 'btn-primary' : 'btn-outline-secondary' }}" 
               style="border-radius:6px; font-size:12px; font-weight:600; padding:3px 10px;">
                <i class="bi bi-calendar-day me-1"></i> Today
            </a>
            <a href="{{ route('admin.sales.index', ['date_from' => today()->subDay()->toDateString(), 'date_to' => today()->subDay()->toDateString()]) }}" 
               class="btn btn-sm {{ ($from === today()->subDay()->toDateString() && $to === today()->subDay()->toDateString()) ? 'btn-primary' : 'btn-outline-secondary' }}" 
               style="border-radius:6px; font-size:12px; font-weight:600; padding:3px 10px;">
                Yesterday
            </a>
            <a href="{{ route('admin.sales.index', ['date_from' => today()->startOfWeek()->toDateString(), 'date_to' => today()->toDateString()]) }}" 
               class="btn btn-sm {{ ($from === today()->startOfWeek()->toDateString() && $to === today()->toDateString()) ? 'btn-primary' : 'btn-outline-secondary' }}" 
               style="border-radius:6px; font-size:12px; font-weight:600; padding:3px 10px;">
                This Week
            </a>
            <a href="{{ route('admin.sales.index', ['date_from' => today()->startOfMonth()->toDateString(), 'date_to' => today()->toDateString()]) }}" 
               class="btn btn-sm {{ ($from === today()->startOfMonth()->toDateString() && $to === today()->toDateString()) ? 'btn-primary' : 'btn-outline-secondary' }}" 
               style="border-radius:6px; font-size:12px; font-weight:600; padding:3px 10px;">
                This Month
            </a>
            <a href="{{ route('admin.sales.index', ['all_dates' => 1]) }}" 
               class="btn btn-sm {{ (!$from && !$to && !$isDefaultView) ? 'btn-primary' : 'btn-outline-secondary' }}" 
               style="border-radius:6px; font-size:12px; font-weight:600; padding:3px 10px;">
                All Dates
            </a>
        </div>
        @if(!$isDefaultView && ($from !== today()->toDateString() || $to !== today()->toDateString()))
            <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-link text-decoration-none text-danger p-0 fw-bold" style="font-size:12px;">
                <i class="bi bi-arrow-counterclockwise"></i> Reset to Today's Sales
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('admin.sales.index') }}">
        {{-- Invoice / customer search --}}
        <div class="fg" style="flex:2; min-width:200px;">
            <label for="search">Search</label>
            <div style="position:relative;">
                <i class="bi bi-search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af;"></i>
                <input type="text" id="search" name="search" class="pos-input" style="padding-left:34px;"
                    value="{{ request('search') }}" placeholder="Invoice number or customer name…">
            </div>
        </div>

        {{-- Date from --}}
        <div class="fg">
            <label for="date_from">From Date</label>
            <input type="date" id="date_from" name="date_from" class="pos-input"
                value="{{ $from }}" style="width:160px;">
        </div>

        {{-- Date to --}}
        <div class="fg">
            <label for="date_to">To Date</label>
            <input type="date" id="date_to" name="date_to" class="pos-input"
                value="{{ $to }}" style="width:160px;">
        </div>

        {{-- Payment method --}}
        <div class="fg">
            <label for="payment_method">Payment</label>
            <select id="payment_method" name="payment_method" class="pos-input" style="width:140px;">
                <option value="">All Payments</option>
                <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>💵 Cash</option>
                <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>💳 Card</option>
                <option value="credit" {{ request('payment_method') === 'credit' ? 'selected' : '' }}>📋 Credit</option>
            </select>
        </div>

        {{-- Status --}}
        <div class="fg">
            <label for="status">Status</label>
            <select id="status" name="status" class="pos-input" style="width:130px;">
                <option value="">All</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                <option value="voided"    {{ request('status') === 'voided'    ? 'selected' : '' }}>Voided</option>
            </select>
        </div>

        <div class="fg">
            <label>&nbsp;</label>
            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn-pos">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="{{ route('admin.sales.index') }}" class="btn-pos-outline text-decoration-none" title="Reset to Today's Sales">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </div>
    </form>
</div>

{{-- ── Sales table ──────────────────────────────────────────── --}}
<div class="pos-card">
    @if($sales->isEmpty())
        <div style="padding:60px 24px; text-align:center; color:#9ca3af;">
            <i class="bi bi-receipt" style="font-size:48px; display:block; margin-bottom:12px;"></i>
            <p style="font-size:15px; font-weight:600; color:#374151;">No sales found</p>
            @if($isDefaultView || ($from === today()->toDateString() && $to === today()->toDateString()))
                <p style="font-size:13px; color:#6b7280; max-width:420px; margin:0 auto 16px;">No transactions recorded yet today. You can search previous dates using the filters above or view all historical records.</p>
                <a href="{{ route('admin.sales.index', ['all_dates' => 1]) }}" class="btn-pos-outline text-decoration-none d-inline-flex align-items-center gap-1 me-2" style="font-size:12.5px;">
                    <i class="bi bi-clock-history"></i> View All Dates
                </a>
            @else
                <p style="font-size:13px; color:#6b7280; max-width:420px; margin:0 auto 16px;">No sales found matching your current filter criteria. Try adjusting the date range or clearing filters.</p>
            @endif
            <a href="{{ route('cashier.pos') }}" class="btn-pos text-decoration-none d-inline-flex align-items-center gap-1" style="font-size:12.5px;">
                <i class="bi bi-plus-circle"></i> New Sale (POS)
            </a>
        </div>
    @else
    <table class="pos-table w-100">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Date & Time</th>
                <th>Employee</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                {{-- Invoice --}}
                <td>
                    <span class="inv-badge">{{ $sale->invoice_number }}</span>
                </td>

                {{-- Date --}}
                <td>
                    <div style="font-weight:600; font-size:13px;">{{ $sale->created_at->format('d M Y') }}</div>
                    <div style="font-size:11px; color:#9ca3af;">{{ $sale->created_at->format('g:i A') }}</div>
                </td>

                {{-- Cashier --}}
                <td style="font-size:13px;">{{ $sale->user?->name ?? '—' }}</td>

                {{-- Customer --}}
                <td style="font-size:13px;">
                    {{ $sale->customer_name ?? 'Walk-in' }}
                    @if($sale->customer_phone)
                        <div style="font-size:11px; color:#9ca3af;">{{ $sale->customer_phone }}</div>
                    @endif
                </td>

                {{-- Items count --}}
                <td>
                    <span style="background:#f3f4f6; border-radius:20px; padding:3px 10px; font-size:12px; font-weight:600;">
                        {{ $sale->items_count }} item{{ $sale->items_count !== 1 ? 's' : '' }}
                    </span>
                </td>

                {{-- Total --}}
                <td>
                    <div class="total-amount">{{ pkr($sale->total_amount, 2) }}</div>
                    @if($sale->total_returned_amount > 0)
                        <div class="mt-1">
                            <span class="badge bg-danger" style="font-size:10px; font-weight:700;">
                                <i class="bi bi-arrow-counterclockwise"></i> Return: -{{ pkr($sale->total_returned_amount, 2) }}
                            </span>
                            <div style="font-size:11px; color:#0f766e; font-weight:700; margin-top:2px;">
                                Net: {{ pkr(max(0, $sale->total_amount - $sale->total_returned_amount), 2) }}
                            </div>
                        </div>
                    @endif
                </td>

                {{-- Payment --}}
                <td>
                    @php
                        $badgeClass = match($sale->payment_method) {
                            'cash'   => 'pay-cash',
                            'card'   => 'pay-card',
                            'credit' => 'pay-credit',
                            default  => 'pay-cash',
                        };
                        $badgeIcon = match($sale->payment_method) {
                            'cash'   => '💵',
                            'card'   => '💳',
                            'credit' => '📋',
                            default  => '💰',
                        };
                    @endphp
                    <span class="pay-badge {{ $badgeClass }}">
                        {{ $badgeIcon }}
                        {{ strtoupper($sale->payment_method) }}
                    </span>
                </td>

                {{-- Status --}}
                <td>
                    @php
                        $st = $sale->display_status;
                    @endphp
                    @if($st === 'pending')
                        <span class="status-badge status-pending" title="Payment Pending">
                            <i class="bi bi-clock-history"></i> Pending
                        </span>
                    @elseif($st === 'voided')
                        <span class="status-badge status-voided">
                            <i class="bi bi-x-circle"></i> Voided
                        </span>
                    @else
                        <span class="status-badge status-completed">
                            <i class="bi bi-check-circle"></i> Completed
                        </span>
                    @endif
                </td>

                {{-- Actions --}}
                <td>
                    <div class="action-btns">
                        {{-- View detail --}}
                        <a href="{{ route('admin.sales.show', $sale) }}"
                           class="btn-pos-outline text-decoration-none"
                           style="padding:5px 10px; font-size:12px;"
                           title="View Details">
                            <i class="bi bi-eye"></i>
                        </a>
                        {{-- Reprint receipt --}}
                        <a href="{{ route('cashier.pos.receipt', $sale) }}"
                           target="_blank" rel="noopener"
                           class="btn-pos text-decoration-none"
                           style="padding:5px 10px; font-size:12px; background:#6b7280;"
                           title="Print Receipt">
                            <i class="bi bi-printer"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Pagination --}}
    @if($sales->hasPages())
    <div style="padding:16px 20px; border-top:1px solid #f3f4f6;">
        {{ $sales->links() }}
    </div>
    @endif
    @endif
</div>

@endsection

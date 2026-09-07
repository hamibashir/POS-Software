@extends('layouts.admin')

@section('title', 'Stock Returns')

@push('styles')
<style>
    .stat-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px 20px; display:flex; align-items:center; gap:14px; }
    .stat-icon  { width:46px; height:46px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
    .stat-label { font-size:11px; color:#9ca3af; font-weight:600; text-transform:uppercase; letter-spacing:.6px; }
    .stat-value { font-size:20px; font-weight:800; color:#111827; }
    .stat-sub   { font-size:11px; color:#9ca3af; }

    .filter-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:14px 18px; margin-bottom:18px; }
    .filter-card form { display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; }
    .filter-card .fg { display:flex; flex-direction:column; gap:4px; }
    .filter-card label { font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.6px; }

    .ref-badge { font-family:monospace; background:#fff1f2; color:#be123c; border-radius:6px; padding:3px 8px; font-size:12px; font-weight:700; text-decoration:none; }
    .ref-badge:hover { color:#9f1239; background:#ffe4e6; }
    .status-badge { border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }
    .status-completed { background:#d1fae5; color:#065f46; }
    .status-pending { background:#fef3c7; color:#92400e; }
    .pay-cash { background:#d1fae5; color:#065f46; }
    .pay-card { background:#ede9fe; color:#5b21b6; }
    .pay-credit { background:#fef3c7; color:#92400e; }
    .pay-badge  { display:inline-flex; align-items:center; gap:4px; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }
</style>
@endpush

@section('content')

<div class="page-hero d-flex align-items-center justify-content-between">
    <div>
        <h1><i class="bi bi-arrow-return-left me-2" style="color:var(--pos-primary)"></i>Stock Returns</h1>
        <p>Return defective or surplus inventory to suppliers and track refunds.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.purchases.index') }}" class="btn-pos-outline text-decoration-none">
            <i class="bi bi-truck"></i> Purchases
        </a>
        <a href="{{ route('admin.purchase-returns.create') }}" class="btn-pos text-decoration-none">
            <i class="bi bi-plus-circle"></i> New Stock Return
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2; color:#b91c1c;"><i class="bi bi-arrow-return-left"></i></div>
            <div>
                <div class="stat-label">Total Returns</div>
                <div class="stat-value">{{ $stats['total_returns'] }}</div>
                <div class="stat-sub">records</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7; color:#b45309;"><i class="bi bi-calendar-event"></i></div>
            <div>
                <div class="stat-label">Today's Returns</div>
                <div class="stat-value">{{ $stats['today_returns'] }}</div>
                <div class="stat-sub">{{ pkr($stats['today_refunded'], 2) }} refunded</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ede9fe; color:#6d28d9;"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="stat-label">Total Return Value</div>
                <div class="stat-value">{{ pkr($stats['total_return_amount'], 2) }}</div>
                <div class="stat-sub">cost value of goods</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5; color:#047857;"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="stat-label">Total Refunded</div>
                <div class="stat-value">{{ pkr($stats['total_refunded'], 2) }}</div>
                <div class="stat-sub">recovered payments</div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="pos-alert pos-alert-success mb-3">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" action="{{ route('admin.purchase-returns.index') }}">
        <div class="fg" style="flex:2; min-width:200px;">
            <label>Search Return</label>
            <div style="position:relative;">
                <i class="bi bi-search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af;"></i>
                <input type="text" name="search" class="pos-input" style="padding-left:34px;"
                    value="{{ request('search') }}" placeholder="Reference number or supplier name…">
            </div>
        </div>

        <div class="fg">
            <label>From Date</label>
            <input type="date" name="date_from" class="pos-input" value="{{ request('date_from') }}" style="width:140px;">
        </div>

        <div class="fg">
            <label>To Date</label>
            <input type="date" name="date_to" class="pos-input" value="{{ request('date_to') }}" style="width:140px;">
        </div>

        <div class="fg">
            <label>Refund Method</label>
            <select name="refund_method" class="pos-input" style="width:130px;">
                <option value="">All Methods</option>
                <option value="cash"   {{ request('refund_method') === 'cash'   ? 'selected' : '' }}>Cash</option>
                <option value="card"   {{ request('refund_method') === 'card'   ? 'selected' : '' }}>Card</option>
                <option value="credit" {{ request('refund_method') === 'credit' ? 'selected' : '' }}>Credit / Balance</option>
            </select>
        </div>

        <div class="fg">
            <label>Status</label>
            <select name="refund_status" class="pos-input" style="width:130px;">
                <option value="">All Statuses</option>
                <option value="completed" {{ request('refund_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="pending"   {{ request('refund_status') === 'pending'   ? 'selected' : '' }}>Pending</option>
            </select>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn-pos"><i class="bi bi-funnel"></i> Filter</button>
            @if(request()->hasAny(['search', 'date_from', 'date_to', 'refund_method', 'refund_status']))
            <a href="{{ route('admin.purchase-returns.index') }}" class="btn-pos-outline text-decoration-none">Clear</a>
            @endif
        </div>
    </form>
</div>

{{-- Table --}}
<div class="pos-card">
    <div style="overflow-x:auto;">
        <table class="pos-table w-100">
            <thead>
                <tr>
                    <th>Return Ref #</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Linked Purchase</th>
                    <th style="text-align:center;">Items</th>
                    <th style="text-align:right;">Return Value</th>
                    <th style="text-align:right;">Refunded</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $return)
                <tr>
                    <td>
                        <a href="{{ route('admin.purchase-returns.show', $return) }}" class="ref-badge">
                            {{ $return->reference_number }}
                        </a>
                    </td>
                    <td>
                        <span style="font-weight:600; color:#111827;">{{ $return->returned_at ? $return->returned_at->format('d M Y') : $return->created_at->format('d M Y') }}</span>
                        <div style="font-size:11px; color:#9ca3af;">{{ $return->created_at->format('g:i A') }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600; color:#111827;">{{ $return->supplier_name }}</div>
                        @if($return->supplier_phone)
                        <div style="font-size:11px; color:#9ca3af;"><i class="bi bi-telephone"></i> {{ $return->supplier_phone }}</div>
                        @endif
                    </td>
                    <td>
                        @if($return->purchase)
                        <a href="{{ route('admin.purchases.show', $return->purchase) }}" style="font-family:monospace; font-size:12px; color:var(--pos-primary); text-decoration:none; font-weight:600;">
                            <i class="bi bi-truck"></i> {{ $return->purchase->reference_number }}
                        </a>
                        @else
                        <span style="color:#9ca3af; font-size:12px;">Direct Return</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <span style="background:#f3f4f6; color:#374151; border-radius:12px; padding:2px 8px; font-size:11px; font-weight:700;">
                            {{ $return->items_count }} {{ Str::plural('item', $return->items_count) }}
                        </span>
                    </td>
                    <td style="text-align:right; font-weight:700; color:#b91c1c;">
                        -{{ pkr($return->total_return_amount, 2) }}
                    </td>
                    <td style="text-align:right; font-weight:700; color:#047857;">
                        {{ pkr($return->refund_amount, 2) }}
                    </td>
                    <td>
                        <span class="pay-badge pay-{{ $return->refund_method }}">
                            {{ $return->refund_method === 'cash' ? '💵 Cash' : ($return->refund_method === 'card' ? '💳 Card' : '📋 Credit') }}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-{{ $return->refund_status }}">
                            {{ ucfirst($return->refund_status) }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <a href="{{ route('admin.purchase-returns.show', $return) }}" class="btn-pos-outline" style="padding:4px 10px; font-size:12px; text-decoration:none;">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align:center; padding:48px 16px; color:#9ca3af;">
                        <i class="bi bi-arrow-return-left" style="font-size:36px; display:block; margin-bottom:8px; opacity:.4;"></i>
                        <p style="font-size:15px; font-weight:600; color:#374151; margin-bottom:4px;">No stock returns found</p>
                        <p style="font-size:13px; margin:0;">Create a stock return whenever inventory is returned to suppliers.</p>
                        <a href="{{ route('admin.purchase-returns.create') }}" class="btn-pos text-decoration-none mt-3" style="font-size:13px;">
                            <i class="bi bi-plus-circle"></i> Create First Stock Return
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($returns->hasPages())
    <div style="padding:16px 20px; border-top:1px solid #f3f4f6;">
        {{ $returns->links() }}
    </div>
    @endif
</div>

@endsection

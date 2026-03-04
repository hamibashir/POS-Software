@extends('layouts.admin')

@section('title', 'Purchases')

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

    .ref-badge { font-family:monospace; background:#fef9ee; color:#92400e; border-radius:6px; padding:3px 8px; font-size:12px; font-weight:700; }
    .status-badge { border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }
    .status-received { background:#d1fae5; color:#065f46; }
    .pay-cash { background:#d1fae5; color:#065f46; }
    .pay-card { background:#ede9fe; color:#5b21b6; }
    .pay-credit { background:#fef3c7; color:#92400e; }
    .pay-badge  { display:inline-flex; align-items:center; gap:4px; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }
</style>
@endpush

@section('content')

<div class="page-hero d-flex align-items-center justify-content-between">
    <div>
        <h1><i class="bi bi-truck me-2" style="color:var(--pos-primary)"></i>Purchases</h1>
        <p>Stock-in entries and supplier orders.</p>
    </div>
    <a href="{{ route('admin.purchases.create') }}" class="btn-pos text-decoration-none">
        <i class="bi bi-plus-circle"></i> New Purchase
    </a>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dbeafe; color:#1d4ed8;"><i class="bi bi-calendar-day"></i></div>
            <div>
                <div class="stat-label">Today's Orders</div>
                <div class="stat-value">{{ $stats['today_purchases'] }}</div>
                <div class="stat-sub">purchases</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7; color:#92400e;"><i class="bi bi-cash-coin"></i></div>
            <div>
                <div class="stat-label">Today's Cost</div>
                <div class="stat-value">${{ number_format($stats['today_cost'], 2) }}</div>
                <div class="stat-sub">spent today</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5; color:#065f46;"><i class="bi bi-boxes"></i></div>
            <div>
                <div class="stat-label">Total Orders</div>
                <div class="stat-value">{{ $stats['total_purchases'] }}</div>
                <div class="stat-sub">all time</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ede9fe; color:#5b21b6;"><i class="bi bi-currency-dollar"></i></div>
            <div>
                <div class="stat-label">Total Spent</div>
                <div class="stat-value">${{ number_format($stats['total_cost'], 2) }}</div>
                <div class="stat-sub">all time</div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" action="{{ route('admin.purchases.index') }}">
        <div class="fg" style="flex:2; min-width:200px;">
            <label>Search</label>
            <div style="position:relative;">
                <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;"></i>
                <input type="text" name="search" class="pos-input" style="padding-left:34px;"
                    value="{{ request('search') }}" placeholder="Reference number or supplier name…">
            </div>
        </div>
        <div class="fg">
            <label>From Date</label>
            <input type="date" name="date_from" class="pos-input" value="{{ request('date_from') }}" style="width:150px;">
        </div>
        <div class="fg">
            <label>To Date</label>
            <input type="date" name="date_to" class="pos-input" value="{{ request('date_to') }}" style="width:150px;">
        </div>
        <div class="fg">
            <label>&nbsp;</label>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn-pos"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('admin.purchases.index') }}" class="btn-pos-outline text-decoration-none"><i class="bi bi-x"></i></a>
            </div>
        </div>
    </form>
</div>

{{-- Flash --}}
@if(session('success'))
<div class="pos-alert pos-alert-success mb-3">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

{{-- Table --}}
<div class="pos-card">
    @if($purchases->isEmpty())
        <div style="padding:60px;text-align:center;color:#9ca3af;">
            <i class="bi bi-truck" style="font-size:48px;display:block;margin-bottom:12px;"></i>
            <p style="font-size:15px;font-weight:600;">No purchase orders yet</p>
            <a href="{{ route('admin.purchases.create') }}" style="color:var(--pos-primary);">Record your first purchase →</a>
        </div>
    @else
    <table class="pos-table w-100">
        <thead>
            <tr>
                <th>Reference</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Items</th>
                <th>Total Cost</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchases as $purchase)
            <tr>
                <td><span class="ref-badge">{{ $purchase->reference_number }}</span></td>
                <td>
                    <div style="font-weight:600;font-size:13px;">{{ $purchase->created_at->format('d M Y') }}</div>
                    <div style="font-size:11px;color:#9ca3af;">{{ $purchase->created_at->format('g:i A') }}</div>
                </td>
                <td>
                    <div style="font-weight:600;font-size:13px;">{{ $purchase->supplier_name }}</div>
                    @if($purchase->supplier_phone)
                        <div style="font-size:11px;color:#9ca3af;">{{ $purchase->supplier_phone }}</div>
                    @endif
                </td>
                <td>
                    <span style="background:#f3f4f6;border-radius:20px;padding:3px 10px;font-size:12px;font-weight:600;">
                        {{ $purchase->items_count }} item{{ $purchase->items_count !== 1 ? 's' : '' }}
                    </span>
                </td>
                <td style="font-size:15px;font-weight:700;color:#111827;">${{ number_format($purchase->total_amount, 2) }}</td>
                <td>
                    @php $pm = $purchase->payment_method; @endphp
                    <span class="pay-badge {{ $pm === 'cash' ? 'pay-cash' : ($pm === 'card' ? 'pay-card' : 'pay-credit') }}">
                        {{ $pm === 'cash' ? '💵' : ($pm === 'card' ? '💳' : '📋') }} {{ strtoupper($pm) }}
                    </span>
                </td>
                <td>
                    <span class="status-badge status-received">{{ ucfirst($purchase->status) }}</span>
                </td>
                <td>
                    <a href="{{ route('admin.purchases.show', $purchase) }}"
                       class="btn-pos-outline text-decoration-none" style="padding:5px 10px;font-size:12px;" title="View">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($purchases->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #f3f4f6;">{{ $purchases->links() }}</div>
    @endif
    @endif
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Customer Sale Returns')

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

    .ref-badge { font-family:monospace; background:#fef2f2; color:#b91c1c; border-radius:6px; padding:3px 8px; font-size:12px; font-weight:700; text-decoration:none; }
    .ref-badge:hover { color:#991b1b; background:#fee2e2; }
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
        <h1><i class="bi bi-arrow-counterclockwise me-2" style="color:#b91c1c;"></i>Customer Sale Returns</h1>
        <p>Audit customer returned products, stock restorations, and refund disbursement records.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.sales.index') }}" class="btn-pos-outline text-decoration-none">
            <i class="bi bi-receipt"></i> Sales History
        </a>
        <a href="{{ route('cashier.pos') }}" class="btn-pos text-decoration-none" style="background:#b91c1c;">
            <i class="bi bi-cart3"></i> Open POS Workstation
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2; color:#b91c1c;"><i class="bi bi-arrow-counterclockwise"></i></div>
            <div>
                <div class="stat-label">Total Returns</div>
                <div class="stat-value">{{ $stats['total_returns'] }}</div>
                <div class="stat-sub">audit records</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7; color:#b45309;"><i class="bi bi-calendar-event"></i></div>
            <div>
                <div class="stat-label">Today's Returns</div>
                <div class="stat-value">{{ $stats['today_returns'] }}</div>
                <div class="stat-sub">{{ pkr($stats['today_refund_amt'], 2) }} refunded</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ede9fe; color:#6d28d9;"><i class="bi bi-cash-coin"></i></div>
            <div>
                <div class="stat-label">Total Refunded Value</div>
                <div class="stat-value">{{ pkr($stats['total_refund_amt'], 2) }}</div>
                <div class="stat-sub">disbursed to customers</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5; color:#047857;"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="stat-label">Restored Stock</div>
                <div class="stat-value">{{ $returns->total() }}</div>
                <div class="stat-sub">inventory batches</div>
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
    <form method="GET" action="{{ route('admin.sale-returns.index') }}">
        <div class="fg" style="flex:2; min-width:200px;">
            <label>Search Voucher / Customer / Invoice</label>
            <input type="text" name="search" class="pos-input" placeholder="e.g. RET-20260311, Ali Khan, INV-..." value="{{ request('search') }}">
        </div>
        <div class="fg" style="min-width:140px;">
            <label>From Date</label>
            <input type="date" name="date_from" class="pos-input" value="{{ request('date_from') }}">
        </div>
        <div class="fg" style="min-width:140px;">
            <label>To Date</label>
            <input type="date" name="date_to" class="pos-input" value="{{ request('date_to') }}">
        </div>
        <div class="fg" style="min-width:150px;">
            <label>Refund Method</label>
            <select name="refund_method" class="pos-input">
                <option value="">All Methods</option>
                <option value="cash" {{ request('refund_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="card" {{ request('refund_method') === 'card' ? 'selected' : '' }}>Card / Bank</option>
                <option value="credit_adjustment" {{ request('refund_method') === 'credit_adjustment' ? 'selected' : '' }}>Credit Adjustment</option>
            </select>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn-pos"><i class="bi bi-funnel"></i> Filter</button>
            @if(request()->hasAny(['search', 'date_from', 'date_to', 'refund_method', 'reason']))
                <a href="{{ route('admin.sale-returns.index') }}" class="btn-pos-outline text-decoration-none">Clear</a>
            @endif
        </div>
    </form>
</div>

{{-- Returns Table --}}
<div class="pos-card p-0">
    <div class="table-responsive">
        <table class="pos-table w-100">
            <thead>
                <tr>
                    <th>Return Voucher #</th>
                    <th>Date & Time</th>
                    <th>Customer Details</th>
                    <th>Original Invoice</th>
                    <th>Items</th>
                    <th class="text-end">Refund Amount</th>
                    <th>Refund Method</th>
                    <th>Processed By</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $ret)
                <tr>
                    <td>
                        <a href="{{ route('admin.sale-returns.show', $ret->id) }}" class="ref-badge">
                            {{ $ret->return_number }}
                        </a>
                    </td>
                    <td>
                        <div style="font-weight:600; color:#111827;">{{ $ret->returned_at ? \Carbon\Carbon::parse($ret->returned_at)->format('d M Y') : $ret->created_at->format('d M Y') }}</div>
                        <div style="font-size:11px; color:#9ca3af;">{{ $ret->created_at->format('g:i A') }}</div>
                    </td>
                    <td>
                        <div style="font-weight:700; color:#111827;">{{ $ret->customer_name }}</div>
                        @if($ret->customer_phone)
                            <div style="font-size:11px; color:#6b7280;"><i class="bi bi-telephone text-muted me-1"></i>{{ $ret->customer_phone }}</div>
                        @endif
                        @if($ret->employee)
                            <div style="font-size:10.5px; color:#0f766e; font-weight:600;">Authorized Customer: {{ $ret->employee->name }}</div>
                        @endif
                    </td>
                    <td>
                        @if($ret->sale)
                            <a href="{{ route('admin.sales.show', $ret->sale->id) }}" class="text-decoration-none fw-bold" style="font-family:monospace; color:#0f766e;">
                                {{ $ret->sale->invoice_number }}
                            </a>
                        @else
                            <span class="badge bg-light text-secondary border">Direct Return</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border fw-bold">
                            {{ $ret->items_count }} {{ Str::plural('item', $ret->items_count) }}
                        </span>
                        <div style="font-size:11px; color:#6b7280; margin-top:2px;">{{ $ret->items->sum('quantity') }} total units</div>
                    </td>
                    <td class="text-end">
                        <span style="font-weight:800; font-size:15px; color:#b91c1c;">
                            -{{ pkr($ret->total_return_amount, 2) }}
                        </span>
                    </td>
                    <td>
                        <span class="pay-badge {{ match($ret->refund_method) {
                            'cash'              => 'pay-cash',
                            'card'              => 'pay-card',
                            'credit_adjustment' => 'pay-credit',
                            default             => 'pay-cash'
                        } }}">
                            <i class="bi bi-{{ match($ret->refund_method) {
                                'cash'              => 'cash-stack',
                                'card'              => 'credit-card',
                                'credit_adjustment' => 'person-lines-fill',
                                default             => 'cash'
                            } }}"></i>
                            {{ match($ret->refund_method) {
                                'cash'              => 'Cash',
                                'card'              => 'Card',
                                'credit_adjustment' => 'Credit Due Adj',
                                default             => ucfirst($ret->refund_method)
                            } }}
                        </span>
                    </td>
                    <td>
                        <div style="font-size:12.5px; font-weight:600; color:#374151;">{{ $ret->user?->name ?? 'System' }}</div>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('admin.sale-returns.show', $ret->id) }}" class="btn btn-sm btn-outline-secondary" title="View Audit Details">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('cashier.pos.return-receipt', $ret->id) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Print Return Voucher">
                                <i class="bi bi-printer"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                        No customer product returns found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($returns->hasPages())
    <div class="p-3 border-top">
        {{ $returns->links() }}
    </div>
    @endif
</div>

@endsection

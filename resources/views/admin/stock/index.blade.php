@extends('layouts.admin')
@section('title', 'Stock Adjustments')

@push('styles')
<style>
    .filter-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:14px 18px; margin-bottom:18px; }
    .filter-card form { display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; }
    .filter-card .fg { display:flex; flex-direction:column; gap:4px; }
    .filter-card label { font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.6px; }
    .badge-in  { background:#d1fae5; color:#065f46; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }
    .badge-out { background:#fee2e2; color:#991b1b; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }
    .mono { font-family:monospace; font-size:12px; color:#374151; }
</style>
@endpush

@section('content')
<div class="page-hero d-flex align-items-center justify-content-between">
    <div>
        <h1><i class="bi bi-arrow-left-right me-2" style="color:var(--pos-primary)"></i>Stock Adjustments</h1>
        <p>Manual inventory increases and decreases with reason tracking.</p>
    </div>
    <a href="{{ route('admin.stock.create') }}" class="btn-pos text-decoration-none">
        <i class="bi bi-plus-circle"></i> New Adjustment
    </a>
</div>

@if(session('success'))
<div class="pos-alert pos-alert-success mb-3">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" action="{{ route('admin.stock.index') }}">
        <div class="fg" style="flex:2;min-width:200px;">
            <label>Search Product</label>
            <div style="position:relative;">
                <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;"></i>
                <input type="text" name="search" class="pos-input" style="padding-left:34px;"
                    value="{{ request('search') }}" placeholder="Product name or SKU…">
            </div>
        </div>
        <div class="fg">
            <label>From</label>
            <input type="date" name="date_from" class="pos-input" value="{{ request('date_from') }}" style="width:150px;">
        </div>
        <div class="fg">
            <label>To</label>
            <input type="date" name="date_to" class="pos-input" value="{{ request('date_to') }}" style="width:150px;">
        </div>
        <div class="fg">
            <label>Type</label>
            <select name="type" class="pos-input" style="width:160px;">
                <option value="">All Types</option>
                <option value="adjustment_in"  {{ request('type') === 'adjustment_in'  ? 'selected' : '' }}>Stock In  (+)</option>
                <option value="adjustment_out" {{ request('type') === 'adjustment_out' ? 'selected' : '' }}>Stock Out (−)</option>
                <option value="return"         {{ request('type') === 'return'         ? 'selected' : '' }}>Supplier Return (↩)</option>
            </select>
        </div>
        <div class="fg">
            <label>&nbsp;</label>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn-pos"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('admin.stock.index') }}" class="btn-pos-outline text-decoration-none"><i class="bi bi-x"></i></a>
            </div>
        </div>
    </form>
</div>

<div class="pos-card">
    @if($movements->isEmpty())
        <div style="padding:60px;text-align:center;color:#9ca3af;">
            <i class="bi bi-arrow-left-right" style="font-size:48px;display:block;margin-bottom:12px;"></i>
            <p style="font-size:15px;font-weight:600;">No adjustments recorded yet</p>
            <a href="{{ route('admin.stock.create') }}" style="color:var(--pos-primary);">Create your first adjustment →</a>
        </div>
    @else
    <table class="pos-table w-100">
        <thead>
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Type</th>
                <th>Qty</th>
                <th>Before</th>
                <th>After</th>
                <th>Reason / Notes</th>
                <th>By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $m)
            <tr>
                <td>
                    <div style="font-weight:600;font-size:13px;">{{ $m->created_at->format('d M Y') }}</div>
                    <div style="font-size:11px;color:#9ca3af;">{{ $m->created_at->format('g:i A') }}</div>
                </td>
                <td>
                    <div style="font-weight:600;">{{ $m->product?->name ?? '—' }}</div>
                    <div class="mono">{{ $m->product?->sku }}</div>
                </td>
                <td>
                    @if($m->type === 'adjustment_in')
                        <span class="badge-in">⬆ Stock In</span>
                    @elseif($m->type === 'return')
                        <span class="badge" style="background:#fff1f2; color:#b91c1c; border:1px solid #fecdd3; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700;">
                            <i class="bi bi-arrow-return-left"></i> Return
                        </span>
                    @else
                        <span class="badge-out">⬇ Stock Out</span>
                    @endif
                </td>
                <td style="font-weight:700;font-size:15px; color:{{ $m->type === 'adjustment_in' ? '#065f46' : '#991b1b' }};">
                    {{ $m->type === 'adjustment_in' ? '+' : '−' }}{{ abs($m->quantity) }}
                </td>
                <td style="color:#9ca3af;">{{ $m->stock_before }}</td>
                <td style="font-weight:600;color:{{ $m->type === 'adjustment_in' ? '#065f46' : '#991b1b' }};">
                    {{ $m->stock_after }}
                </td>
                <td style="font-size:13px;color:#374151;max-width:280px;">{{ $m->notes }}</td>
                <td style="font-size:13px;">{{ $m->user?->name ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($movements->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #f3f4f6;">{{ $movements->links() }}</div>
    @endif
    @endif
</div>
@endsection

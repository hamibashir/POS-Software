@extends('layouts.admin')

@section('title', 'Purchase ' . $purchase->reference_number)

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap');

    .detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px; }
    @media(max-width:768px){ .detail-grid{ grid-template-columns:1fr; } }

    .detail-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; }
    .detail-card-header { background:#f9fafb; border-bottom:1px solid #e5e7eb; padding:12px 18px; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:1px; display:flex; align-items:center; gap:8px; }
    .detail-card-body { padding:18px; }

    .info-row { display:flex; justify-content:space-between; align-items:flex-start; padding:8px 0; border-bottom:1px solid #f9fafb; font-size:14px; }
    .info-row:last-child { border-bottom:none; }
    .info-row .lbl { color:#9ca3af; font-size:12px; font-weight:500; }
    .info-row .val { font-weight:600; color:#111827; text-align:right; }

    .ref-hero { font-family:'JetBrains Mono',monospace; font-size:22px; font-weight:700; color:#92400e; background:#fef9ee; border-radius:8px; padding:8px 16px; display:inline-block; }

    .pay-cash  { background:#d1fae5; color:#065f46; }
    .pay-card  { background:#ede9fe; color:#5b21b6; }
    .pay-credit{ background:#fef3c7; color:#92400e; }
    .pay-badge { display:inline-flex; align-items:center; gap:4px; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:700; }
    .status-badge { border-radius:20px; padding:4px 12px; font-size:12px; font-weight:700; }
    .status-received { background:#d1fae5; color:#065f46; }

    .items-detail-table { width:100%; border-collapse:collapse; }
    .items-detail-table thead th { background:#f9fafb; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#6b7280; padding:10px 16px; border-bottom:1px solid #e5e7eb; }
    .items-detail-table tbody td { padding:14px 16px; font-size:14px; color:#374151; border-bottom:1px solid #f9fafb; vertical-align:middle; }
    .items-detail-table tbody tr:last-child td { border-bottom:none; }
    .item-name { font-weight:700; color:#111827; }
    .item-sku  { font-size:11px; color:#9ca3af; font-family:'JetBrains Mono',monospace; }

    .t-row-foot { display:flex; justify-content:space-between; padding:8px 16px; font-size:13px; color:#374151; }
    .t-row-foot.grand { border-top:2px solid #111827; margin:4px 0; padding-top:12px; font-size:18px; font-weight:800; color:#111827; }

    .stock-badge { display:inline-flex; align-items:center; gap:4px; background:#dbeafe; color:#1d4ed8; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="pos-alert pos-alert-success mb-3">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

{{-- Breadcrumb --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <nav style="font-size:13px;color:#9ca3af;">
        <a href="{{ route('admin.purchases.index') }}" style="color:var(--pos-primary);text-decoration:none;font-weight:600;">
            <i class="bi bi-arrow-left"></i> Purchases
        </a>
        <span class="mx-2">·</span>
        <span style="color:#374151;font-weight:600;">{{ $purchase->reference_number }}</span>
    </nav>
</div>

<div class="mb-3">
    <div class="ref-hero">{{ $purchase->reference_number }}</div>
    <span class="status-badge status-received ms-2">{{ ucfirst($purchase->status) }}</span>
</div>

{{-- Info grid --}}
<div class="detail-grid">
    <div class="detail-card">
        <div class="detail-card-header"><i class="bi bi-info-circle"></i> Purchase Information</div>
        <div class="detail-card-body">
            <div class="info-row">
                <span class="lbl">Date Created</span>
                <span class="val">{{ $purchase->created_at->format('d M Y, g:i A') }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Received Date</span>
                <span class="val">{{ $purchase->received_at ? \Carbon\Carbon::parse($purchase->received_at)->format('d M Y') : '—' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Created By</span>
                <span class="val">{{ $purchase->user?->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Payment Method</span>
                <span class="val">
                    @php $pm = $purchase->payment_method; @endphp
                    <span class="pay-badge {{ $pm === 'cash' ? 'pay-cash' : ($pm === 'card' ? 'pay-card' : 'pay-credit') }}">
                        {{ $pm === 'cash' ? '💵' : ($pm === 'card' ? '💳' : '📋') }} {{ strtoupper($pm) }}
                    </span>
                </span>
            </div>
            @if($purchase->notes)
            <div class="info-row">
                <span class="lbl">Notes</span>
                <span class="val" style="font-weight:400;color:#6b7280;max-width:60%;text-align:right;">{{ $purchase->notes }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-card-header"><i class="bi bi-building"></i> Supplier Details</div>
        <div class="detail-card-body">
            <div class="info-row">
                <span class="lbl">Supplier Name</span>
                <span class="val">{{ $purchase->supplier_name }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Phone</span>
                <span class="val">{{ $purchase->supplier_phone ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Product Lines</span>
                <span class="val">{{ $purchase->items->count() }} line(s)</span>
            </div>
            <div class="info-row">
                <span class="lbl">Total Units</span>
                <span class="val">
                    <span class="stock-badge">
                        <i class="bi bi-arrow-up-circle"></i>+{{ $purchase->items->sum('quantity') }} units stocked
                    </span>
                </span>
            </div>
            <div class="info-row">
                <span class="lbl">Total Cost</span>
                <span class="val" style="color:#92400e;font-size:18px;">${{ number_format($purchase->total_amount, 2) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Items table --}}
<div class="detail-card mb-4">
    <div class="detail-card-header"><i class="bi bi-list-ul"></i> Items Received ({{ $purchase->items->count() }})</div>
    <table class="items-detail-table">
        <thead>
            <tr>
                <th style="text-align:left; width:40%;">Product</th>
                <th style="text-align:right;">Unit Cost</th>
                <th style="text-align:right;">Qty Received</th>
                <th style="text-align:right;">Line Total</th>
                <th style="text-align:right;">Stock Added</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $item)
            <tr>
                <td>
                    <div class="item-name">{{ $item->product_name }}</div>
                    <div class="item-sku">{{ $item->product_sku }} · {{ strtoupper($item->product_unit) }}</div>
                </td>
                <td style="text-align:right;">${{ number_format($item->unit_cost, 2) }}</td>
                <td style="text-align:right; font-weight:700;">{{ $item->quantity }}</td>
                <td style="text-align:right; font-weight:700; color:#111827;">${{ number_format($item->total_cost, 2) }}</td>
                <td style="text-align:right;">
                    <span class="stock-badge">+{{ $item->quantity }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals footer --}}
    <div style="border-top:1.5px dashed #e5e7eb; padding:14px 0 8px;">
        <div class="t-row-foot">
            <span>Total Units Received</span>
            <span>{{ $purchase->items->sum('quantity') }} pcs</span>
        </div>
        <div class="t-row-foot grand">
            <span>TOTAL COST</span>
            <span>${{ number_format($purchase->total_amount, 2) }}</span>
        </div>
    </div>
</div>

@endsection

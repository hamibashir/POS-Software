@extends('layouts.admin')

@section('title', 'Return ' . $purchaseReturn->reference_number)

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

    .ref-hero { font-family:'JetBrains Mono',monospace; font-size:22px; font-weight:700; color:#b91c1c; background:#fff1f2; border-radius:8px; padding:8px 16px; display:inline-block; }

    .pay-cash  { background:#d1fae5; color:#065f46; }
    .pay-card  { background:#ede9fe; color:#5b21b6; }
    .pay-credit{ background:#fef3c7; color:#92400e; }
    .pay-badge { display:inline-flex; align-items:center; gap:4px; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:700; }
    .status-badge { border-radius:20px; padding:4px 12px; font-size:12px; font-weight:700; }
    .status-completed { background:#d1fae5; color:#065f46; }
    .status-pending   { background:#fef3c7; color:#92400e; }

    .items-detail-table { width:100%; border-collapse:collapse; }
    .items-detail-table thead th { background:#f9fafb; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#6b7280; padding:10px 16px; border-bottom:1px solid #e5e7eb; }
    .items-detail-table tbody td { padding:14px 16px; font-size:14px; color:#374151; border-bottom:1px solid #f9fafb; vertical-align:middle; }
    .items-detail-table tbody tr:last-child td { border-bottom:none; }
    .item-name { font-weight:700; color:#111827; }
    .item-sku  { font-size:11px; color:#9ca3af; font-family:'JetBrains Mono',monospace; }

    .t-row-foot { display:flex; justify-content:space-between; padding:8px 16px; font-size:13px; color:#374151; }
    .t-row-foot.grand { border-top:2px solid #111827; margin:4px 0; padding-top:12px; font-size:18px; font-weight:800; color:#111827; }

    .stock-deduct-badge { display:inline-flex; align-items:center; gap:4px; background:#fee2e2; color:#b91c1c; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }

    @media print {
        .pos-topbar, .no-print { display: none !important; }
        .pos-page { margin-top: 0; padding: 0; }
    }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="pos-alert pos-alert-success mb-3 no-print">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

{{-- Breadcrumb & Actions --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2 no-print">
    <nav style="font-size:13px; color:#9ca3af;">
        <a href="{{ route('admin.purchase-returns.index') }}" style="color:var(--pos-primary); text-decoration:none; font-weight:600;">
            <i class="bi bi-arrow-left"></i> Stock Returns
        </a>
        <span class="mx-2">·</span>
        <span style="color:#374151; font-weight:600;">{{ $purchaseReturn->reference_number }}</span>
    </nav>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn-pos-outline">
            <i class="bi bi-printer"></i> Print Slip
        </button>
        <a href="{{ route('admin.purchase-returns.create') }}" class="btn-pos text-decoration-none">
            <i class="bi bi-plus-circle"></i> New Return
        </a>
    </div>
</div>

<div class="mb-4">
    <div class="ref-hero">{{ $purchaseReturn->reference_number }}</div>
    <span class="status-badge status-{{ $purchaseReturn->refund_status }} ms-2">
        Refund: {{ ucfirst($purchaseReturn->refund_status) }}
    </span>
    <span class="stock-deduct-badge ms-2">
        <i class="bi bi-arrow-down-circle"></i> Stock Deducted
    </span>
</div>

{{-- Info Grid --}}
<div class="detail-grid">
    <div class="detail-card">
        <div class="detail-card-header"><i class="bi bi-info-circle"></i> Return & Payment Info</div>
        <div class="detail-card-body">
            <div class="info-row">
                <span class="lbl">Return Date</span>
                <span class="val">{{ $purchaseReturn->returned_at ? $purchaseReturn->returned_at->format('d M Y') : $purchaseReturn->created_at->format('d M Y') }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Recorded At</span>
                <span class="val">{{ $purchaseReturn->created_at->format('d M Y, g:i A') }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Processed By</span>
                <span class="val">{{ $purchaseReturn->user?->name ?? 'Admin' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Refund Method</span>
                <span class="val">
                    @php $rm = $purchaseReturn->refund_method; @endphp
                    <span class="pay-badge pay-{{ $rm }}">
                        {{ $rm === 'cash' ? '💵 Cash' : ($rm === 'card' ? '💳 Bank/Card' : '📋 Credit / Balance Offset') }}
                    </span>
                </span>
            </div>
            <div class="info-row">
                <span class="lbl">Reason</span>
                <span class="val" style="color:#b91c1c;">{{ $purchaseReturn->reason ?? 'Supplier Return' }}</span>
            </div>
            @if($purchaseReturn->notes)
            <div class="info-row">
                <span class="lbl">Notes</span>
                <span class="val" style="font-weight:400; color:#6b7280; max-width:60%; text-align:right;">{{ $purchaseReturn->notes }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-card-header"><i class="bi bi-building"></i> Supplier & Reference</div>
        <div class="detail-card-body">
            <div class="info-row">
                <span class="lbl">Supplier Name</span>
                <span class="val" style="font-size:15px; color:#111827;">{{ $purchaseReturn->supplier_name }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Phone</span>
                <span class="val">{{ $purchaseReturn->supplier_phone ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Linked Purchase Order</span>
                <span class="val">
                    @if($purchaseReturn->purchase)
                    <a href="{{ route('admin.purchases.show', $purchaseReturn->purchase) }}" style="font-family:monospace; color:var(--pos-primary); text-decoration:none; font-weight:700;">
                        {{ $purchaseReturn->purchase->reference_number }}
                    </a>
                    @else
                    <span style="color:#9ca3af;">Direct Supplier Return</span>
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="lbl">Total Units Returned</span>
                <span class="val" style="color:#b91c1c;">
                    -{{ $purchaseReturn->items->sum('quantity') }} pcs
                </span>
            </div>
            <div class="info-row">
                <span class="lbl">Total Refund Claimed</span>
                <span class="val" style="color:#047857; font-size:18px;">
                    {{ pkr($purchaseReturn->refund_amount, 2) }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Items Table --}}
<div class="detail-card mb-4">
    <div class="detail-card-header"><i class="bi bi-list-ul"></i> Returned Items ({{ $purchaseReturn->items->count() }})</div>
    <div style="overflow-x:auto;">
        <table class="items-detail-table">
            <thead>
                <tr>
                    <th style="text-align:left; width:40%;">Product</th>
                    <th style="text-align:right;">Refund Rate</th>
                    <th style="text-align:right;">Qty Returned</th>
                    <th style="text-align:right;">Total Cost</th>
                    <th style="text-align:right;">Stock Deducted</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseReturn->items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item->product_name }}</div>
                        <div class="item-sku">{{ $item->product_sku }} · {{ strtoupper($item->product_unit) }}</div>
                        @if($item->reason)
                        <div style="font-size:11px; color:#b91c1c; margin-top:2px;">Reason: {{ $item->reason }}</div>
                        @endif
                    </td>
                    <td style="text-align:right;">{{ pkr($item->unit_cost, 2) }}</td>
                    <td style="text-align:right; font-weight:700; color:#b91c1c;">{{ $item->quantity }} {{ $item->product_unit }}</td>
                    <td style="text-align:right; font-weight:700; color:#111827;">{{ pkr($item->total_cost, 2) }}</td>
                    <td style="text-align:right;">
                        <span class="stock-deduct-badge">-{{ $item->quantity }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Totals footer --}}
    <div style="border-top:1.5px dashed #e5e7eb; padding:14px 0 8px;">
        <div class="t-row-foot">
            <span>Total Returned Units</span>
            <span style="font-weight:700;">{{ $purchaseReturn->items->sum('quantity') }} pcs</span>
        </div>
        <div class="t-row-foot">
            <span>Total Return Value</span>
            <span style="font-weight:700; color:#b91c1c;">{{ pkr($purchaseReturn->total_return_amount, 2) }}</span>
        </div>
        <div class="t-row-foot grand">
            <span>TOTAL REFUND AMOUNT</span>
            <span style="color:#047857;">{{ pkr($purchaseReturn->refund_amount, 2) }}</span>
        </div>
    </div>
</div>

@endsection

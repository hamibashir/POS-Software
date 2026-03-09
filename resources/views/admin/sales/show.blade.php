@extends('layouts.admin')

@section('title', 'Sale ' . $sale->invoice_number)

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap');

    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
    @media (max-width:768px) { .detail-grid { grid-template-columns: 1fr; } }

    .detail-card {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
        overflow: hidden;
    }
    .detail-card-header {
        background: #f9fafb; border-bottom: 1px solid #e5e7eb;
        padding: 12px 18px;
        font-size: 11px; font-weight: 700; color: #6b7280;
        text-transform: uppercase; letter-spacing: 1px;
        display: flex; align-items: center; gap: 8px;
    }
    .detail-card-body { padding: 18px; }

    .info-row {
        display: flex; justify-content: space-between; align-items: flex-start;
        padding: 8px 0; border-bottom: 1px solid #f9fafb; font-size: 14px;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row .lbl { color: #9ca3af; font-size: 12px; font-weight: 500; }
    .info-row .val { font-weight: 600; color: #111827; text-align: right; }

    .inv-hero {
        font-family: 'JetBrains Mono', monospace;
        font-size: 22px; font-weight: 700;
        color: #1d4ed8;
        background: #eff6ff; border-radius: 8px;
        padding: 8px 16px; display: inline-block;
    }

    .pay-cash { background:#d1fae5; color:#065f46; }
    .pay-card { background:#ede9fe; color:#5b21b6; }
    .pay-badge { display:inline-flex; align-items:center; gap:4px; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:700; }

    .status-completed { background:#d1fae5; color:#065f46; }
    .status-voided    { background:#fee2e2; color:#991b1b; }
    .status-badge     { border-radius:20px; padding:4px 12px; font-size:12px; font-weight:700; }

    /* Items table */
    .items-detail-table { width:100%; border-collapse:collapse; }
    .items-detail-table thead th {
        background:#f9fafb; font-size:11px; font-weight:700;
        text-transform:uppercase; letter-spacing:.6px; color:#6b7280;
        padding:10px 16px; border-bottom:1px solid #e5e7eb;
    }
    .items-detail-table tbody td {
        padding:14px 16px; font-size:14px; color:#374151;
        border-bottom:1px solid #f9fafb; vertical-align:middle;
    }
    .items-detail-table tbody tr:last-child td { border-bottom:none; }
    .items-detail-table tfoot td {
        padding:10px 16px; font-size:13px; border-top:1px solid #e5e7eb;
    }
    .item-name { font-weight:600; color:#111827; }
    .item-sku  { font-size:11px; color:#9ca3af; font-family:'JetBrains Mono', monospace; }

    .t-row-foot { display:flex; justify-content:space-between; padding:6px 16px; font-size:13px; color:#374151; }
    .t-row-foot.discount { color:#10b981; }
    .t-row-foot.grand    { font-size:17px; font-weight:800; color:#111827; border-top:2px solid #111827; padding-top:10px; margin-top:4px; }
    .t-row-foot.change   { background:#d1fae5; border-radius:8px; margin:6px 16px; padding:8px 16px; font-weight:700; color:#065f46; font-size:14px; }

    .action-strip {
        display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap;
    }
</style>
@endpush

@section('content')

{{-- Breadcrumb + actions --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <nav style="font-size:13px; color:#9ca3af;">
        <a href="{{ route('admin.sales.index') }}" style="color:var(--pos-primary); text-decoration:none; font-weight:600;">
            <i class="bi bi-arrow-left"></i> Sales History
        </a>
        <span class="mx-2">·</span>
        <span style="color:#374151; font-weight:600;">{{ $sale->invoice_number }}</span>
    </nav>

    <div class="action-strip mb-0">
        {{-- Reprint (no auto print) --}}
        <a href="{{ route('cashier.pos.receipt', $sale) }}"
           target="_blank" rel="noopener"
           class="btn-pos-outline text-decoration-none">
            <i class="bi bi-eye"></i> View Receipt
        </a>
        {{-- Reprint + auto print --}}
        <a href="{{ route('cashier.pos.receipt', $sale) }}?print=1"
           target="_blank" rel="noopener"
           class="btn-pos text-decoration-none">
            <i class="bi bi-printer"></i> Reprint Receipt
        </a>
    </div>
</div>

<div class="mb-3">
    <div class="inv-hero">{{ $sale->invoice_number }}</div>
    <span class="status-badge {{ $sale->status === 'completed' ? 'status-completed' : 'status-voided' }} ms-2">
        {{ ucfirst($sale->status) }}
    </span>
</div>

{{-- ── Info grid ──────────────────────────────────────────────── --}}
<div class="detail-grid">

    {{-- Sale info --}}
    <div class="detail-card">
        <div class="detail-card-header"><i class="bi bi-info-circle"></i> Sale Information</div>
        <div class="detail-card-body">
            <div class="info-row">
                <span class="lbl">Date & Time</span>
                <span class="val">
                    {{ $sale->created_at->format('d M Y') }}<br>
                    <span style="color:#9ca3af; font-weight:400; font-size:12px;">{{ $sale->created_at->format('g:i:s A') }}</span>
                </span>
            </div>
            <div class="info-row">
                <span class="lbl">Cashier</span>
                <span class="val">{{ $sale->user?->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Payment Method</span>
                <span class="val">
                    <span class="pay-badge {{ $sale->payment_method === 'cash' ? 'pay-cash' : 'pay-card' }}">
                        {{ $sale->payment_method === 'cash' ? '💵' : '💳' }}
                        {{ strtoupper($sale->payment_method) }}
                    </span>
                </span>
            </div>
            @if($sale->notes)
            <div class="info-row">
                <span class="lbl">Notes</span>
                <span class="val" style="font-weight:400; color:#6b7280; text-align:right; max-width:60%;">{{ $sale->notes }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Customer info --}}
    <div class="detail-card">
        <div class="detail-card-header"><i class="bi bi-person"></i> Customer Details</div>
        <div class="detail-card-body">
            <div class="info-row">
                <span class="lbl">Name</span>
                <span class="val">{{ $sale->customer_name ?? 'Walk-in Customer' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Phone</span>
                <span class="val">{{ $sale->customer_phone ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Total Items</span>
                <span class="val">{{ $sale->items->sum('quantity') }} pcs ({{ $sale->items->count() }} lines)</span>
            </div>
            <div class="info-row">
                <span class="lbl">Paid Amount</span>
                <span class="val" style="color:#10b981;">{{ pkr($sale->paid_amount, 2) }}</span>
            </div>
            @if($sale->change_amount > 0)
            <div class="info-row">
                <span class="lbl">Change Given</span>
                <span class="val">{{ pkr($sale->change_amount, 2) }}</span>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Items table + totals ─────────────────────────────────── --}}
<div class="detail-card mb-4">
    <div class="detail-card-header"><i class="bi bi-list-ul"></i> Items Sold ({{ $sale->items->count() }})</div>

    <table class="items-detail-table">
        <thead>
            <tr>
                <th style="text-align:left; width:40%;">Product</th>
                <th style="text-align:right;">Unit Price</th>
                <th style="text-align:right;">Qty</th>
                <th style="text-align:right;">Discount</th>
                <th style="text-align:right;">Line Total</th>
                <th style="text-align:right;">Cost</th>
                <th style="text-align:right;">Margin</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            @php
                $cost   = $item->cost_price * $item->quantity;
                $margin = $item->total_price > 0 ? (($item->total_price - $cost) / $item->total_price) * 100 : 0;
            @endphp
            <tr>
                <td>
                    <div class="item-name">{{ $item->product_name }}</div>
                    <div class="item-sku">{{ $item->product_sku }} · {{ strtoupper($item->product_unit) }}</div>
                </td>
                <td style="text-align:right;">{{ pkr($item->unit_price, 2) }}</td>
                <td style="text-align:right; font-weight:600;">{{ $item->quantity }}</td>
                <td style="text-align:right; color:#10b981;">
                    {{ $item->discount_amount > 0 ? '−$' . number_format($item->discount_amount, 2) : '—' }}
                </td>
                <td style="text-align:right; font-weight:700; color:#111827;">{{ pkr($item->total_price, 2) }}</td>
                <td style="text-align:right; color:#9ca3af; font-size:12px;">{{ pkr($cost, 2) }}</td>
                <td style="text-align:right;">
                    <span style="font-size:12px; font-weight:700;
                        color:{{ $margin >= 20 ? '#065f46' : ($margin >= 10 ? '#92400e' : '#991b1b') }};">
                        {{ number_format($margin, 1) }}%
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals footer --}}
    <div style="border-top:1.5px dashed #e5e7eb; padding: 16px 0 8px;">
        <div class="t-row-foot">
            <span>Subtotal ({{ $sale->items->sum('quantity') }} items)</span>
            <span>{{ pkr($sale->subtotal, 2) }}</span>
        </div>
        @if($sale->discount_amount > 0)
        <div class="t-row-foot discount">
            <span>Discount</span>
            <span>−{{ pkr($sale->discount_amount, 2) }}</span>
        </div>
        @endif
        @if($sale->tax_amount > 0)
        <div class="t-row-foot">
            <span>Tax</span>
            <span>{{ pkr($sale->tax_amount, 2) }}</span>
        </div>
        @endif
        <div class="t-row-foot grand">
            <span>TOTAL</span>
            <span>{{ pkr($sale->total_amount, 2) }}</span>
        </div>
        @if($sale->change_amount > 0)
        <div class="t-row-foot change">
            <span>💵 Change Due</span>
            <span>{{ pkr($sale->change_amount, 2) }}</span>
        </div>
        @endif
    </div>
</div>

@endsection

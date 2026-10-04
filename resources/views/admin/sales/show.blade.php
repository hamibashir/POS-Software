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

    .pay-cash   { background:#d1fae5; color:#065f46; }
    .pay-card   { background:#ede9fe; color:#5b21b6; }
    .pay-credit { background:#fef3c7; color:#92400e; }
    .pay-badge  { display:inline-flex; align-items:center; gap:4px; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:700; }

    .status-completed { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
    .status-pending   { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
    .status-voided    { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
    .status-badge     { border-radius:20px; padding:4px 12px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:4px; }

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
    @php
        $st = $sale->display_status;
    @endphp
    @if($st === 'pending')
        <span class="status-badge status-pending ms-2">
            <i class="bi bi-clock-history"></i> Pending
        </span>
    @elseif($st === 'voided')
        <span class="status-badge status-voided ms-2">
            <i class="bi bi-x-circle"></i> Voided
        </span>
    @else
        <span class="status-badge status-completed ms-2">
            <i class="bi bi-check-circle"></i> Completed
        </span>
    @endif
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
                <span class="lbl">Employee</span>
                <span class="val">{{ $sale->user?->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Payment Method</span>
                <span class="val">
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
                <span class="val">{{ format_qty($sale->items->sum('quantity')) }} ({{ $sale->items->count() }} lines)</span>
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
                <th style="text-align:left; width:35%;">Product</th>
                <th style="text-align:right;">Unit Price</th>
                <th style="text-align:right;">Sold Qty</th>
                <th style="text-align:center;">Returned</th>
                <th style="text-align:right;">Discount</th>
                <th style="text-align:right;">Line Total</th>
                <th style="text-align:right;">Cost</th>
                <th style="text-align:right;">Margin</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            @php
                $returnedQty = (float) $item->returnItems->sum('quantity');
                $netQty = max(0, (float)$item->quantity - $returnedQty);
                $cost   = (float)$item->cost_price * (float)$item->quantity;
                $margin = $item->total_price > 0 ? (($item->total_price - $cost) / $item->total_price) * 100 : 0;
            @endphp
            <tr>
                <td>
                    <div class="item-name">{{ $item->product_name }}</div>
                    <div class="item-sku">{{ $item->product_sku }} · {{ strtoupper($item->product_unit) }}</div>
                </td>
                <td style="text-align:right;">{{ pkr($item->unit_price, 2) }}</td>
                <td style="text-align:right; font-weight:600;">{{ format_qty($item->quantity) }}</td>
                <td style="text-align:center;">
                    @if($returnedQty > 0)
                        <span class="badge bg-danger" title="Returned back to inventory">
                            -{{ format_qty($returnedQty) }} {{ $item->product_unit }}
                        </span>
                    @else
                        <span class="text-muted small">—</span>
                    @endif
                </td>
                <td style="text-align:right; color:#10b981;">
                    {{ $item->discount_amount > 0 ? '−' . pkr($item->discount_amount, 2) : '—' }}
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
            <span>Subtotal ({{ format_qty($sale->items->sum('quantity')) }} items)</span>
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
            <span>GROSS SALE TOTAL</span>
            <span>{{ pkr($sale->total_amount, 2) }}</span>
        </div>
        @if($sale->total_returned_amount > 0)
        <div class="t-row-foot" style="color:#b91c1c; font-weight:700; font-size:14px; background:#fef2f2; margin:6px 16px; padding:8px 16px; border-radius:8px;">
            <span><i class="bi bi-arrow-counterclockwise me-1"></i> Customer Returns Deducted</span>
            <span>−{{ pkr($sale->total_returned_amount, 2) }}</span>
        </div>
        <div class="t-row-foot grand" style="color:#0f766e; border-top:1.5px solid #0f766e; margin-top:6px;">
            <span>NET SALE REVENUE</span>
            <span>{{ pkr(max(0, $sale->total_amount - $sale->total_returned_amount), 2) }}</span>
        </div>
        @endif
        @if($sale->change_amount > 0)
        <div class="t-row-foot change">
            <span>💵 Change Given</span>
            <span>{{ pkr($sale->change_amount, 2) }}</span>
        </div>
        @endif
    </div>
</div>

@if($sale->returns && $sale->returns->count() > 0)
{{-- ── Linked Customer Returns ────────────────────────────────── --}}
<div class="detail-card mb-4" style="border:1.5px solid #fecaca;">
    <div class="detail-card-header" style="background:#fff1f2; color:#991b1b;">
        <i class="bi bi-arrow-counterclockwise"></i> Customer Return Vouchers Linked to this Sale ({{ $sale->returns->count() }})
    </div>
    <div class="table-responsive">
        <table class="pos-table w-100">
            <thead>
                <tr>
                    <th>Voucher #</th>
                    <th>Date</th>
                    <th>Refund Method</th>
                    <th>Processed By</th>
                    <th class="text-end">Refunded Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->returns as $ret)
                <tr>
                    <td>
                        <a href="{{ route('admin.sale-returns.show', $ret->id) }}" class="fw-bold text-danger text-decoration-none" style="font-family:monospace;">
                            {{ $ret->return_number }}
                        </a>
                    </td>
                    <td>{{ $ret->returned_at ? \Carbon\Carbon::parse($ret->returned_at)->format('d M Y') : $ret->created_at->format('d M Y') }}</td>
                    <td><span class="badge bg-light text-dark border">{{ strtoupper($ret->refund_method) }}</span></td>
                    <td>{{ $ret->user?->name ?? 'POS Cashier' }}</td>
                    <td class="text-end fw-bold text-danger">-{{ pkr($ret->total_return_amount, 2) }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.sale-returns.show', $ret->id) }}" class="btn btn-sm btn-outline-secondary">View Return</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection

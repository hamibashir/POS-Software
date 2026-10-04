@extends('layouts.admin')
@section('title', $product->name . ' — Sales & Stock Details')

@push('styles')
<style>
    .prod-hero {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 20px 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .prod-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }
    .prod-stat-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px 18px;
        position: relative;
        overflow: hidden;
    }
    .prod-stat-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #64748b;
        margin-bottom: 4px;
    }
    .prod-stat-value {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .prod-stat-sub {
        font-size: 11.5px;
        color: #94a3b8;
        margin-top: 4px;
    }

    /* Tabs */
    .history-tabs {
        display: flex;
        gap: 6px;
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 18px;
        overflow-x: auto;
    }
    .history-tab-btn {
        padding: 10px 18px;
        font-size: 13.5px;
        font-weight: 700;
        color: #64748b;
        border: none;
        background: none;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.15s;
        white-space: nowrap;
        text-decoration: none;
    }
    .history-tab-btn:hover {
        color: var(--pos-primary);
    }
    .history-tab-btn.active {
        color: var(--pos-primary);
        border-bottom-color: var(--pos-primary);
        background: #f8fafc;
        border-radius: 8px 8px 0 0;
    }

    .prod-panel {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .prod-panel-header {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        padding: 14px 20px;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .audit-table {
        width: 100%;
        border-collapse: collapse;
    }
    .audit-table thead th {
        background: #f8fafc;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #64748b;
        padding: 11px 16px;
        border-bottom: 1px solid #e2e8f0;
    }
    .audit-table tbody td {
        padding: 13px 16px;
        font-size: 13px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .audit-table tbody tr:last-child td {
        border-bottom: none;
    }
    .audit-table tbody tr:hover {
        background: #f8fafc;
    }

    .pill-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
    }
</style>
@endpush

@section('content')

{{-- Hero Header --}}
<div class="prod-hero">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            @if($product->image)
                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" style="width:64px; height:64px; border-radius:10px; object-fit:cover; border:1px solid #e2e8f0;">
            @else
                <div style="width:64px; height:64px; border-radius:10px; background:#f1f5f9; color:#94a3b8; display:flex; align-items:center; justify-content:center; font-size:26px; border:1px solid #e2e8f0;">
                    <i class="bi bi-box-seam"></i>
                </div>
            @endif
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h1 style="font-size:22px; font-weight:800; color:#0f172a; margin:0;">{{ $product->name }}</h1>
                    @if($product->stock_quantity < 0)
                        <span class="badge bg-danger text-white rounded-pill px-3 py-1">
                            <i class="bi bi-exclamation-octagon-fill me-1"></i> Negative Stock ({{ format_qty($product->stock_quantity) }} {{ strtoupper($product->unit) }})
                        </span>
                    @elseif($product->stock_quantity == 0)
                        <span class="badge bg-danger text-white rounded-pill px-3 py-1">
                            <i class="bi bi-slash-circle me-1"></i> Out of Stock
                        </span>
                    @elseif($product->stock_quantity <= $product->low_stock_threshold)
                        <span class="badge rounded-pill px-3 py-1" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Low Stock ({{ format_qty($product->stock_quantity) }} {{ strtoupper($product->unit) }})
                        </span>
                    @else
                        <span class="badge rounded-pill px-3 py-1" style="background:#f0fdf4; color:#166534; border:1px solid #bbf7d0;">
                            <i class="bi bi-check-circle-fill me-1"></i> In Stock ({{ format_qty($product->stock_quantity) }} {{ strtoupper($product->unit) }})
                        </span>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2 mt-1 flex-wrap text-muted" style="font-size:12.5px;">
                    <span class="font-monospace fw-bold text-dark"><i class="bi bi-upc me-1"></i>{{ $product->sku }}</span>
                    @if($product->barcode)
                        <span>&bull; Barcode: <code>{{ $product->barcode }}</code></span>
                    @endif
                    <span>&bull; Category: <strong class="text-secondary">{{ $product->category?->name ?? 'Uncategorized' }}</strong></span>
                    @if($product->supplier?->name || $product->supplier_name)
                        <span>&bull; Supplier: <strong class="text-secondary">{{ $product->supplier?->name ?? $product->supplier_name }}</strong></span>
                    @endif
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.products.index') }}" class="btn-pos-outline" style="text-decoration:none; padding:8px 14px;">
                <i class="bi bi-arrow-left"></i> Products List
            </a>
            <a href="{{ route('admin.stock.create') }}?product_id={{ $product->id }}" class="btn-pos-outline" style="text-decoration:none; padding:8px 14px;">
                <i class="bi bi-plus-slash-minus"></i> Adjust Stock
            </a>
            <a href="{{ route('admin.products.edit', $product) }}" class="btn-pos" style="text-decoration:none; padding:8px 16px;">
                <i class="bi bi-pencil-square"></i> Edit Product
            </a>
        </div>
    </div>
</div>

{{-- Top Statistics Summary Cards --}}
<div class="prod-stat-grid">
    {{-- Current Stock --}}
    <div class="prod-stat-card" style="border-left: 4px solid {{ $product->stock_quantity <= 0 ? '#ef4444' : ($product->stock_quantity <= $product->low_stock_threshold ? '#f59e0b' : 'var(--pos-primary)') }};">
        <div class="prod-stat-label">Current Stock Balance</div>
        <div class="prod-stat-value" style="color: {{ $product->stock_quantity <= 0 ? '#dc2626' : ($product->stock_quantity <= $product->low_stock_threshold ? '#d97706' : 'var(--pos-primary)') }};">
            {{ format_qty($product->stock_quantity) }} <span style="font-size:13px; font-weight:600; color:#64748b;">{{ strtoupper($product->unit) }}</span>
        </div>
        <div class="prod-stat-sub">
            Alert Threshold: <strong>{{ format_qty($product->low_stock_threshold) }} {{ $product->unit }}</strong>
        </div>
    </div>

    {{-- Total Sold --}}
    <div class="prod-stat-card" style="border-left: 4px solid #0284c7;">
        <div class="prod-stat-label" style="color:#0284c7;">Total Sold (Net)</div>
        <div class="prod-stat-value" style="color:#0369a1;">
            {{ format_qty($stats['net_sold_qty']) }} <span style="font-size:13px; font-weight:600; color:#64748b;">{{ strtoupper($product->unit) }}</span>
        </div>
        <div class="prod-stat-sub">
            Gross: {{ format_qty($stats['total_sold_qty']) }} sold | {{ format_qty($stats['total_returned_qty']) }} returned
        </div>
    </div>

    {{-- Net Sales Revenue --}}
    <div class="prod-stat-card" style="border-left: 4px solid #059669;">
        <div class="prod-stat-label" style="color:#059669;">Net Sales Revenue</div>
        <div class="prod-stat-value" style="color:#047857;">
            {{ pkr($stats['net_sales_revenue'], 2) }}
        </div>
        <div class="prod-stat-sub">
            Sale Price: {{ pkr($product->sale_price, 2) }} / {{ $product->unit }}
        </div>
    </div>

    {{-- Gross Profit Generated --}}
    <div class="prod-stat-card" style="border-left: 4px solid #8b5cf6;">
        <div class="prod-stat-label" style="color:#7c3aed;">Gross Profit Generated</div>
        <div class="prod-stat-value" style="color:#6d28d9;">
            {{ pkr($stats['total_gross_profit'], 2) }}
        </div>
        <div class="prod-stat-sub">
            Margin: <strong style="color:#6d28d9;">{{ $stats['profit_margin_pct'] }}%</strong> &bull; Unit Cost: {{ pkr($product->cost_price, 2) }}
        </div>
    </div>

    {{-- Recorded Purchases --}}
    <div class="prod-stat-card" style="border-left: 4px solid #ea580c;">
        <div class="prod-stat-label" style="color:#ea580c;">Recorded Purchases</div>
        <div class="prod-stat-value" style="color:#c2410c;">
            {{ format_qty($stats['total_purchased_qty']) }} <span style="font-size:13px; font-weight:600; color:#64748b;">{{ strtoupper($product->unit) }}</span>
        </div>
        <div class="prod-stat-sub">
            Total Purchase Cost: {{ pkr($stats['total_purchase_cost'], 2) }}
        </div>
    </div>
</div>

{{-- Navigation Tabs --}}
<div class="history-tabs">
    <button type="button" class="history-tab-btn active" onclick="switchTab('sales', this)">
        <i class="bi bi-receipt"></i> Sales History ({{ $saleItems->count() }})
    </button>
    <button type="button" class="history-tab-btn" onclick="switchTab('movements', this)">
        <i class="bi bi-clock-history"></i> Stock Movement Audit ({{ $stockMovements->count() }})
    </button>
    <button type="button" class="history-tab-btn" onclick="switchTab('returns', this)">
        <i class="bi bi-arrow-counterclockwise"></i> Customer Returns ({{ $returnItems->count() }})
    </button>
    <button type="button" class="history-tab-btn" onclick="switchTab('purchases', this)">
        <i class="bi bi-truck"></i> Purchase Invoices ({{ $purchaseItems->count() }})
    </button>
</div>

{{-- TAB 1: SALES HISTORY --}}
<div id="tab-section-sales" class="tab-content-panel">
    <div class="prod-panel">
        <div class="prod-panel-header">
            <span><i class="bi bi-receipt me-1 text-primary"></i> Detailed Sales History for {{ $product->name }}</span>
            <span class="text-muted small">Total: <strong>{{ $saleItems->count() }} transactions</strong> &bull; <strong>{{ format_qty($stats['total_sold_qty']) }} units</strong></span>
        </div>
        @if($saleItems->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-cart-x" style="font-size:42px; color:#cbd5e1; display:block; margin-bottom:10px;"></i>
                <div class="fw-bold">No sales recorded yet for this product.</div>
                <div class="small">When cashiers sell this item at the POS counter, each sale invoice will appear here.</div>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date & Time</th>
                            <th>Customer / Buyer</th>
                            <th>Payment Method</th>
                            <th style="text-align:right;">Qty Sold</th>
                            <th style="text-align:right;">Unit Price</th>
                            <th style="text-align:right;">Total Amount</th>
                            <th>Cashier / User</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($saleItems as $item)
                        @php
                            $sale = $item->sale;
                            $m = strtolower($sale?->payment_method ?? 'cash');
                            $methodBadge = match($m) {
                                'cash'   => 'background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0;',
                                'card'   => 'background:#f0f9ff; color:#0369a1; border:1px solid #bae6fd;',
                                'credit' => 'background:#faf5ff; color:#7e22ce; border:1px solid #e9d5ff;',
                                default  => 'background:#f1f5f9; color:#475569; border:1px solid #cbd5e1;',
                            };
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.sales.show', $item->sale_id) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                    {{ $sale?->invoice_number ?? ('INV-#' . $item->sale_id) }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($sale?->created_at)->format('M d, Y') }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ \Carbon\Carbon::parse($sale?->created_at)->format('h:i A') }}</div>
                            </td>
                            <td>
                                @if($sale?->employee)
                                    <div class="fw-bold text-dark"><i class="bi bi-person-badge text-primary me-1"></i>{{ $sale->employee->name }}</div>
                                    <div class="text-muted" style="font-size:11px;">Customer Credit Account</div>
                                @else
                                    <span class="text-muted"><i class="bi bi-person me-1"></i>Walk-in Customer</span>
                                @endif
                            </td>
                            <td>
                                <span class="pill-badge" style="{{ $methodBadge }}">
                                    {{ ucfirst($sale?->payment_method ?? 'Cash') }}
                                </span>
                            </td>
                            <td style="text-align:right; font-weight:800; color:#0f172a; font-size:14px;">
                                {{ format_qty($item->quantity) }} <span style="font-size:11px; font-weight:600; color:#64748b;">{{ strtoupper($item->product_unit ?? $product->unit) }}</span>
                            </td>
                            <td style="text-align:right; font-weight:600; color:#475569;">
                                {{ pkr($item->unit_price, 2) }}
                            </td>
                            <td style="text-align:right; font-weight:800; color:#059669; font-size:14px; background:#f0fdf4;">
                                {{ pkr($item->total_price, 2) }}
                            </td>
                            <td>
                                <div class="text-dark fw-semibold">{{ $sale?->user?->name ?? 'System' }}</div>
                            </td>
                            <td style="text-align:center;">
                                <a href="{{ route('admin.sales.show', $item->sale_id) }}" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size:11.5px; border-radius:6px;" title="View Invoice">
                                    <i class="bi bi-eye me-1"></i> View Invoice
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- TAB 2: STOCK MOVEMENTS AUDIT TRAIL --}}
<div id="tab-section-movements" class="tab-content-panel" style="display:none;">
    <div class="prod-panel">
        <div class="prod-panel-header">
            <span><i class="bi bi-clock-history me-1 text-primary"></i> Complete Stock Movements & Audit Trail</span>
            <span class="text-muted small">Total: <strong>{{ $stockMovements->count() }} stock change logs</strong></span>
        </div>
        @if($stockMovements->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-activity" style="font-size:42px; color:#cbd5e1; display:block; margin-bottom:10px;"></i>
                <div class="fw-bold">No stock movement logs found for this product.</div>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th style="width:60px;"># ID</th>
                            <th>Date & Time</th>
                            <th>Movement Type</th>
                            <th style="text-align:center;">Change</th>
                            <th style="text-align:center;">Stock Level (Before &rarr; After)</th>
                            <th>Recorded By</th>
                            <th>Notes / Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockMovements as $m)
                        @php
                            $typeStr = strtolower($m->type);
                            $typeBadge = match($typeStr) {
                                'sale'           => 'background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5;',
                                'adjustment_in'  => 'background:#f0fdf4; color:#15803d; border:1px solid #86efac;',
                                'adjustment_out' => 'background:#fff1f2; color:#be123c; border:1px solid #fecdd3;',
                                'adjustment'     => 'background:#fef3c7; color:#b45309; border:1px solid #fde68a;',
                                'return'         => 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;',
                                'purchase'       => 'background:#ecfdf5; color:#047857; border:1px solid #a7f3d0;',
                                default          => 'background:#f1f5f9; color:#475569; border:1px solid #cbd5e1;',
                            };
                            $qtyFormatted = ($m->quantity > 0 ? '+' : '') . format_qty($m->quantity);
                        @endphp
                        <tr>
                            <td class="font-monospace text-muted" style="font-size:11.5px;">
                                #{{ $m->id }}
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($m->created_at)->format('M d, Y') }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ \Carbon\Carbon::parse($m->created_at)->format('h:i:s A') }}</div>
                            </td>
                            <td>
                                <span class="pill-badge" style="{{ $typeBadge }}">
                                    {{ strtoupper(str_replace('_', ' ', $m->type)) }}
                                </span>
                            </td>
                            <td style="text-align:center; font-size:14px; font-weight:800; color:{{ $m->quantity >= 0 ? '#15803d' : '#b91c1c' }};">
                                {{ $qtyFormatted }} {{ strtoupper($product->unit) }}
                            </td>
                            <td style="text-align:center;">
                                <div class="d-inline-flex align-items-center gap-2 px-2 py-1 rounded" style="background:#f8fafc; border:1px solid #e2e8f0; font-family:monospace; font-size:12.5px;">
                                    <span class="text-muted fw-bold">{{ format_qty($m->stock_before) }}</span>
                                    <i class="bi bi-arrow-right text-secondary" style="font-size:11px;"></i>
                                    <span class="fw-extrabold {{ $m->stock_after < 0 ? 'text-danger' : 'text-dark' }}">{{ format_qty($m->stock_after) }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $m->user?->name ?? 'System' }}</div>
                            </td>
                            <td>
                                <div style="font-size:12.5px; color:#334155;">{{ $m->notes ?: '—' }}</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- TAB 3: CUSTOMER RETURNS --}}
<div id="tab-section-returns" class="tab-content-panel" style="display:none;">
    <div class="prod-panel">
        <div class="prod-panel-header">
            <span><i class="bi bi-arrow-counterclockwise me-1 text-danger"></i> Customer Product Returns</span>
            <span class="text-muted small">Total: <strong>{{ $returnItems->count() }} returns</strong> &bull; <strong>{{ format_qty($stats['total_returned_qty']) }} units returned</strong></span>
        </div>
        @if($returnItems->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-arrow-counterclockwise" style="font-size:42px; color:#cbd5e1; display:block; margin-bottom:10px;"></i>
                <div class="fw-bold">No returns recorded for this product.</div>
                <div class="small">Any items returned by customers at the counter will be logged here.</div>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Return #</th>
                            <th>Date & Time</th>
                            <th>Customer</th>
                            <th>Refund Method</th>
                            <th style="text-align:right;">Returned Qty</th>
                            <th style="text-align:right;">Rate</th>
                            <th style="text-align:right;">Refunded Amount</th>
                            <th>Reason</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returnItems as $ret)
                        @php
                            $sr = $ret->saleReturn;
                        @endphp
                        <tr>
                            <td class="fw-bold font-monospace text-primary">
                                {{ $sr?->return_number ?? ('RET-#' . $ret->sale_return_id) }}
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($sr?->returned_at ?: $ret->created_at)->format('M d, Y') }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ \Carbon\Carbon::parse($sr?->returned_at ?: $ret->created_at)->format('h:i A') }}</div>
                            </td>
                            <td>
                                @if($sr?->employee)
                                    <div class="fw-bold text-dark">{{ $sr->employee->name }}</div>
                                    <div class="text-muted" style="font-size:11px;">Customer Credit Account</div>
                                @else
                                    <span class="text-muted">Walk-in Customer</span>
                                @endif
                            </td>
                            <td>
                                <span class="pill-badge" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a;">
                                    {{ ucfirst(str_replace('_', ' ', $sr?->refund_method ?? 'cash')) }}
                                </span>
                            </td>
                            <td style="text-align:right; font-weight:800; color:#b91c1c; font-size:14px;">
                                +{{ format_qty($ret->quantity) }} <span style="font-size:11px; font-weight:600; color:#64748b;">{{ strtoupper($product->unit) }}</span>
                            </td>
                            <td style="text-align:right; font-weight:600; color:#475569;">
                                {{ pkr($ret->unit_price, 2) }}
                            </td>
                            <td style="text-align:right; font-weight:800; color:#b91c1c; font-size:14px;">
                                {{ pkr($ret->total_price, 2) }}
                            </td>
                            <td style="color:#475569; font-size:12.5px;">
                                {{ $ret->reason ?: ($sr?->reason ?: '—') }}
                            </td>
                            <td>
                                <div class="text-dark fw-semibold">{{ $sr?->user?->name ?? 'Cashier' }}</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- TAB 4: PURCHASES HISTORY --}}
<div id="tab-section-purchases" class="tab-content-panel" style="display:none;">
    <div class="prod-panel">
        <div class="prod-panel-header">
            <span><i class="bi bi-truck me-1 text-primary"></i> Stock Purchase Invoices</span>
            <span class="text-muted small">Total: <strong>{{ $purchaseItems->count() }} purchase entries</strong> &bull; <strong>{{ format_qty($stats['total_purchased_qty']) }} units purchased</strong></span>
        </div>
        @if($purchaseItems->isEmpty())
            <div class="p-5 text-center text-muted">
                <i class="bi bi-truck" style="font-size:42px; color:#cbd5e1; display:block; margin-bottom:10px;"></i>
                <div class="fw-bold">No purchase invoices recorded for this product yet.</div>
                <div class="small">When stock is purchased from registered suppliers in Admin Purchases, records will appear here.</div>
                <div class="mt-3">
                    <a href="{{ route('admin.purchases.create') }}" class="btn-pos" style="text-decoration:none;">
                        <i class="bi bi-plus-lg"></i> Record Purchase Invoice
                    </a>
                </div>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Purchase Reference</th>
                            <th>Date Received</th>
                            <th>Supplier</th>
                            <th style="text-align:right;">Qty Purchased</th>
                            <th style="text-align:right;">Unit Cost</th>
                            <th style="text-align:right;">Total Cost</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseItems as $pu)
                        @php
                            $purchase = $pu->purchase;
                            $supName = $purchase?->supplier?->name ?: ($purchase?->supplier_name ?: '—');
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.purchases.show', $pu->purchase_id) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                    {{ $purchase?->reference_number ?? ('PO-#' . $pu->purchase_id) }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($purchase?->received_at ?: $pu->created_at)->format('M d, Y') }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $supName }}</div>
                            </td>
                            <td style="text-align:right; font-weight:800; color:#047857; font-size:14px;">
                                +{{ format_qty($pu->quantity) }} <span style="font-size:11px; font-weight:600; color:#64748b;">{{ strtoupper($product->unit) }}</span>
                            </td>
                            <td style="text-align:right; font-weight:600; color:#475569;">
                                {{ pkr($pu->unit_cost, 2) }}
                            </td>
                            <td style="text-align:right; font-weight:800; color:#0f172a; font-size:14px;">
                                {{ pkr($pu->total_cost, 2) }}
                            </td>
                            <td>
                                <div class="text-dark fw-semibold">{{ $purchase?->user?->name ?? 'Admin' }}</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    function switchTab(tabId, btn) {
        document.querySelectorAll('.history-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content-panel').forEach(p => p.style.display = 'none');
        btn.classList.add('active');
        const target = document.getElementById('tab-section-' + tabId);
        if (target) target.style.display = 'block';
    }
</script>
@endpush

@extends('layouts.admin')

@section('title', 'Return Voucher ' . $saleReturn->return_number)

@push('styles')
<style>
    .detail-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px; margin-bottom:18px; }
    .detail-label { font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.6px; margin-bottom:2px; }
    .detail-value { font-size:14px; font-weight:700; color:#111827; }
    .inv-code { font-family:monospace; font-size:17px; font-weight:800; color:#b91c1c; }
    .pay-cash { background:#d1fae5; color:#065f46; }
    .pay-card { background:#ede9fe; color:#5b21b6; }
    .pay-credit { background:#fef3c7; color:#92400e; }
    .pay-badge  { display:inline-flex; align-items:center; gap:4px; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }
</style>
@endpush

@section('content')

<div class="page-hero d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="{{ route('admin.sale-returns.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Returns
            </a>
            <span class="inv-code">{{ $saleReturn->return_number }}</span>
            <span class="badge bg-danger">Customer Return</span>
        </div>
        <p class="text-muted mb-0">Processed on {{ $saleReturn->created_at->format('d M Y, g:i A') }} by {{ $saleReturn->user?->name ?? 'POS Cashier' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('cashier.pos.return-receipt', $saleReturn->id) }}" target="_blank" class="btn-pos text-decoration-none" style="background:#b91c1c;">
            <i class="bi bi-printer"></i> Print Return Voucher
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Customer Information --}}
    <div class="col-md-4">
        <div class="detail-card h-100">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-person me-1 text-danger"></i> Customer Information</h6>
            <div class="mb-2">
                <div class="detail-label">Customer Name</div>
                <div class="detail-value">{{ $saleReturn->customer_name }}</div>
            </div>
            @if($saleReturn->customer_phone)
            <div class="mb-2">
                <div class="detail-label">Phone Number</div>
                <div class="detail-value">{{ $saleReturn->customer_phone }}</div>
            </div>
            @endif
            @if($saleReturn->employee)
            <div class="mb-2">
                <div class="detail-label">Authorized Customer Account</div>
                <div class="detail-value">
                    <a href="{{ route('admin.staff.employees.ledger', $saleReturn->employee->id) }}" class="text-decoration-none fw-bold" style="color:#0f766e;">
                        {{ $saleReturn->employee->name }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Original Sale & Audit --}}
    <div class="col-md-4">
        <div class="detail-card h-100">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-receipt me-1 text-danger"></i> Original Sale Details</h6>
            <div class="mb-2">
                <div class="detail-label">Invoice Number</div>
                <div class="detail-value">
                    @if($saleReturn->sale)
                        <a href="{{ route('admin.sales.show', $saleReturn->sale->id) }}" class="text-decoration-none fw-bold" style="font-family:monospace; color:#0f766e;">
                            {{ $saleReturn->sale->invoice_number }} <i class="bi bi-box-arrow-up-right ms-1"></i>
                        </a>
                    @else
                        <span class="text-muted">Direct Return (No Invoice Linked)</span>
                    @endif
                </div>
            </div>
            @if($saleReturn->sale)
            <div class="mb-2">
                <div class="detail-label">Original Sale Total</div>
                <div class="detail-value">PKR {{ number_format($saleReturn->sale->total_amount, 2) }} ({{ strtoupper($saleReturn->sale->payment_method) }})</div>
            </div>
            @endif
            <div class="mb-2">
                <div class="detail-label">Return Date</div>
                <div class="detail-value">{{ $saleReturn->returned_at ? \Carbon\Carbon::parse($saleReturn->returned_at)->format('d M Y') : $saleReturn->created_at->format('d M Y') }}</div>
            </div>
        </div>
    </div>

    {{-- Refund Details --}}
    <div class="col-md-4">
        <div class="detail-card h-100" style="background:#fff1f2; border-color:#fecaca;">
            <h6 class="fw-bold text-danger border-bottom pb-2 mb-3"><i class="bi bi-cash-stack me-1"></i> Refund & Disbursement</h6>
            <div class="mb-2">
                <div class="detail-label">Total Refund Amount</div>
                <div class="detail-value fs-4 text-danger">-{{ pkr($saleReturn->total_return_amount, 2) }}</div>
            </div>
            <div class="mb-2">
                <div class="detail-label">Refund Method</div>
                <div class="detail-value">
                    <span class="pay-badge {{ match($saleReturn->refund_method) {
                        'cash'              => 'pay-cash',
                        'card'              => 'pay-card',
                        'credit_adjustment' => 'pay-credit',
                        default             => 'pay-cash'
                    } }}">
                        {{ match($saleReturn->refund_method) {
                            'cash'              => '💵 Cash Counter Refund',
                            'card'              => '💳 Card / Bank Reversal',
                            'credit_adjustment' => '💼 Credit Due Adjusted',
                            default             => ucfirst($saleReturn->refund_method)
                        } }}
                    </span>
                </div>
            </div>
            <div class="mb-1">
                <div class="detail-label">Return Reason</div>
                <div class="detail-value text-secondary">{{ $saleReturn->reason ?? 'Customer Return' }}</div>
            </div>
            @if($saleReturn->notes)
            <div class="mt-2 text-muted small fst-italic">"{{ $saleReturn->notes }}"</div>
            @endif
        </div>
    </div>
</div>

{{-- Items Returned Table --}}
<div class="pos-card p-0 mb-4">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-box-seam me-1 text-danger"></i> Returned Items & Inventory Stock Restoration</h6>
        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Stock Restored to Inventory</span>
    </div>
    <div class="table-responsive">
        <table class="pos-table w-100">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Details</th>
                    <th>SKU / Barcode</th>
                    <th class="text-center">Returned Qty</th>
                    <th class="text-end">Sales Rate</th>
                    <th class="text-end">Line Refund Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saleReturn->items as $idx => $item)
                <tr>
                    <td style="color:#9ca3af; font-size:12px;">{{ $idx + 1 }}</td>
                    <td>
                        <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                        <div class="text-muted small" style="font-size:11px;">Current Inventory Stock: {{ $item->product?->stock_quantity ?? 'N/A' }} {{ $item->product_unit }}</div>
                    </td>
                    <td>
                        <span style="font-family:monospace; font-weight:600; color:#4b5563;">{{ $item->product_sku ?: '—' }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-danger fs-6 px-3 py-1">
                            -{{ $item->quantity }} {{ $item->product_unit ?? 'pcs' }}
                        </span>
                    </td>
                    <td class="text-end fw-bold text-teal" style="color:#0f766e;">
                        {{ pkr($item->unit_price, 2) }}
                    </td>
                    <td class="text-end fw-bold text-danger fs-6">
                        -{{ pkr($item->total_price, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f8fafc; font-weight:800; border-top:2px solid #e2e8f0;">
                    <td colspan="5" class="text-end fs-6">Total Disbursed Refund:</td>
                    <td class="text-end fs-5 text-danger">-{{ pkr($saleReturn->total_return_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@endsection

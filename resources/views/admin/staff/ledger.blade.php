@extends('layouts.admin')

@section('title', 'Customer Credit Ledger - ' . $employee->name)

@push('styles')
<style>
    .emp-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px; margin-bottom:24px; }
    .emp-row { display:flex; justify-content:space-between; align-items:center; padding:8px 0; font-size:14px; border-bottom:1px solid #f9fafb; }
    .emp-row:last-child { border-bottom:none; }
    .badge-pending { background:#fee2e2; color:#b91c1c; font-size:18px; font-weight:800; border-radius:20px; padding:4px 16px; display:inline-block; }
    .badge-clear   { background:#d1fae5; color:#065f46; font-size:16px; font-weight:700; border-radius:20px; padding:4px 14px; display:inline-block; }
</style>
@endpush

@section('content')

{{-- Breadcrumb --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <nav style="font-size:13px; color:#9ca3af;">
        <a href="{{ route('admin.staff.index') }}" style="color:var(--pos-primary); text-decoration:none; font-weight:600;">
            <i class="bi bi-arrow-left"></i> Customers & Staff
        </a>
        <span class="mx-2">·</span>
        <span style="color:#374151; font-weight:600;">{{ $employee->name }} Ledger</span>
    </nav>
    <div class="d-flex gap-2">
        @if($employee->pending_payment > 0)
        <button class="btn-pos" onclick="openPaymentModal({{ $employee->id }}, '{{ addslashes($employee->name) }}', {{ $employee->pending_payment }})">
            <i class="bi bi-cash"></i> Record Payment / Clear Due
        </button>
        @endif
        <a href="{{ route('admin.staff.index') }}" class="btn-pos-outline text-decoration-none">
            Back to Customers
        </a>
    </div>
</div>

@if(session('success'))
<div class="pos-alert pos-alert-success mb-3">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

{{-- Customer Header Card --}}
<div class="emp-card">
    <div class="row align-items-center">
        <div class="col-md-7">
            <h2 style="font-size:22px; font-weight:800; color:#111827; margin:0 0 6px;">{{ $employee->name }}</h2>
            <div style="font-size:14px; color:#4b5563; display:flex; gap:16px; flex-wrap:wrap;">
                <span><i class="bi bi-telephone text-muted me-1"></i>{{ $employee->phone }}</span>
                <span><i class="bi bi-geo-alt text-muted me-1"></i>{{ $employee->address }}</span>
                <span class="badge" style="background:{{ $employee->is_active ? '#d1fae5' : '#fee2e2' }}; color:{{ $employee->is_active ? '#065f46' : '#991b1b' }};">
                    {{ $employee->is_active ? 'Active for Credit' : 'Credit Suspended' }}
                </span>
            </div>
            @if($employee->notes)
            <div style="font-size:12px; color:#6b7280; margin-top:6px;">{{ $employee->notes }}</div>
            @endif
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <div style="font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.6px; margin-bottom:4px;">
                CURRENT PENDING DUE
            </div>
            @if($employee->pending_payment > 0)
            <div class="badge-pending">{{ pkr($employee->pending_payment, 2) }}</div>
            @else
            <div class="badge-clear"><i class="bi bi-check-circle me-1"></i> Cleared (PKR 0.00)</div>
            @endif
            <div style="font-size:12px; color:#6b7280; margin-top:6px;">
                Total Credit Taken: <strong>{{ pkr($employee->total_credit, 2) }}</strong> &nbsp;|&nbsp;
                Total Paid: <strong style="color:#047857;">{{ pkr($employee->total_paid, 2) }}</strong>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Credit Sales Table --}}
    <div class="col-lg-7">
        <div class="pos-card p-0">
            <div style="padding:16px 20px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                <h5 style="margin:0; font-size:15px; font-weight:700; color:#111827;">
                    <i class="bi bi-receipt me-2 text-danger"></i>Credit Purchases ({{ $creditSales->count() }})
                </h5>
                <span style="font-weight:700; color:#b91c1c; font-size:14px;">{{ pkr($employee->total_credit, 2) }}</span>
            </div>
            <div style="overflow-x:auto;">
                <table class="pos-table w-100">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Items</th>
                            <th>Cashier</th>
                            <th style="text-align:right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($creditSales as $sale)
                        <tr>
                            <td>
                                <div style="font-weight:600; font-size:13px;">{{ $sale->created_at->format('d M Y') }}</div>
                                <div style="font-size:11px; color:#9ca3af;">{{ $sale->created_at->format('g:i A') }}</div>
                            </td>
                            <td>
                                <a href="{{ route('cashier.pos.receipt', $sale) }}" target="_blank" style="font-family:monospace; font-weight:700; color:var(--pos-primary); text-decoration:none;">
                                    {{ $sale->invoice_number }}
                                </a>
                            </td>
                            <td>
                                <span style="background:#f3f4f6; color:#374151; border-radius:10px; padding:2px 8px; font-size:11px; font-weight:700;">
                                    {{ $sale->items->count() }} item(s)
                                </span>
                            </td>
                            <td style="font-size:13px; color:#4b5563;">{{ $sale->user?->name ?? '—' }}</td>
                            <td style="text-align:right; font-weight:700; color:#b91c1c;">
                                {{ pkr($sale->total_amount, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:30px; color:#9ca3af;">
                                No credit sales recorded for this customer.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Payments Cleared Table --}}
    <div class="col-lg-5">
        <div class="pos-card p-0">
            <div style="padding:16px 20px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                <h5 style="margin:0; font-size:15px; font-weight:700; color:#111827;">
                    <i class="bi bi-cash-coin me-2 text-success"></i>Payments Cleared ({{ $payments->count() }})
                </h5>
                <span style="font-weight:700; color:#047857; font-size:14px;">{{ pkr($employee->total_paid, 2) }}</span>
            </div>
            <div style="overflow-x:auto;">
                <table class="pos-table w-100">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Recorded By</th>
                            <th style="text-align:right;">Amount Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $pay)
                        <tr>
                            <td>
                                <div style="font-weight:600; font-size:13px;">{{ $pay->payment_date ? $pay->payment_date->format('d M Y') : $pay->created_at->format('d M Y') }}</div>
                                @if($pay->notes)
                                <div style="font-size:11px; color:#9ca3af;">{{ $pay->notes }}</div>
                                @endif
                            </td>
                            <td>
                                <span style="background:#f3f4f6; color:#374151; border-radius:10px; padding:2px 8px; font-size:11px; font-weight:600; text-transform:capitalize;">
                                    {{ str_replace('_', ' ', $pay->payment_method) }}
                                </span>
                            </td>
                            <td style="font-size:13px; color:#4b5563;">{{ $pay->user?->name ?? 'Admin' }}</td>
                            <td style="text-align:right; font-weight:700; color:#047857;">
                                {{ pkr($pay->amount, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align:center; padding:30px; color:#9ca3af;">
                                No payments recorded yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Clear Due Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="paymentForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cash-coin me-2 text-success"></i>Record Payment / Clear Due</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:12px 16px;">
                    <div style="font-size:12px; color:#991b1b; font-weight:600;">CUSTOMER PENDING BALANCE</div>
                    <div style="font-size:20px; font-weight:800; color:#b91c1c;" id="payModalPendingAmount">PKR 0.00</div>
                    <div style="font-size:13px; color:#374151; font-weight:600; margin-top:2px;" id="payModalEmpName">—</div>
                </div>

                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Payment Amount (PKR) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="payModalAmount" class="pos-input" required placeholder="0.00">
                </div>

                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Payment Method <span class="text-danger">*</span></label>
                    <select name="payment_method" class="pos-input" required>
                        <option value="cash">💵 Cash Received</option>
                        <option value="bank">🏦 Bank Transfer</option>
                        <option value="salary_deduction">📋 Salary / Payroll Deduction</option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="pos-input" required value="{{ date('Y-m-d') }}">
                </div>

                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Notes / Receipt #</label>
                    <input type="text" name="notes" class="pos-input" placeholder="e.g. Paid in cash or Salary deduction">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-pos" style="background:#059669;"><i class="bi bi-check2-circle"></i> Save Payment & Clear Due</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPaymentModal(id, name, pendingAmount) {
        document.getElementById('paymentForm').action = `{{ url('admin/staff/employees') }}/${id}/payments`;
        document.getElementById('payModalEmpName').innerText = name;
        document.getElementById('payModalPendingAmount').innerText = 'PKR ' + pendingAmount.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
        document.getElementById('payModalAmount').value = pendingAmount.toFixed(2);
        new bootstrap.Modal(document.getElementById('paymentModal')).show();
    }
</script>

@endsection

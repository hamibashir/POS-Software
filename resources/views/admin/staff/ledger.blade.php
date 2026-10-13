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
        <button class="btn-pos-outline" style="color:#b45309; border-color:#fde68a; background:#fffbeb;" onclick="openAdjustDueModal({{ $employee->id }}, '{{ addslashes($employee->name) }}', {{ $employee->pending_payment }})">
            <i class="bi bi-sliders"></i> Edit / Adjust Due
        </button>
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

@if($errors->any())
<div class="pos-alert pos-alert-error mb-3">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $err)
        <li>{{ $err }}</li>
        @endforeach
    </ul>
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
                Opening Balance: <strong style="color:#4f46e5;">{{ pkr($employee->opening_balance ?? 0, 2) }}</strong> &nbsp;|&nbsp; Total Credit: <strong>{{ pkr($employee->total_credit, 2) }}</strong> &nbsp;|&nbsp;
                Total Paid: <strong style="color:#047857;">{{ pkr($employee->total_paid, 2) }}</strong>
                @if($employee->total_adjustments != 0)
                &nbsp;|&nbsp; Active Adjustments: <strong style="color:{{ $employee->total_adjustments < 0 ? '#059669' : '#dc2626' }};">{{ ($employee->total_adjustments > 0 ? '+' : '') . pkr($employee->total_adjustments, 2) }}</strong>
                @endif
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
                            <th>Recorded By</th>
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
                            <th style="text-align:right;">Amount</th>
                            <th style="text-align:right;">Actions</th>
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
                            <td style="text-align:right;">
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn-pos-outline" style="padding:2px 6px; font-size:11px;"
                                        onclick="openEditPaymentModal({{ $pay->id }}, {{ (float)$pay->amount }}, '{{ $pay->payment_method }}', '{{ $pay->payment_date ? $pay->payment_date->format('Y-m-d') : $pay->created_at->format('Y-m-d') }}', '{{ addslashes($pay->notes ?? '') }}')"
                                        title="Edit Payment">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.staff.employees.payments.destroy', $pay) }}" style="display:inline;" onsubmit="return confirm('Delete and undo this payment of PKR {{ number_format($pay->amount, 2) }}? This will restore the customer pending due.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-pos-outline" style="padding:2px 6px; font-size:11px; border-color:#fee2e2; color:#dc2626;" title="Delete & Undo Payment">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:30px; color:#9ca3af;">
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

{{-- Due Adjustments & Correction History (Undo / Redo Supported) --}}
<div class="mt-4">
    <div class="pos-card p-0">
        <div style="padding:16px 20px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <h5 style="margin:0; font-size:15px; font-weight:700; color:#111827;">
                    <i class="bi bi-sliders me-2 text-warning"></i>Due Adjustments & Correction History ({{ $adjustments->count() }})
                </h5>
                <div style="font-size:12px; color:#6b7280; margin-top:2px;">
                    Manual customer due corrections with 1-click Undo / Redo. Store sales, daily drawer, and general profit ledgers remain unaffected.
                </div>
            </div>
            <button class="btn-pos-outline" style="font-size:12px; color:#b45309; border-color:#fde68a; background:#fffbeb;" onclick="openAdjustDueModal({{ $employee->id }}, '{{ addslashes($employee->name) }}', {{ $employee->pending_payment }})">
                <i class="bi bi-plus-circle"></i> New Due Adjustment
            </button>
        </div>
        <div style="overflow-x:auto;">
            <table class="pos-table w-100">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Admin</th>
                        <th>Previous Due</th>
                        <th>Adjusted Due</th>
                        <th style="text-align:right;">Delta Change</th>
                        <th>Reason</th>
                        <th>Ledger Impact</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:right;">Undo / Redo Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adj)
                    <tr>
                        <td style="white-space:nowrap;">
                            <div style="font-weight:600; font-size:13px;">{{ $adj->created_at->format('d M Y') }}</div>
                            <div style="font-size:11px; color:#9ca3af;">{{ $adj->created_at->format('g:i A') }}</div>
                        </td>
                        <td style="font-size:13px; color:#4b5563;">{{ $adj->user?->name ?? 'Admin' }}</td>
                        <td style="font-size:13px; font-weight:600; color:#4b5563;">{{ pkr($adj->previous_due, 2) }}</td>
                        <td style="font-size:13px; font-weight:700; color:#111827;">{{ pkr($adj->new_due, 2) }}</td>
                        <td style="text-align:right; font-weight:700; color:{{ $adj->amount < 0 ? '#059669' : '#dc2626' }};">
                            {{ ($adj->amount > 0 ? '+' : '') . pkr($adj->amount, 2) }}
                        </td>
                        <td style="font-size:13px; color:#374151; max-width:240px;">
                            {{ $adj->reason }}
                        </td>
                        <td>
                            <span class="badge" style="background:#f0fdf4; color:#166534; font-size:11px;">
                                <i class="bi bi-shield-check"></i> Customer Balance Only (Sales Unaffected)
                            </span>
                        </td>
                        <td style="text-align:center;">
                            @if($adj->is_active)
                            <span class="badge" style="background:#d1fae5; color:#065f46; font-size:12px; font-weight:700;">
                                <i class="bi bi-check-circle me-1"></i> Active
                            </span>
                            @else
                            <span class="badge" style="background:#fee2e2; color:#991b1b; font-size:12px; font-weight:700;">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Undone
                            </span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <div class="d-inline-flex gap-1">
                                <form method="POST" action="{{ route('admin.staff.adjustments.toggle-undo', $adj) }}" style="display:inline;" onsubmit="return confirm('{{ $adj->is_active ? 'Undo this adjustment and restore previous balance?' : 'Redo this adjustment and re-apply balance change?' }}');">
                                    @csrf
                                    @if($adj->is_active)
                                    <button type="submit" class="btn-pos-outline" style="padding:4px 10px; font-size:12px; border-color:#fca5a5; color:#dc2626;" title="Undo Adjustment">
                                        <i class="bi bi-arrow-counterclockwise"></i> Undo
                                    </button>
                                    @else
                                    <button type="submit" class="btn-pos" style="padding:4px 10px; font-size:12px; background:#059669; border-color:#059669;" title="Redo Adjustment">
                                        <i class="bi bi-arrow-clockwise"></i> Redo
                                    </button>
                                    @endif
                                </form>

                                <form method="POST" action="{{ route('admin.staff.adjustments.destroy', $adj) }}" style="display:inline;" onsubmit="return confirm('Permanently delete this adjustment record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-pos-outline" style="padding:4px 8px; font-size:12px; border-color:#fee2e2; color:#dc2626;" title="Delete Record">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center; padding:30px; color:#9ca3af;">
                            No manual due adjustments recorded for this customer.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 1. Clear Due Modal --}}
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

{{-- 2. Edit Payment Modal --}}
<div class="modal fade" id="editPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editPaymentForm" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Payment Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Payment Amount (PKR) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="editPayAmount" class="pos-input" required placeholder="0.00">
                </div>

                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Payment Method <span class="text-danger">*</span></label>
                    <select name="payment_method" id="editPayMethod" class="pos-input" required>
                        <option value="cash">💵 Cash Received</option>
                        <option value="bank">🏦 Bank Transfer</option>
                        <option value="salary_deduction">📋 Salary / Payroll Deduction</option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" id="editPayDate" class="pos-input" required>
                </div>

                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Notes / Receipt #</label>
                    <input type="text" name="notes" id="editPayNotes" class="pos-input" placeholder="e.g. Corrected receipt number">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-pos"><i class="bi bi-check2"></i> Update Payment</button>
            </div>
        </form>
    </div>
</div>

{{-- 3. Adjust / Edit Customer Due Modal --}}
<div class="modal fade" id="adjustDueModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="adjustDueForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-sliders me-2 text-warning"></i>Edit / Adjust Customer Due</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-3">
                    <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:14px 18px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div style="font-size:12px; color:#92400e; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Customer Name</div>
                                <div style="font-size:16px; font-weight:800; color:#1f2937;" id="adjModalEmpName">{{ $employee->name }}</div>
                            </div>
                            <div class="text-end">
                                <div style="font-size:12px; color:#92400e; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Current Pending Due</div>
                                <div style="font-size:20px; font-weight:800; color:#b91c1c;" id="adjModalCurrentDue">PKR {{ number_format($employee->pending_payment, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">New Correct Due (PKR) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="new_due" id="adjModalNewDue" class="pos-input" required placeholder="0.00" value="{{ $employee->pending_payment }}">
                            <div id="adjModalDeltaPreview" style="font-size:12px; font-weight:600; margin-top:4px; color:#4b5563;">
                                Adjustment change: <span id="adjModalDeltaText">No change (PKR 0.00)</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">Reason for Adjustment / Correction <span class="text-danger">*</span></label>
                            <input type="text" name="reason" id="adjModalReason" class="pos-input" required placeholder="e.g. Due entered by mistake, wrong customer chosen on bill">
                        </div>
                    </div>

                    <div class="p-3 rounded" style="background:#f0fdf4; border:1px solid #bbf7d0;">
                        <div class="form-check m-0">
                            <input class="form-check-input" type="checkbox" id="safeAdjustCheck" checked disabled>
                            <label class="form-check-label" for="safeAdjustCheck" style="font-size:13px; font-weight:700; color:#166534;">
                                <i class="bi bi-shield-check me-1"></i> Balance Correction Only: Do not impact store sales, revenue, or ledgers
                            </label>
                        </div>
                        <input type="hidden" name="affect_sales" value="0">
                        <div style="font-size:12px; color:#15803d; margin-top:4px;">
                            This adjustment will directly update this customer's due balance. Daily drawer, sales revenue, COGS, and general profit ledgers will remain 100% unaffected. You can <strong>Undo</strong> or <strong>Redo</strong> this edit at any time.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-pos" style="background:#d97706; border-color:#d97706;"><i class="bi bi-check2-circle"></i> Save Due Adjustment</button>
                </div>
            </form>
        </div>
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

    function openEditPaymentModal(id, amount, method, date, notes) {
        document.getElementById('editPaymentForm').action = `{{ url('admin/staff/employees/payments') }}/${id}`;
        document.getElementById('editPayAmount').value = amount.toFixed(2);
        document.getElementById('editPayMethod').value = method;
        document.getElementById('editPayDate').value = date;
        document.getElementById('editPayNotes').value = notes;
        new bootstrap.Modal(document.getElementById('editPaymentModal')).show();
    }

    let currentAdjPending = {{ (float) $employee->pending_payment }};
    function openAdjustDueModal(id, name, pendingAmount) {
        currentAdjPending = pendingAmount;
        document.getElementById('adjustDueForm').action = `{{ url('admin/staff/employees') }}/${id}/adjust-due`;
        document.getElementById('adjModalEmpName').innerText = name;
        document.getElementById('adjModalCurrentDue').innerText = 'PKR ' + pendingAmount.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
        document.getElementById('adjModalNewDue').value = pendingAmount.toFixed(2);
        document.getElementById('adjModalReason').value = '';
        updateAdjDeltaPreview(pendingAmount);
        new bootstrap.Modal(document.getElementById('adjustDueModal')).show();
    }

    function updateAdjDeltaPreview(newVal) {
        const delta = newVal - currentAdjPending;
        const textEl = document.getElementById('adjModalDeltaText');
        if (Math.abs(delta) < 0.001) {
            textEl.innerHTML = '<span style="color:#6b7280;">No change (PKR 0.00)</span>';
        } else if (delta < 0) {
            textEl.innerHTML = `<span style="color:#059669; font-weight:700;">-PKR ${Math.abs(delta).toFixed(2)} (Reduction)</span>`;
        } else {
            textEl.innerHTML = `<span style="color:#dc2626; font-weight:700;">+PKR ${delta.toFixed(2)} (Increase)</span>`;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const newDueInput = document.getElementById('adjModalNewDue');
        if (newDueInput) {
            newDueInput.addEventListener('input', function() {
                updateAdjDeltaPreview(parseFloat(this.value) || 0);
            });
        }
    });
</script>

@endsection

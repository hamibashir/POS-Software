@extends('layouts.admin')

@section('title', 'Suppliers & Payment Clearance')

@push('styles')
<style>
    .kpi-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .badge-due {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .badge-cleared {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .btn-pay-quick {
        background: #0f766e;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s;
    }
    .btn-pay-quick:hover {
        background: #115e59;
        color: #fff;
    }
</style>
@endpush

@section('content')

{{-- ── Flash messages ─────────────────────────────── --}}
@if(session('success'))
    <div class="pos-alert pos-alert-success mb-4">
        <i class="bi bi-check-circle-fill"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif
@if(session('error'))
    <div class="pos-alert pos-alert-error mb-4">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- ── Header ─────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div class="page-hero mb-0">
        <h1><i class="bi bi-truck text-primary me-2"></i>Suppliers & Payables</h1>
        <p>Manage vendors, track pending balances, and record payment clearances.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn-pos" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="bi bi-plus-lg"></i> Add Supplier
        </button>
    </div>
</div>

{{-- ── KPI Cards ──────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#e8f4f7; color:var(--pos-primary);">
                <i class="bi bi-truck"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold">Total Suppliers</div>
                <div class="fs-4 fw-bold text-dark">{{ $stats['total_suppliers'] }}</div>
                <div class="text-muted small" style="font-size:11px;">{{ $stats['active_suppliers'] }} active</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#e0f2fe; color:#0284c7;">
                <i class="bi bi-box-arrow-in-down"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold">Total Purchases / Invoiced</div>
                <div class="fs-4 fw-bold text-dark">{{ pkr($stats['total_invoiced'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#ecfdf5; color:#059669;">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold">Total Payments Cleared</div>
                <div class="fs-4 fw-bold text-success">{{ pkr($stats['total_paid'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card" style="border-left: 4px solid #ef4444;">
            <div class="kpi-icon" style="background:#fee2e2; color:#dc2626;">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold">Total Pending Payables</div>
                <div class="fs-4 fw-bold text-danger">{{ pkr($stats['total_pending_dues'], 2) }}</div>
                <div class="text-muted small" style="font-size:11px;">Owed to suppliers</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Filter & Search Bar ─────────────────────────── --}}
<div class="pos-card p-3 mb-4">
    <form method="GET" action="{{ route('admin.suppliers.index') }}" class="d-flex gap-2 flex-wrap align-items-center">
        <div class="input-group" style="max-width: 320px;">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" name="search" class="pos-input border-start-0 ps-0"
                   placeholder="Search name, company, phone…" value="{{ request('search') }}">
        </div>

        <select name="balance" class="pos-input" style="max-width: 200px;" onchange="this.form.submit()">
            <option value="">All Balances</option>
            <option value="due" {{ request('balance') === 'due' ? 'selected' : '' }}>Pending Dues Only (🔴)</option>
            <option value="cleared" {{ request('balance') === 'cleared' ? 'selected' : '' }}>Cleared (PKR 0.00)</option>
        </select>

        <select name="status" class="pos-input" style="max-width: 160px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>

        <button type="submit" class="btn-pos">Filter</button>
        @if(request()->hasAny(['search', 'balance', 'status']))
            <a href="{{ route('admin.suppliers.index') }}" class="btn-pos-outline">Reset</a>
        @endif
    </form>
</div>

{{-- ── Table ───────────────────────────────────────── --}}
<div class="pos-card">
    <div class="table-responsive">
        <table class="pos-table w-100 align-middle">
            <thead>
                <tr>
                    <th>Supplier / Company</th>
                    <th>Contact Info</th>
                    <th class="text-end">Total Invoiced</th>
                    <th class="text-end">Total Cleared</th>
                    <th class="text-end">Pending Balance</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                @php
                    $pending = $supplier->pending_balance;
                @endphp
                <tr>
                    <td>
                        <div class="fw-bold text-dark fs-6">{{ $supplier->name }}</div>
                        @if($supplier->company_name)
                            <div class="text-muted small"><i class="bi bi-building me-1"></i>{{ $supplier->company_name }}</div>
                        @endif
                        @if($supplier->address)
                            <div class="text-muted small" style="font-size:11.5px;"><i class="bi bi-geo-alt me-1"></i>{{ Str::limit($supplier->address, 35) }}</div>
                        @endif
                    </td>
                    <td>
                        @if($supplier->phone)
                            <div class="fw-semibold text-dark"><i class="bi bi-telephone text-primary me-1"></i>{{ $supplier->phone }}</div>
                        @endif
                        @if($supplier->email)
                            <div class="text-muted small"><i class="bi bi-envelope me-1"></i>{{ $supplier->email }}</div>
                        @endif
                        @if(!$supplier->phone && !$supplier->email)
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end fw-semibold text-dark">
                        {{ pkr($supplier->total_purchases + (float)$supplier->opening_balance, 2) }}
                    </td>
                    <td class="text-end fw-semibold text-success">
                        {{ pkr($supplier->total_paid, 2) }}
                    </td>
                    <td class="text-end">
                        @if($pending > 0)
                            <span class="badge-due fs-6">{{ pkr($pending, 2) }}</span>
                        @else
                            <span class="badge-cleared fs-6"><i class="bi bi-check2"></i> Cleared (0.00)</span>
                        @endif
                    </td>
                    <td>
                        @if($supplier->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">Active</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1">
                            @if($pending > 0)
                                <button type="button" class="btn-pay-quick"
                                        onclick="openPayModal({{ $supplier->id }}, '{{ addslashes($supplier->name) }}', {{ $pending }})"
                                        title="Clear Payment">
                                    <i class="bi bi-cash-stack"></i> Pay
                                </button>
                            @endif

                            <a href="{{ route('admin.suppliers.ledger', $supplier) }}" class="btn-pos-outline px-2 py-1" title="View Full Ledger Statement">
                                <i class="bi bi-journal-text"></i> Ledger
                            </a>

                            <button type="button" class="btn-pos-outline px-2 py-1"
                                    onclick="openEditModal({{ json_encode($supplier) }})" title="Edit Details">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-truck fs-1 d-block mb-2 text-secondary"></i>
                        No suppliers found matching your criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($suppliers->hasPages())
        <div class="p-3 border-top">
            {{ $suppliers->links() }}
        </div>
    @endif
</div>

{{-- ══════════════ MODALS ══════════════ --}}

{{-- 1. ADD SUPPLIER MODAL --}}
<div class="modal fade" id="addSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.suppliers.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle text-primary me-2"></i>Add New Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Supplier / Contact Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="pos-input" required placeholder="e.g. Master Steel & Pipes">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Company / Shop Name</label>
                        <input type="text" name="company_name" class="pos-input" placeholder="e.g. Master Sanitary Ltd">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="pos-input" placeholder="e.g. 03001234567">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="pos-input" placeholder="e.g. sales@vendor.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address / Location</label>
                        <textarea name="address" class="pos-input" rows="2" placeholder="City, market, or warehouse address..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Opening Balance (PKR) <small class="text-muted fw-normal">(Previous debt if any)</small></label>
                        <input type="number" step="0.01" min="0" name="opening_balance" class="pos-input" value="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes / Reference</label>
                        <input type="text" name="notes" class="pos-input" placeholder="Bank details, terms, etc.">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-pos"><i class="bi bi-check-lg me-1"></i>Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. EDIT SUPPLIER MODAL --}}
<div class="modal fade" id="editSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editSupplierForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Supplier / Contact Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="pos-input" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Company / Shop Name</label>
                        <input type="text" name="company_name" id="edit_company_name" class="pos-input">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" id="edit_phone" class="pos-input">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" id="edit_email" class="pos-input">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address / Location</label>
                        <textarea name="address" id="edit_address" class="pos-input" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Opening Balance (PKR)</label>
                        <input type="number" step="0.01" min="0" name="opening_balance" id="edit_opening_balance" class="pos-input">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="is_active" id="edit_is_active" class="pos-input">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes / Reference</label>
                        <input type="text" name="notes" id="edit_notes" class="pos-input">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-pos"><i class="bi bi-check-lg me-1"></i>Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 3. RECORD PAYMENT / CLEAR PAYMENT MODAL --}}
<div class="modal fade" id="paySupplierModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="paySupplierForm" method="POST">
                @csrf
                <div class="modal-header bg-teal text-white" style="background:#0f766e; color:#fff; border-radius:14px 14px 0 0;">
                    <h5 class="modal-title text-white"><i class="bi bi-cash-stack me-2"></i>Record Supplier Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="p-3 mb-3 rounded" style="background:#f0fdfa; border:1px solid #ccfbf1;">
                        <div class="text-muted small fw-semibold">Paying To:</div>
                        <div class="fs-5 fw-bold text-dark" id="pay_supplier_name">—</div>
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                            <span class="text-muted small">Current Pending Balance:</span>
                            <span class="fs-6 fw-bold text-danger" id="pay_pending_badge">PKR 0.00</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">Payment Amount (PKR) <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none fw-bold" onclick="fillFullPayment()">
                                Pay Full Balance
                            </button>
                        </div>
                        <input type="number" step="0.01" min="0.01" name="amount" id="pay_amount" class="pos-input fs-5 fw-bold text-success" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="pos-input" required>
                                <option value="cash" selected>💵 Cash</option>
                                <option value="bank">🏦 Bank Transfer</option>
                                <option value="cheque">📝 Cheque</option>
                                <option value="online">📱 Online / Card</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="pos-input" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cheque # / Bank Reference</label>
                        <input type="text" name="reference_number" class="pos-input" placeholder="e.g. CHQ-9982 or Trx-49210">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes / Remarks</label>
                        <input type="text" name="notes" class="pos-input" placeholder="e.g. Paid via drawer cash">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-pos" style="background:#0f766e;"><i class="bi bi-check2-circle me-1"></i>Confirm & Clear Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentPendingAmount = 0;

    function openPayModal(id, name, pending) {
        currentPendingAmount = pending;
        document.getElementById('pay_supplier_name').textContent = name;
        document.getElementById('pay_pending_badge').textContent = 'PKR ' + Number(pending).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('pay_amount').value = Number(pending).toFixed(2);
        document.getElementById('paySupplierForm').action = `/admin/suppliers/${id}/payments`;
        new bootstrap.Modal(document.getElementById('paySupplierModal')).show();
    }

    function fillFullPayment() {
        if (currentPendingAmount > 0) {
            document.getElementById('pay_amount').value = Number(currentPendingAmount).toFixed(2);
        }
    }

    function openEditModal(supplier) {
        document.getElementById('edit_name').value = supplier.name || '';
        document.getElementById('edit_company_name').value = supplier.company_name || '';
        document.getElementById('edit_phone').value = supplier.phone || '';
        document.getElementById('edit_email').value = supplier.email || '';
        document.getElementById('edit_address').value = supplier.address || '';
        document.getElementById('edit_opening_balance').value = supplier.opening_balance || '0.00';
        document.getElementById('edit_is_active').value = supplier.is_active ? '1' : '0';
        document.getElementById('edit_notes').value = supplier.notes || '';
        document.getElementById('editSupplierForm').action = `/admin/suppliers/${supplier.id}`;
        new bootstrap.Modal(document.getElementById('editSupplierModal')).show();
    }
</script>
@endpush

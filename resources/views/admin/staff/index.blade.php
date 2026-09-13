@extends('layouts.admin')

@section('title', 'Customers & User Management')

@push('styles')
<style>
    .stat-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px 20px; display:flex; align-items:center; gap:14px; height:100%; }
    .stat-icon  { width:46px; height:46px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
    .stat-label { font-size:11px; color:#9ca3af; font-weight:600; text-transform:uppercase; letter-spacing:.6px; }
    .stat-value { font-size:20px; font-weight:800; color:#111827; }
    .stat-sub   { font-size:11px; color:#9ca3af; }

    .nav-tabs-custom { display:flex; gap:8px; border-bottom:2px solid #e5e7eb; margin-bottom:20px; }
    .nav-tab-btn {
        background:none; border:none; padding:10px 18px; font-size:14px; font-weight:700; color:#6b7280;
        cursor:pointer; position:relative; display:flex; align-items:center; gap:8px;
    }
    .nav-tab-btn.active { color:var(--pos-primary); }
    .nav-tab-btn.active::after {
        content:''; position:absolute; bottom:-2px; left:0; right:0; height:2px; background:var(--pos-primary);
    }

    .badge-pending { background:#fee2e2; color:#b91c1c; font-size:13px; font-weight:800; border-radius:20px; padding:3px 12px; display:inline-block; }
    .badge-clear   { background:#d1fae5; color:#065f46; font-size:12px; font-weight:700; border-radius:20px; padding:3px 10px; display:inline-block; }
    .badge-active  { background:#d1fae5; color:#065f46; border-radius:20px; padding:3px 8px; font-size:11px; font-weight:700; }
    .badge-inactive{ background:#fee2e2; color:#991b1b; border-radius:20px; padding:3px 8px; font-size:11px; font-weight:700; }
    .badge-admin   { background:#ede9fe; color:#6d28d9; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }
    .badge-cashier { background:#e0f2fe; color:#0369a1; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:700; }
    .badge-you     { background:#fef3c7; color:#92400e; border-radius:12px; padding:2px 7px; font-size:10px; font-weight:800; text-transform:uppercase; }
</style>
@endpush

@section('content')

<div class="page-hero d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1><i class="bi bi-people me-2" style="color:var(--pos-primary)"></i>Customers & User Management</h1>
        <p>Manage customers eligible for credit sales, POS cashiers, and system administrators.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button class="btn-pos-outline" data-bs-toggle="modal" data-bs-target="#addAdminModal">
            <i class="bi bi-shield-lock-fill"></i> Add Admin
        </button>
        <button class="btn-pos-outline" data-bs-toggle="modal" data-bs-target="#addCashierModal">
            <i class="bi bi-person-badge"></i> Add Cashier
        </button>
        <button class="btn-pos" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
            <i class="bi bi-person-plus-fill"></i> Add Customer
        </button>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ede9fe; color:#7c3aed;"><i class="bi bi-shield-check"></i></div>
            <div>
                <div class="stat-label">Administrators</div>
                <div class="stat-value">{{ $stats['total_admins'] ?? $admins->count() }}</div>
                <div class="stat-sub">Full system access</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7; color:#b45309;"><i class="bi bi-person-badge"></i></div>
            <div>
                <div class="stat-label">Cashiers</div>
                <div class="stat-value">{{ $stats['total_cashiers'] }}</div>
                <div class="stat-sub">POS terminal operators</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe; color:#0284c7;"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-label">Credit Customers</div>
                <div class="stat-value">{{ $stats['total_employees'] }}</div>
                <div class="stat-sub">{{ $stats['active_employees'] }} active for credit</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2; color:#dc2626;"><i class="bi bi-exclamation-octagon"></i></div>
            <div>
                <div class="stat-label">Pending Dues</div>
                <div class="stat-value" style="color:#b91c1c;">{{ pkr($stats['total_pending'], 2) }}</div>
                <div class="stat-sub">unpaid customer credit</div>
            </div>
        </div>
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

{{-- Tabs --}}
<div class="nav-tabs-custom">
    <button class="nav-tab-btn active" id="tabBtnEmployees" onclick="switchTab('employees')">
        <i class="bi bi-person-lines-fill"></i> Customers (Credit Dues)
        <span class="badge" style="background:#f3f4f6; color:#374151; border-radius:10px; font-size:11px;">{{ $employees->count() }}</span>
    </button>
    <button class="nav-tab-btn" id="tabBtnCashiers" onclick="switchTab('cashiers')">
        <i class="bi bi-person-badge"></i> Cashiers (POS Users)
        <span class="badge" style="background:#f3f4f6; color:#374151; border-radius:10px; font-size:11px;">{{ $cashiers->count() }}</span>
    </button>
    <button class="nav-tab-btn" id="tabBtnAdmins" onclick="switchTab('admins')">
        <i class="bi bi-shield-lock-fill"></i> Administrators
        <span class="badge" style="background:#f3f4f6; color:#374151; border-radius:10px; font-size:11px;">{{ $admins->count() }}</span>
    </button>
</div>

{{-- ═══════════════ EMPLOYEES TAB ═══════════════ --}}
<div id="tabEmployees" class="pos-card">
    <div style="overflow-x:auto;">
        <table class="pos-table w-100">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th style="text-align:right;">Total Credit</th>
                    <th style="text-align:right;">Total Paid</th>
                    <th style="text-align:right;">Pending to Clear</th>
                    <th style="text-align:center;">Credit Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr>
                    <td>
                        <div style="font-weight:700; color:#111827; font-size:14px;">{{ $emp->name }}</div>
                        @if($emp->notes)
                        <div style="font-size:11px; color:#9ca3af;">{{ Str::limit($emp->notes, 40) }}</div>
                        @endif
                    </td>
                    <td>
                        <span style="font-weight:600; color:#374151;"><i class="bi bi-telephone me-1 text-muted"></i>{{ $emp->phone }}</span>
                    </td>
                    <td style="max-width:220px; font-size:13px; color:#4b5563;">
                        <i class="bi bi-geo-alt me-1 text-muted"></i>{{ $emp->address }}
                    </td>
                    <td style="text-align:right; font-weight:600; color:#4b5563;">
                        {{ pkr($emp->total_credit, 2) }}
                    </td>
                    <td style="text-align:right; font-weight:600; color:#047857;">
                        {{ pkr($emp->total_paid, 2) }}
                    </td>
                    <td style="text-align:right;">
                        @if($emp->pending_payment > 0)
                        <span class="badge-pending">
                            {{ pkr($emp->pending_payment, 2) }}
                        </span>
                        @else
                        <span class="badge-clear">
                            <i class="bi bi-check2"></i> Cleared
                        </span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <span class="{{ $emp->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $emp->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <div class="d-inline-flex gap-1">
                            @if($emp->pending_payment > 0)
                            <button class="btn-pos" style="padding:4px 8px; font-size:12px;"
                                onclick="openPaymentModal({{ $emp->id }}, '{{ addslashes($emp->name) }}', {{ $emp->pending_payment }})">
                                <i class="bi bi-cash"></i> Clear Due
                            </button>
                            @endif

                            <a href="{{ route('admin.staff.employees.ledger', $emp) }}" class="btn-pos-outline" style="padding:4px 8px; font-size:12px; text-decoration:none;" title="View Statement">
                                <i class="bi bi-journal-text"></i>
                            </a>

                            <button class="btn-pos-outline" style="padding:4px 8px; font-size:12px;"
                                onclick="openEditEmployeeModal({{ $emp->id }}, '{{ addslashes($emp->name) }}', '{{ addslashes($emp->phone) }}', '{{ addslashes($emp->address) }}', {{ $emp->is_active ? 1 : 0 }}, '{{ addslashes($emp->notes ?? '') }}')"
                                title="Edit Customer">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <form method="POST" action="{{ route('admin.staff.employees.destroy', $emp) }}" style="display:inline;" onsubmit="return confirm('Remove this customer?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-pos-outline" style="padding:4px 8px; font-size:12px; border-color:#fee2e2; color:#dc2626;" title="Delete / Deactivate">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:48px; color:#9ca3af;">
                        <i class="bi bi-people" style="font-size:36px; display:block; margin-bottom:8px; opacity:.5;"></i>
                        <p style="font-weight:600; color:#374151; margin-bottom:4px;">No customers added yet</p>
                        <p style="font-size:13px; margin:0;">Add customers who are authorized to take items on credit sale.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════════ CASHIERS TAB ═══════════════ --}}
<div id="tabCashiers" class="pos-card" style="display:none;">
    <div style="overflow-x:auto;">
        <table class="pos-table w-100">
            <thead>
                <tr>
                    <th>Cashier Name</th>
                    <th>Email / Login</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Date Added</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cashiers as $c)
                <tr>
                    <td>
                        <div style="font-weight:700; color:#111827;">{{ $c->name }}</div>
                    </td>
                    <td>
                        <span style="font-family:monospace; color:#4b5563;">{{ $c->email }}</span>
                    </td>
                    <td>
                        <span class="badge-cashier">
                            <i class="bi bi-person-badge me-1"></i> POS Cashier
                        </span>
                    </td>
                    <td>
                        <span class="{{ $c->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $c->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td style="font-size:13px; color:#6b7280;">
                        {{ $c->created_at->format('d M Y') }}
                    </td>
                    <td style="text-align:right;">
                        <div class="d-inline-flex gap-1">
                            <button class="btn-pos-outline" style="padding:4px 8px; font-size:12px;"
                                onclick="openEditCashierModal({{ $c->id }}, '{{ addslashes($c->name) }}', '{{ addslashes($c->email) }}', {{ $c->is_active ? 1 : 0 }})">
                                <i class="bi bi-pencil"></i> Edit
                            </button>

                            <form method="POST" action="{{ route('admin.staff.cashiers.destroy', $c) }}" style="display:inline;" onsubmit="return confirm('Remove cashier {{ $c->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-pos-outline" style="padding:4px 8px; font-size:12px; border-color:#fee2e2; color:#dc2626;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:48px; color:#9ca3af;">
                        <i class="bi bi-person-badge" style="font-size:36px; display:block; margin-bottom:8px; opacity:.5;"></i>
                        <p style="font-weight:600; color:#374151; margin-bottom:4px;">No cashiers configured</p>
                        <p style="font-size:13px; margin:0;">Create cashier accounts with password to let staff operate the POS.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════════ ADMINISTRATORS TAB ═══════════════ --}}
<div id="tabAdmins" class="pos-card" style="display:none;">
    <div style="overflow-x:auto;">
        <table class="pos-table w-100">
            <thead>
                <tr>
                    <th>Admin Name</th>
                    <th>Email / Login</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Date Added</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $a)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="font-weight:700; color:#111827;">{{ $a->name }}</div>
                            @if($a->id === auth()->id())
                                <span class="badge-you"><i class="bi bi-person-check me-1"></i>You</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span style="font-family:monospace; color:#4b5563;">{{ $a->email }}</span>
                    </td>
                    <td>
                        <span class="badge-admin">
                            <i class="bi bi-shield-lock-fill me-1"></i> Administrator
                        </span>
                    </td>
                    <td>
                        <span class="{{ $a->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $a->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td style="font-size:13px; color:#6b7280;">
                        {{ $a->created_at->format('d M Y') }}
                    </td>
                    <td style="text-align:right;">
                        <div class="d-inline-flex gap-1">
                            <button class="btn-pos-outline" style="padding:4px 8px; font-size:12px;"
                                onclick="openEditAdminModal({{ $a->id }}, '{{ addslashes($a->name) }}', '{{ addslashes($a->email) }}', {{ $a->is_active ? 1 : 0 }}, {{ $a->id === auth()->id() ? 1 : 0 }})">
                                <i class="bi bi-pencil"></i> Edit
                            </button>

                            @if($a->id !== auth()->id() && $admins->count() > 1)
                            <form method="POST" action="{{ route('admin.staff.admins.destroy', $a) }}" style="display:inline;" onsubmit="return confirm('Remove administrator {{ $a->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-pos-outline" style="padding:4px 8px; font-size:12px; border-color:#fee2e2; color:#dc2626;" title="Delete Admin">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:48px; color:#9ca3af;">
                        <i class="bi bi-shield-lock" style="font-size:36px; display:block; margin-bottom:8px; opacity:.5;"></i>
                        <p style="font-weight:600; color:#374151; margin-bottom:4px;">No administrators found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════════ MODALS ═══════════════ --}}

{{-- 1. Add Customer Modal --}}
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.staff.employees.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2 text-primary"></i>Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Customer Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="pos-input" required placeholder="e.g. Muhammad Ali">
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Phone Number <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="pos-input" required placeholder="e.g. 03001234567">
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Address <span class="text-danger">*</span></label>
                    <textarea name="address" class="pos-input" rows="2" required placeholder="Customer address or location"></textarea>
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Notes (Optional)</label>
                    <input type="text" name="notes" class="pos-input" placeholder="e.g. Plumber, Electrician, Contractor">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-pos"><i class="bi bi-check-lg"></i> Save Customer</button>
            </div>
        </form>
    </div>
</div>

{{-- 2. Edit Customer Modal --}}
<div class="modal fade" id="editEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editEmployeeForm" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Customer Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="editEmpName" class="pos-input" required>
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Phone Number <span class="text-danger">*</span></label>
                    <input type="text" name="phone" id="editEmpPhone" class="pos-input" required>
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Address <span class="text-danger">*</span></label>
                    <textarea name="address" id="editEmpAddress" class="pos-input" rows="2" required></textarea>
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Credit Allowed (Active) <span class="text-danger">*</span></label>
                    <select name="is_active" id="editEmpActive" class="pos-input">
                        <option value="1">Active (Allowed to take credit)</option>
                        <option value="0">Inactive (Credit blocked)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Notes</label>
                    <input type="text" name="notes" id="editEmpNotes" class="pos-input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-pos"><i class="bi bi-check-lg"></i> Update Customer</button>
            </div>
        </form>
    </div>
</div>

{{-- 3. Clear Due / Record Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="paymentForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cash-coin me-2 text-success"></i>Clear / Record Customer Due Payment</h5>
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
                        <option value="salary_deduction">📋 Salary / Account Deduction</option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="pos-input" required value="{{ date('Y-m-d') }}">
                </div>

                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Notes / Receipt #</label>
                    <input type="text" name="notes" class="pos-input" placeholder="e.g. Paid in cash at counter">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-pos" style="background:#059669;"><i class="bi bi-check2-circle"></i> Save Payment & Clear Due</button>
            </div>
        </form>
    </div>
</div>

{{-- 4. Add Cashier Modal --}}
<div class="modal fade" id="addCashierModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.staff.cashiers.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-badge me-2 text-primary"></i>Add Cashier Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Cashier Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="pos-input" required placeholder="e.g. Cashier 1">
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Email Address (Login) <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="pos-input" required placeholder="e.g. cashier1@hassanstore.com">
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="pos-input" required minlength="6" placeholder="At least 6 characters">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-pos"><i class="bi bi-check-lg"></i> Create Cashier</button>
            </div>
        </form>
    </div>
</div>

{{-- 5. Edit Cashier Modal --}}
<div class="modal fade" id="editCashierModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editCashierForm" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Cashier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Cashier Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="editCashierName" class="pos-input" required>
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="editCashierEmail" class="pos-input" required>
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" class="pos-input" minlength="6" placeholder="Leave blank to preserve">
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Account Status <span class="text-danger">*</span></label>
                    <select name="is_active" id="editCashierActive" class="pos-input">
                        <option value="1">Active</option>
                        <option value="0">Inactive / Deactivated</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-pos"><i class="bi bi-check-lg"></i> Update Cashier</button>
            </div>
        </form>
    </div>
</div>

{{-- 6. Add Administrator Modal --}}
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.staff.admins.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-shield-lock-fill me-2 text-primary"></i>Add Administrator Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Administrator Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="pos-input" required placeholder="e.g. Hassan Bashir">
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Email Address (Login) <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="pos-input" required placeholder="e.g. hassan@hassanandsonscorp.com">
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="pos-input" required minlength="6" placeholder="At least 6 characters">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-pos"><i class="bi bi-shield-check"></i> Create Administrator</button>
            </div>
        </form>
    </div>
</div>

{{-- 7. Edit Administrator Modal --}}
<div class="modal fade" id="editAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editAdminForm" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Administrator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Administrator Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="editAdminName" class="pos-input" required>
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="editAdminEmail" class="pos-input" required>
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" class="pos-input" minlength="6" placeholder="Leave blank to preserve current password">
                </div>
                <div id="adminActiveWrapper">
                    <label class="form-label" style="font-size:13px; font-weight:600;">Account Status <span class="text-danger">*</span></label>
                    <select name="is_active" id="editAdminActive" class="pos-input">
                        <option value="1">Active</option>
                        <option value="0">Inactive / Deactivated</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-pos"><i class="bi bi-check-lg"></i> Update Administrator</button>
            </div>
        </form>
    </div>
</div>

<script>
    function switchTab(tab) {
        document.getElementById('tabEmployees').style.display = tab === 'employees' ? 'block' : 'none';
        document.getElementById('tabCashiers').style.display  = tab === 'cashiers' ? 'block' : 'none';
        document.getElementById('tabAdmins').style.display    = tab === 'admins' ? 'block' : 'none';
        document.getElementById('tabBtnEmployees').classList.toggle('active', tab === 'employees');
        document.getElementById('tabBtnCashiers').classList.toggle('active', tab === 'cashiers');
        document.getElementById('tabBtnAdmins').classList.toggle('active', tab === 'admins');
    }

    function openEditEmployeeModal(id, name, phone, address, isActive, notes) {
        document.getElementById('editEmployeeForm').action = `{{ url('admin/staff/employees') }}/${id}`;
        document.getElementById('editEmpName').value = name;
        document.getElementById('editEmpPhone').value = phone;
        document.getElementById('editEmpAddress').value = address;
        document.getElementById('editEmpActive').value = isActive;
        document.getElementById('editEmpNotes').value = notes;
        new bootstrap.Modal(document.getElementById('editEmployeeModal')).show();
    }

    function openPaymentModal(id, name, pendingAmount) {
        document.getElementById('paymentForm').action = `{{ url('admin/staff/employees') }}/${id}/payments`;
        document.getElementById('payModalEmpName').innerText = name;
        document.getElementById('payModalPendingAmount').innerText = 'PKR ' + pendingAmount.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
        document.getElementById('payModalAmount').value = pendingAmount.toFixed(2);
        document.getElementById('payModalAmount').max = pendingAmount.toFixed(2);
        new bootstrap.Modal(document.getElementById('paymentModal')).show();
    }

    function openEditCashierModal(id, name, email, isActive) {
        document.getElementById('editCashierForm').action = `{{ url('admin/staff/cashiers') }}/${id}`;
        document.getElementById('editCashierName').value = name;
        document.getElementById('editCashierEmail').value = email;
        document.getElementById('editCashierActive').value = isActive;
        new bootstrap.Modal(document.getElementById('editCashierModal')).show();
    }

    function openEditAdminModal(id, name, email, isActive, isSelf) {
        document.getElementById('editAdminForm').action = `{{ url('admin/staff/admins') }}/${id}`;
        document.getElementById('editAdminName').value = name;
        document.getElementById('editAdminEmail').value = email;
        document.getElementById('editAdminActive').value = isActive;
        
        // Hide deactivation option if editing self to prevent locking out
        const activeWrapper = document.getElementById('adminActiveWrapper');
        if (isSelf) {
            document.getElementById('editAdminActive').value = 1;
            activeWrapper.style.display = 'none';
        } else {
            activeWrapper.style.display = 'block';
        }
        
        new bootstrap.Modal(document.getElementById('editAdminModal')).show();
    }
</script>

@endsection

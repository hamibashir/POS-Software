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
        <p>Manage customers eligible for credit sales, employees (POS operators), and system administrators.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button class="btn-pos-outline" data-bs-toggle="modal" data-bs-target="#addAdminModal">
            <i class="bi bi-shield-lock-fill"></i> Add Admin
        </button>
        <button class="btn-pos-outline" data-bs-toggle="modal" data-bs-target="#addCashierModal">
            <i class="bi bi-person-badge"></i> Add Employee
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
                <div class="stat-label">Employees</div>
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

{{-- Search Bar across Customers, Employees, and Admins --}}
<div class="pos-card mb-4" style="padding: 16px 20px; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    <form method="GET" action="{{ route('admin.staff.index') }}" id="staffSearchForm" class="d-flex align-items-center gap-2 flex-wrap">
        <div style="position:relative; flex: 1; min-width: 260px;">
            <i class="bi bi-search" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:15px;"></i>
            <input type="text"
                   name="search"
                   id="staffSearchInput"
                   value="{{ $search ?? '' }}"
                   placeholder="Search customers, employees, or admins by name, phone, address, email, notes..."
                   class="pos-input"
                   style="padding-left: 40px; padding-right: 36px; height: 44px; font-size: 14px; border-radius: 8px; width: 100%; border: 1.5px solid #d1d5db;"
                   autocomplete="off">
            <button type="button"
                    id="clearSearchBtn"
                    onclick="clearStaffSearch()"
                    style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:#9ca3af; font-size:16px; cursor:pointer; display: {{ !empty($search) ? 'block' : 'none' }}; line-height: 1;"
                    title="Clear search">
                <i class="bi bi-x-circle-fill"></i>
            </button>
        </div>
        <button type="submit" class="btn-pos" style="height: 44px; padding: 0 20px; display:flex; align-items:center; gap:6px;">
            <i class="bi bi-search"></i> Search
        </button>
        @if(!empty($search))
        <a href="{{ route('admin.staff.index') }}" class="btn-pos-outline" style="height: 44px; padding: 0 16px; display:flex; align-items:center; gap:6px; text-decoration:none;">
            <i class="bi bi-arrow-counterclockwise"></i> Reset
        </a>
        @endif
    </form>
    <div id="searchMatchNotice" style="font-size: 12px; color: #6b7280; margin-top: 8px; display: none;">
        <i class="bi bi-info-circle me-1"></i> Live filter active: matching <span id="searchMatchCount" style="font-weight:700; color:#111827;">0</span> total records across all tabs.
    </div>
</div>

{{-- Tabs --}}
<div class="nav-tabs-custom">
    <button class="nav-tab-btn active" id="tabBtnEmployees" onclick="switchTab('employees')">
        <i class="bi bi-person-lines-fill"></i> Customers (Credit Dues)
        <span class="badge" id="badgeEmployeesCount" style="background:#f3f4f6; color:#374151; border-radius:10px; font-size:11px;">{{ $employees->count() }}</span>
    </button>
    <button class="nav-tab-btn" id="tabBtnCashiers" onclick="switchTab('cashiers')">
        <i class="bi bi-person-badge"></i> Employees (POS Users)
        <span class="badge" id="badgeCashiersCount" style="background:#f3f4f6; color:#374151; border-radius:10px; font-size:11px;">{{ $cashiers->count() }}</span>
    </button>
    <button class="nav-tab-btn" id="tabBtnAdmins" onclick="switchTab('admins')">
        <i class="bi bi-shield-lock-fill"></i> Administrators
        <span class="badge" id="badgeAdminsCount" style="background:#f3f4f6; color:#374151; border-radius:10px; font-size:11px;">{{ $admins->count() }}</span>
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
                    <th style="text-align:right;">Opening Balance</th>
                    <th style="text-align:right;">Total Credit</th>
                    <th style="text-align:right;">Total Paid</th>
                    <th style="text-align:right;">Pending to Clear</th>
                    <th style="text-align:center;">Credit Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr class="staff-row employee-row" data-search="{{ strtolower($emp->name . ' ' . $emp->phone . ' ' . $emp->address . ' ' . ($emp->notes ?? '')) }}">
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
                    <td style="text-align:right; font-weight:700; color:#4f46e5;">
                        {{ pkr($emp->opening_balance ?? 0, 2) }}
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
                        @if($emp->adjustments->where('is_active', true)->count() > 0)
                        <div style="font-size:11px; color:#d97706; margin-top:3px; font-weight:600;">
                            <i class="bi bi-sliders"></i> Adjusted ({{ $emp->adjustments->where('is_active', true)->count() }})
                        </div>
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

                            <button type="button" class="btn-pos-outline" style="padding:4px 8px; font-size:12px; color:#b45309; border-color:#fde68a; background:#fffbeb;"
                                onclick="openAdjustDueModal({{ $emp->id }}, '{{ addslashes($emp->name) }}', {{ $emp->pending_payment }}, {{ json_encode($emp->adjustments->map(fn($a) => [
                                    'id' => $a->id,
                                    'date' => $a->created_at->format('d M Y, g:i A'),
                                    'admin' => $a->user?->name ?? 'Admin',
                                    'prev' => (float)$a->previous_due,
                                    'new' => (float)$a->new_due,
                                    'amount' => (float)$a->amount,
                                    'reason' => $a->reason,
                                    'is_active' => (bool)$a->is_active,
                                    'undo_url' => route('admin.staff.adjustments.toggle-undo', $a),
                                ])) }})"
                                title="Edit / Correct Dues (Undo/Redo Supported)">
                                <i class="bi bi-sliders"></i> Edit Due
                            </button>

                            <a href="{{ route('admin.staff.employees.ledger', $emp) }}" class="btn-pos-outline" style="padding:4px 8px; font-size:12px; text-decoration:none;" title="View Statement">
                                <i class="bi bi-journal-text"></i>
                            </a>

                            <button class="btn-pos-outline" style="padding:4px 8px; font-size:12px;"
                                onclick="openEditEmployeeModal({{ $emp->id }}, '{{ addslashes($emp->name) }}', '{{ addslashes($emp->phone) }}', '{{ addslashes($emp->address) }}', {{ (float)($emp->opening_balance ?? 0) }}, {{ $emp->is_active ? 1 : 0 }}, '{{ addslashes($emp->notes ?? '') }}')"
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
                        <p style="font-weight:600; color:#374151; margin-bottom:4px;">No customers found</p>
                        <p style="font-size:13px; margin:0;">{{ !empty($search) ? 'No customers match your search criteria.' : 'Add customers who are authorized to take items on credit sale.' }}</p>
                    </td>
                </tr>
                @endforelse
                <tr class="no-filter-match-row employee-no-match" style="display:none;">
                    <td colspan="8" style="text-align:center; padding:36px; color:#9ca3af;">
                        <i class="bi bi-search" style="font-size:28px; display:block; margin-bottom:8px; opacity:.5;"></i>
                        <p style="font-weight:600; color:#374151; margin-bottom:4px;">No matching customers</p>
                        <p style="font-size:13px; margin:0;">No customers match your current search query in this tab.</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════════ EMPLOYEES (POS USERS) TAB ═══════════════ --}}
<div id="tabCashiers" class="pos-card" style="display:none;">
    <div style="overflow-x:auto;">
        <table class="pos-table w-100">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Email / Login</th>
                    <th>Designation</th>
                    <th style="text-align:right;">Base Salary</th>
                    <th style="text-align:center;">Allowed Holidays</th>
                    <th>Status</th>
                    <th>Date Added</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cashiers as $c)
                <tr class="staff-row cashier-row" data-search="{{ strtolower($c->name . ' ' . $c->email . ' ' . ($c->designation ?? '') . ' ' . ($c->phone ?? '')) }}">
                    <td>
                        <div style="font-weight:700; color:#111827;">{{ $c->name }}</div>
                        @if($c->phone)
                        <div style="font-size:11px; color:#6b7280;"><i class="bi bi-telephone me-1"></i>{{ $c->phone }}</div>
                        @endif
                    </td>
                    <td>
                        <span style="font-family:monospace; color:#4b5563;">{{ $c->email }}</span>
                    </td>
                    <td>
                        <span class="badge-cashier">
                            <i class="bi bi-person-badge me-1"></i> {{ $c->designation ?? 'POS Operator' }}
                        </span>
                    </td>
                    <td style="text-align:right; font-weight:700; color:#111827;">
                        {{ pkr($c->salary ?? 0, 2) }}
                    </td>
                    <td style="text-align:center;">
                        <span class="badge" style="background:#f3f4f6; color:#374151; font-weight:700; border:1px solid #e5e7eb; padding:3px 8px;">
                            {{ $c->allowed_leaves ?? 2 }} days / mo
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
                                onclick="openEditCashierModal({{ $c->id }}, '{{ addslashes($c->name) }}', '{{ addslashes($c->email) }}', {{ $c->is_active ? 1 : 0 }}, {{ (float)($c->salary ?? 0) }}, {{ (int)($c->allowed_leaves ?? 2) }}, '{{ addslashes($c->phone ?? '') }}', '{{ addslashes($c->designation ?? '') }}')">
                                <i class="bi bi-pencil"></i> Edit
                            </button>

                            <form method="POST" action="{{ route('admin.staff.cashiers.destroy', $c) }}" style="display:inline;" onsubmit="return confirm('Remove employee {{ $c->name }}?');">
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
                    <td colspan="8" style="text-align:center; padding:48px; color:#9ca3af;">
                        <i class="bi bi-person-badge" style="font-size:36px; display:block; margin-bottom:8px; opacity:.5;"></i>
                        <p style="font-weight:600; color:#374151; margin-bottom:4px;">No employees found</p>
                        <p style="font-size:13px; margin:0;">{{ !empty($search) ? 'No employees match your search criteria.' : 'Create employee accounts with password and base salary to let staff operate the POS and manage their attendance/payroll.' }}</p>
                    </td>
                </tr>
                @endforelse
                <tr class="no-filter-match-row cashier-no-match" style="display:none;">
                    <td colspan="8" style="text-align:center; padding:36px; color:#9ca3af;">
                        <i class="bi bi-search" style="font-size:28px; display:block; margin-bottom:8px; opacity:.5;"></i>
                        <p style="font-weight:600; color:#374151; margin-bottom:4px;">No matching employees</p>
                        <p style="font-size:13px; margin:0;">No employees match your current search query in this tab.</p>
                    </td>
                </tr>
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
                <tr class="staff-row admin-row" data-search="{{ strtolower($a->name . ' ' . $a->email) }}">
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
                <tr class="no-filter-match-row admin-no-match" style="display:none;">
                    <td colspan="6" style="text-align:center; padding:36px; color:#9ca3af;">
                        <i class="bi bi-search" style="font-size:28px; display:block; margin-bottom:8px; opacity:.5;"></i>
                        <p style="font-weight:600; color:#374151; margin-bottom:4px;">No matching administrators</p>
                        <p style="font-size:13px; margin:0;">No administrators match your current search query in this tab.</p>
                    </td>
                </tr>
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
                    <label class="form-label" style="font-size:13px; font-weight:600;">Opening Balance (PKR)</label>
                    <input type="number" step="0.01" min="0" name="opening_balance" class="pos-input" placeholder="0.00" value="0.00">
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
                    <label class="form-label" style="font-size:13px; font-weight:600;">Opening Balance (PKR)</label>
                    <input type="number" step="0.01" min="0" name="opening_balance" id="editEmpOpeningBalance" class="pos-input" placeholder="0.00">
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

{{-- 3b. Adjust / Edit Customer Due Modal (Undo/Redo Supported) --}}
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
                                <div style="font-size:16px; font-weight:800; color:#1f2937;" id="adjModalEmpName">—</div>
                            </div>
                            <div class="text-end">
                                <div style="font-size:12px; color:#92400e; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Current Pending Due</div>
                                <div style="font-size:20px; font-weight:800; color:#b91c1c;" id="adjModalCurrentDue">PKR 0.00</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:13px; font-weight:600;">New Correct Due (PKR) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="new_due" id="adjModalNewDue" class="pos-input" required placeholder="0.00">
                            <div id="adjModalDeltaPreview" style="font-size:12px; font-weight:600; margin-top:4px; color:#4b5563;">
                                Adjustment change: <span id="adjModalDeltaText">0.00</span>
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

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-pos" style="background:#d97706; border-color:#d97706;"><i class="bi bi-check2-circle"></i> Save Due Adjustment</button>
                    </div>
                </div>
            </form>

            {{-- Adjustment History with 1-click Undo / Redo --}}
            <div style="border-top:1px solid #e5e7eb; padding:16px 20px; background:#f9fafb; border-bottom-left-radius:12px; border-bottom-right-radius:12px;">
                <h6 style="font-size:13px; font-weight:700; color:#374151; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                    <i class="bi bi-clock-history text-secondary"></i> Adjustment History & Undo / Redo
                </h6>
                <div id="adjHistoryEmpty" style="font-size:12px; color:#9ca3af; font-style:italic;">
                    No past adjustments recorded for this customer.
                </div>
                <div id="adjHistoryTableWrapper" style="display:none; max-height:220px; overflow-y:auto;">
                    <table class="pos-table w-100" style="font-size:12px;">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Recorded By</th>
                                <th>Change</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th style="text-align:right;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="adjHistoryTableBody">
                            {{-- Injected dynamically by JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 4. Add Employee Modal --}}
<div class="modal fade" id="addCashierModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.staff.cashiers.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-badge me-2 text-primary"></i>Add Employee Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Employee Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="pos-input" required placeholder="e.g. Employee 1">
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Email Address (Login) <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="pos-input" required placeholder="e.g. employee1@hassanstore.com">
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:13px; font-weight:600;">Designation / Title</label>
                        <input type="text" name="designation" class="pos-input" placeholder="e.g. Senior Cashier">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:13px; font-weight:600;">Phone Number</label>
                        <input type="text" name="phone" class="pos-input" placeholder="e.g. 03001234567">
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:13px; font-weight:600;">Monthly Base Salary (PKR)</label>
                        <input type="number" step="0.01" min="0" name="salary" class="pos-input" placeholder="e.g. 30000" value="0.00">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:13px; font-weight:600;">Allowed Holidays / Leaves</label>
                        <input type="number" min="0" max="31" name="allowed_leaves" class="pos-input" value="2" placeholder="e.g. 2">
                        <small class="text-muted" style="font-size:11px;">Max leaves before salary deduction</small>
                    </div>
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="pos-input" required minlength="6" placeholder="At least 6 characters">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-pos"><i class="bi bi-check-lg"></i> Create Employee</button>
            </div>
        </form>
    </div>
</div>

{{-- 5. Edit Employee Modal --}}
<div class="modal fade" id="editCashierModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editCashierForm" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Employee Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="editCashierName" class="pos-input" required>
                </div>
                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="editCashierEmail" class="pos-input" required>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:13px; font-weight:600;">Designation / Title</label>
                        <input type="text" name="designation" id="editCashierDesignation" class="pos-input">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:13px; font-weight:600;">Phone Number</label>
                        <input type="text" name="phone" id="editCashierPhone" class="pos-input">
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:13px; font-weight:600;">Monthly Base Salary (PKR)</label>
                        <input type="number" step="0.01" min="0" name="salary" id="editCashierSalary" class="pos-input">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:13px; font-weight:600;">Allowed Holidays / Leaves</label>
                        <input type="number" min="0" max="31" name="allowed_leaves" id="editCashierAllowedLeaves" class="pos-input">
                        <small class="text-muted" style="font-size:11px;">Max leaves before salary deduction</small>
                    </div>
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
                <button type="submit" class="btn-pos"><i class="bi bi-check-lg"></i> Update Employee</button>
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

    function filterStaff(query) {
        query = (query || '').toLowerCase().trim();
        const clearBtn = document.getElementById('clearSearchBtn');
        const notice = document.getElementById('searchMatchNotice');
        const matchCountEl = document.getElementById('searchMatchCount');

        if (clearBtn) {
            clearBtn.style.display = query ? 'block' : 'none';
        }

        // Filter Customer / Employee rows
        let empCount = 0;
        document.querySelectorAll('.employee-row').forEach(row => {
            const text = (row.getAttribute('data-search') || '').toLowerCase();
            const match = !query || text.includes(query);
            row.style.display = match ? '' : 'none';
            if (match) empCount++;
        });
        const empNoMatch = document.querySelector('.employee-no-match');
        if (empNoMatch) {
            const totalEmp = document.querySelectorAll('.employee-row').length;
            empNoMatch.style.display = (query && totalEmp > 0 && empCount === 0) ? '' : 'none';
        }
        const badgeEmp = document.getElementById('badgeEmployeesCount');
        if (badgeEmp) badgeEmp.innerText = empCount;

        // Filter Cashier rows
        let cashierCount = 0;
        document.querySelectorAll('.cashier-row').forEach(row => {
            const text = (row.getAttribute('data-search') || '').toLowerCase();
            const match = !query || text.includes(query);
            row.style.display = match ? '' : 'none';
            if (match) cashierCount++;
        });
        const cashierNoMatch = document.querySelector('.cashier-no-match');
        if (cashierNoMatch) {
            const totalCashier = document.querySelectorAll('.cashier-row').length;
            cashierNoMatch.style.display = (query && totalCashier > 0 && cashierCount === 0) ? '' : 'none';
        }
        const badgeCashier = document.getElementById('badgeCashiersCount');
        if (badgeCashier) badgeCashier.innerText = cashierCount;

        // Filter Admin rows
        let adminCount = 0;
        document.querySelectorAll('.admin-row').forEach(row => {
            const text = (row.getAttribute('data-search') || '').toLowerCase();
            const match = !query || text.includes(query);
            row.style.display = match ? '' : 'none';
            if (match) adminCount++;
        });
        const adminNoMatch = document.querySelector('.admin-no-match');
        if (adminNoMatch) {
            const totalAdmin = document.querySelectorAll('.admin-row').length;
            adminNoMatch.style.display = (query && totalAdmin > 0 && adminCount === 0) ? '' : 'none';
        }
        const badgeAdmin = document.getElementById('badgeAdminsCount');
        if (badgeAdmin) badgeAdmin.innerText = adminCount;

        // Update match notice
        const totalMatches = empCount + cashierCount + adminCount;
        if (notice && matchCountEl) {
            if (query) {
                notice.style.display = 'block';
                matchCountEl.innerText = totalMatches;
            } else {
                notice.style.display = 'none';
            }
        }
    }

    function clearStaffSearch() {
        const input = document.getElementById('staffSearchInput');
        if (input) {
            input.value = '';
            filterStaff('');
            input.focus();
        }
        const url = new URL(window.location.href);
        if (url.searchParams.has('search')) {
            url.searchParams.delete('search');
            window.location.href = url.pathname;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('staffSearchInput');
        if (input) {
            input.addEventListener('input', function() {
                filterStaff(this.value);
            });
            if (input.value) {
                filterStaff(input.value);
            }
        }
    });

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

    let currentAdjPending = 0;
    function openAdjustDueModal(id, name, pendingAmount, history) {
        currentAdjPending = pendingAmount;
        document.getElementById('adjustDueForm').action = `{{ url('admin/staff/employees') }}/${id}/adjust-due`;
        document.getElementById('adjModalEmpName').innerText = name;
        document.getElementById('adjModalCurrentDue').innerText = 'PKR ' + pendingAmount.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
        const newDueInput = document.getElementById('adjModalNewDue');
        newDueInput.value = pendingAmount.toFixed(2);
        document.getElementById('adjModalReason').value = '';
        updateAdjDeltaPreview(pendingAmount);

        // Populate history
        const emptyEl = document.getElementById('adjHistoryEmpty');
        const wrapperEl = document.getElementById('adjHistoryTableWrapper');
        const tbody = document.getElementById('adjHistoryTableBody');
        tbody.innerHTML = '';

        if (!history || history.length === 0) {
            emptyEl.style.display = 'block';
            wrapperEl.style.display = 'none';
        } else {
            emptyEl.style.display = 'none';
            wrapperEl.style.display = 'block';
            history.forEach(item => {
                const tr = document.createElement('tr');
                const deltaColor = item.amount < 0 ? '#059669' : '#dc2626';
                const deltaSign = item.amount > 0 ? '+' : '';
                const statusBadge = item.is_active 
                    ? '<span class="badge" style="background:#d1fae5; color:#065f46; font-size:11px;">Active</span>' 
                    : '<span class="badge" style="background:#fee2e2; color:#991b1b; font-size:11px;">Undone</span>';

                const btnClass = item.is_active ? 'btn-pos-outline' : 'btn-pos';
                const btnStyle = item.is_active 
                    ? 'padding:2px 8px; font-size:11px; border-color:#fca5a5; color:#dc2626;' 
                    : 'padding:2px 8px; font-size:11px; background:#059669; border-color:#059669;';
                const btnIcon = item.is_active ? 'bi-arrow-counterclockwise' : 'bi-arrow-clockwise';
                const btnText = item.is_active ? 'Undo' : 'Redo';
                const confirmPrompt = item.is_active 
                    ? 'Undo this adjustment and restore previous due?' 
                    : 'Redo this adjustment and re-apply due change?';

                tr.innerHTML = `
                    <td style="white-space:nowrap;">${item.date}</td>
                    <td>${item.admin}</td>
                    <td style="white-space:nowrap; font-weight:700; color:${deltaColor};">
                        PKR ${item.prev.toFixed(2)} → ${item.new.toFixed(2)} (${deltaSign}${item.amount.toFixed(2)})
                    </td>
                    <td style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${item.reason}">${item.reason}</td>
                    <td>${statusBadge}</td>
                    <td style="text-align:right;">
                        <form method="POST" action="${item.undo_url}" style="display:inline;" onsubmit="return confirm('${confirmPrompt}');">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="${btnClass}" style="${btnStyle}">
                                <i class="bi ${btnIcon}"></i> ${btnText}
                            </button>
                        </form>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

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

    function openEditCashierModal(id, name, email, isActive, salary, allowedLeaves, phone, designation) {
        document.getElementById('editCashierForm').action = `{{ url('admin/staff/cashiers') }}/${id}`;
        document.getElementById('editCashierName').value = name;
        document.getElementById('editCashierEmail').value = email;
        document.getElementById('editCashierActive').value = isActive;
        document.getElementById('editCashierSalary').value = salary || 0;
        document.getElementById('editCashierAllowedLeaves').value = allowedLeaves !== undefined ? allowedLeaves : 2;
        document.getElementById('editCashierPhone').value = phone || '';
        document.getElementById('editCashierDesignation').value = designation || '';
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

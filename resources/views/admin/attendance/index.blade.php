@extends('layouts.admin')

@section('title', 'Daily Employee Attendance')

@push('styles')
<style>
    .stat-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px 18px; display:flex; align-items:center; gap:14px; height:100%; box-shadow:0 1px 3px rgba(0,0,0,.03); }
    .stat-icon  { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
    .stat-label { font-size:11px; color:#6b7280; font-weight:600; text-transform:uppercase; letter-spacing:.6px; }
    .stat-value { font-size:22px; font-weight:800; color:#111827; line-height:1.2; }
    .stat-sub   { font-size:11px; color:#9ca3af; }

    /* Attendance Toggle Buttons */
    .att-toggle-group {
        display: inline-flex;
        background: #f3f4f6;
        padding: 3px;
        border-radius: 8px;
        gap: 3px;
        border: 1px solid #e5e7eb;
    }

    .att-btn {
        border: none;
        background: transparent;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 700;
        border-radius: 6px;
        cursor: pointer;
        color: #4b5563;
        transition: all .15s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }

    .att-btn:hover { background: rgba(255,255,255,0.7); }

    .att-btn.active-present  { background: #10b981; color: #fff; box-shadow: 0 1px 2px rgba(16,185,129,0.3); }
    .att-btn.active-absent   { background: #ef4444; color: #fff; box-shadow: 0 1px 2px rgba(239,68,68,0.3); }
    .att-btn.active-half_day { background: #f59e0b; color: #fff; box-shadow: 0 1px 2px rgba(245,158,11,0.3); }
    .att-btn.active-leave    { background: #3b82f6; color: #fff; box-shadow: 0 1px 2px rgba(59,130,246,0.3); }
    .att-btn.active-holiday  { background: #8b5cf6; color: #fff; box-shadow: 0 1px 2px rgba(139,92,246,0.3); }

    .time-input-sm {
        height: 32px;
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        width: 105px;
    }

    .notes-input-sm {
        height: 32px;
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        width: 100%;
        min-width: 140px;
    }
</style>
@endpush

@section('content')

<div class="page-hero d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h1><i class="bi bi-calendar-check me-2" style="color:var(--pos-primary)"></i>Daily Employee Attendance & Advance Salary</h1>
        <p>Record daily presence, check-in, leaves, and manage advance salary payouts with auto-deduction from monthly salary.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <button type="button" class="btn-pos" style="background:#7c3aed; border-color:#7c3aed;" onclick="openAdvanceModal()">
            <i class="bi bi-cash-stack"></i> Give Advance Salary
        </button>
        <a href="{{ route('admin.attendance.payroll', ['month' => $date->format('Y-m')]) }}" class="btn-pos-outline" style="text-decoration:none;">
            <i class="bi bi-calculator"></i> Monthly Payroll & Salaries
        </a>
        <a href="{{ route('admin.staff.index') }}" class="btn-pos-outline" style="text-decoration:none;">
            <i class="bi bi-people"></i> Manage Staff
        </a>
    </div>
</div>

{{-- Top Summary Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe; color:#0284c7;"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-label">Total Staff</div>
                <div class="stat-value">{{ $stats['total_employees'] }}</div>
                <div class="stat-sub">active staff</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5; color:#059669;"><i class="bi bi-check-circle-fill"></i></div>
            <div>
                <div class="stat-label">Present</div>
                <div class="stat-value" style="color:#059669;">{{ $stats['present'] }}</div>
                <div class="stat-sub">marked present</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2; color:#dc2626;"><i class="bi bi-x-circle-fill"></i></div>
            <div>
                <div class="stat-label">Absent</div>
                <div class="stat-value" style="color:#dc2626;">{{ $stats['absent'] }}</div>
                <div class="stat-sub">unexcused</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7; color:#d97706;"><i class="bi bi-clock-half"></i></div>
            <div>
                <div class="stat-label">Half Day</div>
                <div class="stat-value" style="color:#d97706;">{{ $stats['half_day'] }}</div>
                <div class="stat-sub">0.5 day duty</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff; color:#2563eb;"><i class="bi bi-calendar-event"></i></div>
            <div>
                <div class="stat-label">Leave / Holiday</div>
                <div class="stat-value" style="color:#2563eb;">{{ $stats['leave'] + $stats['holiday'] }}</div>
                <div class="stat-sub">{{ $stats['leave'] }} leave, {{ $stats['holiday'] }} holiday</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f3f4f6; color:#6b7280;"><i class="bi bi-question-circle"></i></div>
            <div>
                <div class="stat-label">Unmarked</div>
                <div class="stat-value" style="color:{{ $stats['unmarked'] > 0 ? '#b91c1c' : '#4b5563' }};">{{ $stats['unmarked'] }}</div>
                <div class="stat-sub">pending today</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 col-lg">
        <div class="stat-card" style="border-color:#ddd6fe; background:#faf5ff;">
            <div class="stat-icon" style="background:#ede9fe; color:#7c3aed;"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="stat-label" style="color:#6d28d9;">Advances ({{ $date->format('M') }})</div>
                <div class="stat-value" style="color:#7c3aed;">{{ pkr($stats['month_advances'], 2) }}</div>
                <div class="stat-sub" style="color:#8b5cf6;">{{ pkr($stats['today_advances'], 2) }} today</div>
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

{{-- Date Selector and Quick Actions Card --}}
<div class="pos-card mb-4" style="padding: 16px 20px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
            <label for="attDate" class="fw-bold fs-6 text-dark" style="white-space:nowrap;">
                <i class="bi bi-calendar3 me-1 text-primary"></i> Attendance Date:
            </label>
            <input type="date"
                   id="attDate"
                   name="date"
                   value="{{ $date->toDateString() }}"
                   class="pos-input"
                   style="width: 170px; height: 40px; font-weight: 600;"
                   onchange="this.form.submit()">

            <div class="d-flex gap-1">
                <a href="{{ route('admin.attendance.index', ['date' => $date->copy()->subDay()->toDateString()]) }}" class="btn-pos-outline" style="height:40px; padding:0 12px; display:inline-flex; align-items:center;" title="Previous Day">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <a href="{{ route('admin.attendance.index', ['date' => now()->toDateString()]) }}" class="btn-pos-outline {{ $date->isToday() ? 'active' : '' }}" style="height:40px; padding:0 14px; display:inline-flex; align-items:center;">
                    Today
                </a>
                <a href="{{ route('admin.attendance.index', ['date' => $date->copy()->addDay()->toDateString()]) }}" class="btn-pos-outline" style="height:40px; padding:0 12px; display:inline-flex; align-items:center;" title="Next Day">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </form>

        <div class="d-flex gap-2 flex-wrap align-items-center">
            <span style="font-size:12px; color:#6b7280; font-weight:600;">Quick Actions:</span>
            <button type="button" class="btn btn-sm btn-outline-success fw-bold" onclick="markAll('present')">
                <i class="bi bi-check-all"></i> All Present
            </button>
            <button type="button" class="btn btn-sm btn-outline-purple fw-bold" style="color:#7c3aed; border-color:#ddd6fe;" onclick="markAll('holiday')">
                <i class="bi bi-building"></i> Store Holiday
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger fw-bold" onclick="markAll('absent')">
                <i class="bi bi-x"></i> All Absent
            </button>
        </div>
    </div>
</div>

{{-- Attendance Sheet Form --}}
<form method="POST" action="{{ route('admin.attendance.mark') }}">
    @csrf
    <input type="hidden" name="date" value="{{ $date->toDateString() }}">

    <div class="pos-card mb-4" style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2" style="background:#f9fafb;">
            <div class="fw-bold" style="font-size:14px; color:#111827;">
                <i class="bi bi-card-checklist me-1 text-primary"></i> Attendance Sheet for {{ $date->format('l, d F Y') }}
            </div>
            <button type="submit" class="btn-pos" style="padding:7px 24px; font-weight:700;">
                <i class="bi bi-save2 me-1"></i> Save Attendance
            </button>
        </div>

        <div style="overflow-x:auto;">
            <table class="pos-table w-100 align-middle">
                <thead>
                    <tr>
                        <th style="width:23%;">Employee Name</th>
                        <th style="width:13%;">Monthly Salary</th>
                        <th style="width:13%;">Advance Salary</th>
                        <th style="width:11%;">Allowed Holidays</th>
                        <th style="width:23%;">Attendance Status</th>
                        <th style="width:9%;">Timing</th>
                        <th style="width:8%;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                    @php
                        $att = $existingAttendances[$emp->id] ?? null;
                        $currentStatus = $att?->status ?? 'present';
                        $empMonthAdvance = (float) $emp->salaryAdvances->sum('amount');
                    @endphp
                    <tr>
                        {{-- Employee --}}
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar" style="width:34px; height:34px; background:var(--pos-primary); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; flex-shrink:0;">
                                    {{ strtoupper(substr($emp->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:700; color:#111827;">{{ $emp->name }}</div>
                                    <div style="font-size:11px; color:#6b7280;">{{ $emp->email }} @if($emp->phone) · {{ $emp->phone }} @endif</div>
                                </div>
                            </div>
                        </td>

                        {{-- Salary --}}
                        <td>
                            <div style="font-weight:700; color:#111827; font-size:13px;">{{ pkr($emp->salary ?? 0, 2) }}</div>
                            <div style="font-size:11px; color:#9ca3af;">per month</div>
                        </td>

                        {{-- Salary Advance --}}
                        <td>
                            @if($empMonthAdvance > 0)
                                <div style="font-weight:700; color:#7c3aed; font-size:13px;">{{ pkr($empMonthAdvance, 2) }}</div>
                                <div style="font-size:11px; color:#8b5cf6;">in {{ $date->format('M Y') }}</div>
                            @else
                                <span style="color:#9ca3af; font-size:12px;">PKR 0.00</span>
                            @endif
                            <button type="button" class="btn btn-sm mt-1" style="padding:1px 6px; font-size:10px; background:#f5f3ff; color:#7c3aed; border:1px solid #ddd6fe; border-radius:4px; font-weight:700;" onclick="openAdvanceModal({{ $emp->id }}, '{{ addslashes($emp->name) }}')">
                                <i class="bi bi-plus"></i> Advance
                            </button>
                        </td>

                        {{-- Allowed Holidays --}}
                        <td>
                            <span style="background:#f3f4f6; color:#374151; border-radius:20px; padding:3px 10px; font-size:12px; font-weight:700;">
                                {{ $emp->allowed_leaves ?? 4 }} days/mo
                            </span>
                        </td>

                        {{-- Status Toggles --}}
                        <td>
                            <input type="hidden" name="attendances[{{ $emp->id }}][status]" id="status_input_{{ $emp->id }}" value="{{ $currentStatus }}">

                            <div class="att-toggle-group" id="group_{{ $emp->id }}">
                                <button type="button" class="att-btn {{ $currentStatus === 'present' ? 'active-present' : '' }}" onclick="selectStatus({{ $emp->id }}, 'present')">
                                    <i class="bi bi-check-circle"></i> Present
                                </button>
                                <button type="button" class="att-btn {{ $currentStatus === 'absent' ? 'active-absent' : '' }}" onclick="selectStatus({{ $emp->id }}, 'absent')">
                                    <i class="bi bi-x-circle"></i> Absent
                                </button>
                                <button type="button" class="att-btn {{ $currentStatus === 'half_day' ? 'active-half_day' : '' }}" onclick="selectStatus({{ $emp->id }}, 'half_day')">
                                    <i class="bi bi-clock-half"></i> Half Day
                                </button>
                                <button type="button" class="att-btn {{ $currentStatus === 'leave' ? 'active-leave' : '' }}" onclick="selectStatus({{ $emp->id }}, 'leave')">
                                    <i class="bi bi-calendar-minus"></i> Leave
                                </button>
                                <button type="button" class="att-btn {{ $currentStatus === 'holiday' ? 'active-holiday' : '' }}" onclick="selectStatus({{ $emp->id }}, 'holiday')">
                                    <i class="bi bi-star"></i> Holiday
                                </button>
                            </div>
                        </td>

                        {{-- Timings --}}
                        <td>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="time" name="attendances[{{ $emp->id }}][check_in]" value="{{ $att?->check_in }}" class="time-input-sm" title="Check In Time" placeholder="In">
                                <span style="color:#9ca3af; font-size:11px;">-</span>
                                <input type="time" name="attendances[{{ $emp->id }}][check_out]" value="{{ $att?->check_out }}" class="time-input-sm" title="Check Out Time" placeholder="Out">
                            </div>
                        </td>

                        {{-- Notes --}}
                        <td>
                            <input type="text" name="attendances[{{ $emp->id }}][notes]" value="{{ $att?->notes }}" class="notes-input-sm" placeholder="e.g. Sick, Permission...">
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:48px; color:#9ca3af;">
                            <i class="bi bi-people" style="font-size:36px; display:block; margin-bottom:8px; opacity:.5;"></i>
                            <p style="font-weight:600; color:#374151; margin-bottom:4px;">No active employees found</p>
                            <p style="font-size:13px; margin:0;"><a href="{{ route('admin.staff.index') }}" style="color:var(--pos-primary);">Add employees</a> in Staff Management first.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->isNotEmpty())
        <div class="p-3 border-top d-flex justify-content-end" style="background:#f9fafb;">
            <button type="submit" class="btn-pos" style="padding:8px 30px; font-weight:700; font-size:14px;">
                <i class="bi bi-save2 me-1"></i> Save Attendance
            </button>
        </div>
        @endif
    </div>
</form>

{{-- Salary Advances Records Table --}}
<div class="pos-card mb-4" style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">
    <div class="p-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2" style="background:#fcfaff;">
        <div>
            <div class="fw-bold" style="font-size:15px; color:#5b21b6;">
                <i class="bi bi-cash-stack me-2" style="color:#7c3aed;"></i> Salary Advances History ({{ $date->format('F Y') }})
            </div>
            <div style="font-size:12px; color:#6b7280; margin-top:2px;">
                Record of advance salary payouts given to employees. System automatically deducts these from total salary in monthly payroll.
            </div>
        </div>
        <button type="button" class="btn-pos" style="background:#7c3aed; border-color:#7c3aed; padding:6px 16px; font-size:13px;" onclick="openAdvanceModal()">
            <i class="bi bi-plus-circle me-1"></i> Give Advance Payment
        </button>
    </div>

    <div style="overflow-x:auto;">
        <table class="pos-table w-100 align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee Name</th>
                    <th style="text-align:right;">Advance Amount</th>
                    <th>Payment Method</th>
                    <th>Notes / Purpose</th>
                    <th style="text-align:center;">Deduction Status</th>
                    <th>Recorded By</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monthAdvances as $adv)
                <tr>
                    <td style="white-space:nowrap;">
                        <div style="font-weight:700; font-size:13px; color:#111827;">{{ $adv->date->format('d M Y') }}</div>
                        @if($adv->date->isToday())
                            <span class="badge" style="background:#d1fae5; color:#065f46; font-size:10px;">Today</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:700; color:#111827;">{{ $adv->employee?->name ?? 'Unknown' }}</div>
                        <div style="font-size:11px; color:#6b7280;">{{ $adv->employee?->designation ?? 'Employee' }}</div>
                    </td>
                    <td style="text-align:right;">
                        <span style="font-size:14px; font-weight:800; color:#7c3aed;">
                            {{ pkr($adv->amount, 2) }}
                        </span>
                    </td>
                    <td>
                        <span style="background:#f3f4f6; color:#374151; border-radius:12px; padding:2px 8px; font-size:11px; font-weight:600; text-transform:capitalize;">
                            {{ str_replace('_', ' ', $adv->payment_method) }}
                        </span>
                    </td>
                    <td style="max-width:240px; font-size:12px; color:#4b5563;">
                        {{ $adv->notes ?: '—' }}
                    </td>
                    <td style="text-align:center;">
                        @if($adv->status === 'deducted' || $adv->salary_payment_id)
                            <span class="badge" style="background:#d1fae5; color:#065f46; font-size:11px; font-weight:700;">
                                <i class="bi bi-check-circle me-1"></i> Deducted in Salary
                            </span>
                        @else
                            <span class="badge" style="background:#fef3c7; color:#92400e; font-size:11px; font-weight:700;">
                                <i class="bi bi-clock me-1"></i> Pending Deduction
                            </span>
                        @endif
                    </td>
                    <td style="font-size:12px; color:#6b7280;">
                        {{ $adv->createdBy?->name ?? 'Admin' }}
                    </td>
                    <td style="text-align:right;">
                        <div class="d-inline-flex gap-1">
                            <button type="button" class="btn-pos-outline" style="padding:3px 7px; font-size:11px;"
                                onclick="openEditAdvanceModal({{ $adv->id }}, '{{ addslashes($adv->employee?->name ?? '') }}', {{ (float)$adv->amount }}, '{{ $adv->date->format('Y-m-d') }}', '{{ $adv->payment_method }}', '{{ addslashes($adv->notes ?? '') }}')"
                                title="Edit Advance Record">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.attendance.advances.destroy', $adv) }}" style="display:inline;" onsubmit="return confirm('Delete this advance payment record of PKR {{ number_format($adv->amount, 2) }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-pos-outline" style="padding:3px 7px; font-size:11px; border-color:#fee2e2; color:#dc2626;" title="Delete Advance">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:32px; color:#9ca3af;">
                        <i class="bi bi-cash-stack" style="font-size:32px; display:block; margin-bottom:6px; opacity:.4;"></i>
                        <p style="font-weight:600; color:#4b5563; margin-bottom:2px;">No advances recorded for {{ $date->format('F Y') }}</p>
                        <p style="font-size:12px; margin:0;">When an employee takes an advance, record it here to automatically deduct it from their monthly salary.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Record Advance Modal --}}
<div class="modal fade" id="advanceModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.attendance.advances.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cash-stack me-2" style="color:#7c3aed;"></i>Record Advance Salary Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <div class="p-3 rounded" style="background:#f5f3ff; border:1px solid #ddd6fe;">
                    <div style="font-size:12px; font-weight:700; color:#5b21b6;">
                        <i class="bi bi-info-circle me-1"></i> AUTOMATIC SALARY DEDUCTION
                    </div>
                    <div style="font-size:12px; color:#6d28d9; margin-top:2px;">
                        This advance will be recorded into the system and automatically deducted from the employee's total salary during monthly payroll.
                    </div>
                </div>

                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Select Employee <span class="text-danger">*</span></label>
                    <select name="user_id" id="advModalUserId" class="pos-input" required>
                        <option value="">-- Choose Employee --</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->name }} (Monthly Salary: PKR {{ number_format($e->salary ?? 0, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:13px; font-weight:600;">Advance Amount (PKR) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="pos-input" required placeholder="e.g. 5000">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:13px; font-weight:600;">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="pos-input" required value="{{ $date->toDateString() }}">
                    </div>
                </div>

                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Payment Method <span class="text-danger">*</span></label>
                    <select name="payment_method" class="pos-input" required>
                        <option value="cash">💵 Cash in Hand</option>
                        <option value="bank">🏦 Bank Transfer</option>
                        <option value="cheque">📄 Cheque</option>
                        <option value="other">📱 Mobile Wallet / Other</option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Notes / Purpose</label>
                    <input type="text" name="notes" class="pos-input" placeholder="e.g. Advance requested for family expense">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-pos" style="background:#7c3aed; border-color:#7c3aed;"><i class="bi bi-check-circle"></i> Save Advance Payment</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Advance Modal --}}
<div class="modal fade" id="editAdvanceModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editAdvanceForm" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Advance Salary Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <div>
                    <label class="form-label text-muted" style="font-size:12px; font-weight:600; text-transform:uppercase;">Employee</label>
                    <div id="editAdvEmpName" style="font-size:16px; font-weight:700; color:#111827;">—</div>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:13px; font-weight:600;">Advance Amount (PKR) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="editAdvAmount" class="pos-input" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:13px; font-weight:600;">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="editAdvDate" class="pos-input" required>
                    </div>
                </div>

                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Payment Method <span class="text-danger">*</span></label>
                    <select name="payment_method" id="editAdvMethod" class="pos-input" required>
                        <option value="cash">💵 Cash in Hand</option>
                        <option value="bank">🏦 Bank Transfer</option>
                        <option value="cheque">📄 Cheque</option>
                        <option value="other">📱 Mobile Wallet / Other</option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size:13px; font-weight:600;">Notes / Purpose</label>
                    <input type="text" name="notes" id="editAdvNotes" class="pos-input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-pos"><i class="bi bi-check-lg"></i> Update Record</button>
            </div>
        </form>
    </div>
</div>

<script>
    function selectStatus(empId, status) {
        document.getElementById('status_input_' + empId).value = status;
        const group = document.getElementById('group_' + empId);
        group.querySelectorAll('.att-btn').forEach(btn => {
            btn.className = 'att-btn';
        });

        const activeClass = 'active-' + status;
        const clickedBtn = group.querySelector(`[onclick*="'${status}'"]`);
        if (clickedBtn) {
            clickedBtn.classList.add(activeClass);
        }
    }

    function markAll(status) {
        document.querySelectorAll('[id^="status_input_"]').forEach(input => {
            const empId = input.id.replace('status_input_', '');
            selectStatus(empId, status);
        });
    }

    function openAdvanceModal(userId = null, empName = null) {
        const select = document.getElementById('advModalUserId');
        if (userId) {
            select.value = userId;
        } else {
            select.value = '';
        }
        new bootstrap.Modal(document.getElementById('advanceModal')).show();
    }

    function openEditAdvanceModal(id, empName, amount, date, method, notes) {
        document.getElementById('editAdvanceForm').action = `{{ url('admin/attendance/advances') }}/${id}`;
        document.getElementById('editAdvEmpName').innerText = empName;
        document.getElementById('editAdvAmount').value = parseFloat(amount).toFixed(2);
        document.getElementById('editAdvDate').value = date;
        document.getElementById('editAdvMethod').value = method;
        document.getElementById('editAdvNotes').value = notes;
        new bootstrap.Modal(document.getElementById('editAdvanceModal')).show();
    }
</script>

@endsection

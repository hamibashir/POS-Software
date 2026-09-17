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
        <h1><i class="bi bi-calendar-check me-2" style="color:var(--pos-primary)"></i>Daily Employee Attendance</h1>
        <p>Record daily check-in, presence, leaves, and holidays for store employees.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
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
    <div class="col-6 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe; color:#0284c7;"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-label">Total Staff</div>
                <div class="stat-value">{{ $stats['total_employees'] }}</div>
                <div class="stat-sub">active employees</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5; color:#059669;"><i class="bi bi-check-circle-fill"></i></div>
            <div>
                <div class="stat-label">Present</div>
                <div class="stat-value" style="color:#059669;">{{ $stats['present'] }}</div>
                <div class="stat-sub">full day marked</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2; color:#dc2626;"><i class="bi bi-x-circle-fill"></i></div>
            <div>
                <div class="stat-label">Absent</div>
                <div class="stat-value" style="color:#dc2626;">{{ $stats['absent'] }}</div>
                <div class="stat-sub">unexcused</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7; color:#d97706;"><i class="bi bi-clock-half"></i></div>
            <div>
                <div class="stat-label">Half Day</div>
                <div class="stat-value" style="color:#d97706;">{{ $stats['half_day'] }}</div>
                <div class="stat-sub">0.5 day duty</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff; color:#2563eb;"><i class="bi bi-calendar-event"></i></div>
            <div>
                <div class="stat-label">Leave / Holiday</div>
                <div class="stat-value" style="color:#2563eb;">{{ $stats['leave'] + $stats['holiday'] }}</div>
                <div class="stat-sub">{{ $stats['leave'] }} leave, {{ $stats['holiday'] }} holiday</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f3f4f6; color:#6b7280;"><i class="bi bi-question-circle"></i></div>
            <div>
                <div class="stat-label">Unmarked</div>
                <div class="stat-value" style="color:{{ $stats['unmarked'] > 0 ? '#b91c1c' : '#4b5563' }};">{{ $stats['unmarked'] }}</div>
                <div class="stat-sub">pending today</div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="pos-alert pos-alert-success mb-3">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
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
                        <th style="width:25%;">Employee Name</th>
                        <th style="width:12%;">Monthly Salary</th>
                        <th style="width:12%;">Allowed Holidays</th>
                        <th style="width:28%;">Attendance Status</th>
                        <th style="width:11%;">Timing (In / Out)</th>
                        <th style="width:12%;">Notes / Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                    @php
                        $att = $existingAttendances[$emp->id] ?? null;
                        $currentStatus = $att?->status ?? 'present';
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
                        <td colspan="6" style="text-align:center; padding:48px; color:#9ca3af;">
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
</script>

@endsection

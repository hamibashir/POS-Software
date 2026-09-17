@extends('layouts.admin')

@section('title', 'Monthly Payroll & Salary Auto-Calculation')

@push('styles')
<style>
    .stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        height: 100%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .stat-label {
        font-size: 11px;
        color: #9ca3af;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
    }
    .stat-value {
        font-size: 20px;
        font-weight: 800;
        color: #111827;
        margin-top: 2px;
    }
    .stat-sub {
        font-size: 11px;
        color: #6b7280;
        margin-top: 2px;
    }

    .badge-status-paid {
        background: #d1fae5;
        color: #065f46;
        font-weight: 700;
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .badge-status-pending {
        background: #fee2e2;
        color: #b91c1c;
        font-weight: 700;
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .pill-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        padding: 0 6px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }
    .pill-present { background: #d1fae5; color: #047857; }
    .pill-half    { background: #fef3c7; color: #b45309; }
    .pill-absent  { background: #fee2e2; color: #b91c1c; }
    .pill-leave   { background: #e0f2fe; color: #0369a1; }
    .pill-holiday { background: #ede9fe; color: #6d28d9; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="page-hero d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1><i class="bi bi-cash-stack me-2" style="color:var(--pos-primary)"></i>Monthly Payroll & Salary Calculation</h1>
        <p>Auto-calculated net payable salaries for {{ $parsedDate->format('F Y') }} based on attendance and allowed holiday thresholds.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <a href="{{ route('admin.attendance.index') }}" class="btn-pos-outline">
            <i class="bi bi-calendar-check"></i> Daily Attendance Sheet
        </a>
        <a href="{{ route('admin.staff.index') }}" class="btn-pos-outline">
            <i class="bi bi-people"></i> Manage Employees
        </a>
    </div>
</div>

{{-- Month Filter Bar --}}
<div class="pos-card mb-4" style="padding: 16px 20px;">
    <form method="GET" action="{{ route('admin.attendance.payroll') }}" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
            <label class="fw-bold text-muted small text-uppercase" style="letter-spacing: .5px;">Select Month:</label>
            <input type="month" name="month" value="{{ $monthYearStr }}" class="pos-input" style="width: auto; height: 40px; font-weight: 700;" onchange="this.form.submit()">
            <button type="submit" class="btn-pos" style="height: 40px; padding: 0 16px;">
                <i class="bi bi-arrow-clockwise"></i> Recalculate
            </button>
        </div>
        <div class="text-muted small">
            <i class="bi bi-info-circle me-1"></i> Calculation rule: <strong>Daily Rate = Base Salary &divide; {{ $parsedDate->daysInMonth }} days</strong>. Absences beyond allowed threshold reduce salary.
        </div>
    </form>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe; color:#0284c7;"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="stat-label">Total Staff</div>
                <div class="stat-value">{{ $payrolls->count() }}</div>
                <div class="stat-sub">{{ $totals['paid_count'] }} paid &bull; {{ $totals['pending_count'] }} pending</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ede9fe; color:#7c3aed;"><i class="bi bi-wallet2"></i></div>
            <div>
                <div class="stat-label">Total Base Salaries</div>
                <div class="stat-value">{{ pkr($totals['total_base_salary'], 2) }}</div>
                <div class="stat-sub">standard monthly sum</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fee2e2; color:#dc2626;"><i class="bi bi-scissors"></i></div>
            <div>
                <div class="stat-label">Holiday / Absence Deductions</div>
                <div class="stat-value" style="color:#b91c1c;">- {{ pkr($totals['total_deductions'], 2) }}</div>
                <div class="stat-sub">for unapproved absences</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#d1fae5; color:#059669;"><i class="bi bi-cash-coin"></i></div>
            <div>
                <div class="stat-label">Net Payable Total</div>
                <div class="stat-value" style="color:#047857;">{{ pkr($totals['total_net_payable'], 2) }}</div>
                <div class="stat-sub">{{ pkr($totals['total_paid'], 2) }} already paid</div>
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

{{-- Payroll Table --}}
<div class="pos-card">
    <div style="overflow-x:auto;">
        <table class="pos-table w-100">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th style="text-align:right;">Base Salary</th>
                    <th style="text-align:center;">Allowed Holidays</th>
                    <th style="text-align:center;">Attendance Breakdown ({{ $parsedDate->daysInMonth }} Days)</th>
                    <th style="text-align:center;">Excess Absences</th>
                    <th style="text-align:right;">Daily Rate</th>
                    <th style="text-align:right;">Salary Deduction</th>
                    <th style="text-align:right;">Net Payable</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls as $p)
                <tr>
                    <td>
                        <div style="font-weight:700; color:#111827; font-size:14px;">{{ $p['user']->name }}</div>
                        <div style="font-size:11px; color:#6b7280;">
                            @if($p['user']->designation)
                                <span class="badge" style="background:#f3f4f6; color:#4b5563; font-weight:600; padding:2px 6px;">{{ $p['user']->designation }}</span>
                            @endif
                            <span style="font-family:monospace;">{{ $p['user']->email }}</span>
                        </div>
                    </td>
                    <td style="text-align:right; font-weight:700; color:#374151;">
                        {{ pkr($p['base_salary'], 2) }}
                    </td>
                    <td style="text-align:center;">
                        <span class="badge" style="background:#f3f4f6; color:#111827; font-weight:700; font-size:12px; border:1px solid #e5e7eb; padding:3px 8px;">
                            {{ $p['allowed_leaves'] }} days / mo
                        </span>
                    </td>
                    <td style="text-align:center;">
                        <div class="d-flex justify-content-center gap-1 flex-wrap">
                            <span class="pill-count pill-present" title="Present Days">P: {{ $p['present_count'] }}</span>
                            <span class="pill-count pill-half" title="Half Days">HD: {{ $p['half_day_count'] }}</span>
                            <span class="pill-count pill-absent" title="Absent Days">A: {{ $p['absent_count'] }}</span>
                            <span class="pill-count pill-leave" title="Approved Leaves">L: {{ $p['leave_count'] }}</span>
                            <span class="pill-count pill-holiday" title="Public Holidays">H: {{ $p['holiday_count'] }}</span>
                        </div>
                        <div style="font-size:10px; color:#9ca3af; margin-top:3px;">
                            Total Absence Units: {{ $p['unpaid_absences'] }}
                        </div>
                    </td>
                    <td style="text-align:center;">
                        @if($p['excess_absences'] > 0)
                            <span class="badge" style="background:#fee2e2; color:#b91c1c; font-weight:800; font-size:12px; padding:3px 8px;">
                                {{ $p['excess_absences'] }} days
                            </span>
                        @else
                            <span class="badge" style="background:#d1fae5; color:#065f46; font-weight:700; font-size:11px; padding:3px 8px;">
                                Within Limit (0)
                            </span>
                        @endif
                    </td>
                    <td style="text-align:right; font-size:13px; color:#6b7280; font-family:monospace;">
                        {{ pkr($p['daily_rate'], 2) }}
                    </td>
                    <td style="text-align:right; font-weight:700; color: {{ $p['deduction_amount'] > 0 ? '#b91c1c' : '#6b7280' }};">
                        @if($p['deduction_amount'] > 0)
                            - {{ pkr($p['deduction_amount'], 2) }}
                        @else
                            PKR 0.00
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <div style="font-size:16px; font-weight:800; color: #047857;">
                            {{ pkr($p['net_payable'], 2) }}
                        </div>
                    </td>
                    <td style="text-align:center;">
                        @if($p['is_paid'])
                            <span class="badge-status-paid">
                                <i class="bi bi-check-circle-fill"></i> Paid
                            </span>
                            <div style="font-size:10px; color:#6b7280; margin-top:2px;">
                                {{ $p['payment']->payment_date->format('d M Y') }}
                            </div>
                        @else
                            <span class="badge-status-pending">
                                <i class="bi bi-clock-fill"></i> Pending
                            </span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <div class="d-inline-flex gap-1">
                            @if(!$p['is_paid'])
                                <button class="btn-pos" style="padding:4px 8px; font-size:12px;"
                                        onclick="openPaySalaryModal({{ $p['user']->id }}, '{{ addslashes($p['user']->name) }}', {{ $p['base_salary'] }}, {{ $p['deduction_amount'] }}, {{ $p['net_payable'] }}, {{ $p['excess_absences'] }})">
                                    <i class="bi bi-cash"></i> Pay Salary
                                </button>
                            @else
                                <a href="{{ route('admin.attendance.payroll.slip', $p['payment']->id) }}" target="_blank" class="btn-pos-outline" style="padding:4px 8px; font-size:12px; text-decoration:none;" title="Print Voucher / Slip">
                                    <i class="bi bi-printer"></i> Slip
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align:center; padding:48px; color:#9ca3af;">
                        <i class="bi bi-people" style="font-size:36px; display:block; margin-bottom:8px; opacity:.5;"></i>
                        <p style="font-weight:600; color:#374151; margin-bottom:4px;">No employees registered</p>
                        <p style="font-size:13px; margin:0;">Create employee accounts under Customers & Users Management to start managing attendance and payroll.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Disburse / Record Salary Payment Modal --}}
<div class="modal fade" id="paySalaryModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="paySalaryForm" class="modal-content">
            @csrf
            <input type="hidden" name="month_year" value="{{ $monthYearStr }}">
            <input type="hidden" name="base_salary" id="formBaseSalary">
            <input type="hidden" name="deduction_amount" id="formDeductionAmount">

            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cash-coin me-2 text-success"></i>Disburse Employee Salary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:14px 16px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size:11px; color:#166534; font-weight:700; text-transform:uppercase;">EMPLOYEE</div>
                            <div style="font-size:16px; font-weight:800; color:#111827;" id="modalEmpName">—</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:11px; color:#166534; font-weight:700; text-transform:uppercase;">FOR PERIOD</div>
                            <div style="font-size:14px; font-weight:700; color:#166534;">{{ $parsedDate->format('F Y') }}</div>
                        </div>
                    </div>
                    <hr style="margin:10px 0; border-color:#bbf7d0;">
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Base Monthly Salary:</span>
                        <span class="fw-bold text-dark" id="modalBaseSalary">PKR 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted small mt-1">
                        <span>Absence Deductions (<span id="modalExcessDays">0</span> excess days):</span>
                        <span class="fw-bold text-danger" id="modalDeduction">- PKR 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2" style="border-top:1px dashed #86efac;">
                        <span class="fw-bold" style="color:#166534;">Net Calculated Salary:</span>
                        <span class="fw-bold" style="font-size:18px; color:#15803d;" id="modalNetCalculated">PKR 0.00</span>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:13px; font-weight:600;">Bonus / Incentive (PKR)</label>
                        <input type="number" step="0.01" min="0" name="bonus_amount" id="modalBonus" class="pos-input" value="0.00" oninput="updateNetSalaryPreview()">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:13px; font-weight:600;">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="pos-input" required value="{{ date('Y-m-d') }}">
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
                    <label class="form-label" style="font-size:13px; font-weight:600;">Payment Reference / Notes</label>
                    <input type="text" name="notes" class="pos-input" placeholder="e.g. Salary paid via cash voucher #12">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-pos" style="background:#059669;"><i class="bi bi-check-circle"></i> Confirm & Save Salary Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentNetPayable = 0;

    function openPaySalaryModal(userId, name, baseSalary, deduction, netPayable, excessDays) {
        document.getElementById('paySalaryForm').action = `{{ url('admin/attendance/payroll') }}/${userId}/pay`;
        document.getElementById('modalEmpName').innerText = name;
        document.getElementById('modalBaseSalary').innerText = 'PKR ' + baseSalary.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
        document.getElementById('modalDeduction').innerText = '- PKR ' + deduction.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
        document.getElementById('modalNetCalculated').innerText = 'PKR ' + netPayable.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
        document.getElementById('formBaseSalary').value = baseSalary;
        document.getElementById('formDeductionAmount').value = deduction;
        document.getElementById('modalExcessDays').innerText = excessDays;
        document.getElementById('modalBonus').value = '0.00';
        currentNetPayable = netPayable;
        
        new bootstrap.Modal(document.getElementById('paySalaryModal')).show();
    }

    function updateNetSalaryPreview() {
        const bonus = parseFloat(document.getElementById('modalBonus').value) || 0;
        const total = currentNetPayable + bonus;
        document.getElementById('modalNetCalculated').innerText = 'PKR ' + total.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
</script>

@endsection

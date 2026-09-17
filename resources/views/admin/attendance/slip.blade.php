<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip - {{ $employee->name }} - {{ $monthName }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: #f3f4f6; padding: 30px 10px; margin: 0; color: #1f2937; }
        .slip-container {
            max-width: 700px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            padding: 36px 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #1a6b7c;
            padding-bottom: 18px;
            margin-bottom: 24px;
        }
        .company-title {
            font-size: 24px;
            font-weight: 800;
            color: #1a6b7c;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .company-sub {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }
        .voucher-tag {
            text-align: right;
        }
        .voucher-title {
            font-size: 16px;
            font-weight: 800;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .voucher-period {
            font-size: 13px;
            font-weight: 600;
            color: #1a6b7c;
            margin-top: 2px;
        }
        .voucher-ref {
            font-size: 11px;
            color: #9ca3af;
            font-family: monospace;
            margin-top: 2px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            background: #f9fafb;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            border: 1px solid #f3f4f6;
        }
        .meta-item { font-size: 13px; }
        .meta-label { color: #6b7280; font-weight: 500; }
        .meta-value { color: #111827; font-weight: 700; margin-top: 2px; }

        .section-heading {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #4b5563;
            margin-bottom: 10px;
        }
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .breakdown-table th {
            background: #f9fafb;
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }
        .breakdown-table td {
            padding: 12px 14px;
            font-size: 13px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
        }
        .total-row td {
            font-size: 15px;
            font-weight: 800;
            color: #111827;
            background: #f9fafb;
            border-top: 2px solid #e5e7eb;
            border-bottom: 2px solid #e5e7eb;
        }
        .attendance-summary {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            text-align: center;
            background: #f9fafb;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 24px;
            border: 1px solid #f3f4f6;
        }
        .att-box-title { font-size: 11px; color: #6b7280; font-weight: 600; }
        .att-box-val { font-size: 15px; font-weight: 800; color: #111827; margin-top: 2px; }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px dashed #d1d5db;
        }
        .signature-line {
            text-align: center;
            width: 200px;
        }
        .signature-line .line {
            border-top: 1px solid #9ca3af;
            margin-bottom: 6px;
        }
        .signature-line .title {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
        }

        .actions {
            max-width: 700px;
            margin: 20px auto 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            background: #1a6b7c;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-outline {
            background: #fff;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .slip-container { box-shadow: none; border: none; padding: 20px; }
            .actions { display: none; }
        }
    </style>
</head>
<body>

<div class="actions">
    <a href="{{ route('admin.attendance.payroll', ['month' => date('Y-m', strtotime($payment->payment_date))]) }}" class="btn btn-outline">
        &larr; Back to Payroll
    </a>
    <button onclick="window.print()" class="btn">
        Print Salary Voucher
    </button>
</div>

<div class="slip-container">
    {{-- Header --}}
    <div class="header">
        <div>
            <h1 class="company-title">Hassan & Sons</h1>
            <div class="company-sub">Hardware & Sanitary Store &bull; Official Salary Slip</div>
        </div>
        <div class="voucher-tag">
            <div class="voucher-title">Salary Payment Voucher</div>
            <div class="voucher-period">{{ $monthName }}</div>
            <div class="voucher-ref">Slip #{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</div>
        </div>
    </div>

    {{-- Employee & Payment Meta --}}
    <div class="meta-grid">
        <div class="meta-item">
            <div class="meta-label">Employee Name</div>
            <div class="meta-value">{{ $employee->name }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Designation / Role</div>
            <div class="meta-value">{{ $employee->designation ?? 'POS Operator / Employee' }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Payment Date</div>
            <div class="meta-value">{{ $payment->payment_date->format('d M Y') }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Payment Method</div>
            <div class="meta-value" style="text-transform: capitalize;">{{ str_replace('_', ' ', $payment->payment_method) }}</div>
        </div>
    </div>

    {{-- Attendance Summary --}}
    <div class="section-heading">Monthly Attendance Summary ({{ $payroll['total_days'] }} Days)</div>
    <div class="attendance-summary">
        <div>
            <div class="att-box-title">Present</div>
            <div class="att-box-val" style="color:#059669;">{{ $payroll['present_count'] }}</div>
        </div>
        <div>
            <div class="att-box-title">Half Days</div>
            <div class="att-box-val" style="color:#d97706;">{{ $payroll['half_day_count'] }}</div>
        </div>
        <div>
            <div class="att-box-title">Absents</div>
            <div class="att-box-val" style="color:#dc2626;">{{ $payroll['absent_count'] }}</div>
        </div>
        <div>
            <div class="att-box-title">Approved Leaves</div>
            <div class="att-box-val" style="color:#2563eb;">{{ $payroll['leave_count'] }}</div>
        </div>
        <div>
            <div class="att-box-title">Public Holidays</div>
            <div class="att-box-val" style="color:#7c3aed;">{{ $payroll['holiday_count'] }}</div>
        </div>
    </div>

    {{-- Earnings & Deductions Breakdown --}}
    <div class="section-heading">Salary Calculation Breakdown</div>
    <table class="breakdown-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align:right;">Details</th>
                <th style="text-align:right;">Amount (PKR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Monthly Base Salary</strong></td>
                <td style="text-align:right; color:#6b7280;">Standard monthly rate</td>
                <td style="text-align:right; font-weight:600;">{{ pkr($payment->base_salary, 2) }}</td>
            </tr>
            <tr>
                <td>
                    <strong>Absence / Holiday Deductions</strong>
                    <div style="font-size:11px; color:#6b7280;">
                        Allowed threshold: {{ $payroll['allowed_leaves'] }} days &bull; Excess: {{ $payment->excess_absences }} days @ {{ pkr($payment->daily_rate ?? ($payroll['daily_rate'] ?? 0), 2) }}/day
                    </div>
                </td>
                <td style="text-align:right; color:#dc2626;">{{ $payment->excess_absences }} days</td>
                <td style="text-align:right; font-weight:600; color:#dc2626;">
                    - {{ pkr($payment->deduction_amount, 2) }}
                </td>
            </tr>
            @if($payment->bonus_amount > 0)
            <tr>
                <td><strong>Incentive / Bonus</strong></td>
                <td style="text-align:right; color:#059669;">Special reward</td>
                <td style="text-align:right; font-weight:600; color:#059669;">+ {{ pkr($payment->bonus_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td colspan="2"><strong>Net Paid Salary</strong></td>
                <td style="text-align:right; color:#1a6b7c; font-size:17px;">
                    {{ pkr($payment->net_payable, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    @if($payment->notes)
    <div style="font-size:12px; color:#6b7280; margin-bottom:20px; background:#f9fafb; padding:10px 14px; border-radius:6px;">
        <strong>Note / Reference:</strong> {{ $payment->notes }}
    </div>
    @endif

    {{-- Signatures --}}
    <div class="signatures">
        <div class="signature-line">
            <div class="line"></div>
            <div class="title">Employee Signature</div>
        </div>
        <div class="signature-line">
            <div class="line"></div>
            <div class="title">Authorized Signature (Admin)</div>
        </div>
    </div>
</div>

</body>
</html>

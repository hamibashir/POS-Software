<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Voucher #{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }} — {{ config('store.name', 'Hassan Corporation') }}</title>

    {{-- Clean typography for crisp thermal printing --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /* ═══════════════════════════════════════════════════════
           BASE & SCREEN LAYOUT
        ═══════════════════════════════════════════════════════ */
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #e2e8f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 12px 60px;
            color: #000000;
        }

        /* Top Action Bar (Screen Only) */
        .action-bar {
            display: flex;
            gap: 10px;
            width: 100%;
            max-width: 360px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all .15s ease;
            white-space: nowrap;
        }
        .btn:active { transform: scale(.98); }

        .btn-back {
            background: #ffffff;
            color: #1e293b;
            border: 1.5px solid #cbd5e1;
        }
        .btn-back:hover { background: #f8fafc; border-color: #94a3b8; }

        .btn-print {
            flex: 1;
            background: #000000;
            color: #ffffff;
        }
        .btn-print:hover { background: #1e293b; }

        /* Controls / Preview Mode (Screen Only) */
        .preview-controls {
            width: 100%;
            max-width: 360px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 12px;
            gap: 8px;
        }

        .auto-print-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #475569;
            cursor: pointer;
            user-select: none;
        }
        .auto-print-wrap input {
            cursor: pointer;
            accent-color: #000000;
        }

        .width-switch {
            display: flex;
            background: #f1f5f9;
            border-radius: 6px;
            padding: 2px;
            gap: 2px;
        }
        .width-btn {
            border: none;
            background: transparent;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            border-radius: 4px;
            cursor: pointer;
        }
        .width-btn.active {
            background: #000000;
            color: #ffffff;
        }

        /* ═══════════════════════════════════════════════════════
           THERMAL RECEIPT MONOCHROME BLACK & WHITE DESIGN
        ═══════════════════════════════════════════════════════ */
        .receipt {
            background: #ffffff;
            width: 100%;
            max-width: 340px;
            padding: 18px 20px 22px 20px;
            border-radius: 4px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
            color: #000000;
            font-size: 12px;
            line-height: 1.35;
            transition: max-width 0.2s ease;
        }

        .receipt.roll-58mm {
            max-width: 250px;
            padding: 12px 14px 16px 14px;
            font-size: 11px;
        }

        /* Store Header */
        .store-header {
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000000;
            margin-bottom: 10px;
        }
        .store-title {
            font-size: 17px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000000;
            margin-bottom: 3px;
        }
        .store-info {
            font-size: 11px;
            font-weight: 500;
            color: #000000;
            line-height: 1.3;
        }

        /* Document Title */
        .doc-title-wrap {
            text-align: center;
            padding: 4px 0 8px;
            border-bottom: 1px dashed #000000;
            margin-bottom: 10px;
        }
        .doc-title {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-invoice-no {
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            font-weight: 700;
            margin-top: 2px;
        }

        /* Meta List */
        .meta-list {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .meta-list td {
            padding: 2px 0;
            font-size: 11.5px;
            vertical-align: top;
            color: #000000;
        }
        .meta-list td:first-child {
            width: 40%;
            font-weight: 600;
            padding-left: 4px;
        }
        .meta-list td:last-child {
            width: 60%;
            text-align: right;
            font-weight: 600;
            padding-right: 4px;
        }

        /* Payment Received Box */
        .payment-box {
            border: 2px solid #000000;
            padding: 10px 12px;
            margin: 10px 0;
            text-align: center;
        }
        .payment-box-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .payment-box-amount {
            font-size: 20px;
            font-weight: 800;
        }

        /* Balance Info Box */
        .balance-info-box {
            border: 1px solid #000000;
            padding: 6px 8px;
            margin: 8px 0;
            font-size: 11.5px;
        }

        /* Signatures */
        .signatures-wrap {
            display: flex;
            justify-content: space-between;
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px dashed #000000;
        }
        .sig-box {
            width: 46%;
            text-align: center;
        }
        .sig-line {
            border-bottom: 1px solid #000000;
            height: 24px;
            margin-bottom: 4px;
        }
        .sig-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        /* Footer */
        .receipt-footer {
            text-align: center;
            border-top: 1px dashed #000000;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 10.5px;
            line-height: 1.35;
        }

        /* ═══════════════════════════════════════════════════════
           DYNAMIC PRINT MEDIA RULES (80mm & 58mm Thermal Rolls)
        ═══════════════════════════════════════════════════════ */
        @media print {
            @page {
                size: auto;
                margin: 0mm;
            }

            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
                font-size: 11.5px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .action-bar,
            .preview-controls {
                display: none !important;
            }

            .receipt {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 auto !important;
                padding: 2mm 5mm 4mm 5mm !important;
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                background: #ffffff !important;
                color: #000000 !important;
            }

            .meta-list td:first-child {
                padding-left: 3px !important;
            }

            .meta-list td:last-child {
                padding-right: 3px !important;
            }

            .store-title {
                font-size: 14pt !important;
            }

            .payment-box-amount {
                font-size: 14pt !important;
            }

            * {
                color: #000000 !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }
        }
    </style>
</head>
<body>

{{-- ── SCREEN ACTION BAR ─────────────────────────────────────── --}}
<div class="action-bar">
    <a href="{{ route('cashier.pos') }}" class="btn btn-back">
        <i class="bi bi-arrow-left"></i> POS Counter
    </a>
    <button class="btn btn-print" onclick="window.print()">
        <i class="bi bi-printer-fill"></i> Print Payment Voucher
    </button>
</div>

{{-- ── SCREEN PREVIEW TOGGLE & CONTROLS ───────────────────────── --}}
<div class="preview-controls">
    <label class="auto-print-wrap" for="autoPrintToggle">
        <input type="checkbox" id="autoPrintToggle"> Auto-print on open
    </label>
    <div class="width-switch">
        <button type="button" class="width-btn active" onclick="setRollWidth('80mm', this)">80mm</button>
        <button type="button" class="width-btn" onclick="setRollWidth('58mm', this)">58mm</button>
    </div>
</div>

{{-- ── MONOCHROME THERMAL RECEIPT ─────────────────────────────── --}}
<div class="receipt" id="receiptCard">

    {{-- Store Header --}}
    <div class="store-header">
        <div class="store-title">{{ config('store.name', 'Hassan Corporation') }}</div>
        <div class="store-info">
            {{ config('store.address', 'Rafi Commercial, Bahria Town Phase 8, Rawalpindi.') }}<br>
            Phone: {{ config('store.phone', '051-8891930') }}
        </div>
    </div>

    {{-- Document Title & Voucher Number --}}
    <div class="doc-title-wrap">
        <div class="doc-title">*** CUSTOMER PAYMENT RECEIPT ***</div>
        <div class="doc-invoice-no">#CPAY-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</div>
    </div>

    {{-- Meta Information --}}
    <table class="meta-list">
        <tbody>
            <tr>
                <td>Date & Time:</td>
                <td>{{ $payment->created_at ? $payment->created_at->format('d-m-Y h:i A') : $payment->payment_date->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <td>Received By:</td>
                <td>{{ $payment->user?->name ?? 'POS Cashier' }}</td>
            </tr>
            <tr>
                <td>Customer Name:</td>
                <td><strong>{{ $payment->employee->name }}</strong></td>
            </tr>
            @if($payment->employee->phone)
            <tr>
                <td>Contact Phone:</td>
                <td>{{ $payment->employee->phone }}</td>
            </tr>
            @endif
            <tr>
                <td>Payment Mode:</td>
                <td>
                    <strong>
                        {{ match($payment->payment_method) {
                            'cash' => 'CASH (COUNTER)',
                            'bank' => 'BANK TRANSFER',
                            'salary_deduction' => 'ACCOUNT DEDUCTION',
                            default => strtoupper($payment->payment_method)
                        } }}
                    </strong>
                </td>
            </tr>
            @if($payment->notes)
            <tr>
                <td>Remarks / Notes:</td>
                <td>{{ $payment->notes }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Amount Received Box (No decimal points) --}}
    <div class="payment-box">
        <div class="payment-box-title">AMOUNT RECEIVED & CLEARED</div>
        <div class="payment-box-amount">{{ pkr($payment->amount, 0) }}</div>
    </div>

    {{-- Remaining Balance (No decimal points) --}}
    <div class="balance-info-box">
        <div style="display:flex; justify-content:space-between; align-items:center; padding:0 4px;">
            <span>Remaining Pending Due:</span>
            <strong>
                @if($payment->employee->pending_payment <= 0)
                    Rs. 0 (All Cleared)
                @else
                    {{ pkr($payment->employee->pending_payment, 0) }}
                @endif
            </strong>
        </div>
    </div>

    {{-- Signatures --}}
    <div class="signatures-wrap">
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-label">Customer Signature</div>
        </div>
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-label">Cashier Signature</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="receipt-footer">
        <div>Customer credit payment clearance voucher.</div>
        <div>Please retain this receipt for your records.</div>
    </div>

</div>

<script>
    function setRollWidth(width, btn) {
        const receipt = document.getElementById('receiptCard');
        document.querySelectorAll('.width-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        if (width === '58mm') {
            receipt.classList.add('roll-58mm');
        } else {
            receipt.classList.remove('roll-58mm');
        }
    }

    const params    = new URLSearchParams(window.location.search);
    const autoPrint = params.get('print') === '1';
    const toggle    = document.getElementById('autoPrintToggle');

    toggle.checked = autoPrint;

    if (autoPrint) {
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 500);
        });
    }

    toggle.addEventListener('change', function () {
        if (this.checked) {
            const url = new URL(window.location.href);
            url.searchParams.set('print', '1');
            history.replaceState({}, '', url.toString());
            window.print();
        } else {
            const url = new URL(window.location.href);
            url.searchParams.delete('print');
            history.replaceState({}, '', url.toString());
        }
    });
</script>
</body>
</html>

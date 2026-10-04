<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Voucher {{ $saleReturn->return_number }} — {{ config('store.name', 'Hassan Corporation') }}</title>

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
           THERMAL RETURN RECEIPT MONOCHROME DESIGN
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
            width: 38%;
            font-weight: 600;
            padding-left: 4px;
        }
        .meta-list td:last-child {
            width: 62%;
            text-align: right;
            font-weight: 600;
            padding-right: 4px;
        }

        /* Returned Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 8px 0;
            border-top: 1px dashed #000000;
            border-bottom: 1px dashed #000000;
        }
        .items-table th {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 6px 2px;
            border-bottom: 1px solid #000000;
            color: #000000;
        }
        .items-table td {
            padding: 5px 2px;
            font-size: 11.5px;
            vertical-align: top;
            color: #000000;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .items-table tbody tr:not(:last-child) td {
            border-bottom: 1px dotted #cccccc;
        }
        .col-item   { width: 44%; text-align: left; padding-left: 4px; }
        .col-qty    { width: 16%; text-align: center; }
        .col-rate   { width: 18%; text-align: right; padding-right: 4px; }
        .col-refund { width: 22%; text-align: right; font-weight: 700; padding-right: 4px; }

        .item-name-text {
            font-weight: 700;
            line-height: 1.25;
            color: #000000;
        }

        /* Refund Box */
        .refund-total-box {
            border: 2px solid #000000;
            padding: 8px 10px;
            margin: 10px 0;
            text-align: center;
        }
        .refund-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .refund-amount {
            font-size: 17px;
            font-weight: 800;
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

            .items-table td.col-item,
            .items-table th.col-item,
            .meta-list td:first-child {
                padding-left: 3px !important;
            }

            .items-table td.col-refund,
            .items-table th.col-refund,
            .meta-list td:last-child {
                padding-right: 3px !important;
            }

            .store-title {
                font-size: 14pt !important;
            }

            .refund-amount {
                font-size: 13pt !important;
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
        <i class="bi bi-printer-fill"></i> Print Return Voucher
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

{{-- ── MONOCHROME THERMAL RETURN VOUCHER ──────────────────────── --}}
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
        <div class="doc-title">*** RETURN & REFUND VOUCHER ***</div>
        <div class="doc-invoice-no">{{ $saleReturn->return_number }}</div>
    </div>

    {{-- Meta Information --}}
    <table class="meta-list">
        <tbody>
            <tr>
                <td>Date & Time:</td>
                <td>{{ $saleReturn->created_at ? $saleReturn->created_at->format('d-m-Y h:i A') : now()->format('d-m-Y h:i A') }}</td>
            </tr>
            <tr>
                <td>Processed By:</td>
                <td>{{ $saleReturn->user?->name ?? 'POS Cashier' }}</td>
            </tr>
            @if($saleReturn->sale)
            <tr>
                <td>Original Invoice:</td>
                <td><strong>{{ $saleReturn->sale->invoice_number }}</strong></td>
            </tr>
            @endif
            <tr>
                <td>Customer Name:</td>
                <td>{{ $saleReturn->customer_name }} {{ $saleReturn->customer_phone ? '(' . $saleReturn->customer_phone . ')' : '' }}</td>
            </tr>
            <tr>
                <td>Refund Mode:</td>
                <td>
                    <strong>
                        {{ match($saleReturn->refund_method) {
                            'cash'   => 'CASH REFUND',
                            'card'   => 'CARD REVERSAL',
                            'credit' => 'CUSTOMER CREDIT DEDUCTION',
                            default  => strtoupper($saleReturn->refund_method)
                        } }}
                    </strong>
                </td>
            </tr>
            @if($saleReturn->notes)
            <tr>
                <td>Return Reason:</td>
                <td>{{ $saleReturn->notes }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Returned Items Table (Clean, NO decimal points, NO product ID, NO unit/type) --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="col-item">Returned Item</th>
                <th class="col-qty">Qty</th>
                <th class="col-rate">Rate</th>
                <th class="col-refund">Refund</th>
            </tr>
        </thead>
        <tbody>
            @foreach($saleReturn->items as $item)
            <tr>
                <td class="col-item">
                    <div class="item-name-text">{{ $item->product_name }}</div>
                </td>
                <td class="col-qty">-{{ format_qty($item->quantity) }}</td>
                <td class="col-rate">{{ pkr($item->unit_price, 0) }}</td>
                <td class="col-refund">-{{ pkr($item->total_price, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Total Refund Box --}}
    <div class="refund-total-box">
        <div class="refund-title">TOTAL AMOUNT REFUNDED</div>
        <div class="refund-amount">-{{ pkr($saleReturn->total_return_amount, 0) }}</div>
    </div>

    {{-- Signatures --}}
    <div class="signatures-wrap">
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-label">Customer Signature</div>
        </div>
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-label">Authorized Staff</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="receipt-footer">
        <div>Items returned and restored to inventory.</div>
        <div>Official store refund confirmation voucher.</div>
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $sale->invoice_number }} — {{ config('store.name', 'Hassan Corporation') }}</title>

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
            max-width: 340px; /* Default 80mm roll preview */
            padding: 18px 20px 22px 20px; /* balanced padding shifting left side inward right and right side inward left */
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

        /* Meta Grid */
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
            padding-left: 4px; /* shifted slightly to the right */
        }
        .meta-list td:last-child {
            width: 62%;
            text-align: right;
            font-weight: 600;
            padding-right: 4px; /* shifted slightly to the left */
        }

        .credit-customer-box {
            border: 1px solid #000000;
            padding: 6px 8px;
            margin: 6px 2px 10px 2px;
            font-size: 11px;
        }
        .credit-customer-title {
            font-weight: 800;
            text-transform: uppercase;
            font-size: 10.5px;
            border-bottom: 1px dashed #000000;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }

        /* Items Table */
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
        .col-item  { width: 46%; text-align: left; padding-left: 4px; } /* shifted right */
        .col-qty   { width: 14%; text-align: center; }
        .col-rate  { width: 19%; text-align: right; padding-right: 4px; }
        .col-total { width: 21%; text-align: right; font-weight: 700; padding-right: 4px; } /* shifted left */

        .item-name-text {
            font-weight: 700;
            line-height: 1.25;
            color: #000000;
        }
        .item-disc-text {
            font-size: 10px;
            font-weight: 600;
            margin-top: 1px;
        }

        /* Totals */
        .totals-section {
            padding-top: 4px;
            margin-bottom: 10px;
        }
        .calc-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2px 0;
            font-size: 12px;
            color: #000000;
        }
        .calc-row span:first-child {
            padding-left: 4px; /* shifted right */
        }
        .calc-row span:last-child {
            padding-right: 4px; /* shifted left */
        }
        .calc-row.grand-total {
            border-top: 2px solid #000000;
            border-bottom: 2px solid #000000;
            margin: 6px 0;
            padding: 6px 0;
            font-size: 16px;
            font-weight: 800;
        }
        .calc-row.paid-line {
            font-weight: 700;
        }
        .calc-row.change-line {
            font-size: 13px;
            font-weight: 800;
            border-top: 1px dashed #000000;
            padding-top: 4px;
            margin-top: 4px;
        }

        /* Footer */
        .receipt-footer {
            text-align: center;
            border-top: 1px dashed #000000;
            padding-top: 10px;
            margin-top: 8px;
            font-size: 11px;
            line-height: 1.4;
        }
        .footer-thanks {
            font-weight: 800;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .barcode-section {
            margin-top: 10px;
            text-align: center;
        }
        .barcode-lines {
            display: inline-block;
            height: 32px;
            width: 170px;
            background: repeating-linear-gradient(
                90deg,
                #000 0px, #000 1px,
                transparent 1px, transparent 2px,
                #000 2px, #000 4px,
                transparent 4px, transparent 6px,
                #000 6px, #000 7px,
                transparent 7px, transparent 9px,
                #000 9px, #000 12px,
                transparent 12px, transparent 13px,
                #000 13px, #000 16px,
                transparent 16px, transparent 18px,
                #000 18px, #000 19px,
                transparent 19px, transparent 22px,
                #000 22px, #000 24px,
                transparent 24px, transparent 25px,
                #000 25px, #000 28px,
                transparent 28px, transparent 31px,
                #000 31px, #000 32px,
                transparent 32px, transparent 35px,
                #000 35px, #000 37px,
                transparent 37px, transparent 38px,
                #000 38px, #000 41px,
                transparent 41px, transparent 44px,
                #000 44px, #000 46px,
                transparent 46px, transparent 48px,
                #000 48px, #000 51px,
                transparent 51px, transparent 52px,
                #000 52px, #000 55px,
                transparent 55px, transparent 58px,
                #000 58px, #000 60px,
                transparent 60px, transparent 63px,
                #000 63px, #000 64px,
                transparent 64px, transparent 67px,
                #000 67px, #000 70px,
                transparent 70px, transparent 71px,
                #000 71px, #000 74px,
                transparent 74px, transparent 77px,
                #000 77px, #000 79px,
                transparent 79px, transparent 81px,
                #000 81px, #000 84px,
                transparent 84px, transparent 86px,
                #000 86px, #000 88px,
                transparent 88px, transparent 91px,
                #000 91px, #000 93px,
                transparent 93px, transparent 96px,
                #000 96px, #000 98px,
                transparent 98px, transparent 101px,
                #000 101px, #000 104px,
                transparent 104px, transparent 106px,
                #000 106px, #000 108px,
                transparent 108px, transparent 111px,
                #000 111px, #000 114px,
                transparent 114px, transparent 116px,
                #000 116px, #000 118px,
                transparent 118px, transparent 121px,
                #000 121px, #000 124px,
                transparent 124px, transparent 126px,
                #000 126px, #000 129px,
                transparent 129px, transparent 132px,
                #000 132px, #000 135px,
                transparent 135px, transparent 138px,
                #000 138px, #000 141px,
                transparent 141px, transparent 144px,
                #000 144px, #000 147px,
                transparent 147px, transparent 150px,
                #000 150px, #000 153px,
                transparent 153px, transparent 156px,
                #000 156px, #000 160px,
                transparent 160px, transparent 163px,
                #000 163px, #000 170px
            );
        }
        .barcode-digits {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-top: 2px;
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
                padding: 2mm 5mm 4mm 5mm !important; /* 5mm on both left and right ensures comfortable framing on all rolls */
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                background: #ffffff !important;
                color: #000000 !important;
            }

            .items-table td.col-item,
            .items-table th.col-item,
            .meta-list td:first-child,
            .calc-row span:first-child {
                padding-left: 3px !important;
            }

            .items-table td.col-total,
            .items-table th.col-total,
            .meta-list td:last-child,
            .calc-row span:last-child {
                padding-right: 3px !important;
            }

            .store-title {
                font-size: 14pt !important;
            }

            .calc-row.grand-total {
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
        <i class="bi bi-printer-fill"></i> Print Thermal Receipt
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

    {{-- Document Title & Invoice Number --}}
    <div class="doc-title-wrap">
        <div class="doc-title">*** SALES RECEIPT ***</div>
        <div class="doc-invoice-no">{{ $sale->invoice_number }}</div>
    </div>

    {{-- Meta Information --}}
    <table class="meta-list">
        <tbody>
            <tr>
                <td>Date & Time:</td>
                <td>{{ $sale->created_at->format('d-m-Y') }} {{ $sale->created_at->format('h:i A') }}</td>
            </tr>
            <tr>
                <td>Cashier / Staff:</td>
                <td>{{ $sale->user?->name ?? 'POS Cashier' }}</td>
            </tr>
            <tr>
                <td>Payment Mode:</td>
                <td><strong>{{ strtoupper($sale->payment_method) }}</strong></td>
            </tr>
            @if($sale->employee)
            <tr>
                <td>Customer Account:</td>
                <td><strong>{{ $sale->employee->name }}</strong></td>
            </tr>
            @elseif($sale->customer_name && $sale->customer_name !== 'Walk-in Customer')
            <tr>
                <td>Customer:</td>
                <td>{{ $sale->customer_name }} {{ $sale->customer_phone ? '(' . $sale->customer_phone . ')' : '' }}</td>
            </tr>
            @else
            <tr>
                <td>Customer:</td>
                <td>Walk-in Customer</td>
            </tr>
            @endif
            @if($sale->notes)
            <tr>
                <td>Notes / Ref:</td>
                <td>{{ $sale->notes }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Credit Customer Balance Info if Credit Sale --}}
    @if($sale->payment_method === 'credit' && $sale->employee)
    <div class="credit-customer-box">
        <div class="credit-customer-title">Customer Ledger Status</div>
        <div style="display:flex; justify-content:space-between; padding:0 4px;">
            <span>Current Invoice Billed:</span>
            <strong>{{ pkr($sale->total_amount, 0) }}</strong>
        </div>
        <div style="display:flex; justify-content:space-between; padding:0 4px; margin-top:2px;">
            <span>Total Outstanding Due:</span>
            <strong>{{ pkr($sale->employee->pending_payment, 0) }}</strong>
        </div>
    </div>
    @endif

    {{-- Purchased Items Table (Clean, NO decimal points, NO product ID, NO unit/type) --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="col-item">Item Description</th>
                <th class="col-qty">Qty</th>
                <th class="col-rate">Rate</th>
                <th class="col-total">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td class="col-item">
                    <div class="item-name-text">{{ $item->product_name }}</div>
                    @if($item->discount_amount > 0)
                        <div class="item-disc-text">(Disc: -{{ pkr($item->discount_amount, 0) }})</div>
                    @endif
                </td>
                <td class="col-qty">{{ format_qty($item->quantity) }}</td>
                <td class="col-rate">{{ pkr($item->unit_price, 0) }}</td>
                <td class="col-total">{{ pkr($item->total_price, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals Summary (Whole integers, no points, balanced margins) --}}
    <div class="totals-section">
        <div class="calc-row">
            <span>Subtotal ({{ format_qty($sale->items->sum('quantity')) }} items):</span>
            <span>{{ pkr($sale->subtotal, 0) }}</span>
        </div>

        @if($sale->discount_amount > 0)
        <div class="calc-row">
            <span>Special Discount:</span>
            <span>-{{ pkr($sale->discount_amount, 0) }}</span>
        </div>
        @endif

        @if($sale->tax_amount > 0)
        <div class="calc-row">
            <span>Sales Tax:</span>
            <span>{{ pkr($sale->tax_amount, 0) }}</span>
        </div>
        @endif

        <div class="calc-row grand-total">
            <span>TOTAL AMOUNT:</span>
            <span>{{ pkr($sale->total_amount, 0) }}</span>
        </div>

        @if($sale->payment_method === 'credit')
        <div class="calc-row paid-line">
            <span>Billed on Credit (Unpaid):</span>
            <span>{{ pkr($sale->total_amount, 0) }}</span>
        </div>
        @else
        <div class="calc-row paid-line">
            <span>Amount Paid ({{ strtoupper($sale->payment_method) }}):</span>
            <span>{{ pkr($sale->paid_amount, 0) }}</span>
        </div>

        @if($sale->change_amount > 0)
        <div class="calc-row change-line">
            <span>Change Returned:</span>
            <span>{{ pkr($sale->change_amount, 0) }}</span>
        </div>
        @endif
        @endif
    </div>

    {{-- Receipt Footer --}}
    <div class="receipt-footer">
        <div class="footer-thanks">THANK YOU FOR YOUR BUSINESS!</div>
        <div>Goods once sold are exchangeable within 7 days with original receipt.</div>

        {{-- Barcode --}}
        <div class="barcode-section">
            <div class="barcode-lines"></div>
            <div class="barcode-digits">{{ $sale->invoice_number }}</div>
        </div>
    </div>

</div>

<script>
    // ── Preview Roll Width Switcher (Screen Only) ─────────────────
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

    // ── Auto-print handling (?print=1) ───────────────────────────
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

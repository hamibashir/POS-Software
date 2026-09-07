<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $sale->invoice_number }} — {{ config('store.name', 'Hassan Sanitary and Hardware Store') }}</title>

    {{-- Fonts: Inter (UI) + JetBrains Mono (invoice / barcode) --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /* ╔══════════════════════════════════════════════════════╗
           ║  CSS CUSTOM PROPERTIES                               ║
           ╚══════════════════════════════════════════════════════╝ */
        :root {
            --primary:    #1a6b7c;
            --primary-dk: #0d4a57;
            --primary-lt: #e8f4f7;
            --success:    #10b981;
            --success-lt: #d1fae5;
            --success-dk: #065f46;
            --paper:      #ffffff;
            --shadow:     0 4px 32px rgba(0,0,0,.12);
            --receipt-w:  340px;    /* screen preview width */
        }

        /* ╔══════════════════════════════════════════════════════╗
           ║  BASE                                                ║
           ╚══════════════════════════════════════════════════════╝ */
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 12px 60px;
            color: #111827;
        }

        /* ╔══════════════════════════════════════════════════════╗
           ║  ACTION BAR  (screen only)                           ║
           ╚══════════════════════════════════════════════════════╝ */
        .action-bar {
            display: flex;
            gap: 8px;
            width: 100%;
            max-width: var(--receipt-w);
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 9px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: background .15s, transform .1s;
            white-space: nowrap;
        }
        .btn:active { transform: scale(.97); }

        .btn-back {
            background: #fff;
            color: #374151;
            border: 1.5px solid #e5e7eb;
        }
        .btn-back:hover { background: #f9fafb; }

        .btn-print {
            flex: 1;
            background: var(--primary);
            color: #fff;
        }
        .btn-print:hover { background: var(--primary-dk); }

        /* auto-print toggle */
        .auto-print-wrap {
            width: 100%;
            max-width: var(--receipt-w);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .auto-print-wrap label {
            font-size: 12px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            user-select: none;
        }
        .auto-print-wrap input[type=checkbox] {
            width: 15px; height: 15px;
            accent-color: var(--primary);
            cursor: pointer;
        }
        .kbd-hint {
            margin-left: auto;
            font-size: 11px;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .kbd {
            background: #e5e7eb;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 1px 5px;
            font-size: 11px;
            font-family: monospace;
            color: #374151;
        }

        /* ╔══════════════════════════════════════════════════════╗
           ║  RECEIPT CARD                                        ║
           ╚══════════════════════════════════════════════════════╝ */
        .receipt {
            background: var(--paper);
            width: 100%;
            max-width: var(--receipt-w);
            border-radius: 14px;
            box-shadow: var(--shadow);
            overflow: hidden;
            position: relative;
        }

        /* ── Store header ────────────────────────────────────── */
        .receipt-header {
            background: linear-gradient(150deg, var(--primary) 0%, var(--primary-dk) 100%);
            color: #fff;
            padding: 26px 22px 20px;
            text-align: center;
            position: relative;
        }
        .receipt-header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 20px;
            background: var(--paper);
            clip-path: polygon(
                0% 100%, 4% 0%, 8% 100%, 12% 0%, 16% 100%, 20% 0%,
                24% 100%, 28% 0%, 32% 100%, 36% 0%, 40% 100%, 44% 0%,
                48% 100%, 52% 0%, 56% 100%, 60% 0%, 64% 100%, 68% 0%,
                72% 100%, 76% 0%, 80% 100%, 84% 0%, 88% 100%, 92% 0%,
                96% 100%, 100% 0%, 100% 100%
            );
        }

        .store-logo {
            font-size: 30px;
            margin-bottom: 6px;
            display: block;
        }
        .store-name {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: .4px;
        }
        .store-tagline {
            font-size: 11px;
            opacity: .7;
            margin-top: 3px;
        }

        /* ── Receipt body ────────────────────────────────────── */
        .receipt-body {
            padding: 28px 20px 16px;   /* extra top for zigzag overlap */
        }

        /* Invoice number section */
        .invoice-section {
            text-align: center;
            margin-bottom: 16px;
            padding-bottom: 14px;
            border-bottom: 1.5px dashed #e5e7eb;
        }
        .invoice-label {
            font-size: 10px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }
        .invoice-number {
            font-family: 'JetBrains Mono', monospace;
            font-size: 17px;
            font-weight: 700;
            color: var(--primary);
            margin-top: 4px;
            letter-spacing: .5px;
        }

        /* Meta info table */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            background: #f9fafb;
            border-radius: 8px;
            overflow: hidden;
        }
        .meta-table td {
            padding: 7px 11px;
            font-size: 12px;
            vertical-align: top;
        }
        .meta-table td:first-child {
            color: #9ca3af;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: .7px;
            width: 36%;
            white-space: nowrap;
        }
        .meta-table td:last-child {
            color: #111827;
            font-weight: 600;
        }
        .meta-table tr { border-bottom: 1px solid #f3f4f6; }
        .meta-table tr:last-child { border-bottom: none; }

        /* Payment badge */
        .pay-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: var(--primary);
            color: #fff;
            border-radius: 20px;
            padding: 2px 9px;
            font-size: 11px;
            font-weight: 700;
        }

        /* ── Items ────────────────────────────────────────────── */
        .items-title {
            font-size: 10px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table thead th {
            font-size: 10px;
            color: #9ca3af;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 1.5px solid #f3f4f6;
            padding: 5px 0;
        }
        .items-table thead th:not(:first-child) { text-align: right; }
        .items-table tbody td {
            padding: 8px 0;
            border-bottom: 1px dashed #f3f4f6;
            font-size: 12px;
            color: #374151;
            vertical-align: top;
        }
        .items-table tbody tr:last-child td { border-bottom: none; }
        .items-table tbody td:not(:first-child) { text-align: right; }
        .item-name  { font-weight: 600; color: #111827; line-height: 1.3; font-size: 12.5px; }
        .item-meta  { font-size: 10px; color: #9ca3af; font-family: 'JetBrains Mono', monospace; margin-top: 1px; }
        .item-total { font-weight: 700; color: #111827; }

        /* ── Totals ────────────────────────────────────────────── */
        .totals-wrap {
            margin-top: 12px;
            border-top: 1.5px dashed #e5e7eb;
            padding-top: 12px;
        }
        .t-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3px 0;
            font-size: 12.5px;
            color: #6b7280;
        }
        .t-row.discount { color: var(--success); }
        .t-row.grand {
            border-top: 2px solid #111827;
            margin-top: 8px;
            padding-top: 10px;
            font-size: 17px;
            font-weight: 800;
            color: #111827;
        }
        .t-row.paid-row { font-weight: 600; color: #374151; margin-top: 4px; }
        .change-box {
            margin-top: 8px;
            background: var(--success-lt);
            border-radius: 8px;
            padding: 9px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            font-weight: 700;
            color: var(--success-dk);
        }

        /* ── Footer ────────────────────────────────────────────── */
        .receipt-footer {
            padding: 14px 20px 20px;
            text-align: center;
            border-top: 1.5px dashed #e5e7eb;
            margin-top: 12px;
        }
        .thank-you   { font-size: 14px; font-weight: 700; color: #111827; }
        .footer-note { font-size: 11px; color: #9ca3af; margin-top: 4px; line-height: 1.5; }

        /* Pseudo barcode strip */
        .barcode-strip {
            margin-top: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }
        .barcode-bars {
            height: 36px;
            width: 180px;
            background: repeating-linear-gradient(
                90deg,
                #000 0px, #000 1px,
                transparent 1px, transparent 2px,
                #000 2px, #000 3px,
                transparent 3px, transparent 5px,
                #000 5px, #000 7px,
                transparent 7px, transparent 9px,
                #000 9px, #000 11px,
                transparent 11px, transparent 14px,
                #000 14px, #000 15px,
                transparent 15px, transparent 17px,
                #000 17px, #000 19px,
                transparent 19px, transparent 21px,
                #000 21px, #000 22px,
                transparent 22px, transparent 25px,
                #000 25px, #000 26px,
                transparent 26px, transparent 28px,
                #000 28px, #000 29px,
                transparent 29px, transparent 31px,
                #000 31px, #000 33px,
                transparent 33px, transparent 36px,
                #000 36px, #000 37px,
                transparent 37px, transparent 38px,
                #000 38px, #000 39px,
                transparent 39px, transparent 41px,
                #000 41px, #000 42px,
                transparent 42px, transparent 45px,
                #000 45px, #000 46px,
                transparent 46px, transparent 47px,
                #000 47px, #000 49px,
                transparent 49px, transparent 50px,
                #000 50px, #000 51px,
                transparent 51px, transparent 54px,
                #000 54px, #000 55px,
                transparent 55px, transparent 57px,
                #000 57px, #000 59px,
                transparent 59px, transparent 61px,
                #000 61px, #000 63px,
                transparent 63px, transparent 65px,
                #000 65px, #000 67px,
                transparent 67px, transparent 70px,
                #000 70px, #000 71px,
                transparent 71px, transparent 73px,
                #000 73px, #000 74px,
                transparent 74px, transparent 76px,
                #000 76px, #000 77px,
                transparent 77px, transparent 79px,
                #000 79px, #000 180px
            );
        }
        .barcode-text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            color: #6b7280;
            letter-spacing: 2px;
        }

        /* ═══════════════════════════════════════════════════════
           PRINT MEDIA — 80mm thermal paper
        ═══════════════════════════════════════════════════════ */
        @media print {
            @page {
                size: 80mm auto;   /* 80mm wide, auto height (thermal roll) */
                margin: 0mm;
            }

            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

            body {
                background: #fff !important;
                padding: 0 !important;
                display: block !important;
                font-size: 11px !important;
            }

            /* Hide everything except the receipt */
            .action-bar,
            .auto-print-wrap  { display: none !important; }

            .receipt {
                box-shadow: none !important;
                border-radius: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }

            /* Solid black header for thermal (saves ink, still readable) */
            .receipt-header {
                background: #111 !important;
                padding: 10px 8px 24px !important;
            }
            .receipt-header::after {
                background: #fff !important;
            }

            .store-logo   { font-size: 20px !important; }
            .store-name   { font-size: 14px !important; }
            .store-tagline { font-size: 9px !important; }

            .receipt-body { padding: 20px 8px 8px !important; }

            .invoice-number { font-size: 13px !important; }

            /* Meta table */
            .meta-table td { padding: 4px 6px !important; font-size: 9px !important; }
            .meta-table td:first-child { font-size: 8px !important; }

            /* Pay badge for print */
            .pay-badge { background: #000 !important; }

            /* Items */
            .items-table thead th { font-size: 9px !important; padding: 4px 0 !important; }
            .items-table tbody td { font-size: 10px !important; padding: 5px 0 !important; }
            .item-name  { font-size: 10px !important; }
            .item-meta  { font-size: 8px !important; }

            /* Totals */
            .t-row       { font-size: 10px !important; }
            .t-row.grand { font-size: 13px !important; }
            .t-row.paid-row { font-size: 11px !important; }
            .change-box  {
                background: #eee !important;
                color: #000 !important;
                font-size: 12px !important;
                padding: 6px 8px !important;
            }

            /* Footer */
            .receipt-footer   { padding: 8px !important; }
            .thank-you        { font-size: 11px !important; }
            .footer-note      { font-size: 9px !important; }
            .barcode-bars     { height: 28px !important; width: 140px !important; }
            .barcode-text     { font-size: 8px !important; }
        }
    </style>
</head>
<body>

{{-- ═══════ ACTION BAR (screen only) ═══════════════════════════════ --}}
<div class="action-bar">
    <a href="{{ route('cashier.pos') }}" class="btn btn-back">
        <i class="bi bi-arrow-left"></i> Back to POS
    </a>
    <button class="btn btn-print" onclick="window.print()">
        <i class="bi bi-printer"></i> Print Receipt
    </button>
</div>

{{-- Auto-print toggle + keyboard hint --}}
<div class="auto-print-wrap">
    <label>
        <input type="checkbox" id="autoPrintToggle"> Auto-print when opened
    </label>
    <div class="kbd-hint">
        <kbd class="kbd">Ctrl</kbd>+<kbd class="kbd">P</kbd> to print
    </div>
</div>

{{-- ═══════ RECEIPT ══════════════════════════════════════════════════ --}}
<div class="receipt" id="receiptCard">

    {{-- Store Header --}}
    <div class="receipt-header">
        <span class="store-logo">🔧</span>
        <div class="store-name">{{ config('store.name', 'Hassan Sanitary and Hardware Store') }}</div>
        <div class="store-tagline">{{ config('store.address', 'Rafi Commercial, Bahria Town Phase 8, Rawalpindi.') }} &bull; {{ config('store.phone', '051-8891930') }}</div>
    </div>

    {{-- Body --}}
    <div class="receipt-body">

        {{-- Invoice number --}}
        <div class="invoice-section">
            <div class="invoice-label">Official Receipt / Invoice</div>
            <div class="invoice-number">{{ $sale->invoice_number }}</div>
        </div>

        {{-- Meta info --}}
        <table class="meta-table">
            <tbody>
                <tr>
                    <td>Date</td>
                    <td>{{ $sale->created_at->format('d M Y') }}, {{ $sale->created_at->format('g:i A') }}</td>
                </tr>
                <tr>
                    <td>Cashier</td>
                    <td>{{ $sale->user?->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Payment</td>
                    <td>
                        <span class="pay-badge" style="{{ $sale->payment_method === 'credit' ? 'background:#b91c1c;' : '' }}">
                            {{ $sale->payment_method === 'cash' ? '💵' : ($sale->payment_method === 'card' ? '💳' : '📋') }}
                            {{ strtoupper($sale->payment_method) }}
                        </span>
                    </td>
                </tr>
                @if($sale->employee)
                <tr>
                    <td>Staff Credit</td>
                    <td>
                        <div style="font-weight:700; color:#111827;">{{ $sale->employee->name }}</div>
                        <div style="font-size:10px; color:#4b5563;">{{ $sale->employee->phone }} · {{ $sale->employee->address }}</div>
                        <div style="font-size:10px; color:#b91c1c; font-weight:800; margin-top:2px;">
                            Total Pending Due: {{ pkr($sale->employee->pending_payment, 2) }}
                        </div>
                    </td>
                </tr>
                @elseif($sale->customer_name && $sale->customer_name !== 'Walk-in Customer')
                <tr>
                    <td>Customer</td>
                    <td>
                        {{ $sale->customer_name }}
                        @if($sale->customer_phone) · {{ $sale->customer_phone }} @endif
                    </td>
                </tr>
                @else
                <tr>
                    <td>Customer</td>
                    <td>Walk-in Customer</td>
                </tr>
                @endif
                @if($sale->notes)
                <tr>
                    <td>Notes</td>
                    <td>{{ $sale->notes }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        {{-- Items --}}
        <div class="items-title">Items Purchased ({{ $sale->items->sum('quantity') }} pcs)</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="text-align:left; width:46%;">Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item->product_name }}</div>
                        <div class="item-meta">{{ $item->product_sku }} · {{ strtoupper($item->product_unit) }}</div>
                        @if($item->discount_amount > 0)
                            <div class="item-meta" style="color:#10b981;">−{{ pkr($item->discount_amount,2) }} disc.</div>
                        @endif
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ pkr($item->unit_price, 2) }}</td>
                    <td class="item-total">{{ pkr($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="totals-wrap">
            <div class="t-row">
                <span>Subtotal</span>
                <span>{{ pkr($sale->subtotal, 2) }}</span>
            </div>

            @if($sale->discount_amount > 0)
            <div class="t-row discount">
                <span>Discount</span>
                <span>−{{ pkr($sale->discount_amount, 2) }}</span>
            </div>
            @endif

            @if($sale->tax_amount > 0)
            <div class="t-row">
                <span>Tax</span>
                <span>{{ pkr($sale->tax_amount, 2) }}</span>
            </div>
            @endif

            <div class="t-row grand">
                <span>TOTAL</span>
                <span>{{ pkr($sale->total_amount, 2) }}</span>
            </div>

            @if($sale->payment_method === 'credit')
            <div class="t-row paid-row" style="color:#b91c1c; font-weight:700;">
                <span>Billed to Account</span>
                <span>{{ pkr($sale->total_amount, 2) }}</span>
            </div>
            <div class="change-box" style="background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;">
                <span>📋 Payment Status</span>
                <span>Unpaid (Staff Credit)</span>
            </div>
            @else
            <div class="t-row paid-row">
                <span>Paid ({{ strtoupper($sale->payment_method) }})</span>
                <span>{{ pkr($sale->paid_amount, 2) }}</span>
            </div>

            @if($sale->change_amount > 0)
            <div class="change-box">
                <span>💵 Change Due</span>
                <span>{{ pkr($sale->change_amount, 2) }}</span>
            </div>
            @endif
            @endif
        </div>

    </div>

    {{-- Footer --}}
    <div class="receipt-footer">
        <div class="thank-you">Thank You for Shopping! 🎉</div>
        <div class="footer-note">
            We appreciate your business.<br>
            Please retain this receipt — exchanges within 7 days.
        </div>

        {{-- CSS bar-code strip --}}
        <div class="barcode-strip">
            <div class="barcode-bars"></div>
            <div class="barcode-text">{{ $sale->invoice_number }}</div>
        </div>
    </div>

</div>

<script>
    // ── Auto-print query param (?print=1) ──────────────────────
    const params    = new URLSearchParams(window.location.search);
    const autoPrint = params.get('print') === '1';
    const toggle    = document.getElementById('autoPrintToggle');

    // Reflect URL state in checkbox
    toggle.checked = autoPrint;

    // If ?print=1, trigger print after a short delay (allows fonts to load)
    if (autoPrint) {
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 600);
        });
    }

    // Checkbox: update URL and trigger print immediately when checked
    toggle.addEventListener('change', function () {
        if (this.checked) {
            // Add ?print=1 to URL (no page reload)
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

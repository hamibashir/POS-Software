<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Voucher {{ $saleReturn->return_number }} — {{ config('store.name', 'Hassan & Sons') }}</title>

    {{-- Fonts: Inter (UI) + JetBrains Mono (invoice / barcode) --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --primary:    #b91c1c;
            --primary-dk: #991b1b;
            --primary-lt: #fee2e2;
            --teal:       #0f766e;
            --teal-dk:    #115e59;
            --paper:      #ffffff;
            --shadow:     0 4px 32px rgba(0,0,0,.12);
            --receipt-w:  340px;    /* screen preview width */
        }

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
            background: var(--teal);
            color: #fff;
        }
        .btn-print:hover { background: var(--teal-dk); }

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
            accent-color: var(--teal);
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

        .receipt {
            background: var(--paper);
            width: 100%;
            max-width: var(--receipt-w);
            border-radius: 14px;
            box-shadow: var(--shadow);
            overflow: hidden;
            position: relative;
        }

        .receipt-header {
            background: linear-gradient(150deg, #991b1b 0%, #7f1d1d 100%);
            color: #fff;
            padding: 24px 20px 18px;
            text-align: center;
            position: relative;
        }
        .receipt-header::after {
            content: '';
            display: block;
            height: 4px;
            background: repeating-linear-gradient(
                90deg,
                transparent,
                transparent 4px,
                rgba(255,255,255,0.2) 4px,
                rgba(255,255,255,0.2) 8px
            );
            margin-top: 14px;
        }

        .store-logo { font-size: 26px; line-height: 1; margin-bottom: 4px; display: block; }
        .store-name { font-size: 15px; font-weight: 800; letter-spacing: -0.3px; line-height: 1.25; margin-bottom: 3px; }
        .store-tagline { font-size: 10.5px; opacity: 0.85; line-height: 1.4; }

        .receipt-body { padding: 18px 18px 14px; }

        .voucher-section {
            text-align: center;
            padding: 8px 0 12px;
            border-bottom: 1px dashed #d1d5db;
            margin-bottom: 12px;
        }
        .voucher-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #b91c1c;
            margin-bottom: 2px;
        }
        .voucher-number {
            font-family: 'JetBrains Mono', monospace;
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            letter-spacing: 0.5px;
        }

        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .meta-table td { padding: 3px 0; font-size: 11.5px; vertical-align: top; }
        .meta-table td:first-child { color: #6b7280; width: 40%; font-weight: 500; }
        .meta-table td:last-child { color: #111827; font-weight: 600; text-align: right; }

        .pay-badge {
            display: inline-block;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 4px;
            padding: 1px 6px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.4px;
        }

        .items-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .items-table th {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            color: #9ca3af;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
            text-align: right;
        }
        .items-table td {
            padding: 7px 0;
            font-size: 11.5px;
            border-bottom: 1px dashed #f3f4f6;
            vertical-align: top;
            text-align: right;
        }
        .item-name { font-weight: 600; color: #111827; text-align: left; }
        .item-meta { font-size: 10px; color: #6b7280; font-family: 'JetBrains Mono', monospace; text-align: left; }
        .item-total { font-weight: 700; color: #111827; }

        .totals-wrap {
            border-top: 1.5px solid #111827;
            padding-top: 8px;
            margin-bottom: 14px;
        }
        .t-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            padding: 2.5px 0;
            color: #374151;
        }
        .t-row.grand {
            font-size: 15px;
            font-weight: 800;
            color: #b91c1c;
            border-top: 1px dashed #d1d5db;
            margin-top: 4px;
            padding-top: 6px;
        }

        .refund-box {
            background: #fef2f2;
            border: 1.5px solid #fecaca;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
        }

        .signatures-wrap {
            display: flex;
            justify-content: space-between;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px dashed #d1d5db;
        }
        .sig-box { width: 46%; text-align: center; }
        .sig-line { border-bottom: 1px solid #9ca3af; height: 28px; margin-bottom: 4px; }
        .sig-label { font-size: 10px; color: #6b7280; font-weight: 600; text-transform: uppercase; }

        .receipt-footer {
            background: #f9fafb;
            border-top: 1px dashed #e5e7eb;
            padding: 14px 18px;
            text-align: center;
        }
        .footer-note { font-size: 10.5px; color: #6b7280; line-height: 1.45; }

        @media print {
            body {
                background: none !important;
                padding: 0 !important;
                margin: 0 !important;
                min-height: auto !important;
            }
            .action-bar, .auto-print-wrap { display: none !important; }
            .receipt {
                box-shadow: none !important;
                border-radius: 0 !important;
                max-width: 100% !important;
                width: 80mm !important;
                margin: 0 auto !important;
            }
            .receipt-header {
                background: #fff !important;
                color: #000 !important;
                padding: 8px 4px 6px !important;
            }
            .store-name { color: #000 !important; font-size: 13pt !important; }
            .store-tagline { color: #333 !important; font-size: 8pt !important; }
            .receipt-header::after { display: none !important; }
            .voucher-label { color: #000 !important; }
            .refund-box {
                border: 1px solid #000 !important;
                background: #fff !important;
                color: #000 !important;
            }
            @page {
                size: 80mm auto;
                margin: 2mm;
            }
        }
    </style>
</head>
<body>

{{-- Action buttons (screen only) --}}
<div class="action-bar">
    <a href="{{ route('cashier.pos') }}" class="btn btn-back">
        <i class="bi bi-arrow-left"></i> POS Counter
    </a>
    <button class="btn btn-print" onclick="window.print()">
        <i class="bi bi-printer-fill"></i> Print Return Voucher
    </button>
</div>

{{-- Auto-print toggle (screen only) --}}
<div class="auto-print-wrap">
    <label for="autoPrintToggle">
        <input type="checkbox" id="autoPrintToggle">
        Auto-print on page load
    </label>
    <div class="kbd-hint">
        <kbd class="kbd">Ctrl</kbd>+<kbd class="kbd">P</kbd> to print
    </div>
</div>

{{-- Receipt Card --}}
<div class="receipt" id="receiptCard">

    {{-- Store Header --}}
    <div class="receipt-header">
        <span class="store-logo">🔧</span>
        <div class="store-name">{{ config('store.name', 'Hassan & Sons') }}</div>
        <div class="store-tagline">{{ config('store.address', 'Rafi Commercial, Bahria Town Phase 8, Rawalpindi.') }} &bull; {{ config('store.phone', '051-8891930') }}</div>
    </div>

    {{-- Body --}}
    <div class="receipt-body">

        {{-- Voucher number --}}
        <div class="voucher-section">
            <div class="voucher-label">Customer Return & Refund Voucher</div>
            <div class="voucher-number">{{ $saleReturn->return_number }}</div>
        </div>

        {{-- Meta info --}}
        <table class="meta-table">
            <tbody>
                <tr>
                    <td>Date & Time</td>
                    <td>{{ $saleReturn->created_at ? $saleReturn->created_at->format('d M Y, g:i A') : now()->format('d M Y, g:i A') }}</td>
                </tr>
                <tr>
                    <td>Cashier</td>
                    <td>{{ $saleReturn->user?->name ?? 'POS Cashier' }}</td>
                </tr>
                @if($saleReturn->sale)
                <tr>
                    <td>Original Invoice</td>
                    <td>
                        <span style="font-family:'JetBrains Mono', monospace; font-weight:700;">
                            {{ $saleReturn->sale->invoice_number }}
                        </span>
                    </td>
                </tr>
                @endif
                <tr>
                    <td>Customer</td>
                    <td>
                        {{ $saleReturn->customer_name }}
                        @if($saleReturn->customer_phone) · {{ $saleReturn->customer_phone }} @endif
                    </td>
                </tr>
                <tr>
                    <td>Refund Method</td>
                    <td>
                        <span class="pay-badge">
                            {{ match($saleReturn->refund_method) {
                                'cash' => '💵 CASH REFUND',
                                'card' => '💳 CARD / BANK',
                                'credit_adjustment' => '💼 CREDIT ADJUSTMENT',
                                default => strtoupper($saleReturn->refund_method)
                            } }}
                        </span>
                    </td>
                </tr>
                @if($saleReturn->reason)
                <tr>
                    <td>Return Reason</td>
                    <td>{{ $saleReturn->reason }}</td>
                </tr>
                @endif
                @if($saleReturn->notes)
                <tr>
                    <td>Notes</td>
                    <td>{{ $saleReturn->notes }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        {{-- Returned Items --}}
        <div class="items-title">Returned Products ({{ $saleReturn->items->sum('quantity') }} pcs)</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="text-align:left; width:46%;">Item</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saleReturn->items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item->product_name }}</div>
                        <div class="item-meta">{{ $item->product_sku }} · {{ strtoupper($item->product_unit) }}</div>
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
            <div class="t-row grand">
                <span>TOTAL REFUND</span>
                <span>{{ pkr($saleReturn->refund_amount, 2) }}</span>
            </div>
        </div>

        {{-- Refund Confirmation Box --}}
        <div class="refund-box">
            <span style="font-weight:600; color:#991b1b;">Refund Status:</span>
            <span style="font-weight:800; font-size:13px; color:#991b1b;">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Refund Disbursed
            </span>
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

    </div>

    {{-- Footer --}}
    <div class="receipt-footer">
        <div class="footer-note">
            Items returned and restored to stock inventory.<br>
            Official store refund confirmation.
        </div>
    </div>

</div>

<script>
    const params    = new URLSearchParams(window.location.search);
    const autoPrint = params.get('print') === '1';
    const toggle    = document.getElementById('autoPrintToggle');

    toggle.checked = autoPrint;

    if (autoPrint) {
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 600);
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

@extends('layouts.cashier')

@section('title', 'POS Workstation')

@push('styles')
<style>
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    :root {
        --pos-primary:        #0f766e;
        --pos-primary-dk:     #115e59;
        --pos-primary-lt:     #ccfbf1;
        --pos-primary-subtle: #f0fdfa;
        --pos-accent:         #0284c7;
        --pos-dark:           #0f172a;
        --pos-border:         #e2e8f0;
        --pos-bg:             #f8fafc;
        --pos-card-bg:        #ffffff;
        --pos-amber:          #d97706;
        --pos-amber-lt:       #fef3c7;
        --bar-h:              52px;
    }

    /* ── Main Layout ─────────────────────────────────────── */
    .pos-layout {
        display: flex;
        flex-direction: column;
        height: calc(100vh - var(--bar-h));
        margin-top: var(--bar-h);
        background: #f1f5f9;
        overflow: hidden;
    }

    /* ── Top Quick Action Header ───────────────── */
    .pos-top-header {
        background: #ffffff;
        border-bottom: 1px solid var(--pos-border);
        padding: 9px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-shrink: 0;
        z-index: 50;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .top-header-brand {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 800;
        font-size: 15px;
        color: #0f172a;
    }
    .top-header-brand .brand-ico {
        font-size: 22px;
        color: #0f766e;
    }
    .top-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    /* ── Big Long Product Search Bar Under Current Order ── */
    .stage-search-container {
        padding: 12px 20px;
        background: #ffffff;
        border-bottom: 1px solid var(--pos-border);
        position: relative;
        z-index: 45;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }
    .stage-search-wrap {
        position: relative;
        width: 100%;
    }
    .stage-search-wrap .ico {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 26px;
        color: #0f766e;
        pointer-events: none;
    }
    .stage-search-input {
        width: 100%;
        height: 52px;
        padding: 0 48px 0 54px;
        border: 2px solid #cbd5e1;
        border-radius: 12px;
        background: #f8fafc;
        font-size: 15.5px;
        font-weight: 600;
        color: #0f172a;
        outline: none;
        transition: all 0.2s ease-in-out;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);
    }
    .stage-search-input:focus {
        border-color: #0f766e;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.15), 0 4px 12px rgba(15, 118, 110, 0.08);
    }
    .stage-search-input::placeholder {
        color: #94a3b8;
        font-weight: 500;
        font-size: 14.5px;
    }
    .stage-search-wrap .search-clear-btn {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 4px;
        border-radius: 50%;
        transition: all 0.15s;
    }
    .stage-search-wrap .search-clear-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* Autocomplete Dropdown */
    .search-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        background: #ffffff;
        border-radius: 14px;
        border: 1.5px solid #cbd5e1;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.18);
        max-height: 440px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }
    .search-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background 0.12s;
    }
    .search-item:last-child { border-bottom: none; }
    .search-item:hover, .search-item.active {
        background: #f0fdfa;
    }
    .search-item .thumb {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        object-fit: cover;
        background: #f1f5f9;
        flex-shrink: 0;
        border: 1px solid #e2e8f0;
    }
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.16);
        max-height: 420px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }
    .search-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background 0.12s;
    }
    .search-item:last-child { border-bottom: none; }
    .search-item:hover, .search-item.active {
        background: #f0fdfa;
    }
    .search-item-thumb {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        background-size: cover;
        background-position: center;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        flex-shrink: 0;
    }
    .search-item-info { flex: 1; min-width: 0; }
    .search-item-name {
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .search-item-meta {
        font-size: 11.5px;
        color: #64748b;
        display: flex;
        gap: 8px;
        align-items: center;
        margin-top: 2px;
    }
    .search-item-price {
        font-size: 14.5px;
        font-weight: 800;
        color: #0f766e;
        text-align: right;
        flex-shrink: 0;
    }

    .top-action-btn {
        height: 42px;
        padding: 0 13px;
        border-radius: 10px;
        border: 1.5px solid #cbd5e1;
        background: #ffffff;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .top-action-btn:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
    }
    .top-action-btn.primary {
        background: #0f766e;
        border-color: #0f766e;
        color: #ffffff;
    }
    .top-action-btn.primary:hover {
        background: #115e59;
    }
    .top-action-btn.hold-badge-btn {
        border-color: #f59e0b;
        background: #fffbeb;
        color: #b45309;
    }
    .top-action-btn.hold-badge-btn:hover {
        background: #fef3c7;
    }
    .count-chip {
        background: #f59e0b;
        color: #ffffff;
        font-size: 11px;
        font-weight: 800;
        padding: 1px 7px;
        border-radius: 10px;
        margin-left: 2px;
    }

    /* ── Workspace Split ─────────────────────────────────── */
    .pos-workspace {
        flex: 1;
        display: flex;
        overflow: hidden;
    }

    /* ── MAIN: Expansive Current Order Table ─────────────── */
    .order-stage {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #ffffff;
        overflow: hidden;
        border-right: 1px solid var(--pos-border);
    }

    .order-stage-header {
        padding: 12px 20px;
        background: #ffffff;
        border-bottom: 1px solid var(--pos-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .order-title-box {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .order-title {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .order-title .si {
        color: #0f766e;
        font-size: 24px;
    }
    .order-tag {
        background: #f1f5f9;
        color: #475569;
        font-size: 11.5px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 6px;
        font-family: monospace;
    }
    .order-metrics {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .metric-pill {
        background: #f0fdfa;
        border: 1px solid #ccfbf1;
        color: #0f766e;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    /* ── HELD ORDERS QUEUE BAR ───────────────────────────── */
    .hold-queue-bar {
        background: #fffbeb;
        border-bottom: 2px solid #fef3c7;
        padding: 8px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        overflow-x: auto;
        scrollbar-width: none;
        flex-shrink: 0;
        animation: slideDown 0.2s ease;
    }
    .hold-queue-bar::-webkit-scrollbar { display: none; }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .hold-queue-title {
        font-size: 12px;
        font-weight: 800;
        color: #b45309;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }
    .hold-chips-container {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .hold-ticket-chip {
        background: #ffffff;
        border: 1.5px solid #fde68a;
        border-radius: 8px;
        padding: 4px 8px 4px 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 1px 3px rgba(180, 83, 9, 0.08);
        white-space: nowrap;
        transition: all 0.15s;
    }
    .hold-ticket-chip:hover {
        border-color: #f59e0b;
        box-shadow: 0 2px 6px rgba(180, 83, 9, 0.15);
    }
    .hold-ticket-info {
        font-size: 12px;
        color: #1e293b;
        font-weight: 600;
    }
    .hold-ticket-info strong {
        color: #b45309;
    }
    .btn-resume-hold {
        background: #0f766e;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        padding: 3px 8px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        transition: background 0.12s;
    }
    .btn-resume-hold:hover {
        background: #115e59;
    }
    .btn-discard-hold {
        background: transparent;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 2px;
        border-radius: 4px;
        transition: all 0.1s;
    }
    .btn-discard-hold:hover {
        background: #fee2e2;
        color: #ef4444;
    }

    /* Table Container */
    .order-table-wrap {
        flex: 1;
        overflow-y: auto;
        position: relative;
    }
    .order-table-wrap::-webkit-scrollbar { width: 6px; height: 6px; }
    .order-table-wrap::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

    .order-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0;
    }
    .order-table thead th {
        position: sticky;
        top: 0;
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        padding: 12px 16px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        z-index: 10;
        white-space: nowrap;
    }
    .order-table tbody tr {
        transition: background 0.12s;
    }
    .order-table tbody tr:hover {
        background: #f8fafc;
    }
    .order-table tbody td {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .td-num {
        width: 44px;
        font-size: 13px;
        font-weight: 700;
        color: #94a3b8;
        text-align: center;
    }
    .prod-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 220px;
    }
    .prod-thumb {
        width: 46px;
        height: 46px;
        border-radius: 8px;
        background-size: cover;
        background-position: center;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        flex-shrink: 0;
    }
    .prod-info-block { min-width: 0; }
    .prod-title {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
    }
    .prod-submeta {
        font-size: 11.5px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 3px;
    }
    .cat-chip {
        background: #f1f5f9;
        color: #475569;
        padding: 1px 6px;
        border-radius: 4px;
        font-weight: 600;
    }
    .stock-hint {
        color: #059669;
        font-weight: 600;
    }
    .stock-hint.low {
        color: #d97706;
    }

    .td-price {
        font-size: 14.5px;
        font-weight: 700;
        color: #334155;
        white-space: nowrap;
    }

    .qty-box {
        display: inline-flex;
        align-items: center;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        overflow: hidden;
    }
    .qty-btn {
        width: 32px;
        height: 32px;
        background: #f8fafc;
        border: none;
        color: #475569;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.1s;
    }
    .qty-btn:hover {
        background: #0f766e;
        color: #ffffff;
    }
    .qty-field {
        width: 44px;
        height: 32px;
        border: none;
        border-left: 1.5px solid #cbd5e1;
        border-right: 1.5px solid #cbd5e1;
        text-align: center;
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
        background: #ffffff;
        outline: none;
    }

    .td-total {
        font-size: 16px;
        font-weight: 800;
        color: #0f766e;
        white-space: nowrap;
    }
    .td-action {
        width: 50px;
        text-align: center;
    }
    .btn-del-item {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s;
    }
    .btn-del-item:hover {
        background: #fee2e2;
        color: #ef4444;
    }

    /* Empty Cart State */
    .cart-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        min-height: 340px;
        color: #94a3b8;
        padding: 40px 20px;
        text-align: center;
    }
    .cart-empty-state .si {
        font-size: 64px;
        color: #cbd5e1;
        margin-bottom: 14px;
    }
    .cart-empty-state h3 {
        font-size: 17px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 6px;
    }
    .cart-empty-state p {
        font-size: 13.5px;
        color: #94a3b8;
        max-width: 360px;
        margin-bottom: 18px;
    }
    .empty-action-shortcuts {
        display: flex;
        gap: 10px;
    }
    .shortcut-pill {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s;
    }
    .shortcut-pill:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* ── RIGHT: Checkout Station ─────────────────────────── */
    .checkout-station {
        width: 390px;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        flex-shrink: 0;
    }
    .checkout-station::-webkit-scrollbar { width: 5px; }
    .checkout-station::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

    .station-section {
        padding: 16px 18px;
        border-bottom: 1px solid var(--pos-border);
        background: #ffffff;
    }

    /* Customer Quick Card */
    .customer-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    .sec-label {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 5px;
        margin: 0;
    }
    .btn-toggle-text {
        font-size: 12px;
        font-weight: 700;
        color: #0f766e;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
    }
    .btn-toggle-text:hover { text-decoration: underline; }

    .cust-inputs-wrap {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 8px;
    }
    .pos-field {
        height: 38px;
        width: 100%;
        padding: 0 12px;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        color: #0f172a;
        background: #ffffff;
        outline: none;
        transition: border-color 0.15s;
    }
    .pos-field:focus {
        border-color: #0f766e;
    }

    /* Summary Totals Box */
    .summary-card {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13.5px;
        color: #64748b;
    }
    .summary-row.bold {
        font-weight: 700;
        color: #1e293b;
    }
    .disc-input-wrap {
        position: relative;
    }
    .disc-input-wrap .si {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 16px;
        color: #94a3b8;
        pointer-events: none;
    }
    .disc-input-field {
        height: 36px;
        width: 100%;
        padding: 0 10px 0 32px;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        outline: none;
    }
    .disc-input-field:focus { border-color: #0f766e; }

    .grand-total-box {
        background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
        border-radius: 12px;
        padding: 14px 16px;
        color: #ffffff;
        margin-top: 4px;
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        box-shadow: 0 4px 14px rgba(15, 118, 110, 0.25);
    }
    .grand-total-label {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.9;
    }
    .grand-total-val {
        font-size: 28px;
        font-weight: 900;
        letter-spacing: -0.5px;
        line-height: 1;
    }

    /* Payment Methods */
    .pay-tabs {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 8px;
    }
    .pay-tab-btn {
        padding: 10px 6px;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        background: #ffffff;
        font-size: 12.5px;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        transition: all 0.15s;
    }
    .pay-tab-btn .si { font-size: 20px; }
    .pay-tab-btn.active {
        border-color: #0f766e;
        background: #f0fdfa;
        color: #0f766e;
    }
    .pay-tab-btn:not(.active):hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    /* Quick Cash Chips */
    .cash-chips {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 6px;
        margin-top: 8px;
    }
    .cash-chip-btn {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 5px 2px;
        font-size: 11.5px;
        font-weight: 700;
        color: #334155;
        cursor: pointer;
        text-align: center;
        transition: all 0.1s;
    }
    .cash-chip-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .received-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
    }
    .received-field {
        height: 44px;
        flex: 1;
        padding: 0 12px;
        border: 2px solid #cbd5e1;
        border-radius: 10px;
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        text-align: right;
        background: #ffffff;
        outline: none;
    }
    .received-field:focus { border-color: #0f766e; }

    .change-box {
        background: #ecfdf5;
        border: 1.5px solid #a7f3d0;
        border-radius: 10px;
        padding: 10px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
        font-weight: 800;
        color: #065f46;
        margin-top: 10px;
    }
    .change-box.neg {
        background: #fef2f2;
        border-color: #fecaca;
        color: #b91c1c;
    }

    /* Employee Credit Box */
    .emp-credit-box {
        background: #fef2f2;
        border: 1.5px solid #fecaca;
        border-radius: 10px;
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-top: 10px;
    }

    /* Complete Sale Action Bar */
    .action-station {
        padding: 16px 18px 20px;
        background: #ffffff;
        margin-top: auto;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .btn-complete-sale {
        width: 100%;
        height: 52px;
        background: #0f766e;
        color: #ffffff;
        font-size: 16px;
        font-weight: 800;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 16px rgba(15, 118, 110, 0.35);
        transition: all 0.15s;
    }
    .btn-complete-sale:hover:not(:disabled) {
        background: #115e59;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(15, 118, 110, 0.45);
    }
    .btn-complete-sale:disabled {
        opacity: 0.45;
        cursor: not-allowed;
        box-shadow: none;
    }
    .btn-complete-sale .si { font-size: 22px; }

    .sub-action-row {
        display: flex;
        gap: 8px;
    }
    .btn-sub-act {
        flex: 1;
        height: 38px;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        transition: all 0.15s;
    }
    .btn-sub-act:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .btn-sub-act.hold-act {
        background: #fffbeb;
        border-color: #fde68a;
        color: #b45309;
    }
    .btn-sub-act.hold-act:hover {
        background: #fef3c7;
        color: #92400e;
    }
    .btn-sub-act.danger:hover {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #ef4444;
    }

    /* ── Offcanvas Visual Catalog ────────────────────────── */
    .offcanvas-catalog {
        width: 480px !important;
    }
    .cat-pills-bar {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        padding-bottom: 8px;
        margin-bottom: 12px;
        scrollbar-width: none;
    }
    .cat-pills-bar::-webkit-scrollbar { display: none; }
    .cat-pill {
        flex-shrink: 0;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 700;
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        color: #64748b;
        cursor: pointer;
        transition: all 0.15s;
    }
    .cat-pill.active {
        background: #0f766e;
        border-color: #0f766e;
        color: #ffffff;
    }
    .catalog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 10px;
    }
    .cat-product-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 8px;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 6px;
        transition: all 0.15s;
    }
    .cat-product-card:hover {
        border-color: #0f766e;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 118, 110, 0.15);
    }
    .cat-prod-img {
        width: 100%;
        aspect-ratio: 1/1;
        border-radius: 6px;
        background-size: cover;
        background-position: center;
        background-color: #f1f5f9;
        position: relative;
    }
    .cat-prod-name {
        font-size: 12px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .cat-prod-price {
        font-size: 13px;
        font-weight: 800;
        color: #0f766e;
    }

    /* ── Receipt Modal ───────────────────────────────────── */
    .receipt-modal .modal-content {
        border-radius: 18px;
        border: none;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    }
    .r-inv {
        font-size: 24px;
        font-weight: 900;
        color: #0f766e;
        letter-spacing: 1px;
    }
    .r-row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        padding: 5px 0;
        color: #334155;
    }
    .r-total {
        font-size: 20px;
        font-weight: 800;
        border-top: 2px dashed #cbd5e1;
        padding-top: 10px;
        margin-top: 6px;
        color: #0f172a;
    }
    .r-change {
        font-size: 24px;
        font-weight: 900;
        color: #059669;
    }
</style>
@endpush

@section('content')

<div class="pos-layout">

    {{-- ══════════ TOP ACTION HEADER ══════════ --}}
    <header class="pos-top-header">
        <div class="top-header-brand">
            <span class="material-symbols-outlined brand-ico">point_of_sale</span>
            <span>POS Workstation</span>
        </div>

        <div class="top-header-actions">
            {{-- Held Orders Queue Trigger --}}
            <button type="button" class="top-action-btn hold-badge-btn" id="topHoldBtn" onclick="openHeldOrdersModal()" title="Held Orders Queue (F7)">
                <span class="material-symbols-outlined" style="font-size:18px;">pause_circle</span>
                <span>Hold Queue</span>
                <span class="count-chip" id="topHoldCountBadge" style="display:none;">0</span>
            </button>

            {{-- Pay Supplier Trigger --}}
            <button type="button" class="top-action-btn" id="topSupplierPayBtn" onclick="openSupplierPayModal()" title="Pay Supplier / Clear Balance (F8)" style="background:#fffbeb; border-color:#fde68a; color:#b45309;">
                <span class="material-symbols-outlined" style="font-size:18px; color:#d97706;">payments</span>
                <span>Pay Supplier</span>
                <span style="font-size:11px; opacity:0.75; margin-left:2px;">[F8]</span>
            </button>

            {{-- Customer Payment Return Trigger --}}
            <button type="button" class="top-action-btn" id="topEmployeePayBtn" onclick="openEmployeePayModal()" title="Receive Customer Payment / Clear Due (F10)" style="background:#f0fdf4; border-color:#bbf7d0; color:#15803d;">
                <span class="material-symbols-outlined" style="font-size:18px; color:#16a34a;">account_balance_wallet</span>
                <span>Clear Customer Due</span>
                <span style="font-size:11px; opacity:0.75; margin-left:2px;">[F10]</span>
            </button>

            {{-- Customer Product Return Trigger --}}
            <button type="button" class="top-action-btn" id="topCustomerReturnBtn" onclick="openCustomerReturnModal()" title="Customer Product Return / Restore Stock (F11)" style="background:#fef2f2; border-color:#fecaca; color:#b91c1c;">
                <span class="material-symbols-outlined" style="font-size:18px; color:#ef4444;">assignment_return</span>
                <span>Customer Return</span>
                <span style="font-size:11px; opacity:0.75; margin-left:2px;">[F11]</span>
            </button>

            {{-- Browse Visual Catalog Drawer Trigger --}}
            <button type="button" class="top-action-btn" data-bs-toggle="offcanvas" data-bs-target="#catalogOffcanvas" title="Open Visual Catalog (F3)">
                <span class="material-symbols-outlined" style="font-size:18px; color:#0f766e;">grid_view</span>
                <span>Browse Catalog</span>
                <span style="font-size:11px; opacity:0.6; margin-left:2px;">[F3]</span>
            </button>

            {{-- Quick Customer Toggle --}}
            <button type="button" class="top-action-btn" id="topCustomerBtn" onclick="toggleCustomerSection()">
                <span class="material-symbols-outlined" style="font-size:18px;">person</span>
                <span id="topCustomerBtnLabel">Customer</span>
            </button>

            {{-- Clear Cart Action --}}
            <button type="button" class="top-action-btn" onclick="clearCart()" title="Clear Current Order">
                <span class="material-symbols-outlined" style="font-size:18px; color:#ef4444;">delete_sweep</span>
                <span>Clear</span>
            </button>
        </div>
    </header>

    {{-- ══════════ MAIN WORKSPACE ══════════ --}}
    <main class="pos-workspace">

        {{-- ────── LEFT: Expansive Current Order Table ────── --}}
        <section class="order-stage">

            {{-- Order Header --}}
            <div class="order-stage-header">
                <div class="order-title-box">
                    <h2 class="order-title">
                        <span class="material-symbols-outlined si">receipt_long</span>
                        Current Order
                    </h2>
                    <span class="order-tag" id="orderNo">#DRAFT</span>
                </div>

                <div class="order-metrics">
                    <div class="metric-pill">
                        <span class="material-symbols-outlined" style="font-size:16px;">inventory_2</span>
                        <span id="metricItemsCount">0 Items</span>
                    </div>
                    <div class="metric-pill">
                        <span class="material-symbols-outlined" style="font-size:16px;">calculate</span>
                        <span id="metricUnitsCount">0 Units</span>
                    </div>
                </div>
            </div>

            {{-- ────── BIG LONG PRODUCT SEARCH BAR UNDER CURRENT ORDER ────── --}}
            <div class="stage-search-container">
                <div class="search-box-wrap stage-search-wrap">
                    <span class="material-symbols-outlined ico">search</span>
                    <input type="text" id="productSearch" class="search-input stage-search-input"
                           placeholder="Scan Barcode or Search Products by Name, SKU, Barcode, Category... (Press F2 to focus)"
                           autocomplete="off" autofocus>
                    <button type="button" class="search-clear-btn" id="searchClearBtn" onclick="clearSearchInput()">
                        <span class="material-symbols-outlined" style="font-size:20px;">close</span>
                    </button>

                    {{-- Floating Dropdown for Results --}}
                    <div class="search-dropdown" id="searchDropdown"></div>
                </div>
            </div>

            {{-- ────── HELD ORDERS QUEUE STRIP (Always visible on stage when orders are on hold) ────── --}}
            <div class="hold-queue-bar" id="holdQueueBar" style="display:none;">
                <div class="hold-queue-title">
                    <span class="material-symbols-outlined" style="font-size:17px;">pause_circle</span>
                    Held Orders Queue (<span id="holdBarCount">0</span>):
                </div>
                <div class="hold-chips-container" id="holdChipsContainer">
                    {{-- Held order ticket chips rendered here --}}
                </div>
            </div>

            {{-- Order Table Container --}}
            <div class="order-table-wrap">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th class="td-num">#</th>
                            <th style="min-width:260px;">Product / Item Details</th>
                            <th style="width:140px;">SKU / Unit</th>
                            <th style="width:140px;">Unit Price</th>
                            <th style="width:160px; text-align:center;">Quantity</th>
                            <th style="width:150px; text-align:right;">Total (Rs.)</th>
                            <th class="td-action"></th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody">
                        {{-- Items rendered dynamically via JavaScript --}}
                    </tbody>
                </table>

                {{-- Empty Cart Illustration --}}
                <div class="cart-empty-state" id="cartEmptyState">
                    <span class="material-symbols-outlined si">shopping_cart_checkout</span>
                    <h3>Current Order is Empty</h3>
                    <p>Scan a product barcode or search items in the top bar to immediately start building this sale.</p>
                    <div class="empty-action-shortcuts">
                        <div class="shortcut-pill" onclick="focusSearch()">
                            <span class="material-symbols-outlined" style="font-size:16px;">search</span>
                            Search Product [F2]
                        </div>
                        <div class="shortcut-pill" data-bs-toggle="offcanvas" data-bs-target="#catalogOffcanvas">
                            <span class="material-symbols-outlined" style="font-size:16px;">grid_view</span>
                            Browse Catalog [F3]
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ────── RIGHT: High-Speed Checkout Station ────── --}}
        <aside class="checkout-station">

            {{-- 1. Customer Section (Collapsible / Active) --}}
            <div class="station-section" id="customerSectionBox" style="display:none;">
                <div class="customer-card-header">
                    <span class="sec-label">
                        <span class="material-symbols-outlined" style="font-size:16px; color:#0f766e;">person</span>
                        Customer Details
                    </span>
                    <button type="button" class="btn-toggle-text" onclick="toggleCustomerSection()">Close</button>
                </div>
                <div class="cust-inputs-wrap">
                    <input type="text" id="customerName" class="pos-field" placeholder="Customer Name (e.g. Ali Khan)">
                    <input type="text" id="customerPhone" class="pos-field" placeholder="Phone (e.g. 0300-1234567)">
                </div>
            </div>

            {{-- 2. Financial Summary & Totals --}}
            <div class="station-section">
                <div class="summary-card">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong id="cartSubtotal">Rs. 0.00</strong>
                    </div>

                    {{-- Discount Row --}}
                    <div class="summary-row">
                        <span>Discount (Rs.)</span>
                        <div class="disc-input-wrap" style="width:140px;">
                            <span class="material-symbols-outlined si">percent</span>
                            <input type="number" id="discountInput" class="disc-input-field"
                                   min="0" step="1" value="0" placeholder="0" oninput="updateTotals()">
                        </div>
                    </div>

                    <div class="summary-row">
                        <span>Tax (0%)</span>
                        <span id="taxDisplay">Rs. 0.00</span>
                    </div>

                    {{-- Big Grand Total Box --}}
                    <div class="grand-total-box">
                        <span class="grand-total-label">Grand Total</span>
                        <span class="grand-total-val" id="cartTotal">Rs. 0.00</span>
                    </div>
                </div>
            </div>

            {{-- 3. Payment Method Station --}}
            <div class="station-section">
                <span class="sec-label mb-2">Payment Method</span>
                <div class="pay-tabs">
                    <button type="button" class="pay-tab-btn active" data-method="cash" onclick="selectPayment('cash')">
                        <span class="material-symbols-outlined si">payments</span>
                        Cash
                    </button>
                    <button type="button" class="pay-tab-btn" data-method="card" onclick="selectPayment('card')">
                        <span class="material-symbols-outlined si">credit_card</span>
                        Card
                    </button>
                    <button type="button" class="pay-tab-btn" data-method="credit" onclick="selectPayment('credit')">
                        <span class="material-symbols-outlined si">account_balance_wallet</span>
                        Credit
                    </button>
                </div>

                {{-- Cash Payment Controls --}}
                <div id="cashControls">
                    <div class="received-row">
                        <span style="font-size:13px; font-weight:700; color:#334155; white-space:nowrap;">Cash Received:</span>
                        <input type="number" id="paidInput" class="received-field"
                               min="0" step="any" placeholder="0.00" oninput="updateChange()">
                    </div>



                    {{-- Change / Balance Due Display --}}
                    <div class="change-box" id="changeDisplay">
                        <span>Change Due</span>
                        <span id="changeAmount" style="font-size:16px;">Rs. 0.00</span>
                    </div>
                </div>

                {{-- Customer Credit Controls --}}
                <div id="creditControls" style="display:none; margin-top:10px;">
                    <label style="font-size:12px; font-weight:700; color:#374151; display:flex; align-items:center; gap:4px; margin-bottom:4px;">
                        <span class="material-symbols-outlined" style="font-size:16px; color:#0f766e;">person</span>
                        Authorized Customer <span style="color:#ef4444;">*</span>
                    </label>
                    <select id="employeeSelect" class="pos-field" onchange="onEmployeeSelect()">
                        <option value="">-- Choose Customer --</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp['id'] }}"
                                data-name="{{ $emp['name'] }}"
                                data-phone="{{ $emp['phone'] }}"
                                data-address="{{ $emp['address'] }}"
                                data-pending="{{ $emp['pending_payment'] }}">
                            {{ $emp['name'] }} (Due: Rs. {{ number_format($emp['pending_payment'], 2) }})
                        </option>
                        @endforeach
                    </select>

                    <div id="employeeDetailsCard" class="emp-credit-box" style="display:none;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                            <div>
                                <div style="font-weight:800; font-size:13.5px; color:#111827;" id="cardEmpName">—</div>
                                <div style="font-size:12px; color:#4b5563;" id="cardEmpPhone">—</div>
                                <div style="font-size:11px; color:#6b7280; margin-top:2px;" id="cardEmpAddress">—</div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:10px; font-weight:700; color:#991b1b; text-transform:uppercase;">Pending Due</div>
                                <div style="font-weight:800; font-size:13px; color:#991b1b;" id="cardEmpPending">Rs. 0.00</div>
                            </div>
                        </div>
                        <div style="border-top:1px dashed #fecaca; margin-top:6px; padding-top:6px; font-size:12px; color:#991b1b; font-weight:700; display:flex; justify-content:space-between;">
                            <span>New Ledger Balance:</span>
                            <span id="cardEmpNewTotal" style="font-size:13px;">Rs. 0.00</span>
                        </div>
                        <div id="cardEmpPayAction" style="margin-top:8px; text-align:right;">
                            <button type="button" class="btn btn-sm btn-outline-success" style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:6px;" onclick="openEmployeePayModalFromCard()">
                                <i class="bi bi-cash me-1"></i> Receive / Clear Due
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Card Method Info Note --}}
                <div id="cardControls" style="display:none; margin-top:10px;">
                    <div style="background:#f0f9ff; border:1px solid #bae6fd; border-radius:8px; padding:10px 12px; font-size:12.5px; color:#0369a1; display:flex; align-items:center; gap:8px;">
                        <span class="material-symbols-outlined" style="font-size:20px;">credit_card</span>
                        <div>Swipe card on bank POS terminal. Paid amount is matched automatically.</div>
                    </div>
                </div>
            </div>

            {{-- 4. Action Station (Complete Sale & Sub Actions) --}}
            <div class="action-station">
                <button type="button" class="btn-complete-sale" id="completeSaleBtn" onclick="completeSale()" disabled>
                    <span id="completeSaleLabel">Complete Sale</span>
                    <span class="material-symbols-outlined si">check_circle</span>
                    <span style="font-size:12px; opacity:0.7; margin-left:4px;">[F9]</span>
                </button>

                <div class="sub-action-row">
                    <button type="button" class="btn-sub-act hold-act" onclick="holdCurrentOrder()" title="Put Current Order on Hold (F7)">
                        <span class="material-symbols-outlined" style="font-size:16px;">pause_circle</span>
                        Hold Order [F7]
                    </button>
                    <button type="button" class="btn-sub-act danger" onclick="clearCart()">
                        <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                        Void Order
                    </button>
                </div>
            </div>
        </aside>
    </main>
</div>

{{-- ══════════ HELD ORDERS MODAL ══════════ --}}
<div class="modal fade" id="heldOrdersModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:18px; border:none; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold" style="color:#0f172a; display:flex; align-items:center; gap:8px;">
                    <span class="material-symbols-outlined" style="color:#d97706; font-size:24px;">pause_circle</span>
                    Held Orders Queue (<span id="modalHoldCount">0</span>)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" style="max-height:60vh; overflow-y:auto;" id="heldOrdersListBody">
                {{-- Loaded dynamically --}}
            </div>
            <div class="modal-footer border-top px-4 py-3">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:10px; font-weight:700;">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════ OFFCANVAS VISUAL CATALOG ══════════ --}}
<div class="offcanvas offcanvas-end offcanvas-catalog" tabindex="-1" id="catalogOffcanvas">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" style="color:#0f172a; display:flex; align-items:center; gap:8px;">
            <span class="material-symbols-outlined" style="color:#0f766e;">grid_view</span>
            Product Catalog
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        {{-- Category Pills --}}
        <div class="cat-pills-bar">
            <button type="button" class="cat-pill active" data-cat="" onclick="selectDrawerCategory(this, '')">All Items</button>
            @foreach(\App\Models\Category::where('is_active', true)->orderBy('name')->get() as $cat)
            <button type="button" class="cat-pill" data-cat="{{ $cat->id }}" onclick="selectDrawerCategory(this, '{{ $cat->id }}')">{{ $cat->name }}</button>
            @endforeach
        </div>

        {{-- Catalog Grid Container --}}
        <div class="catalog-grid" id="catalogDrawerGrid">
            {{-- Loaded dynamically --}}
        </div>
    </div>
</div>

{{-- ══════════ RECEIPT MODAL ══════════ --}}
<div class="modal fade receipt-modal" id="receiptModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 text-center">
            <div style="font-size:54px; color:#059669; margin-bottom:4px;">
                <span class="material-symbols-outlined"
                      style="font-size:54px; color:#059669; font-variation-settings:'FILL' 1,'wght' 400,'GRAD' 0,'opsz' 48;">
                    check_circle
                </span>
            </div>
            <h4 style="font-size:22px; font-weight:800; margin-bottom:2px; color:#0f172a;">Sale Completed!</h4>
            <div class="r-inv mt-1" id="receiptInvoice">—</div>

            <div class="mt-3 text-start" style="background:#f8fafc; border-radius:12px; padding:16px; border:1px solid #e2e8f0;">
                <div class="r-row"><span>Total Amount:</span> <strong id="receiptTotal">—</strong></div>
                <div class="r-row"><span>Amount Paid:</span>  <strong id="receiptPaid">—</strong></div>
                <div class="r-row r-total"><span>Change Returned:</span> <span class="r-change" id="receiptChange">—</span></div>
            </div>
            <p style="font-size:13px; color:#64748b; margin-top:12px;" id="receiptItems">—</p>

            <div style="display:flex; gap:10px; margin-top:14px;">
                <a id="printReceiptBtn" href="#" target="_blank" rel="noopener"
                   style="flex:1; background:#f8fafc; color:#334155; border:1.5px solid #cbd5e1; border-radius:10px;
                          padding:12px; font-size:14px; font-weight:700; text-decoration:none;
                          display:flex; align-items:center; justify-content:center; gap:6px; transition:all 0.15s;">
                    🖨️ Print Receipt
                </a>
                <button type="button" class="btn-complete-sale" onclick="closeReceipt()" style="flex:1; height:46px; border-radius:10px;">
                    <span class="material-symbols-outlined" style="font-size:18px;">refresh</span>
                    New Sale
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════ PAY SUPPLIER MODAL (POS) ══════════ --}}
<div class="modal fade" id="posSupplierPayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px; border:none; box-shadow:0 20px 60px rgba(0,0,0,0.25); overflow:hidden;">
            <div class="modal-header px-4 py-3" style="background:#0f766e; color:#fff;">
                <h5 class="modal-title fw-bold" style="color:#fff; display:flex; align-items:center; gap:8px;">
                    <span class="material-symbols-outlined" style="font-size:22px;">payments</span>
                    Pay Supplier / Clear Balance
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="posSupplierPayForm" onsubmit="submitPosSupplierPayment(event)">
                    {{-- Supplier Selector --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Select Supplier <span class="text-danger">*</span></label>
                        <select id="posSupplierSelect" class="form-select" style="height:44px; border-radius:10px; font-weight:600;" required onchange="onPosSupplierChange(this)">
                            <option value="">-- Choose Supplier --</option>
                            @if(isset($suppliers))
                                @foreach($suppliers as $s)
                                    <option value="{{ $s['id'] }}" data-balance="{{ $s['pending_balance'] }}" data-name="{{ $s['name'] }}" data-phone="{{ $s['phone'] }}">
                                        {{ $s['name'] }} @if(!empty($s['company_name']))({{ $s['company_name'] }})@endif — Due: PKR {{ number_format($s['pending_balance'], 2) }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Pending Balance Card --}}
                    <div id="posSupplierBalanceCard" class="p-3 mb-3 rounded-3" style="background:#f8fafc; border:1.5px solid #e2e8f0; display:none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold" id="posSupplierCardName">—</div>
                                <div class="text-muted" style="font-size:11px;" id="posSupplierCardPhone">—</div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small fw-semibold">Pending Due:</div>
                                <div class="fs-5 fw-bold text-danger" id="posSupplierCardDue">PKR 0.00</div>
                            </div>
                        </div>
                    </div>

                    {{-- Amount Input --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold small text-secondary mb-0">Payment Amount (PKR) <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none fw-bold" style="color:#0f766e;" onclick="fillPosSupplierFullPayment()">
                                Pay Full Due
                            </button>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold text-muted border-end-0">Rs.</span>
                            <input type="number" step="0.01" min="0.01" id="posSupplierAmount" class="form-control fs-5 fw-bold text-dark border-start-0" placeholder="0.00" required>
                        </div>
                    </div>

                    {{-- Payment Method & Ref --}}
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small text-secondary">Method <span class="text-danger">*</span></label>
                            <select id="posSupplierMethod" class="form-select" style="height:42px; border-radius:10px;">
                                <option value="cash" selected>💵 Cash (Counter Drawer)</option>
                                <option value="bank">🏦 Bank Transfer</option>
                                <option value="cheque">📝 Cheque</option>
                                <option value="online">📱 Card / Online</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-secondary">Cheque/Trx Ref</label>
                            <input type="text" id="posSupplierRef" class="form-control" style="height:42px; border-radius:10px;" placeholder="Optional ref #">
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Notes / Remarks</label>
                        <input type="text" id="posSupplierNotes" class="form-control" style="border-radius:10px;" placeholder="e.g. Paid from register till">
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal" style="border-radius:10px; font-weight:700;">Cancel</button>
                        <button type="submit" class="btn text-white px-4" id="posSupplierSubmitBtn" style="background:#0f766e; border-radius:10px; font-weight:700;">
                            <span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle;">check</span> Clear Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ══════════ RECEIVE CUSTOMER PAYMENT MODAL (POS) ══════════ --}}
<div class="modal fade" id="posEmployeePayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px; border:none; box-shadow:0 20px 60px rgba(0,0,0,0.25); overflow:hidden;">
            <div class="modal-header px-4 py-3" style="background:#15803d; color:#fff;">
                <h5 class="modal-title fw-bold" style="color:#fff; display:flex; align-items:center; gap:8px;">
                    <span class="material-symbols-outlined" style="font-size:22px;">account_balance_wallet</span>
                    Receive Customer Payment / Clear Due
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="posEmployeePayForm" onsubmit="submitPosEmployeePayment(event)">
                    {{-- Customer Selector --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Select Customer <span class="text-danger">*</span></label>
                        <select id="posEmployeeSelect" class="form-select" style="height:44px; border-radius:10px; font-weight:600;" required onchange="onPosEmployeeChange(this)">
                            <option value="">-- Choose Customer --</option>
                            @if(isset($employees))
                                @foreach($employees as $e)
                                    <option value="{{ $e['id'] }}" data-pending="{{ $e['pending_payment'] }}" data-name="{{ $e['name'] }}" data-phone="{{ $e['phone'] }}" data-address="{{ $e['address'] }}">
                                        {{ $e['name'] }} — Due: PKR {{ number_format($e['pending_payment'], 2) }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Pending Balance Card --}}
                    <div id="posEmployeeBalanceCard" class="p-3 mb-3 rounded-3" style="background:#f0fdf4; border:1.5px solid #bbf7d0; display:none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-dark small fw-bold" id="posEmployeeCardName">—</div>
                                <div class="text-muted" style="font-size:11px;" id="posEmployeeCardPhone">—</div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small fw-semibold">Current Pending Due:</div>
                                <div class="fs-5 fw-bold text-danger" id="posEmployeeCardDue">PKR 0.00</div>
                            </div>
                        </div>
                    </div>

                    {{-- Amount Input --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold small text-secondary mb-0">Payment Received (PKR) <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none fw-bold" style="color:#15803d;" onclick="fillPosEmployeeFullPayment()">
                                Clear Full Due
                            </button>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold text-muted border-end-0">Rs.</span>
                            <input type="number" step="0.01" min="0.01" id="posEmployeeAmount" class="form-control fs-5 fw-bold text-dark border-start-0" placeholder="0.00" required>
                        </div>
                    </div>

                    {{-- Payment Method --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Payment Method <span class="text-danger">*</span></label>
                        <select id="posEmployeeMethod" class="form-select" style="height:42px; border-radius:10px;">
                            <option value="cash" selected>💵 Cash (Counter Drawer)</option>
                            <option value="bank">🏦 Bank Transfer</option>
                            <option value="salary_deduction">💼 Salary Deduction</option>
                            <option value="online">📱 Card / Online</option>
                        </select>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Notes / Remarks</label>
                        <input type="text" id="posEmployeeNotes" class="form-control" style="border-radius:10px;" placeholder="e.g. Returned cash at counter">
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal" style="border-radius:10px; font-weight:700;">Cancel</button>
                        <button type="submit" class="btn text-white px-4" id="posEmployeeSubmitBtn" style="background:#15803d; border-radius:10px; font-weight:700;">
                            <span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle;">check_circle</span> Receive & Clear Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ══════════ CUSTOMER PRODUCT RETURN MODAL (POS) ══════════ --}}
<div class="modal fade" id="posCustomerReturnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:18px; border:none; box-shadow:0 20px 60px rgba(0,0,0,0.25); overflow:hidden;">
            <div class="modal-header px-4 py-3" style="background:#b91c1c; color:#fff;">
                <h5 class="modal-title fw-bold" style="color:#fff; display:flex; align-items:center; gap:8px;">
                    <span class="material-symbols-outlined" style="font-size:22px;">assignment_return</span>
                    Customer Product Return & Stock Restoration
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Live Product Search / Barcode Scan --}}
                <div class="position-relative mb-3">
                    <label class="form-label fw-bold small text-secondary">Search & Add Product to Return <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <span class="material-symbols-outlined" style="font-size:20px; color:#b91c1c;">barcode_scanner</span>
                        </span>
                        <input type="text" id="posReturnProductSearchInput" class="form-control form-control-lg fs-6 border-start-0" 
                               placeholder="Scan product barcode, or type product name / SKU..." 
                               autocomplete="off"
                               oninput="onReturnProductSearch(this.value)"
                               onkeydown="onReturnProductSearchKeydown(event)">
                        <button type="button" class="btn btn-outline-secondary" onclick="clearReturnProductSearch()" title="Clear">
                            <span class="material-symbols-outlined" style="font-size:18px;">close</span>
                        </button>
                    </div>

                    {{-- Floating live search dropdown --}}
                    <div id="posReturnProductDropdown" class="search-dropdown" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1060; background:#fff; border:1.5px solid #e2e8f0; border-radius:12px; max-height:260px; overflow-y:auto; box-shadow:0 12px 30px rgba(0,0,0,0.15); margin-top:4px;"></div>
                </div>

                {{-- Form for Return Processing --}}
                <form id="posCustomerReturnForm" onsubmit="submitCustomerReturn(event)">
                    {{-- Return Items Table --}}
                    <div class="table-responsive mb-3" style="max-height:280px; overflow-y:auto; border:1px solid #e5e7eb; border-radius:10px;">
                        <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                            <thead style="background:#f8fafc; position:sticky; top:0; z-index:1;">
                                <tr>
                                    <th style="font-weight:700; color:#4b5563;">Product</th>
                                    <th style="font-weight:700; color:#4b5563; text-align:center;">Current Stock</th>
                                    <th style="font-weight:700; color:#0f766e; text-align:center; width:130px;">Sales Rate (PKR)</th>
                                    <th style="font-weight:700; color:#b91c1c; text-align:center; width:130px;">Return Qty</th>
                                    <th style="font-weight:700; color:#4b5563; text-align:right;">Line Total</th>
                                    <th style="width:45px;"></th>
                                </tr>
                            </thead>
                            <tbody id="posReturnItemsTbody">
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted" id="posReturnEmptyPrompt">
                                        <span class="material-symbols-outlined d-block mb-1" style="font-size:32px; opacity:0.4;">qr_code_scanner</span>
                                        <div class="fw-semibold">No products added yet.</div>
                                        <div style="font-size:11.5px;">Scan a barcode or type a product name in the search box above to add items.</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Customer & Staff Information --}}
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">Customer Name</label>
                            <input type="text" id="posReturnCustomerName" class="form-control" style="height:42px; border-radius:10px;" placeholder="Walk-in Customer" value="Walk-in Customer">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">Customer Phone</label>
                            <input type="text" id="posReturnCustomerPhone" class="form-control" style="height:42px; border-radius:10px;" placeholder="Optional phone #">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">Customer (Optional)</label>
                            <select id="posReturnEmployeeSelect" class="form-select" style="height:42px; border-radius:10px;" onchange="onReturnEmployeeChange(this)">
                                <option value="">-- None (Walk-in) --</option>
                                @if(isset($employees))
                                    @foreach($employees as $e)
                                        <option value="{{ $e['id'] }}">{{ $e['name'] }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    {{-- Refund Method & Reason --}}
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Refund Method <span class="text-danger">*</span></label>
                            <select id="posReturnRefundMethod" class="form-select" style="height:42px; border-radius:10px;" required>
                                <option value="cash" selected>💵 Cash Refund (Counter Drawer)</option>
                                <option value="card">💳 Card / Bank Transfer</option>
                                <option value="credit_adjustment" id="posReturnOptionCreditAdj" style="display:none;">💼 Adjust Credit Balance</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Return Reason</label>
                            <select id="posReturnReason" class="form-select" style="height:42px; border-radius:10px;">
                                <option value="Customer Changed Mind">Customer Changed Mind</option>
                                <option value="Defective / Damaged Item">Defective / Damaged Item</option>
                                <option value="Incorrect Item Purchased">Incorrect Item Purchased</option>
                                <option value="Excess / Leftover Quantity">Excess / Leftover Quantity</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Notes / Remarks</label>
                        <input type="text" id="posReturnNotes" class="form-control" style="border-radius:10px;" placeholder="Optional notes about the return condition...">
                    </div>

                    {{-- Total Refund Callout --}}
                    <div class="p-3 my-3 rounded-3 d-flex justify-content-between align-items-center" style="background:#fff1f2; border:1.5px solid #fecdd3;">
                        <div>
                            <div class="fw-bold text-dark fs-6">Total Refund Amount to Customer</div>
                            <div class="text-muted" style="font-size:11px;">Calculated using the product sales rate. Stock will be restored automatically upon completion.</div>
                        </div>
                        <div class="fs-4 fw-bold" style="color:#be123c;" id="posReturnTotalDisplay">PKR 0.00</div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal" style="border-radius:10px; font-weight:700;">Cancel</button>
                        <button type="submit" class="btn text-white px-4" id="posReturnSubmitBtn" style="background:#b91c1c; border-radius:10px; font-weight:700;">
                            <span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle;">assignment_return</span> Complete Return & Restore Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let cart              = [];
let paymentMethod     = 'cash';
let searchTimeout     = null;
let activeDropdownIdx = -1;
let currentSearchResults = [];
let currentDrawerCat  = '';

// Precise in-memory numeric states
let currentSubtotal   = 0.0;
let currentDiscount   = 0.0;
let currentGrandTotal = 0.0;

// Hold Order Queue State
let heldOrders = [];

const CSRF = document.querySelector('meta[name="csrf-token"]').content;

/* ── DOM References ──────────────────────────── */
const productSearch   = document.getElementById('productSearch');
const searchDropdown  = document.getElementById('searchDropdown');
const searchClearBtn  = document.getElementById('searchClearBtn');
const cartTableBody   = document.getElementById('cartTableBody');
const cartEmptyState  = document.getElementById('cartEmptyState');
const completeSaleBtn = document.getElementById('completeSaleBtn');
const paidInput       = document.getElementById('paidInput');
const discountInput   = document.getElementById('discountInput');

/* ── Initial Load & Hold Queue Sync ───────────── */
window.addEventListener('DOMContentLoaded', () => {
    if (productSearch) productSearch.focus();
    loadDrawerCatalog('');
    loadHeldOrdersFromStorage();

    @if(session('error'))
        toast("{{ session('error') }}", 'e');
    @endif
    @if(session('success'))
        toast("{{ session('success') }}", 's');
    @endif
});

/* ── Number Parsing & Currency Formatting Helpers ── */
function parseCleanNumber(val) {
    if (val === null || val === undefined) return 0;
    const cleanStr = String(val).replace(/,/g, '').trim();
    const num = parseFloat(cleanStr);
    return isNaN(num) ? 0 : num;
}

function formatRs(num) {
    const val = Number(num) || 0;
    return 'Rs. ' + val.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/* ── Hold Order Queue Management ─────────────── */
function loadHeldOrdersFromStorage() {
    try {
        const stored = localStorage.getItem('pos_held_orders');
        heldOrders = stored ? JSON.parse(stored) : [];
    } catch(e) {
        heldOrders = [];
    }
    renderHoldQueueUI();
}

function saveHeldOrdersToStorage() {
    try {
        localStorage.setItem('pos_held_orders', JSON.stringify(heldOrders));
    } catch(e) {}
    renderHoldQueueUI();
}

function holdCurrentOrder() {
    if (!cart.length) {
        toast('Current cart is empty, nothing to hold.', 'w');
        return;
    }

    const custName = document.getElementById('customerName').value.trim() || 'Walk-in Customer';
    const custPhone = document.getElementById('customerPhone').value.trim() || '';
    const now = new Date();
    const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    const heldItem = {
        id: 'HOLD_' + Date.now(),
        ticketNo: '#' + (heldOrders.length + 1),
        customerName: custName,
        customerPhone: custPhone,
        cart: JSON.parse(JSON.stringify(cart)),
        discount: parseCleanNumber(discountInput.value),
        total: currentGrandTotal,
        itemsCount: cart.length,
        unitsCount: cart.reduce((acc, i) => acc + i.qty, 0),
        timestamp: timeStr,
        createdAt: Date.now()
    };

    heldOrders.push(heldItem);
    saveHeldOrdersToStorage();

    // Reset active cart for next customer
    cart = [];
    discountInput.value = '0';
    paidInput.value = '';
    document.getElementById('customerName').value = '';
    document.getElementById('customerPhone').value = '';
    renderCart();

    toast(`Order held as ${heldItem.ticketNo} (${custName})`, 's');
    if (productSearch) productSearch.focus();
}

function resumeHeldOrder(holdId) {
    const idx = heldOrders.findIndex(h => h.id === holdId);
    if (idx === -1) return;

    const held = heldOrders[idx];

    if (cart.length > 0) {
        if (!confirm('Resume held order and replace the current cart? (Current cart will be discarded)')) {
            return;
        }
    }

    cart = JSON.parse(JSON.stringify(held.cart));
    discountInput.value = held.discount || '0';
    document.getElementById('customerName').value = held.customerName !== 'Walk-in Customer' ? held.customerName : '';
    document.getElementById('customerPhone').value = held.customerPhone || '';

    if (held.customerName && held.customerName !== 'Walk-in Customer') {
        if (!custSectionOpen) toggleCustomerSection();
    }

    // Remove from hold queue
    heldOrders.splice(idx, 1);
    saveHeldOrdersToStorage();

    // Close modal if open
    const modalEl = document.getElementById('heldOrdersModal');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) modalInstance.hide();

    renderCart();
    toast(`Resumed held order ${held.ticketNo}`, 's');
    if (productSearch) productSearch.focus();
}

function discardHeldOrder(holdId) {
    if (!confirm('Are you sure you want to discard this held order?')) return;
    heldOrders = heldOrders.filter(h => h.id !== holdId);
    saveHeldOrdersToStorage();
    renderHeldOrdersModalBody();
    toast('Held order discarded.', 's');
}

function renderHoldQueueUI() {
    const queueBar = document.getElementById('holdQueueBar');
    const chipsContainer = document.getElementById('holdChipsContainer');
    const countBadge = document.getElementById('topHoldCountBadge');
    const barCount = document.getElementById('holdBarCount');

    const count = heldOrders.length;
    barCount.textContent = count;

    if (count > 0) {
        queueBar.style.display = 'flex';
        countBadge.style.display = 'inline-block';
        countBadge.textContent = count;

        chipsContainer.innerHTML = heldOrders.map(h => `
            <div class="hold-ticket-chip" title="Held at ${h.timestamp}">
                <div class="hold-ticket-info">
                    <strong>${h.ticketNo}</strong>: ${escapeHtml(h.customerName)}
                    <span style="color:#64748b;">(${h.itemsCount} items • ${formatRs(h.total)})</span>
                    <span style="font-size:11px; color:#94a3b8; margin-left:2px;">🕒 ${h.timestamp}</span>
                </div>
                <button type="button" class="btn-resume-hold" onclick="resumeHeldOrder('${h.id}')" title="Resume into cart">
                    <span class="material-symbols-outlined" style="font-size:14px;">play_arrow</span> Resume
                </button>
                <button type="button" class="btn-discard-hold" onclick="discardHeldOrder('${h.id}')" title="Discard">
                    <span class="material-symbols-outlined" style="font-size:16px;">close</span>
                </button>
            </div>
        `).join('');
    } else {
        queueBar.style.display = 'none';
        countBadge.style.display = 'none';
        chipsContainer.innerHTML = '';
    }
}

function openHeldOrdersModal() {
    renderHeldOrdersModalBody();
    const modal = new bootstrap.Modal(document.getElementById('heldOrdersModal'));
    modal.show();
}

function renderHeldOrdersModalBody() {
    const body = document.getElementById('heldOrdersListBody');
    const modalCount = document.getElementById('modalHoldCount');
    modalCount.textContent = heldOrders.length;

    if (!heldOrders.length) {
        body.innerHTML = `
            <div style="padding:40px 20px; text-align:center; color:#94a3b8;">
                <span class="material-symbols-outlined" style="font-size:48px; color:#cbd5e1; margin-bottom:8px;">pause_circle</span>
                <h5 class="fw-bold" style="color:#475569;">No Orders on Hold</h5>
                <p style="font-size:13px; margin:0;">Orders placed on hold will appear here and on the POS window for instant recall.</p>
            </div>`;
        return;
    }

    body.innerHTML = heldOrders.map(h => `
        <div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:12px; padding:16px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                    <span style="background:#fef3c7; color:#b45309; font-weight:800; font-size:12px; padding:2px 8px; border-radius:6px;">${h.ticketNo}</span>
                    <strong style="font-size:15px; color:#0f172a;">${escapeHtml(h.customerName)}</strong>
                    ${h.customerPhone ? `<span style="font-size:12px; color:#64748b;">(${escapeHtml(h.customerPhone)})</span>` : ''}
                    <span style="font-size:12px; color:#94a3b8;">• Held at ${h.timestamp}</span>
                </div>
                <div style="font-size:13px; color:#475569;">
                    <strong>${h.itemsCount} Item(s)</strong> (${h.unitsCount} units) — Total: <strong style="color:#0f766e;">${formatRs(h.total)}</strong>
                </div>
                <div style="font-size:11.5px; color:#64748b; margin-top:4px;">
                    ${h.cart.map(i => `${escapeHtml(i.name)} × ${i.qty}`).slice(0, 3).join(', ')}
                    ${h.cart.length > 3 ? `... +${h.cart.length - 3} more` : ''}
                </div>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="button" class="btn-resume-hold" onclick="resumeHeldOrder('${h.id}')" style="padding:8px 16px; font-size:13px; border-radius:8px;">
                    <span class="material-symbols-outlined" style="font-size:18px;">play_arrow</span> Resume Order
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="discardHeldOrder('${h.id}')" style="border-radius:8px; padding:6px 12px;">
                    <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                </button>
            </div>
        </div>
    `).join('');
}

/* ── Live Product Search & Autocomplete ──────── */
productSearch.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    const q = productSearch.value.trim();
    searchClearBtn.style.display = q ? 'flex' : 'none';

    if (!q) {
        hideDropdown();
        return;
    }

    searchTimeout = setTimeout(async () => {
        try {
            const url = `{{ route('cashier.pos.search') }}?q=${encodeURIComponent(q)}`;
            const list = await (await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
            renderSearchDropdown(list);
        } catch(err) {
            console.error(err);
        }
    }, 200);
});

productSearch.addEventListener('keydown', async (e) => {
    const items = searchDropdown.querySelectorAll('.search-item');
    if (e.key === 'Escape') {
        hideDropdown();
        return;
    }

    if (e.key === 'ArrowDown' && items.length && searchDropdown.style.display !== 'none') {
        e.preventDefault();
        activeDropdownIdx = (activeDropdownIdx + 1) % items.length;
        updateActiveDropdownItem(items);
    } else if (e.key === 'ArrowUp' && items.length && searchDropdown.style.display !== 'none') {
        e.preventDefault();
        activeDropdownIdx = (activeDropdownIdx - 1 + items.length) % items.length;
        updateActiveDropdownItem(items);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (activeDropdownIdx >= 0 && activeDropdownIdx < currentSearchResults.length) {
            addToCart(currentSearchResults[activeDropdownIdx]);
            hideDropdown();
            clearSearchInput();
        } else if (currentSearchResults.length === 1) {
            addToCart(currentSearchResults[0]);
            hideDropdown();
            clearSearchInput();
        } else if (productSearch.value.trim()) {
            const q = productSearch.value.trim();
            try {
                const url = `{{ route('cashier.pos.search') }}?q=${encodeURIComponent(q)}`;
                const list = await (await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
                if (list && list.length > 0) {
                    addToCart(list[0]);
                    hideDropdown();
                    clearSearchInput();
                } else {
                    toast(`No product found for "${q}"`, 'e');
                }
            } catch(err) {
                console.error(err);
            }
        }
    }
});

function updateActiveDropdownItem(items) {
    items.forEach((it, idx) => {
        it.classList.toggle('active', idx === activeDropdownIdx);
        if (idx === activeDropdownIdx) {
            it.scrollIntoView({ block: 'nearest' });
        }
    });
}

function renderSearchDropdown(list) {
    currentSearchResults = list || [];
    activeDropdownIdx = -1;

    if (!list.length) {
        searchDropdown.innerHTML = `
            <div style="padding:16px; text-align:center; color:#94a3b8; font-size:13px;">
                No matching products found.
            </div>`;
        searchDropdown.style.display = 'block';
        return;
    }

    searchDropdown.innerHTML = list.map((p, idx) => {
        const isOos = p.stock_quantity <= 0;
        return `
        <div class="search-item ${idx === 0 ? 'active' : ''}" data-idx="${idx}" onclick="selectSearchDropdownItem(${idx})">
            <div class="search-item-thumb" style="background-image:url('${p.image_url}');"></div>
            <div class="search-item-info">
                <div class="search-item-name">${escapeHtml(p.name)}</div>
                <div class="search-item-meta">
                    <span>SKU: ${p.sku || '—'}</span>
                    <span>•</span>
                    <span style="color:${isOos ? '#ef4444' : '#059669'}; font-weight:700;">
                        ${isOos ? 'Out of Stock' : 'Stock: ' + p.stock_quantity + (p.unit ? ' ' + p.unit : '')}
                    </span>
                    ${p.category ? `<span>•</span><span>${escapeHtml(p.category)}</span>` : ''}
                </div>
            </div>
            <div class="search-item-price">
                ${formatRs(p.sale_price)}
            </div>
        </div>`;
    }).join('');

    activeDropdownIdx = 0;
    searchDropdown.style.display = 'block';
}

function selectSearchDropdownItem(idx) {
    if (idx >= 0 && idx < currentSearchResults.length) {
        addToCart(currentSearchResults[idx]);
        hideDropdown();
        clearSearchInput();
    }
}

function hideDropdown() {
    searchDropdown.style.display = 'none';
    currentSearchResults = [];
    activeDropdownIdx = -1;
}

function clearSearchInput() {
    productSearch.value = '';
    searchClearBtn.style.display = 'none';
    hideDropdown();
    productSearch.focus();
}

document.addEventListener('click', (e) => {
    if (!e.target.closest('.search-box-wrap')) {
        hideDropdown();
    }
});

function focusSearch() {
    productSearch.focus();
    productSearch.select();
}

/* ── Offcanvas Catalog ───────────────────────── */
async function loadDrawerCatalog(catId) {
    const grid = document.getElementById('catalogDrawerGrid');
    grid.innerHTML = '<div style="grid-column:1/-1; padding:30px; text-align:center; color:#94a3b8;">Loading catalog items...</div>';
    try {
        let url = `{{ route('cashier.pos.search') }}?q= `;
        if (catId) url += `&category_id=${catId}`;
        const products = await (await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
        renderDrawerCatalog(products);
    } catch(err) {
        grid.innerHTML = '<div style="grid-column:1/-1; padding:30px; text-align:center; color:#ef4444;">Failed to load catalog.</div>';
    }
}

function selectDrawerCategory(btn, catId) {
    document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    currentDrawerCat = catId;
    loadDrawerCatalog(catId);
}

function renderDrawerCatalog(list) {
    const grid = document.getElementById('catalogDrawerGrid');
    if (!list.length) {
        grid.innerHTML = '<div style="grid-column:1/-1; padding:30px; text-align:center; color:#94a3b8;">No products in this category.</div>';
        return;
    }
    grid.innerHTML = list.map(p => `
        <div class="cat-product-card" onclick="addToCartFromDrawer(${JSON.stringify(p).replace(/"/g, '&quot;')})">
            <div class="cat-prod-img" style="background-image:url('${p.image_url}');"></div>
            <div class="cat-prod-name">${escapeHtml(p.name)}</div>
            <div class="cat-prod-price">Rs. ${parseFloat(p.sale_price).toFixed(0)}</div>
        </div>
    `).join('');
}

function addToCartFromDrawer(p) {
    addToCart(p);
}

/* ── Cart Operations ─────────────────────────── */
function addToCart(p) {
    const existing = cart.find(item => item.id === p.id);
    if (existing) {
        if (existing.qty >= p.stock_quantity) {
            toast('Maximum available stock reached for this item.', 'w');
            return;
        }
        existing.qty++;
    } else {
        if (p.stock_quantity <= 0) {
            toast('Product is out of stock.', 'e');
            return;
        }
        cart.push({ ...p, qty: 1 });
    }
    renderCart();
    toast(`Added "${p.name}" to order`, 's');
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    renderCart();
}

function updateQty(id, delta) {
    const item = cart.find(i => i.id === id);
    if (!item) return;
    const newQty = item.qty + delta;
    if (newQty <= 0) {
        removeFromCart(id);
        return;
    }
    if (newQty > item.stock_quantity) {
        toast('Maximum available stock reached.', 'w');
        return;
    }
    item.qty = newQty;
    renderCart();
}

function setQty(id, val) {
    const item = cart.find(i => i.id === id);
    if (!item) return;
    let qty = parseInt(val) || 1;
    if (qty <= 0) {
        removeFromCart(id);
        return;
    }
    if (qty > item.stock_quantity) {
        toast('Exceeds available stock. Set to max available.', 'w');
        qty = item.stock_quantity;
    }
    item.qty = qty;
    renderCart();
}

function clearCart() {
    if (!cart.length) return;
    if (!confirm('Are you sure you want to clear the entire order?')) return;
    cart = [];
    discountInput.value = '0';
    paidInput.value = '';
    renderCart();
}

function renderCart() {
    if (!cart.length) {
        cartTableBody.innerHTML = '';
        cartEmptyState.style.display = 'flex';
        completeSaleBtn.disabled = true;
        document.getElementById('metricItemsCount').textContent = '0 Items';
        document.getElementById('metricUnitsCount').textContent = '0 Units';
        updateTotals();
        return;
    }

    cartEmptyState.style.display = 'none';
    completeSaleBtn.disabled = false;

    const totalUnits = cart.reduce((acc, i) => acc + i.qty, 0);
    document.getElementById('metricItemsCount').textContent = `${cart.length} Item${cart.length > 1 ? 's' : ''}`;
    document.getElementById('metricUnitsCount').textContent = `${totalUnits} Unit${totalUnits > 1 ? 's' : ''}`;

    cartTableBody.innerHTML = cart.map((item, index) => {
        const lineTotal = parseCleanNumber(item.sale_price) * item.qty;
        const isLow = item.stock_quantity <= (item.low_stock_threshold || 5);
        return `
        <tr>
            <td class="td-num">${index + 1}</td>
            <td>
                <div class="prod-cell">
                    <div class="prod-thumb" style="background-image:url('${item.image_url}');"></div>
                    <div class="prod-info-block">
                        <div class="prod-title">${escapeHtml(item.name)}</div>
                        <div class="prod-submeta">
                            ${item.category ? `<span class="cat-chip">${escapeHtml(item.category)}</span>` : ''}
                            <span class="stock-hint ${isLow ? 'low' : ''}">
                                Stock: ${item.stock_quantity} ${item.unit || 'pcs'}
                            </span>
                        </div>
                    </div>
                </div>
            </td>
            <td style="color:#64748b; font-size:13px; font-weight:600;">
                <div>${item.sku || '—'}</div>
                <div style="font-size:11.5px; opacity:0.8;">${item.unit || 'Piece'}</div>
            </td>
            <td class="td-price">
                ${formatRs(item.sale_price)}
            </td>
            <td style="text-align:center;">
                <div class="qty-box">
                    <button type="button" class="qty-btn" onclick="updateQty(${item.id}, -1)">−</button>
                    <input type="number" class="qty-field" value="${item.qty}" min="1" max="${item.stock_quantity}"
                           onchange="setQty(${item.id}, this.value)"
                           onclick="this.select()">
                    <button type="button" class="qty-btn" onclick="updateQty(${item.id}, 1)">+</button>
                </div>
            </td>
            <td class="td-total" style="text-align:right;">
                ${formatRs(lineTotal)}
            </td>
            <td class="td-action">
                <button type="button" class="btn-del-item" onclick="removeFromCart(${item.id})" title="Remove Item">
                    <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                </button>
            </td>
        </tr>`;
    }).join('');

    updateTotals();
}

/* ── Financial Calculations ─────────────────── */
function updateTotals() {
    currentSubtotal = cart.reduce((sum, item) => sum + (parseCleanNumber(item.sale_price) * item.qty), 0.0);
    const enteredDisc = parseCleanNumber(discountInput.value);
    currentDiscount = Math.min(enteredDisc, currentSubtotal);
    currentGrandTotal = Math.max(0.0, currentSubtotal - currentDiscount);

    document.getElementById('cartSubtotal').textContent = formatRs(currentSubtotal);
    document.getElementById('taxDisplay').textContent   = 'Rs. 0.00';
    document.getElementById('cartTotal').textContent    = formatRs(currentGrandTotal);

    if (paymentMethod === 'card') {
        paidInput.value = currentGrandTotal.toFixed(2);
    }
    if (paymentMethod === 'credit') {
        onEmployeeSelect();
    }
    updateChange();
}

function updateChange() {
    const paid = parseCleanNumber(paidInput.value);
    const change = paid - currentGrandTotal;
    const changeEl = document.getElementById('changeAmount');
    const changeBox = document.getElementById('changeDisplay');

    if (change >= 0) {
        changeEl.textContent = formatRs(change);
        changeBox.classList.remove('neg');
    } else {
        changeEl.textContent = 'Due: ' + formatRs(Math.abs(change));
        changeBox.classList.add('neg');
    }
}

/* ── Cash Preset Helpers ─────────────────────── */
function setExactCash() {
    paidInput.value = currentGrandTotal.toFixed(2);
    updateChange();
}

function addCashPreset(amount) {
    const current = parseCleanNumber(paidInput.value);
    paidInput.value = (current + amount).toFixed(2);
    updateChange();
}

function setCashRound(step) {
    if (currentGrandTotal <= 0) {
        paidInput.value = '0.00';
    } else {
        const rounded = Math.ceil(currentGrandTotal / step) * step;
        paidInput.value = rounded.toFixed(2);
    }
    updateChange();
}

function clearCashPaid() {
    paidInput.value = '';
    updateChange();
}

/* ── Payment Method Switcher ─────────────────── */
function selectPayment(method) {
    paymentMethod = method;
    document.querySelectorAll('.pay-tab-btn').forEach(btn => btn.classList.toggle('active', btn.dataset.method === method));

    const cashControls   = document.getElementById('cashControls');
    const creditControls = document.getElementById('creditControls');
    const cardControls   = document.getElementById('cardControls');

    cashControls.style.display   = method === 'cash' ? 'block' : 'none';
    creditControls.style.display = method === 'credit' ? 'block' : 'none';
    cardControls.style.display   = method === 'card' ? 'block' : 'none';

    if (method === 'credit') {
        paidInput.value = '0.00';
        onEmployeeSelect();
    } else if (method === 'card') {
        paidInput.value = currentGrandTotal.toFixed(2);
    }
    updateChange();
}

function onEmployeeSelect() {
    const sel = document.getElementById('employeeSelect');
    const opt = sel ? sel.selectedOptions[0] : null;
    const card = document.getElementById('employeeDetailsCard');

    if (!opt || !opt.value) {
        if (card) card.style.display = 'none';
        return;
    }

    const name      = opt.dataset.name || '';
    const phone     = opt.dataset.phone || '';
    const address   = opt.dataset.address || '';
    const pending   = parseCleanNumber(opt.dataset.pending);
    const newTotal  = pending + currentGrandTotal;

    document.getElementById('cardEmpName').textContent    = name;
    document.getElementById('cardEmpPhone').textContent   = phone ? '📞 ' + phone : '';
    document.getElementById('cardEmpAddress').textContent = address ? '📍 ' + address : '';
    document.getElementById('cardEmpPending').textContent = formatRs(pending);
    document.getElementById('cardEmpNewTotal').textContent = formatRs(newTotal);
    card.style.display = 'flex';
}

/* ── Customer Section Toggle ─────────────────── */
let custSectionOpen = false;
function toggleCustomerSection() {
    custSectionOpen = !custSectionOpen;
    document.getElementById('customerSectionBox').style.display = custSectionOpen ? 'block' : 'none';
    document.getElementById('topCustomerBtn').classList.toggle('primary', custSectionOpen);
}

/* ── Sale Completion ─────────────────────────── */
async function completeSale() {
    if (!cart.length) return;

    const paid     = parseCleanNumber(paidInput.value);
    const discount = parseCleanNumber(discountInput.value);

    if (paymentMethod === 'cash' && paid < currentGrandTotal) {
        toast('Cash received is less than the Grand Total!', 'e');
        paidInput.focus();
        return;
    }

    if (paymentMethod === 'credit') {
        const empSelect = document.getElementById('employeeSelect');
        if (!empSelect || !empSelect.value) {
            toast('Please select an authorized customer for credit sale!', 'w');
            empSelect.focus();
            return;
        }
    }

    const btn = document.getElementById('completeSaleBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing Sale...';

    try {
        const res = await fetch('{{ route('cashier.pos.complete-sale') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                cart: cart.map(i => ({
                    product_id: i.id,
                    quantity: i.qty,
                    unit_price: parseCleanNumber(i.sale_price),
                    discount_amount: 0
                })),
                payment_method: paymentMethod,
                paid_amount:    paymentMethod === 'card' ? currentGrandTotal : (paymentMethod === 'credit' ? 0 : paid),
                discount_amount: discount,
                employee_id:    paymentMethod === 'credit' ? parseInt(document.getElementById('employeeSelect').value) : null,
                customer_name:  document.getElementById('customerName').value.trim() || 'Walk-in Customer',
                customer_phone: document.getElementById('customerPhone').value.trim() || null,
            }),
        });

        const data = await res.json();
        if (data.success) {
            showReceipt(data);
        } else {
            toast(data.message || 'Sale failed.', 'e');
            btn.disabled = false;
            btn.innerHTML = '<span id="completeSaleLabel">Complete Sale</span><span class="material-symbols-outlined si">check_circle</span><span style="font-size:12px; opacity:0.7; margin-left:4px;">[F9]</span>';
        }
    } catch(err) {
        toast('Network or server error processing sale.', 'e');
        btn.disabled = false;
        btn.innerHTML = '<span id="completeSaleLabel">Complete Sale</span><span class="material-symbols-outlined si">check_circle</span><span style="font-size:12px; opacity:0.7; margin-left:4px;">[F9]</span>';
    }
}

/* ── Receipt Modal Handling ──────────────────── */
const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));

function showReceipt(data) {
    document.getElementById('receiptInvoice').textContent = data.invoice_number;
    document.getElementById('receiptTotal').textContent   = 'Rs. ' + data.total_amount;
    document.getElementById('receiptPaid').textContent    = 'Rs. ' + data.paid_amount;
    document.getElementById('receiptChange').textContent  = 'Rs. ' + data.change_amount;
    document.getElementById('receiptItems').textContent   = data.items_count + ' item(s) in this transaction';
    document.getElementById('printReceiptBtn').href       = data.receipt_url + '?print=1';
    receiptModal.show();
}

function closeReceipt() {
    receiptModal.hide();
    cart = [];
    paymentMethod = 'cash';
    discountInput.value = '0';
    paidInput.value     = '';
    document.getElementById('customerName').value  = '';
    document.getElementById('customerPhone').value = '';

    const empSel = document.getElementById('employeeSelect');
    if (empSel) empSel.value = '';
    const empCard = document.getElementById('employeeDetailsCard');
    if (empCard) empCard.style.display = 'none';

    if (custSectionOpen) toggleCustomerSection();
    selectPayment('cash');
    renderCart();

    completeSaleBtn.disabled = false;
    completeSaleBtn.innerHTML = '<span id="completeSaleLabel">Complete Sale</span><span class="material-symbols-outlined si">check_circle</span><span style="font-size:12px; opacity:0.7; margin-left:4px;">[F9]</span>';

    clearSearchInput();
    if (productSearch) productSearch.focus();
}

/* ── Supplier Payment (POS Counter) ──────────── */
const posSupplierModal = new bootstrap.Modal(document.getElementById('posSupplierPayModal'));
let posSelectedSupplierDue = 0;

function openSupplierPayModal() {
    posSupplierModal.show();
    setTimeout(() => {
        const sel = document.getElementById('posSupplierSelect');
        if (sel) sel.focus();
    }, 200);
}

function onPosSupplierChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const card = document.getElementById('posSupplierBalanceCard');
    const amountInput = document.getElementById('posSupplierAmount');

    if (!opt || !opt.value) {
        if (card) card.style.display = 'none';
        posSelectedSupplierDue = 0;
        return;
    }

    const name = opt.dataset.name || '';
    const phone = opt.dataset.phone || '';
    const balance = parseFloat(opt.dataset.balance) || 0;
    posSelectedSupplierDue = balance;

    document.getElementById('posSupplierCardName').textContent = name;
    document.getElementById('posSupplierCardPhone').textContent = phone ? '📞 ' + phone : '';
    document.getElementById('posSupplierCardDue').textContent = 'PKR ' + balance.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    if (card) card.style.display = 'block';

    if (balance > 0) {
        amountInput.value = balance.toFixed(2);
    }
}

function fillPosSupplierFullPayment() {
    if (posSelectedSupplierDue > 0) {
        document.getElementById('posSupplierAmount').value = posSelectedSupplierDue.toFixed(2);
    }
}

async function submitPosSupplierPayment(e) {
    e.preventDefault();

    const supplierId = parseInt(document.getElementById('posSupplierSelect').value);
    const amount = parseFloat(document.getElementById('posSupplierAmount').value);
    const method = document.getElementById('posSupplierMethod').value;
    const ref = document.getElementById('posSupplierRef').value.trim();
    const notes = document.getElementById('posSupplierNotes').value.trim();

    if (!supplierId) {
        toast('Please select a supplier!', 'w');
        return;
    }
    if (isNaN(amount) || amount <= 0) {
        toast('Please enter a valid payment amount!', 'w');
        return;
    }

    const submitBtn = document.getElementById('posSupplierSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Clearing...';

    try {
        const res = await fetch('{{ route('cashier.pos.supplier-payments') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                supplier_id: supplierId,
                amount: amount,
                payment_method: method,
                reference_number: ref || null,
                notes: notes || null
            })
        });

        const data = await res.json();
        if (data.success) {
            toast(data.message, 's');
            posSupplierModal.hide();

            // Update supplier option dataset & text
            const selectEl = document.getElementById('posSupplierSelect');
            const opt = selectEl.querySelector(`option[value="${supplierId}"]`);
            if (opt) {
                opt.dataset.balance = data.raw_new_balance;
                opt.textContent = `${data.supplier_name} — Due: PKR ${data.new_balance}`;
            }

            // Reset form
            document.getElementById('posSupplierPayForm').reset();
            document.getElementById('posSupplierBalanceCard').style.display = 'none';
            posSelectedSupplierDue = 0;
        } else {
            toast(data.message || 'Payment submission failed.', 'e');
        }
    } catch (err) {
        toast('Network or server error recording payment.', 'e');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle;">check</span> Clear Payment';
    }
}

/* ── Employee Payment (POS Counter) ──────────── */
const posEmployeeModal = new bootstrap.Modal(document.getElementById('posEmployeePayModal'));
let posSelectedEmployeeDue = 0;

function openEmployeePayModal(preSelectedEmployeeId = null) {
    posEmployeeModal.show();
    setTimeout(() => {
        const sel = document.getElementById('posEmployeeSelect');
        if (sel) {
            if (preSelectedEmployeeId) {
                sel.value = preSelectedEmployeeId;
                onPosEmployeeChange(sel);
            }
            sel.focus();
        }
    }, 200);
}

function openEmployeePayModalFromCard() {
    const empSelect = document.getElementById('employeeSelect');
    const empId = empSelect ? empSelect.value : null;
    openEmployeePayModal(empId);
}

function onPosEmployeeChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const card = document.getElementById('posEmployeeBalanceCard');
    const amountInput = document.getElementById('posEmployeeAmount');

    if (!opt || !opt.value) {
        if (card) card.style.display = 'none';
        posSelectedEmployeeDue = 0;
        return;
    }

    const name = opt.dataset.name || '';
    const phone = opt.dataset.phone || '';
    const pending = parseFloat(opt.dataset.pending) || 0;
    posSelectedEmployeeDue = pending;

    document.getElementById('posEmployeeCardName').textContent = name;
    document.getElementById('posEmployeeCardPhone').textContent = phone ? '📞 ' + phone : '';
    document.getElementById('posEmployeeCardDue').textContent = 'PKR ' + pending.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    if (card) card.style.display = 'block';

    if (pending > 0) {
        amountInput.value = pending.toFixed(2);
    }
}

function fillPosEmployeeFullPayment() {
    if (posSelectedEmployeeDue > 0) {
        document.getElementById('posEmployeeAmount').value = posSelectedEmployeeDue.toFixed(2);
    }
}

async function submitPosEmployeePayment(e) {
    e.preventDefault();

    const employeeId = parseInt(document.getElementById('posEmployeeSelect').value);
    const amount = parseFloat(document.getElementById('posEmployeeAmount').value);
    const method = document.getElementById('posEmployeeMethod').value;
    const notes = document.getElementById('posEmployeeNotes').value.trim();

    if (!employeeId) {
        toast('Please select a customer!', 'w');
        return;
    }
    if (isNaN(amount) || amount <= 0) {
        toast('Please enter a valid payment amount!', 'w');
        return;
    }

    const submitBtn = document.getElementById('posEmployeeSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Clearing...';

    try {
        const res = await fetch('{{ route('cashier.pos.employee-payments') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                employee_id: employeeId,
                amount: amount,
                payment_method: method,
                notes: notes || null
            })
        });

        const data = await res.json();
        if (data.success) {
            toast(data.message, 's');
            posEmployeeModal.hide();

            // Update employee option dataset & text in POS employee modal dropdown
            const modalSelect = document.getElementById('posEmployeeSelect');
            const modalOpt = modalSelect ? modalSelect.querySelector(`option[value="${employeeId}"]`) : null;
            if (modalOpt) {
                modalOpt.dataset.pending = data.raw_new_balance;
                modalOpt.textContent = `${data.employee_name} — Due: PKR ${data.new_balance}`;
            }

            // Update checkout employee select dropdown as well
            const checkoutSelect = document.getElementById('employeeSelect');
            const checkoutOpt = checkoutSelect ? checkoutSelect.querySelector(`option[value="${employeeId}"]`) : null;
            if (checkoutOpt) {
                checkoutOpt.dataset.pending = data.raw_new_balance;
                checkoutOpt.textContent = `${data.employee_name} (Due: Rs. ${data.new_balance})`;
            }

            // If currently selected in checkout panel, trigger onEmployeeSelect to refresh balance card
            if (checkoutSelect && checkoutSelect.value == employeeId) {
                onEmployeeSelect();
            }

            // Reset modal form
            document.getElementById('posEmployeePayForm').reset();
            document.getElementById('posEmployeeBalanceCard').style.display = 'none';
            posSelectedEmployeeDue = 0;

            // Offer to view / print clearance receipt
            if (data.receipt_url) {
                setTimeout(() => {
                    if (confirm(`Payment of PKR ${data.amount_paid} recorded for ${data.employee_name}. Would you like to print the Clearance Voucher?`)) {
                        window.open(data.receipt_url + '?print=1', '_blank', 'width=420,height=600');
                    }
                }, 300);
            }
        } else {
            toast(data.message || 'Payment clearance failed.', 'e');
        }
    } catch (err) {
        toast('Network or server error recording payment.', 'e');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle;">check_circle</span> Receive & Clear Payment';
    }
}

/* ── Customer Product Return (POS Counter) ───── */
const posCustomerReturnModal = new bootstrap.Modal(document.getElementById('posCustomerReturnModal'));
let returnItemsList = [];
let returnSearchTimeout = null;
let currentReturnSearchResults = [];

function openCustomerReturnModal() {
    posCustomerReturnModal.show();
    setTimeout(() => {
        const input = document.getElementById('posReturnProductSearchInput');
        if (input) {
            input.focus();
        }
    }, 200);
}

function clearReturnProductSearch() {
    const input = document.getElementById('posReturnProductSearchInput');
    if (input) input.value = '';
    const dropdown = document.getElementById('posReturnProductDropdown');
    if (dropdown) {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
    }
}

function onReturnProductSearch(query) {
    clearTimeout(returnSearchTimeout);
    query = query.trim();

    if (!query) {
        clearReturnProductSearch();
        return;
    }

    returnSearchTimeout = setTimeout(async () => {
        try {
            const res = await fetch(`{{ route('cashier.pos.search') }}?q=${encodeURIComponent(query)}`);
            const products = await res.json();
            currentReturnSearchResults = products;
            renderReturnSearchDropdown(products, query);
        } catch (err) {
            console.error(err);
        }
    }, 180);
}

function renderReturnSearchDropdown(products, query) {
    const dropdown = document.getElementById('posReturnProductDropdown');
    if (!products.length) {
        dropdown.innerHTML = `<div class="p-3 text-center text-muted small">No active products found matching "<strong>${escapeHtml(query)}</strong>"</div>`;
        dropdown.style.display = 'block';
        return;
    }

    // If exact barcode match, auto-add immediately
    const exactBarcode = products.find(p => p.barcode && p.barcode.toLowerCase() === query.toLowerCase());
    if (exactBarcode) {
        addProductToReturn(exactBarcode);
        clearReturnProductSearch();
        return;
    }

    let html = '';
    products.forEach((p) => {
        html += `
            <div class="p-2 border-bottom d-flex justify-content-between align-items-center" 
                 style="cursor:pointer; transition:background .15s;" 
                 onmouseover="this.style.background='#fef2f2'" 
                 onmouseout="this.style.background='#fff'"
                 onclick="addProductToReturn(${JSON.stringify(p).replace(/"/g, '&quot;')}); clearReturnProductSearch();">
                <div>
                    <div class="fw-bold text-dark fs-6">${escapeHtml(p.name)}</div>
                    <div class="text-muted small" style="font-family:monospace;">${escapeHtml(p.sku || '')} ${p.barcode ? '· 🏷️ ' + escapeHtml(p.barcode) : ''} · Stock: ${p.stock_quantity} ${escapeHtml(p.unit || 'pcs')}</div>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-success fs-6">PKR ${p.sale_price.toLocaleString(undefined, {minimumFractionDigits: 2})}</div>
                    <span class="badge bg-danger">Click to Return</span>
                </div>
            </div>
        `;
    });

    dropdown.innerHTML = html;
    dropdown.style.display = 'block';
}

function onReturnProductSearchKeydown(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const input = document.getElementById('posReturnProductSearchInput');
        const query = input.value.trim();
        if (query && currentReturnSearchResults.length > 0) {
            addProductToReturn(currentReturnSearchResults[0]);
            clearReturnProductSearch();
        }
    } else if (e.key === 'Escape') {
        clearReturnProductSearch();
    }
}

function addProductToReturn(product) {
    const existing = returnItemsList.find(item => item.product_id === product.id);
    if (existing) {
        existing.quantity += 1;
    } else {
        returnItemsList.push({
            product_id: product.id,
            product_name: product.name,
            product_sku: product.sku || '',
            product_unit: product.unit || 'pcs',
            current_stock: product.stock_quantity,
            unit_price: parseFloat(product.sale_price) || 0,
            quantity: 1
        });
    }
    renderReturnItemsTable();
}

function renderReturnItemsTable() {
    const tbody = document.getElementById('posReturnItemsTbody');
    if (returnItemsList.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-muted" id="posReturnEmptyPrompt">
                    <span class="material-symbols-outlined d-block mb-1" style="font-size:32px; opacity:0.4;">qr_code_scanner</span>
                    <div class="fw-semibold">No products added yet.</div>
                    <div style="font-size:11.5px;">Scan a barcode or type a product name in the search box above to add items.</div>
                </td>
            </tr>
        `;
        updateReturnGrandTotal();
        return;
    }

    let html = '';
    returnItemsList.forEach((item, idx) => {
        const lineTotal = item.quantity * item.unit_price;
        html += `
            <tr>
                <td>
                    <div class="fw-bold text-dark">${escapeHtml(item.product_name)}</div>
                    <div class="text-muted" style="font-size:11px; font-family:monospace;">${escapeHtml(item.product_sku)}</div>
                </td>
                <td style="text-align:center; font-weight:600; color:#64748b;">
                    ${item.current_stock} ${escapeHtml(item.product_unit)}
                </td>
                <td style="text-align:center;">
                    <div class="input-group input-group-sm" style="width:120px; margin:0 auto;">
                        <span class="input-group-text bg-white border-end-0 text-muted" style="font-size:11px;">Rs.</span>
                        <input type="number" step="0.01" min="0" value="${item.unit_price.toFixed(2)}"
                               class="form-control text-end fw-bold border-start-0" 
                               style="color:#0f766e; font-size:13px;"
                               onchange="onReturnItemRateChange(${idx}, this.value)">
                    </div>
                </td>
                <td style="text-align:center;">
                    <div class="input-group input-group-sm" style="width:110px; margin:0 auto;">
                        <button type="button" class="btn btn-outline-secondary px-2" onclick="adjustReturnItemQty(${idx}, -1)">−</button>
                        <input type="number" min="1" value="${item.quantity}" 
                               class="form-control text-center fw-bold" 
                               onchange="onReturnItemQtyChange(${idx}, this.value)">
                        <button type="button" class="btn btn-outline-secondary px-2" onclick="adjustReturnItemQty(${idx}, 1)">+</button>
                    </div>
                </td>
                <td style="text-align:right; font-weight:800; color:#b91c1c;">
                    PKR ${lineTotal.toFixed(2)}
                </td>
                <td style="text-align:center;">
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeReturnItem(${idx})" title="Remove">
                        <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    updateReturnGrandTotal();
}

function adjustReturnItemQty(idx, change) {
    if (!returnItemsList[idx]) return;
    returnItemsList[idx].quantity += change;
    if (returnItemsList[idx].quantity < 1) returnItemsList[idx].quantity = 1;
    renderReturnItemsTable();
}

function onReturnItemQtyChange(idx, val) {
    if (!returnItemsList[idx]) return;
    let qty = parseInt(val) || 1;
    if (qty < 1) qty = 1;
    returnItemsList[idx].quantity = qty;
    renderReturnItemsTable();
}

function onReturnItemRateChange(idx, val) {
    if (!returnItemsList[idx]) return;
    let rate = parseFloat(val);
    if (isNaN(rate) || rate < 0) rate = 0;
    returnItemsList[idx].unit_price = rate;
    renderReturnItemsTable();
}

function removeReturnItem(idx) {
    returnItemsList.splice(idx, 1);
    renderReturnItemsTable();
}

function updateReturnGrandTotal() {
    let grandTotal = 0;
    returnItemsList.forEach(item => {
        grandTotal += (item.quantity * item.unit_price);
    });
    document.getElementById('posReturnTotalDisplay').textContent = 'PKR ' + grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function onReturnEmployeeChange(sel) {
    const creditOption = document.getElementById('posReturnOptionCreditAdj');
    if (sel.value) {
        creditOption.style.display = 'block';
        creditOption.selected = true;
    } else {
        creditOption.style.display = 'none';
        document.getElementById('posReturnRefundMethod').value = 'cash';
    }
}

async function submitCustomerReturn(e) {
    e.preventDefault();

    if (returnItemsList.length === 0) {
        toast('Please add at least one product to return!', 'w');
        return;
    }

    const submitBtn = document.getElementById('posReturnSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing Return...';

    const empId = document.getElementById('posReturnEmployeeSelect').value;

    try {
        const res = await fetch('{{ route('cashier.pos.process-return') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                customer_name: document.getElementById('posReturnCustomerName').value.trim() || 'Walk-in Customer',
                customer_phone: document.getElementById('posReturnCustomerPhone').value.trim() || null,
                employee_id: empId ? parseInt(empId) : null,
                refund_method: document.getElementById('posReturnRefundMethod').value,
                reason: document.getElementById('posReturnReason').value,
                notes: document.getElementById('posReturnNotes').value.trim() || null,
                items: returnItemsList.map(item => ({
                    product_id: item.product_id,
                    quantity: item.quantity,
                    unit_price: item.unit_price
                }))
            })
        });

        const data = await res.json();
        if (data.success) {
            toast(data.message, 's');
            posCustomerReturnModal.hide();

            // Reset state
            returnItemsList = [];
            renderReturnItemsTable();
            document.getElementById('posCustomerReturnForm').reset();
            document.getElementById('posReturnCustomerName').value = 'Walk-in Customer';
            clearReturnProductSearch();

            // Prompt to print return voucher
            if (data.receipt_url) {
                setTimeout(() => {
                    if (confirm(`Return ${data.return_number} processed successfully! Would you like to print the Return Voucher?`)) {
                        window.open(data.receipt_url + '?print=1', '_blank', 'width=420,height=600');
                    }
                }, 300);
            }
        } else {
            toast(data.message || 'Error processing return.', 'e');
        }
    } catch (err) {
        toast('Network or server error processing return.', 'e');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle;">assignment_return</span> Complete Return & Restore Stock';
    }
}

/* ── Keyboard Shortcuts ──────────────────────── */
document.addEventListener('keydown', (e) => {
    // F2 -> Focus Product Search
    if (e.key === 'F2') {
        e.preventDefault();
        focusSearch();
    }
    // F3 -> Toggle Catalog Drawer
    else if (e.key === 'F3') {
        e.preventDefault();
        const offcanvasEl = document.getElementById('catalogOffcanvas');
        const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
        bsOffcanvas.toggle();
    }
    // F4 -> Focus Discount Input
    else if (e.key === 'F4') {
        e.preventDefault();
        discountInput.focus();
        discountInput.select();
    }
    // F6 -> Focus Cash Received
    else if (e.key === 'F6') {
        e.preventDefault();
        if (paymentMethod === 'cash') {
            paidInput.focus();
            paidInput.select();
        }
    }
    // F7 -> Hold Current Order / Open Hold Queue
    else if (e.key === 'F7') {
        e.preventDefault();
        if (cart.length > 0) {
            holdCurrentOrder();
        } else if (heldOrders.length > 0) {
            openHeldOrdersModal();
        }
    }
    // F8 -> Open Pay Supplier Modal
    else if (e.key === 'F8') {
        e.preventDefault();
        openSupplierPayModal();
    }
    // F9 -> Complete Sale
    else if (e.key === 'F9') {
        e.preventDefault();
        if (!completeSaleBtn.disabled) {
            completeSale();
        }
    }
    // F10 -> Open Receive Employee Payment Modal
    else if (e.key === 'F10') {
        e.preventDefault();
        openEmployeePayModal();
    }
    // F11 -> Open Customer Product Return Modal
    else if (e.key === 'F11') {
        e.preventDefault();
        openCustomerReturnModal();
    }
});

/* ── Toast Notifications ─────────────────────── */
function toast(msg, type = 's') {
    const colors = { s: '#059669', e: '#dc2626', w: '#d97706' };
    const icons  = { s: 'check_circle', e: 'cancel', w: 'warning' };
    const el = document.createElement('div');
    el.style.cssText = `
        position:fixed; bottom:24px; right:24px; z-index:99999; background:#ffffff; border-radius:12px;
        padding:12px 18px; box-shadow:0 10px 30px rgba(0,0,0,0.18); display:flex; align-items:center; gap:10px;
        border-left:5px solid ${colors[type]}; font-size:13.5px; font-weight:600; color:#1e293b; max-width:340px;
    `;
    el.innerHTML = `<span class="material-symbols-outlined" style="color:${colors[type]}; font-size:20px; font-variation-settings:'FILL' 1;">${icons[type]}</span><span>${escapeHtml(msg)}</span>`;
    document.body.appendChild(el);
    setTimeout(() => {
        el.style.transition = 'opacity 0.25s, transform 0.25s';
        el.style.opacity = '0';
        el.style.transform = 'translateY(10px)';
        setTimeout(() => el.remove(), 250);
    }, 2400);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Initial render
renderCart();
</script>
@endpush

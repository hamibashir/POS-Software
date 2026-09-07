@extends('layouts.cashier')

@section('title', 'POS')

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
        --primary:    #1e6d8a;
        --primary-dk: #165268;
        --primary-lt: #e8f4f7;
        --bg:         #f6f7f8;
        --bar-h:      52px; /* matches .pos-bar in layout */
    }

    /* ── Layout ──────────────────────────────────────────── */
    .pos-wrap {
        display: flex;
        height: calc(100vh - var(--bar-h));
        margin-top: var(--bar-h);
        overflow: hidden;
    }

    /* ── LEFT ────────────────────────────────────────────── */
    .pos-left {
        flex: 1 1 0;
        display: flex; flex-direction: column;
        overflow: hidden; min-width: 0;
        background: var(--bg);
        border-right: 1px solid #e2e8f0;
    }

    /* Search header */
    .left-header {
        padding: 16px 20px 12px;
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        flex-shrink: 0;
    }
    .search-row { display: flex; gap: 10px; margin-bottom: 12px; }
    .input-icon-wrap { position: relative; flex: 1; }
    .input-icon-wrap.barcode { width: 230px; flex: none; }
    .input-icon-wrap .ico {
        position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
        font-size: 20px; color: #94a3b8; pointer-events: none;
    }
    .pos-input {
        width: 100%; height: 44px;
        padding: 0 14px 0 40px;
        border: 1.5px solid #e2e8f0; border-radius: 10px;
        background: #f8fafc; font-size: 14px; color: #1e293b;
        outline: none; font-family: 'Inter', sans-serif;
        transition: border-color .15s, background .15s;
    }
    .pos-input:focus { border-color: var(--primary); background: #fff; }

    /* Category pills */
    .cat-pills { display: flex; gap: 8px; overflow-x: auto; scrollbar-width: none; }
    .cat-pills::-webkit-scrollbar { display: none; }
    .cat-pill {
        flex-shrink: 0; padding: 7px 18px; border-radius: 20px;
        font-size: 13px; font-weight: 600; border: none; cursor: pointer;
        white-space: nowrap; font-family: 'Inter', sans-serif;
        transition: background .15s, color .15s;
    }
    .cat-pill.active {
        background: var(--primary); color: #fff;
        box-shadow: 0 2px 8px rgba(30,109,138,.3);
    }
    .cat-pill:not(.active) {
        background: #fff; color: #64748b;
        border: 1.5px solid #e2e8f0;
    }
    .cat-pill:not(.active):hover { background: #f1f5f9; }

    /* Product grid */
    .product-grid {
        flex: 1; overflow-y: auto; padding: 16px 20px 20px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
        gap: 14px; align-content: start;
    }
    .product-grid::-webkit-scrollbar { width: 4px; }
    .product-grid::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

    /* Product card */
    .product-card {
        background: #fff; border: 1.5px solid #e2e8f0; border-radius: 14px;
        padding: 12px; cursor: pointer;
        display: flex; flex-direction: column; gap: 8px;
        transition: border-color .2s, box-shadow .2s, transform .1s;
        user-select: none;
    }
    .product-card:hover {
        border-color: var(--primary);
        box-shadow: 0 6px 20px rgba(30,109,138,.14);
        transform: translateY(-2px);
    }
    .product-card:active { transform: translateY(0); }
    .product-card.oos { opacity: .45; cursor: not-allowed; pointer-events: none; }

    .prod-img-wrap {
        position: relative; width: 100%; aspect-ratio: 1/1;
        border-radius: 10px; overflow: hidden; background: #f1f5f9;
    }
    .prod-img-bg {
        width: 100%; height: 100%;
        background-size: cover; background-position: center;
        transition: transform .3s;
    }
    .product-card:hover .prod-img-bg { transform: scale(1.05); }
    .stock-chip {
        position: absolute; top: 8px; right: 8px;
        background: rgba(255,255,255,.92); backdrop-filter: blur(4px);
        font-size: 10px; font-weight: 700; padding: 3px 8px;
        border-radius: 6px; color: #374151;
    }
    .prod-name {
        font-size: 13px; font-weight: 600; color: #1e293b; line-height: 1.3;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .prod-sku { font-size: 10px; color: #94a3b8; font-family: monospace; }
    .prod-bottom { display: flex; align-items: center; justify-content: space-between; }
    .prod-price { font-size: 16px; font-weight: 800; color: var(--primary); }
    .prod-add-btn {
        background: var(--primary-lt); color: var(--primary);
        width: 30px; height: 30px; border-radius: 8px; border: none;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: background .15s;
        font-size: 20px; line-height: 1;
    }
    .prod-add-btn:hover { background: var(--primary); color: #fff; }

    /* Empty/loading state */
    .prod-state {
        grid-column: 1/-1; text-align: center;
        padding: 56px 20px; color: #94a3b8;
    }
    .prod-state .si { font-size: 52px; display: block; margin-bottom: 12px; opacity: .45; }
    .prod-state p { font-size: 14px; }

    /* ── RIGHT: Cart ──────────────────────────────────────── */
    .pos-right {
        width: 360px; min-width: 320px;
        background: #fff;
        border-left: 1px solid #e2e8f0;
        display: flex; flex-direction: column; overflow: hidden;
    }

    /* Cart header */
    .cart-header {
        padding: 14px 18px; border-bottom: 1px solid #e2e8f0;
        display: flex; align-items: center; justify-content: space-between;
        flex-shrink: 0;
    }
    .cart-hl { display: flex; align-items: center; gap: 8px; }
    .cart-hl .si { color: var(--primary); font-size: 22px; }
    .cart-hl h2 { font-size: 16px; font-weight: 800; margin: 0; }
    .cart-hr { display: flex; align-items: center; gap: 6px; }
    .icon-btn {
        width: 34px; height: 34px; border-radius: 8px; border: none;
        background: transparent; cursor: pointer; color: #94a3b8;
        display: flex; align-items: center; justify-content: center;
        transition: background .15s, color .15s;
    }
    .icon-btn:hover { background: #f1f5f9; color: #1e293b; }
    .icon-btn.del:hover { background: #fee2e2; color: #ef4444; }
    .order-chip {
        font-size: 11px; font-weight: 700; font-family: monospace;
        background: #f1f5f9; color: #64748b; padding: 4px 8px; border-radius: 6px;
    }

    /* Cart items */
    .cart-items { flex: 1; overflow-y: auto; padding: 8px 14px; }
    .cart-items::-webkit-scrollbar { width: 4px; }
    .cart-items::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

    .cart-item {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 10px 0; border-bottom: 1px solid #f1f5f9;
        animation: fadeIn .15s ease;
    }
    @keyframes fadeIn { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:none; } }
    .cart-item:last-child { border-bottom: none; }

    .cart-thumb {
        width: 56px; height: 56px; border-radius: 8px;
        background-size: cover; background-position: center; background-color: #f1f5f9;
        flex-shrink: 0;
    }
    .cart-body { flex: 1; min-width: 0; }
    .cart-name { font-size: 13px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cart-meta { font-size: 11px; color: #64748b; margin-top: 2px; }
    .cart-row2 { display: flex; align-items: center; justify-content: space-between; margin-top: 8px; }
    .cart-line-price { font-size: 14px; font-weight: 700; }

    .qty-ctl {
        display: flex; align-items: center; height: 30px;
        border: 1.5px solid #e2e8f0; border-radius: 8px; overflow: hidden;
    }
    .qty-btn {
        width: 28px; height: 100%; background: #f8fafc;
        border: none; cursor: pointer; font-size: 16px; color: #64748b;
        display: flex; align-items: center; justify-content: center;
        transition: background .1s, color .1s; font-family: 'Inter', sans-serif;
    }
    .qty-btn:hover { background: var(--primary-lt); color: var(--primary); }
    .qty-val {
        width: 32px; height: 100%; border: none;
        border-left: 1.5px solid #e2e8f0; border-right: 1.5px solid #e2e8f0;
        text-align: center; font-size: 13px; font-weight: 700; color: #1e293b;
        font-family: 'Inter', sans-serif;
    }
    .qty-val:focus { outline: none; }

    .rm-btn {
        width: 28px; height: 28px; border-radius: 7px; border: none;
        background: transparent; color: #cbd5e1; cursor: pointer;
        display: flex; align-items: center; justify-content: center; margin-top: 14px; flex-shrink: 0;
        transition: background .1s, color .1s;
    }
    .rm-btn:hover { background: #fee2e2; color: #ef4444; }

    /* Empty cart */
    .cart-empty {
        display: flex; flex-direction: column; align-items: center;
        justify-content: center; height: 100%; color: #cbd5e1; padding: 40px; text-align: center;
    }
    .cart-empty .si { font-size: 56px; display: block; margin-bottom: 14px; }
    .cart-empty p { font-size: 14px; color: #94a3b8; margin: 0; }

    /* Cart footer */
    .cart-footer {
        border-top: 1px solid #e2e8f0; padding: 14px 18px;
        display: flex; flex-direction: column; gap: 10px;
        background: #f8fafc; flex-shrink: 0;
    }
    /* Discount */
    .disc-wrap { position: relative; }
    .disc-wrap .si {
        position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
        font-size: 16px; color: #94a3b8; pointer-events: none;
    }
    .disc-input {
        width: 100%; height: 38px; padding: 0 52px 0 34px;
        border: 1.5px solid #e2e8f0; border-radius: 8px;
        background: #fff; font-size: 13px; color: #1e293b;
        outline: none; font-family: 'Inter', sans-serif; transition: border-color .15s;
    }
    .disc-input:focus { border-color: var(--primary); }
    .disc-apply {
        position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
        font-size: 11px; font-weight: 700; color: var(--primary);
        background: none; border: none; cursor: pointer; text-transform: uppercase; font-family: 'Inter', sans-serif;
    }
    /* Totals */
    .totals { display: flex; flex-direction: column; gap: 5px; }
    .trow { display: flex; justify-content: space-between; font-size: 13px; color: #64748b; }
    .trow.grand {
        font-size: 15px; font-weight: 800; color: #1e293b;
        border-top: 1.5px solid #e2e8f0; padding-top: 8px; margin-top: 2px;
    }
    .grand-amt { font-size: 28px; font-weight: 900; color: var(--primary); line-height: 1; }
    /* Payment */
    .pay-methods { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px; }
    .pay-btn {
        padding: 9px 6px; border-radius: 8px;
        border: 1.5px solid #e2e8f0; background: #f8fafc;
        font-size: 11.5px; font-weight: 700; color: #64748b; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 5px;
        font-family: 'Inter', sans-serif; transition: all .15s;
    }
    .pay-btn .si { font-size: 16px; }
    .pay-btn.active { border-color: var(--primary); background: var(--primary-lt); color: var(--primary); }
    .pay-btn:not(.active):hover { background: #f1f5f9; }

    .emp-credit-box {
        background: #fff; border: 1.5px solid #fecaca; border-radius: 10px;
        padding: 10px 12px; display: flex; flex-direction: column; gap: 4px;
        box-shadow: 0 2px 6px rgba(220,38,38,.06);
    }
    .emp-credit-badge {
        background: #fee2e2; color: #991b1b; font-weight: 800; font-size: 13px;
        padding: 2px 8px; border-radius: 6px; display: inline-block;
    }
    /* Paid */
    .paid-row { display: flex; align-items: center; gap: 8px; }
    .paid-label { font-size: 13px; font-weight: 600; color: #374151; white-space: nowrap; display: flex; align-items: center; gap: 4px; }
    .paid-input {
        flex: 1; height: 40px; padding: 0 12px;
        border: 2px solid #e2e8f0; border-radius: 8px;
        font-size: 15px; font-weight: 600; text-align: right;
        background: #fff; font-family: 'Inter', sans-serif; transition: border-color .15s;
    }
    .paid-input:focus { outline: none; border-color: var(--primary); }
    .change-bar {
        background: #d1fae5; border-radius: 8px; padding: 9px 14px;
        display: flex; justify-content: space-between; align-items: center;
        font-size: 14px; font-weight: 700; color: #065f46;
    }
    .change-bar.neg { background: #fee2e2; color: #991b1b; }
    /* Customer */
    .customer-section { display: flex; flex-direction: column; gap: 6px; }
    .cust-input {
        height: 36px; width: 100%; padding: 0 12px;
        border: 1.5px solid #e2e8f0; border-radius: 8px;
        font-size: 13px; font-family: 'Inter', sans-serif; background: #fff; transition: border-color .15s;
    }
    .cust-input:focus { outline: none; border-color: var(--primary); }
    .cust-toggle {
        font-size: 12px; color: var(--primary); background: none; border: none;
        cursor: pointer; text-align: left; padding: 0; font-family: 'Inter', sans-serif;
        display: flex; align-items: center; gap: 4px;
    }
    .cust-toggle:hover { text-decoration: underline; }
    /* Complete sale */
    .btn-complete {
        width: 100%; padding: 15px; background: var(--primary); color: #fff;
        font-size: 15px; font-weight: 700; font-family: 'Inter', sans-serif;
        border: none; border-radius: 12px; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        box-shadow: 0 4px 16px rgba(30,109,138,.35);
        transition: background .15s, transform .1s;
    }
    .btn-complete:hover:not(:disabled) { background: var(--primary-dk); transform: translateY(-1px); }
    .btn-complete:active:not(:disabled) { transform: translateY(0); }
    .btn-complete:disabled { opacity: .5; cursor: not-allowed; transform: none; }
    .btn-complete .si { font-size: 20px; }
    /* Sub actions */
    .sub-actions { display: flex; gap: 8px; }
    .sub-btn {
        flex: 1; padding: 9px; border-radius: 8px;
        border: 1.5px solid #e2e8f0; background: #fff;
        font-size: 12px; font-weight: 600; color: #64748b;
        cursor: pointer; font-family: 'Inter', sans-serif; transition: background .15s;
    }
    .sub-btn:hover { background: #f1f5f9; color: #1e293b; }
    /* Spinner */
    .sp {
        width: 16px; height: 16px; border-radius: 50%;
        border: 2px solid rgba(255,255,255,.3); border-top-color: #fff;
        animation: spin .6s linear infinite; display: inline-block;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Receipt modal */
    .receipt-modal .modal-content {
        border-radius: 18px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,.18);
    }
    .r-inv { font-size: 22px; font-weight: 800; color: var(--primary); letter-spacing: 1px; }
    .r-row { display: flex; justify-content: space-between; font-size: 14px; padding: 4px 0; }
    .r-total { font-size: 20px; font-weight: 800; border-top: 2px dashed #e2e8f0; padding-top: 10px; margin-top: 6px; }
    .r-change { font-size: 24px; font-weight: 900; color: #065f46; }
</style>
@endpush

@section('content')

<div class="pos-wrap">

    {{-- ══════════ LEFT: Products ══════════ --}}
    <section class="pos-left">

        <div class="left-header">
            {{-- Search + Barcode row --}}
            <div class="search-row">
                <div class="input-icon-wrap barcode">
                    <span class="material-symbols-outlined ico">qr_code_scanner</span>
                    <input type="text" id="barcodeInput" class="pos-input"
                           placeholder="Scan Barcode..." autocomplete="off">
                </div>
                <div class="input-icon-wrap">
                    <span class="material-symbols-outlined ico">search</span>
                    <input type="text" id="productSearch" class="pos-input"
                           placeholder="Search products by name, SKU, or tag..." autocomplete="off" autofocus>
                </div>
            </div>

            {{-- Category pills --}}
            <div class="cat-pills">
                <button class="cat-pill active" data-cat="">All Items</button>
                @foreach(\App\Models\Category::where('is_active', true)->orderBy('name')->get() as $cat)
                <button class="cat-pill" data-cat="{{ $cat->id }}">{{ $cat->name }}</button>
                @endforeach
            </div>
        </div>

        {{-- Product grid --}}
        <div class="product-grid" id="productGrid">
            <div class="prod-state">
                <span class="material-symbols-outlined si">inventory_2</span>
                <p>Search or scan to find products</p>
            </div>
        </div>
    </section>

    {{-- ══════════ RIGHT: Cart ══════════ --}}
    <aside class="pos-right">

        {{-- Header --}}
        <div class="cart-header">
            <div class="cart-hl">
                <span class="material-symbols-outlined si">shopping_cart</span>
                <h2>Current Order</h2>
            </div>
            <div class="cart-hr">
                <button class="icon-btn del" onclick="clearCart()" title="Clear Cart">
                    <span class="material-symbols-outlined" style="font-size:20px;">delete</span>
                </button>
                <button class="icon-btn" onclick="toggleCustomer()" title="Add Customer">
                    <span class="material-symbols-outlined" style="font-size:20px;">person_add</span>
                </button>
                <div class="order-chip" id="orderNo">#4921</div>
            </div>
        </div>

        {{-- Items --}}
        <div class="cart-items" id="cartItems">
            <div class="cart-empty" id="cartEmpty">
                <span class="material-symbols-outlined si">remove_shopping_cart</span>
                <p>Cart is empty<br><small style="font-size:12px;">Tap a product to add</small></p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="cart-footer">

            {{-- Discount --}}
            <div class="disc-wrap">
                <span class="material-symbols-outlined si">percent</span>
                <input type="number" id="discountInput" class="disc-input"
                       min="0" step="0.01" value="0"
                       placeholder="Add Discount Code" oninput="updateTotals()">
                <button class="disc-apply">APPLY</button>
            </div>

            {{-- Totals --}}
            <div class="totals">
                <div class="trow"><span>Subtotal</span><span id="cartSubtotal">Rs. 0.00</span></div>
                <div class="trow"><span>Tax (0%)</span><span id="taxDisplay">Rs. 0.00</span></div>
                <div class="trow grand">
                    <span>Total</span>
                    <span class="grand-amt" id="cartTotal">Rs. 0.00</span>
                </div>
            </div>

            {{-- Payment --}}
            <div class="pay-methods">
                <button class="pay-btn active" data-method="cash" onclick="selectPayment('cash')">
                    <span class="material-symbols-outlined si">payments</span>Cash
                </button>
                <button class="pay-btn" data-method="card" onclick="selectPayment('card')">
                    <span class="material-symbols-outlined si">credit_card</span>Card
                </button>
                <button class="pay-btn" data-method="credit" onclick="selectPayment('credit')">
                    <span class="material-symbols-outlined si">badge</span>Credit
                </button>
            </div>

            {{-- Employee selection for Credit Sale --}}
            <div id="creditEmployeeSection" style="display:none; flex-direction:column; gap:6px;">
                <label style="font-size:12px; font-weight:700; color:#374151; display:flex; align-items:center; gap:4px;">
                    <span class="material-symbols-outlined" style="font-size:16px; color:var(--primary);">badge</span>
                    Employee Taking Credit <span style="color:#ef4444;">*</span>
                </label>
                <select id="employeeSelect" class="cust-input" onchange="onEmployeeSelect()">
                    <option value="">-- Select Authorized Employee --</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp['id'] }}"
                            data-name="{{ $emp['name'] }}"
                            data-phone="{{ $emp['phone'] }}"
                            data-address="{{ $emp['address'] }}"
                            data-pending="{{ $emp['pending_payment'] }}">
                        {{ $emp['name'] }} (Pending: Rs. {{ number_format($emp['pending_payment'], 2) }})
                    </option>
                    @endforeach
                </select>

                <div id="employeeDetailsCard" class="emp-credit-box" style="display:none;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div>
                            <div style="font-weight:800; font-size:14px; color:#111827;" id="cardEmpName">—</div>
                            <div style="font-size:12px; color:#4b5563;" id="cardEmpPhone">—</div>
                            <div style="font-size:11px; color:#6b7280; margin-top:2px;" id="cardEmpAddress">—</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:10px; font-weight:700; color:#991b1b; text-transform:uppercase;">Pending Due</div>
                            <div class="emp-credit-badge" id="cardEmpPending">Rs. 0.00</div>
                        </div>
                    </div>
                    <div style="border-top:1px dashed #fecaca; margin-top:6px; padding-top:6px; font-size:11px; color:#991b1b; font-weight:700; display:flex; justify-content:space-between;">
                        <span>Balance after this sale:</span>
                        <span id="cardEmpNewTotal" style="font-size:12px;">Rs. 0.00</span>
                    </div>
                </div>
            </div>

            {{-- Paid --}}
            <div class="paid-row" id="paidAmountRow">
                <label class="paid-label">
                    <span class="material-symbols-outlined" style="font-size:16px;">wallet</span>Paid
                </label>
                <input type="number" id="paidInput" class="paid-input"
                       min="0" step="0.01" placeholder="0.00" oninput="updateChange()">
            </div>

            {{-- Change --}}
            <div class="change-bar" id="changeDisplay">
                <span>Change</span>
                <span id="changeAmount">Rs. 0.00</span>
            </div>

            {{-- Customer (hidden) --}}
            <div id="customerSection" class="customer-section" style="display:none;">
                <input type="text" id="customerName" class="cust-input" placeholder="Customer name (optional)">
                <input type="text" id="customerPhone" class="cust-input" placeholder="Phone (optional)">
            </div>
            <button class="cust-toggle" onclick="toggleCustomer()" id="custToggleBtn">
                <span class="material-symbols-outlined" style="font-size:15px;">person_add</span>
                Add customer info
            </button>

            {{-- Complete Sale --}}
            <button class="btn-complete" id="completeSaleBtn" onclick="completeSale()" disabled>
                <span id="completeSaleLabel">Complete Sale</span>
                <span class="material-symbols-outlined si">arrow_forward</span>
            </button>

            <div class="sub-actions">
                <button class="sub-btn">Hold Order</button>
                <button class="sub-btn" onclick="clearCart()">Void</button>
            </div>
        </div>
    </aside>
</div>

{{-- ══════════ Receipt Modal ══════════ --}}
<div class="modal fade receipt-modal" id="receiptModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-5 text-center">
            <div style="font-size:60px;color:#10b981;margin-bottom:6px;">
                <span class="material-symbols-outlined"
                      style="font-size:60px;color:#10b981;font-variation-settings:'FILL' 1,'wght' 400,'GRAD' 0,'opsz' 48;">
                    check_circle
                </span>
            </div>
            <h4 style="font-size:22px;font-weight:800;margin-bottom:4px;">Sale Complete!</h4>
            <div class="r-inv mt-1" id="receiptInvoice">—</div>

            <div class="mt-4 text-start" style="background:#f8fafc;border-radius:12px;padding:18px;">
                <div class="r-row"><span>Total</span>  <strong id="receiptTotal">—</strong></div>
                <div class="r-row"><span>Paid</span>   <strong id="receiptPaid">—</strong></div>
                <div class="r-row r-total"><span>Change</span><span class="r-change" id="receiptChange">—</span></div>
            </div>
            <p style="font-size:13px;color:#94a3b8;margin-top:12px;" id="receiptItems">—</p>

            <div style="display:flex;gap:10px;margin-top:14px;">
                <a id="printReceiptBtn" href="#" target="_blank" rel="noopener"
                   style="flex:1;background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;border-radius:10px;
                          padding:12px;font-size:14px;font-weight:600;text-decoration:none;
                          display:flex;align-items:center;justify-content:center;gap:6px;">
                    🖨️ Print Receipt
                </a>
                <button class="btn-complete" onclick="closeReceipt()" style="flex:1;border-radius:10px;">
                    <span class="material-symbols-outlined" style="font-size:18px;">refresh</span>
                    New Sale
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let cart           = [];
let paymentMethod  = 'cash';
let searchTimeout  = null;
let custVisible    = false;
let activeCat      = '';

const CSRF = document.querySelector('meta[name="csrf-token"]').content;

/* ── Category pills ──────────────────────────── */
document.querySelectorAll('.cat-pill').forEach(pill => {
    pill.addEventListener('click', () => {
        document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        activeCat = pill.dataset.cat;
        const q = document.getElementById('productSearch').value.trim();
        if (q || activeCat) { showState('loading'); fetchProducts(q || ' '); }
        else showState('search');
    });
});

/* ── Search ──────────────────────────────────── */
const searchInput  = document.getElementById('productSearch');
const barcodeInput = document.getElementById('barcodeInput');
const productGrid  = document.getElementById('productGrid');

searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    const q = searchInput.value.trim();
    if (!q && !activeCat) { showState('search'); return; }
    showState('loading');
    searchTimeout = setTimeout(() => fetchProducts(q || ' '), 280);
});

barcodeInput.addEventListener('keydown', async e => {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const q = barcodeInput.value.trim();
    if (!q) return;
    const res = await fetchProducts(q, true);
    barcodeInput.value = '';
    barcodeInput.focus();
    if (res && res.length === 1) { addToCart(res[0]); showState('search'); }
});

async function fetchProducts(q, silent = false) {
    if (!silent) showState('loading');
    try {
        let url = `{{ route('cashier.pos.search') }}?q=${encodeURIComponent(q)}`;
        if (activeCat) url += `&category_id=${activeCat}`;
        const products = await (await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })).json();
        if (!silent) renderProducts(products);
        return products;
    } catch(e) { if (!silent) showState('error'); }
}

function renderProducts(list) {
    if (!list.length) { showState('empty'); return; }
    productGrid.innerHTML = list.map(p => {
        const isOutOfStock = p.stock_quantity <= 0;
        const stockChip = isOutOfStock
            ? '<span class="stock-chip" style="background:#fee2e2; color:#b91c1c; font-weight:800;">Out of Stock</span>'
            : '';
        return `
        <div class="product-card ${isOutOfStock ? 'oos' : ''}"
             onclick="addToCart(${JSON.stringify(p).replace(/"/g, '&quot;')})">
            <div class="prod-img-wrap">
                <div class="prod-img-bg" style="background-image:url('${p.image_url}');"></div>
                ${stockChip}
            </div>
            <div class="prod-name">${p.name}</div>
            <div class="prod-sku">SKU: ${p.sku}</div>
            <div class="prod-bottom">
                <span class="prod-price">Rs. ${parseFloat(p.sale_price).toFixed(0)}</span>
                <button class="prod-add-btn" tabindex="-1">
                    <span class="material-symbols-outlined" style="font-size:18px;">add</span>
                </button>
            </div>
        </div>`;
    }).join('');
}

function showState(t) {
    const map = {
        search:  '<span class="material-symbols-outlined si">manage_search</span><p>Search or scan to find products</p>',
        loading: '<span class="material-symbols-outlined si" style="animation:spin .7s linear infinite;">sync</span><p>Searching…</p>',
        empty:   '<span class="material-symbols-outlined si">inbox</span><p>No products found</p>',
        error:   '<span class="material-symbols-outlined si" style="color:#ef4444;">warning</span><p>Search failed. Try again.</p>',
    };
    productGrid.innerHTML = `<div class="prod-state">${map[t]}</div>`;
}

/* ── Cart ────────────────────────────────────── */
function addToCart(p) {
    const ex = cart.find(i => i.id === p.id);
    if (ex) {
        if (ex.qty >= p.stock_quantity) { toast('Maximum available quantity reached', 'w'); return; }
        ex.qty++;
    } else {
        if (p.stock_quantity <= 0) { toast('Product is out of stock', 'e'); return; }
        cart.push({ ...p, qty: 1 });
    }
    renderCart(); toast(`${p.name} added`, 's');
}

function removeFromCart(id) { cart = cart.filter(i => i.id !== id); renderCart(); }

function updateQty(id, d) {
    const item = cart.find(i => i.id === id);
    if (!item) return;
    const n = item.qty + d;
    if (n <= 0) { removeFromCart(id); return; }
    if (n > item.stock_quantity) { toast('Maximum available quantity reached', 'w'); return; }
    item.qty = n; renderCart();
}

function setQty(id, v) {
    const item = cart.find(i => i.id === id);
    if (!item) return;
    const q = parseInt(v) || 1;
    if (q <= 0) { removeFromCart(id); return; }
    if (q > item.stock_quantity) { toast('Maximum available quantity reached', 'w'); return; }
    item.qty = q; renderCart();
}

function clearCart() {
    if (!cart.length) return;
    if (!confirm('Clear the entire cart?')) return;
    cart = []; document.getElementById('discountInput').value = '0';
    renderCart();
}

function renderCart() {
    const c = document.getElementById('cartItems');
    if (!cart.length) {
        c.innerHTML = '';
        const el = document.createElement('div');
        el.className = 'cart-empty'; el.id = 'cartEmpty';
        el.innerHTML = '<span class="material-symbols-outlined si">remove_shopping_cart</span><p>Cart is empty<br><small style="font-size:12px;">Tap a product to add</small></p>';
        c.appendChild(el);
        document.getElementById('completeSaleBtn').disabled = true;
        updateTotals(); return;
    }
    document.getElementById('completeSaleBtn').disabled = false;
    c.innerHTML = cart.map(item => `
        <div class="cart-item">
            <div class="cart-thumb" style="background-image:url('${item.image_url}');"></div>
            <div class="cart-body">
                <div class="cart-name">${item.name}</div>
                <div class="cart-meta">Unit: Rs. ${parseFloat(item.sale_price).toFixed(2)}</div>
                <div class="cart-row2">
                    <span class="cart-line-price">Rs. ${(item.sale_price * item.qty).toFixed(2)}</span>
                    <div class="qty-ctl">
                        <button class="qty-btn" onclick="updateQty(${item.id}, -1)">−</button>
                        <input type="number" class="qty-val" value="${item.qty}" min="1"
                               max="${item.stock_quantity}"
                               onchange="setQty(${item.id}, this.value)"
                               onclick="this.select()">
                        <button class="qty-btn" onclick="updateQty(${item.id}, 1)">+</button>
                    </div>
                </div>
            </div>
            <button class="rm-btn" onclick="removeFromCart(${item.id})">
                <span class="material-symbols-outlined" style="font-size:18px;">close</span>
            </button>
        </div>`).join('');
    updateTotals();
}

/* ── Totals ──────────────────────────────────── */
function updateTotals() {
    const sub  = cart.reduce((s, i) => s + i.sale_price * i.qty, 0);
    const disc = Math.min(parseFloat(document.getElementById('discountInput').value) || 0, sub);
    const total = Math.max(0, sub - disc);
    document.getElementById('cartSubtotal').textContent = 'Rs. ' + sub.toFixed(2);
    document.getElementById('taxDisplay').textContent   = 'Rs. 0.00';
    document.getElementById('cartTotal').textContent    = 'Rs. ' + total.toFixed(2);
    document.getElementById('discountInput').dataset.disc = disc;
    if (paymentMethod === 'card') document.getElementById('paidInput').value = total.toFixed(2);
    if (paymentMethod === 'credit') onEmployeeSelect();
    updateChange();
}

function updateChange() {
    const total  = parseFloat(document.getElementById('cartTotal').textContent.replace('Rs. ', '')) || 0;
    const paid   = parseFloat(document.getElementById('paidInput').value) || 0;
    const change = paid - total;
    const el = document.getElementById('changeAmount');
    const box = document.getElementById('changeDisplay');
    el.textContent = (change < 0 ? '-' : '') + 'Rs. ' + Math.abs(change).toFixed(2);
    box.classList.toggle('neg', change < 0);
}

/* ── Payment ─────────────────────────────────── */
function selectPayment(method) {
    paymentMethod = method;
    document.querySelectorAll('.pay-btn').forEach(b => b.classList.toggle('active', b.dataset.method === method));
    const paidRow   = document.getElementById('paidAmountRow');
    const changeBox = document.getElementById('changeDisplay');
    const creditSec = document.getElementById('creditEmployeeSection');
    const custToggle = document.getElementById('custToggleBtn');
    const custSec    = document.getElementById('customerSection');

    if (method === 'credit') {
        paidRow.style.display    = 'none';
        changeBox.style.display  = 'none';
        creditSec.style.display  = 'flex';
        custToggle.style.display = 'none';
        if (custSec) custSec.style.display = 'none';
        document.getElementById('paidInput').value = '0.00';
        onEmployeeSelect();
    } else if (method === 'card') {
        paidRow.style.display    = 'none';
        changeBox.style.display  = 'flex';
        creditSec.style.display  = 'none';
        custToggle.style.display = 'flex';
        const t = parseFloat(document.getElementById('cartTotal').textContent.replace('Rs. ', '')) || 0;
        document.getElementById('paidInput').value = t.toFixed(2);
    } else {
        paidRow.style.display    = 'flex';
        changeBox.style.display  = 'flex';
        creditSec.style.display  = 'none';
        custToggle.style.display = 'flex';
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
    const pending   = parseFloat(opt.dataset.pending) || 0;
    const cartTotal = parseFloat(document.getElementById('cartTotal').textContent.replace('Rs. ', '')) || 0;
    const newTotal  = pending + cartTotal;

    document.getElementById('cardEmpName').textContent    = name;
    document.getElementById('cardEmpPhone').textContent   = '📞 ' + phone;
    document.getElementById('cardEmpAddress').textContent = '📍 ' + address;
    document.getElementById('cardEmpPending').textContent = 'Rs. ' + pending.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    document.getElementById('cardEmpNewTotal').textContent = 'Rs. ' + newTotal.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    card.style.display = 'flex';
}

/* ── Customer ────────────────────────────────── */
function toggleCustomer() {
    custVisible = !custVisible;
    document.getElementById('customerSection').style.display = custVisible ? 'flex' : 'none';
    document.getElementById('custToggleBtn').innerHTML = custVisible
        ? '<span class="material-symbols-outlined" style="font-size:15px;">close</span> Hide customer info'
        : '<span class="material-symbols-outlined" style="font-size:15px;">person_add</span> Add customer info';
}

/* ── Complete sale ───────────────────────────── */
async function completeSale() {
    if (!cart.length) return;
    const total    = parseFloat(document.getElementById('cartTotal').textContent.replace('Rs. ', '')) || 0;
    const paid     = parseFloat(document.getElementById('paidInput').value) || 0;
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;

    if (paymentMethod === 'cash' && paid < total) { toast('Paid amount is less than total!', 'e'); return; }

    if (paymentMethod === 'credit') {
        const empSelect = document.getElementById('employeeSelect');
        if (!empSelect || !empSelect.value) {
            toast('Please select an authorized employee for credit sale!', 'w');
            return;
        }
    }

    const btn = document.getElementById('completeSaleBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="sp"></span>&nbsp; Processing...';

    try {
        const res  = await fetch('{{ route('cashier.pos.complete-sale') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                cart: cart.map(i => ({ product_id: i.id, quantity: i.qty, unit_price: i.sale_price, discount_amount: 0 })),
                payment_method: paymentMethod,
                paid_amount:    paymentMethod === 'card' ? total : (paymentMethod === 'credit' ? 0 : paid),
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
            btn.innerHTML = '<span id="completeSaleLabel">Complete Sale</span><span class="material-symbols-outlined si">arrow_forward</span>';
        }
    } catch(e) {
        toast('Network error.', 'e');
        btn.disabled = false;
        btn.innerHTML = '<span id="completeSaleLabel">Complete Sale</span><span class="material-symbols-outlined si">arrow_forward</span>';
    }
}

/* ── Receipt ─────────────────────────────────── */
const modal = new bootstrap.Modal(document.getElementById('receiptModal'));

function showReceipt(data) {
    document.getElementById('receiptInvoice').textContent = data.invoice_number;
    document.getElementById('receiptTotal').textContent   = 'Rs. ' + data.total_amount;
    document.getElementById('receiptPaid').textContent    = 'Rs. ' + data.paid_amount;
    document.getElementById('receiptChange').textContent  = 'Rs. ' + data.change_amount;
    document.getElementById('receiptItems').textContent   = data.items_count + ' item(s) sold';
    document.getElementById('printReceiptBtn').href       = data.receipt_url + '?print=1';
    modal.show();
}

function closeReceipt() {
    modal.hide();
    cart = []; paymentMethod = 'cash';
    document.getElementById('discountInput').value = '0';
    document.getElementById('paidInput').value     = '';
    document.getElementById('customerName').value  = '';
    document.getElementById('customerPhone').value = '';
    const empSel = document.getElementById('employeeSelect');
    if (empSel) empSel.value = '';
    const empCard = document.getElementById('employeeDetailsCard');
    if (empCard) empCard.style.display = 'none';
    if (custVisible) toggleCustomer();
    selectPayment('cash');
    renderCart();
    document.getElementById('completeSaleBtn').disabled = false;
    document.getElementById('completeSaleBtn').innerHTML = '<span>Complete Sale</span><span class="material-symbols-outlined si">arrow_forward</span>';
    searchInput.value = ''; showState('search'); searchInput.focus();
}

/* ── Toast ───────────────────────────────────── */
function toast(msg, type) {
    const c = { s: '#10b981', e: '#ef4444', w: '#f59e0b' };
    const ic = { s: 'check_circle', e: 'cancel', w: 'warning' };
    const el = document.createElement('div');
    el.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;background:#fff;border-radius:12px;
        padding:12px 18px;box-shadow:0 8px 28px rgba(0,0,0,.14);display:flex;align-items:center;gap:10px;
        border-left:4px solid ${c[type]};font-size:14px;font-weight:500;max-width:300px;font-family:'Inter',sans-serif;`;
    el.innerHTML = `<span class="material-symbols-outlined" style="color:${c[type]};font-size:20px;font-variation-settings:'FILL' 1;">${ic[type]}</span>${msg}`;
    document.body.appendChild(el);
    setTimeout(() => { el.style.transition='opacity .3s,transform .3s'; el.style.opacity='0'; el.style.transform='translateX(20px)'; setTimeout(()=>el.remove(),300); }, 2200);
}

renderCart();
</script>
@endpush

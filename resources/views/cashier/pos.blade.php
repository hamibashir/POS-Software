@extends('layouts.cashier')

@section('title', 'POS')

@push('styles')
<style>
    :root {
        --bar-h:    52px;
        --primary:  #1a6b7c;
        --primary-lt: #e8f4f7;
    }

    /* ── Layout ─────────────────────────────── */
    .pos-wrap {
        display: flex;
        height: calc(100vh - var(--bar-h));
        margin-top: var(--bar-h);
        overflow: hidden;
    }

    /* ── LEFT panel ─────────────────────────── */
    .pos-left {
        flex: 1 1 60%;
        display: flex;
        flex-direction: column;
        padding: 14px 14px 14px 16px;
        overflow: hidden;
        min-width: 0;
    }

    /* Search bar */
    .search-wrap {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
    }
    .search-wrap .search-input {
        flex: 1;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 16px 10px 42px;
        font-size: 14px;
        color: #111827;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%239ca3af' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.867-3.834zm-5.242 1.156a5 5 0 1 1 0-10 5 5 0 0 1 0 10z'/%3E%3C/svg%3E") no-repeat 14px center;
        transition: border-color .15s;
    }
    .search-wrap .search-input:focus { outline:none; border-color: var(--primary); }

    .barcode-input {
        width: 180px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 14px;
        transition: border-color .15s;
        background: #fff;
    }
    .barcode-input:focus { outline:none; border-color: var(--primary); }

    /* Product grid */
    .product-grid {
        flex: 1;
        overflow-y: auto;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 10px;
        padding-right: 4px;
    }
    .product-grid::-webkit-scrollbar { width: 4px; }
    .product-grid::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

    .product-card {
        background: #fff;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 12px;
        cursor: pointer;
        transition: border-color .15s, box-shadow .15s, transform .1s;
        user-select: none;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .product-card:hover {
        border-color: var(--primary);
        box-shadow: 0 4px 16px rgba(26,107,124,.15);
        transform: translateY(-1px);
    }
    .product-card:active { transform: translateY(0); }
    .product-card.out-of-stock { opacity: .45; cursor: not-allowed; pointer-events: none; }

    .product-card .prod-img {
        width: 100%;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        background: #f3f4f6;
    }
    .product-card .prod-img-ph {
        width: 100%;
        height: 80px;
        background: #f3f4f6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #d1d5db;
        font-size: 26px;
    }
    .product-card .prod-name {
        font-size: 13px;
        font-weight: 600;
        color: #111827;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-card .prod-price {
        font-size: 15px;
        font-weight: 700;
        color: var(--primary);
    }
    .product-card .prod-sku {
        font-size: 10px;
        color: #9ca3af;
        font-family: monospace;
    }
    .product-card .prod-stock {
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 10px;
        font-weight: 600;
        align-self: flex-start;
    }
    .stock-ok  { background: #d1fae5; color: #065f46; }
    .stock-low { background: #fef3c7; color: #92400e; }
    .stock-out { background: #fee2e2; color: #991b1b; }

    /* Empty/loading states */
    .prod-state {
        grid-column: 1/-1;
        text-align: center;
        padding: 48px 20px;
        color: #9ca3af;
    }
    .prod-state i { font-size: 42px; display: block; margin-bottom: 12px; }

    /* ── RIGHT panel (Cart) ───────────────────── */
    .pos-right {
        flex: 0 0 380px;
        background: #fff;
        border-left: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* Cart header */
    .cart-header {
        padding: 14px 18px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .cart-header h2 {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }
    .cart-badge {
        background: var(--primary);
        color: #fff;
        border-radius: 20px;
        padding: 2px 10px;
        font-size: 12px;
        font-weight: 700;
    }
    .btn-clear-cart {
        font-size: 12px;
        color: #ef4444;
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 6px;
        transition: background .15s;
    }
    .btn-clear-cart:hover { background: #fee2e2; }

    /* Cart items */
    .cart-items {
        flex: 1;
        overflow-y: auto;
        padding: 8px 12px;
    }
    .cart-items::-webkit-scrollbar { width: 4px; }
    .cart-items::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }

    .cart-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
        animation: slideIn .15s ease;
    }
    @keyframes slideIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }

    .cart-item:last-child { border-bottom: none; }
    .cart-item-info { flex: 1; min-width: 0; }
    .cart-item-name { font-size: 13px; font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cart-item-meta { font-size: 11px; color: #9ca3af; }
    .cart-item-price { font-size: 14px; font-weight: 700; color: #111827; min-width: 60px; text-align: right; }

    /* Qty controls */
    .qty-controls {
        display: flex;
        align-items: center;
        gap: 0;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }
    .qty-btn {
        width: 28px; height: 28px;
        background: #f9fafb;
        border: none;
        cursor: pointer;
        font-size: 15px;
        color: #374151;
        transition: background .1s;
        display: flex; align-items: center; justify-content: center;
    }
    .qty-btn:hover { background: var(--primary-lt); color: var(--primary); }
    .qty-input {
        width: 36px; height: 28px;
        border: none;
        border-left: 1.5px solid #e5e7eb;
        border-right: 1.5px solid #e5e7eb;
        text-align: center;
        font-size: 13px;
        font-weight: 600;
        color: #111827;
    }
    .qty-input:focus { outline: none; }
    .btn-remove-item {
        background: none; border: none;
        color: #d1d5db; cursor: pointer;
        font-size: 16px; padding: 4px;
        border-radius: 6px;
        transition: color .1s, background .1s;
    }
    .btn-remove-item:hover { color: #ef4444; background: #fee2e2; }

    /* Cart empty */
    .cart-empty {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #d1d5db;
        padding: 40px;
        text-align: center;
    }
    .cart-empty i { font-size: 56px; margin-bottom: 12px; }
    .cart-empty p { font-size: 14px; color: #9ca3af; margin: 0; }

    /* Cart footer (totals + payment) */
    .cart-footer {
        border-top: 1px solid #e5e7eb;
        padding: 14px 18px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        color: #6b7280;
    }
    .total-row.grand {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        border-top: 1.5px solid #e5e7eb;
        padding-top: 8px;
        margin-top: 2px;
    }

    /* Discount input */
    .discount-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .discount-row label { font-size: 13px; color: #6b7280; white-space: nowrap; }
    .discount-input {
        width: 100%;
        border: 1.5px solid #e5e7eb;
        border-radius: 7px;
        padding: 5px 10px;
        font-size: 13px;
        text-align: right;
        transition: border-color .15s;
    }
    .discount-input:focus { outline:none; border-color: var(--primary); }

    /* Payment method */
    .payment-methods {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
    }
    .pay-method-btn {
        padding: 8px;
        border-radius: 8px;
        border: 2px solid #e5e7eb;
        background: #f9fafb;
        font-size: 13px;
        font-weight: 500;
        color: #6b7280;
        cursor: pointer;
        text-align: center;
        transition: all .15s;
        user-select: none;
    }
    .pay-method-btn.selected {
        border-color: var(--primary);
        background: var(--primary-lt);
        color: var(--primary);
        font-weight: 600;
    }
    .pay-method-btn i { display: block; font-size: 18px; margin-bottom: 2px; }

    /* Paid amount */
    .paid-row { display: flex; align-items: center; gap: 8px; }
    .paid-row label { font-size: 13px; font-weight: 600; color: #374151; white-space: nowrap; }
    .paid-input {
        flex: 1;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 15px;
        font-weight: 600;
        text-align: right;
        transition: border-color .15s;
    }
    .paid-input:focus { outline:none; border-color: var(--primary); }

    /* Change */
    .change-display {
        background: #d1fae5;
        border-radius: 8px;
        padding: 8px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 15px;
        font-weight: 700;
        color: #065f46;
    }
    .change-display.change-neg { background: #fee2e2; color: #991b1b; }

    /* Customer fields */
    .customer-section { display: flex; flex-direction: column; gap: 6px; }
    .customer-input {
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        padding: 7px 12px;
        font-size: 13px;
        width: 100%;
        transition: border-color .15s;
    }
    .customer-input:focus { outline:none; border-color: var(--primary); }

    /* Complete sale button */
    .btn-complete-sale {
        background: linear-gradient(135deg, var(--primary), var(--primary-dk, #0d4a57));
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 14px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: opacity .15s, transform .1s;
        letter-spacing: .3px;
    }
    .btn-complete-sale:hover:not(:disabled) { opacity: .9; transform: translateY(-1px); }
    .btn-complete-sale:active:not(:disabled) { transform: translateY(0); }
    .btn-complete-sale:disabled { opacity: .5; cursor: not-allowed; transform: none; }

    /* ── Success modal ──────────────────────── */
    .receipt-modal .modal-content { border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
    .receipt-invoice { font-family: monospace; font-size: 22px; font-weight: 700; letter-spacing: 1px; color: var(--primary); }
    .receipt-row { display:flex; justify-content:space-between; font-size:14px; padding: 4px 0; }
    .receipt-total { font-size: 20px; font-weight: 800; color: #111827; border-top: 2px dashed #e5e7eb; padding-top:10px; margin-top:6px; }
    .change-highlight { font-size: 22px; font-weight: 800; color: #065f46; }

    /* Spinner */
    .spinner-sm { width:18px; height:18px; border:2px solid rgba(255,255,255,.3); border-top-color:#fff; border-radius:50%; animation:spin .6s linear infinite; display:inline-block; }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush

@section('content')

<div class="pos-wrap">

    {{-- ═══════════════════ LEFT: Products ═══════════════════ --}}
    <div class="pos-left">

        {{-- Search + Barcode --}}
        <div class="search-wrap">
            <input type="text"
                id="productSearch"
                class="search-input"
                placeholder="Search by name or SKU..."
                autocomplete="off"
                autofocus>
            <input type="text"
                id="barcodeInput"
                class="barcode-input"
                placeholder="Scan barcode..."
                autocomplete="off"
                title="Barcode scanner input">
        </div>

        {{-- Product grid --}}
        <div class="product-grid" id="productGrid">
            <div class="prod-state">
                <i class="bi bi-box-seam"></i>
                <p>Search or scan to find products</p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ RIGHT: Cart ═══════════════════════ --}}
    <div class="pos-right">

        {{-- Cart header --}}
        <div class="cart-header">
            <h2><i class="bi bi-cart3 me-2"></i>Cart <span class="cart-badge" id="cartCount">0</span></h2>
            <button class="btn-clear-cart" onclick="clearCart()" title="Clear cart">
                <i class="bi bi-trash"></i> Clear
            </button>
        </div>

        {{-- Cart items --}}
        <div class="cart-items" id="cartItems">
            <div class="cart-empty" id="cartEmpty">
                <i class="bi bi-cart-x"></i>
                <p>Cart is empty<br><small>Tap a product to add</small></p>
            </div>
        </div>

        {{-- Cart footer --}}
        <div class="cart-footer">

            {{-- Subtotal --}}
            <div class="total-row">
                <span>Subtotal</span>
                <span id="cartSubtotal">$0.00</span>
            </div>

            {{-- Discount --}}
            <div class="discount-row">
                <label for="discountInput"><i class="bi bi-tag"></i> Discount</label>
                <div style="position:relative; flex:1;">
                    <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:13px;">$</span>
                    <input type="number" id="discountInput" class="discount-input"
                        min="0" step="0.01" value="0" placeholder="0.00"
                        oninput="updateTotals()" style="padding-left:22px;">
                </div>
            </div>

            {{-- Grand total --}}
            <div class="total-row grand">
                <span>TOTAL</span>
                <span id="cartTotal">$0.00</span>
            </div>

            {{-- Payment method --}}
            <div class="payment-methods" id="paymentMethods">
                <div class="pay-method-btn selected" data-method="cash" onclick="selectPayment('cash')">
                    <i class="bi bi-cash-coin"></i>Cash
                </div>
                <div class="pay-method-btn" data-method="card" onclick="selectPayment('card')">
                    <i class="bi bi-credit-card"></i>Card
                </div>
            </div>

            {{-- Paid amount (only for cash) --}}
            <div class="paid-row" id="paidAmountRow">
                <label for="paidInput" style="white-space:nowrap;"><i class="bi bi-wallet2"></i> Paid</label>
                <div style="position:relative; flex:1;">
                    <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:15px;">$</span>
                    <input type="number" id="paidInput" class="paid-input"
                        min="0" step="0.01" placeholder="0.00"
                        oninput="updateChange()" style="padding-left:26px;">
                </div>
            </div>

            {{-- Change --}}
            <div class="change-display" id="changeDisplay">
                <span>Change</span>
                <span id="changeAmount">$0.00</span>
            </div>

            {{-- Customer (collapsed by default) --}}
            <div id="customerSection" class="customer-section" style="display:none;">
                <input type="text" id="customerName" class="customer-input" placeholder="Customer name (optional)">
                <input type="text" id="customerPhone" class="customer-input" placeholder="Phone (optional)">
            </div>
            <button type="button"
                onclick="toggleCustomer()"
                id="toggleCustomerBtn"
                style="background:none; border:none; font-size:12px; color:var(--primary); cursor:pointer; text-align:left; padding:0; margin-top:-4px;">
                <i class="bi bi-person-plus"></i> Add customer info
            </button>

            {{-- Complete Sale --}}
            <button class="btn-complete-sale" id="completeSaleBtn" onclick="completeSale()" disabled>
                <i class="bi bi-check-circle"></i>
                <span id="completeSaleLabel">Complete Sale</span>
            </button>

        </div>
    </div>
</div>

{{-- ═══════════════════ SUCCESS MODAL ═══════════════════ --}}
<div class="modal fade receipt-modal" id="receiptModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 text-center">
            <div style="font-size: 56px; color: #10b981; margin-bottom: 8px;">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h4 class="fw-bold mb-0">Sale Complete!</h4>
            <div class="receipt-invoice mt-2" id="receiptInvoice">—</div>

            <div class="mt-4 text-start" style="background:#f9fafb; border-radius:10px; padding:16px;">
                <div class="receipt-row"><span>Total</span>    <strong id="receiptTotal">—</strong></div>
                <div class="receipt-row"><span>Paid</span>     <strong id="receiptPaid">—</strong></div>
                <div class="receipt-row receipt-total"><span>Change</span> <span class="change-highlight" id="receiptChange">—</span></div>
            </div>

            <p class="text-muted mt-3 mb-2" style="font-size:13px;" id="receiptItems">—</p>

            {{-- Action buttons --}}
            <div class="d-flex gap-2 mt-2">
                <a id="printReceiptBtn" href="#" target="_blank" rel="noopener"
                   style="flex:1; background:#f9fafb; color:#374151; border:1.5px solid #e5e7eb; border-radius:10px;
                          padding:12px; font-size:14px; font-weight:600; text-decoration:none;
                          display:flex; align-items:center; justify-content:center; gap:6px; transition:background .15s;"
                   onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#f9fafb'">
                    🖨️ Print Receipt
                </a>
                <button class="btn-complete-sale" onclick="closeReceipt()" style="flex:1; border-radius:10px;">
                    <i class="bi bi-arrow-repeat"></i> New Sale
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ═══════════════════════════════════════════════════════════
   POS STATE
═══════════════════════════════════════════════════════════ */
let cart             = [];          // { id, name, sku, unit, sale_price, stock_quantity, qty, image_url }
let paymentMethod    = 'cash';
let searchTimeout    = null;
let customerVisible  = false;

const CSRF = document.querySelector('meta[name="csrf-token"]').content;

/* ═══════════════════════════════════════════════════════════
   PRODUCT SEARCH
═══════════════════════════════════════════════════════════ */
const searchInput  = document.getElementById('productSearch');
const barcodeInput = document.getElementById('barcodeInput');
const productGrid  = document.getElementById('productGrid');

searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    const q = searchInput.value.trim();
    if (q.length < 1) { showGridState('search'); return; }
    showGridState('loading');
    searchTimeout = setTimeout(() => fetchProducts(q), 280);
});

// Barcode: trigger on Enter key (scanner sends Enter after barcode)
barcodeInput.addEventListener('keydown', async (e) => {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const q = barcodeInput.value.trim();
    if (!q) return;
    const results = await fetchProducts(q, true);
    barcodeInput.value = '';
    barcodeInput.focus();
    // If exactly one product found via barcode, auto-add it
    if (results && results.length === 1) {
        addToCart(results[0]);
        showGridState('search');
    }
});

async function fetchProducts(q, silent = false) {
    if (!silent) showGridState('loading');
    try {
        const res = await fetch(`{{ route('cashier.pos.search') }}?q=${encodeURIComponent(q)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const products = await res.json();
        if (!silent) renderProducts(products);
        return products;
    } catch(e) {
        if (!silent) showGridState('error');
    }
}

function renderProducts(products) {
    if (!products.length) { showGridState('empty'); return; }

    productGrid.innerHTML = products.map(p => `
        <div class="product-card ${p.stock_quantity <= 0 ? 'out-of-stock' : ''}"
             onclick="addToCart(${JSON.stringify(p).replace(/"/g, '&quot;')})">
            ${p.image_url
                ? `<img src="${p.image_url}" alt="${p.name}" class="prod-img">`
                : `<div class="prod-img-ph"><i class="bi bi-image"></i></div>`
            }
            <div class="prod-name">${p.name}</div>
            <div class="prod-price">$${p.sale_price.toFixed(2)}</div>
            <div class="prod-sku">${p.sku}</div>
            <span class="prod-stock ${p.stock_quantity <= 0 ? 'stock-out' : p.stock_quantity <= 10 ? 'stock-low' : 'stock-ok'}">
                ${p.stock_quantity <= 0 ? 'Out of Stock' : 'Qty: ' + p.stock_quantity}
            </span>
        </div>
    `).join('');
}

function showGridState(type) {
    const states = {
        search:  '<i class="bi bi-search"></i><p>Search or scan to find products</p>',
        loading: '<i class="bi bi-arrow-repeat" style="animation:spin .7s linear infinite;"></i><p>Searching...</p>',
        empty:   '<i class="bi bi-inbox"></i><p>No products found</p>',
        error:   '<i class="bi bi-exclamation-circle" style="color:#ef4444"></i><p>Search failed. Try again.</p>',
    };
    productGrid.innerHTML = `<div class="prod-state">${states[type]}</div>`;
}

/* ═══════════════════════════════════════════════════════════
   CART LOGIC
═══════════════════════════════════════════════════════════ */
function addToCart(product) {
    const existing = cart.find(i => i.id === product.id);
    if (existing) {
        if (existing.qty >= product.stock_quantity) {
            showToast(`Only ${product.stock_quantity} in stock`, 'warning');
            return;
        }
        existing.qty++;
    } else {
        if (product.stock_quantity <= 0) { showToast('Out of stock', 'error'); return; }
        cart.push({ ...product, qty: 1 });
    }
    renderCart();
    showToast(`${product.name} added`, 'success');
}

function removeFromCart(productId) {
    cart = cart.filter(i => i.id !== productId);
    renderCart();
}

function updateQty(productId, delta) {
    const item = cart.find(i => i.id === productId);
    if (!item) return;
    const newQty = item.qty + delta;
    if (newQty <= 0)                      { removeFromCart(productId); return; }
    if (newQty > item.stock_quantity)     { showToast(`Max stock: ${item.stock_quantity}`, 'warning'); return; }
    item.qty = newQty;
    renderCart();
}

function setQty(productId, value) {
    const item = cart.find(i => i.id === productId);
    if (!item) return;
    const qty = parseInt(value) || 1;
    if (qty <= 0)                    { removeFromCart(productId); return; }
    if (qty > item.stock_quantity)   { showToast(`Max stock: ${item.stock_quantity}`, 'warning'); return; }
    item.qty = qty;
    renderCart();
}

function clearCart() {
    if (!cart.length) return;
    if (!confirm('Clear the entire cart?')) return;
    cart = [];
    document.getElementById('discountInput').value = '0';
    renderCart();
}

/* ═══════════════════════════════════════════════════════════
   RENDER CART
═══════════════════════════════════════════════════════════ */
function renderCart() {
    const container = document.getElementById('cartItems');
    const emptyEl   = document.getElementById('cartEmpty');

    if (!cart.length) {
        container.innerHTML = '';
        container.appendChild(createEmptyState());
        document.getElementById('cartCount').textContent = '0';
        document.getElementById('completeSaleBtn').disabled = true;
        updateTotals();
        return;
    }

    document.getElementById('completeSaleBtn').disabled = false;

    container.innerHTML = cart.map(item => `
        <div class="cart-item" id="cart-item-${item.id}">
            <div class="cart-item-info">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-meta">${item.sku} &middot; ${item.unit.toUpperCase()}</div>
            </div>
            <div class="qty-controls">
                <button class="qty-btn" onclick="updateQty(${item.id}, -1)">−</button>
                <input type="number" class="qty-input" value="${item.qty}" min="1"
                    max="${item.stock_quantity}"
                    onchange="setQty(${item.id}, this.value)"
                    onclick="this.select()">
                <button class="qty-btn" onclick="updateQty(${item.id}, 1)">+</button>
            </div>
            <div class="cart-item-price">$${(item.sale_price * item.qty).toFixed(2)}</div>
            <button class="btn-remove-item" onclick="removeFromCart(${item.id})" title="Remove">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>
    `).join('');

    document.getElementById('cartCount').textContent = cart.reduce((s, i) => s + i.qty, 0);
    updateTotals();
}

function createEmptyState() {
    const el = document.createElement('div');
    el.className = 'cart-empty';
    el.id = 'cartEmpty';
    el.innerHTML = '<i class="bi bi-cart-x"></i><p>Cart is empty<br><small>Tap a product to add</small></p>';
    return el;
}

/* ═══════════════════════════════════════════════════════════
   TOTALS
═══════════════════════════════════════════════════════════ */
function updateTotals() {
    const subtotal = cart.reduce((s, i) => s + (i.sale_price * i.qty), 0);
    const discount = Math.min(parseFloat(document.getElementById('discountInput').value) || 0, subtotal);
    const total    = Math.max(0, subtotal - discount);

    document.getElementById('cartSubtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('cartTotal').textContent    = '$' + total.toFixed(2);

    // Auto-fill paid amount for card
    if (paymentMethod === 'card') {
        document.getElementById('paidInput').value = total.toFixed(2);
    }
    updateChange();
}

function updateChange() {
    const total = parseFloat(document.getElementById('cartTotal').textContent.replace('$', '')) || 0;
    const paid  = parseFloat(document.getElementById('paidInput').value) || 0;
    const change = paid - total;

    const el  = document.getElementById('changeAmount');
    const box = document.getElementById('changeDisplay');
    el.textContent = '$' + Math.abs(change).toFixed(2);
    box.classList.toggle('change-neg', change < 0);
    if (change < 0) {
        el.textContent = '-$' + Math.abs(change).toFixed(2);
    }
}

/* ═══════════════════════════════════════════════════════════
   PAYMENT METHOD
═══════════════════════════════════════════════════════════ */
function selectPayment(method) {
    paymentMethod = method;
    document.querySelectorAll('.pay-method-btn').forEach(b => {
        b.classList.toggle('selected', b.dataset.method === method);
    });
    const paidRow = document.getElementById('paidAmountRow');
    if (method === 'card') {
        paidRow.style.display = 'none';
        const total = parseFloat(document.getElementById('cartTotal').textContent.replace('$', '')) || 0;
        document.getElementById('paidInput').value = total.toFixed(2);
    } else {
        paidRow.style.display = 'flex';
    }
    updateChange();
}

/* ═══════════════════════════════════════════════════════════
   CUSTOMER
═══════════════════════════════════════════════════════════ */
function toggleCustomer() {
    customerVisible = !customerVisible;
    document.getElementById('customerSection').style.display = customerVisible ? 'flex' : 'none';
    document.getElementById('toggleCustomerBtn').innerHTML = customerVisible
        ? '<i class="bi bi-x-circle"></i> Hide customer info'
        : '<i class="bi bi-person-plus"></i> Add customer info';
}

/* ═══════════════════════════════════════════════════════════
   COMPLETE SALE
═══════════════════════════════════════════════════════════ */
async function completeSale() {
    if (!cart.length) return;

    const total = parseFloat(document.getElementById('cartTotal').textContent.replace('$', '')) || 0;
    const paid  = parseFloat(document.getElementById('paidInput').value) || 0;
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;

    if (paymentMethod === 'cash' && paid < total) {
        showToast('Paid amount is less than total!', 'error'); return;
    }

    const btn = document.getElementById('completeSaleBtn');
    const label = document.getElementById('completeSaleLabel');
    btn.disabled = true;
    label.innerHTML = '<span class="spinner-sm"></span> Processing...';

    const payload = {
        cart: cart.map(i => ({
            product_id:      i.id,
            quantity:        i.qty,
            unit_price:      i.sale_price,
            discount_amount: 0,
        })),
        payment_method:  paymentMethod,
        paid_amount:     paymentMethod === 'card' ? total : paid,
        discount_amount: discount,
        customer_name:   document.getElementById('customerName').value.trim() || 'Walk-in Customer',
        customer_phone:  document.getElementById('customerPhone').value.trim() || null,
    };

    try {
        const res  = await fetch('{{ route('cashier.pos.complete-sale') }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body:    JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.success) {
            showReceiptModal(data);
        } else {
            showToast(data.message || 'Sale failed. Please try again.', 'error');
            btn.disabled = false;
            label.innerHTML = '<i class="bi bi-check-circle"></i> Complete Sale';
        }
    } catch(e) {
        showToast('Network error. Please try again.', 'error');
        btn.disabled = false;
        label.innerHTML = '<i class="bi bi-check-circle"></i> Complete Sale';
    }
}

/* ═══════════════════════════════════════════════════════════
   RECEIPT MODAL
═══════════════════════════════════════════════════════════ */
const receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));

function showReceiptModal(data) {
    document.getElementById('receiptInvoice').textContent = data.invoice_number;
    document.getElementById('receiptTotal').textContent   = '$' + data.total_amount;
    document.getElementById('receiptPaid').textContent    = '$' + data.paid_amount;
    document.getElementById('receiptChange').textContent  = '$' + data.change_amount;
    document.getElementById('receiptItems').textContent   = `${data.items_count} item(s) sold`;
    // Wire up the print receipt button — append ?print=1 for auto-print
    document.getElementById('printReceiptBtn').href = data.receipt_url + '?print=1';
    receiptModal.show();
}

function closeReceipt() {
    receiptModal.hide();
    // Reset cart
    cart = [];
    paymentMethod = 'cash';
    document.getElementById('discountInput').value = '0';
    document.getElementById('paidInput').value     = '';
    document.getElementById('customerName').value  = '';
    document.getElementById('customerPhone').value = '';
    if (customerVisible) toggleCustomer();
    selectPayment('cash');
    renderCart();
    // Reset buttons
    document.getElementById('completeSaleBtn').disabled = false;
    document.getElementById('completeSaleLabel').innerHTML = '<i class="bi bi-check-circle"></i> Complete Sale';
    // Refocus search
    searchInput.value = '';
    showGridState('search');
    searchInput.focus();
}

/* ═══════════════════════════════════════════════════════════
   TOAST NOTIFICATIONS
═══════════════════════════════════════════════════════════ */
function showToast(message, type = 'success') {
    const colors = { success: '#10b981', error: '#ef4444', warning: '#f59e0b' };
    const icons  = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill' };

    const toast = document.createElement('div');
    toast.style.cssText = `
        position:fixed; bottom:24px; right:24px; z-index:9999;
        background:#fff; border-radius:10px; padding:12px 18px;
        box-shadow:0 8px 24px rgba(0,0,0,.15); display:flex; align-items:center; gap:10px;
        border-left: 4px solid ${colors[type]}; font-size:14px; font-weight:500;
        animation: slideToast .2s ease; max-width: 280px;
    `;
    toast.innerHTML = `<i class="bi ${icons[type]}" style="color:${colors[type]};font-size:18px;"></i>${message}`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.transition = 'opacity .3s, transform .3s';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

// Initial render
renderCart();
</script>
@endpush

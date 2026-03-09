@extends('layouts.admin')

@section('title', 'New Purchase')

@push('styles')
<style>
    .form-section {
        background: #fff; border: 1px solid #e5e7eb;
        border-radius: 12px; overflow: hidden; margin-bottom: 20px;
    }
    .form-section-header {
        background: #f9fafb; border-bottom: 1px solid #e5e7eb;
        padding: 12px 20px; font-size: 11px; font-weight: 700;
        color: #6b7280; text-transform: uppercase; letter-spacing: 1px;
        display: flex; align-items: center; gap: 8px;
    }
    .form-section-body { padding: 20px; }

    .form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
    .fg { display: flex; flex-direction: column; gap: 6px; }
    .fg label { font-size: 12px; font-weight: 600; color: #374151; }
    .fg .hint { font-size: 11px; color: #9ca3af; }
    .required::after { content: ' *'; color: #ef4444; }

    /* ── Product rows ── */
    .product-rows-wrap { min-height: 60px; }

    .product-row {
        display: grid;
        grid-template-columns: 2.5fr 1fr 1fr 1fr 38px;
        gap: 10px; align-items: start;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
        animation: fadeIn .2s ease;
    }
    .product-row:last-child { border-bottom: none; }
    @keyframes fadeIn { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:none; } }

    .product-row .col-header {
        font-size: 10px; font-weight: 700; color: #9ca3af;
        text-transform: uppercase; letter-spacing: .7px; padding-bottom: 4px;
    }

    .btn-remove-row {
        background: #fee2e2; color: #991b1b; border: none;
        border-radius: 8px; width: 34px; height: 38px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 15px; margin-top: 22px;
        transition: background .15s;
    }
    .btn-remove-row:hover { background: #fecaca; }

    .btn-add-row {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--pos-primary-lt); color: var(--pos-primary);
        border: 1.5px dashed var(--pos-primary);
        border-radius: 10px; padding: 10px 18px;
        font-size: 13px; font-weight: 600; cursor: pointer;
        transition: background .15s; margin-top: 8px; width: 100%;
        justify-content: center;
    }
    .btn-add-row:hover { background: #d1eff5; }

    /* ── Summary bar ── */
    .summary-bar {
        background: #111827; color: #fff;
        border-radius: 12px; padding: 16px 22px;
        display: flex; align-items: center; justify-content: space-between;
        gap: 16px; flex-wrap: wrap;
        position: sticky; bottom: 16px; z-index: 10;
        box-shadow: 0 8px 32px rgba(0,0,0,.2);
    }
    .summary-bar .items-count { font-size: 13px; color: #9ca3af; }
    .summary-bar .total-display {
        font-size: 22px; font-weight: 800; color: #fff;
    }
    .summary-bar .total-display span { color: #10b981; }
    .btn-submit {
        background: #10b981; color: #fff; border: none;
        border-radius: 10px; padding: 12px 28px;
        font-size: 15px; font-weight: 700; cursor: pointer;
        display: flex; align-items: center; gap: 8px;
        transition: opacity .15s;
    }
    .btn-submit:hover { opacity: .88; }
    .btn-submit:disabled { opacity: .5; cursor: not-allowed; }

    /* product select search wrapper */
    .select-wrap { position: relative; }
    .select-search {
        width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px;
        padding: 8px 12px; font-size: 13px; font-family: 'Inter', sans-serif;
        color: #111827; cursor: pointer; background: #fff;
        -webkit-appearance: none; appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236b7280' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 10px center;
        padding-right: 30px;
    }
    .select-search:focus { outline: none; border-color: var(--pos-primary); box-shadow: 0 0 0 3px rgba(26,107,124,.1); }

    .line-total-display {
        background: #f9fafb; border: 1.5px solid #e5e7eb;
        border-radius: 8px; padding: 8px 12px; font-size: 14px;
        font-weight: 700; color: #111827; margin-top: 22px;
        text-align: right;
    }

    /* error styles */
    .error-msg { font-size: 12px; color: #ef4444; margin-top: 4px; }
</style>
@endpush

@push('scripts')
<script>
const PRODUCTS = @json($products);

let rowIndex = 0;

function formatCurrency(n) {
    return 'Rs. ' + parseFloat(n || 0).toFixed(2);
}

function recalcRow(idx) {
    const qty  = parseFloat(document.getElementById(`qty_${idx}`).value)  || 0;
    const cost = parseFloat(document.getElementById(`cost_${idx}`).value) || 0;
    document.getElementById(`line_${idx}`).textContent = formatCurrency(qty * cost);
    recalcGrand();
}

function recalcGrand() {
    let grand = 0;
    const rows = document.querySelectorAll('.product-row[data-idx]');
    rows.forEach(row => {
        const idx  = row.dataset.idx;
        const qty  = parseFloat(document.getElementById(`qty_${idx}`)?.value)  || 0;
        const cost = parseFloat(document.getElementById(`cost_${idx}`)?.value) || 0;
        grand += qty * cost;
        document.getElementById(`line_${idx}`).textContent = formatCurrency(qty * cost);
    });
    document.getElementById('grandTotal').textContent = formatCurrency(grand);
    document.getElementById('rowCount').textContent   = rows.length;
    document.getElementById('submitBtn').disabled     = rows.length === 0;
}

function removeRow(idx) {
    const row = document.querySelector(`.product-row[data-idx="${idx}"]`);
    if (row) { row.remove(); recalcGrand(); }
}

function onProductChange(idx) {
    const pid     = parseInt(document.getElementById(`prod_${idx}`).value);
    const product = PRODUCTS.find(p => p.id === pid);
    if (product) {
        document.getElementById(`cost_${idx}`).value = parseFloat(product.cost_price).toFixed(2);
        recalcRow(idx);
    }
}

function addRow() {
    const idx       = rowIndex++;
    const container = document.getElementById('productRows');

    const opts = PRODUCTS.map(p =>
        `<option value="${p.id}">${p.name} \u2014 ${p.sku} (Stock: ${p.stock_quantity})</option>`
    ).join('');

    const html = `
    <div class="product-row" data-idx="${idx}">
        <div class="fg">
            <label style="font-size:11px;color:#6b7280;">Product</label>
            <select id="prod_${idx}" name="items[${idx}][product_id]"
                    class="select-search" required
                    onchange="onProductChange(${idx})">
                <option value="">&mdash; Select product &mdash;</option>
                ${opts}
            </select>
        </div>
        <div class="fg">
            <label style="font-size:11px;color:#6b7280;">Qty</label>
            <input type="number" id="qty_${idx}" name="items[${idx}][quantity]"
                   class="pos-input" min="1" value="1" style="text-align:right;" required
                   oninput="recalcRow(${idx})">
        </div>
        <div class="fg">
            <label style="font-size:11px;color:#6b7280;">Unit Cost (PKR)</label>
            <input type="number" id="cost_${idx}" name="items[${idx}][unit_cost]"
                   class="pos-input" min="0" step="0.01" value="0.00" style="text-align:right;" required
                   oninput="recalcRow(${idx})">
        </div>
        <div class="fg">
            <label style="font-size:11px;color:#6b7280;">Line Total</label>
            <div class="line-total-display" id="line_${idx}">Rs. 0.00</div>
        </div>
        <button type="button" class="btn-remove-row" onclick="removeRow(${idx})" title="Remove">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>`;

    container.insertAdjacentHTML('beforeend', html);
    document.getElementById(`prod_${idx}`).focus();
    recalcGrand();
}

document.getElementById('addRowBtn').addEventListener('click', addRow);
addRow(); // Start with one row
</script>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between mb-3">
    <nav style="font-size:13px; color:#9ca3af;">
        <a href="{{ route('admin.purchases.index') }}" style="color:var(--pos-primary);text-decoration:none;font-weight:600;">
            <i class="bi bi-arrow-left"></i> Purchases
        </a>
        <span class="mx-2">·</span>
        <span style="color:#374151;font-weight:600;">New Purchase Order</span>
    </nav>
</div>

@if($errors->any())
<div class="pos-alert pos-alert-error mb-3">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <div>
        <strong>Please fix the following:</strong>
        <ul class="mb-0 mt-1" style="font-size:13px;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<form method="POST" action="{{ route('admin.purchases.store') }}" id="purchaseForm">
    @csrf

    {{-- ── Supplier Info ─────────────────────────────────── --}}
    <div class="form-section">
        <div class="form-section-header"><i class="bi bi-building"></i> Supplier Information</div>
        <div class="form-section-body">
            <div class="form-grid">
                <div class="fg" style="grid-column: span 2;">
                    <label class="required">Supplier Name</label>
                    <input type="text" name="supplier_name" class="pos-input"
                        value="{{ old('supplier_name') }}"
                        placeholder="e.g. ABC Hardware Distributors" required>
                </div>
                <div class="fg">
                    <label>Supplier Phone</label>
                    <input type="text" name="supplier_phone" class="pos-input"
                        value="{{ old('supplier_phone') }}" placeholder="+1 555-0100">
                </div>
                <div class="fg">
                    <label class="required">Payment Method</label>
                    <select name="payment_method" class="pos-input" required>
                        <option value="cash"   {{ old('payment_method') === 'cash'   ? 'selected' : '' }}>💵 Cash</option>
                        <option value="card"   {{ old('payment_method') === 'card'   ? 'selected' : '' }}>💳 Card</option>
                        <option value="credit" {{ old('payment_method') === 'credit' ? 'selected' : '' }}>📋 Credit / Net Terms</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Received Date</label>
                    <input type="date" name="received_at" class="pos-input"
                        value="{{ old('received_at', date('Y-m-d')) }}">
                </div>
                <div class="fg" style="grid-column: span 2;">
                    <label>Notes (optional)</label>
                    <textarea name="notes" class="pos-input" rows="2"
                        placeholder="e.g. Batch #44, partial delivery…">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Product Rows ──────────────────────────────────── --}}
    <div class="form-section">
        <div class="form-section-header"><i class="bi bi-box-seam"></i> Products to Stock In</div>
        <div class="form-section-body pb-2">

            {{-- Column headers --}}
            <div class="product-row" style="padding-top:0; border-bottom:1.5px solid #e5e7eb; animation:none;">
                <div class="col-header">Product</div>
                <div class="col-header" style="text-align:right;">Quantity</div>
                <div class="col-header" style="text-align:right;">Unit Cost (PKR)</div>
                <div class="col-header" style="text-align:right;">Line Total</div>
                <div></div>
            </div>

            {{-- Dynamic rows injected here --}}
            <div id="productRows" class="product-rows-wrap"></div>

            <button type="button" class="btn-add-row" id="addRowBtn">
                <i class="bi bi-plus-circle"></i> Add Product
            </button>

        </div>
    </div>

    {{-- ── Sticky summary bar ────────────────────────────── --}}
    <div class="summary-bar">
        <div>
            <div class="items-count"><span id="rowCount">0</span> product line(s)</div>
            <div class="total-display">Total Cost: <span id="grandTotal">Rs. 0.00</span></div>
        </div>
        <button type="submit" class="btn-submit" id="submitBtn" disabled>
            <i class="bi bi-arrow-down-circle"></i> Record Purchase & Update Stock
        </button>
    </div>

</form>


@endsection


@extends('layouts.admin')

@section('title', 'Edit Purchase ' . $purchase->reference_number)

@push('styles')
<style>
    .form-section {
        background: #fff; border: 1px solid #e5e7eb;
        border-radius: 12px; overflow: visible; margin-bottom: 20px;
    }
    .form-section-header {
        background: #f9fafb; border-bottom: 1px solid #e5e7eb;
        padding: 12px 20px; font-size: 11px; font-weight: 700;
        color: #6b7280; text-transform: uppercase; letter-spacing: 1px;
        display: flex; align-items: center; gap: 8px;
        border-top-left-radius: 12px; border-top-right-radius: 12px;
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
        grid-template-columns: 3.2fr 1fr 1.1fr 1.2fr 40px;
        gap: 12px; align-items: start;
        padding: 14px 0;
        border-bottom: 1px solid #f3f4f6;
        animation: fadeIn .2s ease;
    }
    .product-row:last-child { border-bottom: none; }
    @keyframes fadeIn { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:none; } }

    .product-row .col-header {
        font-size: 11px; font-weight: 700; color: #64748b;
        text-transform: uppercase; letter-spacing: .7px; padding-bottom: 4px;
    }

    .btn-remove-row {
        background: #fee2e2; color: #991b1b; border: none;
        border-radius: 8px; width: 36px; height: 46px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 15px; margin-top: 22px;
        transition: background .15s;
    }
    .btn-remove-row:hover { background: #fecaca; }

    .btn-add-row {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--pos-primary-lt); color: var(--pos-primary);
        border: 1.5px dashed var(--pos-primary);
        border-radius: 10px; padding: 12px 18px;
        font-size: 14px; font-weight: 700; cursor: pointer;
        transition: all .15s; margin-top: 10px; width: 100%;
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

    /* ── Big, Readable Product TomSelect Dropdown (POS Style) ── */
    .product-select-wrap { position: relative; width: 100%; }
    .product-select-wrap .ts-wrapper { width: 100%; }
    .product-select-wrap .ts-control {
        min-height: 46px !important;
        padding: 8px 14px !important;
        border-radius: 10px !important;
        border: 1.5px solid #cbd5e1 !important;
        font-size: 13.5px !important;
        background: #ffffff !important;
        transition: all 0.2s ease !important;
    }
    .product-select-wrap .ts-wrapper.focus .ts-control {
        border-color: #0f766e !important;
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.15) !important;
    }
    .product-select-wrap .ts-dropdown {
        min-width: 580px !important;
        max-height: 420px !important;
        border-radius: 14px !important;
        border: 1.5px solid #cbd5e1 !important;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18), 0 4px 14px rgba(15, 23, 42, 0.08) !important;
        overflow-y: auto !important;
        z-index: 1060 !important;
    }
    .ts-product-option {
        padding: 10px 14px !important;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background 0.12s;
    }
    .ts-product-option:hover, .ts-dropdown .active.ts-product-option {
        background: #f0fdfa !important;
    }
    .ts-prod-img {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        object-fit: cover;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        flex-shrink: 0;
    }
    .ts-prod-img-fallback {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 20px;
        flex-shrink: 0;
    }
    .ts-prod-name {
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.35;
    }
    .ts-prod-meta {
        font-size: 11px;
        color: #64748b;
    }
    .ts-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        letter-spacing: 0.2px;
    }
    .ts-badge-sku {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }
    .ts-badge-success {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .ts-badge-warning {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .ts-badge-danger {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    .ts-badge-sale {
        background: #f3e8ff;
        color: #7e22ce;
        border: 1px solid #e9d5ff;
    }
    .ts-price-label {
        font-size: 10px;
        text-transform: uppercase;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.5px;
    }
    .ts-price-val {
        font-size: 14.5px;
        font-weight: 800;
        color: #0f766e;
    }

    .pos-input-tall {
        height: 46px !important;
        font-size: 14px !important;
    }

    .line-total-display {
        background: #f9fafb; border: 1.5px solid #e5e7eb;
        border-radius: 8px; padding: 11px 12px; font-size: 14.5px;
        font-weight: 800; color: #111827; margin-top: 22px;
        text-align: right; height: 46px;
        display: flex; align-items: center; justify-content: flex-end;
    }
</style>
@endpush

@push('scripts')
<script>
const PRODUCTS = @json($products);
const INITIAL_ITEMS = @json($purchase->items);

let rowIndex = 0;

function formatCurrency(n) {
    return 'Rs. ' + parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function recalcRow(idx) {
    const qty  = parseFloat(document.getElementById(`qty_${idx}`)?.value)  || 0;
    const cost = parseFloat(document.getElementById(`cost_${idx}`)?.value) || 0;
    const lineEl = document.getElementById(`line_${idx}`);
    if (lineEl) {
        lineEl.textContent = formatCurrency(qty * cost);
    }
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
        const lineEl = document.getElementById(`line_${idx}`);
        if (lineEl) {
            lineEl.textContent = formatCurrency(qty * cost);
        }
    });
    document.getElementById('grandTotal').textContent = formatCurrency(grand);
    document.getElementById('rowCount').textContent   = rows.length;
    document.getElementById('submitBtn').disabled     = rows.length === 0;
}

function removeRow(idx) {
    const prodEl = document.getElementById(`prod_${idx}`);
    if (prodEl && prodEl.tomselect) {
        prodEl.tomselect.destroy();
    }
    const el = document.getElementById(`row_${idx}`);
    if (el) el.remove();
    recalcGrand();
}

function onProductChange(idx, productId) {
    const prod = PRODUCTS.find(p => String(p.id) === String(productId));
    if (!prod) return;

    const unitEl = document.getElementById(`unit_${idx}`);
    if (unitEl) unitEl.textContent = prod.unit || 'Pcs';

    const costEl = document.getElementById(`cost_${idx}`);
    if (costEl && (!costEl.value || parseFloat(costEl.value) === 0)) {
        costEl.value = parseFloat(prod.cost_price || 0);
    }

    recalcRow(idx);
}

function addRow(initialData = null) {
    const idx = rowIndex++;
    const container = document.getElementById('productRows');

    const defaultQty = initialData ? initialData.quantity : 1;
    const defaultCost = initialData ? initialData.unit_cost : '0';
    const defaultUnit = initialData ? (initialData.product_unit || 'Pcs') : 'Pcs';
    const initialProductId = initialData ? initialData.product_id : '';

    const html = `
    <div class="product-row" id="row_${idx}" data-idx="${idx}">
        <div class="fg">
            <label style="font-size:11px;color:#6b7280;">Product <span class="text-danger">*</span></label>
            <div class="product-select-wrap">
                <select name="items[${idx}][product_id]" id="prod_${idx}" required></select>
            </div>
        </div>
        <div class="fg">
            <label style="font-size:11px;color:#6b7280;text-align:right;">Quantity (<span id="unit_${idx}">${defaultUnit}</span>) <span class="text-danger">*</span></label>
            <input type="number" name="items[${idx}][quantity]" id="qty_${idx}"
                   class="pos-input pos-input-tall" min="0.001" step="any" value="${defaultQty}" style="text-align:right; margin-top:0;" required
                   oninput="recalcRow(${idx})">
        </div>
        <div class="fg">
            <label style="font-size:11px;color:#6b7280;text-align:right;">Unit Cost (Rs.) <span class="text-danger">*</span></label>
            <input type="number" name="items[${idx}][unit_cost]" id="cost_${idx}"
                   class="pos-input pos-input-tall" min="0" step="any" value="${defaultCost}" style="text-align:right; margin-top:0;" required
                   oninput="recalcRow(${idx})">
        </div>
        <div class="fg">
            <label style="font-size:11px;color:#6b7280;">Line Total</label>
            <div class="line-total-display" id="line_${idx}" style="margin-top:0;">Rs. 0.00</div>
        </div>
        <button type="button" class="btn-remove-row" onclick="removeRow(${idx})" title="Remove" style="margin-top:23px;">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>`;

    container.insertAdjacentHTML('beforeend', html);

    const newSelect = document.getElementById(`prod_${idx}`);
    if (newSelect) {
        const ts = new TomSelect(newSelect, {
            options: PRODUCTS,
            valueField: 'id',
            labelField: 'name',
            searchField: ['name', 'sku'],
            placeholder: '🔍 Type product name or SKU...',
            maxOptions: 500,
            plugins: ['dropdown_input', 'clear_button'],
            render: {
                option: function(data, escape) {
                    const imgHtml = data.image_url 
                        ? `<img src="${escape(data.image_url)}" class="ts-prod-img" onerror="this.outerHTML='<div class=\\\'ts-prod-img-fallback\\\'><i class=\\\'bi bi-box-seam\\\'></i></div>'"/>` 
                        : `<div class="ts-prod-img-fallback"><i class="bi bi-box-seam"></i></div>`;
                    
                    const stockQty = parseFloat(data.stock_quantity || 0).toFixed(2);
                    const stockBadgeClass = parseFloat(data.stock_quantity || 0) <= 0 
                        ? 'ts-badge-danger' 
                        : (parseFloat(data.stock_quantity || 0) <= 5 ? 'ts-badge-warning' : 'ts-badge-success');
                    
                    return `
                        <div class="ts-product-option d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3" style="min-width: 0; flex: 1;">
                                ${imgHtml}
                                <div class="ts-prod-info" style="min-width: 0; flex: 1;">
                                    <div class="ts-prod-name text-truncate" title="${escape(data.name)}">${escape(data.name)}</div>
                                    <div class="ts-prod-meta d-flex align-items-center gap-2 mt-1 flex-wrap">
                                        <span class="ts-badge ts-badge-sku"><i class="bi bi-upc-scan me-1"></i>${escape(data.sku)}</span>
                                        <span class="ts-badge ${stockBadgeClass}"><i class="bi bi-boxes me-1"></i>Stock: ${stockQty} ${escape(data.unit || 'Pcs')}</span>
                                        ${data.sale_price ? `<span class="ts-badge ts-badge-sale">Sale: Rs. ${parseFloat(data.sale_price).toFixed(2)}</span>` : ''}
                                    </div>
                                </div>
                            </div>
                            <div class="ts-prod-price-box text-end ms-3 flex-shrink-0">
                                <div class="ts-price-label">Cost Price</div>
                                <div class="ts-price-val">Rs. ${parseFloat(data.cost_price || 0).toFixed(2)}</div>
                            </div>
                        </div>
                    `;
                },
                item: function(data, escape) {
                    return `<div class="d-flex align-items-center gap-2 py-1">
                        <span class="fw-bold text-dark">${escape(data.name)}</span>
                        <span class="badge bg-light text-muted border">${escape(data.sku)}</span>
                        <span class="fw-semibold small" style="color:#0f766e;">(Cost: Rs. ${parseFloat(data.cost_price || 0).toFixed(2)})</span>
                    </div>`;
                },
                no_results: function(data, escape) {
                    return '<div class="no-results p-3 text-muted text-center"><i class="bi bi-search me-1"></i> No matching products found for "' + escape(data.input) + '"</div>';
                }
            },
            onChange: function(value) {
                onProductChange(idx, value);
            }
        });

        if (initialProductId) {
            ts.setValue(initialProductId);
        }
    }

    recalcRow(idx);
}

function onSupplierSelected(select) {
    const opt = select.options[select.selectedIndex];
    if (opt && opt.value) {
        document.getElementById('supplierIdInput').value = opt.value;
        document.getElementById('supplierNameInput').value = opt.dataset.name || '';
        document.getElementById('supplierPhoneInput').value = opt.dataset.phone || '';
    } else {
        document.getElementById('supplierIdInput').value = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const supSelect = document.getElementById('supplierSelect');
    if (supSelect && window.initTomSelect) {
        window.initTomSelect(supSelect, {
            placeholder: '🔍 Search registered supplier by name...',
            onChange: function() {
                onSupplierSelected(supSelect);
            }
        });
    }

    // Populate existing items
    if (INITIAL_ITEMS && INITIAL_ITEMS.length > 0) {
        INITIAL_ITEMS.forEach(item => addRow(item));
    } else {
        addRow();
    }
});

document.getElementById('addRowBtn').addEventListener('click', () => addRow());
</script>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between mb-3">
    <nav style="font-size:13px; color:#9ca3af;">
        <a href="{{ route('admin.purchases.index') }}" style="color:var(--pos-primary);text-decoration:none;font-weight:600;">
            <i class="bi bi-arrow-left"></i> Purchases
        </a>
        <span class="mx-2">·</span>
        <a href="{{ route('admin.purchases.show', $purchase) }}" style="color:var(--pos-primary);text-decoration:none;font-weight:600;">
            {{ $purchase->reference_number }}
        </a>
        <span class="mx-2">·</span>
        <span style="color:#374151;font-weight:600;">Edit</span>
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

<form method="POST" action="{{ route('admin.purchases.update', $purchase) }}" id="purchaseForm">
    @csrf
    @method('PUT')

    {{-- ── Supplier Info ─────────────────────────────────── --}}
    <div class="form-section">
        <div class="form-section-header"><i class="bi bi-building"></i> Supplier Information</div>
        <div class="form-section-body">
            <div class="form-grid">
                @if(isset($suppliers) && $suppliers->isNotEmpty())
                <div class="fg" style="grid-column: span 2;">
                    <label>Select Registered Supplier <small class="text-muted fw-normal">(optional)</small></label>
                    <select id="supplierSelect" class="pos-input" onchange="onSupplierSelected(this)">
                        <option value="">-- Choose Existing Supplier --</option>
                        @foreach($suppliers as $s)
                            @php
                                $compName = !empty($s->company_name) && strcasecmp(trim($s->company_name), trim($s->name)) !== 0 ? ' (' . $s->company_name . ')' : '';
                                $isSelected = old('supplier_id', $purchase->supplier_id) == $s->id;
                            @endphp
                            <option value="{{ $s->id }}"
                                {{ $isSelected ? 'selected' : '' }}
                                data-name="{{ $s->name }}"
                                data-phone="{{ $s->phone }}"
                                data-balance="{{ $s->pending_balance }}">
                                {{ $s->name }}{{ $compName }} — Due: {{ pkr($s->pending_balance, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <input type="hidden" name="supplier_id" id="supplierIdInput" value="{{ old('supplier_id', $purchase->supplier_id) }}">

                <div class="fg">
                    <label class="required">Supplier Name</label>
                    <input type="text" name="supplier_name" id="supplierNameInput" class="pos-input"
                        value="{{ old('supplier_name', $purchase->supplier_name) }}"
                        placeholder="e.g. Master Steel & Hardware" required>
                </div>
                <div class="fg">
                    <label>Supplier Phone</label>
                    <input type="text" name="supplier_phone" id="supplierPhoneInput" class="pos-input"
                        value="{{ old('supplier_phone', $purchase->supplier_phone) }}" placeholder="03001234567">
                </div>
                <div class="fg">
                    <label class="required">Payment Method</label>
                    <select name="payment_method" class="pos-input" required>
                        <option value="" disabled {{ !old('payment_method', $purchase->payment_method) ? 'selected' : '' }}>-- Select Payment Method --</option>
                        <option value="cash"   {{ old('payment_method', $purchase->payment_method) === 'cash'   ? 'selected' : '' }}>💵 Cash (Paid)</option>
                        <option value="card"   {{ old('payment_method', $purchase->payment_method) === 'card'   ? 'selected' : '' }}>💳 Card / Bank (Paid)</option>
                        <option value="credit" {{ old('payment_method', $purchase->payment_method) === 'credit' ? 'selected' : '' }}>📋 Credit / Add to Supplier Due</option>
                    </select>
                </div>
                <div class="fg">
                    <label>Received Date</label>
                    <input type="date" name="received_at" class="pos-input"
                        value="{{ old('received_at', $purchase->received_at ? \Carbon\Carbon::parse($purchase->received_at)->format('Y-m-d') : date('Y-m-d')) }}">
                </div>
                <div class="fg" style="grid-column: span 2;">
                    <label>Notes / Reference</label>
                    <input type="text" name="notes" class="pos-input" value="{{ old('notes', $purchase->notes) }}" placeholder="Optional order notes...">
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
                <div class="col-header">Product (Search & Select)</div>
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
        <button type="submit" class="btn-submit" id="submitBtn">
            <i class="bi bi-check2-circle"></i> Update Purchase & Sync Stock
        </button>
    </div>

</form>

@endsection

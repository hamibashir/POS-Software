@extends('layouts.admin')

@section('title', 'New Stock Return')

@push('styles')
<style>
    .form-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:24px; margin-bottom:24px; }
    .form-card-title { font-size:15px; font-weight:700; color:#111827; margin-bottom:18px; display:flex; align-items:center; gap:8px; border-bottom:1px solid #f3f4f6; padding-bottom:12px; }
    .form-grid-3 { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; }
    .form-label { font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; display:block; }
    .form-hint { font-size:11px; color:#9ca3af; margin-top:4px; }

    .items-table { width:100%; border-collapse:collapse; margin-top:12px; }
    .items-table thead th { background:#f9fafb; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#6b7280; padding:10px 12px; border-bottom:1px solid #e5e7eb; }
    .items-table tbody td { padding:10px 12px; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
    .btn-remove-row { background:#fee2e2; color:#b91c1c; border:none; border-radius:6px; width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; }
    .btn-remove-row:hover { background:#fecaca; }

    .summary-card { background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px; padding:20px; }
    .summary-row { display:flex; justify-content:space-between; align-items:center; padding:8px 0; font-size:14px; }
    .summary-row.total { border-top:2px solid #111827; margin-top:8px; padding-top:12px; font-size:18px; font-weight:800; color:#111827; }

    .stock-pill { font-size:11px; font-weight:600; border-radius:12px; padding:2px 8px; display:inline-block; }
    .stock-ok   { background:#d1fae5; color:#065f46; }
    .stock-zero { background:#fee2e2; color:#991b1b; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <nav style="font-size:13px; color:#9ca3af; margin-bottom:4px;">
            <a href="{{ route('admin.purchase-returns.index') }}" style="color:var(--pos-primary); text-decoration:none; font-weight:600;">
                <i class="bi bi-arrow-left"></i> Stock Returns
            </a>
            <span class="mx-2">·</span>
            <span>New Return</span>
        </nav>
        <h1 style="font-size:22px; font-weight:700; margin:0; color:#111827;">Create Stock Return</h1>
    </div>
</div>

@if($errors->any())
<div class="pos-alert pos-alert-error mb-4">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <div>
        <div style="font-weight:700;">Please resolve the following errors:</div>
        <ul style="margin:4px 0 0; padding-left:18px; font-size:13px;">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<form method="POST" action="{{ route('admin.purchase-returns.store') }}" id="returnForm">
    @csrf

    {{-- Header & Supplier Details --}}
    <div class="form-card">
        <div class="form-card-title">
            <i class="bi bi-truck text-primary"></i> Supplier & Linked Purchase
        </div>

        <div class="form-grid-3 mb-3">
            <div>
                <label class="form-label">Link to Existing Purchase (Optional)</label>
                <select name="purchase_id" id="purchaseSelect" class="pos-input">
                    <option value="">-- No link (Direct Return) --</option>
                    @foreach($recentPurchases as $p)
                    <option value="{{ $p->id }}"
                        data-supplier="{{ $p->supplier_name }}"
                        data-phone="{{ $p->supplier_phone }}"
                        data-items='@json($p->items)'
                        {{ (old('purchase_id', $selectedPurchase?->id) == $p->id) ? 'selected' : '' }}>
                        {{ $p->reference_number }} — {{ $p->supplier_name }} ({{ pkr($p->total_amount, 2) }})
                    </option>
                    @endforeach
                </select>
                <div class="form-hint">Selecting an order will auto-fill supplier and available items</div>
            </div>

            <div>
                <label class="form-label">Supplier Name <span style="color:#ef4444;">*</span></label>
                <input type="text" name="supplier_name" id="supplierName" class="pos-input" required
                    value="{{ old('supplier_name', $selectedPurchase?->supplier_name) }}" placeholder="e.g. Master Steel & Hardware">
            </div>

            <div>
                <label class="form-label">Supplier Phone</label>
                <input type="text" name="supplier_phone" id="supplierPhone" class="pos-input"
                    value="{{ old('supplier_phone', $selectedPurchase?->supplier_phone) }}" placeholder="e.g. 03001234567">
            </div>
        </div>

        <div class="form-grid-3">
            <div>
                <label class="form-label">Return Date <span style="color:#ef4444;">*</span></label>
                <input type="date" name="returned_at" class="pos-input" required
                    value="{{ old('returned_at', date('Y-m-d')) }}">
            </div>

            <div>
                <label class="form-label">Primary Return Reason</label>
                <select name="reason" class="pos-input">
                    <option value="Defective / Damaged" {{ old('reason') == 'Defective / Damaged' ? 'selected' : '' }}>Defective / Damaged</option>
                    <option value="Incorrect Item Received" {{ old('reason') == 'Incorrect Item Received' ? 'selected' : '' }}>Incorrect Item Received</option>
                    <option value="Excess / Overstock" {{ old('reason') == 'Excess / Overstock' ? 'selected' : '' }}>Excess / Overstock</option>
                    <option value="Expired / Shelf Life" {{ old('reason') == 'Expired / Shelf Life' ? 'selected' : '' }}>Expired / Shelf Life</option>
                    <option value="Other" {{ old('reason') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <input type="text" name="notes" class="pos-input" value="{{ old('notes') }}" placeholder="Any additional notes or invoice #">
            </div>
        </div>
    </div>

    {{-- Return Items --}}
    <div class="form-card">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="form-card-title mb-0" style="border-bottom:none; padding-bottom:0;">
                <i class="bi bi-box-seam text-danger"></i> Products to Return
            </div>
            <button type="button" id="btnAddRow" class="btn-pos-outline" style="font-size:13px; padding:6px 14px;">
                <i class="bi bi-plus-lg"></i> Add Product
            </button>
        </div>
        <p style="font-size:13px; color:#6b7280; margin-bottom:16px;">
            Returned products will be deducted from active stock and logged as a stock movement.
        </p>

        <div style="overflow-x:auto;">
            <table class="items-table" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width:35%;">Product</th>
                        <th style="width:15%;">Current Stock</th>
                        <th style="width:15%;">Unit Cost (Refund Rate)</th>
                        <th style="width:15%;">Return Qty</th>
                        <th style="width:15%; text-align:right;">Subtotal</th>
                        <th style="width:5%;"></th>
                    </tr>
                </thead>
                <tbody id="itemsContainer">
                    {{-- Rows rendered by JS --}}
                </tbody>
            </table>
        </div>
    </div>

    {{-- Payment & Refund --}}
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="form-card">
                <div class="form-card-title">
                    <i class="bi bi-cash-coin text-success"></i> Payment & Refund Details
                </div>
                <div class="form-grid-3 mb-3">
                    <div>
                        <label class="form-label">Refund Method <span style="color:#ef4444;">*</span></label>
                        <select name="refund_method" class="pos-input" required>
                            <option value="cash"   {{ old('refund_method') === 'cash'   ? 'selected' : '' }}>💵 Cash Refund</option>
                            <option value="card"   {{ old('refund_method') === 'card'   ? 'selected' : '' }}>💳 Bank / Card Refund</option>
                            <option value="credit" {{ old('refund_method') === 'credit' ? 'selected' : '' }}>📋 Credit Note / Deduct Supplier Balance</option>
                        </select>
                        <div class="form-hint">How the supplier is issuing the refund</div>
                    </div>

                    <div>
                        <label class="form-label">Refund Status <span style="color:#ef4444;">*</span></label>
                        <select name="refund_status" class="pos-input" required>
                            <option value="completed" {{ old('refund_status', 'completed') === 'completed' ? 'selected' : '' }}>Completed (Received)</option>
                            <option value="pending"   {{ old('refund_status') === 'pending'   ? 'selected' : '' }}>Pending (Awaiting Refund)</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Refund Amount (PKR)</label>
                        <input type="number" step="0.01" min="0" name="refund_amount" id="refundAmountInput" class="pos-input"
                            value="{{ old('refund_amount') }}" placeholder="Auto-calculated">
                        <div class="form-hint">Defaults to total return cost</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="summary-card">
                <div style="font-size:15px; font-weight:700; color:#111827; margin-bottom:14px;">Return Summary</div>
                <div class="summary-row">
                    <span style="color:#6b7280;">Total Items</span>
                    <span id="summaryTotalItems" style="font-weight:700;">0</span>
                </div>
                <div class="summary-row">
                    <span style="color:#6b7280;">Total Units Returning</span>
                    <span id="summaryTotalUnits" style="font-weight:700;">0 pcs</span>
                </div>
                <div class="summary-row total">
                    <span>TOTAL RETURN VALUE</span>
                    <span id="summaryTotalAmount" style="color:#b91c1c;">PKR 0.00</span>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn-pos justify-content-center py-2" style="font-size:15px;">
                        <i class="bi bi-check2-circle"></i> Confirm & Process Return
                    </button>
                    <a href="{{ route('admin.purchase-returns.index') }}" class="btn-pos-outline justify-content-center py-2 text-decoration-none">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- JSON catalog data for JS --}}
<script>
    const catalogProducts = @json($products);
    const preloadedPurchase = @json($selectedPurchase);

    let rowCount = 0;

    function addRow(data = {}) {
        rowCount++;
        const idx = rowCount;
        const container = document.getElementById('itemsContainer');

        const tr = document.createElement('tr');
        tr.id = `row-${idx}`;

        let options = '<option value="">-- Choose Product --</option>';
        catalogProducts.forEach(p => {
            const isSel = data.product_id == p.id ? 'selected' : '';
            options += `<option value="${p.id}" data-cost="${p.cost_price}" data-stock="${p.stock_quantity}" data-unit="${p.unit}" ${isSel}>${p.name} (${p.sku}) — Stock: ${p.stock_quantity}</option>`;
        });

        tr.innerHTML = `
            <td>
                <select name="items[${idx}][product_id]" class="pos-input product-select" required data-row="${idx}">
                    ${options}
                </select>
                <input type="hidden" name="items[${idx}][purchase_item_id]" value="${data.purchase_item_id || ''}">
            </td>
            <td>
                <span class="stock-pill stock-ok" id="stockBadge-${idx}">
                    ${data.stock_quantity !== undefined ? data.stock_quantity + ' ' + (data.unit || 'pcs') : '—'}
                </span>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${idx}][unit_cost]" class="pos-input unit-cost-input"
                    data-row="${idx}" value="${data.unit_cost !== undefined ? data.unit_cost : ''}" required placeholder="0.00">
            </td>
            <td>
                <input type="number" min="1" max="${data.stock_quantity || 999999}" name="items[${idx}][quantity]" class="pos-input qty-input"
                    data-row="${idx}" value="${data.quantity || 1}" required>
            </td>
            <td style="text-align:right; font-weight:700; color:#111827;" id="subtotal-${idx}">
                PKR 0.00
            </td>
            <td style="text-align:center;">
                <button type="button" class="btn-remove-row" onclick="removeRow(${idx})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        container.appendChild(tr);

        // Bind change events
        const select = tr.querySelector('.product-select');
        const costInput = tr.querySelector('.unit-cost-input');
        const qtyInput = tr.querySelector('.qty-input');

        select.addEventListener('change', function() {
            const opt = this.selectedOptions[0];
            const stock = opt.dataset.stock || 0;
            const cost = opt.dataset.cost || 0;
            const unit = opt.dataset.unit || 'pcs';

            const badge = document.getElementById(`stockBadge-${idx}`);
            badge.innerText = `${stock} ${unit}`;
            badge.className = stock > 0 ? 'stock-pill stock-ok' : 'stock-pill stock-zero';

            qtyInput.max = stock;
            if (!costInput.value) {
                costInput.value = cost;
            }
            updateRowSubtotal(idx);
        });

        costInput.addEventListener('input', () => updateRowSubtotal(idx));
        qtyInput.addEventListener('input', () => updateRowSubtotal(idx));

        if (data.product_id) {
            updateRowSubtotal(idx);
        }
    }

    function removeRow(idx) {
        const row = document.getElementById(`row-${idx}`);
        if (row) {
            row.remove();
            calculateTotals();
        }
    }

    function updateRowSubtotal(idx) {
        const row = document.getElementById(`row-${idx}`);
        if (!row) return;
        const cost = parseFloat(row.querySelector('.unit-cost-input').value) || 0;
        const qty = parseInt(row.querySelector('.qty-input').value) || 0;
        const subtotal = cost * qty;
        document.getElementById(`subtotal-${idx}`).innerText = `PKR ${subtotal.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}`;
        calculateTotals();
    }

    function calculateTotals() {
        let totalItems = 0;
        let totalUnits = 0;
        let totalAmount = 0;

        document.querySelectorAll('#itemsContainer tr').forEach(tr => {
            const qty = parseInt(tr.querySelector('.qty-input')?.value) || 0;
            const cost = parseFloat(tr.querySelector('.unit-cost-input')?.value) || 0;
            if (qty > 0) {
                totalItems++;
                totalUnits += qty;
                totalAmount += (qty * cost);
            }
        });

        document.getElementById('summaryTotalItems').innerText = totalItems;
        document.getElementById('summaryTotalUnits').innerText = `${totalUnits} pcs`;
        document.getElementById('summaryTotalAmount').innerText = `PKR ${totalAmount.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}`;

        // Update refund amount input if not manually modified
        const refundInput = document.getElementById('refundAmountInput');
        if (!refundInput.dataset.manual) {
            refundInput.value = totalAmount.toFixed(2);
        }
    }

    document.getElementById('refundAmountInput').addEventListener('input', function() {
        this.dataset.manual = 'true';
    });

    document.getElementById('btnAddRow').addEventListener('click', () => addRow());

    // Handle Linked Purchase dropdown selection
    document.getElementById('purchaseSelect').addEventListener('change', function() {
        const opt = this.selectedOptions[0];
        if (!opt.value) return;

        if (opt.dataset.supplier) {
            document.getElementById('supplierName').value = opt.dataset.supplier;
        }
        if (opt.dataset.phone) {
            document.getElementById('supplierPhone').value = opt.dataset.phone;
        }

        if (opt.dataset.items) {
            try {
                const items = JSON.parse(opt.dataset.items);
                document.getElementById('itemsContainer').innerHTML = '';
                items.forEach(it => {
                    const prod = catalogProducts.find(p => p.id == it.product_id);
                    addRow({
                        product_id: it.product_id,
                        purchase_item_id: it.id,
                        unit_cost: it.unit_cost,
                        quantity: Math.min(it.quantity, prod ? prod.stock_quantity : it.quantity),
                        stock_quantity: prod ? prod.stock_quantity : 0,
                        unit: it.product_unit
                    });
                });
            } catch(e) {
                console.error(e);
            }
        }
    });

    // Auto-populate from preloaded purchase if available
    window.addEventListener('DOMContentLoaded', () => {
        if (preloadedPurchase && preloadedPurchase.items && preloadedPurchase.items.length > 0) {
            preloadedPurchase.items.forEach(it => {
                const prod = catalogProducts.find(p => p.id == it.product_id);
                addRow({
                    product_id: it.product_id,
                    purchase_item_id: it.id,
                    unit_cost: it.unit_cost,
                    quantity: Math.min(it.quantity, prod ? prod.stock_quantity : it.quantity),
                    stock_quantity: prod ? prod.stock_quantity : 0,
                    unit: it.product_unit
                });
            });
        } else {
            addRow(); // Add one blank row
        }
    });
</script>

@endsection

@extends('layouts.admin')
@section('title', 'New Stock Adjustment')

@push('styles')
<style>
    .adj-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; max-width:680px; margin:0 auto; }
    .adj-header { background:#f9fafb; border-bottom:1px solid #e5e7eb; padding:14px 22px; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:1px; display:flex; align-items:center; gap:8px; }
    .adj-body { padding:24px 22px; }
    .fg { display:flex; flex-direction:column; gap:6px; margin-bottom:18px; }
    .fg label { font-size:13px; font-weight:600; color:#374151; }
    .fg label .req { color:#ef4444; }
    .fg .hint { font-size:12px; color:#9ca3af; }
    .type-toggle { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .type-opt { position:relative; }
    .type-opt input[type=radio] { position:absolute; opacity:0; width:0; height:0; }
    .type-opt label {
        display:flex; flex-direction:column; align-items:center; gap:6px;
        border:2px solid #e5e7eb; border-radius:12px; padding:16px 12px;
        cursor:pointer; transition:all .15s; text-align:center;
    }
    .type-opt input:checked + label { border-color:var(--pos-primary); background:var(--pos-primary-lt); }
    .type-opt .ico { font-size:28px; }
    .type-opt .lbl { font-size:14px; font-weight:700; }
    .type-opt .sub { font-size:11px; color:#9ca3af; }
    .stock-preview {
        background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px;
        padding:14px 18px; display:flex; justify-content:space-between; align-items:center;
        margin-bottom:18px; font-size:14px;
    }
    .stock-preview .current { font-size:22px; font-weight:800; color:#111827; }
    .stock-preview .arrow { font-size:22px; color:#9ca3af; }
    .stock-preview .result { font-size:22px; font-weight:800; }
</style>
@endpush

@push('scripts')
<script>
const products = @json($products);

function onProductChange() {
    const pid = parseInt(document.getElementById('product_id').value);
    const p = products.find(x => x.id === pid);
    if (p) {
        document.getElementById('currentStock').textContent = p.stock_quantity;
        document.getElementById('stockUnit').textContent = p.unit.toUpperCase();
        document.getElementById('preview').style.display = 'flex';
    } else {
        document.getElementById('preview').style.display = 'none';
    }
    updatePreview();
}

function updatePreview() {
    const pid  = parseInt(document.getElementById('product_id').value);
    const p    = products.find(x => x.id === pid);
    const qty  = parseInt(document.getElementById('quantity').value) || 0;
    const type = document.querySelector('input[name="type"]:checked')?.value;
    if (!p) return;

    const current = p.stock_quantity;
    const result  = type === 'adjustment_in' ? current + qty : current - qty;
    const el      = document.getElementById('resultStock');
    el.textContent = result;
    el.style.color = result < 0 ? '#991b1b' : (type === 'adjustment_in' ? '#065f46' : '#b45309');
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[name="type"]').forEach(r => r.addEventListener('change', updatePreview));
    document.getElementById('quantity').addEventListener('input', updatePreview);
    document.getElementById('product_id').addEventListener('change', onProductChange);
});
</script>
@endpush

@section('content')
<div class="d-flex align-items-center mb-3">
    <nav style="font-size:13px;color:#9ca3af;">
        <a href="{{ route('admin.stock.index') }}" style="color:var(--pos-primary);text-decoration:none;font-weight:600;">
            <i class="bi bi-arrow-left"></i> Stock Adjustments
        </a>
        <span class="mx-2">·</span>
        <span style="color:#374151;font-weight:600;">New Adjustment</span>
    </nav>
</div>

@if($errors->any())
<div class="pos-alert pos-alert-error mb-3" style="max-width:680px;margin:0 auto 16px;">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <div>
        @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
</div>
@endif

<div class="adj-card">
    <div class="adj-header"><i class="bi bi-sliders"></i> Manual Stock Adjustment</div>
    <div class="adj-body">

        <form method="POST" action="{{ route('admin.stock.store') }}">
            @csrf

            {{-- Product --}}
            <div class="fg">
                <label for="product_id">Product <span class="req">*</span></label>
                <select id="product_id" name="product_id" class="pos-input" required onchange="onProductChange()">
                    <option value="">— Select a product —</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} — {{ $p->sku }} (Stock: {{ $p->stock_quantity }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Live stock preview --}}
            <div class="stock-preview" id="preview" style="display:none;">
                <div>
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:.7px;color:#9ca3af;font-weight:600;">Current Stock</div>
                    <div class="current"><span id="currentStock">0</span> <span style="font-size:13px;font-weight:500;" id="stockUnit">PCS</span></div>
                </div>
                <div class="arrow">→</div>
                <div>
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:.7px;color:#9ca3af;font-weight:600;">After Adjustment</div>
                    <div class="result" id="resultStock" style="color:#065f46;">0</div>
                </div>
            </div>

            {{-- Adjustment type --}}
            <div class="fg">
                <label>Adjustment Type <span class="req">*</span></label>
                <div class="type-toggle">
                    <div class="type-opt">
                        <input type="radio" id="type_in" name="type" value="adjustment_in"
                            {{ old('type', 'adjustment_in') === 'adjustment_in' ? 'checked' : '' }}>
                        <label for="type_in">
                            <span class="ico">⬆️</span>
                            <span class="lbl" style="color:#065f46;">Stock In</span>
                            <span class="sub">Add units (damaged return,<br>correction, found stock)</span>
                        </label>
                    </div>
                    <div class="type-opt">
                        <input type="radio" id="type_out" name="type" value="adjustment_out"
                            {{ old('type') === 'adjustment_out' ? 'checked' : '' }}>
                        <label for="type_out">
                            <span class="ico">⬇️</span>
                            <span class="lbl" style="color:#991b1b;">Stock Out</span>
                            <span class="sub">Remove units (damaged, lost,<br>expired, correction)</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Quantity --}}
            <div class="fg">
                <label for="quantity">Quantity <span class="req">*</span></label>
                <input type="number" id="quantity" name="quantity" class="pos-input"
                    min="1" value="{{ old('quantity', 1) }}" required
                    style="max-width:180px;">
                <div class="hint">Enter the number of units to add or remove.</div>
            </div>

            {{-- Reason --}}
            <div class="fg">
                <label for="notes">Reason / Notes <span class="req">*</span></label>
                <textarea id="notes" name="notes" class="pos-input" rows="3" required
                    placeholder="e.g. Damaged goods written off, stock count correction, returned item…">{{ old('notes') }}</textarea>
            </div>

            <div style="display:flex;gap:10px;margin-top:4px;">
                <button type="submit" class="btn-pos">
                    <i class="bi bi-check-circle"></i> Save Adjustment
                </button>
                <a href="{{ route('admin.stock.index') }}" class="btn-pos-outline text-decoration-none">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>
@endsection

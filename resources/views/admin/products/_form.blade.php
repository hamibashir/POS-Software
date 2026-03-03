{{--
    Shared product form — used by both create.blade.php and edit.blade.php
    Required variables: $product (optional), $categories, $units, $submitLabel
--}}

@php $isEdit = isset($product) && $product->exists; @endphp

{{-- Validation errors bar --}}
@if($errors->any())
    <div class="pos-alert pos-alert-error mb-4">
        <i class="bi bi-exclamation-circle-fill fs-5"></i>
        <div>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $error)
                    <li style="font-size:13px;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="row g-4">

    {{-- LEFT — Main Info --}}
    <div class="col-lg-8">

        {{-- Basic Info --}}
        <div class="pos-card p-4 mb-4">
            <h6 class="fw-bold mb-4" style="color:#374151; font-size:14px;">
                <i class="bi bi-info-circle me-2" style="color:var(--pos-primary)"></i>Basic Information
            </h6>

            {{-- Product Name --}}
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:13px;">
                    Product Name <span class="text-danger">*</span>
                </label>
                <input type="text" name="name" id="productName"
                    value="{{ old('name', $product->name ?? '') }}"
                    class="pos-input @error('name') is-invalid @enderror"
                    placeholder="e.g. Bosch 18V Cordless Drill"
                    required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- SKU & Barcode --}}
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:13px;">
                        SKU <span class="text-danger">*</span>
                        <button type="button" id="generateSkuBtn"
                            class="btn btn-link p-0 ms-2" style="font-size:11px; color:var(--pos-primary); text-decoration:none;">
                            <i class="bi bi-arrow-clockwise"></i> Generate
                        </button>
                    </label>
                    <input type="text" name="sku" id="skuInput"
                        value="{{ old('sku', $product->sku ?? ($suggestedSku ?? '')) }}"
                        class="pos-input @error('sku') is-invalid @enderror"
                        placeholder="e.g. PW-0042"
                        required>
                    @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:13px;">Barcode (Optional)</label>
                    <input type="text" name="barcode"
                        value="{{ old('barcode', $product->barcode ?? '') }}"
                        class="pos-input @error('barcode') is-invalid @enderror"
                        placeholder="Scan or type barcode">
                    @error('barcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Description --}}
            <div class="mb-0">
                <label class="form-label fw-semibold" style="font-size:13px;">Description</label>
                <textarea name="description" rows="3"
                    class="pos-input @error('description') is-invalid @enderror"
                    style="resize:vertical;"
                    placeholder="Optional product description...">{{ old('description', $product->description ?? '') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Pricing & Unit --}}
        <div class="pos-card p-4 mb-4">
            <h6 class="fw-bold mb-4" style="color:#374151; font-size:14px;">
                <i class="bi bi-currency-dollar me-2" style="color:var(--pos-primary)"></i>Pricing & Unit
            </h6>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:13px;">
                        Unit <span class="text-danger">*</span>
                    </label>
                    <select name="unit" class="pos-input @error('unit') is-invalid @enderror" required>
                        @foreach($units as $value => $label)
                            <option value="{{ $value }}" {{ old('unit', $product->unit ?? 'pc') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:13px;">
                        Cost Price <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:#f9fafb; border:1.5px solid #e5e7eb; border-right:none; border-radius:8px 0 0 8px; color:#6b7280; font-size:14px;">$</span>
                        <input type="number" name="cost_price" step="0.01" min="0"
                            value="{{ old('cost_price', $product->cost_price ?? '') }}"
                            class="pos-input @error('cost_price') is-invalid @enderror"
                            style="border-radius:0 8px 8px 0; border-left:none;"
                            placeholder="0.00" required id="costPrice">
                    </div>
                    @error('cost_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="font-size:13px;">
                        Sale Price <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:#f9fafb; border:1.5px solid #e5e7eb; border-right:none; border-radius:8px 0 0 8px; color:#6b7280; font-size:14px;">$</span>
                        <input type="number" name="sale_price" step="0.01" min="0"
                            value="{{ old('sale_price', $product->sale_price ?? '') }}"
                            class="pos-input @error('sale_price') is-invalid @enderror"
                            style="border-radius:0 8px 8px 0; border-left:none;"
                            placeholder="0.00" required id="salePrice">
                    </div>
                    @error('sale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Margin indicator --}}
            <div class="mt-3 p-2 rounded" style="background:#f9fafb; font-size:13px; color:#6b7280;">
                <i class="bi bi-graph-up me-1"></i>
                Margin: <strong id="marginDisplay">—</strong>
            </div>
        </div>

        {{-- Stock --}}
        <div class="pos-card p-4">
            <h6 class="fw-bold mb-4" style="color:#374151; font-size:14px;">
                <i class="bi bi-layers me-2" style="color:var(--pos-primary)"></i>Stock Management
            </h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:13px;">
                        Opening Stock Quantity <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="stock_quantity" min="0"
                        value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}"
                        class="pos-input @error('stock_quantity') is-invalid @enderror"
                        placeholder="0"
                        {{ $isEdit ? 'disabled title=Use stock adjustment for edits' : 'required' }}>
                    @if($isEdit)
                        <div style="font-size:12px; color:#9ca3af; margin-top:4px;">
                            <i class="bi bi-info-circle"></i> Use Purchase Entry to adjust stock after creation.
                        </div>
                    @endif
                    @error('stock_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:13px;">
                        Low Stock Alert Threshold <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="low_stock_threshold" min="0"
                        value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 10) }}"
                        class="pos-input @error('low_stock_threshold') is-invalid @enderror"
                        placeholder="10" required>
                    <div style="font-size:12px; color:#9ca3af; margin-top:4px;">
                        Alert shown when stock falls at or below this number.
                    </div>
                    @error('low_stock_threshold')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

    </div>

    {{-- RIGHT — Category, Image, Settings --}}
    <div class="col-lg-4">

        {{-- Category --}}
        <div class="pos-card p-4 mb-4">
            <h6 class="fw-bold mb-3" style="color:#374151; font-size:14px;">
                <i class="bi bi-tags me-2" style="color:var(--pos-primary)"></i>Category
            </h6>
            <select name="category_id" class="pos-input @error('category_id') is-invalid @enderror">
                <option value="">— Uncategorized —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Image Upload --}}
        <div class="pos-card p-4 mb-4">
            <h6 class="fw-bold mb-3" style="color:#374151; font-size:14px;">
                <i class="bi bi-image me-2" style="color:var(--pos-primary)"></i>Product Image
            </h6>

            {{-- Preview --}}
            <div id="imagePreviewWrap" class="mb-3 text-center" style="{{ ($isEdit && $product->image) ? '' : 'display:none;' }}">
                <img id="imagePreview"
                    src="{{ $isEdit && $product->image ? Storage::url($product->image) : '' }}"
                    alt="Preview"
                    style="max-width:100%; max-height:180px; border-radius:10px; border:1px solid #e5e7eb; object-fit:contain;">
                @if($isEdit && $product->image)
                    <div class="mt-2">
                        <label style="font-size:12px; color:#ef4444; cursor:pointer;">
                            <input type="checkbox" name="remove_image" value="1" id="removeImageCheck">
                            Remove current image
                        </label>
                    </div>
                @endif
            </div>

            {{-- Upload area --}}
            <div id="uploadArea"
                onclick="document.getElementById('imageInput').click()"
                style="border:2px dashed #e5e7eb; border-radius:10px; padding:24px; text-align:center; cursor:pointer; transition:border-color .2s;">
                <i class="bi bi-cloud-upload" style="font-size:28px; color:#d1d5db;"></i>
                <p class="mb-0 mt-2" style="font-size:13px; color:#9ca3af;">Click to upload image</p>
                <p class="mb-0" style="font-size:11px; color:#d1d5db;">JPG, PNG, WebP — max 2MB</p>
            </div>
            <input type="file" name="image" id="imageInput" accept="image/*" class="d-none" onchange="previewImage(this)">
            @error('image')<div class="text-danger mt-1" style="font-size:13px;">{{ $message }}</div>@enderror
        </div>

        {{-- Settings --}}
        <div class="pos-card p-4 mb-4">
            <h6 class="fw-bold mb-3" style="color:#374151; font-size:14px;">
                <i class="bi bi-gear me-2" style="color:var(--pos-primary)"></i>Settings
            </h6>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1"
                    {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold" for="isActive" style="font-size:13px;">Active (available for sale)</label>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="show_in_catalog" id="showInCatalog" value="1"
                    {{ old('show_in_catalog', $product->show_in_catalog ?? true) ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold" for="showInCatalog" style="font-size:13px;">Show in public catalog</label>
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="d-flex gap-2">
            <a href="{{ route('admin.products.index') }}" class="btn-pos-outline flex-fill text-center">
                <i class="bi bi-x-circle"></i> Cancel
            </a>
            <button type="submit" class="btn-pos flex-fill">
                <i class="bi bi-check-lg"></i> {{ $submitLabel ?? 'Save Product' }}
            </button>
        </div>

    </div>
</div>

@push('scripts')
<script>
    // ── Image preview ────────────────────────────────
    function previewImage(input) {
        if (!input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreviewWrap').style.display = '';
            document.getElementById('uploadArea').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }

    // ── Drag over upload area ─────────────────────────
    const uploadArea = document.getElementById('uploadArea');
    if (uploadArea) {
        uploadArea.addEventListener('dragover', (e) => { e.preventDefault(); uploadArea.style.borderColor = 'var(--pos-primary)'; });
        uploadArea.addEventListener('dragleave', ()  => { uploadArea.style.borderColor = '#e5e7eb'; });
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#e5e7eb';
            const input = document.getElementById('imageInput');
            input.files = e.dataTransfer.files;
            previewImage(input);
        });
    }

    // ── Margin calculator ────────────────────────────
    function updateMargin() {
        const cost = parseFloat(document.getElementById('costPrice').value) || 0;
        const sale = parseFloat(document.getElementById('salePrice').value) || 0;
        const el   = document.getElementById('marginDisplay');
        if (cost === 0 || sale === 0) { el.textContent = '—'; return; }
        const margin  = ((sale - cost) / sale * 100).toFixed(1);
        const profit  = (sale - cost).toFixed(2);
        const color   = margin >= 0 ? '#065f46' : '#991b1b';
        el.innerHTML  = `<span style="color:${color}">${margin}%</span> &nbsp;·&nbsp; $${profit} per unit`;
    }
    document.getElementById('costPrice').addEventListener('input', updateMargin);
    document.getElementById('salePrice').addEventListener('input', updateMargin);
    updateMargin();

    // ── SKU generator ────────────────────────────────
    document.getElementById('generateSkuBtn')?.addEventListener('click', async function () {
        const name = document.getElementById('productName').value || '';
        const res  = await fetch(`{{ route('admin.products.generate-sku') }}?name=${encodeURIComponent(name)}`);
        const data = await res.json();
        document.getElementById('skuInput').value = data.sku;
    });
</script>
@endpush

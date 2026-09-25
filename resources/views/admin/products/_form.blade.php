{{--
    Shared product form — used by both create.blade.php and edit.blade.php
    Required variables: $product (optional), $categories, $units, $submitLabel
--}}

@php 
    $isEdit = isset($product) && $product->exists; 
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
@endphp

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
                <i class="bi bi-currency-rupee me-2" style="color:var(--pos-primary)"></i>Pricing & Unit
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
                        <span class="input-group-text" style="background:#f9fafb; border:1.5px solid #e5e7eb; border-right:none; border-radius:8px 0 0 8px; color:#6b7280; font-size:14px; font-weight:600;">Rs.</span>
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
                        <span class="input-group-text" style="background:#f9fafb; border:1.5px solid #e5e7eb; border-right:none; border-radius:8px 0 0 8px; color:#6b7280; font-size:14px; font-weight:600;">Rs.</span>
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
                        {{ (!$isEdit || $isAdmin) ? 'required' : 'disabled title="Only administrators can edit stock directly"' }}>
                    @if($isEdit)
                        @if($isAdmin)
                            <div style="font-size:12px; color:#059669; margin-top:4px;">
                                <i class="bi bi-shield-check"></i> Admin privilege: You can modify the stock quantity directly.
                            </div>
                        @else
                            <div style="font-size:12px; color:#9ca3af; margin-top:4px;">
                                <i class="bi bi-lock-fill"></i> Only administrators can change stock directly. Use Stock Adjustments or Purchase Entry.
                            </div>
                        @endif
                    @else
                        <div style="font-size:12px; color:#9ca3af; margin-top:4px;">
                            Initial opening inventory count for this product.
                        </div>
                    @endif
                    @error('stock_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="font-size:13px;">
                        Low Stock Alert Threshold <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="low_stock_threshold" min="0"
                        value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 1) }}"
                        class="pos-input @error('low_stock_threshold') is-invalid @enderror"
                        placeholder="1" required>
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

        {{-- Supplier / Vendor --}}
        <div class="pos-card p-4 mb-4">
            <h6 class="fw-bold mb-3" style="color:#374151; font-size:14px;">
                <i class="bi bi-truck me-2" style="color:var(--pos-primary)"></i>Supplier / Vendor
            </h6>
            <select name="supplier_id" class="pos-input @error('supplier_id') is-invalid @enderror">
                <option value="">— No Supplier Assigned —</option>
                @if(isset($suppliers))
                    @foreach($suppliers as $sup)
                        @php
                            $compName = !empty($sup->company_name) && strcasecmp(trim($sup->company_name), trim($sup->name)) !== 0 ? ' (' . $sup->company_name . ')' : '';
                        @endphp
                        <option value="{{ $sup->id }}" {{ old('supplier_id', $product->supplier_id ?? '') == $sup->id ? 'selected' : '' }}>
                            {{ $sup->name }}{{ $compName }}
                        </option>
                    @endforeach
                @endif
            </select>
            @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div style="font-size:11px; color:#9ca3af; margin-top:4px;">
                Identifies which supplier or vendor supplies this product.
            </div>
        </div>

        {{-- Image Upload & Paste --}}
        <div class="pos-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0" style="color:#374151; font-size:14px;">
                    <i class="bi bi-image me-2" style="color:var(--pos-primary)"></i>Product Image
                </h6>
                <span class="badge bg-light text-secondary border px-2 py-1" style="font-size:11px; font-weight:600;">
                    <i class="bi bi-clipboard-check me-1 text-primary"></i>Ctrl+V Paste Enabled
                </span>
            </div>

            {{-- Preview --}}
            <div id="imagePreviewWrap" class="mb-3 text-center" style="{{ ($isEdit && $product->image) ? '' : 'display:none;' }}">
                <div class="position-relative d-inline-block">
                    <img id="imagePreview"
                        src="{{ $isEdit && $product->image ? Storage::url($product->image) : '' }}"
                        alt="Preview"
                        style="max-width:100%; max-height:200px; border-radius:10px; border:1px solid #e5e7eb; object-fit:contain; background:#f9fafb; padding:4px;">
                </div>
                <div class="mt-2 d-flex justify-content-center gap-2 align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="triggerChangeImage()" style="font-size:12px; border-radius:6px;">
                        <i class="bi bi-arrow-repeat me-1"></i>Change Image
                    </button>
                    @if($isEdit && $product->image)
                        <label style="font-size:12px; color:#ef4444; cursor:pointer; margin-bottom:0;" class="ms-2">
                            <input type="checkbox" name="remove_image" value="1" id="removeImageCheck">
                            Remove current image
                        </label>
                    @endif
                </div>
            </div>

            {{-- Upload & Paste area --}}
            <div id="uploadArea"
                tabindex="0"
                style="border:2px dashed #cbd5e1; border-radius:12px; padding:20px; text-align:center; cursor:pointer; background:#f8fafc; transition:all .2s ease; outline:none;"
                onclick="handleUploadAreaClick(event)">
                <div class="d-flex justify-content-center gap-3 mb-2 text-muted">
                    <i class="bi bi-camera-fill fs-2 text-success" title="Phone Camera"></i>
                    <i class="bi bi-cloud-arrow-up fs-2 text-primary" title="Upload File"></i>
                    <i class="bi bi-clipboard2-pulse fs-2 text-info" title="Paste Image"></i>
                </div>
                <p class="mb-1 fw-bold text-dark" style="font-size:13.5px;">Take photo, upload file, or paste image</p>
                <p class="mb-2" style="font-size:11.5px; color:#64748b;">
                    On phones, tap <strong>Take Photo</strong> to capture with camera on the spot &middot; or press <kbd style="background:#e2e8f0; color:#0f172a; padding:2px 6px; border-radius:4px; font-weight:700;">Ctrl + V</kbd> to paste
                </p>
                <div class="d-flex justify-content-center gap-2 mt-3 flex-wrap" onclick="event.stopPropagation()">
                    <button type="button" class="btn btn-sm btn-success px-2 py-1 text-white shadow-sm" style="font-size:11.5px; border-radius:6px; font-weight:600;" onclick="triggerFormCamera()">
                        <i class="bi bi-camera-fill me-1"></i>Take Photo (Camera)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary px-2 py-1" style="font-size:11.5px; border-radius:6px;" onclick="pasteFromClipboard()">
                        <i class="bi bi-clipboard-check me-1"></i>Paste Clipboard
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-1" style="font-size:11.5px; border-radius:6px;" onclick="toggleUrlInput()">
                        <i class="bi bi-link-45deg me-1"></i>Paste Image URL
                    </button>
                </div>
            </div>

            {{-- URL Input Box (Collapsible) --}}
            <div id="urlInputContainer" class="mt-2 p-2 rounded-2" style="display:none; background:#f1f5f9; border:1px solid #e2e8f0;">
                <label class="form-label mb-1 text-muted" style="font-size:11.5px; font-weight:600;">Image Web Address (URL):</label>
                <div class="input-group input-group-sm">
                    <input type="url" id="imageDirectUrlInput" class="form-control" placeholder="https://example.com/product-image.jpg" style="font-size:12px;">
                    <button type="button" class="btn btn-primary" onclick="loadImageFromUrlInput()" style="font-size:12px;">Load</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="toggleUrlInput()" style="font-size:12px;">Cancel</button>
                </div>
            </div>

            <input type="file" name="image" id="imageInput" accept="image/*" class="d-none" onchange="previewImage(this)">
            <input type="file" id="cameraInput" accept="image/*" capture="environment" class="d-none" onchange="handleFormCameraCapture(this)">
            <input type="hidden" name="image_base64" id="imageBase64">
            <input type="hidden" name="image_url" id="imageUrlInput">
            @error('image')<div class="text-danger mt-1" style="font-size:13px;">{{ $message }}</div>@enderror
            @error('image_base64')<div class="text-danger mt-1" style="font-size:13px;">{{ $message }}</div>@enderror
            @error('image_url')<div class="text-danger mt-1" style="font-size:13px;">{{ $message }}</div>@enderror
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
            <a href="{{ route('admin.products.index', request()->query()) }}" class="btn-pos-outline flex-fill text-center">
                <i class="bi bi-x-circle"></i> Cancel
            </a>
            <button type="submit" id="submitProductBtn" class="btn-pos flex-fill">
                <i class="bi bi-check-lg"></i> {{ $submitLabel ?? 'Save Product' }}
            </button>
        </div>

    </div>
</div>

@push('scripts')
<script>
    // ── Image preview & paste logic ────────────────────
    function previewImage(input) {
        if (!input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            showImageInPreview(e.target.result);
            document.getElementById('imageUrlInput').value = '';
            document.getElementById('imageBase64').value = '';
        };
        reader.readAsDataURL(input.files[0]);
    }

    function showImageInPreview(srcUrl) {
        document.getElementById('imagePreview').src = srcUrl;
        document.getElementById('imagePreviewWrap').style.display = '';
        document.getElementById('uploadArea').style.display = 'none';
        document.getElementById('urlInputContainer').style.display = 'none';
        const removeCheck = document.getElementById('removeImageCheck');
        if (removeCheck) removeCheck.checked = false;
    }

    function triggerChangeImage() {
        document.getElementById('uploadArea').style.display = '';
        document.getElementById('uploadArea').focus();
    }

    function handleUploadAreaClick(e) {
        if (e.target.closest('button') || e.target.closest('input')) return;
        document.getElementById('imageInput').click();
    }

    function triggerFormCamera() {
        const cam = document.getElementById('cameraInput');
        if (cam) {
            cam.value = '';
            cam.click();
        }
    }

    function handleFormCameraCapture(input) {
        if (!input.files || !input.files[0]) return;
        try {
            const dt = new DataTransfer();
            dt.items.add(input.files[0]);
            document.getElementById('imageInput').files = dt.files;
        } catch (e) {
            console.log('DataTransfer fallback');
        }
        previewImage(input);
    }

    function toggleUrlInput() {
        const box = document.getElementById('urlInputContainer');
        box.style.display = box.style.display === 'none' ? 'block' : 'none';
        if (box.style.display === 'block') {
            document.getElementById('imageDirectUrlInput').focus();
        }
    }

    function loadImageFromUrlInput() {
        const url = document.getElementById('imageDirectUrlInput').value.trim();
        if (!url) return;
        applyImageUrl(url);
    }

    function applyImageUrl(url) {
        document.getElementById('imageUrlInput').value = url;
        document.getElementById('imageBase64').value = '';
        document.getElementById('imageInput').value = '';
        showImageInPreview(url);
    }

    function applyImageBlob(blob) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const dataUrl = e.target.result;
            document.getElementById('imageBase64').value = dataUrl;
            document.getElementById('imageUrlInput').value = '';

            // Also populate file input using DataTransfer
            try {
                const dt = new DataTransfer();
                const file = new File([blob], 'pasted_image.' + (blob.type.split('/')[1] || 'png'), { type: blob.type });
                dt.items.add(file);
                document.getElementById('imageInput').files = dt.files;
            } catch (err) {
                console.log('DataTransfer not supported, base64 fallback active');
            }

            showImageInPreview(dataUrl);
        };
        reader.readAsDataURL(blob);
    }

    // Direct paste button using clipboard API
    async function pasteFromClipboard() {
        try {
            if (navigator.clipboard && navigator.clipboard.read) {
                const items = await navigator.clipboard.read();
                for (const item of items) {
                    const imageType = item.types.find(t => t.startsWith('image/'));
                    if (imageType) {
                        const blob = await item.getType(imageType);
                        applyImageBlob(blob);
                        return;
                    }
                }
            }

            // Fallback: read text (if it's an image link)
            if (navigator.clipboard && navigator.clipboard.readText) {
                const text = await navigator.clipboard.readText();
                if (text && (text.match(/^https?:\/\/.+/i) || text.startsWith('data:image/'))) {
                    applyImageUrl(text.trim());
                    return;
                }
            }

            alert('Please press Ctrl + V to paste your copied image.');
        } catch (err) {
            alert('Clipboard permission denied. Please press Ctrl + V on your keyboard to paste the image.');
        }
    }

    // ── Global Window Paste Listener ───────────────────
    window.addEventListener('paste', function (e) {
        // If actively typing inside another input/textarea that is NOT the URL input
        const active = document.activeElement;
        const isUrlInput = active && active.id === 'imageDirectUrlInput';
        const isOtherText = active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT') && !isUrlInput;

        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        let imageFound = false;

        if (items) {
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const blob = items[i].getAsFile();
                    if (blob) {
                        e.preventDefault();
                        applyImageBlob(blob);
                        imageFound = true;
                        break;
                    }
                }
            }
        }

        if (!imageFound) {
            const text = (e.clipboardData || window.clipboardData).getData('text');
            if (text && (text.match(/^https?:\/\/.+(\.(jpg|jpeg|png|webp|gif|svg)|images\?|img|photo|media).*/i) || text.startsWith('data:image/'))) {
                if (!isOtherText || isUrlInput || active.id === 'uploadArea') {
                    e.preventDefault();
                    applyImageUrl(text.trim());
                }
            }
        }
    });

    // ── Drag & drop over upload area ───────────────────
    const uploadArea = document.getElementById('uploadArea');
    if (uploadArea) {
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = 'var(--pos-primary)';
            uploadArea.style.background = '#eef2ff';
        });
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.style.borderColor = '#cbd5e1';
            uploadArea.style.background = '#f8fafc';
        });
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#cbd5e1';
            uploadArea.style.background = '#f8fafc';
            const input = document.getElementById('imageInput');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                input.files = e.dataTransfer.files;
                previewImage(input);
            }
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
        el.innerHTML  = `<span style="color:${color}">${margin}%</span> &nbsp;·&nbsp; Rs. ${profit} per unit`;
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

    // ── Enter key triggers product submission / update ──
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            const active = document.activeElement;

            // In multiline textarea: allow normal Enter for new line, but Ctrl+Enter submits
            if (active && active.tagName === 'TEXTAREA') {
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    triggerProductFormSubmit();
                }
                return;
            }

            // In URL direct input: load image on Enter
            if (active && active.id === 'imageDirectUrlInput') {
                e.preventDefault();
                loadImageFromUrlInput();
                return;
            }

            // In other non-submit interactive buttons/links: allow normal click
            if (active && (active.tagName === 'BUTTON' || active.tagName === 'A') && active.id !== 'submitProductBtn') {
                return;
            }

            // For all inputs, selects, number boxes, or anywhere else on page: trigger form submit
            e.preventDefault();
            triggerProductFormSubmit();
        }
    });

    function triggerProductFormSubmit() {
        const btn = document.getElementById('submitProductBtn');
        if (btn) {
            btn.click();
        } else {
            const form = document.getElementById('productForm') || document.querySelector('form');
            if (form) {
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }
        }
    }
</script>
@endpush

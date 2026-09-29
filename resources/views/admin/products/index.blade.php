@extends('layouts.admin')

@section('title', 'Products')

@push('styles')
<style>
.stock-badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-block; }
.stock-in    { background: #d1fae5; color: #065f46; }
.stock-low   { background: #fef3c7; color: #92400e; }
.stock-out   { background: #fee2e2; color: #991b1b; }

/* ── Interactive Image Thumbnail ────────────────────── */
.product-img-wrap {
    position: relative;
    width: 44px;
    height: 44px;
    border-radius: 8px;
    cursor: pointer;
    display: inline-block;
    overflow: hidden;
    vertical-align: middle;
    border: 1.5px solid #e5e7eb;
    background: #f9fafb;
    transition: all .2s ease;
}
.product-img-wrap:hover {
    border-color: var(--pos-primary);
    box-shadow: 0 2px 8px rgba(30,109,138,0.25);
    transform: scale(1.04);
}
.product-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.product-img-placeholder {
    width: 100%;
    height: 100%;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 16px;
}
.product-img-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    opacity: 0;
    transition: opacity .15s ease;
    font-size: 14px;
}
.product-img-wrap:hover .product-img-overlay {
    opacity: 1;
}

/* ── Inline Quick Stock Controls ────────────────────── */
.quick-stock-input {
    width: 70px !important;
    text-align: center;
    font-weight: 700;
    font-size: 13.5px;
    padding: 3px 4px !important;
    border-radius: 6px 0 0 6px !important;
    border: 1.5px solid #d1d5db !important;
    transition: all .2s ease;
    background: #ffffff;
}
.quick-stock-input:focus {
    border-color: var(--pos-primary) !important;
    background: #fff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(30,109,138,0.18) !important;
}
.quick-stock-input.updated-success {
    background-color: #dcfce7 !important;
    border-color: #22c55e !important;
    color: #15803d !important;
}
.btn-save-stock {
    padding: 3px 8px !important;
    font-size: 12px;
    border-radius: 0 6px 6px 0 !important;
    background: var(--pos-primary) !important;
    border: 1.5px solid var(--pos-primary) !important;
    color: #fff !important;
    cursor: pointer;
    transition: all .15s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-save-stock:hover {
    background: #155268 !important;
    border-color: #155268 !important;
}
.btn-quick-add-toggle {
    padding: 3px 7px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    border-radius: 6px !important;
    border: 1.5px solid #e5e7eb !important;
    background: #f8fafc !important;
    color: #475569 !important;
    line-height: 1;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all .15s;
}
.btn-quick-add-toggle:hover {
    background: #e2e8f0 !important;
    color: var(--pos-primary) !important;
    border-color: #cbd5e1 !important;
}
.dropdown-toggle.no-caret::after { display: none !important; }

/* Toast notification */
.stock-toast {
    position: fixed; bottom: 24px; right: 24px; z-index: 99999;
    background: #0f172a; color: #fff; border-radius: 10px;
    padding: 12px 18px; font-size: 13px; font-weight: 600;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    display: flex; align-items: center; gap: 8px;
    transform: translateY(20px); opacity: 0;
    transition: all .25s cubic-bezier(0.16, 1, 0.3, 1);
    pointer-events: none;
}
.stock-toast.show {
    transform: translateY(0); opacity: 1; pointer-events: auto;
}
.stock-toast.toast-success { border-left: 4px solid #22c55e; }
.stock-toast.toast-error   { border-left: 4px solid #ef4444; }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="page-hero d-flex align-items-start justify-content-between">
    <div>
        <h1><i class="bi bi-box-seam me-2" style="color:var(--pos-primary)"></i>Products</h1>
        <p>Manage your hardware store inventory, pricing, and stock levels.</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn-pos">
        <i class="bi bi-plus-lg"></i> Add Product
    </a>
</div>

{{-- Flash --}}
@if(session('success'))
    <div class="pos-alert pos-alert-success mb-4"><i class="bi bi-check-circle-fill fs-5"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="pos-alert pos-alert-error mb-4"><i class="bi bi-exclamation-circle-fill fs-5"></i> {{ session('error') }}</div>
@endif

{{-- Filters --}}
<div class="pos-card p-3 mb-4">
    <form method="GET" action="{{ route('admin.products.index') }}">
        <div class="d-flex gap-2 flex-wrap align-items-end">
            {{-- Search --}}
            <div style="flex:2; min-width:220px;">
                <label class="form-label" style="font-size:12px; font-weight:600; color:#6b7280; margin-bottom:4px;">Search</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#f9fafb; border:1.5px solid #e5e7eb; border-right:none;"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="pos-input" style="border-radius:0 8px 8px 0; border-left:none;"
                        placeholder="Name, SKU or barcode...">
                </div>
            </div>

            {{-- Category --}}
            <div style="min-width:180px;">
                <label class="form-label" style="font-size:12px; font-weight:600; color:#6b7280; margin-bottom:4px;">Category</label>
                <select name="category_id" class="pos-input searchable-select" placeholder="All Categories / Search...">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Supplier --}}
            <div style="min-width:190px;">
                <label class="form-label" style="font-size:12px; font-weight:600; color:#6b7280; margin-bottom:4px;">Supplier</label>
                <select name="supplier_id" class="pos-input searchable-select" placeholder="All Suppliers / Search...">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                            {{ $sup->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div style="min-width:130px;">
                <label class="form-label" style="font-size:12px; font-weight:600; color:#6b7280; margin-bottom:4px;">Status</label>
                <select name="status" class="pos-input">
                    <option value="">All Status</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            {{-- Stock --}}
            <div style="min-width:130px;">
                <label class="form-label" style="font-size:12px; font-weight:600; color:#6b7280; margin-bottom:4px;">Stock</label>
                <select name="stock" class="pos-input">
                    <option value="">All Stock</option>
                    <option value="in_stock" {{ request('stock') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="low"      {{ request('stock') === 'low'      ? 'selected' : '' }}>Low Stock</option>
                    <option value="out"      {{ request('stock') === 'out'      ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>

            <div class="d-flex gap-2" style="margin-top:20px;">
                <button type="submit" class="btn-pos"><i class="bi bi-funnel"></i> Filter</button>
                @if(request('search') || request('category_id') || request('supplier_id') || request('status') || request('stock'))
                    <a href="{{ route('admin.products.index') }}" class="btn-pos-outline"><i class="bi bi-x-circle"></i> Clear</a>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="pos-card">
    @if($products->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-box-seam" style="font-size:48px; color:#d1d5db;"></i>
            <p class="mt-3 text-muted">No products found.
                <a href="{{ route('admin.products.create') }}">Add the first product.</a>
            </p>
        </div>
    @else
        <table class="table pos-table mb-0 align-middle">
            <thead>
                <tr>
                    <th width="54">Image</th>
                    <th>Product</th>
                    <th>SKU / Barcode</th>
                    <th>Supplier</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th>Cost</th>
                    <th>Price</th>
                    <th width="170">Stock (Quick Edit)</th>
                    <th>Status</th>
                    <th width="110">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    {{-- Image --}}
                    <td>
                        <div class="product-img-wrap"
                             id="img-wrap-{{ $product->id }}"
                             data-product-id="{{ $product->id }}"
                             data-product-name="{{ $product->name }}"
                             data-product-sku="{{ $product->sku }}"
                             data-product-image="{{ $product->image ? Storage::url($product->image) : '' }}"
                             data-has-image="{{ $product->image ? '1' : '0' }}"
                             onclick="openQuickImageModal(this)"
                             title="Click to add or change image">
                            <div id="img-container-{{ $product->id }}">
                                @if($product->image)
                                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="product-img" id="product-img-{{ $product->id }}">
                                @else
                                    <div class="product-img-placeholder" id="product-placeholder-{{ $product->id }}"><i class="bi bi-camera"></i></div>
                                @endif
                            </div>
                            <div class="product-img-overlay">
                                <i class="bi bi-camera-fill"></i>
                            </div>
                        </div>
                    </td>

                    {{-- Product name + description --}}
                    <td>
                        <div>
                            <a href="{{ route('admin.products.show', $product) }}" class="text-decoration-none fw-bold" style="color:#111827;" onmouseover="this.style.color='var(--pos-primary)'" onmouseout="this.style.color='#111827'" title="View Sales & Stock Details">
                                {{ $product->name }}
                            </a>
                        </div>
                        @if($product->description)
                            <div style="font-size:12px; color:#9ca3af;">{{ Str::limit($product->description, 50) }}</div>
                        @endif
                    </td>

                    {{-- SKU / Barcode --}}
                    <td>
                        <code style="background:#f3f4f6; padding:2px 7px; border-radius:4px; font-size:12px; display:block; margin-bottom:2px;">{{ $product->sku }}</code>
                        @if($product->barcode)
                            <span style="font-size:11px; color:#9ca3af;"><i class="bi bi-upc-scan"></i> {{ $product->barcode }}</span>
                        @endif
                    </td>

                    {{-- Supplier --}}
                    <td>
                        @if($product->supplier_name)
                            <span class="badge" style="background:#fef3c7; color:#92400e; padding:4px 9px; font-size:11px; font-weight:700; border-radius:12px; display:inline-flex; align-items:center; gap:4px;">
                                <i class="bi bi-truck"></i> {{ $product->supplier_name }}
                            </span>
                        @else
                            <span class="text-muted" style="font-size:12px;">—</span>
                        @endif
                    </td>

                    {{-- Category --}}
                    <td>
                        @if($product->category)
                            <span style="background:#e8f4f7; color:var(--pos-primary); padding:3px 10px; border-radius:20px; font-size:12px; font-weight:500;">
                                {{ $product->category->name }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    {{-- Unit --}}
                    <td style="font-size:13px; color:#6b7280;">{{ strtoupper($product->unit) }}</td>

                    {{-- Cost --}}
                    <td style="font-size:13px; color:#6b7280;">{{ pkr($product->cost_price, 2) }}</td>

                    {{-- Sale Price --}}
                    <td style="font-weight:600; color:#111827;">{{ pkr($product->sale_price, 2) }}</td>

                    {{-- Stock Quick Edit --}}
                    <td class="stock-cell" id="stock-cell-{{ $product->id }}">
                        @php $status = $product->stock_status; @endphp
                        <div class="d-flex align-items-center gap-1 mb-1">
                            <form method="POST" action="{{ route('admin.products.quick-stock', array_merge(['product' => $product->id], request()->query())) }}"
                                  class="quick-stock-form d-flex align-items-center"
                                  data-product-id="{{ $product->id }}"
                                  data-product-name="{{ $product->name }}">
                                @csrf @method('PATCH')
                                <div class="input-group input-group-sm" style="width: 104px;">
                                    <input type="number" name="stock_quantity" value="{{ $product->stock_quantity }}"
                                           class="form-control form-control-sm quick-stock-input"
                                           id="stock-input-{{ $product->id }}"
                                           title="Type stock and press Enter or Save">
                                    <button type="submit" class="btn btn-save-stock" title="Save Stock">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </div>
                            </form>

                            {{-- Quick Add Dropdown --}}
                            <div class="dropdown">
                                <button class="btn btn-quick-add-toggle dropdown-toggle no-caret" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false" title="Quick add units">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm py-1" style="min-width: 140px; font-size: 12px; z-index: 1050;">
                                    <li class="dropdown-header py-1 px-3 text-muted" style="font-size: 10.5px; font-weight: 700; text-transform: uppercase;">Quick Add Units</li>
                                    <li><button type="button" class="dropdown-item py-1 px-3 btn-add-units" data-product-id="{{ $product->id }}" data-amount="1">+1 Unit</button></li>
                                    <li><button type="button" class="dropdown-item py-1 px-3 btn-add-units" data-product-id="{{ $product->id }}" data-amount="5">+5 Units</button></li>
                                    <li><button type="button" class="dropdown-item py-1 px-3 btn-add-units" data-product-id="{{ $product->id }}" data-amount="10">+10 Units</button></li>
                                    <li><button type="button" class="dropdown-item py-1 px-3 btn-add-units" data-product-id="{{ $product->id }}" data-amount="25">+25 Units</button></li>
                                    <li><button type="button" class="dropdown-item py-1 px-3 btn-add-units" data-product-id="{{ $product->id }}" data-amount="50">+50 Units</button></li>
                                    <li><button type="button" class="dropdown-item py-1 px-3 btn-add-units" data-product-id="{{ $product->id }}" data-amount="100">+100 Units</button></li>
                                </ul>
                            </div>
                        </div>
                        <span class="stock-badge stock-badge-pill {{ $status === 'in_stock' ? 'stock-in' : ($status === 'low_stock' ? 'stock-low' : 'stock-out') }}" id="stock-badge-{{ $product->id }}">
                            {{ $product->stock_status_label }}
                        </span>
                    </td>

                    {{-- Active status --}}
                    <td>
                        @if($product->is_active)
                            <span class="badge badge-active rounded-pill px-3 py-1"><i class="bi bi-circle-fill me-1" style="font-size:8px;"></i>Active</span>
                        @else
                            <span class="badge badge-inactive rounded-pill px-3 py-1"><i class="bi bi-circle me-1" style="font-size:8px;"></i>Inactive</span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.products.show', $product) }}"
                               class="btn btn-sm btn-outline-info" style="border-radius:7px;" title="Sales & Stock Details">
                                <i class="bi bi-clock-history"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-primary" style="border-radius:7px;" title="Quick Change Image" onclick="document.getElementById('img-wrap-{{ $product->id }}').click()">
                                <i class="bi bi-camera"></i>
                            </button>
                            <a href="{{ route('admin.products.edit', array_merge(['product' => $product->id], request()->query())) }}"
                               class="btn btn-sm btn-outline-secondary" style="border-radius:7px;" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.products.toggle-status', array_merge(['product' => $product->id], request()->query())) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $product->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                    style="border-radius:7px;" title="{{ $product->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="bi {{ $product->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.products.destroy', array_merge(['product' => $product->id], request()->query())) }}"
                               onsubmit="return confirm('Soft-delete product \'{{ addslashes($product->name) }}\'?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:7px;" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination & Quick Page Navigation --}}
        @if($products->hasPages() || $products->total() > 0)
            <div class="px-4 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
                {{-- Results Count & Page Summary --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <p class="text-muted mb-0" style="font-size:13px;">
                        Showing <strong class="text-dark">{{ $products->firstItem() ?? 0 }}</strong> to <strong class="text-dark">{{ $products->lastItem() ?? 0 }}</strong> of <strong class="text-dark">{{ $products->total() }}</strong> results
                        <span class="badge bg-light text-secondary border ms-1" style="font-size:11.5px; font-weight:600;">Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>
                    </p>
                </div>

                @if($products->lastPage() > 1)
                {{-- Direct Page Jump & Quick Slider Navigation --}}
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    {{-- Quick Page Search / Jump --}}
                    <form method="GET" action="{{ route('admin.products.index') }}" class="d-flex align-items-center gap-1 mb-0" id="pageJumpForm">
                        @foreach(request()->except('page') as $k => $v)
                            @if(is_array($v))
                                @foreach($v as $subKey => $subVal)
                                    <input type="hidden" name="{{ $k }}[{{ $subKey }}]" value="{{ $subVal }}">
                                @endforeach
                            @elseif($v !== null && $v !== '')
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endif
                        @endforeach
                        
                        <label for="pageJumpInput" class="text-muted mb-0" style="font-size:12px; font-weight:600;">Page:</label>
                        <div class="input-group input-group-sm" style="width: 120px;">
                            <input type="number" name="page" id="pageJumpInput" 
                                min="1" max="{{ $products->lastPage() }}" 
                                value="{{ $products->currentPage() }}" 
                                class="form-control text-center fw-bold" 
                                style="font-size:12.5px; border-radius:6px 0 0 6px; border-color:#d1d5db;"
                                placeholder="1-{{ $products->lastPage() }}"
                                title="Type page number (1 to {{ $products->lastPage() }})">
                            <button type="submit" class="btn btn-pos btn-sm px-2" style="border-radius:0 6px 6px 0; font-size:12px;" title="Go to page">
                                Go <i class="bi bi-arrow-right-short"></i>
                            </button>
                        </div>
                    </form>

                    {{-- Page Scroller / Slider for easy jump from e.g. page 21 to 228 --}}
                    <div class="d-none d-sm-flex align-items-center gap-2 ps-3 border-start" style="border-color:#e5e7eb !important;">
                        <span class="text-muted" style="font-size:11.5px; font-weight:600;"><i class="bi bi-sliders me-1"></i>Scroll Page:</span>
                        <input type="range" class="form-range" id="pageScrollRange"
                            min="1" max="{{ $products->lastPage() }}" value="{{ $products->currentPage() }}"
                            style="width: 140px; cursor: pointer; accent-color: var(--pos-primary);"
                            title="Slide to browse pages (1 to {{ $products->lastPage() }})"
                            oninput="document.getElementById('pageJumpInput').value = this.value; document.getElementById('rangePageBadge').textContent = 'Page ' + this.value;"
                            onchange="document.getElementById('pageJumpForm').submit();">
                        <span id="rangePageBadge" class="badge bg-secondary text-white" style="font-size:11px; min-width:65px;">Page {{ $products->currentPage() }}</span>
                    </div>
                </div>
                @endif

                {{-- Standard Pagination Links --}}
                <div class="d-flex align-items-center">
                    {{ $products->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        @endif
    @endif
</div>

{{-- Global Floating Toast for Quick Stock Adjustments & Image Updates --}}
<div id="stockToast" class="stock-toast">
    <i class="bi bi-check-circle-fill text-success fs-5"></i>
    <span id="stockToastMsg">Updated successfully.</span>
</div>

{{-- Quick Image Modal --}}
<div class="modal fade" id="quickImageModal" tabindex="-1" aria-labelledby="quickImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px; border:none; box-shadow:0 20px 40px rgba(0,0,0,0.22); overflow:hidden;">
            <div class="modal-header py-3 px-4" style="border-bottom:1px solid #f1f5f9; background:#f8fafc;">
                <div>
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-0" id="quickImageModalLabel" style="font-size:15px;">
                        <i class="bi bi-image" style="color:var(--pos-primary);"></i>
                        <span id="qimProductName">Product Image</span>
                    </h6>
                    <small class="text-muted" style="font-size:12px;">SKU: <code id="qimProductSku" class="text-dark bg-white px-1.5 py-0.5 rounded border"></code></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Current / Selected Preview --}}
                <div class="text-center mb-3">
                    <div style="background:#f8fafc; border-radius:12px; border:2px dashed #cbd5e1; padding:10px; min-width:180px; min-height:140px; display:inline-flex; align-items:center; justify-content:center; max-width:100%;">
                        <img id="qimPreviewImg" src="" alt="Preview" style="max-width:100%; max-height:180px; border-radius:8px; object-fit:contain; display:none;">
                        <div id="qimNoImageState" class="text-muted py-4">
                            <i class="bi bi-image" style="font-size:42px; color:#cbd5e1; display:block; margin-bottom:4px;"></i>
                            <span style="font-size:12px; color:#94a3b8; font-weight:500;">No Image Assigned</span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span id="qimImageStatusBadge" class="badge bg-light text-secondary border px-2 py-1" style="font-size:11px; font-weight:600;">Current Image</span>
                    </div>
                </div>

                {{-- Drop, Paste & Camera Area --}}
                <div id="qimUploadArea" tabindex="0"
                     style="border:2px dashed #94a3b8; border-radius:12px; padding:18px 12px; text-align:center; cursor:pointer; background:#f8fafc; transition:all .2s ease; outline:none;"
                     onclick="handleQimUploadAreaClick(event)">
                    <div class="d-flex justify-content-center gap-3 mb-1">
                        <i class="bi bi-camera-fill fs-3 text-success" title="Phone Camera"></i>
                        <i class="bi bi-cloud-arrow-up-fill fs-3" style="color:var(--pos-primary);" title="Upload File"></i>
                        <i class="bi bi-clipboard2-check-fill fs-3 text-primary" title="Paste Image"></i>
                    </div>
                    <p class="mb-1 fw-bold text-dark" style="font-size:13px;">Take photo, browse file, or paste image</p>
                    <p class="mb-2 text-muted" style="font-size:11.5px;">
                        On mobile phones, tap <strong>Take Photo</strong> to capture with your camera on the spot
                    </p>
                    <div class="d-flex justify-content-center gap-2 mt-2 flex-wrap" onclick="event.stopPropagation()">
                        <button type="button" class="btn btn-sm btn-success px-2.5 py-1 text-white shadow-sm" style="font-size:12px; border-radius:6px; font-weight:600;" onclick="triggerQimCamera()">
                            <i class="bi bi-camera-fill me-1"></i>Take Photo (Camera)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary px-2.5 py-1" style="font-size:12px; border-radius:6px;" onclick="qimPasteFromClipboard()">
                            <i class="bi bi-clipboard-check me-1"></i>Paste Clipboard
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary px-2.5 py-1" style="font-size:12px; border-radius:6px;" onclick="qimToggleUrlInput()">
                            <i class="bi bi-link-45deg me-1"></i>Image URL
                        </button>
                    </div>
                </div>

                {{-- Image URL Input Box (Collapsible) --}}
                <div id="qimUrlContainer" class="mt-2 p-2 rounded-2" style="display:none; background:#f1f5f9; border:1px solid #e2e8f0;">
                    <label class="form-label mb-1 text-muted" style="font-size:11.5px; font-weight:600;">Image Web Address (URL):</label>
                    <div class="input-group input-group-sm">
                        <input type="url" id="qimUrlInput" class="form-control" placeholder="https://example.com/product-image.jpg" style="font-size:12px;">
                        <button type="button" class="btn btn-primary" onclick="qimLoadFromUrl()" style="font-size:12px;">Load</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="qimToggleUrlInput()" style="font-size:12px;">Cancel</button>
                    </div>
                </div>

                <input type="file" id="qimFileInput" accept="image/*" class="d-none" onchange="qimHandleFile(this, 'file')">
                <input type="file" id="qimCameraInput" accept="image/*" capture="environment" class="d-none" onchange="qimHandleFile(this, 'camera')">

                {{-- Remove Image Option --}}
                <div id="qimRemoveWrap" class="mt-3 text-center" style="display:none;">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="qimMarkRemove()" style="font-size:12px; border-radius:6px;">
                        <i class="bi bi-trash3 me-1"></i>Remove Current Image
                    </button>
                </div>
            </div>
            <div class="modal-footer py-3 px-4" style="border-top:1px solid #f1f5f9; background:#f8fafc;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="font-size:13px; font-weight:600;">Cancel</button>
                <button type="button" class="btn btn-pos" id="qimSaveBtn" onclick="qimSubmitSave()" style="font-size:13px; font-weight:600; padding:7px 20px;">
                    <i class="bi bi-check-lg me-1"></i>Save Image
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Auto-dismiss standard alerts
    setTimeout(() => {
        document.querySelectorAll('.pos-alert').forEach(el => {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        });
    }, 4000);

    // Toast helper
    function showStockToast(msg, isSuccess = true) {
        const toast = document.getElementById('stockToast');
        const toastMsg = document.getElementById('stockToastMsg');
        if (!toast || !toastMsg) return;

        toastMsg.textContent = msg;
        toast.className = `stock-toast ${isSuccess ? 'toast-success' : 'toast-error'} show`;

        const icon = toast.querySelector('i');
        if (icon) {
            icon.className = isSuccess ? 'bi bi-check-circle-fill text-success fs-5' : 'bi bi-exclamation-circle-fill text-danger fs-5';
        }

        clearTimeout(window.__stockToastTimeout);
        window.__stockToastTimeout = setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    // Helper to send stock update via AJAX
    async function submitQuickStock(productId, stockQuantity, addQuantity = null, productName = '') {
        const inputEl = document.getElementById(`stock-input-${productId}`);
        const badgeEl = document.getElementById(`stock-badge-${productId}`);
        const saveBtn = inputEl ? inputEl.closest('form').querySelector('.btn-save-stock') : null;

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width:12px;height:12px;"></span>';
        }

        const payload = {};
        if (addQuantity !== null) {
            payload.add_quantity = addQuantity;
        } else {
            payload.stock_quantity = stockQuantity;
        }

        try {
            const res = await fetch(`/admin/products/${productId}/quick-stock`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (res.ok && data.success) {
                if (inputEl) {
                    inputEl.value = data.stock_quantity;
                    inputEl.classList.add('updated-success');
                    setTimeout(() => inputEl.classList.remove('updated-success'), 1200);
                }
                if (badgeEl) {
                    badgeEl.textContent = data.stock_status_label;
                    badgeEl.className = `stock-badge stock-badge-pill ${data.badge_class}`;
                }
                showStockToast(data.message || `Stock for ${productName || 'product'} updated to ${data.stock_quantity}.`, true);
            } else {
                showStockToast(data.message || 'Error updating stock.', false);
            }
        } catch (err) {
            console.error(err);
            showStockToast('Network error while updating stock.', false);
        } finally {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-check-lg"></i>';
            }
        }
    }

    // Form submit listener for inline stock edit
    document.querySelectorAll('.quick-stock-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const input = document.getElementById(`stock-input-${productId}`);
            if (input) {
                submitQuickStock(productId, parseInt(input.value) || 0, null, productName);
            }
        });
    });

    // Quick add unit buttons (+1, +5, +10, etc.)
    document.querySelectorAll('.btn-add-units').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const amount = parseInt(this.dataset.amount) || 1;
            const input = document.getElementById(`stock-input-${productId}`);
            const form = input ? input.closest('form') : null;
            const productName = form ? form.dataset.productName : '';

            submitQuickStock(productId, null, amount, productName);
        });
    });

    /* ── Quick Image Modal Logic ────────────────────────────────────────── */
    window.qimState = {
        productId: null,
        productName: '',
        mode: 'none', // 'file', 'base64', 'url', 'remove', 'none'
        file: null,
        base64: null,
        url: null,
        remove: false,
        initialHasImage: false,
        initialImage: ''
    };

    let quickImageModalInstance = null;

    function openQuickImageModal(triggerEl) {
        const productId = triggerEl.dataset.productId;
        const productName = triggerEl.dataset.productName;
        const productSku = triggerEl.dataset.productSku;
        const productImage = triggerEl.dataset.productImage;
        const hasImage = triggerEl.dataset.hasImage === '1';

        window.qimState = {
            productId: productId,
            productName: productName,
            mode: 'none',
            file: null,
            base64: null,
            url: null,
            remove: false,
            initialHasImage: hasImage,
            initialImage: productImage
        };

        document.getElementById('qimProductName').textContent = productName;
        document.getElementById('qimProductSku').textContent = productSku;

        const previewImg = document.getElementById('qimPreviewImg');
        const noImageState = document.getElementById('qimNoImageState');
        const statusBadge = document.getElementById('qimImageStatusBadge');
        const removeWrap = document.getElementById('qimRemoveWrap');
        const urlContainer = document.getElementById('qimUrlContainer');
        const fileInput = document.getElementById('qimFileInput');

        if (fileInput) fileInput.value = '';
        if (urlContainer) urlContainer.style.display = 'none';

        if (hasImage && productImage) {
            previewImg.src = productImage;
            previewImg.style.display = 'block';
            noImageState.style.display = 'none';
            statusBadge.className = 'badge bg-light text-secondary border px-2 py-1';
            statusBadge.textContent = 'Current Saved Image';
            removeWrap.style.display = 'block';
        } else {
            previewImg.src = '';
            previewImg.style.display = 'none';
            noImageState.style.display = 'block';
            statusBadge.className = 'badge bg-light text-muted border px-2 py-1';
            statusBadge.textContent = 'No Image Assigned';
            removeWrap.style.display = 'none';
        }

        const modalEl = document.getElementById('quickImageModal');
        if (!quickImageModalInstance) {
            quickImageModalInstance = new bootstrap.Modal(modalEl);
        }
        quickImageModalInstance.show();
    }

    function handleQimUploadAreaClick(e) {
        document.getElementById('qimFileInput').click();
    }

    function triggerQimCamera() {
        const camInput = document.getElementById('qimCameraInput');
        if (camInput) {
            camInput.value = '';
            camInput.click();
        }
    }

    function qimHandleFile(input, source = 'file') {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (!file.type.startsWith('image/')) {
                showStockToast('Please select or capture a valid image.', false);
                return;
            }
            window.qimState.mode = 'file';
            window.qimState.file = file;
            window.qimState.base64 = null;
            window.qimState.url = null;
            window.qimState.remove = false;

            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImg = document.getElementById('qimPreviewImg');
                const noImageState = document.getElementById('qimNoImageState');
                const statusBadge = document.getElementById('qimImageStatusBadge');
                const removeWrap = document.getElementById('qimRemoveWrap');

                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
                noImageState.style.display = 'none';

                if (source === 'camera' || input.id === 'qimCameraInput') {
                    statusBadge.className = 'badge bg-success text-white px-2 py-1';
                    statusBadge.textContent = 'Photo Captured via Phone Camera (Ready to Save)';
                } else {
                    statusBadge.className = 'badge bg-primary text-white px-2 py-1';
                    statusBadge.textContent = `New File Selected: ${file.name}`;
                }

                removeWrap.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }

    async function qimPasteFromClipboard() {
        try {
            if (!navigator.clipboard || !navigator.clipboard.read) {
                showStockToast('Use Ctrl + V to paste images directly.', false);
                return;
            }
            const items = await navigator.clipboard.read();
            for (const item of items) {
                for (const type of item.types) {
                    if (type.startsWith('image/')) {
                        const blob = await item.getType(type);
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            applyQimBase64Image(e.target.result, 'Pasted from Clipboard');
                        };
                        reader.readAsDataURL(blob);
                        return;
                    }
                }
            }
            showStockToast('No image data found in clipboard. Copy an image first!', false);
        } catch (err) {
            console.error(err);
            showStockToast('Press Ctrl + V to paste an image directly.', false);
        }
    }

    function applyQimBase64Image(base64Data, label = 'Pasted Image') {
        window.qimState.mode = 'base64';
        window.qimState.base64 = base64Data;
        window.qimState.file = null;
        window.qimState.url = null;
        window.qimState.remove = false;

        const previewImg = document.getElementById('qimPreviewImg');
        const noImageState = document.getElementById('qimNoImageState');
        const statusBadge = document.getElementById('qimImageStatusBadge');
        const removeWrap = document.getElementById('qimRemoveWrap');

        previewImg.src = base64Data;
        previewImg.style.display = 'block';
        noImageState.style.display = 'none';
        statusBadge.className = 'badge bg-success text-white px-2 py-1';
        statusBadge.textContent = `${label} (Ready to Save)`;
        removeWrap.style.display = 'block';
    }

    function qimToggleUrlInput() {
        const container = document.getElementById('qimUrlContainer');
        const input = document.getElementById('qimUrlInput');
        if (container.style.display === 'none') {
            container.style.display = 'block';
            input.value = '';
            input.focus();
        } else {
            container.style.display = 'none';
        }
    }

    function qimLoadFromUrl() {
        const input = document.getElementById('qimUrlInput');
        const url = (input.value || '').trim();
        if (!url || !/^https?:\/\//i.test(url)) {
            showStockToast('Please enter a valid image web URL starting with http:// or https://', false);
            return;
        }

        window.qimState.mode = 'url';
        window.qimState.url = url;
        window.qimState.file = null;
        window.qimState.base64 = null;
        window.qimState.remove = false;

        const previewImg = document.getElementById('qimPreviewImg');
        const noImageState = document.getElementById('qimNoImageState');
        const statusBadge = document.getElementById('qimImageStatusBadge');
        const removeWrap = document.getElementById('qimRemoveWrap');

        previewImg.src = url;
        previewImg.style.display = 'block';
        noImageState.style.display = 'none';
        statusBadge.className = 'badge bg-info text-white px-2 py-1';
        statusBadge.textContent = 'Web Image URL (Ready to Download & Save)';
        removeWrap.style.display = 'block';
        document.getElementById('qimUrlContainer').style.display = 'none';
    }

    function qimMarkRemove() {
        window.qimState.mode = 'remove';
        window.qimState.file = null;
        window.qimState.base64 = null;
        window.qimState.url = null;
        window.qimState.remove = true;

        const previewImg = document.getElementById('qimPreviewImg');
        const noImageState = document.getElementById('qimNoImageState');
        const statusBadge = document.getElementById('qimImageStatusBadge');
        const removeWrap = document.getElementById('qimRemoveWrap');

        previewImg.src = '';
        previewImg.style.display = 'none';
        noImageState.style.display = 'block';
        statusBadge.className = 'badge bg-danger text-white px-2 py-1';
        statusBadge.textContent = 'Image Will Be Removed (Click Save to Confirm)';
        removeWrap.style.display = 'none';
    }

    // Ctrl + V paste listener for modal
    window.addEventListener('paste', function(e) {
        const modalEl = document.getElementById('quickImageModal');
        if (!modalEl || !modalEl.classList.contains('show')) return;

        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (let i = 0; i < items.length; i++) {
            if (items[i].type.indexOf('image') !== -1) {
                const blob = items[i].getAsFile();
                const reader = new FileReader();
                reader.onload = function(event) {
                    applyQimBase64Image(event.target.result, 'Pasted via Ctrl+V');
                };
                reader.readAsDataURL(blob);
                e.preventDefault();
                return;
            }
        }
    });

    // Drag and drop support on modal drop area
    const qimUploadArea = document.getElementById('qimUploadArea');
    if (qimUploadArea) {
        ['dragenter', 'dragover'].forEach(eventName => {
            qimUploadArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                qimUploadArea.style.borderColor = 'var(--pos-primary)';
                qimUploadArea.style.backgroundColor = '#e0f2fe';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            qimUploadArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                qimUploadArea.style.borderColor = '#94a3b8';
                qimUploadArea.style.backgroundColor = '#f8fafc';
            }, false);
        });

        qimUploadArea.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                const fakeInput = { files: files };
                qimHandleFile(fakeInput);
            }
        }, false);
    }

    // Save image via AJAX
    async function qimSubmitSave() {
        const state = window.qimState;
        if (!state.productId) return;

        if (state.mode === 'none') {
            if (quickImageModalInstance) quickImageModalInstance.hide();
            return;
        }

        const saveBtn = document.getElementById('qimSaveBtn');
        const origContent = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';

        const formData = new FormData();
        if (state.mode === 'file' && state.file) {
            formData.append('image', state.file);
        } else if (state.mode === 'base64' && state.base64) {
            formData.append('image_base64', state.base64);
        } else if (state.mode === 'url' && state.url) {
            formData.append('image_url', state.url);
        } else if (state.mode === 'remove' || state.remove) {
            formData.append('remove_image', '1');
        }

        try {
            const res = await fetch(`/admin/products/${state.productId}/quick-image`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });

            const data = await res.json();

            if (res.ok && data.success) {
                const imgWrap = document.getElementById(`img-wrap-${state.productId}`);
                const imgContainer = document.getElementById(`img-container-${state.productId}`);

                if (imgContainer && imgWrap) {
                    if (data.has_image) {
                        imgContainer.innerHTML = `<img src="${data.image_url}" alt="${state.productName}" class="product-img" id="product-img-${state.productId}">`;
                        imgWrap.dataset.hasImage = '1';
                        imgWrap.dataset.productImage = data.image_url;
                    } else {
                        imgContainer.innerHTML = `<div class="product-img-placeholder" id="product-placeholder-${state.productId}"><i class="bi bi-camera"></i></div>`;
                        imgWrap.dataset.hasImage = '0';
                        imgWrap.dataset.productImage = '';
                    }
                }

                if (quickImageModalInstance) quickImageModalInstance.hide();
                showStockToast(data.message || `Image for "${state.productName}" updated successfully.`, true);
            } else {
                showStockToast(data.message || 'Error updating product image.', false);
            }
        } catch (err) {
            console.error(err);
            showStockToast('Network error while saving image.', false);
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = origContent;
        }
    }

    // Enter key listener: Pressing Enter saves image when Quick Image Modal is active
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            const modalEl = document.getElementById('quickImageModal');
            if (modalEl && modalEl.classList.contains('show')) {
                const urlInput = document.getElementById('qimUrlInput');
                // If user is focused on the URL input and entered a URL, load & save
                if (document.activeElement === urlInput && urlInput.value.trim()) {
                    e.preventDefault();
                    qimLoadFromUrl();
                    setTimeout(() => {
                        qimSubmitSave();
                    }, 80);
                    return;
                }
                e.preventDefault();
                qimSubmitSave();
            }
        }
    });
</script>
@endpush


@extends('layouts.admin')

@section('title', 'Products')

@push('styles')
<style>
.stock-badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.stock-in    { background: #d1fae5; color: #065f46; }
.stock-low   { background: #fef3c7; color: #92400e; }
.stock-out   { background: #fee2e2; color: #991b1b; }
.product-img { width: 44px; height: 44px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb; }
.product-img-placeholder { width: 44px; height: 44px; background: #f3f4f6; border-radius: 8px; display:flex; align-items:center; justify-content:center; border: 1px solid #e5e7eb; color:#d1d5db; font-size:18px; }
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
            <div style="min-width:160px;">
                <label class="form-label" style="font-size:12px; font-weight:600; color:#6b7280; margin-bottom:4px;">Category</label>
                <select name="category_id" class="pos-input">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Supplier --}}
            <div style="min-width:170px;">
                <label class="form-label" style="font-size:12px; font-weight:600; color:#6b7280; margin-bottom:4px;">Supplier</label>
                <select name="supplier_id" class="pos-input">
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
        <table class="table pos-table mb-0">
            <thead>
                <tr>
                    <th width="60">Image</th>
                    <th>Product</th>
                    <th>SKU / Barcode</th>
                    <th>Supplier</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th>Cost</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th width="120">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    {{-- Image --}}
                    <td>
                        @if($product->image)
                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="product-img">
                        @else
                            <div class="product-img-placeholder"><i class="bi bi-image"></i></div>
                        @endif
                    </td>

                    {{-- Product name + description --}}
                    <td>
                        <div style="font-weight:600; color:#111827;">{{ $product->name }}</div>
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

                    {{-- Stock --}}
                    <td>
                        @php $status = $product->stock_status; @endphp
                        <div style="font-weight:700; font-size:15px; color:#111827;">{{ $product->stock_quantity }}</div>
                        <span class="stock-badge {{ $status === 'in_stock' ? 'stock-in' : ($status === 'low_stock' ? 'stock-low' : 'stock-out') }}">
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
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="btn btn-sm btn-outline-secondary" style="border-radius:7px;" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.products.toggle-status', $product) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $product->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                    style="border-radius:7px;" title="{{ $product->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="bi {{ $product->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
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

        {{-- Pagination --}}
        @if($products->hasPages())
            <div class="px-4 py-3 border-top d-flex justify-content-between align-items-center">
                <p class="text-muted mb-0" style="font-size:13px;">
                    Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }} products
                </p>
                {{ $products->links('vendor.pagination.bootstrap-5') }}
            </div>
        @endif
    @endif
</div>

@endsection

@push('scripts')
<script>
    setTimeout(() => {
        document.querySelectorAll('.pos-alert').forEach(el => {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        });
    }, 4000);
</script>
@endpush

@extends('layouts.admin')

@section('title', 'Categories')

@section('content')

{{-- Page Header --}}
<div class="page-hero d-flex align-items-start justify-content-between">
    <div>
        <h1><i class="bi bi-tags me-2" style="color:var(--pos-primary)"></i>Categories</h1>
        <p>Manage product categories for the store and public catalog.</p>
    </div>
    <button class="btn-pos" data-bs-toggle="modal" data-bs-target="#createModal">
        <i class="bi bi-plus-lg"></i> Add Category
    </button>
</div>

{{-- Flash Messages --}}
@if(session('success'))
    <div class="pos-alert pos-alert-success mb-4">
        <i class="bi bi-check-circle-fill fs-5"></i>
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="pos-alert pos-alert-error mb-4">
        <i class="bi bi-exclamation-circle-fill fs-5"></i>
        {{ session('error') }}
    </div>
@endif

{{-- Filter bar --}}
<div class="pos-card p-3 mb-4">
    <form method="GET" action="{{ route('admin.categories.index') }}" class="filter-bar">
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search categories..."
            class="pos-input"
            style="max-width:280px;"
        >
        <select name="status" class="pos-input" style="max-width:160px;">
            <option value="">All Status</option>
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="btn-pos">
            <i class="bi bi-funnel"></i> Filter
        </button>
        @if(request('search') || request('status'))
            <a href="{{ route('admin.categories.index') }}" class="btn-pos-outline">
                <i class="bi bi-x-circle"></i> Clear
            </a>
        @endif
    </form>
</div>

{{-- Categories Table --}}
<div class="pos-card">
    @if($categories->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-tags" style="font-size:48px; color:#d1d5db;"></i>
            <p class="mt-3 text-muted">No categories found.
                <a href="#" data-bs-toggle="modal" data-bs-target="#createModal">Create the first one.</a>
            </p>
        </div>
    @else
        <table class="table pos-table mb-0">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Category Name</th>
                    <th>Slug</th>
                    <th>Icon</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th width="140">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $index => $category)
                <tr>
                    <td class="text-muted" style="font-size:13px;">
                        {{ $categories->firstItem() + $index }}
                    </td>
                    <td>
                        <div style="font-weight:600; color:#111827;">{{ $category->name }}</div>
                        @if($category->description)
                            <div style="font-size:12px; color:#9ca3af; margin-top:2px;">
                                {{ Str::limit($category->description, 60) }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <code style="background:#f3f4f6; padding:3px 8px; border-radius:5px; font-size:12px;">
                            {{ $category->slug }}
                        </code>
                    </td>
                    <td>
                        @if($category->icon)
                            <i class="bi {{ $category->icon }} fs-5" style="color:var(--pos-primary);" title="{{ $category->icon }}"></i>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="fw-semibold">{{ $category->products_count }}</span>
                        <span class="text-muted" style="font-size:12px;">products</span>
                    </td>
                    <td>
                        @if($category->is_active)
                            <span class="badge badge-active rounded-pill px-3 py-1">
                                <i class="bi bi-circle-fill me-1" style="font-size:8px;"></i>Active
                            </span>
                        @else
                            <span class="badge badge-inactive rounded-pill px-3 py-1">
                                <i class="bi bi-circle me-1" style="font-size:8px;"></i>Inactive
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            {{-- Edit button --}}
                            <button
                                class="btn btn-sm btn-outline-secondary"
                                style="border-radius:7px;"
                                onclick="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}', '{{ $category->icon ?? '' }}', {{ $category->is_active ? 'true' : 'false' }})"
                                title="Edit"
                            >
                                <i class="bi bi-pencil"></i>
                            </button>

                            {{-- Toggle status --}}
                            <form method="POST" action="{{ route('admin.categories.toggle-status', $category) }}">
                                @csrf @method('PATCH')
                                <button
                                    type="submit"
                                    class="btn btn-sm {{ $category->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                    style="border-radius:7px;"
                                    title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}"
                                >
                                    <i class="bi {{ $category->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                </button>
                            </form>

                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                onsubmit="return confirm('Delete category \'{{ addslashes($category->name) }}\'? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="btn btn-sm btn-outline-danger"
                                    style="border-radius:7px;"
                                    title="Delete">
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
        @if($categories->hasPages())
            <div class="px-4 py-3 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <p class="text-muted mb-0" style="font-size:13px;">
                        Showing {{ $categories->firstItem() }}–{{ $categories->lastItem() }} of {{ $categories->total() }} categories
                    </p>
                    {{ $categories->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
        @endif
    @endif
</div>

{{-- ═══════════════════════════════════════════════════ --}}
{{-- CREATE MODAL --}}
{{-- ═══════════════════════════════════════════════════ --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">
                    <i class="bi bi-plus-circle me-2" style="color:var(--pos-primary)"></i>
                    Add New Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                @if($errors->any() && !old('_edit_mode'))
                    <div class="pos-alert pos-alert-error mb-3">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Name --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;">
                        Category Name <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        class="pos-input"
                        placeholder="e.g. Power Tools"
                        required
                        id="createName"
                    >
                    <div style="font-size:12px; color:#9ca3af; margin-top:4px;">
                        Slug preview: <code id="slugPreview">—</code>
                    </div>
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;">Description</label>
                    <textarea
                        name="description"
                        class="pos-input"
                        rows="2"
                        placeholder="Optional short description..."
                        style="resize:vertical;"
                    >{{ old('description') }}</textarea>
                </div>

                {{-- Icon --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;">
                        Bootstrap Icon Class
                        <a href="https://icons.getbootstrap.com/" target="_blank" style="font-size:11px; margin-left:6px; color:var(--pos-primary);">
                            <i class="bi bi-box-arrow-up-right"></i> Browse icons
                        </a>
                    </label>
                    <div class="input-group">
                        <input
                            type="text"
                            name="icon"
                            value="{{ old('icon') }}"
                            class="pos-input"
                            placeholder="e.g. bi-tools"
                            id="createIcon"
                            style="border-radius:8px 0 0 8px;"
                        >
                        <span class="input-group-text" style="background:#f9fafb; border:1.5px solid #e5e7eb; border-left:none; border-radius:0 8px 8px 0; min-width:44px; justify-content:center;">
                            <i id="iconPreview" class="bi bi-question-circle" style="color:var(--pos-primary); font-size:18px;"></i>
                        </span>
                    </div>
                </div>

                {{-- Status --}}
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="createIsActive" value="1" checked>
                    <label class="form-check-label fw-semibold" for="createIsActive" style="font-size:13px;">Active</label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-pos">
                    <i class="bi bi-check-lg"></i> Save Category
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════ --}}
{{-- EDIT MODAL --}}
{{-- ═══════════════════════════════════════════════════ --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="editForm" class="modal-content">
            @csrf @method('PUT')
            <input type="hidden" name="_edit_mode" value="1">

            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">
                    <i class="bi bi-pencil-square me-2" style="color:var(--pos-primary)"></i>
                    Edit Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                @if($errors->any() && old('_edit_mode'))
                    <div class="pos-alert pos-alert-error mb-3">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;">
                        Category Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name" id="editName" class="pos-input" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;">Description</label>
                    <textarea name="description" id="editDescription" class="pos-input" rows="2" style="resize:vertical;"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:13px;">
                        Bootstrap Icon Class
                        <a href="https://icons.getbootstrap.com/" target="_blank" style="font-size:11px; margin-left:6px; color:var(--pos-primary);">
                            <i class="bi bi-box-arrow-up-right"></i> Browse icons
                        </a>
                    </label>
                    <div class="input-group">
                        <input type="text" name="icon" id="editIcon" class="pos-input"
                            placeholder="e.g. bi-tools"
                            style="border-radius:8px 0 0 8px;">
                        <span class="input-group-text" style="background:#f9fafb; border:1.5px solid #e5e7eb; border-left:none; border-radius:0 8px 8px 0; min-width:44px; justify-content:center;">
                            <i id="editIconPreview" class="bi bi-question-circle" style="color:var(--pos-primary); font-size:18px;"></i>
                        </span>
                    </div>
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive" value="1">
                    <label class="form-check-label fw-semibold" for="editIsActive" style="font-size:13px;">Active</label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-pos">
                    <i class="bi bi-check-lg"></i> Update Category
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // ── Slug preview on create ──────────────────────
    document.getElementById('createName').addEventListener('input', function () {
        const slug = this.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
        document.getElementById('slugPreview').textContent = slug || '—';
    });

    // ── Icon preview (create) ───────────────────────
    document.getElementById('createIcon').addEventListener('input', function () {
        const icon = document.getElementById('iconPreview');
        icon.className = 'bi ' + (this.value || 'bi-question-circle');
    });

    // ── Icon preview (edit) ─────────────────────────
    document.getElementById('editIcon').addEventListener('input', function () {
        const icon = document.getElementById('editIconPreview');
        icon.className = 'bi ' + (this.value || 'bi-question-circle');
    });

    // ── Open edit modal ─────────────────────────────
    function openEditModal(id, name, description, icon, isActive) {
        const form = document.getElementById('editForm');
        // Set form action dynamically
        form.action = '/admin/categories/' + id;

        document.getElementById('editName').value        = name;
        document.getElementById('editDescription').value = description;
        document.getElementById('editIcon').value        = icon;
        document.getElementById('editIsActive').checked  = isActive;

        // Update icon preview
        const iconEl = document.getElementById('editIconPreview');
        iconEl.className = 'bi ' + (icon || 'bi-question-circle');

        const modal = new bootstrap.Modal(document.getElementById('editModal'));
        modal.show();
    }

    // ── Auto-open create modal if validation failed ──
    @if($errors->any() && !old('_edit_mode'))
        const createModal = new bootstrap.Modal(document.getElementById('createModal'));
        createModal.show();
    @endif

    // ── Auto-open edit modal if validation failed ────
    @if($errors->any() && old('_edit_mode'))
        const editModal = new bootstrap.Modal(document.getElementById('editModal'));
        editModal.show();
    @endif

    // ── Auto-dismiss flash alerts ───────────────────
    setTimeout(() => {
        document.querySelectorAll('.pos-alert').forEach(el => {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        });
    }, 4000);
</script>
@endpush

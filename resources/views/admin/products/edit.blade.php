@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')

<div class="page-hero d-flex flex-wrap align-items-start justify-content-between gap-3">
    <div>
        <h1><i class="bi bi-pencil-square me-2" style="color:var(--pos-primary)"></i>Edit Product</h1>
        <p>
            <a href="{{ route('admin.products.index', request()->query()) }}" style="color:var(--pos-primary); text-decoration:none; font-size:13px;">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
            &nbsp;&middot;&nbsp;
            <span style="font-size:13px; color:#9ca3af;">Editing: <strong style="color:#374151;">{{ $product->name }}</strong></span>
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline-info d-inline-flex align-items-center gap-1" style="font-weight:600; border-radius:8px;">
            <i class="bi bi-clock-history"></i> Sales &amp; Stock History
        </a>
    </div>
</div>

<form id="productForm" method="POST" action="{{ route('admin.products.update', array_merge(['product' => $product->id], request()->query())) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    @include('admin.products._form', ['submitLabel' => 'Update Product'])
</form>

@endsection

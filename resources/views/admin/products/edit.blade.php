@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')

<div class="page-hero d-flex align-items-start justify-content-between">
    <div>
        <h1><i class="bi bi-pencil-square me-2" style="color:var(--pos-primary)"></i>Edit Product</h1>
        <p>
            <a href="{{ route('admin.products.index') }}" style="color:var(--pos-primary); text-decoration:none; font-size:13px;">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
            &nbsp;&middot;&nbsp;
            <span style="font-size:13px; color:#9ca3af;">Editing: <strong style="color:#374151;">{{ $product->name }}</strong></span>
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    @include('admin.products._form', ['submitLabel' => 'Update Product'])
</form>

@endsection

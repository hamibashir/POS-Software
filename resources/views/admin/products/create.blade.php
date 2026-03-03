@extends('layouts.admin')

@section('title', 'Add Product')

@section('content')

<div class="page-hero d-flex align-items-start justify-content-between">
    <div>
        <h1><i class="bi bi-plus-circle me-2" style="color:var(--pos-primary)"></i>Add New Product</h1>
        <p>
            <a href="{{ route('admin.products.index') }}" style="color:var(--pos-primary); text-decoration:none; font-size:13px;">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
    @csrf
    @include('admin.products._form', ['submitLabel' => 'Create Product'])
</form>

@endsection

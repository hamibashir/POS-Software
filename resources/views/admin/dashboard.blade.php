@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="page-hero">
    <h1><i class="bi bi-speedometer2 me-2" style="color:var(--pos-primary)"></i>Dashboard</h1>
    <p>Here's what's happening in your store today.</p>
</div>

<div class="pos-card p-5 text-center text-muted">
    <i class="bi bi-bar-chart-line" style="font-size:48px; color:#d1d5db;"></i>
    <h5 class="mt-3">Dashboard widgets coming in the next prompt.</h5>
    <p style="font-size:14px;">The full dashboard with KPI cards and sales chart will be built soon.</p>
</div>
@endsection

@extends('layouts.catalog')

@section('title', $q ? 'Search: "' . $q . '" — Hassan & Sons' : 'All Products — Hassan & Sons')
@section('meta_description', $q ? 'Search results for "' . $q . '" in our sanitary and hardware product catalog.' : 'Browse all sanitary, hardware, plumbing, electrical, and tool products.')

@push('styles')
<style>
    /* ── Page grid ──────────────────────────────────────────── */
    .search-page-grid {
        display: grid;
        grid-template-columns: 240px 1fr;
        gap: 28px;
        align-items: start;
    }
    @media (max-width: 860px) {
        .search-page-grid { grid-template-columns: 1fr; }
        .cat-sidebar        { display: none; }
    }

    /* ── Breadcrumb ─────────────────────────────────────────── */
    .breadcrumb-row {
        display: flex; align-items: center; gap: 6px;
        font-size: 13px; font-weight: 500; color: #64748b;
        margin-bottom: 24px;
    }
    .breadcrumb-row a { color: #64748b; text-decoration: none; transition: color .15s; }
    .breadcrumb-row a:hover { color: var(--primary); }
    .breadcrumb-row .current { color: #0f172a; font-weight: 600; }
    .breadcrumb-row .ms-icon { font-size: 14px; color: #cbd5e1; }

    /* ── Sidebar ────────────────────────────────────────────── */
    .cat-sidebar {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        position: sticky;
        top: 76px;
    }
    .sidebar-title {
        font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .7px;
        color: #94a3b8; margin-bottom: 12px;
    }
    .sidebar-link {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 12px; border-radius: 10px;
        text-decoration: none; color: #374151;
        font-size: 14px; font-weight: 500;
        margin-bottom: 4px;
        transition: background .15s, color .15s;
        justify-content: space-between;
    }
    .sidebar-link:hover  { background: #f1f5f9; color: var(--primary); }
    .sidebar-link.all-active { background: var(--primary-lt); color: var(--primary); font-weight: 600; }
    .sidebar-link .left  { display: flex; align-items: center; gap: 8px; }
    .sidebar-link .ms-icon { font-size: 18px; }
    .sidebar-link .badge {
        background: #f1f5f9; color: #64748b;
        font-size: 11px; font-weight: 600;
        padding: 2px 7px; border-radius: 10px;
        min-width: 24px; text-align: center;
    }
    .sidebar-link.all-active .badge { background: rgba(30,109,138,.15); color: var(--primary); }

    /* ── Main header ────────────────────────────────────────── */
    .main-header {
        display: flex; align-items: flex-start;
        justify-content: space-between; flex-wrap: wrap; gap: 12px;
        margin-bottom: 22px;
    }
    .main-header h1 { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0; }
    .main-header .sub { font-size: 13px; color: #64748b; margin-top: 4px; }

    /* search bar */
    .inline-search { display: flex; gap: 8px; }
    .inline-search input {
        width: 240px; height: 40px;
        border: 1.5px solid #e2e8f0; border-radius: 8px;
        padding: 0 14px; font-size: 14px; color: #0f172a;
        outline: none; transition: border-color .15s; background: #f8fafc;
    }
    .inline-search input:focus { border-color: var(--primary); background: #fff; }
    .inline-search button {
        height: 40px; padding: 0 16px;
        background: var(--primary); color: #fff;
        border: none; border-radius: 8px; cursor: pointer;
        display: flex; align-items: center; gap: 6px;
        font-size: 14px; font-weight: 600;
        transition: background .15s; white-space: nowrap;
    }
    .inline-search button:hover { background: var(--primary-dk); }

    /* ── Product grid ───────────────────────────────────────── */
    .search-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }
    @media (max-width: 1100px) { .search-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px)  { .search-grid { grid-template-columns: 1fr; } }

    /* ── Product card ───────────────────────────────────────── */
    .prod-card {
        background: #fff; border-radius: 14px;
        border: 1px solid #e2e8f0; overflow: hidden;
        text-decoration: none; color: inherit;
        display: flex; flex-direction: column;
        transition: box-shadow .3s, border-color .3s;
    }
    .prod-card:hover {
        box-shadow: 0 16px 40px rgba(0,0,0,.10);
        border-color: rgba(30,109,138,.28);
    }
    .prod-img-wrap {
        width: 100%; aspect-ratio: 4/3;
        overflow: hidden; position: relative; background: #f1f5f9;
    }
    .prod-img-bg {
        width: 100%; height: 100%;
        background-size: cover; background-position: center;
        transition: transform .5s cubic-bezier(.25,.46,.45,.94);
    }
    .prod-card:hover .prod-img-bg { transform: scale(1.09); }
    .prod-img-bg.out-of-stock { opacity: .6; filter: grayscale(.5); }

    .stock-badge {
        position: absolute; top: 12px; left: 12px; z-index: 5;
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 11px; font-weight: 700;
        padding: 4px 9px; border-radius: 6px;
    }
    .stock-badge .dot { width: 6px; height: 6px; border-radius: 50%; display: block; flex-shrink: 0; }
    .badge-instock  { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-instock  .dot { background: #22c55e; animation: pulse-dot 2s infinite; }
    .badge-lowstock { background: #fef9c3; color: #a16207; border: 1px solid #fde68a; }
    .badge-lowstock .dot { background: #eab308; }
    .badge-outstock { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-outstock .dot { background: #ef4444; }
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: .5; transform: scale(.8); }
    }

    .prod-img-overlay {
        position: absolute; inset: 0; z-index: 4;
        background: rgba(0,0,0,.18);
        display: flex; align-items: center; justify-content: center;
        opacity: 0; transition: opacity .3s;
    }
    .prod-card:hover .prod-img-overlay { opacity: 1; }
    .prod-overlay-btn {
        background: #fff; color: #0f172a;
        padding: 9px 18px; border-radius: 8px; border: none;
        font-size: 13px; font-weight: 600; cursor: pointer;
        transform: translateY(14px);
        transition: transform .3s cubic-bezier(.25,.46,.45,.94);
        box-shadow: 0 4px 12px rgba(0,0,0,.18);
    }
    .prod-card:hover .prod-overlay-btn { transform: translateY(0); }

    .prod-card-body { padding: 16px; flex: 1; display: flex; flex-direction: column; }
    .prod-cat-label { font-size: 11px; color: #64748b; margin-bottom: 5px; }
    .prod-title {
        font-size: 15px; font-weight: 700; color: #0f172a;
        line-height: 1.35; margin-bottom: 6px; transition: color .15s;
    }
    .prod-card:hover .prod-title { color: var(--primary); }
    .prod-description {
        font-size: 13px; color: #475569; line-height: 1.55;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden; margin-bottom: 14px;
    }
    .prod-card-footer {
        margin-top: auto;
        display: flex; align-items: center; justify-content: space-between;
    }
    .prod-price { font-size: 18px; font-weight: 700; color: #0f172a; }
    .prod-price.strike { color: #94a3b8; text-decoration: line-through; }
    .wish-btn {
        width: 34px; height: 34px; border-radius: 8px;
        border: none; background: transparent;
        display: flex; align-items: center; justify-content: center;
        color: var(--primary); cursor: pointer; transition: background .15s;
    }
    .wish-btn:hover { background: var(--primary-lt); }

    /* ── Pagination ─────────────────────────────────────────── */
    .pg-wrap { display: flex; justify-content: center; margin-top: 32px; gap: 6px; }
    .pg-wrap .page-link {
        display: flex; align-items: center; justify-content: center;
        min-width: 36px; height: 36px; padding: 0 10px;
        border-radius: 8px; border: 1px solid #e2e8f0;
        font-size: 13px; font-weight: 600; color: #374151;
        text-decoration: none; background: #fff;
        transition: all .15s;
    }
    .pg-wrap .page-link:hover   { border-color: var(--primary); color: var(--primary); background: var(--primary-lt); }
    .pg-wrap .page-link.active  { background: var(--primary); color: #fff; border-color: var(--primary); }
    .pg-wrap .page-link.disabled{ color: #cbd5e1; pointer-events: none; }

    /* ── Category banner strip ──────────────────────────────── */
    .cat-banner-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 12px;
        margin-bottom: 32px;
    }
    .cat-banner-card {
        display: flex; flex-direction: column;
        align-items: center; text-align: center;
        padding: 16px 10px; border-radius: 12px;
        background: #fff; border: 1px solid #e2e8f0;
        text-decoration: none; color: var(--text);
        transition: border-color .2s, box-shadow .2s, transform .2s;
    }
    .cat-banner-card:hover {
        border-color: rgba(30,109,138,.45);
        box-shadow: 0 4px 16px rgba(0,0,0,.08);
        transform: translateY(-2px);
    }
    .cat-banner-card .icon-wrap {
        width: 48px; height: 48px; border-radius: 50%;
        background: #eff6ff;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 8px; transition: background .2s;
    }
    .cat-banner-card:hover .icon-wrap { background: var(--primary-lt); }
    .cat-banner-card .ms-icon { font-size: 22px; color: var(--primary); }
    .cat-banner-card .cat-name { font-size: 12px; font-weight: 600; color: #374151; }
    .cat-banner-card .cat-count { font-size: 11px; color: #94a3b8; margin-top: 2px; }
</style>
@endpush

@section('content')
<div class="cat-page">

    {{-- Breadcrumb --}}
    <nav class="breadcrumb-row">
        <a href="{{ route('catalog.home') }}">Home</a>
        <span class="material-symbols-outlined ms-icon">chevron_right</span>
        @if($q)
            <a href="{{ route('catalog.search') }}" style="color:#64748b;">All Products</a>
            <span class="material-symbols-outlined ms-icon">chevron_right</span>
            <span class="current">Search: "{{ $q }}"</span>
        @else
            <span class="current">All Products</span>
        @endif
    </nav>

    {{-- ── Category quick-access strip (only on browse-all, not search results) --}}
    @if(!$q && $categories->count())
    @php
    $iconMap = [
        'electric tool' => 'handyman',
        'hand tool'     => 'construction',
        'sanitary'      => 'plumbing',
        'paint'         => 'format_paint',
        'hardware'      => 'hardware',
        'appliance'     => 'kitchen',
        'light'         => 'lightbulb',
        'electric'      => 'bolt',
        'power'         => 'handyman',
        'tool'          => 'construction',
    ];
    @endphp
    <div class="cat-banner-grid">
        @foreach($categories as $cat)
        @php
            $msIcon = 'category';
            foreach ($iconMap as $k => $v) {
                if (stripos($cat->name, $k) !== false || ($cat->icon && stripos($cat->icon, $k) !== false)) {
                    $msIcon = $v; break;
                }
            }
        @endphp
        <a href="{{ route('catalog.category', $cat->slug) }}" class="cat-banner-card">
            <div class="icon-wrap">
                <span class="material-symbols-outlined ms-icon">{{ $msIcon }}</span>
            </div>
            <span class="cat-name">{{ $cat->name }}</span>
            <span class="cat-count">{{ $cat->products_count }} items</span>
        </a>
        @endforeach
    </div>
    @endif

    <div class="search-page-grid">

        {{-- ── Sidebar ───────────────────────────────────── --}}
        <aside class="cat-sidebar">
            <div class="sidebar-title">Categories</div>
            <a href="{{ route('catalog.search') }}"
               class="sidebar-link {{ !$q ? 'all-active' : '' }}">
                <span class="left">
                    <span class="material-symbols-outlined ms-icon">grid_view</span>
                    All Products
                </span>
                <span class="badge">{{ $products->total() }}</span>
            </a>

            @php
            $iconMap2 = [
                'electric tool' => 'handyman',
                'hand tool'     => 'construction',
                'sanitary'      => 'plumbing',
                'paint'         => 'format_paint',
                'hardware'      => 'hardware',
                'appliance'     => 'kitchen',
                'light'         => 'lightbulb',
                'electric'      => 'bolt',
                'power'         => 'handyman',
                'tool'          => 'construction',
            ];
            @endphp
            @foreach($categories as $cat)
            @php
                $msIcon = 'category';
                foreach ($iconMap2 as $k => $v) {
                    if (stripos($cat->name, $k) !== false || ($cat->icon && stripos($cat->icon, $k) !== false)) {
                        $msIcon = $v; break;
                    }
                }
            @endphp
            <a href="{{ route('catalog.category', $cat->slug) }}" class="sidebar-link">
                <span class="left">
                    <span class="material-symbols-outlined ms-icon">{{ $msIcon }}</span>
                    {{ $cat->name }}
                </span>
                <span class="badge">{{ $cat->products_count }}</span>
            </a>
            @endforeach
        </aside>

        {{-- ── Main ─────────────────────────────────────── --}}
        <div>
            <div class="main-header">
                <div>
                    <h1>
                        @if($q)
                            Results for "<span style="color:var(--primary);">{{ $q }}</span>"
                        @else
                            All Products
                        @endif
                    </h1>
                    <p class="sub">
                        {{ $products->total() }} product{{ $products->total() !== 1 ? 's' : '' }} found
                        @if($q) &nbsp;·&nbsp;
                            <a href="{{ route('catalog.search') }}"
                               style="color:var(--primary);text-decoration:none;font-weight:600;">
                                Clear search
                            </a>
                        @endif
                    </p>
                </div>
                <form action="{{ route('catalog.search') }}" method="GET" class="inline-search">
                    <input type="text" name="q" value="{{ $q }}"
                           placeholder="Search all products…">
                    <button type="submit">
                        <span class="material-symbols-outlined" style="font-size:16px;">search</span>
                        Search
                    </button>
                </form>
            </div>

            @if($products->count())
            <div class="search-grid">
                @foreach($products as $product)
                <a href="{{ route('catalog.product', $product->slug) }}" class="prod-card">
                    <div class="prod-img-wrap">
                        @if($product->stock_status === 'in_stock')
                            <span class="stock-badge badge-instock"><span class="dot"></span> In Stock</span>
                        @elseif($product->stock_status === 'low_stock')
                            <span class="stock-badge badge-lowstock"><span class="dot"></span> Low Stock</span>
                        @else
                            <span class="stock-badge badge-outstock"><span class="dot"></span> Out of Stock</span>
                        @endif
                        <div class="prod-img-bg {{ $product->stock_status === 'out_of_stock' ? 'out-of-stock' : '' }}"
                             style="background-image: url('{{ $product->image_url }}');"></div>
                        <div class="prod-img-overlay">
                            <button class="prod-overlay-btn" tabindex="-1">
                                @if($product->stock_status === 'out_of_stock') Notify Me @else Quick View @endif
                            </button>
                        </div>
                    </div>
                    <div class="prod-card-body">
                        <div class="prod-cat-label">{{ $product->category?->name }}</div>
                        <div class="prod-title">{{ $product->name }}</div>
                        @if($product->description)
                        <div class="prod-description">{{ $product->description }}</div>
                        @endif
                        <div class="prod-card-footer">
                            <div class="prod-price {{ $product->stock_status === 'out_of_stock' ? 'strike' : '' }}">
                                Rs.&nbsp;{{ number_format($product->sale_price, 0) }}
                            </div>
                            <button class="wish-btn" onclick="return false;" title="Wishlist">
                                <span class="material-symbols-outlined" style="font-size:20px;">favorite</span>
                            </button>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($products->hasPages())
            <div class="pg-wrap">
                @if($products->onFirstPage())
                    <span class="page-link disabled">
                        <span class="material-symbols-outlined" style="font-size:16px;">chevron_left</span>
                    </span>
                @else
                    <a href="{{ $products->previousPageUrl() }}" class="page-link">
                        <span class="material-symbols-outlined" style="font-size:16px;">chevron_left</span>
                    </a>
                @endif
                @foreach($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                    <a href="{{ $url }}" class="page-link {{ $page == $products->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                @endforeach
                @if($products->hasMorePages())
                    <a href="{{ $products->nextPageUrl() }}" class="page-link">
                        <span class="material-symbols-outlined" style="font-size:16px;">chevron_right</span>
                    </a>
                @else
                    <span class="page-link disabled">
                        <span class="material-symbols-outlined" style="font-size:16px;">chevron_right</span>
                    </span>
                @endif
            </div>
            @endif

            @else
            <div style="text-align:center;padding:72px 24px;color:#94a3b8;">
                <span class="material-symbols-outlined" style="font-size:52px;display:block;margin-bottom:14px;">search_off</span>
                <p style="font-size:16px;color:#475569;">
                    No products found for <strong>"{{ $q }}"</strong>.
                </p>
                <a href="{{ route('catalog.search') }}"
                   style="display:inline-block;margin-top:12px;color:var(--primary);font-weight:600;text-decoration:none;">
                    ← Browse All Products
                </a>
            </div>
            @endif
        </div>{{-- /main --}}
    </div>{{-- /search-page-grid --}}
</div>
@endsection

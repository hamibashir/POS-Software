@extends('layouts.catalog')

@section('title', 'Hassan & Sons — Tools, Plumbing, Sanitary & More')
@section('meta_description', 'Discover professional-grade hardware tools, sanitary ware, plumbing, and electrical supplies at Hassan & Sons. Shop our full catalog and contact us for orders.')

@push('styles')
<style>
    /* ── Hero ──────────────────────────────────────────────── */
    .hero-section {
        position: relative;
        border-radius: 18px;
        overflow: hidden;
        min-height: 400px;
        display: flex;
        align-items: center;
        background: #0f172a;
        margin-bottom: 40px;
    }
    .hero-bg {
        position: absolute; inset: 0; z-index: 0;
        background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAWjtQjxl0QCsb1PoGdT3yp7qTmkfDpHtXZNfjmrNrtb6tDZs8FV7P-HpR-39_gn_FTlp5-XnmI65pt6Y6x7ud_IT1V0sYgbyEDRml6o9A_KXCyq8y6IvuG8rBpwpsAaR-IyGy6_Egy1c2SSY9UW5fZzPJDzyjCX-87bYHXuDkx7FZJrR3m3bN_Ec5gQXA6E4TYU95QJ7gzGybIC-8Ewch2B_nI6DC0SMLGLMOKuJMCqd-HJl_qXCfY7AMUFdgKbRD2pc179ZCLC6X7');
        background-size: cover;
        background-position: center;
    }
    .hero-overlay {
        position: absolute; inset: 0; z-index: 1;
        background: linear-gradient(to right,
            rgba(0,0,0,.85) 0%,
            rgba(0,0,0,.50) 50%,
            transparent 100%);
    }
    .hero-content {
        position: relative; z-index: 2;
        padding: 56px 48px;
        max-width: 640px;
    }
    .hero-badge {
        display: inline-block;
        background: rgba(30,109,138,.22);
        border: 1px solid rgba(30,109,138,.35);
        color: #bae6fd;
        font-size: 11px; font-weight: 700; letter-spacing: .5px;
        padding: 5px 14px; border-radius: 20px;
        margin-bottom: 18px;
        backdrop-filter: blur(6px);
    }
    .hero-title {
        font-size: clamp(34px, 5.5vw, 60px);
        font-weight: 900; color: #fff;
        line-height: 1.08; letter-spacing: -.5px;
        margin-bottom: 18px;
    }
    .hero-title .highlight {
        background: linear-gradient(90deg, #93c5fd, #5eead4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .hero-sub {
        font-size: 16px; color: #cbd5e1;
        line-height: 1.7; margin-bottom: 28px; max-width: 440px;
    }
    .hero-btns { display: flex; gap: 12px; flex-wrap: wrap; }
    .btn-hero-primary {
        padding: 13px 22px; background: var(--primary);
        color: #fff; font-size: 14px; font-weight: 700;
        border: none; border-radius: 10px; cursor: pointer;
        display: inline-flex; align-items: center; gap: 8px;
        text-decoration: none;
        box-shadow: 0 4px 16px rgba(30,109,138,.40);
        transition: background .15s, transform .15s;
    }
    .btn-hero-primary:hover { background: var(--primary-dk); transform: translateY(-2px); color: #fff; }
    .btn-hero-outline {
        padding: 13px 22px;
        background: rgba(255,255,255,.12);
        color: #fff !important; font-size: 14px; font-weight: 600;
        border: 1px solid rgba(255,255,255,.30); border-radius: 10px; cursor: pointer;
        display: inline-flex; align-items: center; gap: 8px;
        text-decoration: none !important;
        backdrop-filter: blur(4px);
        transition: background .15s;
    }
    .btn-hero-outline:hover { background: rgba(255,255,255,.22); color: #fff !important; }

    /* ── Category icon grid ─────────────────────────────── */
    .cat-icon-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 14px;
    }
    @media (max-width: 960px)  { .cat-icon-grid { grid-template-columns: repeat(4, 1fr); } }
    @media (max-width: 640px)  { .cat-icon-grid { grid-template-columns: repeat(3, 1fr); } }

    .cat-icon-card {
        display: flex; flex-direction: column;
        align-items: center; text-align: center;
        padding: 16px 12px; border-radius: 14px;
        background: #fff; border: 1px solid #e2e8f0;
        text-decoration: none; color: var(--text);
        transition: border-color .2s, box-shadow .2s, transform .2s;
        cursor: pointer;
    }
    .cat-icon-card:hover {
        border-color: rgba(30,109,138,.45);
        box-shadow: 0 4px 18px rgba(0,0,0,.09);
        transform: translateY(-3px);
    }
    .cat-icon-card .icon-wrap {
        width: 60px; height: 60px; border-radius: 50%;
        background: #eff6ff;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 10px;
        transition: background .2s;
    }
    .cat-icon-card:hover .icon-wrap { background: var(--primary-lt); }
    .cat-icon-card .ms-icon { font-size: 28px; color: var(--primary); }
    .cat-icon-card .cat-name { font-size: 13px; font-weight: 600; color: var(--text); transition: color .15s; }
    .cat-icon-card:hover .cat-name { color: var(--primary); }
    .cat-icon-card .cat-count { font-size: 11px; color: #94a3b8; margin-top: 3px; }

    /* ── Filter pills ──────────────────────────────────── */
    .filter-pill {
        padding: 8px 18px; border-radius: 20px; border: none;
        font-size: 13px; font-weight: 600; cursor: pointer;
        white-space: nowrap; transition: background .15s, color .15s;
    }
    .filter-pill.active  { background: #0f172a; color: #fff; }
    .filter-pill:not(.active) { background: #e8edf2; color: #475569; }
    .filter-pill:not(.active):hover { background: #dde3ea; }

    /* ── Product grid ──────────────────────────────────── */
    .prod-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 22px;
    }
    @media (max-width: 1180px) { .prod-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 780px)  { .prod-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px)  { .prod-grid { grid-template-columns: 1fr; } }

    /* ── Product card ──────────────────────────────────── */
    .prod-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        text-decoration: none; color: inherit;
        display: flex; flex-direction: column;
        transition: box-shadow .3s, border-color .3s;
    }
    .prod-card:hover {
        box-shadow: 0 20px 48px rgba(0,0,0,.11);
        border-color: rgba(30,109,138,.28);
    }

    /* image area */
    .prod-img-wrap {
        width: 100%; aspect-ratio: 4/3;
        overflow: hidden; position: relative;
        background: #f1f5f9;
    }
    .prod-img-bg {
        width: 100%; height: 100%;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        transition: transform .5s cubic-bezier(.25,.46,.45,.94);
    }
    .prod-card:hover .prod-img-bg { transform: scale(1.09); }

    /* grayscale for out-of-stock */
    .prod-img-bg.out-of-stock { opacity: .6; filter: grayscale(.5); }

    /* stock badge */
    .stock-badge {
        position: absolute; top: 12px; left: 12px; z-index: 5;
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 11px; font-weight: 700;
        padding: 4px 9px; border-radius: 6px;
    }
    .stock-badge .dot {
        width: 6px; height: 6px; border-radius: 50%; display: block; flex-shrink: 0;
    }
    /* pulse only for in-stock */
    .badge-instock  { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-instock  .dot { background: #22c55e; animation: pulse-dot 2s infinite; }
    .badge-lowstock { background: #fef9c3; color: #a16207; border: 1px solid #fde68a; }
    .badge-lowstock .dot { background: #eab308; }
    .badge-outstock { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-outstock .dot { background: #ef4444; }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: .6; transform: scale(.8); }
    }

    /* hover overlay */
    .prod-img-overlay {
        position: absolute; inset: 0; z-index: 4;
        background: rgba(0,0,0,.20);
        display: flex; align-items: center; justify-content: center;
        opacity: 0;
        transition: opacity .3s;
    }
    .prod-card:hover .prod-img-overlay { opacity: 1; }
    .prod-overlay-btn {
        background: #fff; color: #0f172a;
        padding: 9px 18px; border-radius: 8px; border: none;
        font-size: 13px; font-weight: 600; cursor: pointer;
        transform: translateY(14px);
        transition: transform .3s cubic-bezier(.25,.46,.45,.94);
        display: flex; align-items: center; gap: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,.18);
    }
    .prod-card:hover .prod-overlay-btn { transform: translateY(0); }

    /* card body */
    .prod-card-body {
        padding: 16px;
        flex: 1;
        display: flex; flex-direction: column;
    }
    .prod-cat-label {
        font-size: 11px; font-weight: 500;
        color: #64748b; margin-bottom: 5px;
        text-transform: capitalize;
    }
    .prod-title {
        font-size: 15px; font-weight: 700;
        color: #0f172a; line-height: 1.35;
        margin-bottom: 6px;
        transition: color .15s;
    }
    .prod-card:hover .prod-title { color: var(--primary); }
    .prod-description {
        font-size: 13px; color: #475569; line-height: 1.55;
        display: -webkit-box;
        -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 14px;
    }
    .prod-card-footer {
        margin-top: auto;
        display: flex; align-items: center; justify-content: space-between;
    }
    .prod-price {
        font-size: 20px; font-weight: 700; color: #0f172a;
    }
    .prod-price.strike {
        font-size: 18px; font-weight: 700;
        color: #94a3b8; text-decoration: line-through;
    }
    .wish-btn {
        width: 36px; height: 36px;
        border-radius: 8px; border: none; background: transparent;
        display: flex; align-items: center; justify-content: center;
        color: var(--primary); cursor: pointer;
        transition: background .15s;
    }
    .wish-btn:hover { background: var(--primary-lt); }
    .wish-btn.disabled { color: #cbd5e1; cursor: default; }

    /* ── Load more ──────────────────────────────────────── */
    .btn-load-more {
        display: inline-block;
        padding: 13px 36px; background: #fff;
        border: 1px solid #e2e8f0; border-radius: 10px;
        font-size: 14px; font-weight: 600; color: #0f172a;
        text-decoration: none; cursor: pointer;
        box-shadow: 0 1px 4px rgba(0,0,0,.06);
        transition: background .15s, box-shadow .15s;
    }
    .btn-load-more:hover { background: #f8fafc; box-shadow: 0 2px 8px rgba(0,0,0,.09); color: #0f172a; }

    /* ── Section heading ────────────────────────────────── */
    .sec-title { font-size: 22px; font-weight: 700; color: #0f172a; }
    .sec-sub   { font-size: 13px; color: #64748b; margin-top: 4px; }
</style>
@endpush

@section('content')
<div class="cat-page">

    {{-- ── Hero ─────────────────────────────────────────────── --}}
    <section class="hero-section">
        <div class="hero-bg"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <span class="hero-badge">New Arrivals</span>
            <h1 class="hero-title">
                Build Your<br>
                <span class="highlight">Dream Project</span>
            </h1>
            <p class="hero-sub">
                Discover professional-grade tools and materials for<br>
                any job, big or small.
            </p>
            <div class="hero-btns">
                <a href="#products" class="btn-hero-primary">
                    Shop Now
                    <span class="material-symbols-outlined" style="font-size:17px;">arrow_forward</span>
                </a>
                <a href="tel:051-8891930" class="btn-hero-outline" onclick="openPhoneOrderModal(); return false;">
                    <i class="bi bi-telephone-fill"></i>
                    Order by Phone: 051-8891930
                </a>
                <a href="{{ route('catalog.search') }}" class="btn-hero-outline">
                    View Catalog
                </a>
            </div>
        </div>
    </section>

    {{-- ── Featured Categories ────────────────────────────── --}}
    @if($categories->count())
    <section style="margin-bottom:44px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <h2 class="sec-title">Featured Categories</h2>
            <a href="{{ route('catalog.search', ['q' => '']) }}"
               style="font-size:13px;font-weight:600;color:var(--primary);text-decoration:none;
                      display:flex;align-items:center;gap:3px;">
                View All
                <span class="material-symbols-outlined" style="font-size:16px;">chevron_right</span>
            </a>
        </div>

        @php
        $iconMap = [
            'power'     => 'handyman',
            'hand'      => 'construction',
            'electric'  => 'bolt',
            'plumb'     => 'plumbing',
            'sanitary'  => 'plumbing',
            'paint'     => 'format_paint',
            'garden'    => 'yard',
            'fastener'  => 'settings',
            'screw'     => 'settings',
            'lumber'    => 'forest',
            'wood'      => 'forest',
            'safety'    => 'shield',
            'tool'      => 'handyman',
        ];
        @endphp

        <div class="cat-icon-grid">
            @foreach($categories as $cat)
            @php
                $msIcon = 'category';
                foreach ($iconMap as $k => $v) {
                    if (stripos($cat->name, $k) !== false || ($cat->icon && stripos($cat->icon, $k) !== false)) {
                        $msIcon = $v; break;
                    }
                }
            @endphp
            <a href="{{ route('catalog.category', $cat->slug) }}" class="cat-icon-card">
                <div class="icon-wrap">
                    <span class="material-symbols-outlined ms-icon">{{ $msIcon }}</span>
                </div>
                <span class="cat-name">{{ $cat->name }}</span>
                <span class="cat-count">{{ $cat->products_count }}+ Items</span>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ── Popular Products ────────────────────────────────── --}}
    @if($featured->count())
    <section id="products">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;
                    flex-wrap:wrap;gap:12px;margin-bottom:22px;">
            <div>
                <h2 class="sec-title">Popular Products</h2>
                <p class="sec-sub">Our top-selling sanitary & hardware essentials.</p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <button class="filter-pill active">All Items</button>
                <button class="filter-pill">On Sale</button>
                <button class="filter-pill">Under $50</button>
                <button class="filter-pill" style="display:inline-flex;align-items:center;gap:4px;">
                    Filters
                    <span class="material-symbols-outlined" style="font-size:15px;">filter_list</span>
                </button>
            </div>
        </div>

        <div class="prod-grid">
            @foreach($featured as $product)
            <a href="{{ route('catalog.product', $product->slug) }}" class="prod-card">

                {{-- Image area --}}
                <div class="prod-img-wrap">
                    {{-- Stock badge --}}
                    @if($product->stock_status === 'in_stock')
                        <span class="stock-badge badge-instock">
                            <span class="dot"></span> In Stock
                        </span>
                    @elseif($product->stock_status === 'low_stock')
                        <span class="stock-badge badge-lowstock">
                            <span class="dot"></span> Low Stock
                        </span>
                    @else
                        <span class="stock-badge badge-outstock">
                            <span class="dot"></span> Out of Stock
                        </span>
                    @endif

                    {{-- Product image as background div (scale on hover) --}}
                    <div class="prod-img-bg {{ $product->stock_status === 'out_of_stock' ? 'out-of-stock' : '' }}"
                         style="background-image: url('{{ $product->image_url }}');">
                    </div>

                    {{-- Hover overlay --}}
                    <div class="prod-img-overlay">
                        <button class="prod-overlay-btn" tabindex="-1">
                            @if($product->stock_status === 'out_of_stock')
                                <span class="material-symbols-outlined" style="font-size:16px;">notifications</span>
                                Notify Me
                            @else
                                <span class="material-symbols-outlined" style="font-size:16px;">visibility</span>
                                View Product Detail
                            @endif
                        </button>
                    </div>
                </div>

                {{-- Card body --}}
                <div class="prod-card-body">
                    <div class="prod-cat-label">{{ $product->category?->name }}</div>
                    <div class="prod-title">{{ $product->name }}</div>
                    @if($product->description)
                    <div class="prod-description">{{ $product->description }}</div>
                    @endif
                    <div class="prod-card-footer">
                        <div class="prod-price {{ $product->stock_status === 'out_of_stock' ? 'strike' : '' }}">
                            Rs.&nbsp;{{ number_format($product->sale_price, 2) }}
                        </div>
                        @if($product->stock_status !== 'out_of_stock')
                        <button class="wish-btn" onclick="return false;" title="Wishlist">
                            <span class="material-symbols-outlined" style="font-size:22px;">favorite</span>
                        </button>
                        @else
                        <button class="wish-btn disabled" disabled onclick="return false;">
                            <span class="material-symbols-outlined" style="font-size:22px;">favorite</span>
                        </button>
                        @endif
                    </div>
                </div>

            </a>
            @endforeach
        </div>

        {{-- Load More --}}
        <div style="text-align:center;margin-top:36px;">
            <a href="{{ route('catalog.search', ['q' => '']) }}" class="btn-load-more">
                Load More Products
            </a>
        </div>
    </section>

    @else
    <div style="text-align:center;padding:64px 24px;color:#94a3b8;">
        <span class="material-symbols-outlined" style="font-size:52px;display:block;margin-bottom:14px;">inventory_2</span>
        <p>No products available yet.</p>
    </div>
    @endif

</div>
@endsection

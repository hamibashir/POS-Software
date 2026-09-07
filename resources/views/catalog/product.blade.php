@extends('layouts.catalog')

@section('title', $product->name . ' — Hassan Sanitary and Hardware Store')
@section('meta_description', Str::limit(strip_tags($product->description ?? $product->name . ' available at Hassan Sanitary and Hardware Store.'), 155))

@push('styles')
<style>
    /* ── Layout ──────────────────────────────────────────── */
    .pd-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 48px;
        align-items: start;
        margin-bottom: 56px;
    }
    @media (max-width: 900px) { .pd-grid { grid-template-columns: 1fr; gap: 28px; } }

    /* ── Breadcrumb ─────────────────────────────────────── */
    .breadcrumb-row {
        display: flex; align-items: center; gap: 6px;
        font-size: 13px; font-weight: 500; color: #64748b;
        margin-bottom: 28px;
    }
    .breadcrumb-row a { color: #64748b; text-decoration: none; transition: color .15s; }
    .breadcrumb-row a:hover { color: var(--primary); }
    .breadcrumb-row .current { color: #0f172a; font-weight: 600; }
    .breadcrumb-row .ms-icon { font-size: 14px; color: #cbd5e1; }

    /* ── Image Gallery ──────────────────────────────────── */
    .gallery-main {
        width: 100%; aspect-ratio: 1/1;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: #fff;
        overflow: hidden;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        margin-bottom: 14px;
    }
    .gallery-main img {
        width: 100%; height: 100%;
        object-fit: contain; padding: 20px;
        transition: opacity .2s;
    }
    .gallery-thumbs {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }
    .gallery-thumb {
        aspect-ratio: 1/1;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        background: #fff;
        overflow: hidden;
        cursor: pointer;
        opacity: .65;
        transition: border-color .15s, opacity .15s;
    }
    .gallery-thumb.active { border-color: var(--primary); opacity: 1; }
    .gallery-thumb:hover  { opacity: 1; border-color: #94a3b8; }
    .gallery-thumb .thumb-bg {
        width: 100%; height: 100%;
        background-size: cover;
        background-position: center;
    }

    /* ── Right column ───────────────────────────────────── */
    .pd-info { display: flex; flex-direction: column; gap: 20px; }

    /* Stock badge */
    .pd-stock-badge {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 700;
        padding: 5px 12px; border-radius: 20px;
        width: fit-content;
    }
    .pd-stock-badge .ms-icon { font-size: 15px; }
    .pd-stock-badge.in-stock  { background: #d1fae5; color: #065f46; }
    .pd-stock-badge.low-stock { background: #fef3c7; color: #92400e; }
    .pd-stock-badge.out-stock { background: #fee2e2; color: #991b1b; }

    /* Title */
    .pd-title {
        font-size: clamp(24px, 3.5vw, 36px);
        font-weight: 900; color: #0f172a;
        line-height: 1.12; letter-spacing: -.3px;
        margin: 0;
    }

    /* Price */
    .pd-price-row { display: flex; align-items: baseline; gap: 10px; }
    .pd-price-main { font-size: 30px; font-weight: 700; color: var(--primary); }
    .pd-price-old  { font-size: 15px; font-weight: 500; color: #94a3b8; text-decoration: line-through; }

    /* Description */
    .pd-desc {
        font-size: 15px; color: #475569;
        line-height: 1.75; margin: 0;
    }

    /* CTA buttons */
    .pd-cta-row { display: flex; gap: 12px; flex-wrap: wrap; }
    .btn-whatsapp {
        flex: 1; min-width: 160px;
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        background: #25D366; color: #fff;
        border: none; border-radius: 12px;
        padding: 15px 20px; font-size: 14px; font-weight: 700;
        text-decoration: none; cursor: pointer;
        box-shadow: 0 4px 14px rgba(37,211,102,.30);
        transition: filter .15s;
    }
    .btn-whatsapp:hover { filter: brightness(1.08); color: #fff; }
    .btn-call {
        flex: 1; min-width: 160px;
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        background: transparent; color: var(--primary);
        border: 2px solid var(--primary); border-radius: 12px;
        padding: 13px 20px; font-size: 14px; font-weight: 700;
        text-decoration: none; cursor: pointer;
        transition: background .15s;
    }
    .btn-call:hover { background: rgba(30,109,138,.06); color: var(--primary); }

    /* Spec table */
    .pd-specs {
        border: 1px solid #e2e8f0; border-radius: 14px;
        background: #fff; padding: 22px;
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
    }
    .pd-specs h3 {
        font-size: 16px; font-weight: 700; color: #0f172a;
        margin: 0 0 18px;
    }
    .specs-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0 32px;
    }
    @media (max-width: 640px) { .specs-grid { grid-template-columns: 1fr; } }
    .spec-row {
        display: flex; justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }
    .spec-row:last-child { border-bottom: none; }
    .spec-label { color: #64748b; font-weight: 500; }
    .spec-value { color: #0f172a; font-weight: 700; text-align: right; }

    /* Meta row (SKU / Unit) */
    .pd-meta {
        display: flex; gap: 16px; flex-wrap: wrap;
        font-size: 12px; color: #94a3b8;
    }
    .pd-meta span strong { color: #64748b; }

    /* ── Related Products ───────────────────────────────── */
    .related-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }
    @media (max-width: 900px)  { .related-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px)  { .related-grid { grid-template-columns: 1fr; } }

    .prod-card {
        background: #fff; border-radius: 14px;
        border: 1px solid #e2e8f0; overflow: hidden;
        text-decoration: none; color: inherit;
        display: flex; flex-direction: column;
        transition: box-shadow .25s, border-color .25s;
    }
    .prod-card:hover { box-shadow: 0 12px 32px rgba(0,0,0,.09); border-color: rgba(30,109,138,.28); }
    .prod-img-wrap {
        width: 100%; aspect-ratio: 4/3;
        overflow: hidden; position: relative; background: #f1f5f9;
    }
    .prod-img-bg {
        width: 100%; height: 100%;
        background-size: cover; background-position: center;
        transition: transform .5s;
    }
    .prod-card:hover .prod-img-bg { transform: scale(1.08); }
    .prod-card-body { padding: 14px; flex: 1; display: flex; flex-direction: column; }
    .prod-cat-label { font-size: 11px; color: #64748b; margin-bottom: 4px; }
    .prod-title-sm  { font-size: 14px; font-weight: 700; color: #0f172a; line-height: 1.3; margin-bottom: 10px; transition: color .15s; }
    .prod-card:hover .prod-title-sm { color: var(--primary); }
    .prod-price-sm  { font-size: 16px; font-weight: 700; color: #0f172a; margin-top: auto; }

    .stock-badge {
        position: absolute; top: 10px; left: 10px; z-index: 5;
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 10px; font-weight: 700;
        padding: 3px 8px; border-radius: 5px;
    }
    .stock-badge .dot { width: 5px; height: 5px; border-radius: 50%; }
    .badge-instock  { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-instock  .dot { background: #22c55e; }
    .badge-lowstock { background: #fef9c3; color: #a16207; border: 1px solid #fde68a; }
    .badge-lowstock .dot { background: #eab308; }
    .badge-outstock { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-outstock .dot { background: #ef4444; }

    .sec-title { font-size: 20px; font-weight: 700; color: #0f172a; }
</style>
@endpush

@section('content')
<div class="cat-page">

    {{-- ── Breadcrumb ───────────────────────────────────────── --}}
    <nav class="breadcrumb-row">
        <a href="{{ route('catalog.home') }}">Home</a>
        @if($product->category)
        <span class="material-symbols-outlined ms-icon">chevron_right</span>
        <a href="{{ route('catalog.category', $product->category->slug) }}">{{ $product->category->name }}</a>
        @endif
        <span class="material-symbols-outlined ms-icon">chevron_right</span>
        <span class="current">{{ Str::limit($product->name, 40) }}</span>
    </nav>

    {{-- ── Main 2-col grid ─────────────────────────────────── --}}
    <div class="pd-grid">

        {{-- Left: Image gallery --}}
        <div>
            <div class="gallery-main">
                <img id="main-img" src="{{ $product->image_url }}" alt="{{ $product->name }}">
            </div>

            {{-- Thumbnails - product image + 3 placeholder slots --}}
            <div class="gallery-thumbs">
                <div class="gallery-thumb active" data-src="{{ $product->image_url }}">
                    <div class="thumb-bg" style="background-image:url('{{ $product->image_url }}');"></div>
                </div>
                {{-- Extra thumb slots (appear only if product gets multiple images in future) --}}
                <div class="gallery-thumb" style="opacity:.25;pointer-events:none;">
                    <div class="thumb-bg" style="background:#f1f5f9;"></div>
                </div>
                <div class="gallery-thumb" style="opacity:.25;pointer-events:none;">
                    <div class="thumb-bg" style="background:#f1f5f9;"></div>
                </div>
                <div class="gallery-thumb" style="opacity:.25;pointer-events:none;">
                    <div class="thumb-bg" style="background:#f1f5f9;"></div>
                </div>
            </div>
        </div>

        {{-- Right: Product info --}}
        <div class="pd-info">

            {{-- Stock status --}}
            @if($product->stock_status === 'in_stock')
                <span class="pd-stock-badge in-stock">
                    <span class="material-symbols-outlined ms-icon">check_circle</span>
                    In Stock
                </span>
            @elseif($product->stock_status === 'low_stock')
                <span class="pd-stock-badge low-stock">
                    <span class="material-symbols-outlined ms-icon">warning</span>
                    Limited Stock
                </span>
            @else
                <span class="pd-stock-badge out-stock">
                    <span class="material-symbols-outlined ms-icon">cancel</span>
                    Out of Stock
                </span>
            @endif

            {{-- Title --}}
            <div>
                <h1 class="pd-title">{{ $product->name }}</h1>
                <div class="pd-price-row" style="margin-top:12px;">
                    <span class="pd-price-main">Rs. {{ number_format($product->sale_price, 0) }}</span>
                    @if($product->cost_price > 0 && $product->cost_price < $product->sale_price)
                    <span class="pd-price-old">Rs. {{ number_format($product->cost_price * 1.35, 0) }}</span>
                    @endif
                </div>
            </div>

            {{-- Description --}}
            @if($product->description)
            <p class="pd-desc">{!! nl2br(e($product->description)) !!}</p>
            @endif

            {{-- CTA Buttons --}}
            <div class="pd-cta-row">
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', config('store.whatsapp', '923001234567')) }}?text={{ urlencode('Hi! I am interested in: ' . $product->name . ' (SKU: ' . $product->sku . '). Please share availability and price.') }}"
                   class="btn-whatsapp" target="_blank" rel="noopener" id="btn-whatsapp">
                    <span class="material-symbols-outlined" style="font-size:18px;">chat</span>
                    Chat on WhatsApp
                </a>
                <a href="tel:{{ config('store.phone', '+923001234567') }}"
                   class="btn-call" id="btn-call">
                    <span class="material-symbols-outlined" style="font-size:18px;">call</span>
                    Call to Order
                </a>
            </div>

            {{-- Technical Specifications --}}
            <div class="pd-specs">
                <h3>Technical Specifications</h3>
                <div class="specs-grid">
                    <div class="spec-row">
                        <span class="spec-label">SKU</span>
                        <span class="spec-value">{{ $product->sku }}</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Unit</span>
                        <span class="spec-value">{{ strtoupper($product->unit ?? 'pc') }}</span>
                    </div>
                    @if($product->category)
                    <div class="spec-row">
                        <span class="spec-label">Category</span>
                        <span class="spec-value">{{ $product->category->name }}</span>
                    </div>
                    @endif
                    <div class="spec-row">
                        <span class="spec-label">Stock</span>
                        <span class="spec-value">
                            @if($product->stock_status === 'in_stock')
                                {{ $product->stock_quantity }} available
                            @elseif($product->stock_status === 'low_stock')
                                Only {{ $product->stock_quantity }} left
                            @else
                                Currently unavailable
                            @endif
                        </span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Barcode</span>
                        <span class="spec-value">{{ $product->barcode ?? '—' }}</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Price / Unit</span>
                        <span class="spec-value">Rs. {{ number_format($product->sale_price, 2) }}</span>
                    </div>
                </div>
            </div>

        </div>{{-- /pd-info --}}
    </div>{{-- /pd-grid --}}

    {{-- ── Related Products ──────────────────────────────── --}}
    @if($related->count())
    <div style="border-top: 1px solid #e2e8f0; padding-top: 44px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;">
            <h2 class="sec-title">More in {{ $product->category?->name }}</h2>
            @if($product->category)
            <a href="{{ route('catalog.category', $product->category->slug) }}"
               style="font-size:13px;font-weight:600;color:var(--primary);text-decoration:none;display:flex;align-items:center;gap:3px;">
                View All
                <span class="material-symbols-outlined" style="font-size:16px;">chevron_right</span>
            </a>
            @endif
        </div>
        <div class="related-grid">
            @foreach($related as $rel)
            <a href="{{ route('catalog.product', $rel->slug) }}" class="prod-card">
                <div class="prod-img-wrap">
                    @if($rel->stock_status === 'in_stock')
                        <span class="stock-badge badge-instock"><span class="dot"></span> In Stock</span>
                    @elseif($rel->stock_status === 'low_stock')
                        <span class="stock-badge badge-lowstock"><span class="dot"></span> Low Stock</span>
                    @else
                        <span class="stock-badge badge-outstock"><span class="dot"></span> Out of Stock</span>
                    @endif
                    <div class="prod-img-bg" style="background-image:url('{{ $rel->image_url }}');"></div>
                </div>
                <div class="prod-card-body">
                    <div class="prod-cat-label">{{ $rel->category?->name }}</div>
                    <div class="prod-title-sm">{{ $rel->name }}</div>
                    <div class="prod-price-sm">Rs. {{ number_format($rel->sale_price, 0) }}</div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
// Thumbnail switcher
document.querySelectorAll('.gallery-thumb[data-src]').forEach(function(thumb) {
    thumb.addEventListener('click', function() {
        document.getElementById('main-img').src = this.dataset.src;
        document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>
@endpush

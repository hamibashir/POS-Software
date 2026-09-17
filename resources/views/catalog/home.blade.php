@extends('layouts.catalog')

@section('title', 'Hassan & Sons — Next-Gen Hardware, Tools, Sanitary & Plumbing')
@section('meta_description', 'Discover professional-grade hardware tools, sanitary ware, plumbing, and electrical supplies at Hassan & Sons. Interactive catalog and instant phone/WhatsApp ordering.')

@push('styles')
<style>
    /* ═══════════════════════════════════════════════════════════
       ── APPLE-STYLE INTERACTIVE SCROLL STORY ──────────────────
       ═══════════════════════════════════════════════════════════ */
    .apple-scroll-wrapper {
        position: relative;
        width: 100%;
        background: #090d16;
        color: #fff;
        margin-top: -1px;
    }

    /* Scroll track height drives the animation duration */
    .apple-scroll-track {
        position: relative;
        height: 380vh; /* Calibrated for natural scroll pacing */
    }

    @media (max-width: 768px) {
        .apple-scroll-track {
            height: 280vh; /* Shorter track on mobile for quick scrubbing */
        }
    }

    /* Sticky stage pinned to viewport */
    .apple-sticky-stage {
        position: sticky;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle at 50% 45%, #16243b 0%, #090d16 75%);
    }

    /* Interactive Canvas */
    .hero-canvas {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        z-index: 1;
        pointer-events: none;
    }

    /* Preloader */
    .canvas-loader {
        position: absolute;
        inset: 0;
        background: #090d16;
        z-index: 20;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 14px;
        transition: opacity .5s ease, visibility .5s ease;
    }
    .canvas-loader.loaded {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }
    .loader-spinner {
        width: 44px;
        height: 44px;
        border: 3px solid rgba(255,255,255,.1);
        border-top-color: #38bdf8;
        border-radius: 50%;
        animation: spin-loader 0.8s linear infinite;
    }
    @keyframes spin-loader {
        to { transform: rotate(360deg); }
    }
    .loader-text {
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .5px;
        color: #94a3b8;
    }
    .loader-bar-bg {
        width: 180px;
        height: 4px;
        background: rgba(255,255,255,.1);
        border-radius: 4px;
        overflow: hidden;
    }
    .loader-bar {
        width: 0%;
        height: 100%;
        background: linear-gradient(90deg, #38bdf8, #818cf8);
        transition: width .15s ease;
    }

    /* Ambient Vignette & Gradients */
    .ambient-glow {
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 50% 50%, rgba(56, 189, 248, 0.08) 0%, transparent 60%);
        pointer-events: none;
        z-index: 2;
    }
    .stage-vignette {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(9,13,22,.6) 0%, transparent 20%, transparent 80%, #090d16 100%);
        pointer-events: none;
        z-index: 3;
    }

    /* ── Floating Story Captions Overlay ─────────────────── */
    .story-overlay {
        position: absolute;
        inset: 0;
        z-index: 10;
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    .story-step {
        position: absolute;
        max-width: 620px;
        width: calc(100% - 48px);
        text-align: center;
        opacity: 0;
        transform: translateY(28px) scale(0.96);
        transition: opacity .45s cubic-bezier(.16,1,.3,1), transform .45s cubic-bezier(.16,1,.3,1);
        pointer-events: none;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .story-step.active {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: auto;
    }

    /* Glass card background on step */
    .story-card-glass {
        background: rgba(15, 23, 42, 0.65);
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow: 0 20px 50px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.1);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-radius: 24px;
        padding: 32px 36px;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    @media (max-width: 640px) {
        .story-card-glass {
            padding: 20px 18px;
            border-radius: 18px;
        }
    }

    .story-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(56, 189, 248, 0.15);
        border: 1px solid rgba(56, 189, 248, 0.35);
        color: #7dd3fc;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .8px;
        padding: 5px 14px;
        border-radius: 20px;
        margin-bottom: 12px;
    }
    .story-headline {
        font-size: clamp(24px, 4.2vw, 46px);
        font-weight: 900;
        line-height: 1.15;
        letter-spacing: -.6px;
        margin-bottom: 12px;
        color: #ffffff;
    }
    .story-gradient {
        background: linear-gradient(135deg, #38bdf8 0%, #818cf8 50%, #c084fc 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .story-desc {
        font-size: clamp(13px, 1.6vw, 16px);
        color: #cbd5e1;
        line-height: 1.6;
        margin-bottom: 20px;
        max-width: 480px;
    }
    .story-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: center;
    }
    .btn-story-primary {
        background: linear-gradient(135deg, #0284c7, #2563eb);
        color: #fff !important;
        font-weight: 700;
        font-size: 13px;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 15px rgba(2, 132, 199, 0.4);
        transition: transform .15s, box-shadow .15s;
    }
    .btn-story-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(2, 132, 199, 0.6);
    }
    .btn-story-outline {
        background: rgba(255, 255, 255, 0.1);
        color: #fff !important;
        font-weight: 600;
        font-size: 13px;
        padding: 10px 18px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.25);
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        backdrop-filter: blur(8px);
        transition: background .15s;
    }
    .btn-story-outline:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    .btn-story-whatsapp {
        background: #16a34a;
        color: #fff !important;
        font-weight: 700;
        font-size: 13px;
        padding: 10px 18px;
        border-radius: 10px;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 15px rgba(22, 163, 74, 0.4);
        transition: transform .15s;
    }
    .btn-story-whatsapp:hover {
        transform: translateY(-2px);
        background: #15803d;
    }

    .story-features-row {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        justify-content: center;
        margin-top: 4px;
    }
    .story-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #e2e8f0;
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.12);
        padding: 6px 12px;
        border-radius: 8px;
    }

    /* ── Bottom Floating Controls ────────────────────────── */
    .canvas-controls {
        position: absolute;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 15;
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(15, 23, 42, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.15);
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        padding: 8px 16px;
        border-radius: 30px;
        max-width: calc(100% - 32px);
    }
    @media (max-width: 640px) {
        .canvas-controls {
            bottom: 16px;
            padding: 6px 12px;
            gap: 8px;
        }
    }
    .control-btn {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #fff;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
        transition: background .15s;
    }
    .control-btn:hover {
        background: rgba(255, 255, 255, 0.25);
    }
    .scrub-track-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .frame-scrubber {
        -webkit-appearance: none;
        appearance: none;
        width: 110px;
        height: 5px;
        border-radius: 5px;
        background: rgba(255,255,255,0.25);
        outline: none;
        cursor: pointer;
    }
    @media (max-width: 480px) {
        .frame-scrubber { width: 70px; }
    }
    .frame-scrubber::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #38bdf8;
        cursor: pointer;
        box-shadow: 0 0 8px #38bdf8;
    }
    .scroll-hint-label {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }
    .scroll-hint-label i {
        animation: bounce-down 1.5s infinite;
    }
    @keyframes bounce-down {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(4px); }
    }

    /* ═══════════════════════════════════════════════════════════
       ── MAIN CATALOG SECTIONS (LIGHT & DYNAMIC) ───────────────
       ═══════════════════════════════════════════════════════════ */
    .cat-main-content {
        max-width: 1440px;
        margin: 0 auto;
        padding: 48px 20px 80px;
    }

    /* ── Value Pillars ──────────────────────────────────── */
    .value-pillars-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 50px;
    }
    @media (max-width: 1024px) {
        .value-pillars-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 540px) {
        .value-pillars-grid { grid-template-columns: 1fr; }
    }
    .value-pillar-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        transition: transform .2s, box-shadow .2s, border-color .2s;
    }
    .value-pillar-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.06);
        border-color: #cbd5e1;
    }
    .pillar-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .pillar-title {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 2px;
    }
    .pillar-desc {
        font-size: 12px;
        color: #64748b;
        line-height: 1.4;
    }

    /* ── Category icon grid ─────────────────────────────── */
    .cat-icon-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 16px;
    }
    @media (max-width: 1024px) { .cat-icon-grid { grid-template-columns: repeat(4, 1fr); } }
    @media (max-width: 640px)  { .cat-icon-grid { grid-template-columns: repeat(2, 1fr); } }

    .cat-icon-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 20px 14px;
        border-radius: 16px;
        background: #fff;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        color: #1e293b;
        transition: all .25s ease;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }
    .cat-icon-card:hover {
        border-color: rgba(30,109,138,.5);
        box-shadow: 0 8px 24px rgba(30,109,138,.12);
        transform: translateY(-4px);
    }
    .cat-icon-card .icon-wrap {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: #eff6ff;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        transition: background .2s, transform .2s;
    }
    .cat-icon-card:hover .icon-wrap {
        background: #e0f2fe;
        transform: scale(1.08);
    }
    .cat-icon-card .ms-icon { font-size: 30px; color: #0284c7; }
    .cat-icon-card .cat-name { font-size: 14px; font-weight: 700; color: #0f172a; transition: color .15s; }
    .cat-icon-card:hover .cat-name { color: #0284c7; }
    .cat-icon-card .cat-count { font-size: 11px; color: #94a3b8; margin-top: 4px; font-weight: 600; }

    /* ── Product grid ──────────────────────────────────── */
    .prod-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 22px;
    }
    @media (max-width: 1180px) { .prod-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 780px)  { .prod-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px)  { .prod-grid { grid-template-columns: 1fr; } }

    .prod-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        transition: box-shadow .3s, border-color .3s, transform .25s;
    }
    .prod-card:hover {
        box-shadow: 0 18px 40px rgba(0,0,0,.08);
        border-color: rgba(2, 132, 199, .35);
        transform: translateY(-3px);
    }

    .prod-img-wrap {
        width: 100%;
        aspect-ratio: 4/3;
        overflow: hidden;
        position: relative;
        background: #f1f5f9;
    }
    .prod-img-bg {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        transition: transform .5s cubic-bezier(.25,.46,.45,.94);
    }
    .prod-card:hover .prod-img-bg { transform: scale(1.08); }
    .prod-img-bg.out-of-stock { opacity: .6; filter: grayscale(.5); }

    .stock-badge {
        position: absolute; top: 12px; left: 12px; z-index: 5;
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 11px; font-weight: 700;
        padding: 4px 9px; border-radius: 6px;
    }
    .stock-badge .dot {
        width: 6px; height: 6px; border-radius: 50%; display: block; flex-shrink: 0;
    }
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

    .prod-card-body {
        padding: 18px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .prod-cat-label {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    .prod-title {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.35;
        margin-bottom: 6px;
        transition: color .15s;
    }
    .prod-card:hover .prod-title { color: #0284c7; }
    .prod-description {
        font-size: 13px;
        color: #475569;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 14px;
    }
    .prod-card-footer {
        margin-top: auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 10px;
        border-top: 1px solid #f1f5f9;
    }
    .prod-price {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
    }
    .prod-price.strike {
        font-size: 16px;
        font-weight: 600;
        color: #94a3b8;
        text-decoration: line-through;
    }
    .btn-quick-order {
        background: #e0f2fe;
        color: #0369a1;
        border: none;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        cursor: pointer;
        transition: all .15s;
    }
    .btn-quick-order:hover {
        background: #0284c7;
        color: #fff;
    }

    .btn-load-more {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 13px 36px;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        text-decoration: none;
        box-shadow: 0 2px 6px rgba(0,0,0,.04);
        transition: all .15s;
    }
    .btn-load-more:hover {
        background: #0f172a;
        color: #fff;
        border-color: #0f172a;
        box-shadow: 0 6px 16px rgba(0,0,0,.15);
    }

    .sec-title { font-size: 24px; font-weight: 800; color: #0f172a; }
    .sec-sub   { font-size: 14px; color: #64748b; margin-top: 4px; }
</style>
@endpush

@section('content')

{{-- ═══════════════════════════════════════════════════════════
     ── 1. APPLE-STYLE INTERACTIVE STICKY SCROLL HERO ────────
     ═══════════════════════════════════════════════════════════ --}}
<div class="apple-scroll-wrapper" id="interactiveExperience">
    <div class="apple-scroll-track" id="scrollTrack">
        <div class="apple-sticky-stage" id="stickyStage">

            {{-- HTML5 Canvas rendering 141 high-res frames --}}
            <canvas id="heroCanvas" class="hero-canvas"></canvas>

            {{-- Ambient glow & vignette effects --}}
            <div class="ambient-glow"></div>
            <div class="stage-vignette"></div>

            {{-- Preloader Screen --}}
            <div id="canvasLoader" class="canvas-loader">
                <div class="loader-spinner"></div>
                <div class="loader-text">Loading 3D Experience <span id="loaderPercent">0%</span></div>
                <div class="loader-bar-bg">
                    <div id="loaderBar" class="loader-bar"></div>
                </div>
            </div>

            {{-- Dynamic Floating Story Overlays (Synced with Scroll Progression) --}}
            <div class="story-overlay">
                
                {{-- Step 1: 0% - 25% Scroll --}}
                <div class="story-step active" id="storyStep1">
                    <div class="story-card-glass">
                        <span class="story-tag"><i class="bi bi-shield-check"></i> Hassan & Sons Hardware</span>
                        <h1 class="story-headline">Next-Gen <span class="story-gradient">Hardware &amp; Sanitary</span></h1>
                        <p class="story-desc">Engineered for precision durability, industrial quality, and everyday reliability.</p>
                        <div class="story-actions">
                            <a href="#products" class="btn-story-primary"><i class="bi bi-cart3"></i> Explore Collection</a>
                            <a href="tel:051-8891930" onclick="openPhoneOrderModal(); return false;" class="btn-story-outline"><i class="bi bi-telephone-fill"></i> 051-8891930</a>
                        </div>
                    </div>
                </div>

                {{-- Step 2: 25% - 55% Scroll --}}
                <div class="story-step" id="storyStep2">
                    <div class="story-card-glass">
                        <span class="story-tag"><i class="bi bi-gear-wide-connected"></i> Precision Engineering</span>
                        <h2 class="story-headline">Sanitary Ware &amp; <span class="story-gradient">Power Tools</span></h2>
                        <p class="story-desc">From premium residential fixtures to heavy contractor machinery — complete store inventory.</p>
                        <div class="story-features-row">
                            <div class="story-pill"><i class="bi bi-check-circle-fill text-success"></i> 100% Genuine Brands</div>
                            <div class="story-pill"><i class="bi bi-shield-fill-check text-primary"></i> Manufacturer Tested</div>
                        </div>
                    </div>
                </div>

                {{-- Step 3: 55% - 80% Scroll --}}
                <div class="story-step" id="storyStep3">
                    <div class="story-card-glass">
                        <span class="story-tag"><i class="bi bi-boxes"></i> Wholesale &amp; Retail</span>
                        <h2 class="story-headline">Direct Counter Stock at <span class="story-gradient">Best Market Rates</span></h2>
                        <p class="story-desc">Official distributor pricing for plumbers, electricians, contractors, and home builders.</p>
                        <div class="story-features-row">
                            <div class="story-pill"><i class="bi bi-truck text-info"></i> Fast Local Dispatch</div>
                            <div class="story-pill"><i class="bi bi-receipt-cutoff text-warning"></i> Itemized Official Invoices</div>
                        </div>
                    </div>
                </div>

                {{-- Step 4: 80% - 100% Scroll --}}
                <div class="story-step" id="storyStep4">
                    <div class="story-card-glass">
                        <span class="story-tag"><i class="bi bi-lightning-charge-fill"></i> Instant Ordering</span>
                        <h2 class="story-headline">Ready to Build <span class="story-gradient">Your Next Project?</span></h2>
                        <p class="story-desc">Browse our full live inventory below or place your order directly via phone or WhatsApp.</p>
                        <div class="story-actions">
                            <a href="#products" class="btn-story-primary"><i class="bi bi-grid-fill"></i> View Live Stock</a>
                            <a href="https://wa.me/923000000000" target="_blank" class="btn-story-whatsapp"><i class="bi bi-whatsapp"></i> WhatsApp Order</a>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Bottom Control Bar --}}
            <div class="canvas-controls">
                <button type="button" id="btnAutoPlay" class="control-btn" title="Toggle Auto Animation">
                    <i class="bi bi-play-fill" id="playIcon"></i>
                    <span id="playLabel">Auto Play</span>
                </button>
                <div class="scrub-track-wrap">
                    <input type="range" id="frameScrubber" min="1" max="141" value="1" class="frame-scrubber" aria-label="Timeline Scrubber">
                </div>
                <div class="scroll-hint-label d-none d-sm-flex">
                    <i class="bi bi-arrow-down-circle"></i>
                    <span>Scroll to explore</span>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     ── 2. CATALOG BODY CONTENT ──────────────────────────────
     ═══════════════════════════════════════════════════════════ --}}
<div class="cat-main-content">

    {{-- Value Pillars --}}
    <section class="value-pillars-grid">
        <div class="value-pillar-card">
            <div class="pillar-icon" style="background:#e0f2fe; color:#0284c7;">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <div class="pillar-title">100% Original Products</div>
                <div class="pillar-desc">Direct factory supply & guaranteed quality materials.</div>
            </div>
        </div>
        <div class="value-pillar-card">
            <div class="pillar-icon" style="background:#ede9fe; color:#7c3aed;">
                <i class="bi bi-tag-fill"></i>
            </div>
            <div>
                <div class="pillar-title">Wholesale &amp; Retail Rates</div>
                <div class="pillar-desc">Best competitive pricing in Rawalpindi & Islamabad.</div>
            </div>
        </div>
        <div class="value-pillar-card">
            <div class="pillar-icon" style="background:#dcfce7; color:#16a34a;">
                <i class="bi bi-truck"></i>
            </div>
            <div>
                <div class="pillar-title">Fast Order Dispatch</div>
                <div class="pillar-desc">Quick doorstep delivery for contractors and builders.</div>
            </div>
        </div>
        <div class="value-pillar-card">
            <div class="pillar-icon" style="background:#fef3c7; color:#d97706;">
                <i class="bi bi-telephone-inbound-fill"></i>
            </div>
            <div>
                <div class="pillar-title">Direct Phone &amp; WhatsApp</div>
                <div class="pillar-desc">Order on 051-8891930 with instant confirmation.</div>
            </div>
        </div>
    </section>

    {{-- Featured Categories --}}
    @if($categories->count())
    <section style="margin-bottom:56px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
            <div>
                <h2 class="sec-title">Explore by Category</h2>
                <p class="sec-sub">Browse top hardware, plumbing, sanitary, and electrical supplies.</p>
            </div>
            <a href="{{ route('catalog.search', ['q' => '']) }}"
               style="font-size:13px;font-weight:700;color:#0284c7;text-decoration:none;display:flex;align-items:center;gap:3px;">
                View All Categories
                <span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span>
            </a>
        </div>

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
                <span class="cat-count">{{ $cat->products_count }}+ Products</span>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Popular Products --}}
    @if($featured->count())
    <section id="products">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:26px;">
            <div>
                <h2 class="sec-title">Featured Products &amp; Materials</h2>
                <p class="sec-sub">Live counter inventory available for immediate pickup or delivery.</p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <a href="{{ route('catalog.search', ['q' => '']) }}" class="btn btn-sm btn-outline-dark fw-bold rounded-pill px-3">
                    <i class="bi bi-search me-1"></i> Search Full Catalog
                </a>
            </div>
        </div>

        <div class="prod-grid">
            @foreach($featured as $product)
            <div class="prod-card">

                {{-- Image area --}}
                <div class="prod-img-wrap">
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

                    <div class="prod-img-bg {{ $product->stock_status === 'out_of_stock' ? 'out-of-stock' : '' }}"
                         style="background-image: url('{{ $product->image_url }}');">
                    </div>
                </div>

                {{-- Card body --}}
                <div class="prod-card-body">
                    <div class="prod-cat-label">{{ $product->category?->name ?? 'Hardware' }}</div>
                    <a href="{{ route('catalog.product', $product->slug) }}" style="text-decoration:none;">
                        <div class="prod-title">{{ $product->name }}</div>
                    </a>
                    @if($product->description)
                    <div class="prod-description">{{ $product->description }}</div>
                    @endif
                    
                    <div class="prod-card-footer">
                        <div>
                            <div class="prod-price {{ $product->stock_status === 'out_of_stock' ? 'strike' : '' }}">
                                {{ pkr($product->sale_price, 2) }}
                            </div>
                        </div>

                        <button type="button" class="btn-quick-order" onclick="openPhoneOrderModal('{{ addslashes($product->name) }}', '{{ addslashes($product->sku ?? '') }}', '{{ pkr($product->sale_price, 2) }}')">
                            <i class="bi bi-telephone-fill"></i> Order
                        </button>
                    </div>
                </div>

            </div>
            @endforeach
        </div>

        {{-- Load More / Search CTA --}}
        <div style="text-align:center;margin-top:44px;">
            <a href="{{ route('catalog.search', ['q' => '']) }}" class="btn-load-more">
                <span>View Complete Store Catalog</span>
                <i class="bi bi-arrow-right"></i>
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

@push('scripts')
<script>
(function() {
    const TOTAL_FRAMES = 141;
    const canvas = document.getElementById('heroCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const scrollTrack = document.getElementById('scrollTrack');
    const scrubber = document.getElementById('frameScrubber');
    const loader = document.getElementById('canvasLoader');
    const loaderBar = document.getElementById('loaderBar');
    const loaderPercent = document.getElementById('loaderPercent');
    const btnAutoPlay = document.getElementById('btnAutoPlay');
    const playIcon = document.getElementById('playIcon');
    const playLabel = document.getElementById('playLabel');

    const storySteps = [
        { el: document.getElementById('storyStep1'), min: 0.00, max: 0.25 },
        { el: document.getElementById('storyStep2'), min: 0.25, max: 0.55 },
        { el: document.getElementById('storyStep3'), min: 0.55, max: 0.80 },
        { el: document.getElementById('storyStep4'), min: 0.80, max: 1.01 },
    ];

    const frames = [];
    let loadedCount = 0;
    let currentFrameIndex = 1;
    let isAutoPlaying = false;
    let autoPlayInterval = null;
    let isUserScrubbing = false;

    // Helper to get image path
    function getFrameUrl(index) {
        const padded = String(index).padStart(3, '0');
        return `{{ asset('images/ezgif-frame-') }}${padded}.jpg`;
    }

    // Set canvas dimensions with high-DPI scaling
    function resizeCanvas() {
        const dpr = window.devicePixelRatio || 1;
        const rect = canvas.parentElement.getBoundingClientRect();
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;
        canvas.style.width = `${rect.width}px`;
        canvas.style.height = `${rect.height}px`;
        ctx.scale(dpr, dpr);
        drawFrame(currentFrameIndex);
    }

    // Draw frame centered while preserving aspect ratio (contain mode)
    function drawFrame(index) {
        const img = frames[index];
        if (!img || !img.complete || img.naturalWidth === 0) return;

        const dpr = window.devicePixelRatio || 1;
        const displayWidth = canvas.width / dpr;
        const displayHeight = canvas.height / dpr;

        ctx.clearRect(0, 0, displayWidth, displayHeight);

        const imgRatio = img.naturalWidth / img.naturalHeight;
        const canvasRatio = displayWidth / displayHeight;

        let renderWidth, renderHeight;
        if (canvasRatio > imgRatio) {
            renderHeight = displayHeight * 0.95;
            renderWidth = renderHeight * imgRatio;
        } else {
            renderWidth = displayWidth * 0.95;
            renderHeight = renderWidth / imgRatio;
        }

        const renderX = (displayWidth - renderWidth) / 2;
        const renderY = (displayHeight - renderHeight) / 2;

        ctx.drawImage(img, renderX, renderY, renderWidth, renderHeight);
    }

    // Update story caption visibility based on scroll fraction (0 to 1)
    function updateStorySteps(fraction) {
        storySteps.forEach(step => {
            if (!step.el) return;
            const isActive = fraction >= step.min && fraction < step.max;
            step.el.classList.toggle('active', isActive);
        });
    }

    // Handle scroll calculation
    function handleScroll() {
        if (isAutoPlaying || isUserScrubbing) return;

        const rect = scrollTrack.getBoundingClientRect();
        const viewportHeight = window.innerHeight;
        const totalScrollable = rect.height - viewportHeight;

        if (totalScrollable <= 0) return;

        const scrolled = -rect.top;
        const fraction = Math.max(0, Math.min(1, scrolled / totalScrollable));

        const targetFrame = Math.max(1, Math.min(TOTAL_FRAMES, Math.floor(fraction * (TOTAL_FRAMES - 1)) + 1));

        if (targetFrame !== currentFrameIndex) {
            currentFrameIndex = targetFrame;
            requestAnimationFrame(() => {
                drawFrame(currentFrameIndex);
                if (scrubber) scrubber.value = currentFrameIndex;
            });
        }

        updateStorySteps(fraction);
    }

    // Preload image frames progressively
    function preloadFrames() {
        // Priority 1: Load First Frame Immediately
        const firstImg = new Image();
        firstImg.src = getFrameUrl(1);
        firstImg.onload = () => {
            frames[1] = firstImg;
            resizeCanvas();
            drawFrame(1);
        };

        // Priority 2: Preload remaining frames
        for (let i = 1; i <= TOTAL_FRAMES; i++) {
            const img = new Image();
            img.src = getFrameUrl(i);
            img.onload = () => {
                frames[i] = img;
                loadedCount++;
                const pct = Math.round((loadedCount / TOTAL_FRAMES) * 100);
                if (loaderBar) loaderBar.style.width = `${pct}%`;
                if (loaderPercent) loaderPercent.innerText = `${pct}%`;

                if (loadedCount >= TOTAL_FRAMES - 5) {
                    if (loader) loader.classList.add('loaded');
                }
            };
            img.onerror = () => {
                loadedCount++;
            };
        }
    }

    // Scrubber drag / touch interaction
    if (scrubber) {
        scrubber.addEventListener('input', function() {
            isUserScrubbing = true;
            stopAutoPlay();
            currentFrameIndex = parseInt(this.value, 10);
            const fraction = (currentFrameIndex - 1) / (TOTAL_FRAMES - 1);
            drawFrame(currentFrameIndex);
            updateStorySteps(fraction);
        });

        scrubber.addEventListener('change', function() {
            isUserScrubbing = false;
        });
    }

    // Auto Play Controller
    function startAutoPlay() {
        isAutoPlaying = true;
        if (playIcon) playIcon.className = 'bi bi-pause-fill';
        if (playLabel) playLabel.innerText = 'Pause';

        autoPlayInterval = setInterval(() => {
            currentFrameIndex++;
            if (currentFrameIndex > TOTAL_FRAMES) {
                currentFrameIndex = 1;
            }
            drawFrame(currentFrameIndex);
            if (scrubber) scrubber.value = currentFrameIndex;
            const fraction = (currentFrameIndex - 1) / (TOTAL_FRAMES - 1);
            updateStorySteps(fraction);
        }, 1000 / 30); // 30 FPS smooth playback
    }

    function stopAutoPlay() {
        isAutoPlaying = false;
        if (autoPlayInterval) clearInterval(autoPlayInterval);
        if (playIcon) playIcon.className = 'bi bi-play-fill';
        if (playLabel) playLabel.innerText = 'Auto Play';
    }

    if (btnAutoPlay) {
        btnAutoPlay.addEventListener('click', () => {
            if (isAutoPlaying) {
                stopAutoPlay();
            } else {
                startAutoPlay();
            }
        });
    }

    // Event Listeners
    window.addEventListener('scroll', handleScroll, { passive: true });
    window.addEventListener('resize', resizeCanvas);

    // Initialize
    preloadFrames();
    resizeCanvas();
    handleScroll();
})();
</script>
@endpush

@endsection

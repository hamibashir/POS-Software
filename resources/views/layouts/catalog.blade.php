<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Browse our full range of sanitary and hardware products.')">
    <title>@yield('title', 'Product Catalog') — Hassan & Sons</title>

    <!-- Favicon & App Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=6">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=6">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=6">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=6">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=6">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --primary:     #1e6d8a;
            --primary-dk:  #155268;
            --primary-lt:  #e0f0f5;
            --text:        #121617;
            --muted:       #64748b;
            --border:      #e2e8f0;
            --bg:          #f6f7f8;
            --card:        #ffffff;
        }
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--bg); color: var(--text); min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden; }

        /* ── Scrollbar ──────────────────── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

        /* ── Navbar ─────────────────────── */
        .cat-nav {
            background: #fff;
            border-bottom: 1px solid var(--border);
            position: sticky; top: 0; z-index: 1000;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
            width: 100%;
        }
        .cat-nav .inner {
            max-width: 1440px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 0 16px; height: 64px;
            box-sizing: border-box; width: 100%;
        }
        .cat-nav .brand {
            display: flex; align-items: center; gap: 8px;
            font-weight: 800; font-size: 18px; color: var(--text);
            text-decoration: none; white-space: nowrap; flex-shrink: 0;
        }
        .cat-nav .brand .ms-icon { font-size: 28px; color: var(--primary); }

        /* Category nav links */
        .cat-nav-links {
            display: flex; gap: 18px; margin: 0 12px;
            overflow-x: auto; scrollbar-width: none;
            flex-shrink: 1; min-width: 0;
        }
        .cat-nav-links::-webkit-scrollbar { display: none; }
        .cat-nav-links a {
            font-size: 13px; font-weight: 600; color: #475569;
            text-decoration: none; transition: color .15s; white-space: nowrap;
        }
        .cat-nav-links a:hover { color: var(--primary); }

        /* Search + auth buttons */
        .cat-nav-right {
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: flex-end; gap: 8px;
            margin-left: auto;
        }
        .cat-search-wrap {
            width: 220px; position: relative;
        }
        .cat-search-wrap i {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 15px; pointer-events: none;
        }
        .cat-search-input {
            width: 100%; height: 36px;
            background: #f1f5f9; border: 1px solid transparent; border-radius: 8px;
            padding: 0 12px 0 34px; font-size: 13px; color: var(--text);
            outline: none; transition: all .2s;
        }
        .cat-search-input:focus {
            box-shadow: 0 0 0 2px rgba(30,109,138,.25);
            border-color: var(--primary);
            background: #fff;
        }
        .cat-search-input::placeholder { color: #94a3b8; }

        .btn-order-phone {
            background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;
            padding: 7px 12px; font-size: 12px; font-weight: 700; border-radius: 8px;
            cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
            white-space: nowrap; flex-shrink: 0; transition: all .15s;
        }
        .btn-order-phone:hover { background: #bae6fd; color: #0369a1; }

        .btn-qr-nav {
            background: #f8fafc; color: #334155; border: 1px solid #cbd5e1;
            padding: 7px 12px; font-size: 12px; font-weight: 700; border-radius: 8px;
            cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
            white-space: nowrap; flex-shrink: 0; transition: all .15s;
        }
        .btn-qr-nav:hover { background: #e2e8f0; color: #0f172a; border-color: #94a3b8; }

        .btn-login {
            padding: 7px 14px; font-size: 13px; font-weight: 700;
            color: #fff; background: var(--primary);
            border: none; border-radius: 8px; cursor: pointer;
            text-decoration: none; white-space: nowrap; flex-shrink: 0;
            display: inline-flex; align-items: center; gap: 6px;
            transition: background .15s;
        }
        .btn-login:hover { background: var(--primary-dk); color: #fff; }

        /* ── Responsive nav ─────────────────── */
        @media (max-width: 992px) {
            .cat-nav-links { display: none; }
            .cat-search-wrap { width: 160px; }
        }
        @media (max-width: 640px) {
            .cat-search-wrap { display: none; }
            .btn-order-phone span { display: none; }
            .btn-order-phone { padding: 7px 10px; }
            .btn-qr-nav span { display: none; }
            .btn-qr-nav { padding: 7px 10px; }
            .cat-nav .inner { gap: 6px; padding: 0 10px; }
            .cat-nav .brand { font-size: 15px; }
            .cat-nav .brand .ms-icon { font-size: 22px; }
        }

        /* ── Page content ───────────────── */
        .cat-page {
            max-width: 1440px; margin: 0 auto;
            padding: 24px 24px 64px;
            flex: 1;
        }

        /* ── Product card ───────────────── */
        .prod-card {
            background: #fff; border-radius: 14px;
            border: 1px solid var(--border);
            overflow: hidden; text-decoration: none; color: inherit;
            display: flex; flex-direction: column;
            transition: box-shadow .25s, border-color .25s;
        }
        .prod-card:hover {
            box-shadow: 0 16px 40px rgba(0,0,0,.10);
            border-color: rgba(30,109,138,.3);
        }
        .prod-card .img-wrap {
            width: 100%; aspect-ratio: 4/3;
            background: #f1f5f9; overflow: hidden;
            position: relative;
        }
        .prod-card .img-wrap img {
            width: 100%; height: 100%; object-fit: cover;
            transition: transform .5s;
        }
        .prod-card:hover .img-wrap img { transform: scale(1.08); }

        /* hover overlay */
        .prod-card .img-overlay {
            position: absolute; inset: 0;
            background: rgba(0,0,0,.18);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: opacity .25s;
        }
        .prod-card:hover .img-overlay { opacity: 1; }
        .prod-card .img-overlay .view-btn {
            background: #fff; color: #0f172a;
            padding: 8px 18px; border-radius: 8px; border: none;
            font-size: 13px; font-weight: 600; cursor: pointer;
            transform: translateY(12px); transition: transform .25s;
        }
        .prod-card:hover .img-overlay .view-btn { transform: translateY(0); }

        /* stock badge on image */
        .stock-badge {
            position: absolute; top: 12px; left: 12px; z-index: 5;
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 11px; font-weight: 700;
            padding: 3px 8px; border-radius: 6px;
        }
        .stock-badge .dot {
            width: 6px; height: 6px; border-radius: 50%;
        }
        .badge-instock  { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-instock .dot { background: #22c55e; }
        .badge-lowstock { background: #fef9c3; color: #a16207; border: 1px solid #fef08a; }
        .badge-lowstock .dot { background: #eab308; }
        .badge-outstock { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-outstock .dot { background: #ef4444; }

        /* card body */
        .prod-card .card-body { padding: 16px; flex: 1; display: flex; flex-direction: column; gap: 4px; }
        .prod-card .prod-cat  { font-size: 11px; color: var(--muted); }
        .prod-card .prod-name { font-size: 16px; font-weight: 700; color: var(--text); line-height: 1.3; }
        .prod-card .prod-desc { font-size: 13px; color: #475569; line-height: 1.5;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .prod-card .prod-footer { margin-top: auto; padding-top: 12px;
            display: flex; align-items: center; justify-content: space-between; }
        .prod-card .prod-price { font-size: 20px; font-weight: 700; color: var(--text); }
        .prod-card .prod-price.strike { text-decoration: line-through; color: #94a3b8; }
        .prod-card .wish-btn {
            width: 36px; height: 36px; border-radius: 8px; border: none; background: none;
            display: flex; align-items: center; justify-content: center;
            color: var(--primary); cursor: pointer; transition: background .15s;
        }
        .prod-card .wish-btn:hover { background: var(--primary-lt); }

        /* ── Category icon card ─────────── */
        .cat-icon-card {
            display: flex; flex-direction: column;
            align-items: center; text-align: center;
            padding: 16px 12px; border-radius: 12px;
            background: #fff; border: 1px solid var(--border);
            text-decoration: none; color: var(--text);
            transition: border-color .15s, box-shadow .15s;
        }
        .cat-icon-card:hover {
            border-color: rgba(30,109,138,.5);
            box-shadow: 0 4px 16px rgba(0,0,0,.08);
        }
        .cat-icon-card .icon-wrap {
            width: 64px; height: 64px; border-radius: 50%;
            background: #eff6ff; display: flex; align-items: center; justify-content: center;
            margin-bottom: 10px; transition: background .15s;
        }
        .cat-icon-card:hover .icon-wrap { background: var(--primary-lt); }
        .cat-icon-card .ms-icon { font-size: 28px; color: var(--primary); }
        .cat-icon-card .cat-name { font-size: 14px; font-weight: 600; }
        .cat-icon-card .cat-count { font-size: 12px; color: var(--muted); margin-top: 2px; }

        /* ── Section headings ───────────── */
        .sec-title { font-size: 22px; font-weight: 700; color: var(--text); }
        .sec-sub   { font-size: 13px; color: var(--muted); margin-top: 4px; }

        /* ── Footer ─────────────────────── */
        .cat-footer {
            background: #fff;
            border-top: 1px solid var(--border);
            padding: 48px 24px;
            margin-top: auto;
        }
        .cat-footer .footer-inner {
            max-width: 1440px; margin: 0 auto;
        }
        .cat-footer-grid {
            display: grid; grid-template-columns: 1.8fr 1fr 1.2fr 1fr;
            gap: 32px; margin-bottom: 32px;
        }
        .cat-footer .brand-col .brand-row {
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 12px;
        }
        .cat-footer .brand-col .brand-name {
            font-size: 17px; font-weight: 700; color: var(--text);
        }
        .cat-footer .brand-col p {
            font-size: 13px; color: var(--muted); line-height: 1.6;
        }
        .cat-footer h4 {
            font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 16px;
        }
        .cat-footer ul { list-style: none; }
        .cat-footer ul li { margin-bottom: 8px; }
        .cat-footer ul li a { font-size: 13px; color: var(--muted); text-decoration: none; transition: color .15s; }
        .cat-footer ul li a:hover { color: var(--primary); }
        .cat-footer .contact-item {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: var(--muted); margin-bottom: 8px;
        }
        .cat-footer .contact-item .ms-icon { font-size: 16px; }
        .cat-footer-bottom {
            border-top: 1px solid #f1f5f9; padding-top: 24px;
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 12px;
        }
        .cat-footer-bottom p { font-size: 12px; color: #94a3b8; }
        .cat-footer-bottom .social a {
            font-size: 12px; color: #94a3b8; text-decoration: none;
            margin-left: 16px; transition: color .15s;
        }
        .cat-footer-bottom .social a:hover { color: var(--primary); }

        @media (max-width: 900px) {
            .cat-nav-links { display: none; }
            .cat-footer-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 480px) {
            .cat-footer-grid { grid-template-columns: 1fr; }
            .cat-page { padding: 16px 16px 48px; }
        }

        /* ── Phone Order Button in Navbar ──────── */
        .btn-order-phone {
            display: inline-flex; align-items: center; gap: 7px;
            background: #f0fdf4; color: #166534;
            border: 1px solid #bbf7d0; border-radius: 8px;
            padding: 7px 13px; font-size: 13px; font-weight: 700;
            text-decoration: none; cursor: pointer; white-space: nowrap; flex-shrink: 0;
            transition: all .15s ease;
        }
        .btn-order-phone:hover {
            background: #dcfce7; color: #14532d; border-color: #86efac;
            transform: translateY(-1px);
        }
        .btn-order-phone i { font-size: 14px; }

        /* ── Phone Modal Popup ─────────────────── */
        .phone-modal-overlay {
            position: fixed; inset: 0; z-index: 99999;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(5px);
            display: none; align-items: center; justify-content: center;
            padding: 20px;
            animation: fadeIn .2s ease-out;
        }
        .phone-modal-overlay.active {
            display: flex;
        }
        .phone-modal-box {
            background: #ffffff;
            border-radius: 18px;
            width: 100%; max-width: 460px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0,0,0,.06);
            padding: 28px;
            position: relative;
            transform: scale(0.96);
            transition: transform .2s cubic-bezier(0.16, 1, 0.3, 1);
            animation: popIn .25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes popIn {
            from { opacity: 0; transform: scale(0.93) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .phone-modal-close {
            position: absolute; top: 16px; right: 16px;
            background: #f1f5f9; border: none; width: 32px; height: 32px;
            border-radius: 50%; font-size: 18px; color: #64748b;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all .15s;
        }
        .phone-modal-close:hover {
            background: #e2e8f0; color: #0f172a;
        }
        .phone-modal-header {
            text-align: center; margin-bottom: 20px;
        }
        .phone-modal-icon {
            width: 56px; height: 56px; border-radius: 50%;
            background: #e0f2fe; color: #0284c7;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 24px; margin-bottom: 12px;
        }
        .phone-modal-header h3 {
            font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 6px;
        }
        .phone-modal-header p {
            font-size: 13px; color: #64748b; line-height: 1.5;
        }
        .phone-modal-product-info {
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 12px 16px; margin-bottom: 18px; text-align: left;
        }
        .prod-badge-label {
            font-size: 10px; font-weight: 800; text-transform: uppercase;
            letter-spacing: .6px; color: #0284c7; margin-bottom: 4px;
        }
        .prod-info-title {
            font-size: 14px; font-weight: 700; color: #1e293b;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .prod-info-sub {
            font-size: 12px; color: #64748b; margin-top: 2px;
        }
        .phone-display-card {
            background: linear-gradient(135deg, #0d5c75 0%, #1e6d8a 100%);
            border-radius: 14px; padding: 20px; text-align: center;
            color: #ffffff; margin-bottom: 18px;
            box-shadow: 0 10px 25px -5px rgba(30, 109, 138, 0.4);
        }
        .phone-tag {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .8px; color: #bae6fd; margin-bottom: 6px;
        }
        .phone-number-link {
            display: inline-block; font-size: 28px; font-weight: 900;
            color: #ffffff; text-decoration: none; letter-spacing: 0.5px;
            transition: transform .15s, color .15s;
        }
        .phone-number-link:hover {
            color: #e0f2fe; transform: scale(1.02);
        }
        .phone-actions-row {
            display: flex; gap: 10px; margin-top: 16px; justify-content: center;
        }
        .btn-call-direct {
            flex: 1; padding: 10px 14px;
            background: #ffffff; color: #0d5c75 !important;
            font-size: 13px; font-weight: 700; border-radius: 9px;
            text-decoration: none !important; display: inline-flex;
            align-items: center; justify-content: center; gap: 6px;
            transition: all .15s;
        }
        .btn-call-direct:hover {
            background: #f0fdf4; color: #166534 !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .btn-copy-number {
            flex: 1; padding: 10px 14px;
            background: rgba(255, 255, 255, 0.18); color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.35);
            font-size: 13px; font-weight: 600; border-radius: 9px;
            cursor: pointer; display: inline-flex;
            align-items: center; justify-content: center; gap: 6px;
            transition: all .15s;
        }
        .btn-copy-number:hover {
            background: rgba(255, 255, 255, 0.28); color: #fff;
        }
        .phone-modal-footer-info {
            display: flex; flex-direction: column; gap: 8px; font-size: 12px; color: #64748b;
        }
        .phone-modal-footer-info .info-point {
            display: flex; align-items: center; gap: 8px;
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ── Navbar ────────────────────────────────────── --}}
<nav class="cat-nav">
    <div class="inner">
        <a href="{{ route('catalog.home') }}" class="brand">
            <img src="{{ asset('images/hassan-logo-icon.png') }}" alt="Hassan & Sons" style="width:30px; height:30px; object-fit:contain;">
            <span>Hassan & Sons</span>
        </a>

        <div class="cat-nav-links">
            @foreach(\App\Models\Category::where('is_active', true)->orderBy('name')->get() as $navCat)
            <a href="{{ route('catalog.category', $navCat->slug) }}">{{ $navCat->name }}</a>
            @endforeach
        </div>

        <div class="cat-nav-right">
            <div class="cat-search-wrap d-none d-sm-block">
                <form action="{{ route('catalog.search') }}" method="GET">
                    <i class="bi bi-search"></i>
                    <input type="text" name="q" class="cat-search-input"
                           placeholder="Search products…" value="{{ request('q') }}">
                </form>
            </div>
            <button type="button" class="btn-qr-nav" onclick="openQrModal('{{ url()->current() }}', 'Scan to Open on Mobile')" title="Scan QR Code to open on Mobile">
                <i class="bi bi-qr-code-scan"></i>
                <span>Scan QR</span>
            </button>
            <button type="button" class="btn-order-phone" onclick="openPhoneOrderModal()" title="Order from Phone Number">
                <i class="bi bi-telephone-fill"></i>
                <span>051-8891930</span>
            </button>
            <a href="{{ route('staff') }}" class="btn-login"><i class="bi bi-person-fill"></i> <span>Log In</span></a>
        </div>
    </div>
</nav>

{{-- ── Page Content ──────────────────────────────── --}}
<main style="flex:1;">
    @yield('content')
</main>

{{-- ── Footer ───────────────────────────────────── --}}
<footer class="cat-footer">
    <div class="footer-inner">
        <div class="cat-footer-grid">
            {{-- Brand --}}
            <div class="brand-col">
                <div class="brand-row">
                    <img src="{{ asset('images/hassan-logo-icon.png') }}" alt="Hassan & Sons" style="width:28px; height:28px; object-fit:contain;">
                    <span class="brand-name">Hassan & Sons</span>
                </div>
                <p>Your trusted partner for quality sanitary ware, hardware tools, plumbing, and building materials.</p>
            </div>

            {{-- Shop --}}
            <div>
                <h4>Shop</h4>
                <ul>
                    @foreach(\App\Models\Category::where('is_active', true)->orderBy('name')->get() as $fc)
                    <li><a href="{{ route('catalog.category', $fc->slug) }}">{{ $fc->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h4>Contact</h4>
                <div class="contact-item">
                    <span class="material-symbols-outlined ms-icon">call</span>
                    <a href="tel:051-8891930" onclick="openPhoneOrderModal(); return false;" style="color:inherit;text-decoration:none;font-weight:600;">
                        051-8891930
                    </a>
                </div>
                @if(config('store.whatsapp'))
                <div class="contact-item">
                    <span class="material-symbols-outlined ms-icon">chat</span>
                    <a href="https://wa.me/{{ config('store.whatsapp') }}" target="_blank" rel="noopener" style="color:inherit;text-decoration:none;">{{ config('store.whatsapp') }}</a>
                </div>
                @endif
                @if(config('store.address'))
                <div class="contact-item" style="align-items:flex-start;">
                    <span class="material-symbols-outlined ms-icon" style="margin-top:2px;flex-shrink:0;">location_on</span>
                    <span>{{ config('store.address') }}</span>
                </div>
                @endif
            </div>

            {{-- QR Code Column --}}
            <div>
                <h4>Scan &amp; Visit</h4>
                <div style="background:#fff; padding:8px; border-radius:12px; border:1px solid var(--border); display:inline-block; margin-bottom:8px; box-shadow:0 2px 8px rgba(0,0,0,.04); cursor:pointer;" onclick="openQrModal('{{ url('/') }}', 'Hassan & Sons Store')">
                    <img src="{{ asset('images/hassanandsons-qr.png') }}" alt="Store QR Code" style="width:100px; height:100px; display:block; border-radius:6px;">
                </div>
                <p style="font-size:12px; color:var(--muted); line-height:1.4; margin-bottom:6px;">Scan with your smartphone camera to access store on mobile.</p>
                <a href="{{ asset('images/hassanandsons-qr.png') }}" download="Hassan-and-Sons-QR.png" style="font-size:12px; font-weight:700; color:var(--primary); text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                    <i class="bi bi-download"></i> Download QR
                </a>
            </div>
        </div>

        <div class="cat-footer-bottom">
            <p>&copy; {{ date('Y') }} Hassan & Sons. All rights reserved.</p>
        </div>
    </div>
</footer>

{{-- ── Phone Order Popup Modal ───────────────────── --}}
<div id="phoneOrderModal" class="phone-modal-overlay" onclick="if(event.target===this) closePhoneOrderModal()">
    <div class="phone-modal-box">
        <button type="button" class="phone-modal-close" onclick="closePhoneOrderModal()" aria-label="Close popup">&times;</button>
        
        <div class="phone-modal-header">
            <div class="phone-modal-icon">
                <i class="bi bi-telephone-outbound-fill"></i>
            </div>
            <h3>Order by Phone</h3>
            <p>Call our direct store phone number to place your order or check product availability.</p>
        </div>

        {{-- Optional Product Details (populated dynamically if on product page) --}}
        <div id="phoneModalProductInfo" class="phone-modal-product-info" style="display:none;">
            <div class="prod-badge-label"><i class="bi bi-box-seam me-1"></i> Ordering Product</div>
            <div class="prod-info-title" id="modalProdName"></div>
            <div class="prod-info-sub">
                SKU: <span id="modalProdSku"></span> &bull; Price: <strong id="modalProdPrice" style="color:#065f46;"></strong>
            </div>
        </div>

        {{-- Phone display card --}}
        <div class="phone-display-card">
            <div class="phone-tag">Direct Store Helpline</div>
            <a href="tel:051-8891930" class="phone-number-link">
                <i class="bi bi-telephone-fill me-1"></i> 051-8891930
            </a>
            <div class="phone-actions-row">
                <a href="tel:051-8891930" class="btn-call-direct">
                    <i class="bi bi-telephone-forward-fill"></i> Call 051-8891930
                </a>
                <button type="button" class="btn-copy-number" id="btnCopyPhone" onclick="copyPhoneNumber('051-8891930')">
                    <i class="bi bi-clipboard" id="copyIcon"></i> <span id="copyText">Copy Number</span>
                </button>
            </div>
        </div>

        <div class="phone-modal-footer-info">
            <div class="info-point">
                <i class="bi bi-geo-alt-fill text-danger"></i>
                <span>Rafi Commercial, Bahria Town Phase 8, Rawalpindi.</span>
            </div>
            <div class="info-point">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Instant telephone assistance & quick store delivery</span>
            </div>
        </div>
    </div>
</div>

{{-- ── QR Code Popup Modal ───────────────────── --}}
<div id="qrCodeModal" class="phone-modal-overlay" onclick="if(event.target===this) closeQrModal()">
    <div class="phone-modal-box" style="text-align:center;">
        <button type="button" class="phone-modal-close" onclick="closeQrModal()" aria-label="Close popup">&times;</button>
        
        <div class="phone-modal-header" style="margin-bottom:14px;">
            <div class="phone-modal-icon" style="background:#e0f2fe; color:#0284c7;">
                <i class="bi bi-qr-code-scan"></i>
            </div>
            <h3 id="qrModalTitle">Scan &amp; Access Store</h3>
            <p id="qrModalDesc">Point your smartphone camera at the QR code below to open this page instantly on your mobile phone.</p>
        </div>

        <div style="background:#fff; border:2px dashed #cbd5e1; border-radius:16px; padding:16px; display:inline-block; margin-bottom:18px; box-shadow:0 4px 16px rgba(0,0,0,0.06);">
            <img id="qrModalImage" src="{{ asset('images/hassanandsons-qr.png') }}" alt="Website QR Code" style="width:200px; height:200px; display:block; border-radius:8px;">
        </div>

        <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap; margin-bottom:16px;">
            <a id="btnDownloadQr" href="{{ asset('images/hassanandsons-qr.png') }}" download="Hassan-and-Sons-QR.png" class="btn-call-direct" style="background:#1e6d8a; color:#fff !important; flex:initial; padding:10px 18px;">
                <i class="bi bi-download"></i> Download QR Image
            </a>
            <button type="button" class="btn-copy-number" id="btnCopyPageLink" style="background:#f1f5f9; color:#0f172a; border-color:#cbd5e1; flex:initial; padding:10px 18px;" onclick="copyPageLink()">
                <i class="bi bi-link-45deg" id="copyLinkIcon"></i> <span id="copyLinkText">Copy Link</span>
            </button>
        </div>

        <div class="phone-modal-footer-info" style="text-align:left;">
            <div class="info-point">
                <i class="bi bi-phone-fill text-primary"></i>
                <span>Compatible with iOS Camera (iPhone) &amp; Android Lens / Scanner</span>
            </div>
            <div class="info-point">
                <i class="bi bi-shield-check text-success"></i>
                <span>Direct link to official Hassan &amp; Sons online store</span>
            </div>
        </div>
    </div>
</div>

<script>
    // Open Phone Order Modal
    function openPhoneOrderModal(productName = '', productSku = '', productPrice = '') {
        const modal = document.getElementById('phoneOrderModal');
        const prodBox = document.getElementById('phoneModalProductInfo');
        
        if (productName) {
            document.getElementById('modalProdName').textContent = productName;
            document.getElementById('modalProdSku').textContent = productSku || '—';
            document.getElementById('modalProdPrice').textContent = productPrice || '—';
            prodBox.style.display = 'block';
        } else {
            prodBox.style.display = 'none';
        }

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // Close Phone Order Modal
    function closePhoneOrderModal() {
        const modal = document.getElementById('phoneOrderModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // QR Code Modal
    let currentQrUrl = "{{ url('/') }}";

    function openQrModal(targetUrl, title = 'Scan & Access Store') {
        currentQrUrl = targetUrl || "{{ url('/') }}";
        const modal = document.getElementById('qrCodeModal');
        const img = document.getElementById('qrModalImage');
        const dlBtn = document.getElementById('btnDownloadQr');
        const titleEl = document.getElementById('qrModalTitle');

        if (titleEl) titleEl.textContent = title;

        if (!targetUrl || targetUrl === "{{ url('/') }}" || targetUrl.indexOf('localhost') !== -1 || targetUrl.indexOf('127.0.0.1') !== -1) {
            img.src = "{{ asset('images/hassanandsons-qr.png') }}";
            dlBtn.href = "{{ asset('images/hassanandsons-qr.png') }}";
        } else {
            const dynamicQr = "https://api.qrserver.com/v1/create-qr-code/?size=600x600&margin=15&data=" + encodeURIComponent(currentQrUrl);
            img.src = dynamicQr;
            dlBtn.href = dynamicQr;
        }

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeQrModal() {
        const modal = document.getElementById('qrCodeModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function copyPageLink() {
        const text = currentQrUrl || window.location.href;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => showCopyLinkSuccess());
        } else {
            const input = document.createElement('input');
            input.value = text;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            showCopyLinkSuccess();
        }
    }

    function showCopyLinkSuccess() {
        const copyLinkText = document.getElementById('copyLinkText');
        const copyLinkIcon = document.getElementById('copyLinkIcon');
        const orig = copyLinkText.textContent;
        copyLinkText.textContent = 'Link Copied!';
        copyLinkIcon.className = 'bi bi-check-lg text-success';
        setTimeout(() => {
            copyLinkText.textContent = orig;
            copyLinkIcon.className = 'bi bi-link-45deg';
        }, 2200);
    }

    // Copy Phone Number to Clipboard with visual feedback
    function copyPhoneNumber(num) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(num).then(() => showCopySuccess());
        } else {
            const input = document.createElement('input');
            input.value = num;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            showCopySuccess();
        }
    }

    function showCopySuccess() {
        const copyText = document.getElementById('copyText');
        const copyIcon = document.getElementById('copyIcon');
        const origText = copyText.textContent;
        
        copyText.textContent = 'Copied!';
        copyIcon.className = 'bi bi-check-lg';
        
        setTimeout(() => {
            copyText.textContent = origText;
            copyIcon.className = 'bi bi-clipboard';
        }, 2200);
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePhoneOrderModal();
            closeQrModal();
        }
    });
</script>

@stack('scripts')
</body>
</html>

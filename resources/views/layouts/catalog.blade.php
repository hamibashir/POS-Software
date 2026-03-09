<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Browse our full range of hardware and sanitary products.')">
    <title>@yield('title', 'Product Catalog') — Hardware World</title>

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
        body { background: var(--bg); color: var(--text); min-height: 100vh; display: flex; flex-direction: column; }

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
        }
        .cat-nav .inner {
            max-width: 1440px; margin: 0 auto;
            display: flex; align-items: center; gap: 16px;
            padding: 0 24px; height: 64px;
        }
        .cat-nav .brand {
            display: flex; align-items: center; gap: 8px;
            font-weight: 800; font-size: 18px; color: var(--text);
            text-decoration: none; white-space: nowrap; flex-shrink: 0;
        }
        .cat-nav .brand .ms-icon { font-size: 28px; color: var(--primary); }

        /* Category nav links */
        .cat-nav-links {
            display: flex; gap: 24px; margin: 0 16px;
        }
        .cat-nav-links a {
            font-size: 14px; font-weight: 500; color: #475569;
            text-decoration: none; transition: color .15s; white-space: nowrap;
        }
        .cat-nav-links a:hover { color: var(--primary); }

        /* Search */
        .cat-nav-right {
            flex: 1; display: flex; align-items: center; justify-content: flex-end; gap: 12px;
        }
        .cat-search-wrap {
            flex: 1; max-width: 420px; position: relative;
        }
        .cat-search-wrap i {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 16px; pointer-events: none;
        }
        .cat-search-input {
            width: 100%; height: 38px;
            background: #f1f5f9; border: none; border-radius: 8px;
            padding: 0 14px 0 36px; font-size: 14px; color: var(--text);
            outline: none; transition: box-shadow .2s;
        }
        .cat-search-input:focus {
            box-shadow: 0 0 0 2px rgba(30,109,138,.25);
            background: #fff;
        }
        .cat-search-input::placeholder { color: #94a3b8; }

        .btn-login {
            padding: 7px 16px; font-size: 13px; font-weight: 600;
            color: var(--primary); background: var(--primary-lt);
            border: none; border-radius: 8px; cursor: pointer;
            text-decoration: none; white-space: nowrap;
            transition: background .15s;
        }
        .btn-login:hover { background: #c8e4ee; }
        .btn-signup {
            padding: 7px 16px; font-size: 13px; font-weight: 600;
            color: #fff; background: var(--primary);
            border: none; border-radius: 8px; cursor: pointer;
            text-decoration: none; white-space: nowrap;
            transition: background .15s;
        }
        .btn-signup:hover { background: var(--primary-dk); }

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
            display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr;
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

        @media (max-width: 768px) {
            .cat-nav-links { display: none; }
            .cat-footer-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 480px) {
            .cat-footer-grid { grid-template-columns: 1fr; }
            .cat-page { padding: 16px 16px 48px; }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ── Navbar ────────────────────────────────────── --}}
<nav class="cat-nav">
    <div class="inner">
        <a href="{{ route('catalog.home') }}" class="brand">
            <span class="material-symbols-outlined ms-icon">home_repair_service</span>
            <span>Hardware World</span>
        </a>

        <div class="cat-nav-links">
            @foreach(\App\Models\Category::where('is_active', true)->orderBy('name')->take(5)->get() as $navCat)
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
            <a href="{{ route('staff') }}" class="btn-login">Log In</a>
            <a href="{{ route('staff') }}" class="btn-signup">Sign Up</a>
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
                    <span class="material-symbols-outlined ms-icon" style="color:var(--primary);font-size:22px;">home_repair_service</span>
                    <span class="brand-name">Hardware World</span>
                </div>
                <p>Your trusted partner for quality tools, materials, and expert advice for every project.</p>
            </div>

            {{-- Shop --}}
            <div>
                <h4>Shop</h4>
                <ul>
                    @foreach(\App\Models\Category::where('is_active', true)->orderBy('name')->take(4)->get() as $fc)
                    <li><a href="{{ route('catalog.category', $fc->slug) }}">{{ $fc->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Support --}}
            <div>
                <h4>Support</h4>
                <ul>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Shipping</a></li>
                    <li><a href="#">Returns</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h4>Contact</h4>
                @if(config('store.phone'))
                <div class="contact-item">
                    <span class="material-symbols-outlined ms-icon">call</span>
                    {{ config('store.phone') }}
                </div>
                @endif
                @if(config('store.whatsapp'))
                <div class="contact-item">
                    <span class="material-symbols-outlined ms-icon">chat</span>
                    {{ config('store.whatsapp') }}
                </div>
                @endif
                @if(config('store.address'))
                <div class="contact-item">
                    <span class="material-symbols-outlined ms-icon">location_on</span>
                    {{ config('store.address') }}
                </div>
                @endif
            </div>
        </div>

        <div class="cat-footer-bottom">
            <p>&copy; {{ date('Y') }} Hardware World. All rights reserved.</p>
            <div class="social">
                <a href="#">FB</a>
                <a href="#">TW</a>
                <a href="#">IG</a>
            </div>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Hassan & Sons</title>

    <!-- Favicon & App Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=6">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=6">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=6">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=6">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=6">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --pos-primary:     #1a6b7c;
            --pos-primary-dk:  #0d4a57;
            --pos-primary-lt:  #e8f4f7;
            --pos-sidebar-w:   240px;
            --pos-topbar-h:    58px;
        }

        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }
        body { background: #f3f4f6; margin: 0; min-height: 100vh; overflow-x: hidden; }

        /* ── Topbar ───────────────────────────────── */
        .pos-topbar {
            position: fixed; top: 0; left: 0; right: 0; height: var(--pos-topbar-h);
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            display: flex; align-items: center;
            padding: 0 14px;
            z-index: 1040;
            gap: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }

        .pos-topbar .brand {
            display: flex; align-items: center; gap: 6px;
            font-weight: 800; font-size: 15px; color: var(--pos-primary);
            text-decoration: none; white-space: nowrap; flex-shrink: 0;
            padding-right: 6px;
        }

        .pos-topbar .brand i { font-size: 18px; }

        /* Direct, clean navigation links row */
        .pos-nav-container {
            display: flex;
            align-items: center;
            gap: 2px;
            flex: 1;
            min-width: 0;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .pos-nav-container::-webkit-scrollbar { display: none; }

        .pos-nav-link {
            font-size: 12.5px; font-weight: 600; color: #4b5563;
            padding: 5px 8px; border-radius: 6px;
            text-decoration: none; transition: all .15s;
            white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;
            flex-shrink: 0;
        }

        .pos-nav-link:hover {
            background: var(--pos-primary-lt);
            color: var(--pos-primary);
        }

        .pos-nav-link.active {
            background: var(--pos-primary-lt);
            color: var(--pos-primary);
            font-weight: 700;
        }

        .pos-nav-link i { font-size: 13px; }

        /* Right Action Area */
        .pos-topbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
            flex-shrink: 0;
        }

        .btn-pos-counter {
            background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;
            padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 700;
            text-decoration: none; display: inline-flex; align-items: center; gap: 4px;
            white-space: nowrap; transition: all .15s;
        }
        .btn-pos-counter:hover { background: #bae6fd; color: #0369a1; }

        .user-badge {
            display: flex; align-items: center; gap: 6px;
            font-size: 12px; color: #374151; white-space: nowrap;
        }

        .user-badge .avatar {
            width: 28px; height: 28px; background: var(--pos-primary);
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; color: #fff; font-size: 12px; font-weight: 700;
        }

        .btn-logout {
            display: flex; align-items: center; gap: 5px;
            background: #ef4444; color: #fff !important;
            border: none; border-radius: 6px;
            padding: 5px 12px; font-size: 12px; font-weight: 700;
            cursor: pointer; text-decoration: none; transition: opacity .15s;
            white-space: nowrap; flex-shrink: 0;
        }
        .btn-logout:hover { opacity: .88; color: #fff; }

        .btn-hamburger {
            background: none; border: 1px solid #e5e7eb; border-radius: 6px;
            padding: 4px 8px; font-size: 18px; color: #374151;
            cursor: pointer; display: none; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        /* ── Responsive breakpoints ─────────────────── */
        @media (max-width: 768px) {
            .btn-hamburger { display: flex; }
            .pos-nav-container { display: none; }
            .user-badge .user-meta { display: none; }
        }

        @media (max-width: 576px) {
            .pos-topbar { padding: 0 8px; gap: 6px; }
            .btn-pos-counter span { display: none; }
            .btn-logout span { display: none; }
            .btn-logout { padding: 5px 8px; }
            .brand span { display: none; }
        }

        /* ── Page wrapper ─────────────────────────── */
        .pos-page {
            margin-top: var(--pos-topbar-h);
            padding: 24px 20px 48px;
            min-height: calc(100vh - var(--pos-topbar-h));
            max-width: 100vw;
            box-sizing: border-box;
        }

        /* ── Cards ───────────────────────────────── */
        .pos-card {
            background: #fff; border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }

        /* ── Page header  ────────────────────────── */
        .page-hero { margin-bottom: 24px; }
        .page-hero h1 { font-size: 22px; font-weight: 700; color: #111827; margin: 0; }
        .page-hero p  { font-size: 14px; color: #6b7280; margin: 4px 0 0; }

        /* ── Badges ──────────────────────────────── */
        .badge-active   { background: #d1fae5; color: #065f46; font-size: 12px; font-weight: 600; }
        .badge-inactive { background: #fee2e2; color: #991b1b; font-size: 12px; font-weight: 600; }

        /* ── Table ───────────────────────────────── */
        .pos-table thead th {
            background: #f9fafb; font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: .6px; color: #6b7280;
            border-bottom: 1px solid #e5e7eb; padding: 12px 16px;
        }
        .pos-table tbody td { padding: 14px 16px; font-size: 14px; color: #374151; vertical-align: middle; border-bottom: 1px solid #f3f4f6; }
        .pos-table tbody tr:last-child td { border-bottom: none; }
        .pos-table tbody tr:hover { background: #fafafa; }

        /* ── Buttons ─────────────────────────────── */
        .btn-pos {
            background: var(--pos-primary); color: #fff;
            border: none; border-radius: 8px;
            padding: 8px 18px; font-size: 14px; font-weight: 600;
            cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
            transition: opacity .15s;
        }
        .btn-pos:hover { opacity: .88; color: #fff; }

        .btn-pos-outline {
            background: transparent; color: var(--pos-primary);
            border: 1.5px solid var(--pos-primary); border-radius: 8px;
            padding: 7px 16px; font-size: 14px; font-weight: 600;
            cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
            transition: all .15s;
        }
        .btn-pos-outline:hover { background: var(--pos-primary-lt); }

        /* ── Form inputs ─────────────────────────── */
        .pos-input {
            border: 1.5px solid #e5e7eb; border-radius: 8px;
            padding: 9px 14px; font-size: 14px; color: #111827;
            width: 100%; transition: border-color .15s;
        }
        .pos-input:focus {
            outline: none; border-color: var(--pos-primary);
            box-shadow: 0 0 0 3px rgba(26,107,124,.1);
        }

        /* ── Alert flash ─────────────────────────── */
        .pos-alert {
            border-radius: 10px; padding: 12px 16px;
            font-size: 14px; font-weight: 500;
            display: flex; align-items: center; gap: 10px;
        }
        .pos-alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
        .pos-alert-error   { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }

        /* ── Modal ───────────────────────────────── */
        .modal-header { border-bottom: 1px solid #f3f4f6; padding: 20px 24px 16px; }
        .modal-header .modal-title { font-size: 17px; font-weight: 700; color: #111827; }
        .modal-footer { border-top: 1px solid #f3f4f6; padding: 16px 24px; }
        .modal-body   { padding: 20px 24px; }
        .modal-content { border-radius: 14px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,.15); }

        /* ── Filter bar ──────────────────────────── */
        .filter-bar { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .filter-bar .pos-input { width: auto; flex: 1; min-width: 200px; }

        /* ── Offcanvas Mobile Drawer ─────────────── */
        .offcanvas-admin { width: 280px !important; }
        .offcanvas-admin .nav-mobile-link {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 16px; border-radius: 10px;
            color: #374151; font-weight: 600; font-size: 14px;
            text-decoration: none; margin-bottom: 4px;
            transition: all .15s;
        }
        .offcanvas-admin .nav-mobile-link:hover,
        .offcanvas-admin .nav-mobile-link.active {
            background: var(--pos-primary-lt);
            color: var(--pos-primary);
        }
        .offcanvas-admin .nav-mobile-link i { font-size: 18px; width: 22px; text-align: center; }
    </style>

    @stack('styles')
</head>
<body>

{{-- ── Topbar ─────────────────────────────────────── --}}
<nav class="pos-topbar">
    <button class="btn-hamburger" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminMobileMenu" aria-label="Open menu">
        <i class="bi bi-list"></i>
    </button>

    <a href="{{ route('admin.dashboard') }}" class="brand">
        <img src="{{ asset('images/hassan-logo-icon.png') }}" alt="Logo" style="width:28px; height:28px; object-fit:contain;">
        <span>Hassan <strong style="color:#111827">& Sons</strong></span>
    </a>

    {{-- All 11 Navigation Links in Direct Top Header --}}
    <div class="pos-nav-container">
        <a href="{{ route('admin.dashboard') }}"
           class="pos-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>Dashboard
        </a>
        <a href="{{ route('admin.categories.index') }}"
           class="pos-nav-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i>Categories
        </a>
        <a href="{{ route('admin.products.index') }}"
           class="pos-nav-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i>Products
        </a>
        <a href="{{ route('admin.sales.index') }}"
           class="pos-nav-link {{ request()->routeIs('admin.sales*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i>Sales
        </a>
        <a href="{{ route('admin.purchases.index') }}"
           class="pos-nav-link {{ request()->routeIs('admin.purchases*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i>Purchases
        </a>
        <a href="{{ route('admin.suppliers.index') }}"
           class="pos-nav-link {{ request()->routeIs('admin.suppliers*') ? 'active' : '' }}">
            <i class="bi bi-building"></i>Suppliers
        </a>
        <a href="{{ route('admin.purchase-returns.index') }}"
           class="pos-nav-link {{ request()->routeIs('admin.purchase-returns*') ? 'active' : '' }}">
            <i class="bi bi-arrow-return-left"></i>Returns
        </a>
        <a href="{{ route('admin.stock.index') }}"
           class="pos-nav-link {{ request()->routeIs('admin.stock*') ? 'active' : '' }}">
            <i class="bi bi-arrow-left-right"></i>Stock
        </a>
        <a href="{{ route('admin.reports.index') }}"
           class="pos-nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i>Reports
        </a>
        <a href="{{ route('admin.expenses.index') }}"
           class="pos-nav-link {{ request()->routeIs('admin.expenses*') ? 'active' : '' }}">
            <i class="bi bi-wallet2"></i>Expenses
        </a>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.attendance.index') }}"
           class="pos-nav-link {{ request()->routeIs('admin.attendance*') ? 'active' : '' }}">
            <i class="bi bi-calendar-check"></i>Attendance
        </a>
        <a href="{{ route('admin.staff.index') }}"
           class="pos-nav-link {{ request()->routeIs('admin.staff*') ? 'active' : '' }}"
           style="background: #ede9fe; color: #6d28d9; font-weight: 700;">
            <i class="bi bi-people-fill"></i>Customers & Users
        </a>
        @endif
    </div>

    {{-- Always-Visible Right Control Area --}}
    <div class="pos-topbar-right">
        <a href="{{ route('cashier.pos') }}" class="btn-pos-counter" title="Open POS Counter">
            <i class="bi bi-cart3"></i>
            <span>POS</span>
        </a>

        <div class="user-badge">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="user-meta">
                <div style="font-weight:700; font-size:12px; color:#111827; line-height:1.2;">{{ auth()->user()->name }}</div>
                <div style="font-size:10px; color:#9ca3af; text-transform:capitalize;">{{ auth()->user()->role }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
            @csrf
            <button type="submit" class="btn-logout" title="Sign Out">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</nav>

{{-- ── Mobile Drawer Navigation (Only on mobile phones < 768px) ──── --}}
<div class="offcanvas offcanvas-start offcanvas-admin" tabindex="-1" id="adminMobileMenu">
    <div class="offcanvas-header border-bottom">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-tools text-primary fs-5"></i>
            <span class="fw-bold fs-6">Hassan & Sons</span>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column justify-content-between p-3">
        <div class="d-flex flex-column">
            <div class="text-uppercase text-muted px-2 mb-2" style="font-size:11px; font-weight:700; letter-spacing:.6px;">Menu Navigation</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-mobile-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('admin.categories.index') }}" class="nav-mobile-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i> Categories
            </a>
            <a href="{{ route('admin.products.index') }}" class="nav-mobile-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Products
            </a>
            <a href="{{ route('admin.sales.index') }}" class="nav-mobile-link {{ request()->routeIs('admin.sales*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Sales History
            </a>
            <a href="{{ route('admin.purchases.index') }}" class="nav-mobile-link {{ request()->routeIs('admin.purchases*') ? 'active' : '' }}">
                <i class="bi bi-truck"></i> Purchases
            </a>
            <a href="{{ route('admin.suppliers.index') }}" class="nav-mobile-link {{ request()->routeIs('admin.suppliers*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Suppliers
            </a>
            <a href="{{ route('admin.purchase-returns.index') }}" class="nav-mobile-link {{ request()->routeIs('admin.purchase-returns*') ? 'active' : '' }}">
                <i class="bi bi-arrow-return-left"></i> Returns
            </a>
            <a href="{{ route('admin.stock.index') }}" class="nav-mobile-link {{ request()->routeIs('admin.stock*') ? 'active' : '' }}">
                <i class="bi bi-arrow-left-right"></i> Stock Adjustments
            </a>
            <a href="{{ route('admin.reports.index') }}" class="nav-mobile-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Reports
            </a>
            <a href="{{ route('admin.expenses.index') }}" class="nav-mobile-link {{ request()->routeIs('admin.expenses*') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i> Daily Expenses
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.attendance.index') }}" class="nav-mobile-link {{ request()->routeIs('admin.attendance*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i> Attendance & Payroll
            </a>
            <a href="{{ route('admin.staff.index') }}" class="nav-mobile-link {{ request()->routeIs('admin.staff*') ? 'active' : '' }}" style="background:#ede9fe; color:#6d28d9; font-weight:700;">
                <i class="bi bi-people-fill"></i> Customers & Users
            </a>
            @endif

            <hr class="my-3">

            <a href="{{ route('cashier.pos') }}" class="btn btn-primary w-100 mb-2 py-2 fw-semibold">
                <i class="bi bi-cart3 me-1"></i> Open POS Counter
            </a>
        </div>

        <div class="border-top pt-3">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar" style="width:34px; height:34px; background:var(--pos-primary); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:13px;">{{ auth()->user()->name }}</div>
                        <div class="text-muted" style="font-size:11px; text-transform:capitalize;">{{ auth()->user()->role }}</div>
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 fw-semibold">
                    <i class="bi bi-box-arrow-right me-1"></i> Sign Out
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ── Page content ──────────────────────────────── --}}
<main class="pos-page">
    @yield('content')
</main>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')
</body>
</html>

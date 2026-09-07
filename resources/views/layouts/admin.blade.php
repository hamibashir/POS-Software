<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1280">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Hassan Sanitary and Hardware Store</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --pos-primary:     #1a6b7c;
            --pos-primary-dk:  #0d4a57;
            --pos-primary-lt:  #e8f4f7;
            --pos-sidebar-w:   240px;
            --pos-topbar-h:    60px;
        }

        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }
        body { background: #f3f4f6; margin: 0; min-height: 100vh; }

        /* ── Topbar ───────────────────────────────── */
        .pos-topbar {
            position: fixed; top: 0; left: 0; right: 0; height: var(--pos-topbar-h);
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            display: flex; align-items: center;
            padding: 0 24px;
            z-index: 1040;
            gap: 8px;
        }

        .pos-topbar .brand {
            display: flex; align-items: center; gap: 8px;
            font-weight: 700; font-size: 16px; color: var(--pos-primary);
            text-decoration: none; margin-right: 24px; white-space: nowrap;
        }

        .pos-topbar .brand i { font-size: 22px; }

        .pos-topbar .nav-link {
            font-size: 14px; font-weight: 500; color: #6b7280;
            padding: 6px 14px; border-radius: 8px;
            text-decoration: none; transition: all .15s;
            white-space: nowrap;
        }

        .pos-topbar .nav-link:hover,
        .pos-topbar .nav-link.active {
            background: var(--pos-primary-lt);
            color: var(--pos-primary);
        }

        .pos-topbar .nav-link i { margin-right: 5px; }

        .pos-topbar .ms-auto { margin-left: auto !important; }

        .pos-topbar .user-badge {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: #374151;
        }

        .pos-topbar .user-badge .avatar {
            width: 34px; height: 34px; background: var(--pos-primary);
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; color: #fff; font-size: 14px; font-weight: 600;
        }

        .btn-logout {
            display: flex; align-items: center; gap: 6px;
            background: var(--pos-primary); color: #fff !important;
            border: none; border-radius: 8px;
            padding: 7px 16px; font-size: 13px; font-weight: 600;
            cursor: pointer; text-decoration: none; transition: opacity .15s;
        }
        .btn-logout:hover { opacity: .88; color: #fff; }

        /* ── Page wrapper ─────────────────────────── */
        .pos-page {
            margin-top: var(--pos-topbar-h);
            padding: 28px 28px 48px;
            min-height: calc(100vh - var(--pos-topbar-h));
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
    </style>

    @stack('styles')
</head>
<body>

{{-- ── Topbar ─────────────────────────────────────── --}}
<nav class="pos-topbar">
    <a href="{{ route('admin.dashboard') }}" class="brand">
        <i class="bi bi-tools"></i>
        Hassan Sanitary <span style="color:#111827">& Hardware</span>
    </a>

    <a href="{{ route('admin.dashboard') }}"
       class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i>Dashboard
    </a>
    <a href="{{ route('admin.categories.index') }}"
       class="nav-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
        <i class="bi bi-tags"></i>Categories
    </a>
    <a href="{{ route('admin.products.index') }}"
       class="nav-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
        <i class="bi bi-box-seam"></i>Products
    </a>
    <a href="{{ route('admin.sales.index') }}"
       class="nav-link {{ request()->routeIs('admin.sales*') ? 'active' : '' }}">
        <i class="bi bi-receipt"></i>Sales
    </a>
    <a href="{{ route('admin.purchases.index') }}"
       class="nav-link {{ request()->routeIs('admin.purchases*') ? 'active' : '' }}">
        <i class="bi bi-truck"></i>Purchases
    </a>
    <a href="{{ route('admin.purchase-returns.index') }}"
       class="nav-link {{ request()->routeIs('admin.purchase-returns*') ? 'active' : '' }}">
        <i class="bi bi-arrow-return-left"></i>Returns
    </a>
    <a href="{{ route('admin.stock.index') }}"
       class="nav-link {{ request()->routeIs('admin.stock*') ? 'active' : '' }}">
        <i class="bi bi-arrow-left-right"></i>Stock
    </a>
    <a href="{{ route('admin.reports.index') }}"
       class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
        <i class="bi bi-bar-chart-line"></i>Reports
    </a>
    @if(auth()->user()->isAdmin())
    <a href="{{ route('admin.staff.index') }}"
       class="nav-link {{ request()->routeIs('admin.staff*') ? 'active' : '' }}">
        <i class="bi bi-people"></i>Staff
    </a>
    @endif

    <div class="ms-auto d-flex align-items-center gap-3">
        <div class="user-badge">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div>
                <div style="font-weight:600; font-size:13px;">{{ auth()->user()->name }}</div>
                <div style="font-size:11px; color:#9ca3af; text-transform:capitalize;">{{ auth()->user()->role }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>
</nav>

{{-- ── Page content ──────────────────────────────── --}}
<main class="pos-page">
    @yield('content')
</main>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')
</body>
</html>

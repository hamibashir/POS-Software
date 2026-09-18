<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS') — Hassan & Sons</title>

    <!-- Favicon & App Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=6">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=6">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=6">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=6">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=6">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --pos-primary:    #1a6b7c;
            --pos-primary-dk: #0d4a57;
            --pos-primary-lt: #e8f4f7;
            --pos-bar-h:      52px;
        }
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }
        body { background: #f1f5f9; margin: 0; overflow: hidden; height: 100vh; }

        /* ── Top bar ────────────────────────── */
        .pos-bar {
            height: var(--pos-bar-h);
            background: var(--pos-primary);
            display: flex; align-items: center;
            padding: 0 20px; gap: 12px;
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
        }
        .pos-bar .brand { color: #fff; font-weight: 700; font-size: 15px; display:flex; align-items:center; gap:6px; }
        .pos-bar .clock { color: rgba(255,255,255,.75); font-size: 13px; margin-left: auto; }
        .pos-bar .cashier-name { color: rgba(255,255,255,.85); font-size: 13px; display:flex; align-items:center; gap:6px; }
        .pos-bar .btn-admin-back {
            background: rgba(255,255,255,.15); color:#fff; border: 1px solid rgba(255,255,255,.3);
            border-radius:6px; padding: 4px 12px; font-size:12px; text-decoration:none;
            transition: background .15s;
        }
        .pos-bar .btn-admin-back:hover { background: rgba(255,255,255,.25); }

        /* ── Logout form ─────────────────────── */
        .pos-bar form { margin: 0; }
        .pos-bar .btn-logout-sm {
            background:rgba(255,255,255,.15); color:#fff; border:1px solid rgba(255,255,255,.3);
            border-radius:6px; padding:4px 10px; font-size:12px; cursor:pointer; transition: background .15s;
        }
        .pos-bar .btn-logout-sm:hover { background:rgba(255,255,255,.25); }
    </style>

    @stack('styles')
</head>
<body>

<div class="pos-bar">
    <div class="brand"><img src="{{ asset('images/hassan-logo-icon.png') }}" alt="Logo" style="width:26px; height:26px; object-fit:contain;"> Hassan &amp; Sons</div>

    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.dashboard') }}" class="btn-admin-back">
            <i class="bi bi-shield-check me-1"></i>Admin
        </a>
    @endif

    <div class="cashier-name ms-auto">
        <i class="bi bi-person-badge"></i>
        {{ auth()->user()->name }}
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-logout-sm">
            <i class="bi bi-box-arrow-right"></i> Logout
        </button>
    </form>
</div>

@yield('content')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS') — Hassan Corporation</title>

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
    <!-- TomSelect Searchable Dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --pos-primary:    #1a6b7c;
            --pos-primary-dk: #0d4a57;
            --pos-primary-lt: #e8f4f7;
            --pos-bar-h:      52px;
        }

        /* ── TomSelect Searchable Dropdown Overrides ─────────────────────── */
        .ts-wrapper.form-select, .ts-wrapper.form-control, .ts-control {
            border-radius: 10px !important;
            border: 1.5px solid #d1d5db !important;
            padding: 8px 12px !important;
            font-size: 13.5px !important;
            font-family: 'Inter', sans-serif !important;
            background-color: #ffffff !important;
            min-height: 44px !important;
            display: flex !important;
            align-items: center !important;
            box-shadow: none !important;
            transition: border-color .15s ease, box-shadow .15s ease !important;
        }
        .ts-wrapper.focus .ts-control, .ts-control:focus {
            border-color: var(--pos-primary) !important;
            box-shadow: 0 0 0 3px rgba(26, 107, 124, 0.18) !important;
            outline: none !important;
        }
        .ts-dropdown {
            border-radius: 10px !important;
            border: 1.5px solid #e5e7eb !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
            font-size: 13px !important;
            font-family: 'Inter', sans-serif !important;
            z-index: 1080 !important;
            overflow: hidden !important;
        }
        .ts-dropdown .option {
            padding: 9px 12px !important;
            cursor: pointer !important;
            transition: background-color .1s ease !important;
        }
        .ts-dropdown .option:hover, .ts-dropdown .active {
            background-color: var(--pos-primary-lt) !important;
            color: var(--pos-primary) !important;
            font-weight: 600 !important;
        }
        .ts-dropdown .selected {
            background-color: var(--pos-primary) !important;
            color: #ffffff !important;
        }
        .ts-wrapper .ts-control input {
            font-size: 13.5px !important;
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
    <div class="brand"><img src="{{ asset('images/hassan-logo-icon.png') }}" alt="Logo" style="width:26px; height:26px; object-fit:contain;"> Hassan Corporation</div>

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
<!-- TomSelect JS -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
    window.initTomSelect = function(elementOrSelector, options = {}) {
        const el = typeof elementOrSelector === 'string' ? document.querySelector(elementOrSelector) : elementOrSelector;
        if (!el) return null;
        if (el.tomselect) return el.tomselect;

        const defaultOptions = {
            create: false,
            allowEmptyOption: true,
            plugins: ['dropdown_input', 'clear_button'],
            maxOptions: 500,
            placeholder: el.getAttribute('placeholder') || el.options[0]?.text || '-- Search and select --',
            render: {
                no_results: function(data, escape) {
                    return '<div class="no-results p-2 text-muted small text-center"><i class="bi bi-search me-1"></i>No matching results found</div>';
                }
            },
            ...options
        };

        return new TomSelect(el, defaultOptions);
    };

    // Auto-initialize all searchable-select and select-search elements
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('select.searchable-select, select.select-search').forEach(function(selectEl) {
            if (!selectEl.tomselect) {
                window.initTomSelect(selectEl);
            }
        });
    });
</script>

@stack('scripts')
</body>
</html>

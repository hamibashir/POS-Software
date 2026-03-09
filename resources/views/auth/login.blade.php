<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — HardwarePro POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #f6f7f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        /* ── Card ───────────────────────────────────────── */
        .login-card {
            width: 100%;
            max-width: 480px;
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 8px 32px rgba(0,0,0,.12);
            overflow: hidden;
        }

        /* ── Header photo banner ────────────────────────── */
        .login-header {
            position: relative;
            height: 192px;
            background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAeQ_3vah-xjtxCJUyJYv-cc8X0gY8Pno-skh2tdsceopUuriEOdTISF0qGoON_DU5VVZBzx84au0d4JReEeMLsvBZZitKpDMkoHab-scGKQXK1ZD9b6o7k9Xc_qpEfk96xtGLXG7o2UMfMAmH95wX825yvHSr15g73bb555cI6ty_RjEU8p3Z29JIrPAwjoZHP4pESTu35mruJybItIjGUHkwTDYsKUmndPqBh6mxfP6iYHdVe9GbUr1ck_lwAp4tTLFZY4kN81NJx');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .login-header .overlay {
            position: absolute;
            inset: 0;
            background: rgba(30, 109, 138, 0.40);
            backdrop-filter: blur(2px);
        }
        .login-header .header-inner {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .login-header .logo-circle {
            width: 64px; height: 64px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,.20);
        }
        .login-header .logo-circle i {
            font-size: 28px;
            color: #1e6d8a;
        }
        .login-header h1 {
            color: #fff;
            font-size: 22px;
            font-weight: 700;
            text-shadow: 0 1px 4px rgba(0,0,0,.25);
        }

        /* ── Body ───────────────────────────────────────── */
        .login-body { padding: 32px 32px 24px; }

        .login-body .welcome-title {
            font-size: 22px; font-weight: 700;
            color: #0f172a; text-align: center; margin-bottom: 6px;
        }
        .login-body .welcome-sub {
            font-size: 14px; color: #64748b;
            text-align: center; margin-bottom: 28px;
        }

        /* ── Inputs ─────────────────────────────────────── */
        .field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 20px; }
        .field-label-row {
            display: flex; justify-content: space-between; align-items: center;
        }
        .field label {
            font-size: 14px; font-weight: 500; color: #1e293b;
        }
        .forgot-link {
            font-size: 12px; font-weight: 500;
            color: #1e6d8a; text-decoration: none;
        }
        .forgot-link:hover { text-decoration: underline; }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrap .icon-left {
            position: absolute; left: 14px;
            color: #94a3b8; font-size: 18px;
            pointer-events: none;
        }
        .input-wrap .icon-right {
            position: absolute; right: 14px;
            color: #94a3b8; font-size: 18px;
            cursor: pointer; background: none; border: none; padding: 0;
            display: flex; align-items: center;
        }
        .input-wrap .icon-right:hover { color: #475569; }

        .pos-field {
            width: 100%;
            height: 48px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            color: #0f172a;
            font-size: 14px;
            padding: 0 44px 0 44px;
            outline: none;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }
        .pos-field::placeholder { color: #94a3b8; }
        .pos-field:focus {
            border-color: #1e6d8a;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(30,109,138,.12);
        }
        .pos-field.is-invalid { border-color: #ef4444; }

        /* ── Error alert ────────────────────────────────── */
        .alert-err {
            display: flex; align-items: center; gap: 8px;
            background: #fee2e2; color: #b91c1c;
            border-radius: 8px; padding: 10px 14px;
            font-size: 13px; margin-bottom: 20px;
        }

        /* ── Submit button ──────────────────────────────── */
        .btn-signin {
            width: 100%;
            height: 48px;
            background: #1e6d8a;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
            transition: background .15s, opacity .15s, transform .1s;
            box-shadow: 0 1px 3px rgba(0,0,0,.12);
        }
        .btn-signin:hover  { background: #165a73; }
        .btn-signin:active { transform: scale(.98); }
        .btn-signin:disabled { opacity: .7; cursor: not-allowed; }

        /* ── Footer ─────────────────────────────────────── */
        .login-footer {
            border-top: 1px solid #f1f5f9;
            padding: 20px 32px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.6;
        }
        .login-footer a { color: #1e6d8a; font-weight: 500; text-decoration: none; }
        .login-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="login-card">

    {{-- ── Header ─────────────────────────────────── --}}
    <div class="login-header">
        <div class="overlay"></div>
        <div class="header-inner">
            <div class="logo-circle">
                <i class="bi bi-tools"></i>
            </div>
            <h1>HardwarePro POS</h1>
        </div>
    </div>

    {{-- ── Body ────────────────────────────────────── --}}
    <div class="login-body">
        <p class="welcome-title">Welcome Back</p>
        <p class="welcome-sub">Sign in to access the point of sale system</p>

        @if ($errors->any())
        <div class="alert-err">
            <i class="bi bi-exclamation-circle-fill"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" id="loginForm">
            @csrf

            {{-- Email --}}
            <div class="field">
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <i class="bi bi-envelope icon-left"></i>
                    <input type="email" id="email" name="email" class="pos-field @error('email') is-invalid @enderror"
                           placeholder="employee@hardwarepro.com"
                           value="{{ old('email') }}"
                           required autofocus autocomplete="email">
                </div>
            </div>

            {{-- Password --}}
            <div class="field">
                <div class="field-label-row">
                    <label for="password">Password</label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>
                <div class="input-wrap">
                    <i class="bi bi-lock icon-left"></i>
                    <input type="password" id="password" name="password"
                           class="pos-field @error('password') is-invalid @enderror"
                           placeholder="Enter your password"
                           required autocomplete="current-password">
                    <button type="button" class="icon-right" id="togglePassword" title="Show/hide password">
                        <i class="bi bi-eye-slash" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-signin" id="signinBtn">
                <span id="btnText">Sign In</span>
                <i class="bi bi-box-arrow-in-right" id="btnIcon"></i>
            </button>
        </form>
    </div>

    {{-- ── Footer ──────────────────────────────────── --}}
    <div class="login-footer">
        Need help accessing your account?<br>
        Contact the <a href="mailto:admin@hardwarepro.com">IT Support Desk</a>
    </div>

</div>

<script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function () {
        const pwd  = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.className = 'bi bi-eye';
        } else {
            pwd.type = 'password';
            icon.className = 'bi bi-eye-slash';
        }
    });

    // Disable button on submit
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn  = document.getElementById('signinBtn');
        btn.disabled = true;
        document.getElementById('btnText').textContent = 'Signing in…';
        document.getElementById('btnIcon').className   = 'bi bi-arrow-repeat';
    });
</script>

</body>
</html>

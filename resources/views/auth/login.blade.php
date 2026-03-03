@extends('layouts.guest')

@section('title', 'Sign In — HardwarePro POS')

@push('styles')
<style>
    body {
        background-color: #f0f2f5;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-card {
        width: 100%;
        max-width: 460px;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.12);
        background: #fff;
    }

    .login-header {
        background: linear-gradient(135deg, #1a6b7c 0%, #0d4a57 100%);
        padding: 40px 20px 30px;
        text-align: center;
        position: relative;
    }

    .login-header img.hero-bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.25;
    }

    .login-header .logo-circle {
        width: 64px;
        height: 64px;
        background: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        position: relative;
        z-index: 1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .login-header .logo-circle i {
        font-size: 28px;
        color: #1a6b7c;
    }

    .login-header h1 {
        color: #fff;
        font-size: 22px;
        font-weight: 700;
        margin: 0;
        position: relative;
        z-index: 1;
    }

    .login-body {
        padding: 36px 36px 28px;
    }

    .login-body h2 {
        font-size: 22px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 6px;
    }

    .login-body p.subtitle {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 28px;
    }

    .form-label {
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }

    .input-group-text {
        background: #f9fafb;
        border-right: none;
        color: #9ca3af;
    }

    .form-control {
        border-left: none;
        font-size: 14px;
        padding: 11px 14px;
        background: #f9fafb;
        border-color: #e5e7eb;
        color: #111827;
    }

    .form-control:focus,
    .input-group:focus-within .input-group-text,
    .form-control:focus {
        box-shadow: none;
        border-color: #1a6b7c;
        background: #fff;
    }

    .input-group:focus-within .input-group-text {
        border-color: #1a6b7c;
        background: #fff;
    }

    .btn-toggle-password {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-left: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 0 14px;
    }

    .btn-toggle-password:hover {
        color: #1a6b7c;
    }

    .btn-signin {
        background: linear-gradient(135deg, #1a6b7c, #0d4a57);
        color: #fff;
        border: none;
        width: 100%;
        padding: 13px;
        font-size: 15px;
        font-weight: 600;
        border-radius: 8px;
        letter-spacing: 0.3px;
        transition: opacity 0.2s;
        margin-top: 8px;
    }

    .btn-signin:hover {
        opacity: 0.92;
        color: #fff;
    }

    .forgot-link {
        font-size: 13px;
        color: #1a6b7c;
        text-decoration: none;
        font-weight: 500;
    }

    .forgot-link:hover { text-decoration: underline; }

    .login-footer {
        border-top: 1px solid #f3f4f6;
        padding: 16px 36px;
        text-align: center;
        font-size: 13px;
        color: #9ca3af;
    }

    .login-footer a {
        color: #1a6b7c;
        font-weight: 500;
        text-decoration: none;
    }

    .alert-danger {
        font-size: 13px;
        border-radius: 8px;
        padding: 10px 14px;
    }
</style>
@endpush

@section('content')
<div class="login-card">

    {{-- Header --}}
    <div class="login-header">
        <div class="logo-circle">
            <i class="bi bi-tools"></i>
        </div>
        <h1>HardwarePro POS</h1>
    </div>

    {{-- Body --}}
    <div class="login-body">
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to access the point of sale system</p>

        {{-- Error Messages --}}
        @if ($errors->any())
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        {{-- Login Form --}}
        <form method="POST" action="{{ route('login.post') }}" id="loginForm">
            @csrf

            {{-- Email --}}
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input
                        type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="employee@hardwarepro.com"
                        required
                        autofocus
                        autocomplete="email"
                    >
                </div>
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label mb-0">Password</label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input
                        type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="btn-toggle-password" id="togglePassword" title="Toggle password">
                        <i class="bi bi-eye-slash" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            {{-- Remember Me --}}
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember" style="font-size:13px; color:#6b7280;">
                    Keep me signed in
                </label>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn btn-signin" id="signinBtn">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>
    </div>

    {{-- Footer --}}
    <div class="login-footer">
        Need help accessing your account?
        Contact the <a href="mailto:admin@hardwarepro.com">IT Support Desk</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function () {
        const pwd = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        } else {
            pwd.type = 'password';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        }
    });

    // Disable button on submit to prevent double-click
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('signinBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in...';
    });
</script>
@endpush

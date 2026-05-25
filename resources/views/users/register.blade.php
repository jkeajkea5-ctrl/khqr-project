@extends('layouts.app')

@section('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
    :root {
        --brand-ink: #150734;
        --brand-primary: #28559A;
        --brand-accent: #3778C2;
        --brand-bg: #f3f6fb;
    }

    .user-auth-page {
    font-family: 'Poppins', sans-serif;
        min-height: 100vh;
        background: radial-gradient(800px 400px at 10% 10%, rgba(55,120,194,0.2), transparent 60%),
                    radial-gradient(700px 380px at 90% 20%, rgba(40,85,154,0.18), transparent 55%),
                    var(--brand-bg);
        display: flex;
        align-items: center;
        padding: 16px;
    }

    .user-auth-card {
        background: #ffffff;
        border-radius: 5px;
        border: 1px solid rgba(21, 7, 52, 0.08);
        box-shadow: 0 20px 40px rgba(21, 7, 52, 0.08);
        width: 100%;
        max-width: 440px;
        margin: auto;
        animation: floatIn 0.6s ease;
    }

    @keyframes floatIn {
        from { opacity: 0; transform: translateY(18px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .auth-title {
        font-size: 1.6rem;
        color: var(--brand-ink);
        letter-spacing: -0.5px;
    }

    .form-label {
        font-weight: 600;
        color: #475569;
        margin-left: 4px;
        font-size: 0.85rem;
    }

    .input-wrap {
        position: relative;
    }

    .input-wrap i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .form-control {
        height: 54px;
        border-radius: 5px;
        border: 1.5px solid #e2e8f0;
        padding: 0 18px 0 46px;
        transition: all 0.2s ease;
        background-color: #f8fafc;
    }

    .form-control:focus {
        border-color: var(--brand-primary);
        box-shadow: 0 0 0 4px rgba(40, 85, 154, 0.12);
        background-color: #fff;
    }

    .btn-register {
        height: 54px;
        border-radius: 5px;
        background: var(--brand-primary);
        color: #ffffff;
        font-weight: 600;
        font-size: 1rem;
        border: none;
        box-shadow: 0 10px 18px rgba(40, 85, 154, 0.2);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .btn-register:active {
        transform: scale(0.98);
    }

    .login-link {
        color: var(--brand-primary);
        text-decoration: none;
        font-weight: 600;
    }

    @media (max-width: 576px) {
        .user-auth-page {
    font-family: 'Poppins', sans-serif;
            align-items: flex-end;
            padding: 0;
            background: #fff;
        }
        .user-auth-card {
            border-radius: 5px 5px 0 0;
            box-shadow: none;
            border: none;
            padding-bottom: 50px !important;
        }
    }
</style>
@endsection

@section('content')
<div class="user-auth-page">
    <div class="user-auth-card p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="mb-3" style="font-size: 2.2rem; color: var(--brand-accent);">
                <i class="bi bi-person-plus"></i>
            </div>
            <h3 class="auth-title fw-bold mb-1">Create account</h3>
            <p class="text-muted">Join TEAM10 Clothing Store in seconds</p>
        </div>

        <form method="POST" action="{{ route('user.register.submit') }}">
            @csrf

            @if($errors->any())
                <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <div class="input-wrap">
                    <i class="bi bi-person"></i>
                    <input
                        name="name"
                        type="text"
                        class="form-control"
                        placeholder="Your name"
                        autocomplete="name"
                        value="{{ old('name') }}"
                        required
                    >
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <div class="input-wrap">
                    <i class="bi bi-telephone"></i>
                    <input
                        name="phone"
                        type="tel"
                        class="form-control"
                        placeholder="012 345 678"
                        inputmode="tel"
                        autocomplete="tel"
                        value="{{ old('phone') }}"
                        required
                    >
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Email Address (optional)</label>
                <div class="input-wrap">
                    <i class="bi bi-envelope"></i>
                    <input
                        name="email"
                        type="email"
                        class="form-control"
                        placeholder="you@example.com"
                        autocomplete="email"
                        value="{{ old('email') }}"
                    >
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-wrap password-field">
                    <i class="bi bi-lock"></i>
                    <input
                        name="password"
                        type="password"
                        class="form-control"
                        placeholder="Create a password"
                        autocomplete="new-password"
                        required
                    >
                    <button type="button" class="password-toggle" data-password-toggle aria-label="Show password" aria-pressed="false">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Confirm Password</label>
                <div class="input-wrap password-field">
                    <i class="bi bi-shield-lock"></i>
                    <input
                        name="password_confirmation"
                        type="password"
                        class="form-control"
                        placeholder="Repeat your password"
                        autocomplete="new-password"
                        required
                    >
                    <button type="button" class="password-toggle" data-password-toggle aria-label="Show password" aria-pressed="false">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-register w-100">
                <i class="bi bi-check-circle me-1"></i> Register
            </button>
        </form>

        <div class="text-center mt-4">
            <p class="text-muted">
                Already have an account? 
                <a href="{{ route('user.login') }}" class="login-link">Sign In</a>
            </p>
        </div>
    </div>
</div>
@endsection


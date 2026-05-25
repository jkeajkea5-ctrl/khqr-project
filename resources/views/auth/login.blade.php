@extends('layouts.app')

@section('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
:root {
    --admin-blue-1: #28559a;
    --admin-blue-2: #1f3e77;
    --admin-blue-3: #4f7fbb;
    --admin-blue-4: #dce8f8;
    --ink: #0f172a;
    --muted: #64748b;
}

.login-page {
    font-family: 'Poppins', sans-serif;
    min-height: 100vh;
    background:
        radial-gradient(circle at top left, rgba(79, 127, 187, 0.2), transparent 42%),
        radial-gradient(circle at bottom right, rgba(40, 85, 154, 0.16), transparent 38%),
        #eef4fb;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.login-card {
    width: 100%;
    max-width: 960px;
    background: #ffffff;
    border-radius: 5px;
    box-shadow: 0 24px 60px rgba(31, 62, 119, 0.14);
    overflow: hidden;
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
}

.login-left {
    padding: 48px 46px;
}

.login-right {
    background: linear-gradient(160deg, var(--admin-blue-3), var(--admin-blue-2));
    color: #ffffff;
    padding: 48px 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.login-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 6px;
}

.login-subtitle {
    color: var(--muted);
    margin-bottom: 28px;
}

.form-label {
    font-weight: 600;
    color: #334155;
    font-size: 0.9rem;
    margin-left: 4px;
}

.input-wrap {
    position: relative;
}

.input-wrap i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--admin-blue-1);
    font-size: 1.1rem;
}

.form-control {
    height: 56px;
    border-radius: 5px;
    border: 1.5px solid #d3deee;
    padding: 0 18px 0 48px;
    background: #f7fafe;
    transition: all 0.2s ease;
}

.form-control:focus {
    border-color: var(--admin-blue-1);
    box-shadow: 0 0 0 4px rgba(40, 85, 154, 0.12);
    background: #fff;
}

.btn-login {
    height: 56px;
    border-radius: 5px;
    background: linear-gradient(135deg, var(--admin-blue-1), var(--admin-blue-2));
    color: #ffffff;
    font-weight: 600;
    border: none;
    width: 100%;
    box-shadow: 0 12px 24px rgba(40, 85, 154, 0.24);
    transition: transform 0.2s ease;
}

.btn-login:active { transform: scale(0.98); }

.login-alert {
    margin-bottom: 18px;
    border: none;
    background: #eff6ff;
    color: #1d4ed8;
}

.login-alert-error {
    background: #fef2f2;
    color: #b91c1c;
}

.field-error {
    margin-top: 8px;
    font-size: 0.88rem;
    color: #b91c1c;
}

.right-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.right-text {
    font-size: 0.95rem;
    opacity: 0.9;
}

@media (max-width: 900px) {
    .login-card {
        grid-template-columns: 1fr;
    }
    .login-right {
        order: -1;
        padding: 32px 24px;
    }
}

@media (max-width: 576px) {
    .login-left {
        padding: 32px 22px;
    }
}
</style>
@endsection

@section('content')
<div class="login-page">
    <div class="login-card">
        <div class="login-left">
            <div class="login-title">Login Form</div>
            <div class="login-subtitle">Sign in to your admin account</div>

            @if(session('error'))
                <div class="alert login-alert login-alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert login-alert login-alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="input-wrap">
                        <i class="bi bi-person"></i>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="example@gmail.com" value="{{ old('email') }}" required>
                    </div>
                    @error('email')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-wrap password-field">
                        <i class="bi bi-lock"></i>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required>
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Show password" aria-pressed="false">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn-login" type="submit">Sign In</button>
            </form>
        </div>

        <div class="login-right">
            <div>
                <div class="right-title">Welcome Back!</div>
                <div class="right-text">Manage products, orders, and slides from your TEAM10 dashboard.</div>
            </div>
        </div>
    </div>
</div>
@endsection

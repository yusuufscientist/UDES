@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-5 col-lg-4">
            <div class="card animate__animated animate__fadeInUp" style="border-radius: var(--radius-xl); border: 1px solid var(--border);">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3" style="width: 80px; height: 80px; margin: 0 auto; background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-sun-fill" style="font-size: 2.5rem; color: var(--solar-amber);"></i>
                        </div>
                        <h2 class="mt-3 fw-bold" style="color: var(--solar-amber); font-size: 1.75rem; font-weight: 800;">Welcome Back</h2>
                        <p style="color: var(--text-muted);">Sign in to your account</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: var(--surface); border: 1.5px solid var(--border); border-right: none; border-radius: var(--radius-md) 0 0 var(--radius-md); color: var(--text-dim);">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}" 
                                       placeholder="you@example.com" required autofocus
                                       style="border-left: none; border-radius: 0 var(--radius-md) var(--radius-md) 0;">
                            </div>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: var(--surface); border: 1.5px solid var(--border); border-right: none; border-radius: var(--radius-md) 0 0 var(--radius-md); color: var(--text-dim);">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password" 
                                       placeholder="Enter your password" required
                                       style="border-left: none; border-radius: 0 var(--radius-md) var(--radius-md) 0;">
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" 
                                   style="accent-color: var(--solar-amber);">
                            <label class="form-check-label" style="color: var(--text-muted);" for="remember">Remember me</label>
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                            </button>
                        </div>
                    </form>

                    <div class="text-center">
                        <p class="mb-0" style="color: var(--text-muted);">Don't have an account? 
                            <a href="{{ route('register') }}" class="fw-bold text-decoration-none" style="color: var(--solar-amber);">
                                Create Account
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center py-5">
        <div class="col-md-6 col-lg-5">
            <div class="card animate__animated animate__fadeInUp" style="border-radius: var(--radius-xl); border: 1px solid var(--border);">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3" style="width: 80px; height: 80px; margin: 0 auto; background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-sun-fill" style="font-size: 2.5rem; color: var(--solar-amber);"></i>
                        </div>
                        <h2 class="mt-3 fw-bold" style="color: var(--solar-amber); font-size: 1.75rem; font-weight: 800;">Create Account</h2>
                        <p style="color: var(--text-muted);">Join SolarSmart today</p>
                    </div>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: var(--surface); border: 1.5px solid var(--border); border-right: none; border-radius: var(--radius-md) 0 0 var(--radius-md); color: var(--text-dim);">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="Enter your full name" required autofocus
                                       style="border-left: none; border-radius: 0 var(--radius-md) var(--radius-md) 0;">
                            </div>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: var(--surface); border: 1.5px solid var(--border); border-right: none; border-radius: var(--radius-md) 0 0 var(--radius-md); color: var(--text-dim);">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}" 
                                       placeholder="you@example.com" required
                                       style="border-left: none; border-radius: 0 var(--radius-md) var(--radius-md) 0;">
                            </div>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: var(--surface); border: 1.5px solid var(--border); border-right: none; border-radius: var(--radius-md) 0 0 var(--radius-md); color: var(--text-dim);">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" 
                                           placeholder="Password" required
                                           style="border-left: none; border-radius: 0 var(--radius-md) var(--radius-md) 0;">
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: var(--surface); border: 1.5px solid var(--border); border-right: none; border-radius: var(--radius-md) 0 0 var(--radius-md); color: var(--text-dim);">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" class="form-control"
                                           id="password_confirmation" name="password_confirmation" 
                                           placeholder="Confirm" required
                                           style="border-left: none; border-radius: 0 var(--radius-md) var(--radius-md) 0;">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number <span style="color: var(--text-dim);">(Optional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: var(--surface); border: 1.5px solid var(--border); border-right: none; border-radius: var(--radius-md) 0 0 var(--radius-md); color: var(--text-dim);">
                                    <i class="bi bi-phone"></i>
                                </span>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone') }}"
                                       placeholder="Enter phone number"
                                       style="border-left: none; border-radius: 0 var(--radius-md) var(--radius-md) 0;">
                            </div>
                            @error('phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="address" class="form-label">Address <span style="color: var(--text-dim);">(Optional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: var(--surface); border: 1.5px solid var(--border); border-right: none; border-radius: var(--radius-md) 0 0 var(--radius-md); color: var(--text-dim); align-items: flex-start; padding-top: 8px;">
                                    <i class="bi bi-geo-alt"></i>
                                </span>
                                <textarea class="form-control @error('address') is-invalid @enderror"
                                          id="address" name="address" rows="2"
                                          placeholder="Enter your address"
                                          style="border-left: none; border-radius: 0 var(--radius-md) var(--radius-md) 0;">{{ old('address') }}</textarea>
                            </div>
                            @error('address')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-person-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </form>

                    <div class="text-center">
                        <p class="mb-0" style="color: var(--text-muted);">Already have an account? 
                            <a href="{{ route('login') }}" class="fw-bold text-decoration-none" style="color: var(--solar-amber);">
                                Sign In
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

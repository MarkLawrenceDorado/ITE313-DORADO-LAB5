@extends('layouts.guest')

@section('title', 'Login')
@section('subtitle', 'Sign in to your dashboard')

@section('content')

    {{-- Session status (e.g. after password reset) --}}
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" id="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required autofocus autocomplete="username">
            </div>
        </div>

        {{-- Password --}}
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="password"
                       class="form-control @error('password') is-invalid @enderror"
                       required autocomplete="current-password">
            </div>
        </div>

        {{-- Remember Me --}}
        <div class="mb-3 form-check">
            <input type="checkbox" name="remember" id="remember" class="form-check-input">
            <label class="form-check-label" for="remember">Remember Me</label>
        </div>

        {{-- Login Button --}}
        <button type="submit" class="btn btn-primary w-100 py-2">
            Login
        </button>

        @if (Route::has('password.request'))
            <div class="text-center mt-3">
                <a class="text-primary-custom small" href="{{ route('password.request') }}">
                    Forgot your password?
                </a>
            </div>
        @endif
    </form>

@endsection
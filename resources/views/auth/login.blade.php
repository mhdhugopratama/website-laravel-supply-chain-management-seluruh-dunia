@extends('layouts.app')
@section('title', 'Login | GoSupply')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height:70vh;padding:2rem 1rem">
    <div style="width:100%;max-width:440px">
        <div class="nb-card">
                <div class="nb-card-header text-center pb-2">
                    Sign In to GoSupply
                </div>
            <div class="nb-card-body">
                @if($errors->any())
                    <div class="nb-alert nb-alert-danger">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="/login">
                    @csrf
                    <div class="mb-3">
                        <label style="font-weight:700;font-size:0.85rem">Email Address</label>
                        <input type="email" name="email" class="nb-input mt-1" value="{{ old('email') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label style="font-weight:700;font-size:0.85rem">Password</label>
                        <input type="password" name="password" class="nb-input mt-1" required>
                    </div>
                    <div class="mb-3 d-flex align-items-center gap-2">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember" style="font-size:0.85rem;cursor:pointer">Remember me</label>
                    </div>
                    <button type="submit" class="nb-btn nb-btn-primary w-100" style="justify-content:center">
                        Login
                    </button>
                </form>
                <hr style="border:1px solid rgba(255,255,255,0.18);margin:1.2rem 0">
                <div class="text-center" style="font-size:0.85rem">
                    No account? <a href="{{ route('register') }}" style="font-weight:700">Register here</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

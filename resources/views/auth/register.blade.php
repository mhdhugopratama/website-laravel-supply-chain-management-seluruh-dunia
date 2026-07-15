@extends('layouts.app')
@section('title', 'Register — SupplyChainIQ')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height:70vh;padding:2rem 1rem">
    <div style="width:100%;max-width:440px">
        <div class="nb-card">
            <div class="nb-card-header text-center" style="font-size:1.2rem;padding:1.2rem">
                <i class="bi bi-person-plus-fill"></i> Create Account
            </div>
            <div class="nb-card-body">
                @if($errors->any())
                    <div class="nb-alert nb-alert-danger">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="/register">
                    @csrf
                    <div class="mb-3">
                        <label style="font-weight:700;font-size:0.85rem">Full Name</label>
                        <input type="text" name="name" class="nb-input mt-1" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label style="font-weight:700;font-size:0.85rem">Email Address</label>
                        <input type="email" name="email" class="nb-input mt-1" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label style="font-weight:700;font-size:0.85rem">Password</label>
                        <input type="password" name="password" class="nb-input mt-1" required>
                    </div>
                    <div class="mb-3">
                        <label style="font-weight:700;font-size:0.85rem">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="nb-input mt-1" required>
                    </div>
                    <button type="submit" class="nb-btn nb-btn-primary w-100" style="justify-content:center">
                        <i class="bi bi-person-check"></i> Create Account
                    </button>
                </form>
                <hr style="border:1px solid rgba(255,255,255,0.18);margin:1.2rem 0">
                <div class="text-center" style="font-size:0.85rem">
                    Already have an account? <a href="{{ route('login') }}" style="font-weight:700">Login here</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

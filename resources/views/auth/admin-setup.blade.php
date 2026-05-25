@extends('layouts.app')

@section('content')
<div class="container py-5" style="max-width: 560px;">
    <div class="card-khqr p-4 p-md-5">
        <div class="mb-4 text-center">
            <h2 class="section-title mb-2">Create Admin Account</h2>
            <p class="section-subtitle mb-0">Set up the first admin account for this deployment.</p>
        </div>

        <form method="POST" action="{{ route('admin.setup.store') }}" class="d-grid gap-3">
            @csrf

            <div>
                <label for="name" class="form-label">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" class="form-control rounded-3" required>
                @error('name')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="email" class="form-label">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control rounded-3" required>
                @error('email')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="password" class="form-label">Password</label>
                <input id="password" name="password" type="password" class="form-control rounded-3" required>
                @error('password')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control rounded-3" required>
            </div>

            <button type="submit" class="btn btn-khqr w-100">Create Admin</button>
        </form>
    </div>
</div>
@endsection

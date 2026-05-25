@extends('admin.layouts.app')

@section('admin_content')
<div class="admin-card overflow-hidden">
    <div class="p-4 border-bottom">
        <div class="text-uppercase small admin-muted">Staff Access</div>
        <h2 class="fw-semibold mb-0">Edit Staff Account</h2>
    </div>

    @if($errors->any())
        <div class="alert alert-danger rounded-0 border-0 mb-0">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.admins.update', $admin) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="p-4">
            <div class="row g-3">
                <div class="col-12">
                    <label for="photo" class="form-label fw-semibold">Picture</label>
                    <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                        @if($admin->photo)
                            <img src="{{ $admin->photo }}" alt="{{ $admin->name }}" style="width:88px;height:88px;object-fit:cover;border-radius:50%;border:2px solid rgba(15, 23, 42, 0.08);">
                        @else
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-semibold" style="width:88px;height:88px;background:linear-gradient(135deg, #3778C2, #28559A);font-size:1.5rem;">
                                {{ $admin->initials() }}
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <input type="file" class="form-control form-control-lg rounded-4" id="photo" name="photo" accept=".jpg,.jpeg,.png,.webp">
                            <div class="form-text">Upload a new picture to replace the current one.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="name" class="form-label fw-semibold">Name</label>
                    <input type="text" class="form-control form-control-lg rounded-4" id="name" name="name" value="{{ old('name', $admin->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control form-control-lg rounded-4" id="email" name="email" value="{{ old('email', $admin->email) }}" required>
                </div>
                <div class="col-md-6">
                    <label for="role" class="form-label fw-semibold">Role</label>
                    <select class="form-select form-select-lg rounded-4" id="role" name="role" required>
                        @foreach(\App\Models\Admin::roleOptions() as $role => $label)
                            <option value="{{ $role }}" @selected(old('role', $admin->role) === $role)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label fw-semibold">New Password</label>
                    <input type="password" class="form-control form-control-lg rounded-4" id="password" name="password">
                    <div class="form-text">Leave blank to keep the current password.</div>
                </div>
                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label fw-semibold">Confirm New Password</label>
                    <input type="password" class="form-control form-control-lg rounded-4" id="password_confirmation" name="password_confirmation">
                </div>
            </div>
        </div>

        <div class="p-4 border-top d-flex gap-3">
            <a href="{{ route('admin.admins.index') }}" class="btn btn-outline-secondary rounded-3 w-50">Back</a>
            <button type="submit" class="btn btn-khqr w-50">Save Changes</button>
        </div>
    </form>
</div>
@endsection

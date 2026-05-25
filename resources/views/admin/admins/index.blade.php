@extends('admin.layouts.app')

@section('admin_content')
<div class="admin-page-header">
    <div>
        <div class="text-uppercase small admin-muted">Admin Access</div>
        <h2 class="fw-semibold">Manage Staff</h2>
    </div>
    <div class="admin-muted small">{{ $admins->total() }} staff account(s)</div>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-4 border-0 shadow-sm">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger rounded-4 border-0 shadow-sm">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    <div class="col-xl-5">
        <div class="admin-card p-4" id="create-admin">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="admin-muted small text-uppercase">Create</div>
                    <h5 class="fw-semibold mb-0">Add Staff Account</h5>
                </div>
                <span class="badge text-bg-primary rounded-pill px-3 py-2">Secure Login</span>
            </div>

            <div class="d-flex flex-wrap gap-2 mb-4">
                @foreach(\App\Models\Admin::roleOptions() as $role => $label)
                    <span class="badge text-bg-light border">{{ $label }}: {{ $roleCounts[$role] ?? 0 }}</span>
                @endforeach
            </div>

            <form method="POST" action="{{ route('admin.admins.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="photo" class="form-label fw-semibold">Picture</label>
                    <input type="file" class="form-control form-control-lg rounded-4" id="photo" name="photo" accept=".jpg,.jpeg,.png,.webp">
                    <div class="form-text">Optional profile picture for admin, stock controller, or employee.</div>
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Name</label>
                    <input type="text" class="form-control form-control-lg rounded-4" id="name" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control form-control-lg rounded-4" id="email" name="email" value="{{ old('email') }}" required>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label fw-semibold">Role</label>
                    <select class="form-select form-select-lg rounded-4" id="role" name="role" required>
                        @foreach(\App\Models\Admin::roleOptions() as $role => $label)
                            <option value="{{ $role }}" @selected(old('role', \App\Models\Admin::ROLE_EMPLOYEE) === $role)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <div class="position-relative password-field">
                        <input type="password" class="form-control form-control-lg rounded-4" id="password" name="password" required>
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Show password" aria-pressed="false">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                    <div class="position-relative password-field">
                        <input type="password" class="form-control form-control-lg rounded-4" id="password_confirmation" name="password_confirmation" required>
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Show password" aria-pressed="false">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="admin-quick-btn admin-quick-btn-add w-100">
                    <i class="bi bi-plus"></i>
                    Create Staff Account
                </button>
            </form>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="admin-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="admin-muted small text-uppercase">Directory</div>
                    <h5 class="fw-semibold mb-0">Staff Accounts</h5>
                </div>
                <span class="admin-muted small">Latest first</span>
            </div>

            <div class="table-responsive d-none d-md-block">
                <table class="table align-middle mb-0">
                    <thead class="admin-muted small text-uppercase">
                        <tr>
                            <th>#</th>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Email</th>
                            <th>Created</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td>{{ $admin->id }}</td>
                                <td>
                                    @if($admin->photo)
                                        <img src="{{ $admin->photo }}" alt="{{ $admin->name }}" class="admin-profile-photo" style="width:44px;height:44px;object-fit:cover;border:1px solid rgba(15, 23, 42, 0.08);">
                                    @else
                                        <div class="admin-profile-initials d-inline-flex align-items-center justify-content-center text-white fw-semibold" style="width:44px;height:44px;background:linear-gradient(135deg, #3778C2, #28559A);">
                                            {{ $admin->initials() }}
                                        </div>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $admin->name }}</td>
                                <td><span class="badge text-bg-light border">{{ $admin->roleLabel() }}</span></td>
                                <td>{{ $admin->email }}</td>
                                <td>{{ $admin->created_at->format('d M Y, h:i A') }}</td>
                                <td class="text-end">
                                    <div class="admin-actions justify-content-end">
                                        <a class="admin-quick-btn admin-quick-btn-edit" href="{{ route('admin.admins.edit', $admin) }}">
                                            <i class="bi bi-pencil"></i>
                                            Edit
                                        </a>
                                        @if(auth('admin')->id() !== $admin->id)
                                            <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}" onsubmit="return confirm('Delete this staff account?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="admin-quick-btn admin-quick-btn-delete" type="submit">
                                                    <i class="bi bi-trash"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center admin-muted py-4">No staff accounts yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-md-none">
                @forelse($admins as $admin)
                    <div class="admin-list-item">
                        <div class="d-flex align-items-center gap-3">
                            @if($admin->photo)
                                <img src="{{ $admin->photo }}" alt="{{ $admin->name }}" class="admin-profile-photo" style="width:52px;height:52px;object-fit:cover;border:1px solid rgba(15, 23, 42, 0.08);">
                            @else
                                <div class="admin-profile-initials d-inline-flex align-items-center justify-content-center text-white fw-semibold" style="width:52px;height:52px;background:linear-gradient(135deg, #3778C2, #28559A);">
                                    {{ $admin->initials() }}
                                </div>
                            @endif
                            <div>
                                <div class="fw-semibold">{{ $admin->name }}</div>
                                <div class="admin-muted small">{{ $admin->roleLabel() }}</div>
                                <div class="admin-muted small">{{ $admin->email }}</div>
                                <div class="admin-muted small">{{ $admin->created_at->format('d M Y, h:i A') }}</div>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-end gap-2">
                            <div class="badge text-bg-light border">#{{ $admin->id }}</div>
                            <a class="admin-quick-btn admin-quick-btn-edit" href="{{ route('admin.admins.edit', $admin) }}">
                                <i class="bi bi-pencil"></i>
                                Edit
                            </a>
                            @if(auth('admin')->id() !== $admin->id)
                                <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}" onsubmit="return confirm('Delete this staff account?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="admin-quick-btn admin-quick-btn-delete" type="submit">
                                        <i class="bi bi-trash"></i>
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="admin-muted">No staff accounts yet.</div>
                @endforelse
            </div>

            <div class="mt-3">{{ $admins->links() }}</div>
        </div>
    </div>
</div>
@endsection

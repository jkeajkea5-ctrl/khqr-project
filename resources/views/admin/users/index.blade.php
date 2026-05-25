@extends('admin.layouts.app')

@section('admin_content')
<div class="admin-page-header">
    <div>
        <div class="text-uppercase small admin-muted">Users</div>
        <h2 class="fw-semibold mb-0">Registered customers</h2>
    </div>
    <div class="admin-muted small">{{ $users->total() }} user account(s)</div>
</div>

<div class="admin-card p-0">
    <div class="card-body p-0">
        <div class="table-responsive admin-table-responsive d-none d-md-block">
            <table class="table align-middle mb-0 admin-table-compact">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Orders</th>
                        <th>Paid Total</th>
                        <th class="pe-4">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-4">{{ $user->id }}</td>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td>{{ $user->phone }}</td>
                            <td>{{ $user->email ?? '-' }}</td>
                            <td>{{ $user->orders_count }}</td>
                            <td class="text-success fw-semibold">${{ number_format((float) ($user->paid_total ?? 0), 2) }}</td>
                            <td class="pe-4">{{ $user->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">No users found yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-md-none p-3">
            @forelse($users as $user)
                <div class="admin-card p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div class="fw-semibold">{{ $user->name }}</div>
                            <div class="admin-muted small">{{ $user->phone }}</div>
                            <div class="admin-muted small">{{ $user->email ?? 'No email' }}</div>
                        </div>
                        <div class="badge text-bg-light border">#{{ $user->id }}</div>
                    </div>
                    <div class="admin-muted small mt-3">Orders: {{ $user->orders_count }}</div>
                    <div class="admin-muted small">Paid total: ${{ number_format((float) ($user->paid_total ?? 0), 2) }}</div>
                    <div class="admin-muted small">Joined: {{ $user->created_at->format('d M Y, h:i A') }}</div>
                </div>
            @empty
                <div class="text-center text-muted py-5">No users found yet.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="mt-3">{{ $users->links() }}</div>
@endsection

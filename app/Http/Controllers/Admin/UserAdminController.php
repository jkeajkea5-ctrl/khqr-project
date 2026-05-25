<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserAdminController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->withCount('orders')
            ->withSum(
                ['orders as paid_total' => fn ($query) => $query->where('status', 'PAID')],
                'amount'
            )
            ->latest()
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }
}

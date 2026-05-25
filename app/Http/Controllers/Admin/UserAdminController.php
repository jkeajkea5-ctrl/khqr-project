<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;

class UserAdminController extends Controller
{
    public function index()
    {
        $users = User::query()->latest()->paginate(15);
        $orders = Order::query()
            ->whereIn('user_id', $users->pluck('id')->all())
            ->get(['user_id', 'status', 'amount']);

        $stats = [];
        foreach ($orders as $order) {
            $key = (string) $order->user_id;
            $stats[$key]['orders_count'] = ($stats[$key]['orders_count'] ?? 0) + 1;

            if ($order->status === 'PAID') {
                $stats[$key]['paid_total'] = ($stats[$key]['paid_total'] ?? 0) + (float) $order->amount;
            }
        }

        $users->getCollection()->transform(function (User $user) use ($stats): User {
            $key = (string) $user->id;
            $user->setAttribute('orders_count', (int) ($stats[$key]['orders_count'] ?? 0));
            $user->setAttribute('paid_total', (float) ($stats[$key]['paid_total'] ?? 0));

            return $user;
        });

        return view('admin.users.index', compact('users'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class UserOrderController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        Order::expirePending(2000);
        $orders = Order::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('users.orders', compact('orders'));
    }
}

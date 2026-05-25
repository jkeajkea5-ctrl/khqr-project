<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        if (Auth::user()?->role !== 'user') {
            return redirect()->route('home');
        }

        return $next($request);
    }
}

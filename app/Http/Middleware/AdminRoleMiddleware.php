<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        if ($roles === [] || $admin->hasAnyRole(...$roles)) {
            return $next($request);
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('error', 'You do not have permission to access that page.');
    }
}

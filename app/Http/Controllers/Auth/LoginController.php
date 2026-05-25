<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            if (Auth::guard('admin')->attempt($credentials)) {
                $request->session()->regenerate();

                return redirect()->intended(route('admin.dashboard'));
            }
        } catch (RuntimeException $exception) {
            if (!str_contains($exception->getMessage(), 'Bcrypt algorithm')) {
                throw $exception;
            }
        }

        if ($this->attemptLegacyAdminLogin($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()
            ->withErrors(['email' => 'Invalid credentials'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function attemptLegacyAdminLogin(array $credentials): bool
    {
        $admin = Admin::where('email', $credentials['email'])->first();

        if (!$admin || !$this->matchesLegacyPassword((string) $credentials['password'], (string) $admin->getAuthPassword())) {
            return false;
        }

        $admin->password = $credentials['password'];
        $admin->save();

        Auth::guard('admin')->login($admin);

        return true;
    }

    private function matchesLegacyPassword(string $plainPassword, string $storedPassword): bool
    {
        if ($storedPassword === '') {
            return false;
        }

        if (hash_equals($storedPassword, $plainPassword)) {
            return true;
        }

        $passwordInfo = password_get_info($storedPassword);

        if (($passwordInfo['algoName'] ?? 'unknown') === 'unknown') {
            return false;
        }

        return password_verify($plainPassword, $storedPassword);
    }
}

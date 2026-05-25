<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    public function showLogin()
    {
        return view('users.login');
    }

    public function showRegister()
    {
        return view('users.register');
    }

    public function register(Request $request)
    {
        $normalizedPhone = $this->normalizePhone($request->input('phone'));
        $normalizedEmail = $this->normalizeEmail($request->input('email'));

        $request->merge([
            'phone' => $normalizedPhone,
            'email' => $normalizedEmail,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email',
            'phone' => 'required|string|min:8|max:30|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?: null,
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => 'required|string|max:255',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
        ]);

        $login = trim($validated['login']);
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL) !== false;
        $identifierField = $isEmail ? 'email' : 'phone';
        $identifierValue = $isEmail ? $this->normalizeEmail($login) : $this->normalizePhone($login);

        $credentials = [
            $identifierField => $identifierValue,
            'password' => $validated['password'],
            'role' => 'user',
        ];

        if (Auth::attempt($credentials, (bool) ($validated['remember'] ?? false))) {
            $request->session()->regenerate();
            return redirect()->route('home');
        }

        return back()
            ->withErrors(['login' => 'Invalid phone, email, or password.'])
            ->withInput($request->only('login', 'remember'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function normalizePhone(?string $phone): string
    {
        $phone = trim((string) $phone);

        if ($phone === '') {
            return '';
        }

        if (str_starts_with($phone, '+')) {
            return '+'.preg_replace('/\D+/', '', substr($phone, 1));
        }

        return preg_replace('/\D+/', '', $phone);
    }

    private function normalizeEmail(?string $email): ?string
    {
        $email = trim((string) $email);

        if ($email === '') {
            return null;
        }

        return mb_strtolower($email);
    }
}

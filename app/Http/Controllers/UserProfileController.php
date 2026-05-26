<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        Order::expirePending(2000);

        $user = Auth::user();
        $orders = Order::where('user_id', $user->getAuthIdentifier())
            ->orderByDesc('created_at')
            ->get();

        $stats = $this->buildStats($orders);
        $recentOrders = $orders->take(4);

        return view('users.profile', compact('user', 'stats', 'recentOrders'));
    }

    public function update(Request $request): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        $user = Auth::user();
        $normalizedPhone = $this->normalizePhone($request->input('phone'));
        $normalizedEmail = $this->normalizeEmail($request->input('email'));

        $request->merge([
            'phone' => $normalizedPhone,
            'email' => $normalizedEmail,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->getAuthIdentifier(), $user->getAuthIdentifierName()),
            ],
            'phone' => [
                'required',
                'string',
                'min:8',
                'max:30',
                Rule::unique('users', 'phone')->ignore($user->getAuthIdentifier(), $user->getAuthIdentifierName()),
            ],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'] ?: null;
        $user->phone = $validated['phone'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()
            ->route('user.profile')
            ->with('success', 'Your profile was updated successfully.');
    }

    private function buildStats(Collection $orders): array
    {
        $paidOrders = $orders->where('status', 'PAID');
        $pendingOrders = $orders->where('status', 'PENDING');
        $failedOrders = $orders->where('status', 'FAILED');

        return [
            'total_orders' => $orders->count(),
            'paid_orders' => $paidOrders->count(),
            'pending_orders' => $pendingOrders->count(),
            'failed_orders' => $failedOrders->count(),
            'paid_total' => (float) $paidOrders->sum(fn (Order $order): float => (float) $order->amount),
            'latest_paid_at' => optional($paidOrders->first())->paid_at,
        ];
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

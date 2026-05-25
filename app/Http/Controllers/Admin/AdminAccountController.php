<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Support\MediaStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminAccountController extends Controller
{
    public function index()
    {
        $admins = Admin::latest()->paginate(12);
        $roleCounts = collect(Admin::roleOptions())
            ->mapWithKeys(fn (string $label, string $role) => [
                $role => Admin::where('role', $role)->count(),
            ]);

        return view('admin.admins.index', compact('admins', 'roleCounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(array_keys(Admin::roleOptions()))],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data = $validated;

        if ($request->hasFile('photo')) {
            $data['photo'] = MediaStorage::store($request->file('photo'), 'admins');
        }

        Admin::create($data);

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Staff account created successfully.');
    }

    public function edit(Admin $admin)
    {
        return view('admin.admins.edit', compact('admin'));
    }

    public function update(Request $request, Admin $admin)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id)],
            'role' => ['required', Rule::in(array_keys(Admin::roleOptions()))],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if (
            $admin->isAdmin()
            && $validated['role'] !== Admin::ROLE_ADMIN
            && Admin::where('role', Admin::ROLE_ADMIN)->count() === 1
        ) {
            return back()
                ->withErrors(['role' => 'At least one account must keep the admin role.'])
                ->withInput();
        }

        if (
            Auth::guard('admin')->id() === $admin->id
            && $admin->isAdmin()
            && $validated['role'] !== Admin::ROLE_ADMIN
        ) {
            return back()
                ->withErrors(['role' => 'Use another admin account if you need to remove your own admin access.'])
                ->withInput();
        }

        $admin->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        if (!empty($validated['password'])) {
            $admin->password = $validated['password'];
        }

        if ($request->hasFile('photo')) {
            $this->deletePhotoFile($admin);
            $admin->photo = MediaStorage::store($request->file('photo'), 'admins');
        }

        $admin->save();

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Staff account updated successfully.');
    }

    public function destroy(Admin $admin)
    {
        if (Auth::guard('admin')->id() === $admin->id) {
            return redirect()
                ->route('admin.admins.index')
                ->with('error', 'You cannot delete the account you are currently signed in with.');
        }

        if (
            $admin->isAdmin()
            && Admin::where('role', Admin::ROLE_ADMIN)->count() === 1
        ) {
            return redirect()
                ->route('admin.admins.index')
                ->with('error', 'You cannot delete the last admin account.');
        }

        $this->deletePhotoFile($admin);
        $admin->delete();

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Staff account deleted successfully.');
    }

    private function deletePhotoFile(Admin $admin): void
    {
        $photoPath = $admin->photoStoragePath();

        if ($photoPath) {
            MediaStorage::delete($photoPath);
        }
    }
}

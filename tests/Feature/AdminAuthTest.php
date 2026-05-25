<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_sign_in_and_sign_out_cleanly(): void
    {
        Admin::create([
            'name' => 'Main Admin',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => Admin::ROLE_ADMIN,
        ]);

        $this->post(route('admin.login.submit'), [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated('admin');
        $this->get(route('admin.dashboard'))->assertOk();

        $this->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('admin');
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_login_upgrades_legacy_plain_text_passwords(): void
    {
        DB::table('admins')->insert([
            'name' => 'Legacy Admin',
            'email' => 'legacy@example.com',
            'password' => 'password123',
            'role' => Admin::ROLE_ADMIN,
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post(route('admin.login.submit'), [
            'email' => 'legacy@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated('admin');

        $admin = Admin::where('email', 'legacy@example.com')->firstOrFail();

        $this->assertNotSame('password123', $admin->getRawOriginal('password'));
        $this->assertTrue(Hash::check('password123', $admin->getAuthPassword()));
    }

    public function test_admin_login_page_displays_session_expired_message(): void
    {
        $response = $this->withSession([
            'error' => 'Your admin session expired. Please sign in again.',
        ])->get(route('admin.login'));

        $response->assertOk();
        $response->assertSeeText('Your admin session expired. Please sign in again.');
    }
}

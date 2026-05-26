<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $product = Product::create([
            'name' => 'Profile Jacket',
            'description' => 'Profile test item',
            'price' => 19.99,
        ]);

        $user = User::create([
            'name' => 'Profile User',
            'email' => 'profile@example.com',
            'phone' => '012345678',
            'password' => 'password123',
            'role' => 'user',
        ]);

        Order::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'product_name' => 'Jacket',
            'amount' => 19.99,
            'currency' => 'USD',
            'md5' => 'profile-order-1',
            'status' => 'PAID',
            'paid_at' => now(),
            'items' => [],
        ]);

        $this->actingAs($user);

        $this->get(route('user.profile'))
            ->assertOk()
            ->assertSeeText('Profile User')
            ->assertSeeText('My profile')
            ->assertSeeText('Recent activity');
    }

    public function test_user_can_update_profile_and_password(): void
    {
        $user = User::create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'phone' => '011111111',
            'password' => 'password123',
            'role' => 'user',
        ]);

        $this->actingAs($user);

        $this->put(route('user.profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '099888777',
            'password' => 'newsecret',
            'password_confirmation' => 'newsecret',
        ])->assertRedirect(route('user.profile'));

        $user->refresh();

        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertSame('099888777', $user->phone);
        $this->assertTrue(Hash::check('newsecret', $user->getAuthPassword()));
    }
}

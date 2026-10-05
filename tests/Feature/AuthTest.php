<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_register_page(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('ساخت حساب کاربری');
    }

    public function test_guest_can_register_successfully(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'کاربر تستی',
            'email' => 'user@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('account.dashboard'));

        $user = User::where('email', 'user@example.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->is_admin);
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_requires_strong_password(): void
    {
        $this->post(route('register.store'), [
            'name' => 'کاربر تستی',
            'email' => 'user@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post(route('register.store'), [
            'name' => 'کاربر تستی',
            'email' => 'taken@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ])->assertSessionHasErrors('email');
    }

    public function test_customer_can_login(): void
    {
        $user = User::factory()->create(['password' => 'secret1234']);

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'secret1234',
        ])->assertRedirect(route('account.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_is_redirected_to_admin_panel_on_login(): void
    {
        $admin = User::factory()->create([
            'password' => 'secret1234',
            'is_admin' => true,
        ]);

        $this->post(route('login.attempt'), [
            'email' => $admin->email,
            'password' => 'secret1234',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('shop.home'));

        $this->assertGuest();
    }

    public function test_account_pages_require_authentication(): void
    {
        $this->get(route('account.dashboard'))->assertRedirect(route('login'));
        $this->get(route('account.orders'))->assertRedirect(route('login'));
    }
}

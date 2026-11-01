<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * احراز هویت API (v2.12.0):
 * ثبت‌نام/ورود/خروج/پروفایل با توکن Sanctum
 */
class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------
    // ثبت‌نام
    // ---------------------------------------------------------------------

    public function test_guest_can_register_and_receive_token(): void
    {
        $response = $this->postJson(route('api.v1.auth.register'), [
            'name' => 'مریم رضایی',
            'email' => 'maryam@example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'مریم رضایی')
            ->assertJsonPath('data.email', 'maryam@example.test')
            ->assertJsonStructure(['message', 'data' => ['id', 'name', 'email', 'created_at'], 'token']);

        $this->assertDatabaseHas('users', ['email' => 'maryam@example.test']);
        $this->assertNotEmpty($response->json('token'));

        // توکن واقعاً کار می‌کند — همان کاربر با همان توکن
        $me = $this->withToken($response->json('token'))
            ->getJson(route('api.v1.auth.me'));

        $me->assertOk()->assertJsonPath('data.email', 'maryam@example.test');
    }

    public function test_register_validates_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.test']);

        $this->postJson(route('api.v1.auth.register'), [
            'name' => 'کاربر تکراری',
            'email' => 'taken@example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_register_validates_short_password(): void
    {
        $this->postJson(route('api.v1.auth.register'), [
            'name' => 'کاربر تست',
            'email' => 'short@example.test',
            'password' => '123',
            'password_confirmation' => '123',
        ])->assertUnprocessable()->assertJsonValidationErrors(['password']);
    }

    // ---------------------------------------------------------------------
    // ورود
    // ---------------------------------------------------------------------

    public function test_user_can_login_and_receive_token(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'secret123',
            'device_name' => 'iPhone 15',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonStructure(['message', 'data', 'token']);

        // نام دستگاه در رکورد توکن ثبت می‌شود
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'iPhone 15',
        ]);
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        $this->postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_login_error_does_not_reveal_which_field(): void
    {
        // ایمیل موجود اما رمز غلط — پیام باید همان پیام عمومی باشد
        $user = User::factory()->create(['password' => 'secret123']);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertSame(
            ['اطلاعات ورود نادرست است.'],
            $response->json('errors.email')
        );

        // ایمیل ناموجود — همان پیام، همان کلید
        $response2 = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'ghost@example.test',
            'password' => 'whatever',
        ]);

        $this->assertSame(
            ['اطلاعات ورود نادرست است.'],
            $response2->json('errors.email')
        );
    }

    // ---------------------------------------------------------------------
    // توکن و خروج
    // ---------------------------------------------------------------------

    public function test_me_requires_valid_token(): void
    {
        $this->getJson(route('api.v1.auth.me'))->assertUnauthorized();

        $this->withToken('1|invalid-token-value')
            ->getJson(route('api.v1.auth.me'))
            ->assertUnauthorized();
    }

    public function test_logout_revokes_only_current_token(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        // دو توکن واقعی از دو «دستگاه»
        $phone = $this->postJson(route('api.v1.auth.login'), [
            'email' => $user->email, 'password' => 'secret123', 'device_name' => 'phone',
        ])->json('token');

        $tablet = $this->postJson(route('api.v1.auth.login'), [
            'email' => $user->email, 'password' => 'secret123', 'device_name' => 'tablet',
        ])->json('token');

        // خروج از گوشی — فقط همان توکن باطل می‌شود
        $this->withToken($phone)
            ->postJson(route('api.v1.auth.logout'))
            ->assertOk();

        // guard بین دو درخواستِ همان تست memoize می‌شود؛ ریست می‌کنیم
        // تا دقیقاً مثل یک درخواست تازهٔ اپ موبایل باشیم
        $this->app->make('auth')->forgetGuards();

        $this->withToken($phone)
            ->getJson(route('api.v1.auth.me'))
            ->assertUnauthorized();

        // توکن تبلت زنده است
        $this->app->make('auth')->forgetGuards();

        $this->withToken($tablet)
            ->getJson(route('api.v1.auth.me'))
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_me_returns_profile_without_sensitive_fields(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson(route('api.v1.auth.me'));

        $response->assertOk()
            ->assertJsonPath('data.name', $user->name)
            ->assertJsonMissing(['password'])
            ->assertJsonMissing(['remember_token']);
    }
}

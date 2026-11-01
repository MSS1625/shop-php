<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

/**
 * مدیریت توکن‌های API از پنل (v2.14.0):
 * لیست/تب‌ها، صدور با انقضا، ابطال، و رفتار واقعی Sanctum
 */
class TokenManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    // ---------------------------------------------------------------------
    // سطح دسترسی
    // ---------------------------------------------------------------------

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.tokens.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_is_forbidden_on_all_three_actions(): void
    {
        $user = User::factory()->create(); // مشتری معمولی
        $token = $user->createToken('دستگاه مشتری'); // id واقعی تا binding 404 ندهد

        $this->actingAs($user)->get(route('admin.tokens.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.tokens.store'), [])->assertForbidden();
        $this->actingAs($user)
            ->delete(route('admin.tokens.destroy', $token->accessToken))
            ->assertForbidden();
    }

    // ---------------------------------------------------------------------
    // لیست و تب‌ها
    // ---------------------------------------------------------------------

    public function test_admin_sees_tokens_with_users_devices_and_tabs(): void
    {
        $sara = User::factory()->create(['name' => 'سارا محمدی', 'email' => 'sara@example.test']);
        $sara->createToken('iPhone 15');
        User::factory()->create()->createToken('Android');

        $response = $this->actingAs($this->admin())
            ->get(route('admin.tokens.index'));

        $response->assertOk()
            ->assertSee('سارا محمدی')
            ->assertSee('sara@example.test')
            ->assertSee('iPhone 15')
            ->assertSee('Android')
            ->assertSee('بدون انقضا')
            ->assertSee('همه (۲)')
            ->assertSee('توکن‌های API'); // آیتم سایدبار
    }

    public function test_newest_token_is_listed_first(): void
    {
        $u = User::factory()->create();
        $old = $u->createToken('قدیمی');
        $old->accessToken->forceFill(['created_at' => now()->subDay()])->save();
        $u->createToken('جدید');

        $html = $this->actingAs($this->admin())
            ->get(route('admin.tokens.index'))
            ->getContent();

        $this->assertLessThan(
            mb_strpos($html, 'قدیمی'),
            mb_strpos($html, 'جدید'),
            'جدیدترین توکن باید بالای لیست باشد'
        );
    }

    public function test_unused_and_expired_tabs_filter_correctly(): void
    {
        $u = User::factory()->create();

        $used = $u->createToken('تبلت-سارا')->accessToken;
        $used->forceFill(['last_used_at' => now()->subDays(3)])->save();

        $u->createToken('گوشی-علی'); // هرگز استفاده نشده

        $expired = $u->createToken('تبلت-قدیمی')->accessToken;
        $expired->forceFill([
            'expires_at' => now()->subDay(),
            'last_used_at' => now()->subDays(60),
        ])->save();

        // تب «بدون استفاده» — فقط گوشی-علی
        $page = $this->actingAs($this->admin())
            ->get(route('admin.tokens.index', ['filter' => 'unused']));
        $page->assertOk()
            ->assertSee('گوشی-علی')
            ->assertDontSee('تبلت-سارا')
            ->assertDontSee('تبلت-قدیمی')
            ->assertSee('بدون استفاده (۱)');

        // تب «منقضی‌شده» — فقط تبلت-قدیمی
        $page = $this->actingAs($this->admin())
            ->get(route('admin.tokens.index', ['filter' => 'expired']));
        $page->assertOk()
            ->assertSee('تبلت-قدیمی')
            ->assertDontSee('تبلت-سارا')
            ->assertDontSee('گوشی-علی')
            ->assertSee('منقضی‌شده (۱)');

        // مقدار filter ناشناخته → همان «همه»
        $this->actingAs($this->admin())
            ->get(route('admin.tokens.index', ['filter' => 'nonsense']))
            ->assertOk()
            ->assertSee('تبلت-سارا')
            ->assertSee('گوشی-علی');
    }

    public function test_expired_and_never_used_rows_carry_labels_and_admin_gets_badge(): void
    {
        $admin = $this->admin(); // توکن برای خود مدیر → بج «مدیر»
        $admin->createToken('پنل-تست', ['*'], now()->subDay())->accessToken
            ->forceFill(['last_used_at' => now()->subDays(2)])->save();

        User::factory()->create()->createToken('دست‌نخورده');

        $html = $this->actingAs($admin)
            ->get(route('admin.tokens.index'))
            ->getContent();

        // بج مدیر روی توکن کاربرِ مدیر
        $this->assertStringContainsString('<span class="badge text-bg-info">مدیر</span>', $html);
        // بج «منقضی‌شده» + بج «هرگز» برای توکن بدون استفاده
        $this->assertStringContainsString('<span class="badge text-bg-danger">منقضی‌شده</span>', $html);
        $this->assertStringContainsString('<span class="badge text-bg-warning">هرگز</span>', $html);
    }

    public function test_orphaned_token_shows_deleted_user(): void
    {
        $user = User::factory()->create();
        $user->createToken('یتیم');
        $user->delete(); // توکن‌های Sanctum FK آبشاری ندارند → رکورد می‌ماند

        $this->actingAs($this->admin())
            ->get(route('admin.tokens.index'))
            ->assertOk()
            ->assertSee('کاربر حذف‌شده')
            ->assertSee('یتیم');
    }

    public function test_create_form_lists_every_user_with_email(): void
    {
        User::factory()->create(['name' => 'مریم رضایی', 'email' => 'maryam@example.test']);

        $this->actingAs($this->admin())
            ->get(route('admin.tokens.index'))
            ->assertOk()
            ->assertSee('مریم رضایی — maryam@example.test')
            ->assertSee('صدور توکن جدید');
    }

    // ---------------------------------------------------------------------
    // صدور توکن از پنل
    // ---------------------------------------------------------------------

    public function test_store_validates_missing_and_bad_input(): void
    {
        // خالی کامل
        $this->actingAs($this->admin())
            ->post(route('admin.tokens.store'), [])
            ->assertSessionHasErrors(['user_id', 'name', 'expires']);

        // مقادیر نامعتبر
        $this->actingAs($this->admin())
            ->post(route('admin.tokens.store'), [
                'user_id' => 999999,          // کاربر وجود ندارد
                'name' => 'ab',               // کوتاه‌تر از ۳
                'expires' => 'abc',           // خارج از گزینه‌ها
            ])
            ->assertSessionHasErrors(['user_id', 'name', 'expires']);
    }

    public function test_store_issues_token_with_expiry_and_one_time_modal(): void
    {
        $user = User::factory()->create(['name' => 'علی کریمی']);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.tokens.store'), [
                'user_id' => $user->id,
                'name' => '  iPhone 15  ', // trim شود
                'expires' => '30',
            ]);

        // PRG به لیست + فلش یک‌باره
        $response->assertRedirect(route('admin.tokens.index'));
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'iPhone 15',
            'abilities' => '["*"]',
        ]);

        // انقضا: ~۳۰ روز دیگر (نه ۲۹، نه ۳۱) — محاسبهٔ خالص روی timestamp تا
        // رفتار API کربن در نسخه‌ها دخالتی در تست نکند
        $row = PersonalAccessToken::where('name', 'iPhone 15')->first();
        $days = ($row->expires_at->getTimestamp() - now()->getTimestamp()) / 86400;
        $this->assertGreaterThan(29, $days);
        $this->assertLessThan(31, $days);

        // درخواستِ بعد از ریدایرکت: مودال مقدار کامل را نشان می‌دهد…
        $plain = session('new_token');
        $this->assertNotEmpty($plain);
        $page = $this->get(route('admin.tokens.index'));
        $page->assertOk()->assertSee($plain);

        // …و درخواستِ بعدی دیگر هیچ ردی از توکن ندارد (فلشِ یک‌باره)
        $this->get(route('admin.tokens.index'))
            ->assertOk()
            ->assertDontSee($plain);
    }

    public function test_admin_issued_token_works_against_real_api(): void
    {
        $user = User::factory()->create(['email' => 'akbar@example.test']);
        $plain = $user->createToken('تست-واقعی')->plainTextToken;

        // توکنِ صادرشده از پنل، در API واقعی کار می‌کند
        $this->app->make('auth')->forgetGuards();
        $this->withToken($plain)
            ->getJson(route('api.v1.auth.me'))
            ->assertOk()
            ->assertJsonPath('data.email', 'akbar@example.test');

        // استفاده → last_used_at ثبت می‌شود و در پنل جای «هرگز» تاریخ می‌نشیند
        $this->assertNotNull(
            PersonalAccessToken::where('name', 'تست-واقعی')->first()->last_used_at
        );

        $row = PersonalAccessToken::where('name', 'تست-واقعی')->first();
        $this->actingAs($this->admin())
            ->get(route('admin.tokens.index'))
            ->assertOk()
            ->assertSee($row->last_used_at->format('Y-m-d H:i'))
            ->assertDontSee('<span class="badge text-bg-warning">هرگز</span>', false);
    }

    public function test_sanctum_rejects_expired_token_end_to_end(): void
    {
        $user = User::factory()->create();
        $issued = $user->createToken('منقضی', ['*'], now()->addDays(7));
        $plain = $issued->plainTextToken;

        // هنوز زنده است
        $this->app->make('auth')->forgetGuards();
        $this->withToken($plain)
            ->getJson(route('api.v1.auth.me'))
            ->assertOk();

        // تاریخ انقضا می‌گذرد → همان توکن دیگر احراز هویت نمی‌شود
        // (forceFill: expires_at fillable است ولی برای یکدستی با last_used_at همین‌جا هم forceFill می‌زنیم)
        $issued->accessToken->forceFill(['expires_at' => now()->subMinute()])->save();
        $this->app->make('auth')->forgetGuards();
        $this->withToken($plain)
            ->getJson(route('api.v1.auth.me'))
            ->assertUnauthorized();
    }

    // ---------------------------------------------------------------------
    // ابطال توکن
    // ---------------------------------------------------------------------

    public function test_admin_can_revoke_token_end_to_end(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        // صدور از طریق خود فرم پنل (مسیر واقعی، نه دور‌زدن)
        $this->actingAs($admin)
            ->post(route('admin.tokens.store'), [
                'user_id' => $user->id,
                'name' => 'گوشی مشتری',
                'expires' => 'never',
            ])->assertRedirect(route('admin.tokens.index'));

        $plain = session('new_token');

        // قبل از ابطال: API با توکن جواب می‌دهد
        $this->app->make('auth')->forgetGuards();
        $this->withToken($plain)
            ->getJson(route('api.v1.auth.me'))
            ->assertOk();

        // ابطال از پنل (from: دکمه روی صفحهٔ لیست فشرده شده — الگوی PRG)
        $token = PersonalAccessToken::where('name', 'گوشی مشتری')->first();
        $this->actingAs($admin)
            ->from(route('admin.tokens.index'))
            ->delete(route('admin.tokens.destroy', $token))
            ->assertRedirect(route('admin.tokens.index'));

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->id]);

        // بعد از ابطال: همان توکن مرده است و دستگاه باید دوباره وارد شود
        $this->app->make('auth')->forgetGuards();
        $this->withToken($plain)
            ->getJson(route('api.v1.auth.me'))
            ->assertUnauthorized();
    }

    public function test_revoke_unknown_token_returns_404(): void
    {
        $this->actingAs($this->admin())
            ->delete(route('admin.tokens.destroy', 999999))
            ->assertNotFound();
    }
}

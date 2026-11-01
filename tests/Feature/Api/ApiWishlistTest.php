<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * علاقه‌مندی‌های API (v2.12.0):
 * لیست/افزودن/حذف با توکن — idempotent برای اپلیکیشن موبایل
 */
class ApiWishlistTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $title, array $attributes = []): Product
    {
        return Product::factory()->create(array_merge(['title' => $title], $attributes));
    }

    // ---------------------------------------------------------------------
    // دسترسی
    // ---------------------------------------------------------------------

    public function test_wishlist_endpoints_require_token(): void
    {
        $product = $this->product('مودم TD-LTE');

        $this->getJson(route('api.v1.wishlist.index'))->assertUnauthorized();
        $this->postJson(route('api.v1.wishlist.store', $product))->assertUnauthorized();
        $this->deleteJson(route('api.v1.wishlist.destroy', $product))->assertUnauthorized();
    }

    // ---------------------------------------------------------------------
    // لیست
    // ---------------------------------------------------------------------

    public function test_wishlist_lists_users_saved_active_products(): void
    {
        $user = User::factory()->create();
        $saved = $this->product('مودم TD-LTE');
        $inactiveSaved = $this->product('محصول خاموش', ['is_active' => false]);

        $user->wishlistProducts()->attach([$saved->id, $inactiveSaved->id]);

        Sanctum::actingAs($user);

        $this->getJson(route('api.v1.wishlist.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'مودم TD-LTE')
            ->assertJsonPath('data.0.is_in_wishlist', true);
    }

    // ---------------------------------------------------------------------
    // افزودن / حذف
    // ---------------------------------------------------------------------

    public function test_user_can_add_product_to_wishlist(): void
    {
        $user = User::factory()->create();
        $product = $this->product('کابل شبکه CAT6');

        Sanctum::actingAs($user);

        $this->postJson(route('api.v1.wishlist.store', $product))
            ->assertOk()
            ->assertJsonPath('data.slug', $product->slug)
            ->assertJsonPath('data.is_in_wishlist', true);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_adding_twice_is_idempotent(): void
    {
        $user = User::factory()->create();
        $product = $this->product('تستر شبکه');

        Sanctum::actingAs($user);

        $this->postJson(route('api.v1.wishlist.store', $product))->assertOk();
        $this->postJson(route('api.v1.wishlist.store', $product))->assertOk();

        $this->assertDatabaseCount('wishlists', 1);
    }

    public function test_user_can_remove_product_from_wishlist(): void
    {
        $user = User::factory()->create();
        $product = $this->product('کابل شبکه CAT6');
        $user->wishlistProducts()->attach($product->id);

        Sanctum::actingAs($user);

        $this->deleteJson(route('api.v1.wishlist.destroy', $product))
            ->assertOk()
            ->assertJsonPath('data.is_in_wishlist', false);

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_removing_unsaved_product_is_idempotent(): void
    {
        $user = User::factory()->create();
        $product = $this->product('مودم TD-LTE');

        Sanctum::actingAs($user);

        $this->deleteJson(route('api.v1.wishlist.destroy', $product))
            ->assertOk()
            ->assertJsonPath('data.is_in_wishlist', false);
    }

    public function test_adding_inactive_product_returns_404(): void
    {
        $user = User::factory()->create();
        $product = $this->product('محصول خاموش', ['is_active' => false]);

        Sanctum::actingAs($user);

        $this->postJson(route('api.v1.wishlist.store', $product))
            ->assertNotFound();

        $this->assertDatabaseCount('wishlists', 0);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * علاقه‌مندی‌ها (v2.10.0):
 * افزودن/حذف با یک کلیک، شمارندهٔ ناوبار، صفحهٔ اختصاصی و قواعد نمایش
 */
class WishlistTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------
    // دسترسی
    // ---------------------------------------------------------------------

    public function test_guest_toggle_is_redirected_to_login(): void
    {
        $product = $this->product('کابل شبکه CAT6');

        $this->post(route('wishlist.toggle', $product))
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_guest_wishlist_page_is_redirected_to_login(): void
    {
        $this->get(route('wishlist.index'))
            ->assertRedirect(route('login'));
    }

    public function test_guest_sees_login_heart_on_product_cards(): void
    {
        $this->product('مودم TD-LTE');

        $this->get(route('shop.products.index'))
            ->assertOk()
            ->assertSee('برای ذخیره در علاقه‌مندی‌ها وارد شوید');
    }

    // ---------------------------------------------------------------------
    // افزودن و حذف (toggle)
    // ---------------------------------------------------------------------

    public function test_user_can_add_product_to_wishlist(): void
    {
        $user = User::factory()->create();
        $product = $this->product('کابل شبکه CAT6');

        $this->actingAs($user)
            ->from(route('shop.products.show', $product))
            ->post(route('wishlist.toggle', $product))
            ->assertRedirect(route('shop.products.show', $product))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_user_can_remove_product_by_toggling_again(): void
    {
        $user = User::factory()->create();
        $product = $this->product('سوییچ ۸ پورت');
        $this->attach($user, $product);

        $this->actingAs($user)
            ->from(route('wishlist.index'))
            ->post(route('wishlist.toggle', $product))
            ->assertRedirect(route('wishlist.index'));

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_toggle_responds_json_for_ajax(): void
    {
        $user = User::factory()->create();
        $product = $this->product('کریمپر شبکه');

        $this->actingAs($user)
            ->post(route('wishlist.toggle', $product), [], [
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('inWishlist', true)
            ->assertJsonPath('count', 1);

        $this->actingAs($user)
            ->post(route('wishlist.toggle', $product), [], [
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->assertOk()
            ->assertJsonPath('inWishlist', false)
            ->assertJsonPath('count', 0);
    }

    public function test_inactive_product_cannot_be_toggled(): void
    {
        $user = User::factory()->create();
        $product = $this->product('محصول خاموش', false);

        $this->actingAs($user)
            ->post(route('wishlist.toggle', $product))
            ->assertNotFound();

        $this->assertDatabaseCount('wishlists', 0);
    }

    // ---------------------------------------------------------------------
    // صفحهٔ علاقه‌مندی‌ها
    // ---------------------------------------------------------------------

    public function test_wishlist_page_lists_newest_first(): void
    {
        $user = User::factory()->create();
        $old = $this->product('مودم مدل قدیم');
        $new = $this->product('مودم مدل نو');

        $this->attach($user, $old);
        $this->travel(1)->minutes();
        $this->attach($user, $new);

        $this->actingAs($user)
            ->get(route('wishlist.index'))
            ->assertOk()
            ->assertSeeInOrder([$new->title, $old->title]);
    }

    public function test_wishlist_page_shows_only_own_items(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $mine = $this->product('کابل مخصوص آلیس');
        $theirs = $this->product('مودم مخصوص بابک');

        $this->attach($alice, $mine);
        $this->attach($bob, $theirs);

        $this->actingAs($alice)
            ->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee($mine->title)
            ->assertDontSee($theirs->title);
    }

    public function test_wishlist_page_hides_inactive_products(): void
    {
        $user = User::factory()->create();
        $active = $this->product('سوییچ روشن');
        $inactive = $this->product('سوییچ خاموش', false);

        $this->attach($user, $active);
        $this->attach($user, $inactive);

        $this->actingAs($user)
            ->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee($active->title)
            ->assertDontSee($inactive->title);
    }

    public function test_wishlist_page_shows_empty_state(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('هنوز محصولی به علاقه‌مندی‌ها اضافه نکرده‌اید');
    }

    public function test_wishlist_page_is_paginated_at_twelve(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 13; $i++) {
            $this->attach($user, $this->product("محصول شماره {$i}"));
        }

        $first = $this->actingAs($user)->get(route('wishlist.index'));
        $this->assertSame(12, substr_count($first->getContent(), 'ds-product-card'));

        $second = $this->actingAs($user)->get(route('wishlist.index', ['page' => 2]));
        $this->assertSame(1, substr_count($second->getContent(), 'ds-product-card'));
    }

    // ---------------------------------------------------------------------
    // ناوبار و کارت‌ها
    // ---------------------------------------------------------------------

    public function test_navbar_count_counts_only_active_products(): void
    {
        $user = User::factory()->create();
        $this->attach($user, $this->product('کابل فعال یک'));
        $this->attach($user, $this->product('کابل فعال دو'));
        $this->attach($user, $this->product('کابل خاموش', false));

        $content = $this->actingAs($user)
            ->get(route('shop.home'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="dsWishlistCount"', $content);
        $this->assertStringNotContainsString('id="dsWishlistCount" style', $content);

        preg_match('/id="dsWishlistCount"[^>]*>\s*([^<]+)</u', $content, $m);
        $this->assertSame('۲', trim($m[1] ?? ''));
    }

    public function test_navbar_count_is_hidden_for_guests(): void
    {
        $content = $this->get(route('shop.home'))->assertOk()->getContent();

        $this->assertStringContainsString('id="dsWishlistCount" style="display:none"', $content);
    }

    public function test_heart_is_solid_for_wishlisted_product_on_cards(): void
    {
        $user = User::factory()->create();
        $product = $this->product('مودم ویژه الف');

        $this->attach($user, $product);

        $this->actingAs($user)
            ->get(route('shop.products.index', ['q' => 'ویژه الف']))
            ->assertOk()
            ->assertSee('data-wishlist-product="'.$product->slug.'"', false)
            ->assertSee('ds-wish-btn active');
    }

    public function test_heart_is_outline_for_other_products_on_cards(): void
    {
        $user = User::factory()->create();
        $product = $this->product('مودم ویژه ب');

        $this->actingAs($user)
            ->get(route('shop.products.index', ['q' => 'ویژه ب']))
            ->assertOk()
            ->assertSee('data-wishlist-product="'.$product->slug.'"', false)
            ->assertDontSee('ds-wish-btn active');
    }

    public function test_product_detail_page_has_wishlist_toggle_form(): void
    {
        $user = User::factory()->create();
        $product = $this->product('کریمپر حرفه‌ای');

        $this->actingAs($user)
            ->get(route('shop.products.show', $product))
            ->assertOk()
            ->assertSee('action="'.route('wishlist.toggle', $product).'"', false)
            ->assertSee('ds-wish-btn-lg');
    }

    public function test_account_dropdown_links_to_wishlist(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('shop.home'))
            ->assertOk()
            ->assertSee(route('wishlist.index'));
    }

    // ---------------------------------------------------------------------
    // یکپارچگی داده
    // ---------------------------------------------------------------------

    public function test_deleting_product_cascades_to_wishlist_rows(): void
    {
        $user = User::factory()->create();
        $product = $this->product('محصول موقتی');

        $this->attach($user, $product);

        $product->delete();

        $this->assertDatabaseCount('wishlists', 0);
    }

    // ---------------------------------------------------------------------
    // هلپرها
    // ---------------------------------------------------------------------

    private function product(string $title, bool $isActive = true): Product
    {
        static $counter = 0;

        return Product::factory()->create([
            'title' => $title,
            'slug' => 'wish-'.++$counter,
            'price' => 500000,
            'stock' => 10,
            'is_active' => $isActive,
            'category_id' => Category::factory()->create()->id,
        ]);
    }

    private function attach(User $user, Product $product): Wishlist
    {
        return Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }
}

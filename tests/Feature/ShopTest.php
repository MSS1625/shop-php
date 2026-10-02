<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('دیجی‌شاپ');
    }

    public function test_products_page_displays_active_products(): void
    {
        $product = Product::factory()->create([
            'title' => 'مودم تستی',
            'is_active' => true,
        ]);

        $this->get('/products')
            ->assertOk()
            ->assertSee('مودم تستی');
    }

    public function test_inactive_products_are_hidden(): void
    {
        $product = Product::factory()->create([
            'title' => 'محصول مخفی',
            'is_active' => false,
        ]);

        $this->get('/products')
            ->assertOk()
            ->assertDontSee('محصول مخفی');
    }

    public function test_product_detail_page_shows_by_slug(): void
    {
        $product = Product::factory()->create(['slug' => 'test-modem']);

        $this->get('/products/test-modem')
            ->assertOk()
            ->assertSee($product->title);
    }

    public function test_search_filters_products(): void
    {
        Product::factory()->create(['title' => 'سوییچ شبکه']);
        Product::factory()->create(['title' => 'کابل برق']);

        $this->get('/products?q='.urlencode('سوییچ'))
            ->assertOk()
            ->assertSee('سوییچ شبکه')
            ->assertDontSee('کابل برق');
    }

    public function test_category_filter_works(): void
    {
        $category = Category::factory()->create(['slug' => 'tools']);
        Product::factory()->create([
            'title' => 'آچار شبکه',
            'category_id' => $category->id,
        ]);
        Product::factory()->create(['title' => 'محصول بدون دسته دیگر']);

        $this->get('/products?category=tools')
            ->assertOk()
            ->assertSee('آچار شبکه');
    }

    public function test_admin_panel_requires_authentication(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_users_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }
}

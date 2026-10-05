<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_user_stats_and_recent_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->forUser($user)->count(3)->create();

        $this->actingAs($user)
            ->get(route('account.dashboard'))
            ->assertOk()
            ->assertSee('کل سفارش‌ها');
    }

    public function test_orders_history_shows_only_own_orders(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $myOrder = Order::factory()->forUser($user)->create(['order_number' => 'DS-111111-AAAA']);
        $otherOrder = Order::factory()->forUser($other)->create(['order_number' => 'DS-222222-BBBB']);

        $response = $this->actingAs($user)->get(route('account.orders'));

        $response->assertOk()
            ->assertSee('DS-111111-AAAA')
            ->assertDontSee('DS-222222-BBBB');
    }

    public function test_order_detail_is_accessible_only_for_owner(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $order = Order::factory()->forUser($other)->create();

        $this->actingAs($user)
            ->get(route('account.orders.show', $order))
            ->assertForbidden();
    }

    public function test_order_detail_shows_timeline_and_items(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->forUser($user)->create();
        $order->items()->create([
            'product_id' => Product::factory()->create()->id,
            'product_title' => 'روتر تستی',
            'unit_price' => 100000,
            'quantity' => 2,
        ]);

        $this->actingAs($user)
            ->get(route('account.orders.show', $order))
            ->assertOk()
            ->assertSee('روتر تستی')
            ->assertSee('در انتظار بررسی');
    }

    public function test_user_can_cancel_pending_unpaid_order_and_stock_is_restored(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $order = Order::factory()->forUser($user)->create(['total' => 200000]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_title' => $product->title,
            'unit_price' => $product->price,
            'quantity' => 3,
        ]);

        $this->actingAs($user)
            ->post(route('account.orders.cancel', $order))
            ->assertRedirect();

        $order->refresh();
        $product->refresh();

        $this->assertTrue($order->isCancelled());
        $this->assertSame(8, $product->stock); // ۵ + ۳ برگشتی
    }

    public function test_user_cannot_cancel_paid_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->forUser($user)->online()->paid()->create();

        $this->actingAs($user)
            ->post(route('account.orders.cancel', $order))
            ->assertForbidden();
    }

    public function test_user_cannot_cancel_shipped_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->forUser($user)->create([
            'status' => Order::STATUS_SHIPPED,
        ]);

        $this->actingAs($user)
            ->post(route('account.orders.cancel', $order))
            ->assertForbidden();
    }

    public function test_checkout_links_order_to_logged_in_user_and_prefills_name(): void
    {
        $user = User::factory()->create(['name' => 'علی تستی']);
        $product = Product::factory()->create(['price' => 150000, 'stock' => 10]);

        // افزودن به سبد پیش از دیدن فرم تکمیل خرید
        $this->actingAs($user)
            ->post('/cart/add/'.$product->slug, ['quantity' => 1])
            ->assertRedirect();

        // پیش‌پر شدن نام در فرم
        $this->actingAs($user)->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('علی تستی');

        // ثبت سفارش
        $this->post(route('checkout.store'), [
            'customer_name' => 'علی تستی',
            'customer_phone' => '09123456789',
            'customer_address' => 'تهران، خیابان ولیعصر، پلاک ۱۰',
            'payment_method' => 'cod',
        ])->assertRedirect(route('order.success', Order::first()->order_number));

        $order = Order::first();
        $this->assertNotNull($order->user_id);
        $this->assertEquals($user->id, $order->user_id);
    }
}

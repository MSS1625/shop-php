<?php

namespace Tests\Feature\Api;

use App\Mail\OrderPlaced;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * سفارش‌های API (v2.12.0):
 * ثبت مستقیم با اقلام درخواست (همان سرویس PlaceOrder وب)،
 * لیست/جزئیات/لغو با جداسازی مالکیت
 */
class ApiOrderTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $title, array $attributes = []): Product
    {
        return Product::factory()->create(array_merge([
            'title' => $title,
            'price' => 1_000_000,
            'stock' => 10,
        ], $attributes));
    }

    private function payload(Product $product, array $overrides = []): array
    {
        return array_merge([
            'items' => [['slug' => $product->slug, 'quantity' => 2]],
            'customer_name' => 'مریم رضایی',
            'customer_phone' => '09123456789',
            'customer_address' => 'تهران، خیابان آزادی، پلاک ۱۰',
            'payment_method' => 'cod',
        ], $overrides);
    }

    // ---------------------------------------------------------------------
    // دسترسی
    // ---------------------------------------------------------------------

    public function test_order_endpoints_require_token(): void
    {
        $this->getJson(route('api.v1.orders.index'))->assertUnauthorized();
        $this->postJson(route('api.v1.orders.store'), [])->assertUnauthorized();
        $this->getJson(route('api.v1.orders.show', 'DS-000000-XXXX'))->assertUnauthorized();
    }

    // ---------------------------------------------------------------------
    // ثبت مستقیم سفارش
    // ---------------------------------------------------------------------

    public function test_user_can_place_order_directly(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $product = $this->product('مودم TD-LTE');

        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.v1.orders.store'), $this->payload($product))
            ->assertCreated()
            ->assertJsonPath('message', 'سفارش شما با موفقیت ثبت شد.')
            ->assertJsonPath('data.total', 2_000_000)
            ->assertJsonPath('data.total_fa', '۲٬۰۰۰٬۰۰۰')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.items.0.product_title', 'مودم TD-LTE')
            ->assertJsonPath('data.items.0.quantity', 2);

        $orderNumber = $response->json('data.order_number');

        $this->assertDatabaseHas('orders', [
            'order_number' => $orderNumber,
            'user_id' => $user->id,
            'status' => Order::STATUS_PENDING,
        ]);

        // موجودی انبار کم شد
        $this->assertSame(8, $product->fresh()->stock);

        // پرداخت در محل → لینک پرداختی در پاسخ نیست
        $this->assertArrayNotHasKey('payment_url', $response->json('data'));
    }

    public function test_duplicate_items_are_merged_like_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->product('کابل شبکه CAT6');

        Sanctum::actingAs($user);

        $this->postJson(route('api.v1.orders.store'), $this->payload($product, [
            'items' => [
                ['slug' => $product->slug, 'quantity' => 1],
                ['slug' => $product->slug, 'quantity' => 2],
            ],
        ]))->assertCreated()->assertJsonPath('data.items.0.quantity', 3);

        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_online_payment_order_returns_payment_url(): void
    {
        $user = User::factory()->create();
        $product = $this->product('مودم TD-LTE');

        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.v1.orders.store'), $this->payload($product, [
            'payment_method' => 'zarinpal',
        ]))->assertCreated();

        $this->assertSame(
            route('payment.start', $response->json('data.order_number')),
            $response->json('data.payment_url')
        );
    }

    public function test_valid_coupon_is_applied(): void
    {
        $user = User::factory()->create();
        $product = $this->product('سوئیچ ۲۴ پورت');
        $coupon = Coupon::create([
            'code' => 'WELCOME10',
            'type' => Coupon::TYPE_PERCENT,
            'value' => 10,
            'min_order_amount' => 0,
            'usage_limit' => 100,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $this->postJson(route('api.v1.orders.store'), $this->payload($product, [
            'coupon_code' => 'WELCOME10',
        ]))
            ->assertCreated()
            ->assertJsonPath('data.discount_amount', 200_000)
            ->assertJsonPath('data.coupon_code', 'WELCOME10')
            ->assertJsonPath('data.total', 1_800_000);

        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_invalid_coupon_returns_422_with_reason(): void
    {
        $user = User::factory()->create();
        $product = $this->product('تستر شبکه');

        // کوپن با حداقل خرید بالاتر از سبد
        Coupon::create([
            'code' => 'BIGORDER',
            'type' => Coupon::TYPE_FIXED,
            'value' => 500_000,
            'min_order_amount' => 10_000_000,
            'usage_limit' => null,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $this->postJson(route('api.v1.orders.store'), $this->payload($product, [
            'coupon_code' => 'BIGORDER',
        ]))->assertUnprocessable()->assertJsonValidationErrors(['coupon_code']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_unknown_coupon_returns_422(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = $this->product('مودم TD-LTE');

        $this->postJson(route('api.v1.orders.store'), $this->payload($product, [
            'coupon_code' => 'GHOST',
        ]))->assertUnprocessable()->assertJsonValidationErrors(['coupon_code']);
    }

    public function test_insufficient_stock_returns_409(): void
    {
        $user = User::factory()->create();
        $product = $this->product('مودم TD-LTE', ['stock' => 1]);

        Sanctum::actingAs($user);

        $this->postJson(route('api.v1.orders.store'), $this->payload($product))
            ->assertConflict()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'موجودی «مودم TD-LTE» کافی نیست. سبد خود را بروزرسانی کنید.');

        // انبار دست‌نخورده، سفارشی ثبت نشده
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_inactive_product_slug_is_rejected(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = $this->product('محصول خاموش', ['is_active' => false]);

        $this->postJson(route('api.v1.orders.store'), $this->payload($product))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.slug']);
    }

    public function test_order_sends_notification(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $product = $this->product('مودم TD-LTE');

        Sanctum::actingAs($user);

        $this->postJson(route('api.v1.orders.store'), $this->payload($product, [
            'customer_email' => 'maryam@example.test',
        ]))->assertCreated();

        // همان ایمیلی که فرم خرید وب می‌فرستد
        Mail::assertSent(OrderPlaced::class, fn (OrderPlaced $mail) => $mail->hasTo('maryam@example.test'));
    }

    // ---------------------------------------------------------------------
    // لیست و جزئیات
    // ---------------------------------------------------------------------

    public function test_orders_list_shows_only_own_orders(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        Order::factory()->forUser($me)->create(['total' => 100]);
        Order::factory()->forUser($me)->create(['total' => 200]);
        Order::factory()->count(3)->forUser($other)->create();

        Sanctum::actingAs($me);

        $this->getJson(route('api.v1.orders.index'))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'id', 'order_number', 'status', 'status_label',
                    'total', 'total_fa', 'items_count', 'created_at',
                ]],
                'links', 'meta',
            ]);
    }

    public function test_order_details_show_own_order_with_items(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->forUser($user)->create();
        $order->items()->create([
            'product_id' => $this->product('کابل CAT6')->id,
            'product_title' => 'کابل CAT6',
            'unit_price' => 500_000,
            'quantity' => 3,
        ]);

        Sanctum::actingAs($user);

        $this->getJson(route('api.v1.orders.show', $order->order_number))
            ->assertOk()
            ->assertJsonPath('data.order_number', $order->order_number)
            ->assertJsonPath('data.items.0.quantity', 3)
            ->assertJsonPath('data.items.0.subtotal', 1_500_000);
    }

    public function test_order_details_of_another_user_return_404(): void
    {
        $me = User::factory()->create();
        $owner = User::factory()->create();
        $order = Order::factory()->forUser($owner)->create();

        Sanctum::actingAs($me);

        $this->getJson(route('api.v1.orders.show', $order->order_number))
            ->assertNotFound();
    }

    // ---------------------------------------------------------------------
    // لغو
    // ---------------------------------------------------------------------

    public function test_pending_order_can_be_cancelled_with_restock(): void
    {
        $user = User::factory()->create();
        $product = $this->product('مودم TD-LTE', ['stock' => 10]);

        Sanctum::actingAs($user);

        $orderNumber = $this->postJson(route('api.v1.orders.store'), $this->payload($product))
            ->assertCreated()
            ->json('data.order_number');

        $this->assertSame(8, $product->fresh()->stock);

        $this->postJson(route('api.v1.orders.cancel', $orderNumber))
            ->assertOk()
            ->assertJsonPath('message', 'سفارش لغو شد و موجودی محصولات به انبار بازگشت.');

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertDatabaseHas('orders', [
            'order_number' => $orderNumber,
            'status' => Order::STATUS_CANCELLED,
        ]);
    }

    public function test_paid_or_shipped_order_cannot_be_cancelled(): void
    {
        $user = User::factory()->create();

        $shipped = Order::factory()->forUser($user)->create(['status' => Order::STATUS_SHIPPED]);
        $paid = Order::factory()->forUser($user)->paid()->create();

        Sanctum::actingAs($user);

        $this->postJson(route('api.v1.orders.cancel', $shipped->order_number))->assertForbidden();
        $this->postJson(route('api.v1.orders.cancel', $paid->order_number))->assertForbidden();
    }

    public function test_cannot_cancel_another_users_order(): void
    {
        $me = User::factory()->create();
        $owner = User::factory()->create();
        $order = Order::factory()->forUser($owner)->create();

        Sanctum::actingAs($me);

        $this->postJson(route('api.v1.orders.cancel', $order->order_number))
            ->assertNotFound();
    }
}

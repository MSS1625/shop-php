<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function coupon(array $attributes = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'TEST10',
            'type' => Coupon::TYPE_PERCENT,
            'value' => 10,
            'min_order_amount' => 0,
            'is_active' => true,
        ], $attributes));
    }

    /** محصول در سبد — پیش‌فرض: ۲ عدد × ۲۰۰٬۰۰۰ تومان = ۴۰۰٬۰۰۰ تومان */
    private function addToCart(int $quantity = 2): Product
    {
        $product = Product::factory()->create(['price' => 200000, 'stock' => 10]);

        $this->post('/cart/add/'.$product->slug, ['quantity' => $quantity])->assertRedirect();

        return $product;
    }

    public function test_guest_can_apply_valid_percent_coupon(): void
    {
        $this->addToCart();
        $coupon = $this->coupon();

        $response = $this->from('/cart')->post(route('cart.coupon.apply'), ['code' => 'test10']);

        $response->assertRedirect('/cart');
        $response->assertSessionHas('coupon_code', 'TEST10');
        $response->assertSessionHas('success');

        // کوپن هنوز مصرف نشده — مصرف فقط هنگام ثبت سفارش است
        $this->assertSame(0, $coupon->fresh()->used_count);
    }

    public function test_unknown_coupon_code_is_rejected(): void
    {
        $this->addToCart();

        $this->from('/cart')
            ->post(route('cart.coupon.apply'), ['code' => 'NOPE99'])
            ->assertRedirect('/cart')
            ->assertSessionHas('error');

        $this->assertNull(session('coupon_code'));
    }

    public function test_expired_coupon_is_rejected(): void
    {
        $this->addToCart();
        $this->coupon(['expires_at' => now()->subDay()]);

        $this->from('/cart')
            ->post(route('cart.coupon.apply'), ['code' => 'TEST10'])
            ->assertRedirect('/cart')
            ->assertSessionHas('error');

        $this->assertNull(session('coupon_code'));
    }

    public function test_exhausted_coupon_is_rejected(): void
    {
        $this->addToCart();
        $this->coupon(['usage_limit' => 5, 'used_count' => 5]);

        $this->from('/cart')
            ->post(route('cart.coupon.apply'), ['code' => 'TEST10'])
            ->assertSessionHas('error');

        $this->assertNull(session('coupon_code'));
    }

    public function test_coupon_below_min_order_amount_is_rejected(): void
    {
        $this->addToCart(); // 400,000
        $this->coupon(['min_order_amount' => 500000]);

        $this->from('/cart')
            ->post(route('cart.coupon.apply'), ['code' => 'TEST10'])
            ->assertSessionHas('error');

        $this->assertNull(session('coupon_code'));
    }

    public function test_coupon_can_be_removed_from_cart(): void
    {
        $this->addToCart();
        $this->coupon();

        $this->post(route('cart.coupon.apply'), ['code' => 'TEST10'])
            ->assertSessionHas('coupon_code', 'TEST10');

        $this->from('/cart')
            ->delete(route('cart.coupon.remove'))
            ->assertRedirect('/cart')
            ->assertSessionHas('success');

        $this->assertNull(session('coupon_code'));
    }

    public function test_coupon_cannot_be_applied_to_empty_cart(): void
    {
        $this->coupon();

        $this->from('/cart')
            ->post(route('cart.coupon.apply'), ['code' => 'TEST10'])
            ->assertRedirect('/cart')
            ->assertSessionHas('error');
    }

    public function test_cart_page_shows_discount_and_payable(): void
    {
        $this->addToCart(); // 400,000
        $this->coupon();

        $this->post(route('cart.coupon.apply'), ['code' => 'TEST10']);

        $this->get('/cart')
            ->assertOk()
            ->assertSee('TEST10')
            ->assertSee('جمع اقلام')
            ->assertSee('مبلغ قابل پرداخت')
            ->assertSee('۴۰٬۰۰۰')  // تخفیف ۱۰٪
            ->assertSee('۳۶۰٬۰۰۰'); // قابل پرداخت
    }

    public function test_fixed_coupon_discount_is_capped_at_subtotal(): void
    {
        $this->addToCart(); // 400,000
        $this->coupon(['type' => Coupon::TYPE_FIXED, 'value' => 900000]);

        $this->post(route('cart.coupon.apply'), ['code' => 'TEST10']);

        $this->get('/cart')
            ->assertOk()
            ->assertSee('۴۰۰٬۰۰۰')  // تخفیف = کل سبد
            ->assertSee('۰');       // قابل پرداخت = ۰
    }

    public function test_checkout_stores_discount_on_order(): void
    {
        $this->addToCart(); // 400,000
        $coupon = $this->coupon();

        $this->post(route('cart.coupon.apply'), ['code' => 'TEST10']);

        $this->post(route('checkout.store'), [
            'customer_name' => 'کاربر تستی',
            'customer_phone' => '09123456789',
            'customer_address' => 'تهران، خیابان آزادی، پلاک ۱۲',
            'payment_method' => 'cod',
        ])->assertRedirect();

        $order = Order::firstOrFail();

        $this->assertSame(400000, $order->subtotal);
        $this->assertSame(40000, $order->discount_amount);
        $this->assertSame('TEST10', $order->coupon_code);
        $this->assertSame(360000, $order->total);
        $this->assertTrue($order->hasDiscount());
        $this->assertSame(1, $coupon->fresh()->used_count);

        // سفارش بدون تخفیفِ سشن رها می‌شود
        $this->assertNull(session('coupon_code'));
    }

    public function test_checkout_page_shows_discount_lines(): void
    {
        $this->addToCart();
        $this->coupon();

        $this->post(route('cart.coupon.apply'), ['code' => 'TEST10']);

        $this->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('TEST10')
            ->assertSee('۳۶۰٬۰۰۰');
    }

    public function test_invalid_coupon_at_checkout_redirects_to_cart(): void
    {
        $this->addToCart();
        $coupon = $this->coupon();

        $this->post(route('cart.coupon.apply'), ['code' => 'TEST10']);

        // کوپن بعد از اعمال، پیش از ثبت سفارش غیرفعال می‌شود
        $coupon->update(['is_active' => false]);

        $this->from('/checkout')
            ->post(route('checkout.store'), [
                'customer_name' => 'کاربر تستی',
                'customer_phone' => '09123456789',
                'customer_address' => 'تهران، خیابان آزادی، پلاک ۱۲',
                'payment_method' => 'cod',
            ])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error');

        // سفارشی ثبت نشده و کوپن مصرف نشده است
        $this->assertSame(0, Order::count());
        $this->assertSame(0, $coupon->fresh()->used_count);
    }

    public function test_order_success_page_shows_discount_lines(): void
    {
        $this->addToCart();
        $this->coupon();

        $this->post(route('cart.coupon.apply'), ['code' => 'TEST10']);

        $this->post(route('checkout.store'), [
            'customer_name' => 'کاربر تستی',
            'customer_phone' => '09123456789',
            'customer_address' => 'تهران، خیابان آزادی، پلاک ۱۲',
            'payment_method' => 'cod',
        ]);

        $order = Order::firstOrFail();

        $this->get(route('order.success', $order->order_number))
            ->assertOk()
            ->assertSee('TEST10')
            ->assertSee('تخفیف کد')
            ->assertSee('مبلغ قابل پرداخت');
    }

    public function test_admin_can_create_coupon(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.coupons.store'), [
                'code' => 'summer 20',
                'type' => 'percent',
                'value' => 20,
                'min_order_amount' => 1000000,
                'usage_limit' => 50,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.coupons.index'))
            ->assertSessionHas('success');

        // کد ورودی کاربر نرمال شده است (حرف بزرگ + حذف فاصله)
        $coupon = Coupon::findByCode('SUMMER20');
        $this->assertNotNull($coupon);
        $this->assertSame(20, $coupon->value);
        $this->assertSame(1000000, $coupon->min_order_amount);
    }

    public function test_admin_percent_coupon_above_100_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.coupons.store'), [
                'code' => 'BIG150',
                'type' => 'percent',
                'value' => 150,
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('value');

        $this->assertSame(0, Coupon::count());
    }

    public function test_admin_can_edit_and_deactivate_coupon(): void
    {
        $coupon = $this->coupon();

        $this->actingAs($this->admin())
            ->put(route('admin.coupons.update', $coupon), [
                'code' => 'TEST10',
                'type' => 'percent',
                'value' => 15,
                'min_order_amount' => 0,
                'is_active' => '0',
            ])
            ->assertRedirect(route('admin.coupons.index'))
            ->assertSessionHas('success');

        $fresh = $coupon->fresh();
        $this->assertSame(15, $fresh->value);
        $this->assertFalse($fresh->is_active);
    }

    public function test_non_admin_cannot_manage_coupons(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $coupon = $this->coupon();

        $this->actingAs($user)
            ->post(route('admin.coupons.store'), [
                'code' => 'HACK1',
                'type' => 'percent',
                'value' => 50,
                'is_active' => '1',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('admin.coupons.destroy', $coupon))
            ->assertForbidden();

        $this->assertDatabaseHas('coupons', ['id' => $coupon->id]);
    }

    public function test_admin_coupons_page_lists_coupons(): void
    {
        $this->coupon(['code' => 'LISTED1']);

        $this->actingAs($this->admin())
            ->get(route('admin.coupons.index'))
            ->assertOk()
            ->assertSee('LISTED1')
            ->assertSee('کد تخفیف جدید');
    }
}

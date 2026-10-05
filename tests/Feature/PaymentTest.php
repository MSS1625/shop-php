<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /** ساخت سفارش از سبد خرید با روش پرداخت مشخص */
    private function checkoutWith(string $method): Order
    {
        $product = Product::factory()->create(['price' => 200000, 'stock' => 10]);

        $this->post('/cart/add/'.$product->slug, ['quantity' => 2])->assertRedirect();

        $this->post(route('checkout.store'), [
            'customer_name' => 'کاربر تستی',
            'customer_phone' => '09123456789',
            'customer_address' => 'تهران، خیابان آزادی، پلاک ۱۲',
            'payment_method' => $method,
        ]);

        return Order::firstOrFail();
    }

    public function test_cod_checkout_creates_pending_order(): void
    {
        $order = $this->checkoutWith('cod');

        $this->assertSame(Order::METHOD_COD, $order->payment_method);
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
        $this->assertFalse($order->isPaid());
    }

    public function test_online_checkout_redirects_to_payment_start(): void
    {
        $order = $this->checkoutWith('zarinpal');

        $this->assertSame(Order::METHOD_ZARINPAL, $order->payment_method);

        // مسیر start باید در سشن همین کاربر معتبر باشد و به درگاه هدایت کند
        $this->get(route('payment.start', $order->order_number))
            ->assertRedirect();
    }

    public function test_mock_gateway_page_loads_for_requested_order(): void
    {
        $order = $this->checkoutWith('zarinpal');

        // start مسیر mock را برمی‌گرداند
        $redirect = $this->get(route('payment.start', $order->order_number))->assertRedirect()->headers->get('Location');

        $this->get($redirect)
            ->assertOk()
            ->assertSee('پرداخت آزمایشی')
            ->assertSee('درگاه آزمایشی');
    }

    public function test_successful_payment_marks_order_as_paid(): void
    {
        $order = $this->checkoutWith('zarinpal');
        $product = $order->items()->first()->product;

        // شروع پرداخت → آدرس درگاه mock
        $redirect = $this->get(route('payment.start', $order->order_number))
            ->assertRedirect()
            ->headers->get('Location');

        // کاربر در درگاه آزمایشی پرداخت موفق را انتخاب می‌کند
        $this->post(route('payment.mock.result', $order->order_number), [
            'result' => 'success',
            'authority' => $order->refresh()->authority,
        ])->assertRedirect(route('payment.callback', [
            'Authority' => $order->authority,
            'Status' => 'OK',
        ]));

        // بازگشت از درگاه → تایید نهایی
        $this->get(route('payment.callback', [
            'Authority' => $order->authority,
            'Status' => 'OK',
        ]))->assertRedirect(route('order.success', $order->order_number));

        $order->refresh();

        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->ref_id);
        $this->assertNotNull($order->paid_at);
        // موجودی در پرداخت موفق نباید دوباره تغییر کند — فقط هنگام ثبت سفارش کسر شده
        $this->assertSame(8, $product->fresh()->stock);

        // صفحه موفقیت شماره پیگیری را نشان می‌دهد
        $this->get(route('order.success', $order->order_number))
            ->assertOk()
            ->assertSee('شماره پیگیری بانکی');
    }

    public function test_cancelled_payment_can_be_retried(): void
    {
        $order = $this->checkoutWith('zarinpal');

        $this->get(route('payment.start', $order->order_number))->assertRedirect();
        $authority = $order->refresh()->authority;

        // کاربر در درگاه انصراف می‌دهد
        $this->get(route('payment.callback', [
            'Authority' => $authority,
            'Status' => 'NOK',
        ]))->assertRedirect(route('order.success', $order->order_number));

        $order->refresh();
        $this->assertSame(Order::PAYMENT_CANCELLED, $order->payment_status);
        $this->assertFalse($order->isPaid());
        $this->assertTrue($order->canBePaidOnline());

        // تلاش مجدد → start مسیر جدیدی می‌سازد
        $this->get(route('payment.start', $order->order_number))->assertRedirect();
    }

    public function test_callback_with_unknown_authority_redirects_home(): void
    {
        $this->get(route('payment.callback', [
            'Authority' => 'NOT-EXISTS-1234',
            'Status' => 'OK',
        ]))->assertRedirect(route('shop.home'));
    }

    public function test_payment_start_is_not_accessible_for_strangers(): void
    {
        $order = Order::factory()->online()->create();

        // مهمان بدون سشن خرید → 404
        $this->get(route('payment.start', $order->order_number))->assertNotFound();
    }

    public function test_mock_routes_are_disabled_outside_mock_mode(): void
    {
        Config::set('zarinpal.mode', 'production');

        $order = Order::factory()->online()->create();

        $this->get(route('payment.mock.show', $order->order_number))->assertNotFound();
        $this->post(route('payment.mock.result', $order->order_number), [
            'result' => 'success',
            'authority' => 'x',
        ])->assertNotFound();
    }

    public function test_invalid_payment_method_is_rejected(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post('/cart/add/'.$product->slug, ['quantity' => 1])->assertRedirect();

        $this->post(route('checkout.store'), [
            'customer_name' => 'کاربر تستی',
            'customer_phone' => '09123456789',
            'customer_address' => 'تهران، خیابان آزادی، پلاک ۱۲',
            'payment_method' => 'bitcoin',
        ])->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
    }
}

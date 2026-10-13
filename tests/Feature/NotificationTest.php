<?php

namespace Tests\Feature;

use App\Contracts\OrderNotifier;
use App\Mail\OrderPlaced;
use App\Mail\OrderStatusChanged;
use App\Mail\PaymentConfirmed;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Notifications\NullNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /** ساخت سفارش با ایمیل مشخص — از سبد خرید واقعی */
    private function checkoutWith(?string $email, string $method = 'cod'): Order
    {
        $product = Product::factory()->create(['price' => 200000, 'stock' => 10]);

        $this->post('/cart/add/'.$product->slug, ['quantity' => 1])->assertRedirect();

        $this->post(route('checkout.store'), array_filter([
            'customer_name' => 'کاربر تستی',
            'customer_phone' => '09123456789',
            'customer_email' => $email,
            'customer_address' => 'تهران، خیابان آزادی، پلاک ۱۲',
            'payment_method' => $method,
        ]))->assertRedirect();

        return Order::firstOrFail();
    }

    /** مدیر ساختگی برای پنل ادمین */
    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_order_placed_email_is_sent_to_customer_email(): void
    {
        Mail::fake();

        $order = $this->checkoutWith('customer@example.com');

        Mail::assertSent(OrderPlaced::class, fn ($mail) => $mail->hasTo('customer@example.com')
            && $mail->order->is($order)
        );
    }

    public function test_order_placed_email_contains_order_summary(): void
    {
        Mail::fake();

        $order = $this->checkoutWith('customer@example.com');

        $mailable = new OrderPlaced($order->load('items'));

        $this->assertStringContainsString($order->order_number, $mailable->render());
        $this->assertStringContainsString($order->items->first()->product_title, $mailable->render());
    }

    public function test_no_email_is_sent_without_customer_email(): void
    {
        Mail::fake();

        $this->checkoutWith(null);

        Mail::assertNothingSent();
    }

    public function test_logged_in_user_email_is_used_as_fallback(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'member@example.com']);
        $this->actingAs($user);

        $order = $this->checkoutWith(null);

        $this->assertNull($order->customer_email);

        Mail::assertSent(OrderPlaced::class, fn ($mail) => $mail->hasTo('member@example.com'));
    }

    public function test_invalid_email_is_rejected_at_checkout(): void
    {
        $product = Product::factory()->create();

        $this->post('/cart/add/'.$product->slug, ['quantity' => 1])->assertRedirect();

        $this->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'customer_name' => 'کاربر تستی',
                'customer_phone' => '09123456789',
                'customer_email' => 'not-an-email',
                'customer_address' => 'تهران، خیابان آزادی، پلاک ۱۲',
                'payment_method' => 'cod',
            ])->assertSessionHasErrors('customer_email');
    }

    public function test_payment_confirmation_email_is_sent_once(): void
    {
        Mail::fake();

        $order = $this->checkoutWith('customer@example.com', 'zarinpal');

        // شروع پرداخت → درگاه mock
        $redirect = $this->get(route('payment.start', $order->order_number))
            ->assertRedirect()
            ->headers->get('Location');

        $this->get($redirect)->assertOk();

        // پرداخت موفق در درگاه آزمایشی
        $this->post(route('payment.mock.result', $order->order_number), [
            'result' => 'success',
            'authority' => $order->refresh()->authority,
        ])->assertRedirect();

        // بازگشت از درگاه → تایید نهایی
        $this->get(route('payment.callback', [
            'Authority' => $order->authority,
            'Status' => 'OK',
        ]))->assertRedirect(route('order.success', $order->order_number));

        $order->refresh();
        $this->assertTrue($order->isPaid());

        // فقط یک ایمیل تأیید پرداخت — نه بیشتر
        Mail::assertSent(PaymentConfirmed::class, 1);
        Mail::assertSent(PaymentConfirmed::class, fn ($mail) => $mail->order->is($order));

        // بازگشت دوباره به callback نباید ایمیل تکراری بفرستد
        $this->get(route('payment.callback', ['Authority' => $order->authority, 'Status' => 'OK']))
            ->assertRedirect();

        Mail::assertSent(PaymentConfirmed::class, 1);
    }

    public function test_status_change_email_is_sent_to_customer(): void
    {
        Mail::fake();

        $order = $this->checkoutWith('customer@example.com');

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_SHIPPED])
            ->assertRedirect();

        Mail::assertSent(OrderStatusChanged::class, function ($mail) use ($order) {
            return $mail->hasTo('customer@example.com')
                && $mail->order->is($order)
                && $mail->oldStatus === Order::STATUS_PENDING
                && $mail->order->status === Order::STATUS_SHIPPED
                && str_contains($mail->envelope()->subject, 'ارسال شده');
        });
    }

    public function test_no_status_email_when_status_stays_the_same(): void
    {
        Mail::fake();

        $order = $this->checkoutWith('customer@example.com');

        // همان وضعیت فعلی (pending) — نباید ایمیلی ارسال شود
        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_PENDING])
            ->assertRedirect();

        Mail::assertNotSent(OrderStatusChanged::class);
    }

    public function test_notifications_can_be_disabled_via_config(): void
    {
        Mail::fake();
        Config::set('shop.notifications.enabled', false);

        $order = $this->checkoutWith('customer@example.com');

        // اتصال قرارداد باید به NullNotifier برود
        $this->assertInstanceOf(
            NullNotifier::class,
            app(OrderNotifier::class),
        );

        Mail::assertNothingSent();
    }

    public function test_mail_send_failure_never_breaks_checkout(): void
    {
        Mail::fake();
        // شبیه‌سازی خطای ارسال — ثبت سفارش نباید شکست بخورد
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $product = Product::factory()->create(['price' => 100000, 'stock' => 5]);

        $this->post('/cart/add/'.$product->slug, ['quantity' => 1])->assertRedirect();

        $this->post(route('checkout.store'), [
            'customer_name' => 'کاربر تستی',
            'customer_phone' => '09123456789',
            'customer_email' => 'customer@example.com',
            'customer_address' => 'تهران، خیابان آزادی، پلاک ۱۲',
            'payment_method' => 'cod',
        ])->assertRedirect(route('order.success', Order::firstOrFail()->order_number));

        $this->assertDatabaseCount('orders', 1);
    }
}

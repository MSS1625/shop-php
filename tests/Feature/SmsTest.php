<?php

namespace Tests\Feature;

use App\Contracts\OrderNotifier;
use App\Contracts\SmsGateway;
use App\Exceptions\SmsGatewayException;
use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Notifications\CompositeNotifier;
use App\Services\Notifications\MailNotifier;
use App\Services\Notifications\NullNotifier;
use App\Services\Sms\KavenegarSmsGateway;
use App\Services\Sms\LogSmsGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SmsTest extends TestCase
{
    use RefreshDatabase;

    /** ساخت سفارش واقعی از سبد خرید — شماره موبایل الزامی فرم */
    private function checkout(string $method = 'cod'): Order
    {
        $product = Product::factory()->create(['price' => 200000, 'stock' => 10]);

        $this->post('/cart/add/'.$product->slug, ['quantity' => 1])->assertRedirect();

        $this->post(route('checkout.store'), [
            'customer_name' => 'کاربر تستی',
            'customer_phone' => '09123456789',
            'customer_email' => 'customer@example.com',
            'customer_address' => 'تهران، خیابان آزادی، پلاک ۱۲',
            'payment_method' => $method,
        ])->assertRedirect();

        return Order::firstOrFail();
    }

    /** مدیر ساختگی برای پنل ادمین */
    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_sms_is_sent_when_channel_enabled(): void
    {
        Config::set('shop.notifications.channels', ['sms']);
        $gateway = $this->mock(SmsGateway::class);

        $order = $this->checkout();

        $gateway->shouldHaveReceived('send')->once()->withArgs(
            fn (string $mobile, string $message) => $mobile === '09123456789'
                && str_contains($message, $order->order_number)
        );
    }

    public function test_order_placed_sms_contains_amount_in_persian_digits(): void
    {
        Config::set('shop.notifications.channels', ['sms']);
        $gateway = $this->mock(SmsGateway::class);

        $order = $this->checkout();

        $gateway->shouldHaveReceived('send')->once()->withArgs(
            fn (string $mobile, string $message) => str_contains($message, fa_price($order->total))
                && str_contains($message, 'تومان')
        );
    }

    public function test_no_sms_when_channel_is_mail_only(): void
    {
        Config::set('shop.notifications.channels', ['mail']);
        Mail::fake();
        $gateway = $this->mock(SmsGateway::class);

        $this->checkout();

        $gateway->shouldNotHaveReceived('send');
        Mail::assertSent(OrderPlaced::class);
    }

    public function test_sms_and_email_are_both_sent_when_both_channels_enabled(): void
    {
        Config::set('shop.notifications.channels', ['mail', 'sms']);
        Mail::fake();
        $gateway = $this->mock(SmsGateway::class);

        $order = $this->checkout();

        // زنجیرهٔ کانال‌ها باید هر دو اطلاع‌رسانی را انجام دهد
        $this->assertInstanceOf(CompositeNotifier::class, app(OrderNotifier::class));

        Mail::assertSent(OrderPlaced::class, fn ($mail) => $mail->hasTo('customer@example.com'));
        $gateway->shouldHaveReceived('send')->once()->withArgs(
            fn (string $mobile) => $mobile === '09123456789'
        );
    }

    public function test_sms_failure_never_breaks_checkout(): void
    {
        Config::set('shop.notifications.channels', ['sms']);
        $this->mock(SmsGateway::class)
            ->shouldReceive('send')
            ->once()
            ->andThrow(new SmsGatewayException('Kavenegar is down'));

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

    public function test_sms_failure_does_not_stop_email_in_composite(): void
    {
        Config::set('shop.notifications.channels', ['mail', 'sms']);
        Mail::fake();
        $this->mock(SmsGateway::class)
            ->shouldReceive('send')
            ->once()
            ->andThrow(new SmsGatewayException('insufficient credit'));

        $this->checkout();

        // خطای پیامک نباید ایمیل را متوقف کند
        Mail::assertSent(OrderPlaced::class, fn ($mail) => $mail->hasTo('customer@example.com'));
    }

    public function test_payment_confirmed_sms_contains_ref_id(): void
    {
        Config::set('shop.notifications.channels', ['sms']);
        $messages = [];
        $this->mock(SmsGateway::class)
            ->shouldReceive('send')
            ->andReturnUsing(function (string $mobile, string $message) use (&$messages) {
                $messages[] = [$mobile, $message];
            });

        $order = $this->checkout('zarinpal');

        // شروع پرداخت → درگاه mock → پرداخت موفق
        $redirect = $this->get(route('payment.start', $order->order_number))
            ->assertRedirect()->headers->get('Location');
        $this->get($redirect)->assertOk();

        $this->post(route('payment.mock.result', $order->order_number), [
            'result' => 'success',
            'authority' => $order->refresh()->authority,
        ])->assertRedirect();

        $this->get(route('payment.callback', [
            'Authority' => $order->authority,
            'Status' => 'OK',
        ]))->assertRedirect(route('order.success', $order->order_number));

        $order->refresh();
        $this->assertTrue($order->isPaid());

        // دو پیام: ثبت سفارش + تأیید پرداخت — پیام دوم باید کد پیگیری را داشته باشد
        $this->assertCount(2, $messages);
        $this->assertSame('09123456789', $messages[1][0]);
        $this->assertStringContainsString(fa_num((string) $order->ref_id), $messages[1][1]);
    }

    public function test_status_changed_sms_contains_old_and_new_labels(): void
    {
        Config::set('shop.notifications.channels', ['sms']);
        $messages = [];
        $this->mock(SmsGateway::class)
            ->shouldReceive('send')
            ->andReturnUsing(function (string $mobile, string $message) use (&$messages) {
                $messages[] = [$mobile, $message];
            });

        $order = $this->checkout();

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_SHIPPED])
            ->assertRedirect();

        // دو پیام: ثبت سفارش + تغییر وضعیت — پیام دوم باید هر دو برچسب را داشته باشد
        $this->assertCount(2, $messages);
        $this->assertStringContainsString('در انتظار بررسی', $messages[1][1]);
        $this->assertStringContainsString('ارسال شده', $messages[1][1]);
    }

    public function test_no_sms_when_status_stays_the_same(): void
    {
        Config::set('shop.notifications.channels', ['sms']);
        $messages = [];
        $this->mock(SmsGateway::class)
            ->shouldReceive('send')
            ->andReturnUsing(function (string $mobile, string $message) use (&$messages) {
                $messages[] = [$mobile, $message];
            });

        $order = $this->checkout();

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_PENDING])
            ->assertRedirect();

        // فقط پیام ثبت سفارش — بدون پیام تغییر وضعیت
        $this->assertCount(1, $messages);
    }

    public function test_kavenegar_gateway_posts_to_kavenegar_api(): void
    {
        Http::fake(['api.kavenegar.com/*' => Http::response([
            'return' => ['status' => 200, 'message' => 'Success'],
            'entries' => [],
        ])]);

        $gateway = new KavenegarSmsGateway('test-api-key', '10004346');
        $gateway->send('09123456789', 'سلام');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.kavenegar.com/v1/test-api-key/sms/send.json')
                && $request['receptor'] === '09123456789'
                && $request['message'] === 'سلام'
                && $request['sender'] === '10004346';
        });
    }

    public function test_kavenegar_gateway_throws_on_api_error(): void
    {
        Http::fake(['api.kavenegar.com/*' => Http::response([
            'return' => ['status' => 418, 'message' => 'Insufficient credit'],
        ])]);

        $gateway = new KavenegarSmsGateway('test-api-key');

        $this->expectException(SmsGatewayException::class);
        $this->expectExceptionMessage('418');

        $gateway->send('09123456789', 'سلام');
    }

    public function test_kavenegar_gateway_throws_without_api_key(): void
    {
        $gateway = new KavenegarSmsGateway('');

        $this->expectException(SmsGatewayException::class);
        $this->expectExceptionMessage('KAVENEGAR_API_KEY');

        $gateway->send('09123456789', 'سلام');
    }

    public function test_sms_driver_resolves_to_configured_gateway(): void
    {
        Config::set('shop.notifications.sms.driver', 'log');
        $this->assertInstanceOf(LogSmsGateway::class, app(SmsGateway::class));

        Config::set('shop.notifications.sms.driver', 'kavenegar');
        $this->app->forgetInstance(SmsGateway::class);
        $this->assertInstanceOf(KavenegarSmsGateway::class, app(SmsGateway::class));
    }

    public function test_unknown_channels_are_ignored(): void
    {
        Config::set('shop.notifications.channels', ['mail', 'telegram']);
        $this->assertInstanceOf(MailNotifier::class, app(OrderNotifier::class));

        Config::set('shop.notifications.channels', ['telegram']);
        $this->app->forgetInstance(OrderNotifier::class);
        $this->assertInstanceOf(NullNotifier::class, app(OrderNotifier::class));
    }

    public function test_disabled_notifications_still_bind_null_notifier(): void
    {
        Config::set('shop.notifications.enabled', false);
        Config::set('shop.notifications.channels', ['mail', 'sms']);

        $this->assertInstanceOf(NullNotifier::class, app(OrderNotifier::class));
    }
}

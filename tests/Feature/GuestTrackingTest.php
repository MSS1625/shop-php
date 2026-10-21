<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GuestTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function guestOrder(array $attributes = []): Order
    {
        $order = Order::factory()->create(array_merge([
            'order_number' => 'DS-261021-A1B2',
            'customer_phone' => '09123456789',
        ], $attributes));

        $order->items()->create([
            'product_id' => Product::factory()->create()->id,
            'product_title' => 'کابل شبکه Cat6',
            'unit_price' => 150000,
            'quantity' => 2,
        ]);

        return $order->fresh(['items']);
    }

    private function lookup(array $overrides = []): TestResponse
    {
        return $this->post(route('shop.track.lookup'), array_merge([
            'order_number' => 'DS-261021-A1B2',
            'phone' => '09123456789',
        ], $overrides));
    }

    public function test_track_page_is_publicly_accessible(): void
    {
        $this->get(route('shop.track'))
            ->assertOk()
            ->assertSee('پیگیری سفارش')
            ->assertSee('شماره سفارش');
    }

    public function test_navbar_shows_tracking_link(): void
    {
        $this->get(route('shop.home'))
            ->assertOk()
            ->assertSee(route('shop.track'));
    }

    public function test_guest_order_can_be_tracked_with_order_number_and_phone(): void
    {
        $order = $this->guestOrder();

        $this->lookup()
            ->assertRedirect(route('shop.track'));

        $this->get(route('shop.track'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('کابل شبکه Cat6')
            ->assertSee('در انتظار بررسی');
    }

    public function test_registered_user_orders_can_be_tracked_too(): void
    {
        $user = User::factory()->create();
        $order = $this->guestOrder(['user_id' => $user->id, 'order_number' => 'DS-261021-USER']);

        $this->lookup(['order_number' => 'DS-261021-USER'])
            ->assertRedirect(route('shop.track'));

        $this->get(route('shop.track'))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_tracking_form_requires_both_fields(): void
    {
        $this->post(route('shop.track.lookup'), [])
            ->assertSessionHasErrors(['order_number', 'phone']);
    }

    public function test_wrong_phone_is_rejected(): void
    {
        $order = $this->guestOrder();

        $response = $this->lookup(['phone' => '09999999999'])
            ->assertRedirect(route('shop.track'))
            ->assertSessionHas('track_error');

        // هیچ داده‌ای از سفارش فاش نمی‌شود
        // (شماره‌ای که خود کاربر تایپ کرده در فرم می‌ماند — اما نتیجه رندر نمی‌شود)
        $this->get(route('shop.track'))
            ->assertOk()
            ->assertDontSee('کابل شبکه Cat6')
            ->assertDontSee('در انتظار بررسی')
            ->assertDontSee('اقلام سفارش');
    }

    public function test_wrong_order_number_is_rejected(): void
    {
        $order = $this->guestOrder();

        $this->lookup(['order_number' => 'DS-261021-ZZZZ'])
            ->assertRedirect(route('shop.track'))
            ->assertSessionHas('track_error');

        $this->get(route('shop.track'))
            ->assertOk()
            ->assertDontSee('اقلام سفارش')
            ->assertDontSee('در انتظار بررسی');
    }

    public function test_error_message_is_identical_for_wrong_phone_and_wrong_order_number(): void
    {
        // پیام یکسان = هیچ سرنخی دربارهٔ وجود/عدم وجود شماره سفارش لو نمی‌رود
        $this->guestOrder();

        $wrongPhone = $this->lookup(['phone' => '09999999999'])->assertRedirect();
        $messageA = $wrongPhone->getSession()?->get('track_error');

        $wrongNumber = $this->lookup(['order_number' => 'DS-261021-ZZZZ'])->assertRedirect();
        $messageB = $wrongNumber->getSession()?->get('track_error');

        $this->assertNotNull($messageA);
        $this->assertNotNull($messageB);
        $this->assertSame($messageA, $messageB);
    }

    public function test_persian_digits_are_normalized_on_lookup(): void
    {
        // کاربر با کیبورد فارسی هم باید بتواند پیگیری کند
        $this->guestOrder();

        $this->lookup([
            'order_number' => 'DS-۲۶۱۰۲۱-A1B2',
            'phone' => '۰۹۱۲۳۴۵۶۷۸۹',
        ])->assertRedirect(route('shop.track'));

        $this->get(route('shop.track'))
            ->assertOk()
            ->assertSee('DS-261021-A1B2');
    }

    public function test_tracked_session_expires_after_30_minutes(): void
    {
        $this->guestOrder();
        $this->lookup()->assertRedirect();

        Carbon::setTestNow(now()->addMinutes(31));

        $this->get(route('shop.track'))
            ->assertOk()
            ->assertDontSee('کابل شبکه Cat6');

        Carbon::setTestNow();
    }

    public function test_lookup_is_rate_limited(): void
    {
        $this->guestOrder();

        foreach (range(1, 10) as $i) {
            $this->lookup(['order_number' => 'DS-261021-WRONG'])
                ->assertRedirect(); // پیام خطا — نه 429
        }

        // یازدهمین تلاش: قفل ضد brute-force
        $this->lookup(['order_number' => 'DS-261021-A1B2'])
            ->assertStatus(429);
    }

    public function test_cancelled_order_shows_cancelled_state(): void
    {
        $this->guestOrder(['status' => Order::STATUS_CANCELLED]);
        $this->lookup()->assertRedirect();

        $this->get(route('shop.track'))
            ->assertOk()
            ->assertSee('این سفارش لغو شده است');
    }

    public function test_tracking_grants_payment_retry_access(): void
    {
        // سفارش آنلاینِ پرداخت‌نشده — قبل از پیگیری، درگاه 404 می‌دهد
        $this->guestOrder([
            'order_number' => 'DS-261021-PAY1',
            'payment_method' => Order::METHOD_ZARINPAL,
            'payment_status' => Order::PAYMENT_PENDING,
        ]);

        $this->get(route('payment.start', 'DS-261021-PAY1'))->assertNotFound();

        // بعد از پیگیری موفق — همان مکانیزم viewed_orders تسویه‌حساب
        $this->lookup(['order_number' => 'DS-261021-PAY1'])->assertRedirect();

        $this->get(route('payment.start', 'DS-261021-PAY1'))
            ->assertRedirect(); // هدایت به درگاه (mock)
    }

    public function test_tracking_page_hides_customer_address(): void
    {
        $this->guestOrder(['customer_address' => 'تهران، خیابان خیلی مخفی، پلاک ۷']);
        $this->lookup()->assertRedirect();

        // حداقلِ دادهٔ لازم — آدرس و مشخصات حساس نمایش داده نمی‌شوند
        $this->get(route('shop.track'))
            ->assertOk()
            ->assertDontSee('خیابان خیلی مخفی');
    }

    public function test_phone_is_masked_on_tracking_result(): void
    {
        $this->guestOrder(['customer_phone' => '09121112233']);
        $this->lookup(['phone' => '09121112233'])->assertRedirect();

        $this->get(route('shop.track'))
            ->assertOk()
            ->assertSee('۰۹۱۲***۲۳۳')
            ->assertDontSee('09121112233');
    }

    public function test_order_success_page_links_guests_to_tracking(): void
    {
        $order = $this->guestOrder();

        // مهمانی که همین الان خرید کرده (viewed_orders در سشن ثبت شده)
        $this->withSession(['viewed_orders' => [$order->order_number]])
            ->get(route('order.success', $order->order_number))
            ->assertOk()
            ->assertSee(route('shop.track'))
            ->assertSee('برای پیگیری بعدی');
    }
}

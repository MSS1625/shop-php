<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Exceptions\PaymentGatewayException;
use App\Models\Order;
use Illuminate\Support\Str;

/**
 * درگاه آزمایشی محلی — شبیه‌ساز کامل چرخه پرداخت
 *
 * هیچ درخواستی به زرین‌پال زده نمی‌شود؛ کاربر به یک صفحه محلی هدایت می‌شود
 * و می‌تواند نتیجه پرداخت (موفق/لغو) را خودش انتخاب کند. کل مسیر کد —
 * از request تا callback و verify — دقیقاً همان مسیر درگاه واقعی است؛
 * بنابراین برای توسعه و تست پوشه‌ای بدون اینترنت کاملا مناسب است.
 */
class MockGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'درگاه آزمایشی';
    }

    public function request(Order $order): string
    {
        $authority = 'MOCK-'.strtoupper(Str::random(40));

        $order->forceFill(['authority' => $authority])->save();

        return route('payment.mock.show', [
            'order' => $order->order_number,
            'authority' => $authority,
        ]);
    }

    public function verify(Order $order): int
    {
        if (blank($order->authority) || ! str_starts_with($order->authority, 'MOCK-')) {
            throw new PaymentGatewayException('شناسه ارجاع این سفارش یافت نشد.');
        }

        // شبیه‌سازی شماره پیگیری بانکی
        return (int) ('9'.random_int(100000000, 999999999));
    }
}

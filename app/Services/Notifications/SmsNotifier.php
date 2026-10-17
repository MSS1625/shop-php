<?php

namespace App\Services\Notifications;

use App\Contracts\OrderNotifier;
use App\Contracts\SmsGateway;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * اطلاع‌رسانی پیامکی رویدادهای سفارش —
 * پیام‌های کوتاه فارسی برای همان سه رویداد ایمیل؛
 * خطای ارسال هرگز جریان اصلی (ثبت سفارش/پرداخت) را نمی‌شکند و فقط لاگ می‌شود
 */
class SmsNotifier implements OrderNotifier
{
    public function __construct(private readonly SmsGateway $gateway) {}

    public function orderPlaced(Order $order): void
    {
        $this->send(
            $order,
            'دیجی‌شاپ: سفارش شما با کد '.$order->order_number.' ثبت شد. مبلغ: '
            .fa_price($order->total).' تومان. سپاس از خرید شما.',
            'orderPlaced',
        );
    }

    public function paymentConfirmed(Order $order): void
    {
        $this->send(
            $order,
            'دیجی‌شاپ: پرداخت سفارش '.$order->order_number.' تأیید شد. کد پیگیری بانکی: '
            .fa_num((string) $order->ref_id).'.',
            'paymentConfirmed',
        );
    }

    public function statusChanged(Order $order, string $oldStatus): void
    {
        $oldLabel = Order::STATUS_LABELS[$oldStatus] ?? $oldStatus;

        $this->send(
            $order,
            'دیجی‌شاپ: وضعیت سفارش '.$order->order_number.' از «'.$oldLabel.'» به «'
            .$order->statusLabel().'» تغییر کرد.',
            'statusChanged',
        );
    }

    /**
     * ارسال با محافظت دوگانه: بدون موبایل → هیچ؛ خطای ارسال → فقط لاگ
     */
    private function send(Order $order, string $message, string $event): void
    {
        $mobile = $order->recipientMobile();

        if (! $mobile) {
            return;
        }

        try {
            $this->gateway->send($mobile, $message);
        } catch (Throwable $e) {
            // پیامک جزئی از جریان اصلی نیست — خطایش نباید سفارش یا پرداخت را متوقف کند
            Log::warning("failed to send `{$event}` sms for order {$order->order_number}: {$e->getMessage()}");
        }
    }
}

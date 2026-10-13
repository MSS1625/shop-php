<?php

namespace App\Services\Notifications;

use App\Contracts\OrderNotifier;
use App\Mail\OrderPlaced;
use App\Mail\OrderStatusChanged;
use App\Mail\PaymentConfirmed;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * اطلاع‌رسانی ایمیلی رویدادهای سفارش —
 * خطای ارسال هرگز جریان اصلی (ثبت سفارش/پرداخت) را نمی‌شکند و فقط لاگ می‌شود
 */
class MailNotifier implements OrderNotifier
{
    public function orderPlaced(Order $order): void
    {
        $this->send($order, fn (string $to) => Mail::to($to)->send(new OrderPlaced($order)), 'orderPlaced');
    }

    public function paymentConfirmed(Order $order): void
    {
        $this->send($order, fn (string $to) => Mail::to($to)->send(new PaymentConfirmed($order)), 'paymentConfirmed');
    }

    public function statusChanged(Order $order, string $oldStatus): void
    {
        $this->send(
            $order,
            fn (string $to) => Mail::to($to)->send(new OrderStatusChanged($order, $oldStatus)),
            'statusChanged',
        );
    }

    /**
     * ارسال با محافظت دوگانه: بدون ایمیل → هیچ؛ خطای ارسال → فقط لاگ
     */
    private function send(Order $order, callable $mailTo, string $event): void
    {
        $to = $order->recipientEmail();

        if (! $to) {
            return;
        }

        try {
            $mailTo($to);
        } catch (Throwable $e) {
            // اطلاع‌رسانی جزئی از جریان اصلی نیست — خطایش نباید سفارش یا پرداخت را متوقف کند
            Log::warning("failed to send `{$event}` email for order {$order->order_number}: {$e->getMessage()}");
        }
    }
}

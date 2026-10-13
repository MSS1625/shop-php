<?php

namespace App\Contracts;

use App\Models\Order;

/**
 * قرارداد اطلاع‌رسانی رویدادهای سفارش —
 * پیاده‌سازی پیش‌فرض: ایمیل (MailNotifier)؛ قابل تعویض با SMS یا هر کانال دیگر
 */
interface OrderNotifier
{
    /** سفارش جدید ثبت شد (پرداخت هنوز انجام نشده) */
    public function orderPlaced(Order $order): void;

    /** پرداخت آنلاین سفارش تأیید شد */
    public function paymentConfirmed(Order $order): void;

    /** وضعیت سفارش توسط مدیر تغییر کرد */
    public function statusChanged(Order $order, string $oldStatus): void;
}

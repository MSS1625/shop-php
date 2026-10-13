<?php

namespace App\Services\Notifications;

use App\Contracts\OrderNotifier;
use App\Models\Order;

/**
 * پیاده‌سازی خاموش قرارداد اطلاع‌رسانی —
 * وقتی اطلاع‌رسانی در تنظیمات خاموش است، هیچ کاری انجام نمی‌شود
 */
class NullNotifier implements OrderNotifier
{
    public function orderPlaced(Order $order): void {}

    public function paymentConfirmed(Order $order): void {}

    public function statusChanged(Order $order, string $oldStatus): void {}
}

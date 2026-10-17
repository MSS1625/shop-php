<?php

namespace App\Services\Notifications;

use App\Contracts\OrderNotifier;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * زنجیرهٔ کانال‌های اطلاع‌رسانی —
 * هر رویداد به همهٔ کانال‌های فعال (مثلاً ایمیل + پیامک) می‌رسد؛
 * خطای یک کانال هرگز کانال‌های بعدی را متوقف نمی‌کند
 */
class CompositeNotifier implements OrderNotifier
{
    /** @param OrderNotifier[] $notifiers */
    public function __construct(private readonly array $notifiers) {}

    public function orderPlaced(Order $order): void
    {
        $this->dispatch('orderPlaced', $order, fn (OrderNotifier $n) => $n->orderPlaced($order));
    }

    public function paymentConfirmed(Order $order): void
    {
        $this->dispatch('paymentConfirmed', $order, fn (OrderNotifier $n) => $n->paymentConfirmed($order));
    }

    public function statusChanged(Order $order, string $oldStatus): void
    {
        $this->dispatch('statusChanged', $order, fn (OrderNotifier $n) => $n->statusChanged($order, $oldStatus));
    }

    /** اجرای رویداد روی همهٔ کانال‌ها با جداسازی خطا */
    private function dispatch(string $event, Order $order, callable $fire): void
    {
        foreach ($this->notifiers as $notifier) {
            try {
                $fire($notifier);
            } catch (Throwable $e) {
                Log::warning("notification channel failed for `{$event}` on order {$order->order_number}: {$e->getMessage()}");
            }
        }
    }
}

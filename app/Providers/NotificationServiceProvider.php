<?php

namespace App\Providers;

use App\Contracts\OrderNotifier;
use App\Services\Notifications\MailNotifier;
use App\Services\Notifications\NullNotifier;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * ثبت سرویس اطلاع‌رسانی سفارش بر اساس تنظیمات (singleton — یک نمونه در هر درخواست)
     */
    public function register(): void
    {
        $this->app->singleton(OrderNotifier::class, function () {
            return config('shop.notifications.enabled')
                ? new MailNotifier
                : new NullNotifier;
        });
    }
}

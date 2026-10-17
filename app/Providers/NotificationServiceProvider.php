<?php

namespace App\Providers;

use App\Contracts\OrderNotifier;
use App\Contracts\SmsGateway;
use App\Services\Notifications\CompositeNotifier;
use App\Services\Notifications\MailNotifier;
use App\Services\Notifications\NullNotifier;
use App\Services\Notifications\SmsNotifier;
use App\Services\Sms\KavenegarSmsGateway;
use App\Services\Sms\LogSmsGateway;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * ثبت سرویس اطلاع‌رسانی سفارش بر اساس تنظیمات (singleton — یک نمونه در هر درخواست)
     *
     * کانال‌های فعال از config خوانده می‌شوند: mail و/یا sms.
     * افزودن کانال جدید یعنی یک implementation تازه + یک خط در همین binding —
     * کنترلرها و بقیهٔ کد از این تغییر بی‌خبر می‌مانند.
     */
    public function register(): void
    {
        $this->app->singleton(SmsGateway::class, function () {
            return match (config('shop.notifications.sms.driver')) {
                'kavenegar' => new KavenegarSmsGateway(
                    (string) config('shop.notifications.sms.kavenegar.api_key'),
                    config('shop.notifications.sms.kavenegar.from'),
                ),
                default => new LogSmsGateway,
            };
        });

        $this->app->singleton(OrderNotifier::class, function ($app) {
            if (! config('shop.notifications.enabled')) {
                return new NullNotifier;
            }

            $notifiers = [];

            foreach ((array) config('shop.notifications.channels', ['mail']) as $channel) {
                $notifiers[] = match ($channel) {
                    'mail' => new MailNotifier,
                    'sms' => new SmsNotifier($app->make(SmsGateway::class)),
                    default => null,
                };
            }

            $notifiers = array_values(array_filter($notifiers));

            if ($notifiers === []) {
                return new NullNotifier;
            }

            return count($notifiers) === 1
                ? $notifiers[0]
                : new CompositeNotifier($notifiers);
        });
    }
}

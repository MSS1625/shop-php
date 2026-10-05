<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Services\Payments\MockGateway;
use App\Services\Payments\ZarinPalGateway;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * ثبت درگاه پرداخت بر اساس تنظیمات (singleton — یک نمونه در هر درخواست)
     */
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, function () {
            $mode = (string) config('zarinpal.mode');

            // درگاه آزمایشی محلی — بدون هیچ ارتباطی با زرین‌پال
            if ($mode === 'mock') {
                return new MockGateway;
            }

            $baseUrl = match ($mode) {
                'sandbox' => 'https://sandbox.zarinpal.com',
                default => 'https://payment.zarinpal.com',
            };

            return new ZarinPalGateway(
                (string) config('zarinpal.merchant_id'),
                $baseUrl,
                (int) config('zarinpal.amount_multiplier'),
            );
        });
    }
}

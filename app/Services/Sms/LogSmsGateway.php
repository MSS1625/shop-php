<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\Log;

/**
 * درگاه پیامک آزمایشی محلی — پیام فقط در لاگ ثبت می‌شود
 *
 * همان نقش MockGateway در پرداخت: برای توسعه و تست، بدون هزینه واقعی؛
 * کل مسیر کد (ساخت پیام و ارسال) دقیقاً مثل درگاه واقعی اجرا می‌شود.
 */
class LogSmsGateway implements SmsGateway
{
    public function name(): string
    {
        return 'درگاه پیامک آزمایشی';
    }

    public function send(string $mobile, string $message): void
    {
        Log::info("[sms:{$mobile}] {$message}");
    }
}

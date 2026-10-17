<?php

namespace App\Contracts;

use App\Exceptions\SmsGatewayException;

/**
 * قرارداد درگاه‌های پیامک
 *
 * هر درگاه فقط باید بتواند یک پیام کوتاه به یک شماره موبایل بفرستد؛
 * ساخت متن پیام و اتصال به رویدادهای سفارش (SmsNotifier) ربطی به این قرارداد ندارد.
 */
interface SmsGateway
{
    /**
     * نام درگاه برای لاگ و نمایش
     */
    public function name(): string;

    /**
     * ارسال پیام کوتاه به شماره موبایل (فرمت 09xxxxxxxxx)
     *
     * @throws SmsGatewayException در صورت خطای ارسال
     */
    public function send(string $mobile, string $message): void;
}

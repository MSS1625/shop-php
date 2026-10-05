<?php

namespace App\Contracts;

use App\Exceptions\PaymentGatewayException;
use App\Exceptions\PaymentVerificationFailedException;
use App\Models\Order;

/**
 * قرارداد درگاه‌های پرداخت آنلاین
 *
 * هر درگاه باید بتواند درخواست پرداخت بسازد و نتیجه تراکنش را تایید کند.
 * پیاده‌سازی‌ها باید state خود را روی خودِ سفارش (ستون authority) نگه دارند.
 */
interface PaymentGateway
{
    /**
     * نام درگاه برای نمایش به کاربر
     */
    public function name(): string;

    /**
     * ساخت درخواست پرداخت برای سفارش.
     *
     * شناسه Authority روی سفارش ذخیره شده و آدرس صف پرداخت برگردانده می‌شود.
     *
     * @throws PaymentGatewayException اگر درگاه درخواست را نپذیرد
     */
    public function request(Order $order): string;

    /**
     * تایید نتیجه تراکنش پس از بازگشت از درگاه.
     *
     * در صورت موفقیت شماره پیگیری بانکی (RefID) برگردانده می‌شود.
     *
     * @throws PaymentVerificationFailedException اگر تراکنش تایید نشود
     * @throws PaymentGatewayException در صورت خطای ارتباط با درگاه
     */
    public function verify(Order $order): int;
}

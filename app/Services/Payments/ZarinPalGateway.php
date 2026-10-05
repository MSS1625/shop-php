<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Exceptions\PaymentGatewayException;
use App\Exceptions\PaymentVerificationFailedException;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

/**
 * درگاه پرداخت زرین‌پال — API نسخه ۴
 *
 * مستندات: https://docs.zarinpal.com
 * - مبلغ در این API «ریال» است؛ مبالغ فروشگاه به‌صورت پیش‌فرض تومان‌اند
 *   و با ضریب amountMultiplier به ریال تبدیل می‌شوند.
 * - کد 100 = تایید شد، 101 = قبلا تایید شده (موفق)، سایر = خطا
 */
class ZarinPalGateway implements PaymentGateway
{
    public function __construct(
        protected string $merchantId,
        protected string $baseUrl,
        protected int $amountMultiplier = 10,
    ) {}

    public function name(): string
    {
        return 'زرین‌پال';
    }

    public function request(Order $order): string
    {
        $this->assertConfigured();

        $response = Http::timeout(20)
            ->acceptJson()
            ->post($this->url('/pg/v4/payment/request.json'), [
                'merchant_id' => $this->merchantId,
                'amount' => $this->gatewayAmount($order),
                'callback_url' => route('payment.callback'),
                'description' => 'پرداخت سفارش '.$order->order_number.' — '.config('app.name'),
                'metadata' => [
                    'mobile' => $order->customer_phone,
                    'order_id' => $order->order_number,
                ],
            ])
            ->throw()
            ->json();

        $code = (int) ($response['data']['code'] ?? 0);
        $authority = (string) ($response['data']['authority'] ?? '');

        if ($code !== 100 || $authority === '') {
            throw PaymentGatewayException::fromZarinpalCode(
                $code !== 0 ? $code : null,
                $response['errors'] ?? []
            );
        }

        // ذخیره شناسه ارجاع برای تطبیق در بازگشت از درگاه
        $order->forceFill(['authority' => $authority])->save();

        return $this->url('/pg/StartPay/'.$authority);
    }

    public function verify(Order $order): int
    {
        $this->assertConfigured();

        if (blank($order->authority)) {
            throw new PaymentGatewayException('شناسه ارجاع این سفارش یافت نشد.');
        }

        $response = Http::timeout(20)
            ->acceptJson()
            ->post($this->url('/pg/v4/payment/verify.json'), [
                'merchant_id' => $this->merchantId,
                'amount' => $this->gatewayAmount($order),
                'authority' => $order->authority,
            ])
            ->throw()
            ->json();

        $code = (int) ($response['data']['code'] ?? 0);
        $refId = (int) ($response['data']['ref_id'] ?? 0);

        // 100 = تایید شد | 101 = قبلا تایید شده — هر دو موفقند
        if (! in_array($code, [100, 101], true)) {
            throw PaymentVerificationFailedException::fromZarinpalCode($code !== 0 ? $code : null);
        }

        return $refId;
    }

    /**
     * تبدیل مبلغ سفارش (تومان) به واحد درگاه (ریال)
     */
    protected function gatewayAmount(Order $order): int
    {
        return (int) ($order->total * $this->amountMultiplier);
    }

    protected function url(string $path): string
    {
        return rtrim($this->baseUrl, '/').$path;
    }

    /**
     * بررسی تنظیم بودن شناسه پذیرنده با پیام واضح فارسی
     */
    protected function assertConfigured(): void
    {
        if (blank($this->merchantId)) {
            throw new PaymentGatewayException(
                'شناسه پذیرنده زرین‌پال (ZARINPAL_MERCHANT_ID) تنظیم نشده است. '
                .'آن را از پنل زرین‌پال دریافت و در فایل .env وارد کنید.'
            );
        }
    }
}

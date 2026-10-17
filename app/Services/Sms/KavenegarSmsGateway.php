<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use App\Exceptions\SmsGatewayException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * درگاه پیامک کاوه‌نگار — ارسال واقعی از طریق REST API
 *
 * کلید API از پنل kavenegar.com گرفته می‌شود؛ در صورت نبود کلید یا خطای
 * پنل، SmsGatewayException پرتاب می‌شود که در لایهٔ بالاتر فقط لاگ می‌شود.
 */
class KavenegarSmsGateway implements SmsGateway
{
    public function __construct(
        private readonly string $apiKey,
        private readonly ?string $from = null,
    ) {}

    public function name(): string
    {
        return 'کاوه‌نگار';
    }

    public function send(string $mobile, string $message): void
    {
        if ($this->apiKey === '') {
            throw new SmsGatewayException('کلید API کاوه‌نگار تنظیم نشده است (KAVENEGAR_API_KEY).');
        }

        try {
            $response = Http::timeout(10)->asForm()->post(
                "https://api.kavenegar.com/v1/{$this->apiKey}/sms/send.json",
                array_filter([
                    'receptor' => $mobile,
                    'message' => $message,
                    'sender' => $this->from,
                ]),
            );
        } catch (ConnectionException $e) {
            throw new SmsGatewayException('ارتباط با کاوه‌نگار برقرار نشد: '.$e->getMessage());
        }

        $status = (int) data_get($response->json(), 'return.status', 0);

        if ($status !== 200) {
            throw new SmsGatewayException('کاوه‌نگار ارسال را نپذیرفت (status '.$status.'): '
                .(string) data_get($response->json(), 'return.message', 'بدون توضیح'));
        }
    }
}

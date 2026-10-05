<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * پرداخت انجام شده ولی تایید نشده (Verify ناموفق)
 */
class PaymentVerificationFailedException extends RuntimeException
{
    public static function fromZarinpalCode(?int $code): static
    {
        $message = PaymentGatewayException::ZARINPAL_MESSAGES[$code] ?? 'تراکنش تایید نشد؛ پرداخت ناموفق است.';

        return new static($message, $code ?? 0);
    }
}

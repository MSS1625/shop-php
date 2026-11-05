<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * پرداخت انجام شده ولی تایید نشده (Verify ناموفق)
 */
class PaymentVerificationFailedException extends RuntimeException
{
    /** سازندهٔ نهایی — تا new static در فکتوری ایستا امن باشد */
    final public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function fromZarinpalCode(?int $code): static
    {
        $message = PaymentGatewayException::ZARINPAL_MESSAGES[$code] ?? 'تراکنش تایید نشد؛ پرداخت ناموفق است.';

        return new static($message, $code ?? 0);
    }
}

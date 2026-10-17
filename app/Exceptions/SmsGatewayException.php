<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * خطای درگاه پیامک — عدم پذیرش پیام، خطای ارتباطی یا کلید API نامعتبر
 */
class SmsGatewayException extends RuntimeException {}

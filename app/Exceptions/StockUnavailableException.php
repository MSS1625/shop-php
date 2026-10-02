<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * وقتی موجودی انبار برای تکمیل سفارش کافی نباشد
 */
class StockUnavailableException extends RuntimeException {}

<?php

// صبر برای آماده‌شدن دیتابیس داخل کانتینر — خروجی ۰ یعنی «وصل و آماده»
// (بوت سبک لاراول بدون کنسول کامل؛ فقط یک select 1)

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

try {
    DB::select('select 1');
} catch (Throwable) {
    exit(1);
}

exit(0);

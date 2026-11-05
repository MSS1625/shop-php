#!/usr/bin/env php
<?php

/**
 * ساخت بج پوشش تست از گزارش Clover — بدون سرویس خارجی
 *
 * phpunit با پوشش (pcov) اجرا می‌شود و clover.xml می‌سازد؛ این اسکریپت
 * درصد پوشش را از metrics گزارش می‌خواند، JSON قالب endpoint بج shields.io
 * را می‌سازد (تا CI آن را روی شاخهٔ coverage-badge پوش کند و README بج
 * زندهٔ «Coverage» داشته باشد) و درصد را هم در خروجی کنسول چاپ می‌کند.
 *
 * استفاده:
 *   php scripts/coverage-badge.php <clover.xml> [output.json]
 *
 * رنگ: قرمز < ۵۰٪ | زرد ≥ ۵۰٪ | سبز ≥ ۸۰٪
 * خروجی: «coverage: NN% (covered/total)» در stdout (exit 0) — یا خطا (exit 1)
 */
$clover = $argv[1] ?? null;
$output = $argv[2] ?? null;

if ($clover === null) {
    fwrite(STDERR, "استفاده: php scripts/coverage-badge.php <clover.xml> [output.json]\n");
    exit(1);
}

if (! is_file($clover) || ! is_readable($clover)) {
    fwrite(STDERR, "فایل Clover یافت نشد یا خوانا نیست: {$clover}\n");
    exit(1);
}

$xml = @simplexml_load_file($clover);

if ($xml === false) {
    fwrite(STDERR, "فایل Clover قابل خواندن نیست (XML نامعتبر): {$clover}\n");
    exit(1);
}

$metrics = $xml->xpath('/coverage/project/metrics');

if ($metrics === false || $metrics === []) {
    fwrite(STDERR, "در فایل Clover بخش metrics پیدا نشد — گزارش معتبر نیست.\n");
    exit(1);
}

$statements = (int) $metrics[0]['statements'];
$covered = (int) $metrics[0]['coveredstatements'];

if ($statements <= 0) {
    fwrite(STDERR, 'گزارش Clover statements=0 دارد — پوشش قابل محاسبه نیست (درایور پوشش فعال است؟).'.PHP_EOL);
    exit(1);
}

$percent = (int) round($covered / $statements * 100);
$color = $percent >= 80 ? 'green' : ($percent >= 50 ? 'yellow' : 'red');

$badge = [
    'schemaVersion' => 1,
    'label' => 'coverage',
    'message' => $percent.'%',
    'color' => $color,
];

if ($output !== null) {
    if (@file_put_contents($output, json_encode($badge, JSON_UNESCAPED_SLASHES).PHP_EOL) === false) {
        fwrite(STDERR, "نوشتن خروجی ممکن نشد: {$output}\n");
        exit(1);
    }
}

echo sprintf('coverage: %d%% (%d/%d statements) — badge: %s%s', $percent, $covered, $statements, $color, PHP_EOL);

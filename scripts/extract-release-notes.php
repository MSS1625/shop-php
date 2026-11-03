#!/usr/bin/env php
<?php

/**
 * استخراج یادداشت‌های انتشار یک نسخه از CHANGELOG.md
 *
 * CHANGELOG.md برای هر نسخه دو بخش دارد (🇬🇧 English و 🇮🇷 فارسی) که هر دو
 * با سرفصل «## [x.y.z] — تاریخ» شروع می‌شوند. این اسکریپت بدنهٔ هر دو بخش
 * نسخهٔ خواسته‌شده را پیدا می‌کند و پشت سر هم (با جداکنندهٔ ---) چاپ می‌کند —
 * دقیقاً همان چیزی که GitHub Release لازم دارد، بدون کپی‌وپیست دستی.
 *
 * استفاده:
 *   php scripts/extract-release-notes.php <version> [--file=CHANGELOG.md] [--title]
 *
 *   --title   به‌جای بدنه، متنِ اولین سرفصل «### …» بخش اول (انگلیسی) را
 *             چاپ می‌کند — برای عنوان Release
 *
 * خروجی: متن مارک‌داون (exit 0) — یا پیام خطا در stderr (exit 1)
 */
$version = $argv[1] ?? null;
$file = 'CHANGELOG.md';
$titleOnly = false;

foreach (array_slice($argv, 2) as $arg) {
    if ($arg === '--title') {
        $titleOnly = true;
    } elseif (str_starts_with($arg, '--file=')) {
        $file = substr($arg, strlen('--file='));
    } else {
        fwrite(STDERR, "آرگومان ناشناخته: {$arg}\n");
        exit(1);
    }
}

if ($version === null || $version === '') {
    fwrite(STDERR, "استفاده: php scripts/extract-release-notes.php <version> [--file=CHANGELOG.md] [--title]\n");
    exit(1);
}

if (! is_file($file) || ! is_readable($file)) {
    fwrite(STDERR, "فایل یافت نشد یا خوانا نیست: {$file}\n");
    exit(1);
}

$heading = '/^## \['.preg_quote($version, '/').'\]/';
$lines = file($file, FILE_IGNORE_NEW_LINES) ?: [];

$sections = [];
$current = null;
$buffer = [];

// بدنهٔ هر بخش = خطوط بعد از سرفصل «## [x.y.z] …» تا سرفصل «## » بعدی
foreach ($lines as $line) {
    if (preg_match($heading, $line)) {
        if ($current !== null) {
            $sections[] = $buffer;
        }
        $current = $line;
        $buffer = [];

        continue;
    }

    if ($current !== null && str_starts_with($line, '## ')) {
        $sections[] = $buffer;
        $current = null;
        $buffer = [];

        continue;
    }

    if ($current !== null) {
        $buffer[] = $line;
    }
}

if ($current !== null) {
    $sections[] = $buffer;
}

if ($sections === []) {
    fwrite(STDERR, "نسخه «{$version}» در {$file} پیدا نشد — سرفصل «## [{$version}]» وجود ندارد.\n");
    exit(1);
}

$trim = fn (array $lines): array => array_values(array_filter($lines, 'trim'));

if ($titleOnly) {
    // اولین سرفصل «### …» بخش اول (انگلیسی) = عنوان مناسب Release
    foreach ($trim($sections[0]) as $line) {
        if (preg_match('/^###\s+(.+)$/', $line, $m)) {
            echo $m[1].PHP_EOL;
            exit(0);
        }
    }

    fwrite(STDERR, "برای نسخه «{$version}» سرفصل «### …» پیدا نشد.\n");
    exit(1);
}

echo implode(PHP_EOL.'---'.PHP_EOL, array_map(
    fn (array $body) => implode(PHP_EOL, $trim($body)),
    $sections
)).PHP_EOL;

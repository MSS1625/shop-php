# 🤖 v2.15.0 — اتوماسیون CI: لاراستان، بج پوشش، انتشار خودکار (CI Automation: Larastan, Coverage Badge, Auto-Release)

<div dir="rtl" align="center">

**دیجی‌شاپ** — فروشگاه آنلاین تجهیزات شبکه | **Digi-Shop** — Online network-equipment store
Laravel 12 · PHP 8.2+ · Bootstrap 5 RTL · MIT License

</div>

## 🇬🇧 English

### 🤖 New — CI automation: Larastan, coverage badge, auto-release

- **Larastan (PHPStan for Laravel) level 5 — 0 errors, 0 ignores**: the whole codebase (`app`, `config`, `database`, `routes`, `tests`) now passes strict static analysis. A new `static-analysis` job runs it in CI on every push and PR, beside the test matrix (PHP 8.2 / 8.3 / 8.4) and Pint
- Getting there meant **~35 files got real type annotations instead of suppressed errors**: every Eloquent relation declares its generics, factories bind their model, every API Resource declares `@mixin` of its model — so `$order->items`, `$item->product` and `$this->id` inside resources are statically checked, not magic
- The analysis caught real things, and they are fixed: cart and API order-item building is now an explicit null-safe loop, `AdminUserSeeder` reads the admin password via `config()` (plain `env()` breaks under `config:cache`), and the stock `ExampleTest` placeholder — an always-true assertion — is gone
- **Live coverage badge, zero third-party services**: on every push to `main` a coverage job runs the suite with pcov, `scripts/coverage-badge.php` (unit-tested) turns the Clover report into a shields.io endpoint JSON and pushes it to the `coverage-badge` branch — the new Coverage badge in both READMEs reads from there (red < 50% · yellow ≥ 50% · green ≥ 80%). It appears after the first run of the new workflow on `main`
- **Auto-release**: push a tag `v*` and `release.yml` runs the whole test suite (a broken tag never ships), extracts the bilingual notes for that version from `CHANGELOG.md` via `scripts/extract-release-notes.php` (unit-tested) and creates the GitHub Release — title and body included, no manual copy-paste
- New composer shortcuts: `composer analyse` (Larastan) and `composer check` (tests + Pint + Larastan — the exact gate CI runs)

### 📊 Numbers

- **304 tests (1183 assertions)** — all green; Pint clean; **Larastan level 5: 0 errors, 0 ignores**
- **Zero migrations, zero config breaking changes** — apply the zip and you're done; `shop:install` has nothing new to run
- New files: `phpstan.neon.dist`, `scripts/extract-release-notes.php`, `scripts/coverage-badge.php`, `tests/Unit/ExtractReleaseNotesTest.php`, `tests/Unit/CoverageBadgeTest.php`, `.github/workflows/release.yml`; larastan ^3.14 added to require-dev (composer.lock updated)

<details>
<summary>Seeing it in action</summary>

On your machine:

```bash
composer analyse   # vendor/bin/phpstan analyse — level 5, 0 errors
composer check     # tests + Pint + Larastan — the same gate CI runs
```

On GitHub, after pushing this version to `main`:

- **Actions → CI** now shows four jobs: `PHP 8.2 / 8.3 / 8.4 — Tests`, `Code Style (Pint)`, `Static Analysis (Larastan)` and `Coverage + Badge`
- when `Coverage + Badge` finishes, the **Coverage** badge in the README stops saying "inaccessible" and shows the real percentage — and updates itself on every push to `main`
- push a tag and watch **Actions → Release**: tests run first, then the release appears on the Releases page with the bilingual notes pulled from `CHANGELOG.md`

</details>

<details>
<summary>Quick start (unchanged)</summary>

```bash
git clone https://github.com/MSS1625/shop-php.git && cd shop-php
composer install
php artisan shop:install     # env + key + migrate + seed + link (includes WELCOME10)
php artisan admin:create     # interactive admin user
php artisan serve            # → http://localhost:8000
```

</details>

## 🇮🇷 فارسی

### 🤖 جدید — اتوماسیون CI: لاراستان، بج پوشش، انتشار خودکار

- **لاراستان (PHPStan مخصوص Laravel) در سطح ۵ — صفر خطا، صفر استثنا**: کل کدبیس (`app`، `config`، `database`، `routes`، `tests`) تحلیل ایستای سخت‌گیرانه را پاس می‌کند. جاب جدید `static-analysis` آن را در هر push و PR اجرا می‌کند — کنار ماتریس تست‌ها (PHP 8.2 / 8.3 / 8.4) و Pint
- برای رسیدن به این نقطه، **حدود ۳۵ فایل به‌جای سرکوب‌کردن خطاها تایپ واقعی گرفتند**: هر رابطهٔ Eloquent جنریک‌هایش را اعلام می‌کند، فکتوری‌ها مدل‌شان را بایند می‌کنند و هر Resource مربوط به API یک `@mixin` از مدلش دارد — یعنی `$order->items` و `$item->product` و `$this->id` داخل Resourceها ایستا چک می‌شوند، نه جادویی
- تحلیل چند ایراد واقعی هم پیدا کرد که همه رفع شدند: ساخت اقلام سبد و سفارشِ API حالا حلقهٔ صریحِ null-safe است، `AdminUserSeeder` رمز مدیر را از `config()` می‌خواند (فراخوانی خام `env()` زیر `config:cache` می‌شکند) و جانگهدار پیش‌فرض `ExampleTest` — یک ادعای همیشه-درست — حذف شد
- **بج زندهٔ پوشش، بدون هیچ سرویس خارجی**: روی هر push به `main` جاب پوشش، تست‌ها را با pcov اجرا می‌کند، `scripts/coverage-badge.php` (تست‌شده) گزارش Clover را به JSON بج shields.io تبدیل و روی شاخهٔ `coverage-badge` پوش می‌کند — بج جدید Coverage در هر دو README از همان‌جا می‌خواند (قرمز < ۵۰٪ · زرد ≥ ۵۰٪ · سبز ≥ ۸۰٪) و بعد از اولین اجرای ورک‌فلو روی `main` ظاهر می‌شود
- **انتشار خودکار**: تگ `v*` را پوش کنید تا `release.yml` اول کل تست‌ها را اجرا کند (تگِ شکسته هرگز رلیز نمی‌شود)، یادداشت‌های دوزبانهٔ همان نسخه را از `CHANGELOG.md` بکشد و Release گیت‌هاب را با عنوان و متن بسازد — بدون کپی‌وپیست دستی
- میان‌برهای جدید کامپوزر: `composer analyse` (لاراستان) و `composer check` (تست + Pint + لاراستان — دقیقاً همان دروازه‌ای که CI اجرا می‌کند)

### 📊 اعداد

- **۳۰۴ تست (۱۱۸۳ ادعا)** — همه سبز؛ Pint تمیز؛ **لاراستان سطح ۵: صفر خطا، صفر استثنا**
- **صفر مایگریشن و صفر تغییر شکنندهٔ کانفیگ** — زیپ را اعمال کنید و تمام؛ `shop:install` چیز جدیدی برای اجرا ندارد
- فایل‌های جدید: `phpstan.neon.dist`، `scripts/extract-release-notes.php`، `scripts/coverage-badge.php`، `tests/Unit/ExtractReleaseNotesTest.php`، `tests/Unit/CoverageBadgeTest.php`، `.github/workflows/release.yml`؛ لاراستان ^3.14 به require-dev اضافه شد (composer.lock آپدیت شد)

<details>
<summary>دیدنش در عمل</summary>

روی سیستم خودتان:

```bash
composer analyse   # vendor/bin/phpstan analyse — سطح ۵، صفر خطا
composer check     # تست + Pint + لاراستان — همان دروازهٔ CI
```

روی گیت‌هاب، بعد از پوش این نسخه به `main`:

- **Actions → CI** حالا چهار جاب دارد: تست‌های 8.2 / 8.3 / 8.4، Pint، **Static Analysis (Larastan)** و **Coverage + Badge**
- وقتی جاب Coverage تمام شد، بج **Coverage** در README از حالت «inaccessible» درمی‌آید و درصد واقعی را نشان می‌دهد — و روی هر push به `main` خودش آپدیت می‌شود
- یک تگ پوش کنید و **Actions → Release** را ببینید: اول تست‌ها اجرا می‌شوند، بعد Release با یادداشت‌های دوزبانهٔ استخراج‌شده از `CHANGELOG.md` در صفحهٔ Releases ظاهر می‌شود

</details>

<details>
<summary>شروع سریع (بدون تغییر)</summary>

```bash
git clone https://github.com/MSS1625/shop-php.git && cd shop-php
composer install
php artisan shop:install     # env + key + migrate + seed + link
php artisan admin:create     # ساخت مدیر (تعاملی)
php artisan serve            # → http://localhost:8000
```

</details>

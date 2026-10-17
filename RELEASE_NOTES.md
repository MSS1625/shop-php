<!-- ============================================================
  نحوه استفاده | How to use:
  ۱) در گیت‌هاب: Releases → Draft a new release → Choose tag → v2.5.0 (Create on main)
  ۲) عنوان: «📱 v2.5.0 — SMS Notifications»
  ۳) متن زیر خط جداکننده (---) را در باکس توضیحات پیست کنید
  ۴) در صورت تمایل، فایل shop-php-v2.5.0.zip را بکشید و رها کنید
  ۵) Publish release
  ============================================================ -->

---

# 📱 v2.5.0 — SMS Notifications

<div dir="rtl" align="center">

**دیجی‌شاپ** — فروشگاه آنلاین تجهیزات شبکه | **Digi-Shop** — Online network-equipment store
Laravel 12 · PHP 8.2+ · Bootstrap 5 RTL · MIT License

</div>

## 🇬🇧 English

### 📱 New — SMS Notifications (Kavenegar)

- The same three order events as email — **order placed / payment confirmed / status changed** — now also go out as short Persian text messages, to the mobile number already collected at checkout
- **Zero-controller-change release** — the promise made in v2.4.0 is kept: the whole feature plugs in through one `OrderNotifier` binding in the service provider; no migration, no controller line, no template change
- `SmsGateway` contract (mirrors `PaymentGateway`):
  - `KavenegarSmsGateway` — real delivery through [Kavenegar](https://kavenegar.com)'s REST API
  - `LogSmsGateway` — logs messages instead of sending them; the default driver, perfect for local development and tests
- `CompositeNotifier` — with both channels on (`SHOP_NOTIFICATION_CHANNELS=mail,sms`) every event reaches email **and** SMS; a failure in one channel never stops the other
- Same failure-isolation rule as email: an SMS failure is caught and logged — checkout and payment can never break because of it
- Message examples (Persian, with Persian digits):
  - `دیجی‌شاپ: سفارش شما با کد DS-261016-8F3K ثبت شد. مبلغ: ۲,۴۰۰,۰۰۰ تومان. سپاس از خرید شما.`
  - `دیجی‌شاپ: پرداخت سفارش DS-261016-8F3K تأیید شد. کد پیگیری بانکی: ۱۲۳۴۵۶.`
  - `دیجی‌شاپ: وضعیت سفارش DS-261016-8F3K از «در انتظار بررسی» به «ارسال شده» تغییر کرد.`

### ⚙️ Setup

```bash
# .env — enable the SMS channel next to email:
SHOP_NOTIFICATION_CHANNELS=mail,sms

# local development (default) — messages land in storage/logs/laravel.log:
SMS_DRIVER=log

# real delivery — get an API key from kavenegar.com:
SMS_DRIVER=kavenegar
KAVENEGAR_API_KEY=your-api-key
KAVENEGAR_FROM=            # optional sender line; empty = panel default
```

### 📊 Numbers

- **86 feature tests** (331 assertions) — 15 new SMS tests (channel dispatch, dual-channel composite, failure isolation, message content with order number / Persian-digit amount / bank RefID / old→new status labels, the Kavenegar HTTP contract, API-error and missing-key exceptions, driver resolution, unknown-channel fallback)
- Code style: Pint clean across 103 files

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

<details>
<summary>Trying the SMS locally</summary>

```bash
# .env (defaults after this release)
SHOP_NOTIFICATION_CHANNELS=mail,sms
SMS_DRIVER=log

# place an order → then read the rendered messages:
tail -n 20 storage/logs/laravel.log
# [sms:09123456789] دیجی‌شاپ: سفارش شما با کد DS-… ثبت شد. …
```

</details>

---

## 🇮🇷 فارسی

<div dir="rtl">

### 📱 جدید — اطلاع‌رسانی پیامکی (کاوه‌نگار)

- همان سه رویداد سفارش که ایمیل می‌روند — **ثبت سفارش / تأیید پرداخت / تغییر وضعیت** — حالا به‌صورت پیام‌های کوتاه فارسی هم ارسال می‌شوند؛ به همان شماره موبایلی که از ابتدا در فرم تسویه گرفته می‌شود
- **ریلیزی با تغییر صفر در کنترلرها** — قولِ v2.4.0 عمل شد: کل قابلیت فقط با یک binding قرارداد `OrderNotifier` در سرویس‌پرووایدر وصل می‌شود؛ بدون مهاجرت دیتابیس، بدون یک خط تغییر در کنترلرها و قالب‌ها
- قرارداد `SmsGateway` (قرینهٔ `PaymentGateway`):
  - `KavenegarSmsGateway` — ارسال واقعی از طریق REST API [کاوه‌نگار](https://kavenegar.com)
  - `LogSmsGateway` — پیام را به‌جای ارسال، لاگ می‌کند؛ درایور پیش‌فرض، مناسب توسعهٔ محلی و تست
- `CompositeNotifier` — با فعال‌بودن هر دو کانال (`SHOP_NOTIFICATION_CHANNELS=mail,sms`) هر رویداد به ایمیل **و** پیامک می‌رسد؛ خطای یک کانال هرگز کانال دیگر را متوقف نمی‌کند
- همان قانون ایزوله‌سازی خطای ایمیل: خطای پیامک گرفته و لاگ می‌شود — ثبت سفارش و پرداخت به‌خاطر آن هرگز نمی‌شکنند
- نمونه پیام‌ها (فارسی، با ارقام فارسی):
  - `دیجی‌شاپ: سفارش شما با کد DS-261016-8F3K ثبت شد. مبلغ: ۲,۴۰۰,۰۰۰ تومان. سپاس از خرید شما.`
  - `دیجی‌شاپ: پرداخت سفارش DS-261016-8F3K تأیید شد. کد پیگیری بانکی: ۱۲۳۴۵۶.`
  - `دیجی‌شاپ: وضعیت سفارش DS-261016-8F3K از «در انتظار بررسی» به «ارسال شده» تغییر کرد.`

### ⚙️ تنظیمات

```bash
# در .env — فعال‌کردن کانال پیامک کنار ایمیل:
SHOP_NOTIFICATION_CHANNELS=mail,sms

# توسعهٔ محلی (پیش‌فرض) — پیام‌ها در storage/logs/laravel.log:
SMS_DRIVER=log

# ارسال واقعی — کلید API را از kavenegar.com بگیرید:
SMS_DRIVER=kavenegar
KAVENEGAR_API_KEY=کلید-شما
KAVENEGAR_FROM=            # خط فرستنده (اختیاری)؛ خالی = خط پیش‌فرض پنل
```

### 📊 اعداد

- **۸۶ تست فیچر** (۳۳۱ ادعا) — ۱۵ تست جدید پیامک (ارسال بر اساس کانال، زنجیرهٔ دوکاناله، ایزوله‌سازی خطا، محتوای پیام شامل شماره سفارش / مبلغ با ارقام فارسی / کد پیگیری بانکی / برچسب وضعیت قبلی و جدید، قرارداد HTTP کاوه‌نگار، خطای API و نبود کلید، تشخیص درایور، نادیده‌گرفتن کانال ناشناخته)
- سبک کد: Pint پاک روی ۱۰۳ فایل

<details>
<summary>راه‌اندازی سریع (بدون تغییر)</summary>

```bash
git clone https://github.com/MSS1625/shop-php.git && cd shop-php
composer install
php artisan shop:install     # env + کلید + مهاجرت + داده + لینک (شامل WELCOME10)
php artisan admin:create     # ساخت مدیر (تعاملی)
php artisan serve            # → http://localhost:8000
```

</details>

<details>
<summary>امتحان پیامک به‌صورت محلی</summary>

```bash
# در .env (پیش‌فرض این نسخه)
SHOP_NOTIFICATION_CHANNELS=mail,sms
SMS_DRIVER=log

# یک سفارش ثبت کنید → سپس پیام‌ها را بخوانید:
tail -n 20 storage/logs/laravel.log
# [sms:09123456789] دیجی‌شاپ: سفارش شما با کد DS-… ثبت شد. …
```

</details>

</div>

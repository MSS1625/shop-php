<!-- ============================================================
  نحوه استفاده | How to use:
  ۱) در گیت‌هاب: Releases → Draft a new release → Choose tag → v2.4.0 (Create on main)
  ۲) عنوان: «📧 v2.4.0 — Order Email Notifications»
  ۳) متن زیر خط جداکننده (---) را در باکس توضیحات پیست کنید
  ۴) در صورت تمایل، فایل shop-php-v2.4.0.zip را بکشید و رها کنید
  ۵) Publish release
  ============================================================ -->

---

# 📧 v2.4.0 — Order Email Notifications

<div dir="rtl" align="center">

**دیجی‌شاپ** — فروشگاه آنلاین تجهیزات شبکه | **Digi-Shop** — Online network-equipment store
Laravel 12 · PHP 8.2+ · Bootstrap 5 RTL · MIT License

</div>

## 🇬🇧 English

### 📧 New — Order Email Notifications

- Optional **email field at checkout** — guests can get order updates too; when left empty, the logged-in account's email is used as a fallback
- Three automatic emails (RTL Persian templates, inline CSS, safe in every mail client):
  - **Order placed** — right after checkout, for both online and cash-on-delivery orders
  - **Payment confirmed** — exactly once, the moment the gateway verifies the payment; repeat callbacks never duplicate it
  - **Status changed** — when the admin moves the order to processing / shipped / delivered / cancelled, showing old → new status
- Every email carries the full order summary: items, quantities, unit prices, subtotal, discount line (with coupon code) and the final payable amount
- **Contract-based architecture** (`OrderNotifier`) — the same pattern as the payment gateway: the default implementation sends email, `NullNotifier` keeps it silent, and a future SMS channel can be plugged in without touching business logic
- **Failure isolation** — a broken SMTP is caught and logged; it can never break checkout or payment confirmation
- Toggle everything with one switch: `SHOP_NOTIFICATIONS=false` in `.env`
- Zero-config local development: with `MAIL_MAILER=log` (the default) emails land in `storage/logs/laravel.log` — set SMTP whenever you're ready for real delivery
- Admin order view now shows the customer's email next to the phone number

### 📊 Numbers

- **71 feature tests** (276 assertions) — 10 new notification tests (sent, account-email fallback, invalid email rejected, disabled via config, SMTP-failure isolation, no duplicates on repeat callbacks, no email on same-status)
- Code style: Pint clean across 96 files

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
<summary>Trying the emails locally</summary>

```bash
# .env (already the default after shop:install)
MAIL_MAILER=log
SHOP_NOTIFICATIONS=true

# place an order → then read the rendered email:
tail -n 40 storage/logs/laravel.log
```

</details>

---

## 🇮🇷 فارسی

<div dir="rtl">

### 📧 جدید — اطلاع‌رسانی ایمیلی سفارش

- فیلد **ایمیل اختیاری در فرم تکمیل خرید** — خریدار مهمان هم اطلاع‌رسانی می‌گیرد؛ اگر خالی بماند، ایمیل حساب کاربریِ واردشده به‌عنوان جایگزین استفاده می‌شود
- سه ایمیل خودکار (قالب‌های راست‌چین فارسی با CSS درون‌خطی، سازگار با همه کلاینت‌های ایمیل):
  - **ثبت سفارش** — بلافاصله پس از ثبت، هم برای پرداخت آنلاین هم پرداخت در محل
  - **تأیید پرداخت** — دقیقاً یک بار، همان لحظه‌ای که درگاه پرداخت را تأیید می‌کند؛ کال‌بک‌های تکراری هرگز ایمیل تکراری نمی‌سازند
  - **تغییر وضعیت** — وقتی ادمین سفارش را به پردازش / ارسال / تحویل / لغو می‌برد، با ذکر وضعیت قبلی و جدید
- هر ایمیل خلاصهٔ کامل سفارش را دارد: اقلام، تعداد، قیمت واحد، جمع، سطر تخفیف (با کد کوپن) و مبلغ نهایی قابل پرداخت
- **معماری قراردادمحور** (`OrderNotifier`) — همان الگوی درگاه پرداخت: پیاده‌سازی پیش‌فرض ایمیل می‌فرستد، `NullNotifier` ساکت است و کانال آینده مثل SMS بدون دست‌زدن به منطق اصلی قابل اضافه‌شدن است
- **ایزوله‌سازی خطا** — خرابی SMTP گرفته و لاگ می‌شود؛ هرگز ثبت سفارش یا تأیید پرداخت را نمی‌شکند
- خاموش/روشن با یک کلید: `SHOP_NOTIFICATIONS=false` در `.env`
- توسعهٔ محلی بدون تنظیمات: با `MAIL_MAILER=log` (پیش‌فرض) ایمیل‌ها در `storage/logs/laravel.log` می‌ریزند — هر وقت خواستید SMTP را تنظیم کنید
- نمای سفارش ادمین حالا ایمیل مشتری را کنار شماره موبایل نشان می‌دهد

### 📊 اعداد

- **۷۱ تست فیچر** (۲۷۶ ادعا) — ۱۰ تست جدید اطلاع‌رسانی (ارسال، جایگزین ایمیل حساب، رد ایمیل نامعتبر، خاموش با کانفیگ، ایزوله‌سازی خطای SMTP، عدم تکرار در کال‌بک تکراری، عدم ارسال در وضعیت بدون تغییر)
- سبک کد: Pint پاک روی ۹۶ فایل

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
<summary>امتحان ایمیل‌ها به‌صورت محلی</summary>

```bash
# در .env (پیش‌فرض بعد از shop:install همین است)
MAIL_MAILER=log
SHOP_NOTIFICATIONS=true

# یک سفارش ثبت کنید → سپس ایمیل رندرشده را بخوانید:
tail -n 40 storage/logs/laravel.log
```

</details>

</div>

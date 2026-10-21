# 🚚 v2.7.0 — Guest Order Tracking

<div dir="rtl" align="center">

**دیجی‌شاپ** — فروشگاه آنلاین تجهیزات شبکه | **Digi-Shop** — Online network-equipment store
Laravel 12 · PHP 8.2+ · Bootstrap 5 RTL · MIT License

</div>

## 🇬🇧 English

### 🚚 New — Order Tracking Without an Account

- New public **order-tracking page** at `/track` — customers who checked out as guests (no account) can now follow their order with just two things they already have: the **order number** and the **mobile number they entered at checkout** — both must match, one alone is never enough
- The result shows the **same 4-step timeline** as the account area (pending → processing → shipped → delivered), the payment status card, the full items list with prices and the discount line — cancelled orders show their cancelled state
- **Payment retry for guests**: after a successful lookup, an unpaid online order shows the pay / retry button — powered by the exact `viewed_orders` session mechanism checkout already uses, so no new authorization surface was added
- **Security-first design**:
  - Uniform error message for a wrong order number and a wrong phone — the response never reveals which field was valid (anti-enumeration)
  - Rate limited like the login form (10 lookups per minute) — brute-forcing order numbers is impractical
  - Minimum-data page: the recipient's address is never shown and the mobile number is masked (`۰۹۱۲***۷۸۹`)
  - The lookup result lives in the session for 30 minutes only, and the flow follows Post/Redirect/Get — refreshing the page never re-submits the form
- **Persian keyboard friendly**: Persian/Arabic digits are normalized on both fields (same approach as coupon codes) — `DS-۲۶۱۰۲۱-...` typed on a Persian keyboard finds the order
- The navbar gained a «پیگیری سفارش» link, and the order-success page now guides guests to the tracking page for future visits

### 📊 Numbers

- **112 feature tests** (476 assertions) — 16 new tracking tests: correct/wrong/mismatched lookups, identical-error anti-enumeration check, Persian-digit normalization, session expiry, rate limiting, cancelled state, payment-retry access grant, address-hiding, phone masking, success-page guidance
- Code style: Pint clean across 108 files

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
<summary>Seeing it in action</summary>

```bash
# Place an order as a guest (checkout without logging in) and note the
# order number (DS-YYMMDD-XXXX) and the mobile number you typed.
# Then open http://localhost:8000/track and enter both —
# the timeline, payment status and items appear.
# Try a wrong phone: the same friendly error, zero order data.
```

</details>

---

## 🇮🇷 فارسی

<div dir="rtl">

### 🚚 جدید — پیگیری سفارش بدون حساب کاربری

- صفحهٔ عمومی **پیگیری سفارش** در آدرس `/track` — مشتریانی که به‌صورت مهمان (بدون حساب) خرید کرده‌اند حالا با همان دو چیزی که از قبل دارند سفارششان را دنبال می‌کنند: **شماره سفارش** و **شماره موبایلی که هنگام خرید وارد کرده‌اند** — هر دو باید همزمان بخوانند، یکی به‌تنهایی هرگز کافی نیست
- نتیجه همان **تایم‌لاین ۴ مرحله‌ای** بخش حساب کاربری را نشان می‌دهد (در انتظار بررسی ← در حال پردازش ← ارسال شده ← تحویل داده شده)، به‌همراه کارت وضعیت پرداخت، فهرست کامل اقلام با قیمت و سطر تخفیف — سفارش‌های لغوشده هم وضعیت لغو خود را نشان می‌دهند
- **تلاش مجدد پرداخت برای مهمان‌ها**: بعد از پیگیری موفق، سفارش آنلاینِ پرداخت‌نشده دکمهٔ پرداخت/تلاش مجدد را نشان می‌دهد — با دقیقاً همان مکانیزم سشن `viewed_orders` که تسویه‌حساب استفاده می‌کند؛ یعنی هیچ سطح دسترسی جدیدی به پروژه اضافه نشد
- **طراحی امنیت‌محور**:
  - پیام خطای یکسان برای شماره سفارش غلط و موبایل غلط — پاسخ هرگز لو نمی‌دهد کدام فیلد درست بوده (ضد enumeration)
  - محدودیت نرخ مثل فرم ورود (۱۰ جستجو در دقیقه) — حدس زدن شماره سفارش‌ها عملاً بی‌فایده است
  - صفحهٔ حداقلِ داده: آدرس گیرنده هرگز نمایش داده نمی‌شود و شماره موبایل ماسک می‌شود (`۰۹۱۲***۷۸۹`)
  - نتیجهٔ پیگیری فقط ۳۰ دقیقه در سشن می‌ماند و جریان Post/Redirect/Get است — رفرش صفحه هرگز فرم را دوباره ارسال نمی‌کند
- **سازگار با کیبورد فارسی**: ارقام فارسی/عربی هر دو فیلد نرمال می‌شوند (همان رویکرد کد تخفیف) — با کیبورد فارسی هم `DS-۲۶۱۰۲۱-...` پیدا می‌شود
- نوبار به لینک «پیگیری سفارش» مجهز شد و صفحهٔ موفقیت سفارش حالا مهمان‌ها را برای پیگیری‌های بعدی راهنمایی می‌کند

### 📊 اعداد

- **۱۱۲ تست فیچر** (۴۷۶ ادعا) — ۱۶ تست جدید پیگیری: جستجوی درست/غلط/ناهماهنگ، بررسی پیام یکسان ضد enumeration، نرمال‌سازی ارقام فارسی، انقضای سشن، محدودیت نرخ، وضعیت لغو، دسترسی تلاش مجدد پرداخت، مخفی‌سازی آدرس، ماسک موبایل و راهنمای صفحه موفقیت
- سبک کد: Pint پاک روی ۱۰۸ فایل

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
<summary>دیدن آن در عمل</summary>

```bash
# بدون ورود، یک سفارش ثبت کنید و شماره سفارش (DS-YYMMDD-XXXX)
# و موبایلی که وارد کردید را یادداشت کنید.
# سپس http://localhost:8000/track را باز کنید و هر دو را وارد کنید —
# تایم‌لاین، وضعیت پرداخت و اقلام ظاهر می‌شوند.
# یک بار هم موبایل اشتباه بزنید: همان پیام دوستانه، بدون هیچ داده‌ای از سفارش.
```

</details>

</div>

# 📡 v2.12.0 — REST API v1 (Sanctum, Mobile-Ready)

<div dir="rtl" align="center">

**دیجی‌شاپ** — فروشگاه آنلاین تجهیزات شبکه | **Digi-Shop** — Online network-equipment store
Laravel 12 · PHP 8.2+ · Sanctum 4 · Bootstrap 5 RTL · MIT License

</div>

## 🇬🇧 English

### 📡 New — REST API v1, authenticated with Sanctum tokens

- The whole store is now available **to a mobile app or any external client**: 18 endpoints under `/api/v1` — auth, catalog, reviews, questions, wishlist and orders — authenticated with **Laravel Sanctum personal access tokens** (`Authorization: Bearer …`); the web shop keeps its session flow untouched
- **Token auth that behaves like a phone**: `POST /auth/login` names the token after your `device_name`; `POST /auth/logout` revokes **only the current device's token** — sign out on the phone, stay logged in on the tablet; login errors are deliberately generic so the API never reveals whether an email exists; tokens are stored hashed
- **The catalog with the same rules as the web**: `GET /products` runs the exact same filter request as the storefront — search, category, **price ranges typed in Persian digits (۵۰۰٬۰۰۰)**, in-stock filter and best-selling sort (`withSum`); `GET /products/{slug}` fills an entire product screen **in one call**: gallery, rating summary + 5→1 breakdown, first page of approved reviews **with verified-buyer badges (one query, no N+1)**, answered questions and 4 related products
- **Orders placed straight from the app**: `POST /orders` takes `items: [{slug, quantity}]`, customer details, payment method and an optional `coupon_code` — everything runs through the **new shared `PlaceOrder` service** (the web checkout was refactored onto it too): final stock re-validation, row-locked coupon redemption, price snapshots, stock decrement and mail/SMS notifications behave identically on both surfaces; ZarinPal orders answer with a `payment_url` for WebView hand-off, and every response carries raw values (`status`, `total`) **plus** ready-made Persian labels (`status_label`, `total_fa`)
- **The social layer, readable and writable**: paginated `GET` endpoints for reviews and questions (approved/answered only, verified badges included); `POST` endpoints enforce the web rules — one review per user/product, pending moderation on both, `throttle:5,1`
- **Idempotent wishlist**: `POST` / `DELETE /wishlist/{slug}` always return the final state — a retry after a flaky connection never errors; product responses carry `is_in_wishlist` for logged-in tokens (one query per page)
- **Guardrails**: 60 req/min per token (`throttle:api`), 10/min on login/register, standard 422 validation shape, `409` for stock conflicts with the Persian message, the same `[a-z0-9-]+` slug constraint as the web
- Housekeeping: `laravel/sanctum` ^4.3 (one migration — `personal_access_tokens` — applied automatically by `shop:install`); `symfony/mime` bumped past **CVE-2026-45070** (composer audit clean); `fa_price()` now uses the Persian thousands separator «٬» — `۲٬۰۰۰٬۰۰۰` everywhere
- 48 new tests across 5 API suites: auth flows, token revocation, filters + Persian-digit parsing, one-call detail shape, verified badges, wishlist idempotency, ownership isolation, direct orders with coupon math, 409 stock conflicts, restock on cancel, email delivery

### 📊 Numbers

- **264 feature tests** (990 assertions) — all green; code style: Pint clean
- One new migration (`personal_access_tokens`) — `php artisan shop:install` or plain `migrate` handles it; zero config changes
- 18 new API endpoints under `/api/v1`; 1 new shared service (`PlaceOrder`); 7 API resources; 5 new feature-test suites

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
# Log in from your "app" and grab a token:
curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"you@example.com","password":"secret","device_name":"Pixel 8"}'
# → {"message":"خوش آمدید!","data":{…},"token":"1|abc…"}

# Browse the catalog — Persian digits work in price filters:
curl -s "http://localhost:8000/api/v1/products?sort=popular&min=۵۰۰٬۰۰۰" \
  -H "Accept: application/json"

# Place an order straight from the app, with a coupon:
curl -s -X POST http://localhost:8000/api/v1/orders \
  -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"items":[{"slug":"network-switch","quantity":1}],
       "customer_name":"سارا محمدی","customer_phone":"09123456789",
       "customer_address":"تهران، خیابان ولیعصر، پلاک ۱۲",
       "payment_method":"cod","coupon_code":"WELCOME10"}'
# → 201 + order with status_label, total_fa and payment_url when online

# Sign out on just this device — the tablet keeps working:
curl -s -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer $TOKEN" -H "Accept: application/json"
```

</details>

---

## 🇮🇷 فارسی

<div dir="rtl">

### 📡 جدید — REST API نسخهٔ ۱ با توکن Sanctum

- کل فروشگاه حالا **در دسترس اپلیکیشن موبایل یا هر کلاینت بیرونی** است: ۱۸ نقطهٔ اتصال زیر `/api/v1` — احراز هویت، کاتالوگ، نظرات، پرسش‌ها، علاقه‌مندی‌ها و سفارش‌ها — با **توکن دسترسی شخصی Sanctum** (`Authorization: Bearer …`)؛ جریان سشن وب دست‌نخورده ماند
- **توکنی که مثل گوشی رفتار می‌کند**: `POST /auth/login` توکن را به نام `device_name` شما ثبت می‌کند؛ `POST /auth/logout` **فقط توکن همین دستگاه** را باطل می‌کند — از گوشی خارج شوید، تبلت لاگین می‌ماند؛ پیام خطای ورود عمداً کلی است تا API لو ندهد ایمیل وجود دارد یا نه؛ توکن‌ها هش‌شده ذخیره می‌شوند
- **کاتالوگ با همان قواعد وب**: `GET /products` همان فیلترهای ویترین را اجرا می‌کند — جستجو، دسته، **بازهٔ قیمت با ارقام فارسی (۵۰۰٬۰۰۰)**، فیلتر موجودی و پرفروش‌ترین (`withSum`)؛ `GET /products/{slug}` کل صفحهٔ محصول را **در یک فراخوانی** پر می‌کند: گالری، خلاصهٔ امتیاز + تفکیک ۵→۱، اولین صفحهٔ نظرات تأییدشده **با نشان خریدار تأییدشده (یک کوئری، بدون N+1)**، پرسش‌های پاسخ‌داده و ۴ محصول مشابه
- **سفارش مستقیم از اپ**: `POST /orders` با `items`، اطلاعات مشتری، روش پرداخت و `coupon_code` اختیاری — همه‌چیز از **سرویس مشترک جدید `PlaceOrder`** می‌گذرد (فرم خرید وب هم روی آن بازآرایی شد): اعتبارسنجی نهایی موجودی، نقد کردن کوپن با قفل ردیف، اسنپ‌شات قیمت، کاهش انبار و اطلاع‌رسانی ایمیل/پیامک روی هر دو سطح یکی است؛ سفارش زرین‌پال `payment_url` برای WebView برمی‌گرداند و هر پاسخ هم مقدار خام دارد (`status`, `total`) **هم** برچسب فارسی آماده (`status_label`, `total_fa`)
- **لایهٔ اجتماعی، خواندنی و نوشتنی**: `GET`های صفحه‌بندی‌شدهٔ نظرات و پرسش‌ها (فقط تأییدشده/پاسخ‌داده، با نشان خریدار)؛ `POST`ها همان قواعد وب را دارند — یک نظر برای هر کاربر/محصول، شروع در انتظار تأیید، `throttle:5,1`
- **علاقه‌مندی idempotent**: `POST` / `DELETE /wishlist/{slug}` همیشه وضعیت نهایی را برمی‌گردانند — تلاش دوباره بعد از قطع اینترنت خطا نمی‌دهد؛ پاسخ محصولات برای توکن لاگین‌شده `is_in_wishlist` دارد (یک کوئری برای کل صفحه)
- **حفاظت‌ها**: ۶۰ درخواست در دقیقه برای هر توکن (`throttle:api`)، ۱۰ در دقیقه برای ورود/ثبت‌نام، شکل استاندارد 422 برای اعتبارسنجی، `409` برای تعارض موجودی با پیام فارسی، همان قید اسلاگ `[a-z0-9-]+` وب
- کارهای جانبی: `laravel/sanctum` نسخهٔ ^4.3 (یک مایگریشن — `personal_access_tokens` — که `shop:install` خودش اعمال می‌کند)؛ `symfony/mime` از **CVE-2026-45070** عبور داده شد (composer audit پاک)؛ `fa_price()` حالا جداکنندهٔ هزارگان فارسی «٬» می‌گذارد — `۲٬۰۰۰٬۰۰۰` همه‌جا
- ۴۸ تست جدید در ۵ مجموعهٔ API: جریان‌های احراز هویت، باطل‌کردن توکن، فیلترها + پارس ارقام فارسی، شکل پاسخ یک‌فراخوانی، نشان‌های خریدار، idempotency علاقه‌مندی، جداسازی مالکیت، سفارش مستقیم با محاسبهٔ کوپن، تعارض 409، بازگشت موجودی هنگام لغو، ارسال ایمیل

### 📊 اعداد

- **۲۶۴ تست فیچر** (۹۹۰ ادعا) — همه سبز؛ سبک کد: Pint پاک
- یک مهاجرت جدید (`personal_access_tokens`) — با `php artisan shop:install` یا `migrate` ساده اعمال می‌شود؛ بدون تغییر کانفیگ
- ۱۸ نقطهٔ اتصال جدید زیر `/api/v1`؛ ۱ سرویس مشترک جدید (`PlaceOrder`)؛ ۷ منبع API؛ ۵ مجموعهٔ تست جدید

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
# از «اپ» وارد شوید و توکن بگیرید:
curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"you@example.com","password":"secret","device_name":"Pixel 8"}'
# → {"message":"خوش آمدید!","data":{…},"token":"1|abc…"}

# کاتالوگ را ببینید — ارقام فارسی در فیلتر قیمت کار می‌کند:
curl -s "http://localhost:8000/api/v1/products?sort=popular&min=۵۰۰٬۰۰۰" \
  -H "Accept: application/json"

# مستقیم از اپ سفارش ثبت کنید، با کد تخفیف:
curl -s -X POST http://localhost:8000/api/v1/orders \
  -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"items":[{"slug":"network-switch","quantity":1}],
       "customer_name":"سارا محمدی","customer_phone":"09123456789",
       "customer_address":"تهران، خیابان ولیعصر، پلاک ۱۲",
       "payment_method":"cod","coupon_code":"WELCOME10"}'
# → 201 + سفارش با status_label و total_fa و payment_url برای سفارش آنلاین

# فقط از همین دستگاه خارج شوید — تبلت کارش را می‌کند:
curl -s -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer $TOKEN" -H "Accept: application/json"
```

</details>

</div>

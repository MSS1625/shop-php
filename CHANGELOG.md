# 📋 Changelog — تاریخچه تغییرات

All notable changes to this project are documented in this file.
(تمام تغییرات مهم این پروژه در این فایل ثبت می‌شود.)

<div dir="rtl" align="center">

راه سریع تشخیص نسخه: جدیدترین ورودی بالای هر بخش = نسخه فعلی؛ الان **v2.4.0** ✅

</div>

## 🇬🇧 English

## [2.4.0] — 2026-10-14

### 📧 Added — Order Email Notifications

- New optional `customer_email` on checkout — orders can now notify customers by email (guests included); when the field is left empty, the logged-in account's email is used as fallback
- Contract-based notifier (`App\Contracts\OrderNotifier`) following the same pattern as the payment gateway — the default implementation sends emails, a `NullNotifier` keeps everything silent when disabled, and the channel can be swapped (e.g. SMS) without touching the business logic
- Three bilingual-ready RTL Persian email templates (inline CSS, email-client safe): order summary, payment breakdown with discount line
  - **Order placed** — sent right after checkout (both online and cash-on-delivery orders)
  - **Payment confirmed** — sent exactly once, right after the gateway verifies the payment (repeat callbacks never duplicate it)
  - **Status changed** — sent when the admin moves the order (processing / shipped / delivered / cancelled), including old → new status
- Failure isolation: notification errors are caught and logged — a broken SMTP can never break checkout or payment confirmation
- Admin order view now shows the customer's email next to phone
- Config switch: `SHOP_NOTIFICATIONS=true|false` in `.env` (documented in `.env.example`); with `MAIL_MAILER=log` emails land in `storage/logs/laravel.log` — perfect for local development without SMTP
- 10 new feature tests (sent/fallback/invalid-email/disabled-config/SMTP-failure-isolation/no-duplicate/no-same-status) — **71 tests total, 276 assertions**

## [2.3.0] — 2026-10-11

### 🎟 Added — Discount Coupon Codes

- New `coupons` table: percent or fixed-amount codes, optional minimum cart amount, optional total usage cap, optional start/expiry window, active toggle
- Shop cart: coupon input box in the order summary — wrong, expired, exhausted or below-minimum codes are rejected with clear Persian messages; a valid code shows the applied-code chip, discount line and the final payable amount
- Checkout: the summary shows subtotal, discount line and payable amount; before the order is placed the coupon is re-validated transparently — if it became invalid the customer is sent back to the cart instead of being silently charged full price
- Orders now store `subtotal`, `discount_amount` and `coupon_code` (historical text, no FK) — the success page, customer account and admin order view all show the discount breakdown
- Race-safe redemption: inside the order transaction the coupon row is locked (`lockForUpdate`) and re-validated before `used_count` is incremented, so the usage cap cannot be exceeded by concurrent checkouts
- Fixed-amount discount is always capped at the cart subtotal — the payable amount can never go below zero
- Admin panel: full coupon management (list with status badges — فعال / غیرفعال / منقضی / تکمیل ظرفیت / شروع نشده — create, edit, delete with confirm) under a new «کدهای تخفیف» menu item
- Code normalization everywhere: Persian/Arabic digits converted, spaces stripped, upper-cased; `summer 500` typed by an admin becomes `SUMMER500`
- Seeder ships a demo coupon `WELCOME10` (10٪, 100 uses) so the feature is visible right after `shop:install`
- 18 new feature tests (apply/remove/reject paths, cap math, checkout storage, usage increment, invalid-at-checkout guard, admin CRUD + guards) — **61 tests total, 231 assertions**

## [2.2.0] — 2026-10-05

### 🖼 Added — Product Image Gallery

- New `product_images` table with position ordering — each product can now have extra shots alongside its cover image
- Admin product form: multi-file upload (up to 4 per submit, same MIME/size/dimension rules as the cover) plus per-image delete checkboxes; the existing gallery is displayed as thumbnails on every edit
- Product detail page: a thumbnail strip under the main image — click any thumb to swap the big view; the strip is omitted entirely for products without a gallery
- IDOR-safe removal: delete requests are always scoped through the product relation, so a foreign image ID has no effect; files are deleted together with their rows, and deleting a product cleans up its gallery files

### 🔧 Fixed — CI failures (composer platform pin)

- Root cause of the red CI badge: the lock file had been resolved on PHP 8.4 without a platform pin, pulling packages that refuse PHP 8.2/8.3 (`symfony/*` 8.1 needs `>=8.4.1`, `laravel/pint` 1.32 needs `^8.3`) — so the 8.2/8.3 matrix legs failed at `composer install`
- Fix: added `config.platform.php = "8.2.0"` to `composer.json` and ran a full `composer update` — the lock now resolves for PHP 8.2.0+ and installs on every matrix leg (verified locally with `composer install --dry-run`)
- Locked now: laravel/pint 1.30.4 and symfony 7.4.x; laravel/framework stays 12.69.3
- Workflow hygiene: `actions/checkout` and `actions/cache` bumped v4 → v5 (Node 20 deprecation warnings gone) and the runner pinned to `ubuntu-24.04` to stay ahead of the `ubuntu-latest` → Ubuntu 26 migration (2026-10-19)

### 🔍 Changed

- 8 new feature tests (gallery upload/update/remove, IDOR guard, 4-image limit, detail-page render, guest guard) — **43 tests total, 145 assertions**

## [2.1.0] — 2026-10-05

### 🌐 Added — Project Website (GitHub Pages)

- New `docs/` folder with a **static RTL demo page** (`index.html`) replicating the shop home page: hero, categories, all 8 sample products with real images & prices, feature cards and a quick-start terminal
- Hosted **free on GitHub Pages** at <https://mss1625.github.io/shop-php/> — enabled with a single toggle (repo Settings → Pages → main branch `/docs`)
- Ships with `.nojekyll` and `robots.txt`; every button links back to the repository

### 📝 Changed — README internationalization

- README split into **`README.md` (English)** + **`README.fa.md` (فارسی)** with a two-way language switcher at the top of each file — fixes the missing link to the English version
- `README.fa.md` now has full parity with the English file (project structure, roadmap and contributing sections added)
- Release badge now **auto-updates from GitHub Releases** (`github/v/release`) instead of a hard-coded version
- Live-preview link added to both files

### 📦 Added — Release tooling

- `RELEASE_NOTES.md` — paste-ready bilingual notes for the v2.1.0 GitHub Release

## [2.0.0] — 2026-10-04

### 💳 Added — ZarinPal Payment Gateway

- Full online-payment integration using **ZarinPal v4 REST API** (`request.json` / `verify.json` / `StartPay`)
- **Three modes** out of the box: `mock` (local simulator page — no merchant account needed), `sandbox`, and `production`
- **Server-side verification** on callback — payment status is never trusted from the URL alone (anti-tampering)
- Payment record per order: `payment_method`, `payment_status`, `authority`, `ref_id`, `paid_at`
- **Cash on Delivery (پرداخت در محل)** option alongside online payment
- Friendly retry flow: failed payments can be retried on the same order without creating duplicates
- Clean payment-abstraction layer (`App\Contracts\PaymentGateway`) — swap in any other Iranian PSP with minimal code

### 👤 Added — Customer Accounts & Order History

- Customer **registration / login / logout** with rate-limited login
- Personal **account dashboard**: total orders, paid amount, pending orders
- **Order history** page (paginated) with status and payment badges
- **Order tracking page** with a 4-step visual timeline (pending → processing → shipped → delivered)
- Customers can **cancel an unpaid pending order** — stock is restored atomically
- IDOR protection: users can only view their own orders
- Guest checkout still works — account is optional

### 🤖 Added — GitHub Actions CI

- Workflow `.github/workflows/ci.yml` runs **automatically on every push** (all branches) and every Pull Request
- Test matrix: **PHP 8.2 / 8.3 / 8.4** on `ubuntu-latest` with SQLite
- Separate **Pint code-style** job
- Composer dependency caching for fast runs

### 🔧 Changed

- Admin orders table now shows **payment method + payment status** columns
- Admin order detail page shows a full **payment card** (ref ID, paid-at timestamp)
- Order status changes refactored onto an atomic, stock-safe method
- Navbar now has a **user dropdown** (account, orders, logout) for logged-in customers
- 26 new feature tests (auth, account, payments) — **35 tests total**, all passing

## [1.0.0] — 2026-10-04

### ⚡ Initial Laravel Release

- Complete rewrite of the original plain-PHP shop on **Laravel 12** (PHP 8.2+)
- Full shopping cart (session-based, AJAX add/update/remove) and DB-transactional checkout with stock re-validation
- Order management with 5-state workflow and price snapshots
- Admin panel: dashboard with stats & 7-day sales chart, product/category CRUD, secure image upload (MIME whitelist, 2MB, random filenames), order management with cancel-and-restock
- Security hardening: CSRF, rate-limited admin login (5/min), session regeneration, security headers, no default passwords (`admin:create` interactive command)
- One-command installer: `php artisan shop:install`
- Dark neon RTL theme, Vazirmatn font, Persian digits & currency helpers
- Bilingual README (EN/FA), MIT License, 9 feature tests

---

## 🇮🇷 فارسی

## [2.4.0] — ۲۰۲۶-۱۰-۱۴

### 📧 افزوده شد — اطلاع‌رسانی ایمیلی سفارش

- فیلد اختیاری `customer_email` در فرم تکمیل خرید — سفارش‌ها (حتی خرید مهمان) می‌توانند مشتری را با ایمیل مطلع کنند؛ اگر خالی بماند، ایمیل حساب کاربری واردشده به‌عنوان جایگزین استفاده می‌شود
- اطلاع‌رسانی قراردادمحور (`App\Contracts\OrderNotifier`) با همان الگوی درگاه پرداخت — پیاده‌سازی پیش‌فرض ایمیل می‌فرستد، `NullNotifier` در حالت خاموش ساکت است و کانال (مثلاً SMS) بدون دست‌زدن به منطق اصلی قابل تعویض است
- سه قالب ایمیلی راست‌چین فارسی با CSS درون‌خطی (سازگار با کلاینت‌های ایمیل): خلاصه سفارش، ریز مبلغ با سطر تخفیف
  - **ثبت سفارش** — بلافاصله پس از checkout (هم پرداخت آنلاین، هم پرداخت در محل)
  - **تأیید پرداخت** — دقیقاً یک بار، بلافاصله پس از verify درگاه (کال‌بک‌های تکراری هرگز ایمیل تکراری نمی‌سازند)
  - **تغییر وضعیت** — هنگام تغییر وضعیت سفارش توسط ادمین (پردازش / ارسال / تحویل / لغو)، با ذکر وضعیت قبلی و جدید
- ایزوله‌سازی خطا: خطاهای ارسال اطلاع‌رسانی گرفته و لاگ می‌شوند — خرابی SMTP هرگز ثبت سفارش یا تأیید پرداخت را نمی‌شکند
- نمای سفارش ادمین حالا ایمیل مشتری را کنار شماره موبایل نشان می‌دهد
- کلید تنظیم: `SHOP_NOTIFICATIONS=true|false` در `.env` (در `.env.example` مستند شده)؛ با `MAIL_MAILER=log` ایمیل‌ها در `storage/logs/laravel.log` می‌ریزند — بدون SMTP برای توسعه محلی
- ۱۰ تست فیچر جدید (ارسال/جایگزین/ایمیل نامعتبر/کانفیگ خاموش/ایزوله‌سازی خطای SMTP/عدم تکرار/وضعیت بدون تغییر) — در مجموع **۷۱ تست، ۲۷۶ ادعا**

## [2.3.0] — ۲۰۲۶-۱۰-۱۱

### 🎟 افزوده شد — کدهای تخفیف

- جدول جدید `coupons`: کدهای درصدی یا مبلغی، حداقل مبلغ سبد اختیاری، سقف مصرف کل اختیاری، بازه شروع/انقضا اختیاری و کلید فعال/غیرفعال
- سبد خرید: باکس وارد کردن کد تخفیف در خلاصه سفارش — کدهای غلط/منقضی/تکمیل‌ظرفیت/کمتر از حداقل با پیام فارسی روشن رد می‌شوند؛ کد معتبر چیپ اعمال‌شده، سطر تخفیف و مبلغ نهایی قابل پرداخت را نشان می‌دهد
- تسویه: خلاصه سفارش جمع، سطر تخفیف و مبلغ قابل پرداخت را نشان می‌دهد؛ پیش از ثبت سفارش کد یک بار شفاف اعتبارسنجی مجدد می‌شود — اگر نامعتبر شده بود مشتری به سبد برمی‌گردد، نه اینکه بی‌خبر قیمت کامل بپردازد
- سفارش‌ها حالا `subtotal` و `discount_amount` و `coupon_code` (متن تاریخی، بدون FK) را ذخیره می‌کنند — صفحه موفقیت، حساب کاربری و نمای ادمین همه ریز تخفیف را نشان می‌دهند
- بازخرید ضد رقابت: داخل تراکنش سفارش، ردیف کوپن قفل (`lockForUpdate`) و مجدداً اعتبارسنجی می‌شود و بعد `used_count` زیاد می‌شود؛ سقف مصرف با تسویه‌های همزمان قابل عبور نیست
- تخفیف مبلغی همیشه به جمع سبد محدود است — مبلغ قابل پرداخت هرگز منفی نمی‌شود
- پنل ادمین: مدیریت کامل کوپن‌ها (لیست با بج وضعیت — فعال / غیرفعال / منقضی / تکمیل ظرفیت / شروع نشده — ساخت، ویرایش، حذف با تأیید) زیر آیتم منوی «کدهای تخفیف»
- نرمال‌سازی کد در همه‌جا: ارقام فارسی/عربی تبدیل، فاصله‌ها حذف و حروف بزرگ می‌شوند؛ `summer 500` که ادمین بنویسد `SUMMER500` می‌شود
- سیدر یک کوپن نمونه `WELCOME10` (۱۰٪، ۱۰۰ بار) می‌سازد تا قابلیت بلافاصله بعد از `shop:install` دیده شود
- ۱۸ تست فیچر جدید (مسیرهای اعمال/حذف/رد، محاسبه سقف، ذخیره در تسویه، افزایش مصرف، گارد نامعتبر-در-تسویه، CRUD ادمین + گاردها) — در مجموع **۶۱ تست، ۲۳۱ ادعا**

## [2.2.0] — ۲۰۲۶-۱۰-۰۵

### 🖼 افزوده شد — گالری تصاویر محصول

- جدول جدید `product_images` با ترتیب نمایش — هر محصول می‌تواند در کنار تصویر اصلی، تصاویر اضافی داشته باشد
- فرم ادمین: آپلود چندتایی (حداکثر ۴ تصویر در هر ارسال، همان قواعد فرمت/حجم/ابعاد تصویر اصلی) + چک‌باکس حذف برای هر تصویر؛ گالری موجود در هر ویرایش به‌صورت بندانگشتی نمایش داده می‌شود
- صفحه جزئیات محصول: نوار بندانگشتی زیر تصویر اصلی — با کلیک، تصویر بزرگ عوض می‌شود؛ برای محصول بدون گالری هیچ نوار اضافه‌ای رندر نمی‌شود
- حذف ضد IDOR: درخواست‌های حذف همیشه از رابطه محصول پاس داده می‌شوند، پس ID تصویر محصول دیگر هیچ اثری ندارد؛ فایل‌ها همراه رکوردشان حذف می‌شوند و حذف محصول، فایل‌های گالری‌اش را هم پاک می‌کند

### 🔧 رفع شد — خرابی CI (پین platform کمپوزر)

- علت بج قرمز CI: فایل قفل روی PHP 8.4 و بدون پین platform رزول شده بود و بسته‌هایی را می‌آورد که 8.2/8.3 را قبول نمی‌کنند (`symfony/*` 8.1 نیازمند `>=8.4.1`، `pint` 1.32 نیازمند `^8.3`) — در نتیجه پاهای 8.2/8.3 ماتریس در مرحله `composer install` رد می‌شدند
- راه‌حل: `config.platform.php = "8.2.0"` به `composer.json` اضافه و یک `composer update` کامل اجرا شد — قفل جدید برای PHP 8.2.0+ رزول می‌شود و روی همه پاهای ماتریس نصب می‌شود (با `composer install --dry-run` تأیید شد)
- نسخه‌های قفل‌شده جدید: laravel/pint 1.30.4 و symfony 7.4.x؛ فریم‌ورک همان 12.69.3 می‌ماند

### 🔍 تغییرات

- ۸ تست فیچر جدید (آپلود/ویرایش/حذف گالری، محافظ IDOR، سقف ۴ تصویر، رندر صفحه جزئیات، گارد مهمان) — در مجموع **۴۳ تست، ۱۴۵ ادعا**

## [2.1.0] — ۲۰۲۶-۱۰-۰۵

### 🌐 افزوده شد — وب‌سایت پروژه (GitHub Pages)

- پوشه `docs/` جدید با **صفحه دموی ایستای راست‌چین** (`index.html`) که صفحه اصلی فروشگاه را بازسازی می‌کند: هیرو، دسته‌بندی‌ها، هر ۸ محصول نمونه با تصویر و قیمت واقعی، کارت‌های امکانات و ترمینال راه‌اندازی
- میزبانی **رایگان روی GitHub Pages** در <https://mss1625.github.io/shop-php/> — فقط با یک تنظیم فعال می‌شود (Settings ← Pages ← شاخه main / پوشه docs)
- همراه با `.nojekyll` و `robots.txt`؛ همه دکمه‌ها به مخزن لینک می‌شوند

### 📝 تغییرات — بین‌المللی‌سازی README

- README به **`README.md` (انگلیسی)** + **`README.fa.md` (فارسی)** تفکیک شد با سوییچر زبان دوسویه بالای هر فایل — مشکل نبودن لینک نسخه انگلیسی حل شد
- `README.fa.md` حالا هم‌تراز کامل با نسخه انگلیسی است (ساختار پروژه، نقشه راه و مشارکت اضافه شد)
- بج Release حالا **خودکار از GitHub Releases** به‌روز می‌شود (`github/v/release`) به‌جای نسخه ثابت دستی
- لینک پیش‌نمایش آنلاین به هر دو فایل اضافه شد

### 📦 افزوده شد — ابزار انتشار

- `RELEASE_NOTES.md` — یادداشت دوزبانه آماده پیست برای ریلیز v2.1.0 در گیت‌هاب

## [2.0.0] — ۲۰۲۶-۱۰-۰۴

### 💳 افزوده شد — درگاه پرداخت زرین‌پال

- اتصال کامل پرداخت آنلاین با **ZarinPal v4 REST API** (`request.json` / `verify.json` / `StartPay`)
- **سه حالت آماده**: `mock` (شبیه‌ساز محلی — بدون نیاز به مرچنت‌آی‌دی)، `sandbox` و `production`
- **تأیید سمت سرور** در کال‌بک — وضعیت پرداخت هرگز صرفاً از URL گرفته نمی‌شود (ضد دستکاری)
- ثبت اطلاعات پرداخت برای هر سفارش: روش، وضعیت، `authority`، `ref_id` و زمان پرداخت
- گزینه **پرداخت در محل** در کنار پرداخت آنلاین
- امکان **تلاش مجدد** برای پرداخت‌های ناموفق روی همان سفارش، بدون ایجاد سفارش تکراری
- لایه انتزاعی پرداخت (`PaymentGateway`) برای تعویض آسان با هر PSP ایرانی دیگر

### 👤 افزوده شد — حساب کاربری و تاریخچه سفارش‌ها

- **ثبت‌نام / ورود / خروج** مشتری با محدودیت تعداد تلاش ورود
- **داشبورد شخصی**: تعداد کل سفارش‌ها، مبلغ پرداخت‌شده، سفارش‌های در انتظار
- صفحه **تاریخچه سفارش‌ها** (صفحه‌بندی‌شده) با نشان وضعیت و پرداخت
- صفحه **رهگیری سفارش** با تایم‌لاین چهار مرحله‌ای
- **لغو سفارش** پرداخت‌نشده در حالت «در انتظار» — با بازگردانی خودکار و اتمیک موجودی
- محافظت IDOR: هر کاربر فقط سفارش‌های خودش را می‌بیند
- خرید مهمان همچنان ممکن است — داشتن حساب اختیاری است

### 🤖 افزوده شد — GitHub Actions

- ورک‌فلاو `.github/workflows/ci.yml` که **روی هر push** (همه شاخه‌ها) و هر Pull Request به‌صورت خودکار اجرا می‌شود
- ماتریس تست: **PHP 8.2 / 8.3 / 8.4** روی `ubuntu-latest` با SQLite
- جاب جداگانه **بررسی استایل کد (Pint)**
- کش وابستگی‌های Composer برای اجرای سریع‌تر

### 🔍 تغییرات

- جدول سفارش‌های ادمین: ستون‌های **روش و وضعیت پرداخت** اضافه شد
- صفحه جزئیات سفارش ادمین: **کارت پرداخت** کامل (شماره پیگیری، زمان پرداخت)
- منوی سایت: **دراپ‌داون کاربر** (حساب من، سفارش‌ها، خروج) برای مشتریان واردشده
- ۲۶ تست فیچر جدید (احراز هویت، حساب کاربری، پرداخت) — در مجموع **۳۵ تست**، همه سبز ✅

## [1.0.0] — ۲۰۲۶-۱۰-۰۴

### ⚡ نسخه اول لاراولی

- بازنویسی کامل پروژه PHP ساده با **Laravel 12** (PHP 8.2+)
- سبد خرید کامل (مبتنی بر سشن، AJAX) و تسویه‌حساب تراکنشی با اعتبارسنجی مجدد موجودی
- مدیریت سفارش با ۵ وضعیت و اسنپ‌شات قیمت‌ها
- پنل ادمین: داشبورد آماری و نمودار فروش ۷ روزه، CRUD محصول/دسته، آپلود امن تصویر، مدیریت سفارش
- امنیت: CSRF، محدودیت ورود ادمین، تولید مجدد سشن، هدرهای امنیتی، بدون رمز پیش‌فرض
- نصب یک‌فرمانی: `php artisan shop:install`
- تم دارک RTL، فونت وزیرمتن، اعداد و ارز فارسی
- README دوزبانه، لایسنس MIT، ۹ تست فیچر

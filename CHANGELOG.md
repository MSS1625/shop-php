# 📋 Changelog — تاریخچه تغییرات

All notable changes to this project are documented in this file.
(تمام تغییرات مهم این پروژه در این فایل ثبت می‌شود.)

<div dir="rtl" align="center">

راه سریع تشخیص نسخه: جدیدترین ورودی بالای هر بخش = نسخه فعلی؛ الان **v2.1.0** ✅

</div>

## 🇬🇧 English

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

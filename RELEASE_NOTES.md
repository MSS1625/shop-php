<!-- ============================================================
  نحوه استفاده | How to use:
  ۱) در گیت‌هاب: Releases → Draft a new release → Choose tag → v2.1.0 (Create on main)
  ۲) عنوان را تایپ کنید: «⚡ v2.1.0 — Payment, Accounts, CI & Project Website»
  ۳) همه‌ی متن زیر خط جداکننده (---) را در باکس توضیحات پیست کنید
  ۴) فایل shop-php-v2.1.0.zip را بکشید و رها کنید (Attach binaries)
  ۵) اگر می‌خواهید اول ببینیدش، Set as pre-release را بزنید؛ در غیر این صورت Publish release
  ============================================================ -->

---

# ⚡ v2.1.0 — Payment, Accounts, CI & Project Website

<div dir="rtl" align="center">

**دیجی‌شاپ** — فروشگاه آنلاین تجهیزات شبکه | **Digi-Shop** — Online network-equipment store
Laravel 12 · PHP 8.2+ · Bootstrap 5 RTL · MIT License

</div>

## 🇬🇧 English

Since this is the first published release, it ships **everything** — the full Laravel rewrite of the original plain-PHP shop:

- 💳 **ZarinPal payment gateway** (v4 API, server-side verification) with `mock` / `sandbox` / `production` modes + Cash on Delivery — test the whole payment flow locally with the built-in mock gateway, no merchant account needed
- 👤 **Customer accounts** — register/login, personal dashboard, order history, 4-step tracking timeline, cancel-with-restock; guest checkout still works
- 🛒 **Full cart & orders** — AJAX cart, DB-transactional checkout with stock re-validation, 5-state order workflow, price snapshots
- 🛠 **Admin panel** — dashboard with stats & 7-day sales chart, product/category CRUD, secure image upload, order management with payment columns
- 🌐 **Project website** — static demo hosted on GitHub Pages: <https://mss1625.github.io/shop-php/>
- 📖 **Bilingual docs** — [English](https://github.com/MSS1625/shop-php/blob/main/README.md) · [فارسی](https://github.com/MSS1625/shop-php/blob/main/README.fa.md)
- 🤖 **CI on every push** — test matrix PHP 8.2 / 8.3 / 8.4 + Pint style check
- 🔐 **Security hardened** — CSRF, rate-limited logins, upload whitelist, security headers, atomic payments, no default credentials
- ✅ **35 passing tests** (110 assertions)

**Quick start:**

```bash
git clone https://github.com/MSS1625/shop-php.git && cd shop-php
composer install
php artisan shop:install     # env + key + migrate + seed + link
php artisan admin:create     # interactive admin user
php artisan serve            # → http://localhost:8000
```

---

## 🇮🇷 فارسی

<div dir="rtl">

این نخستین ریلیز منتشرشده است و **همه‌چیز** را دارد — بازنویسی کامل لاراولی پروژه PHP ساده:

- 💳 **درگاه پرداخت زرین‌پال** (API نسخه ۴ با تایید سمت سرور) در سه حالت `mock` / `sandbox` / `production` + پرداخت در محل — با درگاه آزمایشی محلی، کل جریان پرداخت بدون پذیرنده قابل تست است
- 👤 **حساب کاربری مشتری** — ثبت‌نام/ورود، داشبورد شخصی، تاریخچه سفارش، تایم‌لاین رهگیری و لغو سفارش با بازگشت موجودی؛ خرید مهمان هم ممکن است
- 🛒 **سبد خرید و سفارش‌های کامل** — سبد Ajax، تسویه تراکنشی با اعتبارسنجی مجدد موجودی، گردش کار ۵ وضعیتی و اسنپ‌شات قیمت
- 🛠 **پنل مدیریت** — داشبورد آماری و نمودار ۷ روزه، CRUD محصول/دسته، آپلود امن تصویر و مدیریت سفارش‌ها
- 🌐 **وب‌سایت پروژه** — دموی ایستا روی GitHub Pages: <https://mss1625.github.io/shop-php/>
- 📖 **مستندات دوزبانه** — [English](https://github.com/MSS1625/shop-php/blob/main/README.md) · [فارسی](https://github.com/MSS1625/shop-php/blob/main/README.fa.md)
- 🤖 **CI روی هر push** — ماتریس تست PHP 8.2 / 8.3 / 8.4 + بررسی استایل Pint
- 🔐 **امنیت سخت‌گیرانه** — CSRF، محدودیت ورود، وایت‌لیست آپلود، هدرهای امنیتی، پرداخت اتمیک و بدون رمز پیش‌فرض
- ✅ **۳۵ تست پاس‌شده** (۱۱۰ ادعا)

**راه‌اندازی سریع:**

```bash
git clone https://github.com/MSS1625/shop-php.git && cd shop-php
composer install
php artisan shop:install     # env + کلید + مهاجرت + داده + لینک
php artisan admin:create     # ساخت مدیر (تعاملی)
php artisan serve            # ← http://localhost:8000
```

</div>

---

**Full changelog:** [CHANGELOG.md](https://github.com/MSS1625/shop-php/blob/main/CHANGELOG.md) · **Live demo:** <https://mss1625.github.io/shop-php/> · **Issues:** [GitHub Issues](https://github.com/MSS1625/shop-php/issues)

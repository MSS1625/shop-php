<div dir="rtl" align="center">

# ⚡ دیجی‌شاپ

**فروشگاه آنلاین تجهیزات شبکه — ساخته‌شده با Laravel**

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen?style=flat-square)](CONTRIBUTING.md)

فروشگاه اینترنتی کامل با پنل مدیریت، سبد خرید و مدیریت سفارش‌ها — راست‌چین، فارسی و با طراحی دارک مدرن.

</div>

<div align="center">

## 🇬🇧 English | [🇮🇷 فارسی](#-فارسی)

</div>

<div align="center">

| Home | Products |
|:---:|:---:|
| ![Home](screenshots/01-home.png) | ![Products](screenshots/02-products.png) |

| Admin Dashboard | Admin Orders |
|:---:|:---:|
| ![Dashboard](screenshots/07-admin-dashboard.png) | ![Orders](screenshots/09-admin-orders.png) |

</div>

## ✨ Features

- 🛒 **Full Shopping Cart** — session-based cart with AJAX add/update/remove
- 📦 **Order Management** — checkout flow, order tracking, status workflow (pending → processing → shipped → delivered / cancelled), automatic stock adjustment
- 🔍 **Search, Filter & Sort** — live product search, category filters, price sorting, pagination
- 🗂 **Categories** — dynamic categories with icons and product counts
- 📊 **Admin Dashboard** — sales stats, 7-day sales chart, low-stock alerts, latest orders
- 🖼 **Secure Image Upload** — validated images only (JPG/PNG/WebP, max 2MB), random filenames
- 🌐 **Persian-first** — RTL layout, Vazirmatn font, Persian digits (۰۱۲۳۴۵۶۷۸۹), Toman currency
- 🌙 **Dark Modern UI** — neon violet theme built on Bootstrap 5 RTL
- 🔐 **Security Hardened** — CSRF protection, rate-limited login, XSS-safe templating, mass-assignment protection, security headers

## 🚀 Quick Start (SQLite — zero config)

```bash
# 1) Clone and install
git clone https://github.com/USERNAME/shop-php.git
cd shop-php
composer install

# 2) One-command setup (env + key + migrate + seed + storage link)
php artisan shop:install

# 3) Create an admin user (interactive)
php artisan admin:create

# 4) Serve
php artisan serve
```

Open **http://localhost:8000** — the shop is ready with 8 sample products. 🎉

## 🛠 Setup with XAMPP / MySQL

1. Create an empty database named `shop_php` in phpMyAdmin
2. Edit `.env` — comment the SQLite line and uncomment the MySQL block:

```env
# DB_CONNECTION=sqlite
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shop_php
DB_USERNAME=root
DB_PASSWORD=
```

3. Run the installer:

```bash
php artisan shop:install
php artisan admin:create
php artisan serve
```

> **Requirements:** PHP 8.2+ with `pdo_sqlite` (or `pdo_mysql`), `mbstring`, `gd`, `fileinfo` — all enabled by default in XAMPP.

## 🔐 Security Features

| Threat | Protection |
|---|---|
| SQL Injection | Eloquent ORM + prepared statements everywhere |
| XSS | Blade `{{ }}` auto-escaping — no raw output |
| CSRF | `@csrf` on all forms, `VerifyCsrfToken` middleware |
| Brute Force | 5 failed logins/minute per email+IP (throttle + RateLimiter) |
| Malicious Uploads | `image` validation + MIME whitelist + 2MB limit + random names |
| Session Fixation | Session ID regenerated on login |
| Mass Assignment | Explicit `$fillable` whitelists |
| Clickjacking | `X-Frame-Options: DENY` + security headers middleware |
| Default Credentials | None — admin is created interactively via `admin:create` (or seeded with a strong random password) |

## 📁 Project Structure

```
app/
├── Console/Commands/      # shop:install و admin:create
├── Exceptions/            # StockUnavailableException
├── Http/
│   ├── Controllers/
│   │   ├── Admin/         # Dashboard, Product, Category, Order
│   │   ├── Auth/          # AdminLogin (rate-limited)
│   │   └── Shop/          # Home, Product, Cart, Checkout
│   ├── Middleware/        # EnsureUserIsAdmin, SecurityHeaders
│   └── Requests/          # Form Request validations
├── Models/                # Product, Category, Order, OrderItem, User
├── Services/Cart.php      # سرویس سبد خرید مبتنی بر سشن
└── Support/helpers.php    # توابع فارسی‌سازی اعداد (fa_num, fa_price)
database/seeders/images/   # تصاویر نمونه محصولات
public/assets/             # تم دارک + اسکریپت سبد خرید
resources/views/           # Blade templates (shop + admin + auth)
```

## 🗺 Roadmap

- [ ] Online payment gateway (ZarinPal / IDPay)
- [ ] Customer accounts & order history
- [ ] Product gallery (multiple images)
- [ ] Discount codes
- [ ] Email/SMS notifications

## 🤝 Contributing

PRs are welcome! Run `composer test` and `vendor/bin/pint` before submitting.

## 👤 Author

**Mohammad Sadegh Sedaghat** — with ❤️

## 📄 License

[MIT](LICENSE)

---

<div dir="rtl" align="center">

# ⚡ دیجی‌شاپ — فارسی

</div>

<div dir="rtl">

## ✨ امکانات

- 🛒 **سبد خرید کامل** — افزودن/حذف/تغییر تعداد بدون رفرش صفحه (Ajax)
- 📦 **مدیریت سفارش‌ها** — ثبت سفارش، گردش کار وضعیت (در انتظار → پردازش → ارسال → تحویل/لغو)، کاهش و بازگشت خودکار موجودی انبار
- 🔍 **جستجو، فیلتر و مرتب‌سازی** — جستجوی زنده محصولات، فیلتر دسته‌بندی، مرتب‌سازی بر اساس قیمت و جدیدترین، صفحه‌بندی
- 🗂 **دسته‌بندی‌ها** — دسته‌بندی پویا با آیکون و تعداد محصولات
- 📊 **داشبورد مدیریت** — آمار فروش، نمودار فروش ۷ روز اخیر، هشدار موجودی کم، آخرین سفارش‌ها
- 🖼 **آپلود امن تصویر** — فقط تصاویر معتبر (JPG/PNG/WebP حداکثر ۲ مگابایت) با نام تصادفی
- 🌐 **کاملاً فارسی** — راست‌چین، فونت وزیرمتن، اعداد فارسی (۰۱۲۳۴۵۶۷۸۹)، واحد تومان
- 🌙 **رابط دارک مدرن** — تم نئونی بنفش بر پایه Bootstrap 5 RTL

## 🚀 راه‌اندازی سریع (SQLite — بدون تنظیمات)

```bash
# ۱) دریافت و نصب وابستگی‌ها
git clone https://github.com/USERNAME/shop-php.git
cd shop-php
composer install

# ۲) نصب یک‌مرحله‌ای (env + کلید + مهاجرت + داده اولیه + لینک تصاویر)
php artisan shop:install

# ۳) ساخت کاربر مدیر (تعاملی)
php artisan admin:create

# ۴) اجرا
php artisan serve
```

حالا **http://localhost:8000** را باز کنید — فروشگاه با ۸ محصول نمونه آماده است. 🎉

## 🛠 راه‌اندازی با XAMPP / MySQL

۱. در phpMyAdmin یک دیتابیس خالی با نام `shop_php` بسازید

۲. در فایل `.env` خط SQLite را کامنت و بخش MySQL را از کامنت خارج کنید:

```env
# DB_CONNECTION=sqlite
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shop_php
DB_USERNAME=root
DB_PASSWORD=
```

۳. نصب و اجرا:

```bash
php artisan shop:install
php artisan admin:create
php artisan serve
```

> **پیش‌نیازها:** PHP نسخه 8.2 به بالا با اکستنشن‌های `pdo_sqlite` (یا `pdo_mysql`)، `mbstring`، `gd` و `fileinfo` — همه به‌صورت پیش‌فرض در XAMPP فعال هستند.

## 🔐 امنیت

| تهدید | راهکار |
|---|---|
| SQL Injection | استفاده کامل از Eloquent ORM و کوئری‌های پارامتری |
| XSS | خروجی امن خودکار Blade — `{{ }}` |
| CSRF | توکن `@csrf` در همه فرم‌ها |
| حمله Brute Force | حداکثر ۵ تلاش ناموفق در دقیقه برای هر ایمیل+IP |
| آپلود فایل خطرناک | اعتبارسنجی تصویر + وایت‌لیست فرمت + محدودیت حجم + نام تصادفی |
| Session Fixation | بازسازی شناسه سشن هنگام ورود |
| Mass Assignment | لیست سفید `$fillable` در همه مدل‌ها |
| Clickjacking | هدرهای امنیتی استاندارد |

## 🧪 تست‌ها

```bash
php artisan test
```

## 👤 نویسنده

**محمد صادق صداقت** — با ❤️

## 📄 مجوز

این پروژه تحت مجوز [MIT](LICENSE) منتشر شده است.

</div>

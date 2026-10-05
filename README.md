<div align="center">

# ⚡ دیجی‌شاپ

**Online Shop for Network Equipment — built with Laravel**

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php)](https://php.net)
[![CI](https://github.com/MSS1625/shop-php/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/MSS1625/shop-php/actions/workflows/ci.yml)
[![Release](https://img.shields.io/github/v/release/MSS1625/shop-php?style=flat-square&label=Release)](../../releases)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen?style=flat-square)](CONTRIBUTING.md)

A complete online shop with admin panel, shopping cart, ZarinPal online payment and order management — RTL, Persian-first, dark modern UI.

**🌐 Live UI preview:** <https://mss1625.github.io/shop-php/>

</div>

---

**English** | [فارسی](README.fa.md)

---

<div align="center">

| Home | Products |
|:---:|:---:|
| ![Home](screenshots/01-home.png) | ![Products](screenshots/02-products.png) |

| Checkout & Payment | Mock Gateway |
|:---:|:---:|
| ![Checkout](screenshots/checkout-payment.png) | ![Gateway](screenshots/payment-gateway.png) |

| Account Orders | Order Tracking |
|:---:|:---:|
| ![Orders](screenshots/account-orders.png) | ![Tracking](screenshots/account-order-detail.png) |

| Admin Dashboard | Admin Orders |
|:---:|:---:|
| ![Dashboard](screenshots/07-admin-dashboard.png) | ![Orders](screenshots/09-admin-orders.png) |

</div>

## 🌐 Live Preview (GitHub Pages)

A **static demo page** of the shop UI is hosted on GitHub Pages:

> **<https://mss1625.github.io/shop-php/>**

It replicates the home page (hero, categories, product cards) with the real sample products. Note that GitHub Pages only serves static files — the *full* application (cart, checkout, payment, admin panel) runs with PHP, via the 4-command quick start below. The preview link is also listed in the repo **About** sidebar, so it is visible right on the project page.

## ✨ Features

- 💳 **Online Payment (ZarinPal)** — full payment-gateway integration (ZarinPal v4 API) with server-side verification, retry-on-failure, plus Cash-on-Delivery option; ships with a local **mock gateway** so you can test the whole payment flow without a merchant account
- 👤 **Customer Accounts** — registration/login, personal dashboard with purchase stats, full order history and order tracking timeline; guests can still checkout
- 🛒 **Full Shopping Cart** — session-based cart with AJAX add/update/remove
- 📦 **Order Management** — checkout flow, order tracking, status workflow (pending → processing → shipped → delivered / cancelled), automatic stock adjustment, customer-initiated cancellation with stock restore
- 🔍 **Search, Filter & Sort** — live product search, category filters, price sorting, pagination
- 🗂 **Categories** — dynamic categories with icons and product counts
- 📊 **Admin Dashboard** — sales stats, 7-day sales chart, low-stock alerts, latest orders, payment status per order
- 🖼 **Secure Image Upload** — validated images only (JPG/PNG/WebP, max 2MB), random filenames
- 🌐 **Persian-first** — RTL layout, Vazirmatn font, Persian digits (۰۱۲۳۴۵۶۷۸۹), Toman currency
- 🌙 **Dark Modern UI** — neon violet theme built on Bootstrap 5 RTL
- 🔐 **Security Hardened** — CSRF protection, rate-limited login, XSS-safe templating, mass-assignment protection, security headers, atomic payments

## 🚀 Quick Start (SQLite — zero config)

```bash
# 1) Clone and install
git clone https://github.com/MSS1625/shop-php.git
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

## 💳 Payment Gateway (ZarinPal)

The payment layer is built on a clean `PaymentGateway` contract, so gateways are swappable. Three modes are supported via `.env`:

| Mode | `ZARINPAL_MODE` | Description |
|---|---|---|
| **Mock** (default) | `mock` | Local payment simulator — no internet or merchant account needed; perfect for development & demos |
| **Sandbox** | `sandbox` | ZarinPal sandbox environment (`sandbox.zarinpal.com`) for testing with the test merchant UUID |
| **Production** | `production` | Real payments (`payment.zarinpal.com`) |

To go live, get your **Merchant ID** (36-char UUID) from the [ZarinPal panel](https://zarinpal.com) and set:

```env
ZARINPAL_MERCHANT_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
ZARINPAL_MODE=production
# مبلغ سفارش‌ها «تومان» است و API زرین‌پال «ریال» می‌گیرد؛ ضریب پیش‌فرض ۱۰ است
ZARINPAL_AMOUNT_MULTIPLIER=10
```

**How the payment flow works:** checkout → order created (atomic, stock reserved) → redirect to gateway → server-side **verify** on callback (never trusting the query string) → order marked as `paid` with bank `ref_id`. Failed or cancelled payments can be retried by the customer. Cash on Delivery remains available as a second option.

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
| Payment Tampering | Gateway result is **verified server-to-server** (verify.json); query params are never trusted |
| IDOR on orders | Orders are visible only to their owner (or admin); payment pages are session-bound |
| Default Credentials | None — admin is created interactively via `admin:create` (or seeded with a strong random password) |

## 📁 Project Structure

```
app/
├── Console/Commands/      # shop:install و admin:create
├── Contracts/             # PaymentGateway (قرارداد درگاه پرداخت)
├── Exceptions/            # StockUnavailable, PaymentGateway, PaymentVerificationFailed
├── Http/
│   ├── Controllers/
│   │   ├── Admin/         # Dashboard, Product, Category, Order
│   │   ├── Auth/          # AdminLogin, Login, Register
│   │   └── Shop/          # Home, Product, Cart, Checkout, Payment, Account
│   ├── Middleware/        # EnsureUserIsAdmin, SecurityHeaders
│   └── Requests/          # Form Request validations
├── Models/                # Product, Category, Order, OrderItem, User
├── Services/
│   ├── Cart.php           # سرویس سبد خرید مبتنی بر سشن
│   └── Payments/          # ZarinPalGateway, MockGateway
database/seeders/images/   # تصاویر نمونه محصولات
docs/                      # صفحه دموی ایستا برای GitHub Pages
public/assets/             # تم دارک + اسکریپت سبد خرید
resources/views/           # Blade templates (shop + admin + auth + account)
.github/workflows/ci.yml   # اجرای خودکار تست روی هر push
README.md / README.fa.md   # English | فارسی
```

## 🧪 Tests & CI

```bash
php artisan test        # 35 feature tests
vendor/bin/pint --test  # code style
```

Every `push` and `pull request` runs the full test matrix automatically on GitHub Actions (PHP 8.2 / 8.3 / 8.4 + Pint) — see the badge at the top.

## 🗺 Roadmap

- [x] Online payment gateway (ZarinPal)
- [x] Customer accounts & order history
- [x] GitHub Actions CI
- [x] Project website (GitHub Pages demo)
- [ ] Product gallery (multiple images)
- [ ] Discount codes
- [ ] Email/SMS notifications

## 🤝 Contributing

PRs are welcome! Run `composer test` and `vendor/bin/pint` before submitting. See [CONTRIBUTING.md](CONTRIBUTING.md).

## 👤 Author

**Mohammad Sadegh Sedaghat** — with ❤️

## 📄 License

[MIT](LICENSE)

# 🖼 v2.2.0 — Product Gallery & CI Fix

<div dir="rtl" align="center">

**دیجی‌شاپ** — فروشگاه آنلاین تجهیزات شبکه | **Digi-Shop** — Online network-equipment store
Laravel 12 · PHP 8.2+ · Bootstrap 5 RTL · MIT License

</div>

## 🇬🇧 English

### 🖼 New — Product Image Gallery

- Every product can now have **multiple images** next to its cover: a `product_images` table with position ordering
- Admin form: multi-upload (up to 4 per submit, same validation as the cover) + per-image delete checkboxes; the current gallery is shown as thumbnails on every edit
- Product detail page: a **thumbnail strip** under the main image — click any thumb to swap the big view; products without a gallery render no extra markup
- Removal is **IDOR-safe**: delete requests always go through the product relation, so a forged image ID does nothing; files are deleted with their rows; deleting a product cleans its gallery files too

### 🔧 Fixed — CI red badge

- Root cause: `composer.lock` had been resolved on PHP 8.4 **without a platform pin**, locking packages that refuse PHP 8.2/8.3 (symfony 8.1 → `>=8.4.1`, pint 1.32 → `^8.3`), so the 8.2/8.3 CI legs failed at `composer install`
- Fix: `config.platform.php = "8.2.0"` in `composer.json` + full `composer update`; the lock now installs on **every** matrix leg (PHP 8.2 / 8.3 / 8.4) — verified with `composer install --dry-run`
- Workflow hygiene: `actions/checkout` & `actions/cache` bumped v4 → v5 (Node 20 warnings gone), runner pinned to `ubuntu-24.04` ahead of the Ubuntu 26 migration (2026-10-19)

### 📊 Numbers

- **43 feature tests** (145 assertions) — 8 new gallery tests (upload, remove, IDOR guard, 4-image cap, detail render, guest guard)
- Code style: Pint clean across 80 files

<details>
<summary>Quick start (unchanged)</summary>

```bash
git clone https://github.com/MSS1625/shop-php.git && cd shop-php
composer install
php artisan shop:install     # env + key + migrate + seed + link
php artisan admin:create     # interactive admin user
php artisan serve            # → http://localhost:8000
```

</details>

---

## 🇮🇷 فارسی

<div dir="rtl">

### 🖼 جدید — گالری تصاویر محصول

- هر محصول حالا می‌تواند در کنار تصویر اصلی، **چند تصویر** داشته باشد: جدول `product_images` با ترتیب نمایش
- فرم ادمین: آپلود چندتایی (حداکثر ۴ تصویر در هر ارسال، همان اعتبارسنجی تصویر اصلی) + چک‌باکس حذف برای هر تصویر؛ گالری فعلی در هر ویرایش به‌صورت بندانگشتی دیده می‌شود
- صفحه جزئیات محصول: **نوار بندانگشتی** زیر تصویر اصلی — کلیک روی هر بندانگشتی، تصویر بزرگ را عوض می‌کند؛ محصولِ بدون گالری هیچ نشانه اضافه‌ای رندر نمی‌کند
- حذف **ضد IDOR**: درخواست حذف همیشه از رابطه محصول می‌گذرد، پس ID جعلی تصویر هیچ کاری نمی‌کند؛ فایل‌ها همراه رکورد حذف می‌شوند و حذف محصول هم فایل‌های گالری‌اش را پاک می‌کند

### 🔧 رفع شد — بج قرمز CI

- علت: `composer.lock` روی PHP 8.4 و **بدون پین platform** رزول شده بود و بسته‌هایی قفل کرده بود که PHP 8.2/8.3 را قبول نمی‌کنند (symfony 8.1 نیازمند `>=8.4.1` و pint 1.32 نیازمند `^8.3`) — به همین دلیل پاهای 8.2/8.3 در مرحله `composer install` رد می‌شدند
- راه‌حل: `config.platform.php = "8.2.0"` در `composer.json` + یک `composer update` کامل؛ قفل جدید روی **همه** پاهای ماتریس (PHP 8.2 / 8.3 / 8.4) نصب می‌شود — با `composer install --dry-run` تأیید شد
- به‌روزرسانی اکشن‌های GitHub Actions به نسخه v5 (رفع هشدار Node 20) و پین‌کردن رانر روی `ubuntu-24.04` برای پیشگیری از مشکل مهاجرت Ubuntu 26 (۱۹ اکتبر ۲۰۲۶)

### 📊 اعداد

- **۴۳ تست فیچر** (۱۴۵ ادعا) — ۸ تست جدید گالری (آپلود، حذف، محافظ IDOR، سقف ۴ تصویر، رندر جزئیات، گارد مهمان)
- سبک کد: Pint پاک روی ۸۰ فایل

<details>
<summary>راه‌اندازی سریع (بدون تغییر)</summary>

```bash
git clone https://github.com/MSS1625/shop-php.git && cd shop-php
composer install
php artisan shop:install     # env + کلید + مهاجرت + داده + لینک
php artisan admin:create     # ساخت مدیر (تعاملی)
php artisan serve            # ← http://localhost:8000
```

</details>

</div>

---

**Full changelog:** [CHANGELOG.md](https://github.com/MSS1625/shop-php/blob/main/CHANGELOG.md) · **Live demo:** <https://mss1625.github.io/shop-php/> · **Issues:** [GitHub Issues](https://github.com/MSS1625/shop-php/issues)

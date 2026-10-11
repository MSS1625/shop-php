<!-- ============================================================
  نحوه استفاده | How to use:
  ۱) در گیت‌هاب: Releases → Draft a new release → Choose tag → v2.3.0 (Create on main)
  ۲) عنوان: «🎟 v2.3.0 — Discount Coupon Codes»
  ۳) متن زیر خط جداکننده (---) را در باکس توضیحات پیست کنید
  ۴) در صورت تمایل، فایل shop-php-v2.3.0.zip را بکشید و رها کنید
  ۵) Publish release
  ============================================================ -->

---

# 🎟 v2.3.0 — Discount Coupon Codes

<div dir="rtl" align="center">

**دیجی‌شاپ** — فروشگاه آنلاین تجهیزات شبکه | **Digi-Shop** — Online network-equipment store
Laravel 12 · PHP 8.2+ · Bootstrap 5 RTL · MIT License

</div>

## 🇬🇧 English

### 🎟 New — Discount Coupon Codes

- Create **percent or fixed-amount** coupon codes with optional minimum cart amount, usage cap and start/expiry window — all from a new «کدهای تخفیف» (Coupons) page in the admin panel
- Customers apply a code right in the cart: wrong, expired, exhausted or below-minimum codes get clear Persian error messages; a valid code shows the chip, the discount line and the **final payable amount**
- The discount is re-validated at checkout — if the code became invalid the customer is sent back to the cart instead of being silently charged full price
- Race-safe redemption: the coupon row is locked inside the order transaction and re-validated before usage is counted, so the usage cap cannot be bypassed by concurrent checkouts
- Fixed-amount discounts are capped at the cart subtotal — payable never goes below zero
- Orders store the full breakdown (`subtotal` / `discount_amount` / `coupon_code`) — visible on the success page, in the customer account and in the admin order view
- Input is normalized everywhere: `summer 500` → `SUMMER500` (Persian digits and spaces handled)
- A demo coupon **WELCOME10** (10٪, 100 uses) is seeded by `shop:install` so you can try it immediately

### 📊 Numbers

- **61 feature tests** (231 assertions) — 18 new coupon tests (apply/remove/reject, cap math, checkout storage, usage increment, invalid-at-checkout guard, admin CRUD + guards)
- Code style: Pint clean across 87 files

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

---

## 🇮🇷 فارسی

<div dir="rtl">

### 🎟 جدید — کدهای تخفیف

- ساخت کد تخفیف **درصدی یا مبلغ ثابت** با حداقل مبلغ سبد، سقف تعداد استفاده و بازه اعتبار — همه از صفحه جدید «کدهای تخفیف» در پنل مدیریت
- مشتری کد را همان‌جا در سبد خرید اعمال می‌کند: کد اشتباه/منقضی/تکمیل‌ظرفیت/کمتر از حداقل، پیام خطای فارسی روشن می‌گیرد؛ کد معتبر چیپ کد + خط تخفیف + **مبلغ قابل پرداخت نهایی** را نشان می‌دهد
- تخفیف در لحظه ثبت سفارش دوباره اعتبارسنجی می‌شود — اگر کد نامعتبر شده باشد مشتری به سبد برمی‌گردد، نه اینکه بی‌خبر مبلغ کامل بپردازد
- مصرف ضدرقابت: ردیف کوپن داخل تراکنش سفارش قفل و دوباره اعتبارسنجی می‌شود، پس سقف مصرف با سفارش‌های همزمان قابل دور زدن نیست
- تخفیف مبلغ ثابت سقف‌دار به جمع سبد است — مبلغ قابل پرداخت هرگز منفی نمی‌شود
- سفارش تفکیک کامل را ذخیره می‌کند (`subtotal` / `discount_amount` / `coupon_code`) — در صفحه موفقیت، حساب مشتری و ویو سفارش ادمین دیده می‌شود
- ورودی همه‌جا نرمال می‌شود: `summer 500` ← `SUMMER500` (ارقام فارسی و فاصله هم مدیریت می‌شود)
- با `shop:install` کد نمونه **WELCOME10** (۱۰٪، ۱۰۰ بار) ساخته می‌شود تا همین حالا امتحانش کنید

### 📊 اعداد

- **۶۱ تست فیچر** (۲۳۱ ادعا) — ۱۸ تست جدید کوپن (اعمال/حذف/رد، ریاضی سقف، ذخیره در سفارش، شمارش مصرف، گارد نامعتبری در ثبت، CRUD و گارد ادمین)
- سبک کد: Pint پاک روی ۸۷ فایل

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

</div>

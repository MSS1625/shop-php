# 🔍 v2.8.0 — Smart Product Discovery

<div dir="rtl" align="center">

**دیجی‌شاپ** — فروشگاه آنلاین تجهیزات شبکه | **Digi-Shop** — Online network-equipment store
Laravel 12 · PHP 8.2+ · Bootstrap 5 RTL · MIT License

</div>

## 🇬🇧 English

### 🔍 New — Price Range, Availability & Best-Selling Sort

- The products page gained a **price-range filter** — minimum and maximum, each usable on its own — plus an **in-stock-only toggle**, and both combine freely with search, category and any sort order; active filters also survive every pagination link
- **Persian keyboard friendly, again**: price inputs normalize Persian/Arabic digits and thousands separators, so `۵۰۰٬۰۰۰`, `500,000` and `500000` all mean the same thing — the same approach as coupon codes and order tracking, now extracted into a tiny `en_num()` helper (the mirror of the existing `fa_num()`)
- **Forgiving by design**: if the range is entered backwards (min > max) it is silently swapped instead of returning an empty grid, and non-numeric input is simply ignored — no error pages, no lost context
- New **«پرفروش‌ترین» (best-selling)** sort: products ranked by units sold through a single `withSum` subquery — items of cancelled orders never count (the same rule the admin dashboard follows), and ties break toward the newest product
- **Related products, upgraded**: same category and active as before, but the random shuffle is gone — **in-stock items now come first** (newest first within each group) and out-of-stock ones only fill leftover slots; the section is deterministic and fully testable
- A «فیلتر فعال» indicator with a one-click reset sits next to the result count, and the empty state offers a filter reset too — validation lives in a dedicated `ProductFilterRequest`

### 📊 Numbers

- **133 feature tests** (537 assertions) — 21 new discovery tests: price min/max/range, backwards-range swap, Persian digits & separators, graceful non-numeric input, in-stock filter, popular ranking, cancelled-item exclusion, newest tie-break, combined filters, pagination query-string, indicator/reset rendering, validation failures, inactive-product regression, and related-products ordering/exclusion/fallback
- Code style: Pint clean across 109 files
- Zero migrations, zero new config — drop-in upgrade

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
# Open http://localhost:8000/products
# → type ۵۰۰٬۰۰۰ in «حداقل قیمت» and ۵۰۰۰۰۰۰ in «حداکثر قیمت», tick
#   «فقط کالاهای موجود», hit اعمال — the grid narrows, the count shows
#   «فیلتر فعال», and pagination keeps everything.
# → Swap the two prices on purpose (big number first): same results.
# → Pick «پرفروش‌ترین» in the sort select: best sellers first.
# → Open any product: related items now show available ones first.
```

</details>

---

## 🇮🇷 فارسی

<div dir="rtl">

### 🔍 جدید — فیلتر بازهٔ قیمت، موجودی و مرتب‌سازی پرفروش‌ترین

- صفحهٔ محصولات به **فیلتر بازهٔ قیمت** مجهز شد — حداقل و حداکثر، هر کدام به‌تنهایی هم کار می‌کند — به‌علاوهٔ گزینهٔ **«فقط کالاهای موجود»»؛ هر دو آزادانه با جستجو، دسته و هر مرتب‌سازی ترکیب می‌شوند و فیلترهای فعال در تمام لینک‌های صفحه‌بندی هم حفظ می‌شوند
- **باز هم سازگار با کیبورد فارسی**: ورودی‌های قیمت ارقام فارسی/عربی و جداکنندهٔ هزارگان را نرمال می‌کنند — `۵۰۰٬۰۰۰` و `500,000` و `500000` هر سه یک معنی دارند؛ همان رویکرد کد تخفیف و پیگیری سفارش، این بار در قالب هلپر کوچک `en_num()` (قرینهٔ `fa_num()` موجود)
- **بخشنده در طراحی**: اگر بازه برعکس وارد شود (حداقل > حداکثر) بی‌صدا جابه‌جا می‌شود تا نتیجهٔ خالی نبینید، و ورودی غیرعددی هم نادیده گرفته می‌شود — نه صفحهٔ خطا، نه بافتِ ازدست‌رفته
- مرتب‌سازی جدید **«پرفروش‌ترین»**: رتبه‌بندی محصولات بر اساس تعداد فروش با یک زیرکوئری `withSum` — اقلام سفارش‌های لغوشده هرگز حساب نمی‌شوند (همان قاعدهٔ داشبورد مدیریت) و تساوی‌ها به نفع محصول جدیدتر می‌شکنند
- **محصولات مشابه، ارتقا یافت**: هم‌دسته و فعال مثل قبل، اما چیدمان تصادفی حذف شد — **موارد موجود اول می‌آیند** (داخل هر گروه، جدیدترین اول) و ناموجودها فقط جاهای خالی را پر می‌کنند؛ بخش حالا قطعی و کاملاً قابل‌آزمون است
- نشانگر «فیلتر فعال» با دکمهٔ حذف یک‌کلیکی کنار تعداد نتایج نشسته، حالت خالی هم دکمهٔ حذف فیلترها دارد — و اعتبارسنجی در `ProductFilterRequest` اختصاصی منتقل شده

### 📊 اعداد

- **۱۳۳ تست فیچر** (۵۳۷ ادعا) — ۲۱ تست جدید کشف محصول: حداقل/حداکثر/بازهٔ قیمت، جابه‌جایی بازهٔ معکوس، ارقام فارسی و جداکننده، ورودی غیرعددی، فیلتر موجودی، رتبه‌بندی پرفروش، حذف اقلام لغوشده، شکست تساوی با جدیدترین، ترکیب فیلترها، رشتهٔ کوئری صفحه‌بندی، نمایش نشانگر و دکمهٔ حذف، خطاهای اعتبارسنجی، رگرسیون محصولات غیرفعال و ترتیب/حذف/جانشینی محصولات مشابه
- سبک کد: Pint پاک روی ۱۰۹ فایل
- بدون مهاجرت دیتابیس، بدون کانفیگ جدید — ارتقای درجا

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
# http://localhost:8000/products را باز کنید
# ← در «حداقل قیمت» بنویسید ۵۰۰٬۰۰۰ و در «حداکثر قیمت» ۵۰۰۰۰۰۰،
#   «فقط کالاهای موجود» را تیک بزنید و «اعمال» را بزنید —
#   شبکه محدود می‌شود، کنار تعداد «فیلتر فعال» می‌آید و
#   صفحه‌بندی همه‌چیز را حفظ می‌کند.
# ← عمداً دو قیمت را برعکس وارد کنید (عدد بزرگ اول): همان نتایج.
# ← در مرتب‌سازی «پرفروش‌ترین» را انتخاب کنید: پرفروش‌ها اول.
# ← یک محصول را باز کنید: مشابه‌های موجود اول نمایش داده می‌شوند.
```

</details>

</div>

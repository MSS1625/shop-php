<!-- ============================================================
  نحوه استفاده | How to use:
  ۱) در گیت‌هاب: Releases → Draft a new release → Choose tag → v2.6.0 (Create on main)
  ۲) عنوان: «📊 v2.6.0 — Admin Reports Dashboard»
  ۳) متن زیر خط جداکننده (---) را در باکس توضیحات پیست کنید
  ۴) در صورت تمایل، فایل shop-php-v2.6.0.zip را بکشید و رها کنید
  ۵) Publish release
  ============================================================ -->

---

# 📊 v2.6.0 — Admin Reports Dashboard

<div dir="rtl" align="center">

**دیجی‌شاپ** — فروشگاه آنلاین تجهیزات شبکه | **Digi-Shop** — Online network-equipment store
Laravel 12 · PHP 8.2+ · Bootstrap 5 RTL · MIT License

</div>

## 🇬🇧 English

### 📊 New — Admin Reports Dashboard

- The admin dashboard is now a full reports page, built around **revenue**: today / this month / all-time / average order value — cancelled orders are excluded from every sales figure
- The sales chart grew from 7 days to a **14-day chart fed by a single grouped query** (previously one query per day); days without sales are zero-filled so the chart is always complete, and every bar carries its day's order count and amount
- New **best-sellers table** — products ranked by units sold with per-product revenue; items belonging to cancelled orders are excluded
- New **order-status breakdown** — count, percentage share and a progress bar per status, plus a footer line with this month's new customers and product counts
- All dashboard queries now live in one testable `DashboardStats` service — the controller became a thin pass-through
- The low-stock panel gained a separate **out-of-stock badge**

### ⚙️ Optional config (works out of the box)

```env
# .env — both optional, defaults shown:
SHOP_DASHBOARD_DAYS=14          # length of the sales chart
SHOP_LOW_STOCK_THRESHOLD=3      # low-stock alert threshold
```

### 📊 Numbers

- **96 feature tests** (406 assertions) — 10 new dashboard tests: access guards, KPI math with cancelled and previous-month orders, chart series coverage, best-seller ranking, status percentages, low-stock threshold (default and overridden) and empty states
- Code style: Pint clean across 105 files

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
# log in at /admin/login → the dashboard is the first page:
# four revenue KPI cards, the 14-day chart, the status breakdown,
# best sellers, low-stock alerts and the latest orders.
# Place a test order (mock gateway) and refresh — today's numbers move.
```

</details>

---

## 🇮🇷 فارسی

<div dir="rtl">

### 📊 جدید — داشبورد گزارش‌های مدیریت

- داشبورد ادمین حالا یک صفحهٔ کامل گزارش‌ها است، حول **فروش**: امروز / این ماه / کل / میانگین ارزش سفارش — سفارش‌های لغوشده از همهٔ اعداد فروش حذف هستند
- نمودار فروش از ۷ روز به **نمودار ۱۴ روزه با یک کوئری گروهی** ارتقا یافت (قبلاً برای هر روز یک کوئری زده می‌شد)؛ روزهای بدون فروش صفر پر می‌شوند تا نمودار همیشه کامل باشد و روی هر ستون تعداد سفارش و مبلغ همان روز هست
- جدول جدید **پرفروش‌ترین محصولات** — رتبه‌بندی بر اساس تعداد فروش به‌همراه درآمد هر محصول؛ اقلام سفارش‌های لغوشده حساب نمی‌شوند
- تفکیک جدید **وضعیت سفارش‌ها** — تعداد، درصد سهم و نوار پیشرفت برای هر وضعیت + سطر پایانی با کاربران جدید این ماه و تعداد محصولات
- تمام کوئری‌های داشبورد در سرویس قابل‌آزمون `DashboardStats` جمع شدند — کنترلر فقط لاغر شده است
- پنل موجودی کم به **بج جداگانهٔ ناموجود** مجهز شد

### ⚙️ تنظیمات اختیاری (بدون تنظیم هم کار می‌کند)

```env
# .env — هر دو اختیاری، پیش‌فرض‌ها:
SHOP_DASHBOARD_DAYS=14          # طول نمودار فروش
SHOP_LOW_STOCK_THRESHOLD=3      # آستانهٔ هشدار موجودی
```

### 📊 اعداد

- **۹۶ تست فیچر** (۴۰۶ ادعا) — ۱۰ تست جدید داشبورد: گاردهای دسترسی، ریاضی شاخص‌ها با سفارش لغوشده و ماه قبل، پوشش سری نمودار، رتبه‌بندی پرفروش‌ها، درصدهای وضعیت، آستانهٔ موجودی (پیش‌فرض و override) و حالت‌های خالی
- سبک کد: Pint پاک روی ۱۰۵ فایل

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
# وارد /admin/login شوید → داشبورد اولین صفحه است:
# چهار کارت شاخص فروش، نمودار ۱۴ روزه، تفکیک وضعیت،
# پرفروش‌ها، هشدارهای موجودی و آخرین سفارش‌ها.
# یک سفارش تستی ثبت کنید (درگاه mock) و رفرش کنید — اعداد امروز جابه‌جا می‌شوند.
```

</details>

</div>

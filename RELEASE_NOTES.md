# 🔑 v2.14.0 — پنل مدیریت توکن‌های API (Admin API Token Management)

<div dir="rtl" align="center">

**دیجی‌شاپ** — فروشگاه آنلاین تجهیزات شبکه | **Digi-Shop** — Online network-equipment store
Laravel 12 · PHP 8.2+ · Bootstrap 5 RTL · MIT License

</div>

## 🇬🇧 English

### 🔑 New — issue, audit and revoke API tokens from the admin panel

- The v2.12.0 REST API shipped tokens nobody could see. Now `/admin/tokens` lists **every personal access token in one table** — owner (blue «مدیر» badge for manager tokens, «کاربر حذف‌شده» for orphans), device name, issue date, **last use** («هرگز» badge for never-used ones) and expiry (red «منقضی‌شده» badge for dead ones)
- **Issue tokens without touching a terminal**: pick a user, name the device (`iPhone 15`…), choose a lifetime — **no expiry, 7, 30 or 90 days**; the full plain token appears **exactly once** in a modal after the redirect (copy button included) — refresh and it's gone forever; the database still stores only the hash
- **Expiry is enforced by Sanctum itself**, at authentication time: «۷ روز» really means the token dies on day 7 — and every authenticated API call stamps `last_used_at`, so the panel always knows when a device last called
- **Revoke in one click** (with a confirm dialog): the device must log in again; the user's orders, reviews and everything else stay untouched. Tabs «همه / بدون استفاده / منقضی‌شده» (with counts) point at the cleanup candidates

### 📊 Numbers

- **297 feature tests** (1157 assertions) — all green; code style: Pint clean
- **Zero migrations, zero config changes** — apply the zip and you're done; `shop:install` has nothing new to run
- 1 new controller (`Admin/TokenController`); 1 new admin view (`admin/tokens`); 3 new routes (`GET/POST /admin/tokens`, `DELETE /admin/tokens/{token}`); 14 new tests in `TokenManagementTest`

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

Log into **http://localhost:8000/admin** and click **«توکن‌های API»** in the sidebar:

- the table shows every token: who owns it, on which device, when it last called,
  when it expires — manager tokens carry the blue «مدیر» badge
- open the **«بدون استفاده»** tab — those are the forgotten tokens; the
  **«منقضی‌شده»** tab shows the dead ones (red badge)
- issue one from the form: pick a user, type `iPhone 15`, choose «۷ روز» and
  hit **«صدور توکن»** — a modal shows the full token **once** (copy it);
  refresh the page — it never appears again
- call the API with it, then **revoke** it from the panel and try again — the
  same token is now 401, the device must log in again

Want the proof without the UI? Here is a full lifecycle through real endpoints:

```bash
# 1) the admin panel issues a token (form → PRG; token appears once in the modal HTML)
#    — do it once in the browser and copy the value, then:

# 2) the token works against the real API:
curl -s http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer 3|COPY_THE_TOKEN_FROM_THE_MODAL"
# → {"data":{"id":2,"name":"...", "email":"..."}}   and last_used_at gets stamped

# 3) admin revokes it (one click in /admin/tokens) — the same token is now dead:
curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer 3|COPY_THE_TOKEN_FROM_THE_MODAL"
# → 401  (the device must log in again; the user's data is untouched)
```

</details>

---

## 🇮🇷 فارسی

<div dir="rtl">

### 🔑 جدید — صدور، گزارش و ابطالِ توکن‌های API از پنل مدیریت

- API نسخهٔ v2.12.0 توکن‌هایی صادر می‌کرد که کسی نمی‌توانست ببیند. حالا `/admin/tokens` **همهٔ توکن‌های دسترسی شخصی را در یک جدول** می‌آورد — صاحب (بج آبی «مدیر» برای توکن مدیران، «کاربر حذف‌شده» برای یتیم‌ها)، نام دستگاه، تاریخ صدور، **آخرین استفاده** (بج «هرگز» برای هرگز استفاده‌نشده‌ها) و انقضا (بج قرمز «منقضی‌شده» برای مرده‌ها)
- **صدور توکن بدون خط فرمان**: یک کاربر را انتخاب کنید، دستگاه را نام‌گذاری کنید (`iPhone 15`…)، عمر را تعیین کنید — **بدون انقضا، ۷، ۳۰ یا ۹۰ روز**؛ مقدار کامل توکن **دقیقاً یک‌بار** بعد از ریدایرکت در مودال ظاهر می‌شود (با دکمهٔ کپی) — رفرش کنید و برای همیشه می‌رود؛ دیتابیس همچنان فقط هش را نگه می‌دارد
- **انقضا را خود Sanctum**، در لحظهٔ احراز هویت، اجرا می‌کند: «۷ روز» واقعاً یعنی روز هفتم توکن می‌میرد — و هر فراخوانی احرازشدهٔ API، `last_used_at` را مهر می‌زند تا پنل همیشه بداند هر دستگاه آخرین بار کِی زنگ زده
- **ابطال با یک کلیک** (با دیالوگ تأیید): دستگاه باید دوباره وارد شود؛ سفارش‌ها، نظرات و بقیهٔ سوابق همان کاربر دست‌نخورده می‌مانند. تب‌های «همه / بدون استفاده / منقضی‌شده» (با شمارنده) کاندیداهای پاک‌سازی را نشانتان می‌دهند

### 📊 اعداد

- **۲۹۷ تست فیچر** (۱۱۵۷ ادعا) — همه سبز؛ سبک کد: Pint پاک
- **بدون مهاجرت جدید، بدون تغییر کانفیگ** — زیپ را اعمال کنید و تمام؛ `shop:install` چیز تازه‌ای برای اجرا ندارد
- ۱ کنترلر جدید (`Admin/TokenController`)؛ ۱ نمای جدید (`admin/tokens`)؛ ۳ مسیر جدید (`GET/POST /admin/tokens` و `DELETE /admin/tokens/{token}`)؛ ۱۴ تست جدید در `TokenManagementTest`

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

وارد **http://localhost:8000/admin** شوید و در نوار کناری روی **«توکن‌های API»** کلیک کنید:

- جدول همهٔ توکن‌ها را نشان می‌دهد: صاحبِ هر کدام، روی کدام دستگاه، آخرین بار کِی
  زنگ زده، کِی می‌میرد — توکن مدیران بج آبی «مدیر» دارند
- تب **«بدون استفاده»** را باز کنید — این‌ها همان توکن‌های فراموش‌شده‌اند؛ تب
  **«منقضی‌شده»** مرده‌ها را با بج قرمز نشان می‌دهد
- از فرم یکی صادر کنید: یک کاربر را انتخاب کنید، `iPhone 15` بنویسید، «۷ روز»
  را بردارید و **«صدور توکن»** را بزنید — مودال مقدار کامل را **یک‌بار** نشان
  می‌دهد (کپی کنید)؛ صفحه را رفرش کنید — دیگر هرگز دیده نمی‌شود
- با آن API را صدا بزنید، بعد از پنل **ابطالش** کنید و دوباره امتحان کنید —
  همان توکن حالا 401 است؛ دستگاه باید دوباره وارد شود

بدون UI هم می‌خواهید ببینید؟ این یک چرخهٔ کامل از دل نقاط اتصال واقعی است:

```bash
# ۱) پنل توکن را صادر می‌کند (فرم → PRG؛ توکن فقط یک‌بار در HTML مودال هست)
#    — یک‌بار در مرورگر انجامش بدهید و مقدار را کپی کنید، بعد:

# ۲) توکن در API واقعی کار می‌کند:
curl -s http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer 3|مقدار_توکن_را_از_مودال_کپی_کنید"
# → {"data":{"id":2,"name":"...", "email":"..."}}   و last_used_at مهر می‌خورد

# ۳) مدیر از پنل ابطالش کند (یک کلیک در /admin/tokens) — همان توکن مرده است:
curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer 3|مقدار_توکن_را_از_مودال_کپی_کنید"
# → 401  (دستگاه باید دوباره وارد شود؛ داده‌های کاربر دست‌نخورده است)
```

</details>

</div>

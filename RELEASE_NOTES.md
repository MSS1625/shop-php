
# ⭐ v2.9.0 — Product Reviews & Ratings

<div dir="rtl" align="center">

**دیجی‌شاپ** — فروشگاه آنلاین تجهیزات شبکه | **Digi-Shop** — Online network-equipment store
Laravel 12 · PHP 8.2+ · Bootstrap 5 RTL · MIT License

</div>

## 🇬🇧 English

### ⭐ New — Product Reviews & Ratings

- Customers can now **rate products 1–5 stars and write a review** on the product page — every review starts as pending and only goes public after an admin approves it; nothing unmoderated ever reaches the storefront
- **«خریدار تأییدشده» (verified buyer) badge** — a review whose author actually bought that product in a non-cancelled order gets the green badge; computed with a single query for the whole page, following the same cancelled-excluded rule as the dashboard's sales figures
- The product page gained a **rating summary**: big Persian-decimal average («۴٫۳»), star row and a **5→1 breakdown chart** with progress bars — plus a stars+average link under the product title that jumps straight to the reviews
- **Product cards show the rating now** (star, average, count) across the home page, listing and related products — one `withRating()` scope, two subqueries, the same pattern as the best-selling sort
- Sensible rules baked in: one review per user per product (DB unique + friendly error + race-safe catch), the form becomes a **status box** after reviewing, guests get a login prompt, `auth` + `throttle:5,1` keep the spam out
- A polished **RTL star picker** with hover-to-preview, and validation in a dedicated `ReviewRequest` (rating 1–5, comment 5–2000, Persian messages)
- New **admin reviews page** at `/admin/reviews`: status tabs with counts, one-click **approve / reject / delete**, product links, and a pending badge in the sidebar — mirroring the orders workflow

### 📊 Numbers

- **162 feature tests** (632 assertions) — 29 new review tests: access guards, validation, pending-until-approved flow, rejection, one-review rule, rate limit, average/count/breakdown rendering, approved-only aggregates, verified-buyer badge (real / non-buyer / cancelled order), guest prompt, own-review status box, admin moderation flows, sidebar badge, product-deletion cascade
- Code style: Pint clean across 110 files
- One new migration (`reviews`) — `php artisan shop:install` or plain `migrate` handles it; zero config changes

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
# Register an account, open any product and scroll to «نظرات خریداران»:
# → hover the star picker, pick 5, write a comment, «ثبت نظر» —
#   you get the «نظر شما ثبت شد و پس از تأیید…» message and a status box.
# → Log in as admin → نظرات: the sidebar shows a pending badge (۲);
#   the tabs read در انتظار تأیید (۲) / تأییدشده (۶) — hit the ✓ on a
#   pending review: it instantly appears on the product page, badge
#   «خریدار تأییدشده» included for real buyers.
# → The products listing now shows «★ ۴٫۳ (۴ نظر)» on rated cards.
```

</details>

---

## 🇮🇷 فارسی

<div dir="rtl">

### ⭐ جدید — نظرات و امتیازدهی محصولات

- مشتری‌ها حالا روی صفحهٔ محصول می‌توانند **از ۱ تا ۵ ستاره امتیاز بدهند و نظر بنویسند** — هر نظر با وضعیت «در انتظار تأیید» شروع می‌شود و فقط بعد از تأیید مدیر عمومی می‌شود؛ هیچ نظر بررسی‌نشده‌ای راه ویترین پیدا نمی‌کند
- نشان **«خریدار تأییدشده»** — نویسنده‌ای که واقعاً همان محصول را در سفارشی غیرلغوشده خریده، نشان سبز می‌گیرد؛ با یک کوئری برای کل صفحه و با همان قاعدهٔ حذف سفارش‌های لغوشدهٔ داشبورد
- صفحهٔ محصول به **خلاصهٔ امتیازها** مجهز شد: میانگین درشت با رقم اعشار فارسی («۴٫۳»)، ردیف ستاره‌ها و **نمودار تفکیک ۵ به ۱** با نوارهای پیشرفت — به‌علاوه لینک ستاره‌ها + میانگین زیر عنوان محصول که مستقیم به بخش نظرات می‌پرد
- **کارت‌های محصول هم امتیاز نشان می‌دهند** (ستاره، میانگین، تعداد نظر) در صفحهٔ اصلی، لیست محصولات و محصولات مشابه — یک اسکوپ `withRating()`، دو زیرکوئری؛ همان الگوی پرفروش‌ترین
- قواعد حساب‌شده: هر کاربر برای هر محصول یک نظر (کلید یکتا + پیام دوستانه + مقاوم به ثبت همزمان)، فرم بعد از ثبت به **جعبهٔ وضعیت** تبدیل می‌شود، مهمان‌ها راهنمای ورود می‌بینند و `auth` + `throttle:5,1` جلوی اسپم را می‌گیرد
- **انتخاب ستارهٔ راست‌به‌چپِ** صیقلی با پیش‌نمایش hover و اعتبارسنجی در `ReviewRequest` اختصاصی (امتیاز ۱ تا ۵، متن ۵ تا ۲۰۰۰ کاراکتر، پیام‌های فارسی)
- صفحهٔ جدید **مدیریت نظرات** در `/admin/reviews`: تب‌های وضعیت با شمارنده، دکمه‌های یک‌کلیکی **تأیید / رد / حذف**، لینک محصولات و بج «در انتظار تأیید» در سایدبار — قرینهٔ جریان سفارش‌ها

### 📊 اعداد

- **۱۶۲ تست فیچر** (۶۳۲ ادعا) — ۲۹ تست جدید نظرات: گاردهای دسترسی، اعتبارسنجی، جریان تأیید تا نمایش، رد نظر، قاعدهٔ تک‌نظر، محدودیت نرخ، رندر میانگین/تعداد/تفکیک، شمارش فقط تأییدشده‌ها، نشان خریدار تأییدشده (واقعی / غیرخریدار / سفارش لغوشده)، راهنمای ورود مهمان، جعبهٔ وضعیت، جریان‌های مدیریت، بج سایدبار و حذف آبشاری
- سبک کد: Pint پاک روی ۱۱۰ فایل
- یک مهاجرت جدید (`reviews`) — با `php artisan shop:install` یا `migrate` ساده اعمال می‌شود؛ بدون تغییر کانفیگ

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
# ثبت‌نام کنید، یک محصول را باز کنید و به «نظرات خریداران» بروید:
# ← روی ستاره‌ها hover کنید، ۵ ستاره را انتخاب کنید، نظر بنویسید و
#   «ثبت نظر» را بزنید — پیام «نظر شما ثبت شد و پس از تأیید…» + جعبهٔ وضعیت.
# ← به‌عنوان مدیر وارد شوید ← نظرات: سایدبار بج (۲) نشان می‌دهد؛
#   تب‌ها: در انتظار تأیید (۲) / تأییدشده (۶) — روی یک نظر در انتظار ✓ بزنید:
#   بلافاصله در صفحهٔ محصول می‌آید، با نشان «خریدار تأییدشده» برای خریداران واقعی.
# ← لیست محصولات حالا روی کارت‌های امتیازدار «★ ۴٫۳ (۴ نظر)» نشان می‌دهد.
```

</details>

</div>

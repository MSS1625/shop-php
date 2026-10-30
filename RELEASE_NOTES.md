# ❓ v2.11.0 — Product Questions & Answers

<div dir="rtl" align="center">

**دیجی‌شاپ** — فروشگاه آنلاین تجهیزات شبکه | **Digi-Shop** — Online network-equipment store
Laravel 12 · PHP 8.2+ · Bootstrap 5 RTL · MIT License

</div>

## 🇬🇧 English

### ❓ New — Product Questions & Answers

- Customers can now **ask a question right on the product page** — a new «پرسش و پاسخ» section sits below the reviews; questions start as «در انتظار پاسخ» and stay private until answered, so nothing unanswered ever reaches the storefront
- **The store answers with one voice**: the new `/admin/questions` panel lists every question with status tabs and counts (همه / در انتظار پاسخ / پاسخ داده شد / ردشده) — and the answer box sits **right inside the table row**, so answering is one textarea + one click, no page jumps
- **Publishing and notifying in a single step**: the moment an admin answers, the question goes public and the asker receives a **«پاسخ پرسش شما» email** — built on the same mail design as the order notifications, question and answer side by side with a link back to the product; editing an answer later is silent, and an SMTP failure never blocks the answering itself (logged, not thrown)
- The public Q&A cards carry the same **«خریدار تأییدشده» (verified buyer) badge** as reviews — computed for the whole page with a single query (no N+1), reusing the same cancelled-excluded rule
- Unlike reviews, **one user may ask several questions** about the same product — real buyers ask follow-ups; anti-spam stays intact via `auth` + `throttle:5,1` plus admin reject/delete
- The admin sidebar gained a **«پرسش‌ها» link with a pending-count badge** next to «نظرات» — mirroring the orders and reviews workflows
- 35 new tests (QuestionTest): submission guards and validation, visibility rules, pagination, verified badges, email lifecycle (sent once, silent on edit, never breaking on failure), moderation flows, access guards and cascade integrity

### 📊 Numbers

- **216 feature tests** (778 assertions) — 35 new Q&A tests: guest redirect, body required/min-length, pending start + trim, multiple questions per user, inactive 404, rate limit 429, pending/rejected invisible, answered visible + count heading, 5-per-page pagination (newest first), verified badge (real buyer / non-buyer / cancelled-order asker), guest login prompt vs authed form, empty state, admin access guards, tabs + counts, answer flow + publishing, answer validation, email sent / not on reject / not resent on edit / never breaking, first answered_at preserved on edit, rejected-question revival, reject, delete, customer moderation 403, sidebar badge, product/user cascade, excerpt
- Code style: Pint clean
- One new migration (`questions`) — `php artisan shop:install` or plain `migrate` handles it; zero config changes

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
# Register an account, open any product page:
# → scroll below the reviews — the «پرسش و پاسخ» section:
#   type «گارانتی این محصول چند سال است؟» and hit «ثبت پرسش».
# → Log in as admin → /admin/questions: the question waits in
#   «در انتظار پاسخ» with a yellow sidebar badge. Type the answer
#   right inside the row and click «ثبت پاسخ».
# → Back on the product page the question + «پاسخ دیجی‌شاپ» are now
#   public — and the asker's inbox has the «پاسخ پرسش شما» email.
# → Edit the answer in admin: the page updates, but no second
#   email goes out — edits are silent.
```

</details>

---

## 🇮🇷 فارسی

<div dir="rtl">

### ❓ جدید — پرسش و پاسخ محصولات

- مشتری‌ها حالا می‌توانند **مستقیم در صفحهٔ محصول سؤال بپرسند** — بخش جدید «پرسش و پاسخ» زیر نظرات قرار دارد؛ پرسش‌ها «در انتظار پاسخ» شروع می‌شوند و تا پاسخ‌گرفتن خصوصی می‌مانند؛ هیچ پرسش بی‌پاسخی به فروشگاه نمی‌رسد
- **فروشگاه با یک صدا جواب می‌دهد**: پنل جدید `/admin/questions` همهٔ پرسش‌ها را با تب‌های وضعیت و شمارنده نشان می‌دهد (همه / در انتظار پاسخ / پاسخ داده شد / ردشده) — و جعبهٔ پاسخ **داخل خود ردیف جدول** است؛ پاسخ‌دادن یعنی یک textarea و یک کلیک، بدون پرش صفحه
- **انتشار و خبررسانی در یک قدم**: به محض پاسخ مدیر، پرسش عمومی می‌شود و پرسنده **ایمیل «پاسخ پرسش شما»** را می‌گیرد — با همان طراحی ایمیل‌های سفارش، پرسش و پاسخ کنار هم و لینک برگشت به محصول؛ ویرایش بعدی بی‌صداست و خطای SMTP هرگز پاسخ‌دادن را نمی‌شکند (لاگ می‌شود، نه خطا)
- کارت‌های عمومی پرسش و پاسخ همان **نشان «خریدار تأییدشده»** نظرات را دارند — با یک کوئری برای کل صفحه (بدون N+1) و همان قانونِ عدم‌شمول سفارش‌های لغوشده
- برخلاف نظرات، **یک کاربر می‌تواند چند پرسش** دربارهٔ همان محصول بپرسد — خریدارهای واقعی سؤال بعدی هم دارند؛ ضداسپم با `auth` + `throttle:5,1` و رد/حذف مدیر سر جایش است
- نوار کناری پنل هم لینک **«پرسش‌ها» با نشان زرد در انتظار** کنار «نظرات» گرفت — قرینهٔ جریان سفارش‌ها و نظرات
- ۳۵ تست جدید (QuestionTest): گاردهای ثبت و اعتبارسنجی، قوانین نمایش، صفحه‌بندی، نشان‌های خریدار، چرخهٔ ایمیل (یک‌بار ارسال، بی‌صدا در ویرایش، بی‌اثر در خطا)، جریان‌های نظارت، گاردهای دسترسی و یکپارچگی آبشاری

### 📊 اعداد

- **۲۱۶ تست فیچر** (۷۷۸ ادعا) — ۳۵ تست جدید پرسش و پاسخ: ریدایرکت مهمان، الزامی/حداقل متن، شروع در انتظار + trim، چند پرسش برای هر کاربر، ۴۰۴ غیرفعال، محدودیت نرخ، نامرئی‌بودن در انتظار/ردشده، دیده‌شدن پاسخ + شمارنده، صفحه‌بندی ۵تایی (جدیدترین اول)، نشان خریدار (واقعی / غیرخریدار / لغوشده)، راهنمای ورود در برابر فرم، حالت خالی، گاردهای ادمین، تب‌ها، جریان پاسخ + انتشار، اعتبارسنجی پاسخ، ایمیل (ارسال / عدم ارسال در رد / عدم تکرار در ویرایش / بی‌شکستن در خطا)، حفظ تاریخ پاسخ اول، احیای ردشده، رد، حذف، 403 مشتری، نشان نوار کناری، cascade محصول/کاربر، برش متن
- سبک کد: Pint پاک
- یک مهاجرت جدید (`questions`) — با `php artisan shop:install` یا `migrate` ساده اعمال می‌شود؛ بدون تغییر کانفیگ

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
# ثبت‌نام کنید و صفحهٔ یک محصول را باز کنید:
# ← زیر نظرات، بخش «پرسش و پاسخ» را پیدا کنید:
#   «گارانتی این محصول چند سال است؟» را بنویسید و «ثبت پرسش» را بزنید.
# ← به‌عنوان مدیر وارد شوید → /admin/questions: پرسش در تب
#   «در انتظار پاسخ» با نشان زرد نوار کناری منتظر است. پاسخ را
#   داخل خود ردیف بنویسید و «ثبت پاسخ» را بزنید.
# ← در صفحهٔ محصول، پرسش + «پاسخ دیجی‌شاپ» حالا عمومی است —
#   و صندوق پرسنده ایمیل «پاسخ پرسش شما» را گرفته.
# ← پاسخ را در پنل ویرایش کنید: صفحه به‌روز می‌شود ولی ایمیل
#   دوم نمی‌رود — ویرایش بی‌صداست.
```

</details>

</div>

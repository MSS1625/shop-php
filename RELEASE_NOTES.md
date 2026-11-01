# ⚖️ v2.13.0 — مقایسهٔ محصولات (Product Comparison)

<div dir="rtl" align="center">

**دیجی‌شاپ** — فروشگاه آنلاین تجهیزات شبکه | **Digi-Shop** — Online network-equipment store
Laravel 12 · PHP 8.2+ · Bootstrap 5 RTL · MIT License

</div>

## 🇬🇧 English

### ⚖️ New — Compare up to 4 products side by side

- Customers can now **line products up before they buy**: a session-based compare list capped at **4 products** (more columns than that and the table stops being readable) — and because it lives in the session like the cart, **guests can compare without an account**; no login wall between a visitor and a decision
- **One click on the scale, everywhere the product appears**: the scale button sits on the corner of every product card (right under the wishlist heart), a big variant joins the price box on the product page, and the navbar gained a **compare icon with a live count badge** — the same instant-toggle Ajax pattern as the wishlist hearts (PRG fallback when JavaScript is off); the scale turns cyan while the product is in the list
- **`/compare` — the comparison table**: one column per product, rows for price, category, stock, rating average + review count, answered-questions count and buy buttons; the cheapest column is highlighted green with a **«بهترین قیمت» (best price) badge**, so the money decision takes one glance
- **One query for the whole table, no N+1**: rating aggregates come from `withRating()` and answered questions from a `withCount` subquery — the same one-query patterns the product cards and the admin dashboard already use
- **The table keeps your order**: columns follow **insertion order** (the order you added the products, not their IDs) — preserved through the session and re-applied after the aggregate query, so the page reads like your own shortlist
- **A list that heals itself**: deactivate or delete a product and it silently drops from the table **while the same request rewrites the session** — the navbar badge never counts a product the store no longer shows
- **Guardrails**: a 5th product is refused with a friendly warning flash and, for Ajax callers, a `full` flag in the JSON; inactive products 404 on toggle; `POST /compare/clear` empties the whole list; the `/compare/clear` route is registered before the slug route so the two can never collide
- Mobile-ready: the table scrolls horizontally inside `table-responsive` (640px floor) instead of squashing the columns; the empty state matches the wishlist page's tone and points to the catalog

### 📊 Numbers

- **283 feature tests** (1086 assertions) — all green; code style: Pint clean
- **Zero migrations, zero config changes** — apply the zip and you're done; `shop:install` has nothing new to run
- 1 new service (`Compare`, session-based, `MAX = 4`); 1 new controller (`CompareController`); 3 new routes (`/compare`, `/compare/{slug}`, `/compare/clear`); 19 new tests in `CompareTest`

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

Open **http://localhost:8000/products** and click the **scale icon** on the corner of
any product card — the scale turns cyan and the navbar badge jumps to «۱».
Add three more (try the cheapest and the most expensive in a category), then open
**http://localhost:8000/compare**:

- the four products stand side by side: price, category, stock, rating, answered questions
- the **cheapest column glows green with a «بهترین قیمت» badge**
- try adding a 5th product — you get the cap warning («فهرست مقایسه پر است…»), not a broken table
- remove one with its «حذف از فهرست» button — the column disappears without a full reload
- everything above works **logged out**; compare is a session feature, like the cart

Want the state without the UI? The toggle is a plain form — any HTTP client works:

```bash
CSRF=$(curl -s -c /tmp/c.txt http://localhost:8000/products \
 | grep -oP 'csrf-token" content="\K[^"]+')

# add a product to the compare list (Ajax branch returns JSON):
curl -s -b /tmp/c.txt -X POST http://localhost:8000/compare/fiber-optic-cable \
  -H "X-Requested-With: XMLHttpRequest" -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: $CSRF"
# → {"success":true,"inCompare":true,"full":false,"count":1,"message":"«کابل فیبر نوری» به فهرست مقایسه اضافه شد."}

curl -s -b /tmp/c.txt http://localhost:8000/compare | grep -c ds-compare-table
# → 1  (the table renders for this session)

curl -s -b /tmp/c.txt -X POST http://localhost:8000/compare/clear \
  -H "X-CSRF-TOKEN: $CSRF" -o /dev/null -w "%{http_code}\n"
# → 302 (list emptied, PRG back to /compare)
```

</details>

---

## 🇮🇷 فارسی

<div dir="rtl">

### ⚖️ جدید — مقایسهٔ حداکثر ۴ محصول، کنار-به-کنار

- مشتری حالا می‌تواند **قبل از خرید، محصولات را کنار هم بچیند**: فهرست مقایسهٔ مبتنی بر سشن با سقف **۴ محصول** (بیشتر از آن، جدول دیگر خوانا نمی‌ماند) — و چون مثل سبد خرید روی سشن زندگی می‌کند، **مهمان هم بدون حساب کاربری مقایسه می‌کند**؛ بین بازدیدکننده و تصمیمش دیواری نیست
- **یک کلیک روی ترازو، همه‌جایی که محصول هست**: دکمهٔ ترازو گوشهٔ کارت هر محصول است (درست زیر قلب علاقه‌مندی)، نسخهٔ بزرگش به ردیف قیمتِ صفحهٔ محصول می‌پیوندد و ناوبار **آیکون مقایسه با شمارندهٔ زنده** گرفت — همان الگوی Ajax یک‌کلیکی قلب‌های علاقه‌مندی (بدون جاوااسکریپت همان فرم عادی سابمیت می‌شود)؛ ترازو در فهرست بودن محصول را با رنگ فیروزه‌ای نشان می‌دهد
- **`/compare` — جدول مقایسه**: یک ستون برای هر محصول و ردیف‌های قیمت، دسته‌بندی، موجودی، میانگین امتیاز + تعداد نظر، پرسش‌های پاسخ‌داده و دکمه‌های خرید؛ **ارزان‌ترین ستون سبز می‌شود با بج «بهترین قیمت»** تا تصمیم مالی با یک نگاه گرفته شود
- **یک کوئری برای کل جدول، بدون N+1**: میانگین امتیاز از `withRating()` و پرسش‌های پاسخ‌داده از subquery یک `withCount` می‌آیند — همان الگوهای یک-کوئری کارت‌های محصول و داشبورد ادمین
- **جدول ترتیب شما را نگه می‌دارد**: ستون‌ها به **ترتیب افزودن** می‌آیند (ترتیبی که محصول‌ها را اضافه کردید، نه ترتیب شناسه‌ها) — هم در سشن و هم بعد از کوئری تجمیعی؛ صفحه مثل فهرست انتخابی خودتان خوانده می‌شود
- **فهرستی که خودش را ترمیم می‌کند**: محصولی را غیرفعال یا حذف کنید — بی‌سروصدا از جدول می‌افتد **و همان درخواست، سشن را بازمی‌نویسد**؛ شمارندهٔ ناوبار هرگز محصولی را نمی‌شمارد که فروشگاه دیگر نشان نمی‌دهد
- **حفاظت‌ها**: محصول پنجم با فلش هشداری رد می‌شود و برای Ajax با فیلد `full` در JSON؛ محصول غیرفعال روی toggle ارور 404 می‌گیرد؛ `POST /compare/clear` کل فهرست را خالی می‌کند؛ مسیر `/compare/clear` قبل از مسیر اسلاگ ثبت شده تا هرگز تداخل نکنند
- آمادهٔ موبایل: جدول داخل `table-responsive` (کف 640px) به‌طور افقی اسکرول می‌خورد به‌جای اینکه ستون‌ها له شوند؛ حالت خالی هم‌لحن صفحهٔ علاقه‌مندی‌هاست و به ویترین می‌رساند

### 📊 اعداد

- **۲۸۳ تست فیچر** (۱۰۸۶ ادعا) — همه سبز؛ سبک کد: Pint پاک
- **بدون مهاجرت جدید، بدون تغییر کانفیگ** — زیپ را اعمال کنید و تمام؛ `shop:install` چیز تازه‌ای برای اجرا ندارد
- ۱ سرویس جدید (`Compare`، سشن‌محور، `MAX = 4`)؛ ۱ کنترلر جدید (`CompareController`)؛ ۳ مسیر جدید (`/compare`، `/compare/{slug}`، `/compare/clear`)؛ ۱۹ تست جدید در `CompareTest`

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

**http://localhost:8000/products** را باز کنید و روی **آیکون ترازو** گوشهٔ هر کارت محصول
کلیک کنید — ترازو فیروزه‌ای می‌شود و شمارندهٔ ناوبار می‌پرد به «۱». سه محصول دیگر
باید امتحان کنید (مثلاً ارزان‌ترین و گران‌ترین یک دسته) و بعد
**http://localhost:8000/compare** را باز کنید:

- چهار محصول کنار هم می‌ایستند: قیمت، دسته، موجودی، امتیاز، پرسش‌های پاسخ‌داده
- **ارزان‌ترین ستون سبز می‌درخشد با بج «بهترین قیمت»**
- محصول پنجم را هم امتحان کنید — پیام سقف می‌گیرید («فهرست مقایسه پر است…»)، نه جدولِ شکسته
- با دکمهٔ «حذف از فهرست» یکی را بردارید — ستون بدون رفرش کامل می‌رود
- همهٔ این‌ها **بدون ورود** کار می‌کند؛ مقایسه مثل سبد، فیچر سشنی است

بدون UI هم می‌خواهید ببینید؟ toggle یک فرم ساده است — هر کلاینت HTTP جواب می‌دهد:

```bash
CSRF=$(curl -s -c /tmp/c.txt http://localhost:8000/products \
 | grep -oP 'csrf-token" content="\K[^"]+')

# افزودن محصول به فهرست (شاخهٔ Ajax پاسخ JSON می‌دهد):
curl -s -b /tmp/c.txt -X POST http://localhost:8000/compare/fiber-optic-cable \
  -H "X-Requested-With: XMLHttpRequest" -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: $CSRF"
# → {"success":true,"inCompare":true,"full":false,"count":1,"message":"«کابل فیبر نوری» به فهرست مقایسه اضافه شد."}

curl -s -b /tmp/c.txt http://localhost:8000/compare | grep -c ds-compare-table
# → 1  (جدول برای همین سشن رندر می‌شود)

curl -s -b /tmp/c.txt -X POST http://localhost:8000/compare/clear \
  -H "X-CSRF-TOKEN: $CSRF" -o /dev/null -w "%{http_code}\n"
# → 302 (فهرست خالی شد، PRG به /compare)
```

</details>

</div>

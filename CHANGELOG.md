# 📋 Changelog — تاریخچه تغییرات

All notable changes to this project are documented in this file.
(تمام تغییرات مهم این پروژه در این فایل ثبت می‌شود.)

<div dir="rtl" align="center">

راه سریع تشخیص نسخه: جدیدترین ورودی بالای هر بخش = نسخه فعلی؛ الان **v2.10.0** ✅

</div>

## 🇬🇧 English

## [2.10.0] — 2026-10-27

### ❤️ Added — Customer Wishlist

- Customers can now **save products to a wishlist** with a single click on the heart — the button appears on every product card and next to the add-to-cart button on the product page; clicking it **toggles** between add and remove, no page reload needed
- **Progressive enhancement**: the heart is a real POST form (Post/Redirect/Get), so it works without JavaScript; with JavaScript the toggle goes through the existing Ajax helper — every heart of the same product on the page flips at once, the navbar counter updates live, and on the wishlist page itself a removal fades the card out
- New **`/wishlist` page** (login-only, linked from the navbar heart and the account dropdown): saved products on the same familiar product cards — newest saved first (ordered by the pivot timestamp), 12 per page, with an empty state that guides users to the shop
- The **navbar gained a heart button with a live count badge** next to the cart; the heart fills in when the list is non-empty — mirroring the cart button design
- Wishlist state is shared across the whole storefront through a new **`Wishlist` singleton service** (the same pattern as the cart): one memoized query per request keeps the hearts in sync on listings, the product page and related products — no N+1, no per-card queries
- Same **one-row rule as reviews, backed by the database**: a unique (user, product) constraint plus idempotent toggle logic — double-clicks and double-tabs can't create duplicates
- Only **active products** count: an item the admin deactivated quietly disappears from the wishlist page and the navbar counter (the same active-only rule as every other storefront surface); deleting a product cascades its wishlist rows away
- Guests clicking any heart land on the login page with a hint, instead of a dead button
- Hardened the **empty navbar badges** (cart + wishlist): the hidden-state markup now renders as a real `style` attribute instead of an HTML-escaped string — same look in browsers, strictly correct markup
- 19 new tests (WishlistTest): guest access, add/remove toggle flow, JSON responses for Ajax, inactive-product guard, newest-first ordering, own-items-only, inactive hiding, empty state, 12-per-page pagination, navbar badge (Persian numeral, active-only, hidden for guests), solid/outline heart states, product-page toggle form, account dropdown link, product-deletion cascade — **181 tests total, 685 assertions**

## [2.9.0] — 2026-10-24

### ⭐ Added — Product Reviews & Ratings

- Customers can now **rate products 1–5 stars and write a review** right on the product page — reviews start as «در انتظار تأیید» (pending) and only appear publicly after an admin approves them, so nothing unmoderated ever reaches the storefront
- Every review carries the **«خریدار تأییدشده» (verified buyer) badge** when its author actually bought that product in a non-cancelled order — computed for the whole page with a single query (no N+1), reusing the same cancelled-excluded rule as the dashboard's sales figures
- The product page gained a **rating summary**: big average (one Persian decimal, e.g. «۴٫۳»), star row, and a 5→1 **breakdown chart** with progress bars and per-star counts — plus a stars+average link under the product title that anchors straight to the reviews
- **Product cards now show the rating** (star, average, review count) on the home page, the products listing and related products — via a single `withRating()` scope adding two subqueries, the same pattern as the best-selling sort
- One review per user per product (DB unique constraint + a friendly error for repeats and even a race-safe catch for double-tab submissions); the form turns into a **status box** («نظر شما ثبت شده است…») once the user has already reviewed; guests see a login prompt instead of the form
- A polished **RTL star picker** (hover-to-preview, keyboard-free radios) ships in `shop.js`, and validation lives in a dedicated `ReviewRequest` (rating 1–5, comment 5–2000 chars, Persian messages)
- New **admin reviews page** (`/admin/reviews`) with status tabs and counts (همه / در انتظار تأیید / تأییدشده / ردشده), one-click **approve / reject / delete**, product links, and a pending-count badge in the sidebar next to «نظرات» — mirroring the orders workflow
- Anti-spam by design: `auth` + `throttle:5,1` on submission, moderation before visibility, and cascade-on-delete keeps the data tidy when a product goes away
- 29 new tests (ReviewTest): guest/auth access, rating and comment validation, pending-until-approved flow, rejection, one-review rule, different products allowed, rate limit, inactive product, average/count/breakdown rendering, aggregates counting only approved reviews, card rating, verified-buyer badge (real buyer / non-buyer / cancelled order), guest login prompt, own-review status box, admin access guards, status tabs, approve/reject/delete flows, customer 403s, sidebar badge, product-deletion cascade — **162 tests total, 632 assertions**

## [2.8.0] — 2026-10-24

### 🔍 Added — Smart Product Discovery

- The products page gained a **price-range filter** (min / max, each usable on its own) and an **in-stock-only filter** — both combine freely with search, category and sorting, and survive pagination links (`withQueryString`)
- Price inputs understand **Persian/Arabic digits and thousands separators** (`۵۰۰٬۰۰۰` = `500,000` = `500000`) — the same normalization approach as coupon codes and order tracking, via the new `en_num()` helper (the mirror of `fa_num`)
- If the range is entered backwards (min > max) it is **silently swapped** instead of showing an empty result — the user never loses the page over a typo; non-numeric input is gracefully ignored
- New **«پرفروش‌ترین» (best-selling) sort** — products ranked by units sold with a single `withSum` subquery; items of cancelled orders never count, the same rule as the dashboard, and ties break toward the newest product
- **Related products upgraded**: still same category and active, but now **in-stock items come first** (newest first within each group) instead of a random shuffle — the section is deterministic, testable, and shows available items while out-of-stock ones only fill leftover slots
- Validation moved to a dedicated `ProductFilterRequest` form request; an active-filter indicator («فیلتر فعال») with a one-click reset appears next to the result count, and the empty state now offers filter reset as well
- 21 new tests (ProductDiscoveryTest): price min/max/range, backwards-range swap, Persian digits and separators, graceful non-numeric input, in-stock filter, popular ranking, cancelled-item exclusion, newest tie-break, combined filters, pagination query-string, indicator/reset rendering, validation failures, inactive-product regression, related-products ordering/exclusion/fallback — **133 tests total, 537 assertions**

## [2.7.0] — 2026-10-21

### 🚚 Added — Guest Order Tracking

- New public order-tracking page at `/track` — customers who checked out as guests can now follow their order with the **order number + the mobile number they entered at checkout**; both must match, one alone is never enough
- The result shows the same 4-step timeline as the account area, the payment status card, the full items list with the discount line, and the cancelled state for cancelled orders
- Payment retry for guests: after a successful lookup, an unpaid online order shows the pay / retry button — powered by the exact `viewed_orders` session mechanism checkout already uses; no new authorization surface
- Security-first design: a uniform error message for wrong order number and wrong phone (anti-enumeration), rate limiting like the login form (10/min), a minimum-data result page (address never shown, mobile masked `۰۹۱۲***۷۸۹`), a 30-minute session TTL and a Post/Redirect/Get flow so refreshing never re-submits
- Persian/Arabic digit normalization on both fields (same approach as coupon codes) — typing with a Persian keyboard just works
- Navbar gained a «پیگیری سفارش» link and the order-success page now guides guests to the tracking page for future visits
- 16 new tests (GuestTrackingTest): correct/wrong/mismatched lookups, identical-error anti-enumeration check, Persian digits, session expiry, rate limit, cancelled state, payment-retry grant, address hiding, phone masking — **112 tests total, 476 assertions**

## [2.6.0] — 2026-10-18

### 📊 Added — Admin Reports Dashboard

- The admin dashboard is now a full reports page: revenue KPIs for **today / this month / all-time** plus **average order value** — cancelled orders are excluded from every sales figure
- Sales chart grew from 7 days (one query per day) to a **14-day chart fed by a single grouped query** — days without sales are zero-filled so the chart is always complete; every bar carries its day's order count and amount
- New **best-sellers table** — products ranked by units sold with per-product revenue; items belonging to cancelled orders are excluded
- New **order-status breakdown** — count, percentage share and a progress bar per status, plus a footer line with this month's new customers and product counts
- New `DashboardStats` service collects every dashboard query in one testable class — the controller is now a thin pass-through
- The low-stock panel gained a separate **out-of-stock badge**, and the threshold + chart length moved to config: `SHOP_DASHBOARD_DAYS` (default 14) and `SHOP_LOW_STOCK_THRESHOLD` (default 3), documented in `.env.example`
- 10 new tests (DashboardTest): access guards, KPI math with cancelled and previous-month orders, chart series coverage, best-seller ranking, status percentages, low-stock threshold (default and overridden), empty states — **96 tests total, 406 assertions**

## [2.5.0] — 2026-10-16

### 📱 Added — SMS Notifications (Kavenegar)

- SMS channel for order notifications — the same three events as email (order placed / payment confirmed / status changed) now also go out as short Persian text messages to the customer's mobile number already collected at checkout; **no database change and not a single controller line touched** — the whole feature plugs in through the `OrderNotifier` binding in the service provider, exactly the swap promised in the v2.4.0 architecture
- `SmsGateway` contract (mirrors `PaymentGateway`): `KavenegarSmsGateway` sends for real through Kavenegar's REST API, `LogSmsGateway` only logs messages for local development and tests
- `CompositeNotifier` — with both `mail` and `sms` channels enabled, every event is dispatched to both, and a failure in one channel never stops the other
- New env/config switches: `SHOP_NOTIFICATION_CHANNELS` (default `mail`; e.g. `mail,sms`), `SMS_DRIVER` (`log` | `kavenegar`), `KAVENEGAR_API_KEY`, `KAVENEGAR_FROM` — all documented in `.env.example`
- Same failure-isolation rule as email: an SMS failure is caught and logged — it can never break checkout or payment
- 15 new tests (SmsTest): channel dispatch, dual-channel composite, failure isolation, message content (order number, Persian-digit amount, bank RefID, old→new status labels), the Kavenegar HTTP contract, API-error / missing-key exceptions, driver resolution and unknown-channel fallback — **86 tests total, 331 assertions**

## [2.4.0] — 2026-10-14

### 📧 Added — Order Email Notifications

- New optional `customer_email` on checkout — orders can now notify customers by email (guests included); when the field is left empty, the logged-in account's email is used as fallback
- Contract-based notifier (`App\Contracts\OrderNotifier`) following the same pattern as the payment gateway — the default implementation sends emails, a `NullNotifier` keeps everything silent when disabled, and the channel can be swapped (e.g. SMS) without touching the business logic
- Three bilingual-ready RTL Persian email templates (inline CSS, email-client safe): order summary, payment breakdown with discount line
  - **Order placed** — sent right after checkout (both online and cash-on-delivery orders)
  - **Payment confirmed** — sent exactly once, right after the gateway verifies the payment (repeat callbacks never duplicate it)
  - **Status changed** — sent when the admin moves the order (processing / shipped / delivered / cancelled), including old → new status
- Failure isolation: notification errors are caught and logged — a broken SMTP can never break checkout or payment confirmation
- Admin order view now shows the customer's email next to phone
- Config switch: `SHOP_NOTIFICATIONS=true|false` in `.env` (documented in `.env.example`); with `MAIL_MAILER=log` emails land in `storage/logs/laravel.log` — perfect for local development without SMTP
- 10 new feature tests (sent/fallback/invalid-email/disabled-config/SMTP-failure-isolation/no-duplicate/no-same-status) — **71 tests total, 276 assertions**

## [2.3.0] — 2026-10-11

### 🎟 Added — Discount Coupon Codes

- New `coupons` table: percent or fixed-amount codes, optional minimum cart amount, optional total usage cap, optional start/expiry window, active toggle
- Shop cart: coupon input box in the order summary — wrong, expired, exhausted or below-minimum codes are rejected with clear Persian messages; a valid code shows the applied-code chip, discount line and the final payable amount
- Checkout: the summary shows subtotal, discount line and payable amount; before the order is placed the coupon is re-validated transparently — if it became invalid the customer is sent back to the cart instead of being silently charged full price
- Orders now store `subtotal`, `discount_amount` and `coupon_code` (historical text, no FK) — the success page, customer account and admin order view all show the discount breakdown
- Race-safe redemption: inside the order transaction the coupon row is locked (`lockForUpdate`) and re-validated before `used_count` is incremented, so the usage cap cannot be exceeded by concurrent checkouts
- Fixed-amount discount is always capped at the cart subtotal — the payable amount can never go below zero
- Admin panel: full coupon management (list with status badges — فعال / غیرفعال / منقضی / تکمیل ظرفیت / شروع نشده — create, edit, delete with confirm) under a new «کدهای تخفیف» menu item
- Code normalization everywhere: Persian/Arabic digits converted, spaces stripped, upper-cased; `summer 500` typed by an admin becomes `SUMMER500`
- Seeder ships a demo coupon `WELCOME10` (10٪, 100 uses) so the feature is visible right after `shop:install`
- 18 new feature tests (apply/remove/reject paths, cap math, checkout storage, usage increment, invalid-at-checkout guard, admin CRUD + guards) — **61 tests total, 231 assertions**

## [2.2.0] — 2026-10-05

### 🖼 Added — Product Image Gallery

- New `product_images` table with position ordering — each product can now have extra shots alongside its cover image
- Admin product form: multi-file upload (up to 4 per submit, same MIME/size/dimension rules as the cover) plus per-image delete checkboxes; the existing gallery is displayed as thumbnails on every edit
- Product detail page: a thumbnail strip under the main image — click any thumb to swap the big view; the strip is omitted entirely for products without a gallery
- IDOR-safe removal: delete requests are always scoped through the product relation, so a foreign image ID has no effect; files are deleted together with their rows, and deleting a product cleans up its gallery files

### 🔧 Fixed — CI failures (composer platform pin)

- Root cause of the red CI badge: the lock file had been resolved on PHP 8.4 without a platform pin, pulling packages that refuse PHP 8.2/8.3 (`symfony/*` 8.1 needs `>=8.4.1`, `laravel/pint` 1.32 needs `^8.3`) — so the 8.2/8.3 matrix legs failed at `composer install`
- Fix: added `config.platform.php = "8.2.0"` to `composer.json` and ran a full `composer update` — the lock now resolves for PHP 8.2.0+ and installs on every matrix leg (verified locally with `composer install --dry-run`)
- Locked now: laravel/pint 1.30.4 and symfony 7.4.x; laravel/framework stays 12.69.3
- Workflow hygiene: `actions/checkout` and `actions/cache` bumped v4 → v5 (Node 20 deprecation warnings gone) and the runner pinned to `ubuntu-24.04` to stay ahead of the `ubuntu-latest` → Ubuntu 26 migration (2026-10-19)

### 🔍 Changed

- 8 new feature tests (gallery upload/update/remove, IDOR guard, 4-image limit, detail-page render, guest guard) — **43 tests total, 145 assertions**

## [2.1.0] — 2026-10-05

### 🌐 Added — Project Website (GitHub Pages)

- New `docs/` folder with a **static RTL demo page** (`index.html`) replicating the shop home page: hero, categories, all 8 sample products with real images & prices, feature cards and a quick-start terminal
- Hosted **free on GitHub Pages** at <https://mss1625.github.io/shop-php/> — enabled with a single toggle (repo Settings → Pages → main branch `/docs`)
- Ships with `.nojekyll` and `robots.txt`; every button links back to the repository

### 📝 Changed — README internationalization

- README split into **`README.md` (English)** + **`README.fa.md` (فارسی)** with a two-way language switcher at the top of each file — fixes the missing link to the English version
- `README.fa.md` now has full parity with the English file (project structure, roadmap and contributing sections added)
- Release badge now **auto-updates from GitHub Releases** (`github/v/release`) instead of a hard-coded version
- Live-preview link added to both files

### 📦 Added — Release tooling

- `RELEASE_NOTES.md` — paste-ready bilingual notes for the v2.1.0 GitHub Release

## [2.0.0] — 2026-10-04

### 💳 Added — ZarinPal Payment Gateway

- Full online-payment integration using **ZarinPal v4 REST API** (`request.json` / `verify.json` / `StartPay`)
- **Three modes** out of the box: `mock` (local simulator page — no merchant account needed), `sandbox`, and `production`
- **Server-side verification** on callback — payment status is never trusted from the URL alone (anti-tampering)
- Payment record per order: `payment_method`, `payment_status`, `authority`, `ref_id`, `paid_at`
- **Cash on Delivery (پرداخت در محل)** option alongside online payment
- Friendly retry flow: failed payments can be retried on the same order without creating duplicates
- Clean payment-abstraction layer (`App\Contracts\PaymentGateway`) — swap in any other Iranian PSP with minimal code

### 👤 Added — Customer Accounts & Order History

- Customer **registration / login / logout** with rate-limited login
- Personal **account dashboard**: total orders, paid amount, pending orders
- **Order history** page (paginated) with status and payment badges
- **Order tracking page** with a 4-step visual timeline (pending → processing → shipped → delivered)
- Customers can **cancel an unpaid pending order** — stock is restored atomically
- IDOR protection: users can only view their own orders
- Guest checkout still works — account is optional

### 🤖 Added — GitHub Actions CI

- Workflow `.github/workflows/ci.yml` runs **automatically on every push** (all branches) and every Pull Request
- Test matrix: **PHP 8.2 / 8.3 / 8.4** on `ubuntu-latest` with SQLite
- Separate **Pint code-style** job
- Composer dependency caching for fast runs

### 🔧 Changed

- Admin orders table now shows **payment method + payment status** columns
- Admin order detail page shows a full **payment card** (ref ID, paid-at timestamp)
- Order status changes refactored onto an atomic, stock-safe method
- Navbar now has a **user dropdown** (account, orders, logout) for logged-in customers
- 26 new feature tests (auth, account, payments) — **35 tests total**, all passing

## [1.0.0] — 2026-10-04

### ⚡ Initial Laravel Release

- Complete rewrite of the original plain-PHP shop on **Laravel 12** (PHP 8.2+)
- Full shopping cart (session-based, AJAX add/update/remove) and DB-transactional checkout with stock re-validation
- Order management with 5-state workflow and price snapshots
- Admin panel: dashboard with stats & 7-day sales chart, product/category CRUD, secure image upload (MIME whitelist, 2MB, random filenames), order management with cancel-and-restock
- Security hardening: CSRF, rate-limited admin login (5/min), session regeneration, security headers, no default passwords (`admin:create` interactive command)
- One-command installer: `php artisan shop:install`
- Dark neon RTL theme, Vazirmatn font, Persian digits & currency helpers
- Bilingual README (EN/FA), MIT License, 9 feature tests

---

## 🇮🇷 فارسی

## [2.10.0] — ۲۰۲۶-۱۰-۲۷

### ❤️ افزوده شد — علاقه‌مندی‌های مشتری

- مشتری‌ها حالا می‌توانند محصولات را با **یک کلیک روی قلب** در علاقه‌مندی‌ها ذخیره کنند — دکمه روی همهٔ کارت‌های محصول و کنار دکمهٔ افزودن به سبد در صفحهٔ جزئیات هست؛ کلیک دوم همان محصول را **حذف می‌کند** (toggle)، بدون رفرش صفحه
- **بهبود تدریجی**: قلب یک فرم POST واقعی است (Post/Redirect/Get) و بدون جاوااسکریپت هم کار می‌کند؛ با جاوااسکریپت همان helper موجود Ajax را می‌گیرد — همهٔ قلب‌های یک محصول در صفحه با هم می‌چرخند، شمارندهٔ ناوبار همان لحظه به‌روز می‌شود و روی خود صفحهٔ علاقه‌مندی‌ها حذف یعنی محو شدن کارت
- صفحهٔ جدید **/wishlist** (فقط با ورود، از قلب ناوبار و منوی حساب در دسترس): محصولات ذخیره‌شده روی همان کارت‌های آشنا — جدیدترین ذخیره اول (مرتب‌سازی روی تاریخ پیوت)، ۱۲تا در هر صفحه، با حالت خالی که کاربر را به فروشگاه راهنمایی می‌کند
- ناوبار **دکمهٔ قلب با شمارندهٔ زنده** کنار سبد گرفت؛ با پر شدن فهرست، قلب ناوبار هم پر می‌شود — قرینهٔ طراحی دکمهٔ سبد
- وضعیت علاقه‌مندی از طریق **سرویس singleton جدید `Wishlist`** (همان الگوی سبد خرید) در کل فروشگاه به اشتراک گذاشته می‌شود: یک کوئری memoize در هر درخواست، قلب‌های لیست، صفحهٔ جزئیات و محصولات مشابه را همگام نگه می‌دارد — بدون N+1، بدون کوئری برای هر کارت
- همان **قانون «یک ردیف» نظرات، با پشتیبان دیتابیس**: قید یکتای (کاربر، محصول) + منطق toggle پیش‌فرض — دابل‌کلیک و سابمیت همزمان از دو تب نمی‌تواند تکراری بسازد
- فقط **محصولات فعال** حساب می‌شوند: محصولی که مدیر غیرفعال کند بی‌صدا از صفحه و شمارنده کنار می‌رود (همان قاعدهٔ فعال-فقط بقیهٔ ویترین)؛ حذف محصول هم ردیف‌های علاقه‌مندی‌اش را cascade می‌کند
- مهمان‌ها با کلیک روی هر قلب با یک راهنما به صفحهٔ ورود می‌رسند، نه دکمهٔ مرده
- **بج‌های خالی ناوبار** (سبد + علاقه‌مندی) سخت‌تر شد: مارک‌آپ حالت مخفی حالا به‌صورت `style` واقعی رندر می‌شود، نه رشتهٔ HTML-escaped — ظاهر همان است، مارک‌آپ اصولاً درست
- ۱۹ تست جدید (WishlistTest): دسترسی مهمان، جریان افزودن/حذف، پاسخ JSON برای Ajax، محافظ محصول غیرفعال، ترتیب جدیدترین‌اول، فقط-آیتم‌های-خود، مخفی‌شدن غیرفعال‌ها، حالت خالی، صفحه‌بندی ۱۲تایی، شمارندهٔ ناوبار (عدد فارسی، فعال-فقط، مخفی برای مهمان)، وضعیت پر/خالی قلب، فرم toggle صفحهٔ جزئیات، لینک منوی حساب، cascade حذف محصول — **جمع ۱۸۱ تست، ۶۸۵ ادعا**

## [2.9.0] — ۲۰۲۶-۱۰-۲۴

### ⭐ افزوده شد — نظرات و امتیازدهی محصولات

- مشتری‌ها حالا می‌توانند روی صفحهٔ محصول **از ۱ تا ۵ ستاره امتیاز بدهند و نظر بنویسند** — نظرها با وضعیت «در انتظار تأیید» شروع می‌شوند و فقط بعد از تأیید مدیر برای همه نمایش داده می‌شوند؛ هیچ نظر بدون بررسی‌ای راه ویترین پیدا نمی‌کند
- کنار نظر هر نویسنده‌ای که واقعاً آن محصول را در سفارشی غیرلغوشده خریده نشان **«خریدار تأییدشده»** می‌خورد — با یک کوئری برای کل صفحه (بدون N+1) و با همان قاعدهٔ حذف سفارش‌های لغوشده که در اعداد فروش داشبورد استفاده می‌شود
- صفحهٔ محصول به **خلاصهٔ امتیازها** مجهز شد: میانگین درشت با یک رقم اعشار فارسی (مثل «۴٫۳»)، ردیف ستاره‌ها و **نمودار تفکیک ۵ به ۱** با نوار پیشرفت و تعداد هر ستاره — به‌علاوه لینک ستاره‌ها + میانگین زیر عنوان محصول که مستقیم به بخش نظرات می‌پرد
- **کارت‌های محصول هم امتیاز نشان می‌دهند** (ستاره، میانگین، تعداد نظر) در صفحهٔ اصلی، لیست محصولات و محصولات مشابه — با اسکوپ واحد `withRating()` که دو زیرکوئری اضافه می‌کند؛ همان الگوی مرتب‌سازی پرفروش‌ترین
- هر کاربر برای هر محصول فقط یک نظر (کلید یکتای دیتابیس + پیام خطای دوستانه برای تکرار و حتی catch مقاوم در برابر ثبت همزمان از دو تب)؛ بعد از ثبت، فرم به **جعبهٔ وضعیت** («نظر شما ثبت شده است…») تبدیل می‌شود؛ مهمان‌ها به‌جای فرم راهنمای ورود می‌بینند
- **انتخاب ستارهٔ راست‌به‌چپِ** صیقلی (پیش‌نمایش با hover) در `shop.js` و اعتبارسنجی در `ReviewRequest` اختصاصی (امتیاز ۱ تا ۵، متن ۵ تا ۲۰۰۰ کاراکتر، پیام‌های فارسی)
- صفحهٔ جدید **مدیریت نظرات** در `/admin/reviews` با تب‌های وضعیت و شمارنده (همه / در انتظار تأیید / تأییدشده / ردشده)، دکمه‌های یک‌کلیکی **تأیید / رد / حذف**، لینک محصولات و بج تعداد در انتظار در سایدبار کنار «نظرات» — قرینهٔ جریان سفارش‌ها
- ضداسپم از پایه: `auth` + `throttle:5,1` روی ثبت نظر، بررسی پیش از نمایش و cascade-on-delete برای مرتب‌بودن داده‌ها بعد از حذف محصول
- ۲۹ تست جدید (ReviewTest): دسترسی مهمان/کاربر، اعتبارسنجی امتیاز و متن، جریان تأیید تا نمایش، رد نظر، قاعدهٔ تک‌نظر، مجاز بودن نظرات برای محصولات مختلف، محدودیت نرخ، محصول غیرفعال، رندر میانگین/تعداد/تفکیک، شمارش فقط تأییدشده‌ها در آمار، امتیاز کارت محصول، نشان خریدار تأییدشده (خریدار واقعی / غیرخریدار / سفارش لغوشده)، راهنمای ورود مهمان، جعبهٔ وضعیت نظر خودِ کاربر، گاردهای دسترسی ادمین، تب‌های وضعیت، جریان‌های تأیید/رد/حذف، 403 برای مشتری، بج سایدبار و حذف آبشاری با محصول — در مجموع **۱۶۲ تست، ۶۳۲ ادعا**

## [2.8.0] — ۲۰۲۶-۱۰-۲۴

### 🔍 افزوده شد — کشف هوشمند محصولات

- صفحهٔ محصولات به **فیلتر بازهٔ قیمت** (حداقل/حداکثر، هر کدام به‌تنهایی هم کار می‌کند) و **فیلتر «فقط کالاهای موجود»** مجهز شد — هر دو آزادانه با جستجو، دسته و مرتب‌سازی ترکیب می‌شوند و در لینک‌های صفحه‌بندی هم حفظ می‌شوند (`withQueryString`)
- ورودی قیمت **ارقام فارسی/عربی و جداکنندهٔ هزارگان** را می‌فهمد (`۵۰۰٬۰۰۰` = `500,000` = `500000`) — همان رویکرد نرمال‌سازی کد تخفیف و پیگیری سفارش، این بار با هلپر جدید `en_num()` (قرینهٔ `fa_num`)
- اگر بازه برعکس وارد شود (حداقل > حداکثر) **بی‌صدا جابه‌جا می‌شود** تا کاربر به‌خاطر یک اشتباه تایپی نتیجهٔ خالی نبیند؛ ورودی غیرعددی هم نادیده گرفته می‌شود
- مرتب‌سازی جدید **«پرفروش‌ترین»** — رتبه‌بندی بر اساس تعداد فروش با یک زیرکوئری `withSum`؛ اقلام سفارش‌های لغوشده هرگز حساب نمی‌شوند (همان قاعدهٔ داشبورد) و تساوی‌ها به نفع محصول جدید‌تر می‌شکنند
- **ارتقای محصولات مشابه**: همچنان هم‌دسته و فعال، اما حالا **موارد موجود اول می‌آیند** (داخل هر گروه، جدیدترین اول) به‌جای چیدمان تصادفی — بخشش قطعی و قابل‌آزمون شد و ناموجودها فقط جاهای خالی را پر می‌کنند
- اعتبارسنجی به فرم‌ریکوئست اختصاصی `ProductFilterRequest` منتقل شد؛ نشانگر «فیلتر فعال» با دکمهٔ حذف یک‌کلیکی کنار تعداد نتایج اضافه شد و حالت خالی هم دکمهٔ حذف فیلترها دارد
- ۲۱ تست جدید (ProductDiscoveryTest): حداقل/حداکثر/بازهٔ قیمت، جابه‌جایی بازهٔ معکوس، ارقام فارسی و جداکننده، ورودی غیرعددی، فیلتر موجودی، رتبه‌بندی پرفروش، حذف اقلام لغوشده، شکست تساوی با جدیدترین، ترکیب فیلترها، رشتهٔ کوئری صفحه‌بندی، نمایش نشانگر و دکمهٔ حذف، خطاهای اعتبارسنجی، رگرسیون محصولات غیرفعال، ترتیب/حذف/جانشینی محصولات مشابه — در مجموع **۱۳۳ تست، ۵۳۷ ادعا**

## [2.7.0] — ۲۰۲۶-۱۰-۲۱

### 🚚 افزوده شد — پیگیری سفارش مهمان

- صفحهٔ عمومی پیگیری سفارش در `/track` — مشتریانی که به‌صورت مهمان خرید کرده‌اند حالا با **شماره سفارش + شماره موبایلی که هنگام خرید وارد کرده‌اند** سفارششان را دنبال می‌کنند؛ هر دو باید همزمان بخوانند، یکی به‌تنهایی کافی نیست
- نتیجه همان تایم‌لاین ۴ مرحله‌ای بخش حساب کاربری، کارت وضعیت پرداخت، فهرست کامل اقلام با سطر تخفیف و وضعیت لغو برای سفارش‌های لغوشده را نشان می‌دهد
- تلاش مجدد پرداخت برای مهمان‌ها: بعد از پیگیری موفق، سفارش آنلاینِ پرداخت‌نشده دکمهٔ پرداخت/تلاش مجدد را نشان می‌دهد — با دقیقاً همان مکانیزم سشن `viewed_orders` تسویه‌حساب؛ بدون هیچ سطح دسترسی جدید
- طراحی امنیت‌محور: پیام خطای یکسان برای شماره سفارش غلط و موبایل غلط (ضد enumeration)، محدودیت نرخ مثل فرم ورود (۱۰ در دقیقه)، صفحهٔ حداقلِ داده (آدرس هرگز نمایش داده نمی‌شود، موبایل ماسک `۰۹۱۲***۷۸۹`)، اعتبار ۳۰ دقیقه‌ای سشن و جریان Post/Redirect/Get تا رفرش هرگز فرم را دوباره ارسال نکند
- نرمال‌سازی ارقام فارسی/عربی در هر دو فیلد (همان رویکرد کد تخفیف) — با کیبورد فارسی هم کار می‌کند
- نوبار به لینک «پیگیری سفارش» مجهز شد و صفحهٔ موفقیت سفارش مهمان‌ها را برای پیگیری‌های بعدی راهنمایی می‌کند
- ۱۶ تست جدید (GuestTrackingTest): جستجوی درست/غلط/ناهماهنگ، پیام یکسان ضد enumeration، ارقام فارسی، انقضای سشن، محدودیت نرخ، وضعیت لغو، دسترسی تلاش مجدد، مخفی‌سازی آدرس، ماسک موبایل — در مجموع **۱۱۲ تست، ۴۷۶ ادعا**

## [2.6.0] — ۲۰۲۶-۱۰-۱۸

### 📊 افزوده شد — داشبورد گزارش‌های مدیریت

- داشبورد ادمین حالا یک صفحهٔ کامل گزارش‌ها است: شاخص‌های فروش **امروز / این ماه / کل** به‌همراه **میانگین ارزش سفارش** — سفارش‌های لغوشده از همهٔ اعداد فروش حذف هستند
- نمودار فروش از ۷ روز (با یک کوئری برای هر روز) به **نمودار ۱۴ روزه با یک کوئری گروهی** ارتقا یافت — روزهای بدون فروش صفر پر می‌شوند تا نمودار همیشه کامل باشد؛ روی هر ستون تعداد سفارش و مبلغ همان روز هست
- جدول جدید **پرفروش‌ترین محصولات** — رتبه‌بندی بر اساس تعداد فروش به‌همراه درآمد هر محصول؛ اقلام سفارش‌های لغوشده حساب نمی‌شوند
- تفکیک جدید **وضعیت سفارش‌ها** — تعداد، درصد سهم و نوار پیشرفت برای هر وضعیت + سطر پایانی با کاربران جدید این ماه و تعداد محصولات
- سرویس جدید `DashboardStats` تمام کوئری‌های داشبورد را در یک کلاس قابل‌آزمون جمع می‌کند — کنترلر فقط لاغر شده است
- پنل موجودی کم به **بج جداگانهٔ ناموجود** مجهز شد و آستانهٔ موجودی + طول نمودار به کانفیگ رفت: `SHOP_DASHBOARD_DAYS` (پیش‌فرض ۱۴) و `SHOP_LOW_STOCK_THRESHOLD` (پیش‌فرض ۳) — در `.env.example` مستند شده‌اند
- ۱۰ تست جدید (DashboardTest): گاردهای دسترسی، ریاضی شاخص‌ها با سفارش لغوشده و ماه قبل، پوشش سری نمودار، رتبه‌بندی پرفروش‌ها، درصدهای وضعیت، آستانهٔ موجودی (پیش‌فرض و override) و حالت‌های خالی — در مجموع **۹۶ تست، ۴۰۶ ادعا**

## [2.5.0] — ۲۰۲۶-۱۰-۱۶

### 📱 افزوده شد — اطلاع‌رسانی پیامکی (کاوه‌نگار)

- کانال پیامک برای اطلاع‌رسانی سفارش — همان سه رویداد ایمیل (ثبت سفارش / تأیید پرداخت / تغییر وضعیت) حالا به‌صورت پیام‌های کوتاه فارسی هم به شماره موبایلی که از همان ابتدای فرم تسویه گرفته می‌شود ارسال می‌شود؛ **بدون هیچ تغییر در دیتابیس و بدون دست‌زدن به حتی یک خط از کنترلرها** — کل قابلیت فقط از طریق binding قرارداد `OrderNotifier` در سرویس‌پرووایدر وصل می‌شود؛ همان تعویضی که در معماری v2.4.0 قولش را دادیم
- قرارداد `SmsGateway` (قرینهٔ `PaymentGateway`): `KavenegarSmsGateway` ارسال واقعی از طریق REST API کاوه‌نگار، `LogSmsGateway` فقط پیام را برای توسعه و تست لاگ می‌کند
- `CompositeNotifier` — با فعال‌بودن هر دو کانال `mail` و `sms`، هر رویداد به هر دو می‌رسد و خطای یک کانال هرگز کانال دیگر را متوقف نمی‌کند
- کلیدهای جدید env/config: `SHOP_NOTIFICATION_CHANNELS` (پیش‌فرض `mail`؛ مثال: `mail,sms`)، `SMS_DRIVER` (`log` | `kavenegar`)، `KAVENEGAR_API_KEY` و `KAVENEGAR_FROM` — همه در `.env.example` مستند شده‌اند
- همان قانون ایزوله‌سازی خطای ایمیل: خطای پیامک گرفته و لاگ می‌شود — هرگز ثبت سفارش یا پرداخت را نمی‌شکند
- ۱۵ تست جدید (SmsTest): ارسال بر اساس کانال، زنجیرهٔ دوکاناله، ایزوله‌سازی خطا، محتوای پیام (شماره سفارش، مبلغ با ارقام فارسی، کد پیگیری بانکی، برچسب وضعیت قبلی ← جدید)، قرارداد HTTP کاوه‌نگار، خطای API / نبود کلید، تشخیص درایور و نادیده‌گرفتن کانال ناشناخته — در مجموع **۸۶ تست، ۳۳۱ ادعا**

## [2.4.0] — ۲۰۲۶-۱۰-۱۴

### 📧 افزوده شد — اطلاع‌رسانی ایمیلی سفارش

- فیلد اختیاری `customer_email` در فرم تکمیل خرید — سفارش‌ها (حتی خرید مهمان) می‌توانند مشتری را با ایمیل مطلع کنند؛ اگر خالی بماند، ایمیل حساب کاربری واردشده به‌عنوان جایگزین استفاده می‌شود
- اطلاع‌رسانی قراردادمحور (`App\Contracts\OrderNotifier`) با همان الگوی درگاه پرداخت — پیاده‌سازی پیش‌فرض ایمیل می‌فرستد، `NullNotifier` در حالت خاموش ساکت است و کانال (مثلاً SMS) بدون دست‌زدن به منطق اصلی قابل تعویض است
- سه قالب ایمیلی راست‌چین فارسی با CSS درون‌خطی (سازگار با کلاینت‌های ایمیل): خلاصه سفارش، ریز مبلغ با سطر تخفیف
  - **ثبت سفارش** — بلافاصله پس از checkout (هم پرداخت آنلاین، هم پرداخت در محل)
  - **تأیید پرداخت** — دقیقاً یک بار، بلافاصله پس از verify درگاه (کال‌بک‌های تکراری هرگز ایمیل تکراری نمی‌سازند)
  - **تغییر وضعیت** — هنگام تغییر وضعیت سفارش توسط ادمین (پردازش / ارسال / تحویل / لغو)، با ذکر وضعیت قبلی و جدید
- ایزوله‌سازی خطا: خطاهای ارسال اطلاع‌رسانی گرفته و لاگ می‌شوند — خرابی SMTP هرگز ثبت سفارش یا تأیید پرداخت را نمی‌شکند
- نمای سفارش ادمین حالا ایمیل مشتری را کنار شماره موبایل نشان می‌دهد
- کلید تنظیم: `SHOP_NOTIFICATIONS=true|false` در `.env` (در `.env.example` مستند شده)؛ با `MAIL_MAILER=log` ایمیل‌ها در `storage/logs/laravel.log` می‌ریزند — بدون SMTP برای توسعه محلی
- ۱۰ تست فیچر جدید (ارسال/جایگزین/ایمیل نامعتبر/کانفیگ خاموش/ایزوله‌سازی خطای SMTP/عدم تکرار/وضعیت بدون تغییر) — در مجموع **۷۱ تست، ۲۷۶ ادعا**

## [2.3.0] — ۲۰۲۶-۱۰-۱۱

### 🎟 افزوده شد — کدهای تخفیف

- جدول جدید `coupons`: کدهای درصدی یا مبلغی، حداقل مبلغ سبد اختیاری، سقف مصرف کل اختیاری، بازه شروع/انقضا اختیاری و کلید فعال/غیرفعال
- سبد خرید: باکس وارد کردن کد تخفیف در خلاصه سفارش — کدهای غلط/منقضی/تکمیل‌ظرفیت/کمتر از حداقل با پیام فارسی روشن رد می‌شوند؛ کد معتبر چیپ اعمال‌شده، سطر تخفیف و مبلغ نهایی قابل پرداخت را نشان می‌دهد
- تسویه: خلاصه سفارش جمع، سطر تخفیف و مبلغ قابل پرداخت را نشان می‌دهد؛ پیش از ثبت سفارش کد یک بار شفاف اعتبارسنجی مجدد می‌شود — اگر نامعتبر شده بود مشتری به سبد برمی‌گردد، نه اینکه بی‌خبر قیمت کامل بپردازد
- سفارش‌ها حالا `subtotal` و `discount_amount` و `coupon_code` (متن تاریخی، بدون FK) را ذخیره می‌کنند — صفحه موفقیت، حساب کاربری و نمای ادمین همه ریز تخفیف را نشان می‌دهند
- بازخرید ضد رقابت: داخل تراکنش سفارش، ردیف کوپن قفل (`lockForUpdate`) و مجدداً اعتبارسنجی می‌شود و بعد `used_count` زیاد می‌شود؛ سقف مصرف با تسویه‌های همزمان قابل عبور نیست
- تخفیف مبلغی همیشه به جمع سبد محدود است — مبلغ قابل پرداخت هرگز منفی نمی‌شود
- پنل ادمین: مدیریت کامل کوپن‌ها (لیست با بج وضعیت — فعال / غیرفعال / منقضی / تکمیل ظرفیت / شروع نشده — ساخت، ویرایش، حذف با تأیید) زیر آیتم منوی «کدهای تخفیف»
- نرمال‌سازی کد در همه‌جا: ارقام فارسی/عربی تبدیل، فاصله‌ها حذف و حروف بزرگ می‌شوند؛ `summer 500` که ادمین بنویسد `SUMMER500` می‌شود
- سیدر یک کوپن نمونه `WELCOME10` (۱۰٪، ۱۰۰ بار) می‌سازد تا قابلیت بلافاصله بعد از `shop:install` دیده شود
- ۱۸ تست فیچر جدید (مسیرهای اعمال/حذف/رد، محاسبه سقف، ذخیره در تسویه، افزایش مصرف، گارد نامعتبر-در-تسویه، CRUD ادمین + گاردها) — در مجموع **۶۱ تست، ۲۳۱ ادعا**

## [2.2.0] — ۲۰۲۶-۱۰-۰۵

### 🖼 افزوده شد — گالری تصاویر محصول

- جدول جدید `product_images` با ترتیب نمایش — هر محصول می‌تواند در کنار تصویر اصلی، تصاویر اضافی داشته باشد
- فرم ادمین: آپلود چندتایی (حداکثر ۴ تصویر در هر ارسال، همان قواعد فرمت/حجم/ابعاد تصویر اصلی) + چک‌باکس حذف برای هر تصویر؛ گالری موجود در هر ویرایش به‌صورت بندانگشتی نمایش داده می‌شود
- صفحه جزئیات محصول: نوار بندانگشتی زیر تصویر اصلی — با کلیک، تصویر بزرگ عوض می‌شود؛ برای محصول بدون گالری هیچ نوار اضافه‌ای رندر نمی‌شود
- حذف ضد IDOR: درخواست‌های حذف همیشه از رابطه محصول پاس داده می‌شوند، پس ID تصویر محصول دیگر هیچ اثری ندارد؛ فایل‌ها همراه رکوردشان حذف می‌شوند و حذف محصول، فایل‌های گالری‌اش را هم پاک می‌کند

### 🔧 رفع شد — خرابی CI (پین platform کمپوزر)

- علت بج قرمز CI: فایل قفل روی PHP 8.4 و بدون پین platform رزول شده بود و بسته‌هایی را می‌آورد که 8.2/8.3 را قبول نمی‌کنند (`symfony/*` 8.1 نیازمند `>=8.4.1`، `pint` 1.32 نیازمند `^8.3`) — در نتیجه پاهای 8.2/8.3 ماتریس در مرحله `composer install` رد می‌شدند
- راه‌حل: `config.platform.php = "8.2.0"` به `composer.json` اضافه و یک `composer update` کامل اجرا شد — قفل جدید برای PHP 8.2.0+ رزول می‌شود و روی همه پاهای ماتریس نصب می‌شود (با `composer install --dry-run` تأیید شد)
- نسخه‌های قفل‌شده جدید: laravel/pint 1.30.4 و symfony 7.4.x؛ فریم‌ورک همان 12.69.3 می‌ماند

### 🔍 تغییرات

- ۸ تست فیچر جدید (آپلود/ویرایش/حذف گالری، محافظ IDOR، سقف ۴ تصویر، رندر صفحه جزئیات، گارد مهمان) — در مجموع **۴۳ تست، ۱۴۵ ادعا**

## [2.1.0] — ۲۰۲۶-۱۰-۰۵

### 🌐 افزوده شد — وب‌سایت پروژه (GitHub Pages)

- پوشه `docs/` جدید با **صفحه دموی ایستای راست‌چین** (`index.html`) که صفحه اصلی فروشگاه را بازسازی می‌کند: هیرو، دسته‌بندی‌ها، هر ۸ محصول نمونه با تصویر و قیمت واقعی، کارت‌های امکانات و ترمینال راه‌اندازی
- میزبانی **رایگان روی GitHub Pages** در <https://mss1625.github.io/shop-php/> — فقط با یک تنظیم فعال می‌شود (Settings ← Pages ← شاخه main / پوشه docs)
- همراه با `.nojekyll` و `robots.txt`؛ همه دکمه‌ها به مخزن لینک می‌شوند

### 📝 تغییرات — بین‌المللی‌سازی README

- README به **`README.md` (انگلیسی)** + **`README.fa.md` (فارسی)** تفکیک شد با سوییچر زبان دوسویه بالای هر فایل — مشکل نبودن لینک نسخه انگلیسی حل شد
- `README.fa.md` حالا هم‌تراز کامل با نسخه انگلیسی است (ساختار پروژه، نقشه راه و مشارکت اضافه شد)
- بج Release حالا **خودکار از GitHub Releases** به‌روز می‌شود (`github/v/release`) به‌جای نسخه ثابت دستی
- لینک پیش‌نمایش آنلاین به هر دو فایل اضافه شد

### 📦 افزوده شد — ابزار انتشار

- `RELEASE_NOTES.md` — یادداشت دوزبانه آماده پیست برای ریلیز v2.1.0 در گیت‌هاب

## [2.0.0] — ۲۰۲۶-۱۰-۰۴

### 💳 افزوده شد — درگاه پرداخت زرین‌پال

- اتصال کامل پرداخت آنلاین با **ZarinPal v4 REST API** (`request.json` / `verify.json` / `StartPay`)
- **سه حالت آماده**: `mock` (شبیه‌ساز محلی — بدون نیاز به مرچنت‌آی‌دی)، `sandbox` و `production`
- **تأیید سمت سرور** در کال‌بک — وضعیت پرداخت هرگز صرفاً از URL گرفته نمی‌شود (ضد دستکاری)
- ثبت اطلاعات پرداخت برای هر سفارش: روش، وضعیت، `authority`، `ref_id` و زمان پرداخت
- گزینه **پرداخت در محل** در کنار پرداخت آنلاین
- امکان **تلاش مجدد** برای پرداخت‌های ناموفق روی همان سفارش، بدون ایجاد سفارش تکراری
- لایه انتزاعی پرداخت (`PaymentGateway`) برای تعویض آسان با هر PSP ایرانی دیگر

### 👤 افزوده شد — حساب کاربری و تاریخچه سفارش‌ها

- **ثبت‌نام / ورود / خروج** مشتری با محدودیت تعداد تلاش ورود
- **داشبورد شخصی**: تعداد کل سفارش‌ها، مبلغ پرداخت‌شده، سفارش‌های در انتظار
- صفحه **تاریخچه سفارش‌ها** (صفحه‌بندی‌شده) با نشان وضعیت و پرداخت
- صفحه **رهگیری سفارش** با تایم‌لاین چهار مرحله‌ای
- **لغو سفارش** پرداخت‌نشده در حالت «در انتظار» — با بازگردانی خودکار و اتمیک موجودی
- محافظت IDOR: هر کاربر فقط سفارش‌های خودش را می‌بیند
- خرید مهمان همچنان ممکن است — داشتن حساب اختیاری است

### 🤖 افزوده شد — GitHub Actions

- ورک‌فلاو `.github/workflows/ci.yml` که **روی هر push** (همه شاخه‌ها) و هر Pull Request به‌صورت خودکار اجرا می‌شود
- ماتریس تست: **PHP 8.2 / 8.3 / 8.4** روی `ubuntu-latest` با SQLite
- جاب جداگانه **بررسی استایل کد (Pint)**
- کش وابستگی‌های Composer برای اجرای سریع‌تر

### 🔍 تغییرات

- جدول سفارش‌های ادمین: ستون‌های **روش و وضعیت پرداخت** اضافه شد
- صفحه جزئیات سفارش ادمین: **کارت پرداخت** کامل (شماره پیگیری، زمان پرداخت)
- منوی سایت: **دراپ‌داون کاربر** (حساب من، سفارش‌ها، خروج) برای مشتریان واردشده
- ۲۶ تست فیچر جدید (احراز هویت، حساب کاربری، پرداخت) — در مجموع **۳۵ تست**، همه سبز ✅

## [1.0.0] — ۲۰۲۶-۱۰-۰۴

### ⚡ نسخه اول لاراولی

- بازنویسی کامل پروژه PHP ساده با **Laravel 12** (PHP 8.2+)
- سبد خرید کامل (مبتنی بر سشن، AJAX) و تسویه‌حساب تراکنشی با اعتبارسنجی مجدد موجودی
- مدیریت سفارش با ۵ وضعیت و اسنپ‌شات قیمت‌ها
- پنل ادمین: داشبورد آماری و نمودار فروش ۷ روزه، CRUD محصول/دسته، آپلود امن تصویر، مدیریت سفارش
- امنیت: CSRF، محدودیت ورود ادمین، تولید مجدد سشن، هدرهای امنیتی، بدون رمز پیش‌فرض
- نصب یک‌فرمانی: `php artisan shop:install`
- تم دارک RTL، فونت وزیرمتن، اعداد و ارز فارسی
- README دوزبانه، لایسنس MIT، ۹ تست فیچر

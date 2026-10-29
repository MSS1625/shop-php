# ❤️ v2.10.0 — Customer Wishlist

<div dir="rtl" align="center">

**دیجی‌شاپ** — فروشگاه آنلاین تجهیزات شبکه | **Digi-Shop** — Online network-equipment store
Laravel 12 · PHP 8.2+ · Bootstrap 5 RTL · MIT License

</div>

## 🇬🇧 English

### ❤️ New — Customer Wishlist

- Customers can now **save products with one click on the heart** — the button sits on every product card and next to add-to-cart on the product page; a second click removes it (toggle), and with JavaScript the heart flips in place with a toast, no reload
- **Progressive enhancement, not JavaScript-dependence**: the heart is a real POST form with a Post/Redirect/Get fallback, so it works even with scripting disabled — the same philosophy as the rest of the storefront
- New **`/wishlist` page**: saved products on the familiar product cards, newest saved first, 12 per page — linked from the navbar heart (with a live count badge) and the account dropdown; a friendly empty state guides new users to the shop
- Hearts stay in sync across the whole storefront (listings, product page, related products) via a new **`Wishlist` singleton service — the exact pattern the cart already uses**: one memoized query per request, no per-card queries, no N+1
- **One row per user per product, enforced by the database** (unique constraint + idempotent toggle) — double-clicks and double-tab submits can't create duplicates
- Only **active products** count: something the admin deactivated quietly disappears from the wishlist and the badge; deleting a product cascades its wishlist rows away
- Guests clicking a heart land on the login page with a hint — never a dead button
- 19 new tests (WishlistTest) covering access, toggle flows, Ajax JSON, inactive guards, ordering, pagination, navbar badge states and cascade integrity

### 📊 Numbers

- **181 feature tests** (~690 assertions) — 19 new wishlist tests: guest access, add/remove toggle, JSON responses, inactive-product guard, newest-first ordering, own-items-only, inactive hiding, empty state, 12-per-page pagination, navbar badge (Persian numeral, active-only, hidden for guests), solid/outline heart states, product-page form, account dropdown link, product-deletion cascade
- Code style: Pint clean
- One new migration (`wishlists`) — `php artisan shop:install` or plain `migrate` handles it; zero config changes

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
# Register an account, browse the products listing:
# → click the heart on a card — it fills in red, the navbar heart
#   gains a count badge (۱) and a toast confirms «به علاقه‌مندی‌ها اضافه شد».
# → click the navbar heart: the /wishlist page lists your saved
#   products, newest first — click the heart again to remove one,
#   the card fades and the badge counts down.
# → Log out: hearts become login links — no dead buttons for guests.
```

</details>

---

## 🇮🇷 فارسی

<div dir="rtl">

### ❤️ جدید — علاقه‌مندی‌های مشتری

- مشتری‌ها حالا می‌توانند **محصولات را با یک کلیک روی قلب ذخیره کنند** — دکمه روی همهٔ کارت‌های محصول و کنار «افزودن به سبد» در صفحهٔ جزئیات هست؛ کلیک دوم همان محصول را حذف می‌کند (toggle) و با جاوااسکریپت قلب همان‌جا می‌چرخد، بدون رفرش
- **بهبود تدریجی، نه وابستگی به جاوااسکریپت**: قلب یک فرم POST واقعی با جریان Post/Redirect/Get است و حتی با خاموش‌بودن اسکریپت‌ها کار می‌کند — همان فلسفهٔ بقیهٔ فروشگاه
- صفحهٔ جدید **/wishlist**: محصولات ذخیره‌شده روی همان کارت‌های آشنا، جدیدترین ذخیره اول، ۱۲تا در صفحه — از قلب ناوبار (با شمارندهٔ زنده) و منوی حساب در دسترس؛ حالت خالیِ دوستانه کاربر تازه‌وارد را به فروشگاه می‌فرستد
- قلب‌ها در کل فروشگاه (لیست، صفحهٔ جزئیات، محصولات مشابه) همگام می‌مانند از طریق **سرویس singleton جدید `Wishlist` — دقیقاً همان الگویی که سبد خرید از قبل دارد**: یک کوئری memoize در هر درخواست، بدون کوئری برای هر کارت، بدون N+1
- **یک ردیف برای هر کاربر/محصول، با پشتیبان دیتابیس** (قید یکتا + toggle پیش‌فرض) — دابل‌کلیک و سابمیت همزمان از دو تب تکراری نمی‌سازد
- فقط **محصولات فعال** حساب می‌شوند: محصولی که مدیر غیرفعال کند بی‌صدا از فهرست و شمارنده کنار می‌رود؛ حذف محصول هم ردیف‌های علاقه‌مندی‌اش را آبشاری پاک می‌کند
- مهمان‌ها با کلیک روی قلب با یک راهنما به صفحهٔ ورود می‌رسند — هرگز دکمهٔ مرده نمی‌بینند
- ۱۹ تست جدید (WishlistTest): دسترسی، جریان‌های toggle، پاسخ Ajax، گاردهای غیرفعال، ترتیب، صفحه‌بندی، وضعیت‌های شمارندهٔ ناوبار و یکپارچگی آبشاری

### 📊 اعداد

- **۱۸۱ تست فیچر** (۶۸۵ ادعا) — ۱۹ تست جدید علاقه‌مندی: دسترسی مهمان، افزودن/حذف، پاسخ JSON، محافظ محصول غیرفعال، ترتیب جدیدترین‌اول، فقط-آیتم‌های-خود، مخفی‌شدن غیرفعال‌ها، حالت خالی، صفحه‌بندی ۱۲تایی، شمارندهٔ ناوبار (عدد فارسی، فعال-فقط، مخفی برای مهمان)، وضعیت پر/خالی قلب، فرم صفحهٔ جزئیات، لینک منوی حساب، cascade حذف
- سبک کد: Pint پاک
- یک مهاجرت جدید (`wishlists`) — با `php artisan shop:install` یا `migrate` ساده اعمال می‌شود؛ بدون تغییر کانفیگ

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
# ثبت‌نام کنید و لیست محصولات را باز کنید:
# ← روی قلب یک کارت کلیک کنید — قرمز پر می‌شود، قلب ناوبار
#   شمارندهٔ (۱) می‌گیرد و پیام «به علاقه‌مندی‌ها اضافه شد» می‌آید.
# ← روی قلب ناوبار کلیک کنید: صفحهٔ /wishlist محصولات ذخیره‌شده را
#   جدیدترین‌اول نشان می‌دهد — قلب را دوباره بزنید تا حذف شود،
#   کارت محو می‌شود و شمارنده کم می‌شود.
# ← خارج شوید: قلب‌ها لینک ورود می‌شوند — مهمان دکمهٔ مرده نمی‌بیند.
```

</details>

</div>

<!-- ============================================================
  نحوه استفاده | How to use — v2.17.0

  راه خودکار (پیشنهادی):
  ۱) روی گیت‌هاب: Releases → Draft a new release → Choose tag → v2.17.0 (Create on main)
     و Publish — یا فقط تگ را پوش کنید:
        git tag v2.17.0 && git push origin v2.17.0
  ۲) ورک‌فلو release.yml خودش: کل تست‌ها را اجرا می‌کند، یادداشت‌های دوزبانهٔ
     همین نسخه را از CHANGELOG.md استخراج می‌کند و Release را با عنوان و متن
     می‌سازد — نیازی به کپی متن زیر نیست.
  ۳) در صورت تمایل، فایل shop-php-v2.17.0.zip را به Release ضمیمه کنید.

  راه دستی (اگر خواستید خودتان بنویسید): عنوان
  «☁️ v2.17.0 — دیپلوی پروداکشن: VPS رایگان، دامنه، HTTPS خودکار (Production deployment: free VPS, domain, automatic HTTPS)»
  و متن زیر خط جداکننده (---) را در باکس توضیحات پیست کنید.
  ============================================================ -->

---

# ☁️ v2.17.0 — دیپلوی پروداکشن: VPS رایگان، دامنه، HTTPS خودکار (Production deployment: free VPS, domain, automatic HTTPS)

<div dir="rtl" align="center">

**دیجی‌شاپ** — فروشگاه آنلاین تجهیزات شبکه | **Digi-Shop** — Online network-equipment store
Laravel 12 · PHP 8.2+ · Bootstrap 5 RTL · MIT License

</div>

## 🇬🇧 English

### ☁️ New — Production deployment: a free VPS, a domain, automatic HTTPS

- **Two commands deploy the whole store to a real server**: copy `.env.production.example` → `.env.production` (your domain + strong DB passwords), then `docker compose --env-file .env.production -f docker-compose.prod.yml up -d --build --wait` → **https://your-domain**
- **`docker-compose.prod.yml` — the production stack**: the same app/web/db trio plus **Caddy** as a fourth service and the only public entry point (80/443); it obtains the **Let's Encrypt certificate automatically**, redirects HTTP→HTTPS, speaks HTTP/3 and **renews the certificate itself** (certs persist in the `caddy-data` volume)
- **Production secrets stay secret**: domain and DB credentials come from `.env.production` — compose refuses to boot without them; they're injected as real environment variables that override the dev defaults inside the container (booleans deliberately not injected — a string `"false"` would truthy-cast)
- **The one real PHP fix — `trustProxies`**: behind a reverse proxy Laravel saw internal HTTP, so URLs/redirects could come out as `http://`; now `X-Forwarded-For/Proto/Host` are trusted (`at: '*'`), with a new `TrustedProxyTest` proving URLs flip to https only when the proxy header is present
- **`docs/DEPLOY.md` + `docs/DEPLOY.fa.md` — the honest free-tier guide**: what is really free in autumn 2026 (Oracle Always Free ⭐ ≈ 2 ARM cores + 12 GB RAM always-on; GCP e2-micro; Codespaces 60 h/month; Render and why it doesn't fit), then step by step: ARM instance, the Oracle Security List trap (open 80/443!), DNS-first-then-deploy, backups, troubleshooting — plus a zero-server practice path on Codespaces
- CI's docker job now also validates the production compose file on every push/PR

### 📊 Numbers

- **Zero migrations** — **306 tests (1187 assertions)**, all green; Pint clean; Larastan level 5: 0 errors
- New files: `docker-compose.prod.yml`, `deploy/Caddyfile`, `.env.production.example`, `docs/DEPLOY.md`, `docs/DEPLOY.fa.md`, `tests/Feature/TrustedProxyTest.php`; `bootstrap/app.php` + `trustProxies`; `ci.yml` + prod-compose validation step
- All stack images ship arm64 builds — the free Oracle ARM instances run it as-is

<details>
<summary>Seeing it in action (on any Ubuntu server)</summary>

```bash
git clone https://github.com/MSS1625/shop-php.git && cd shop-php
cp .env.production.example .env.production      # set DIGISHOP_DOMAIN + strong DB passwords
docker compose --env-file .env.production -f docker-compose.prod.yml up -d --build --wait
# → https://your-domain  (Caddy gets the certificate automatically)

# tips:
alias dc='docker compose --env-file .env.production -f docker-compose.prod.yml'
dc logs -f caddy     # certificate / proxy logs
dc logs -f app       # Laravel logs (admin password printed once on first boot)
dc exec db sh -c 'exec mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > backup.sql
```

Full guide — free tier comparison, DNS, updates, image backups, troubleshooting: `docs/DEPLOY.md`

</details>

<details>
<summary>Local development (unchanged)</summary>

```bash
git clone https://github.com/MSS1625/shop-php.git && cd shop-php
docker compose up -d --wait     # dev stack → http://localhost:8080
# or without Docker:
composer install && php artisan shop:install && php artisan serve
```

</details>

## 🇮🇷 فارسی

### ☁️ جدید — دیپلوی پروداکشن: VPS رایگان، دامنه، HTTPS خودکار

- **دو دستور، فروشگاه روی سرور واقعی**: `.env.production.example` را به `.env.production` کپی کنید (دامنه + رمزهای قوی دیتابیس)، بعد `docker compose --env-file .env.production -f docker-compose.prod.yml up -d --build --wait` → **https://دامنهٔ-شما**
- **`docker-compose.prod.yml` — استک پروداکشن**: همان سه سرویس app/web/db به‌همراه **Caddy** به‌عنوان سرویس چهارم و تنها دروازهٔ عمومی (۸۰/۴۴۳)؛ **گواهی Let's Encrypt را خودکار می‌گیرد**، HTTP را به HTTPS ریدایرکت می‌کند، HTTP/3 حرف می‌زند و **تمدید گواهی را هم خودش انجام می‌دهد** (گواهی‌ها روی volume با نام `caddy-data` می‌مانند)
- **رمزهای پروداکشن محفوظ می‌مانند**: دامنه و اطلاعات دیتابیس از `.env.production` خوانده می‌شوند — بدون آنها compose بالا نمی‌آید؛ به‌صورت متغیر محیطی واقعی به کانتینر تزریق می‌شوند و پیش‌فرض‌های توسعه را override می‌کنند (بولین‌ها عمداً تزریق نمی‌شوند — رشتهٔ `"false"` به‌عنوان true خوانده می‌شد!)
- **تنها تغییر واقعی PHP — `trustProxies`**: پشت پروکسی معکوس لاراول HTTP داخلی می‌دید و URLها می‌توانستند `http://` دربیایند؛ حالا هدرهای `X-Forwarded-For/Proto/Host` باور می‌شوند و تست جدید `TrustedProxyTest` ثابت می‌کند URLها فقط با وجود هدر پروکسی https می‌شوند
- **`docs/DEPLOY.fa.md` و `docs/DEPLOY.md` — راهنمای صادقانهٔ Free Tierها**: پاییز ۲۰۲۶ واقعاً چه چیزی رایگان است (Oracle Always Free ⭐ معادل ۲ هسته ARM و ۱۲ گیگ رم همیشه‌روشن؛ e2-micro گوگل؛ ۶۰ ساعت در ماه Codespaces؛ Render و اینکه چرا مناسب این پروژه نیست)، بعد قدم‌به‌قدم: ماشین ARM، تلهٔ Security List اوراکل (باز کردن ۸۰/۴۴۳!)، اول-DNS-بعد-دیپلوی، بکاپ، عیب‌یابی — به‌همراه مسیر تمرین بدون سرور روی Codespaces
- جاب داکر در CI از این پس فایل compose پروداکشن را هم اعتبارسنجی می‌کند

### 📊 اعداد

- **صفر مایگریشن** — **۳۰۶ تست (۱۱۸۷ ادعا)**، همه سبز؛ Pint تمیز؛ لاراستان سطح ۵: صفر خطا
- فایل‌های جدید: `docker-compose.prod.yml`، `deploy/Caddyfile`، `.env.production.example`، `docs/DEPLOY.md`، `docs/DEPLOY.fa.md`، `tests/Feature/TrustedProxyTest.php`؛ `bootstrap/app.php` با `trustProxies`؛ گام اعتبارسنجی compose پروداکشن در `ci.yml`
- همهٔ ایمیج‌های استک arm64 دارند — ماشین‌های رایگان ARM اوراکل بدون هیچ کاری اجرایش می‌کنند

<details>
<summary>دیدنش در عمل (روی هر سرور اوبونتویی)</summary>

```bash
git clone https://github.com/MSS1625/shop-php.git && cd shop-php
cp .env.production.example .env.production      # DIGISHOP_DOMAIN + رمزهای قوی دیتابیس
docker compose --env-file .env.production -f docker-compose.prod.yml up -d --build --wait
# ← https://دامنهٔ-شما  (گواهی را Caddy خودکار می‌گیرد)

# میان‌بر:
alias dc='docker compose --env-file .env.production -f docker-compose.prod.yml'
dc logs -f caddy     # لاگ گواهی و پروکسی
dc logs -f app       # لاگ لاراول (رمز مدیر یک‌بار در بوت اول چاپ می‌شود)
dc exec db sh -c 'exec mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > backup.sql
```

راهنمای کامل — مقایسهٔ Free Tierها، DNS، آپدیت، بکاپ تصاویر، عیب‌یابی: `docs/DEPLOY.fa.md`

</details>

<details>
<summary>توسعهٔ محلی (بدون تغییر)</summary>

```bash
git clone https://github.com/MSS1625/shop-php.git && cd shop-php
docker compose up -d --wait     # استک توسعه → http://localhost:8080
# یا بدون داکر:
composer install && php artisan shop:install && php artisan serve
```

</details>

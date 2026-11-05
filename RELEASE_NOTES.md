# 🐳 v2.16.0 — داکر: کل فروشگاه در یک دستور (Docker: the whole shop in one command)

<div dir="rtl" align="center">

**دیجی‌شاپ** — فروشگاه آنلاین تجهیزات شبکه | **Digi-Shop** — Online network-equipment store
Laravel 12 · PHP 8.2+ · Bootstrap 5 RTL · MIT License

</div>

## 🇬🇧 English

### 🐳 New — Docker: the whole shop in one command

- **`docker compose up -d --wait` → http://localhost:8080** — the full store (PHP-FPM app, nginx, MySQL 8.4) boots with one command; no PHP, no Composer, no MySQL on the host — only Docker
- **One image, three containers**: a multi-stage `Dockerfile` (composer:2 stage bakes the optimized no-dev autoloader, then `php:8.3-fpm-alpine` + nginx) runs twice — the `app` service is php-fpm, the `web` service is the same image in web role serving static files and proxying PHP to `app:9000` by service name; `db` is the official `mysql:8.4` with a health-checked volume
- **The container boots through the project's own installer**: the entrypoint copies `.env.docker` → `.env`, waits for MySQL to be really ready (healthchecks + a `docker/await-db.php` retry loop — `depends_on` alone only guarantees order), then runs the same idempotent `php artisan shop:install` the project has had since v1.0.0 — restart-safe, no duplicate data
- **Data survives rebuilds** in the `db-data` and `storage-data` volumes; host port changeable via `DIGISHOP_PORT`; admin password printed once in `docker compose logs app` (or set `ADMIN_PASSWORD` in `.env.docker`)
- **Tests inside a container, zero host tooling**: `docker compose run --rm test` runs all 304 tests on SQLite in-memory
- **New `docker` job in CI** proves the image on every push and PR: build → healthcheck-gated compose up → homepage smoke test (title + seeded product) → full suite in the `test` container
- `.dockerignore` keeps the image lean and excludes the machine-local `public/storage` symlink (it would dangle in the container; the entrypoint recreates it each boot)

### 📊 Numbers

- **Zero migrations, zero PHP code changes** — the suite stays at **304 tests (1183 assertions)**, all green; Pint clean (163 files); Larastan level 5: 0 errors
- New files: `Dockerfile`, `docker-compose.yml`, `.dockerignore`, `.env.docker`, `docker/nginx.conf`, `docker/entrypoint.sh`, `docker/await-db.php`; `ci.yml` gained the `docker` job (now five jobs + coverage on main)

<details>
<summary>Seeing it in action</summary>

On any machine with Docker:

```bash
git clone https://github.com/MSS1625/shop-php.git && cd shop-php
docker compose up -d --wait     # build + boot app/web/db, gated on healthchecks
# → http://localhost:8080

docker compose logs app         # admin password + Laravel logs (stderr)
docker compose run --rm test    # the whole 304-test suite, inside a container
docker compose down             # stop (data stays) · add -v to wipe
```

On GitHub, after pushing this version:

- **Actions → CI** now shows the **Docker — build & boot the full stack** job beside the test matrix, Pint, Larastan and Coverage: it builds the production image, boots the whole stack with `--wait`, greps the rendered homepage for the store title and a seeded product, then runs the full suite inside the `test` container

</details>

<details>
<summary>Quick start without Docker (unchanged)</summary>

```bash
git clone https://github.com/MSS1625/shop-php.git && cd shop-php
composer install
php artisan shop:install     # env + key + migrate + seed + link (includes WELCOME10)
php artisan admin:create     # interactive admin user
php artisan serve            # → http://localhost:8000
```

</details>

## 🇮🇷 فارسی

### 🐳 جدید — داکر: کل فروشگاه در یک دستور

- **`docker compose up -d --wait` → http://localhost:8080** — کل فروشگاه (اپ PHP-FPM، nginx، MySQL 8.4) با یک دستور بالا می‌آید؛ بدون PHP و Composer و MySQL روی سیستم — فقط خود داکر
- **یک ایمیج، سه کانتینر**: `Dockerfile` چندمرحله‌ای (مرحلهٔ composer:2 که autoloader بهینهٔ بدون-dev را می‌پزد و بعد `php:8.3-fpm-alpine` + nginx) یک‌بار ساخته و دو بار استفاده می‌شود — سرویس `app` همان php-fpm است و سرویس `web` همان ایمیج در نقش وب: فایل‌های استاتیک را مستقیم می‌دهد و PHP را با نام سرویس به `app:9000` پاس می‌کند؛ `db` هم ایمیج رسمی `mysql:8.4` با volume دارای healthcheck است
- **کانتینر با نصب‌کنندهٔ خود پروژه بوت می‌شود**: entrypoint فایل `.env.docker` را به `.env` کپی می‌کند، برای آماده‌شدن واقعی MySQL صبر می‌کند (healthcheckها + حلقهٔ تلاش مجدد `docker/await-db.php` — `depends_on` به‌تنهایی فقط ترتیب را تضمین می‌کند) و بعد همان `php artisan shop:install` idempotent را اجرا می‌کند که از v1.0.0 همراه پروژه بوده — restart بدون خطر، بدون دادهٔ تکراری
- **داده‌ها از rebuild می‌مانند** (volumeهای `db-data` و `storage-data`)؛ پورت هاست با `DIGISHOP_PORT` قابل تغییر است؛ رمز مدیر یک‌بار در `docker compose logs app` چاپ می‌شود (یا `ADMIN_PASSWORD` را در `.env.docker` بگذارید)
- **تست داخل کانتینر، بدون هیچ ابزاری روی سیستم**: `docker compose run --rm test` هر ۳۰۴ تست را با SQLite درون‌حافظه اجرا می‌کند
- **جاب جدید `docker` در CI** ایمیج را روی هر push و PR اثبات می‌کند: بیلد ← بوت compose با دروازهٔ healthcheckها ← اسموک‌تست صفحهٔ اول (عنوان + محصول seed شده) ← کل تست‌ها در کانتینر `test`
- `.dockerignore` ایمیج را سبک نگه می‌دارد و سیم‌لینک محلی `public/storage` را حذف می‌کند (داخل کانتینر می‌شکست؛ entrypoint در هر بوت می‌سازدش)

### 📊 اعداد

- **صفر مایگریشن و صفر تغییر کد PHP** — تست‌ها همان **۳۰۴ تست (۱۱۸۳ ادعا)**، همه سبز؛ Pint تمیز (۱۶۳ فایل)؛ لاراستان سطح ۵: صفر خطا
- فایل‌های جدید: `Dockerfile`، `docker-compose.yml`، `.dockerignore`، `.env.docker`، `docker/nginx.conf`، `docker/entrypoint.sh`، `docker/await-db.php`؛ جاب `docker` به `ci.yml` اضافه شد (حالا پنج جاب + پوشش روی main)

<details>
<summary>دیدنش در عمل</summary>

روی هر سیستمی که داکر دارد:

```bash
git clone https://github.com/MSS1625/shop-php.git && cd shop-php
docker compose up -d --wait     # بیلد + بوت app/web/db با دروازهٔ healthcheckها
# ← http://localhost:8080

docker compose logs app         # رمز مدیر + لاگ‌های لاراول (روی stderr)
docker compose run --rm test    # کل ۳۰۴ تست، داخل کانتینر
docker compose down             # توقف (داده‌ها می‌مانند) · با -v پاک می‌شوند
```

روی گیت‌هاب، بعد از پوش این نسخه:

- **Actions → CI** حالا جاب **Docker — build & boot the full stack** را کنار ماتریس تست، Pint، لاراستان و پوشش نشان می‌دهد: ایمیج production را می‌سازد، کل استک را با `--wait` بالا می‌آورد، عنوان فروشگاه و یک محصول seed شده را در صفحهٔ رندرشده جستجو می‌کند و بعد کل تست‌ها را داخل کانتینر `test` اجرا می‌کند

</details>

<details>
<summary>شروع سریع بدون داکر (بدون تغییر)</summary>

```bash
git clone https://github.com/MSS1625/shop-php.git && cd shop-php
composer install
php artisan shop:install     # env + کلید + مهاجرت + داده اولیه + لینک
php artisan admin:create     # ساخت مدیر (تعاملی)
php artisan serve            # → http://localhost:8000
```

</details>

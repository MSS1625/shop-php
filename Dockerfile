# syntax=docker/dockerfile:1

# ============================================================
#  دیجی‌شاپ — ایمیج داکر | Digi-Shop Docker image
#
#  app (target پیش‌فرض) → php-fpm + کد + وابستگی‌های production
#  dev  (target تست)    → همان کد + ابزارهای توسعه (phpunit و…)
#
#  سه کانتینر از همین یک ایمیج بالا می‌آیند (docker-compose.yml):
#    app — php-fpm روی پورت ۹۰۰۰
#    web — nginx روی پورت ۸۰ (فایل‌های استاتیک + پروکسی به app:9000)
#    db  — MySQL 8.4 (ایمیج رسمی، جدا از این فایل)
# ============================================================

# ---------- مرحله ۱: وابستگی‌های production ----------
# لایهٔ جدا تا تغییر کد، نصب کامپوزر را دوباره اجرا نکند
FROM composer:2 AS vendor-prod

WORKDIR /app

# اول فقط manifestها — تا وقتی composer.json/lock تغییر نکرده، لایه کش می‌ماند
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev --no-scripts --no-autoloader \
        --prefer-dist --no-interaction --no-progress

# کد اپلیکیشن (هرچه .dockerignore اجازه بدهد)
COPY . .

# autoloader بهینه + کش پکیج‌ها، همان‌جا در ایمیج پخته می‌شود
RUN composer dump-autoload \
        --optimize --no-dev --classmap-authoritative \
        --no-interaction

# ---------- مرحله ۱-ب: وابستگی‌های development (برای target تست) ----------
FROM composer:2 AS vendor-dev

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
        --no-scripts --no-autoloader \
        --prefer-dist --no-interaction --no-progress

COPY . .

RUN composer dump-autoload \
        --optimize --classmap-authoritative \
        --no-interaction

# ---------- مرحله ۲: runtime مشترک ----------
FROM php:8.3-fpm-alpine AS base

# icu-data-full → داده‌های کامل locale برای فارسی
# nginx        → همان باینری برای نقش web (یک ایمیج، دو نقش)
RUN apk add --no-cache \
        icu-data-full \
        nginx \
    && docker-php-ext-install \
        pdo_mysql bcmath intl opcache

WORKDIR /var/www

# ---------- ایمیج نهایی — target پیش‌فرض ----------
FROM base AS app

COPY --from=vendor-prod /app .
COPY docker/entrypoint.sh /usr/local/bin/shop-entrypoint
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

RUN chmod +x /usr/local/bin/shop-entrypoint \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80 9000

ENTRYPOINT ["shop-entrypoint"]
CMD ["app"]

# ---------- target تست/توسعه ----------
# docker compose run --rm test
FROM base AS dev

COPY --from=vendor-dev /app .
COPY docker/entrypoint.sh /usr/local/bin/shop-entrypoint

RUN chmod +x /usr/local/bin/shop-entrypoint \
    && chown -R www-data:www-data storage bootstrap/cache

ENTRYPOINT ["shop-entrypoint"]
CMD ["app"]

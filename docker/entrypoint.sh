#!/bin/sh
# ============================================================
#  دیجی‌شاپ — entrypoint کانتینر | Digi-Shop container entrypoint
#
#  app (پیش‌فرض) : صبر برای دیتابیس → shop:install → php-fpm
#  web          : nginx (فایل‌های استاتیک + پروکسی به app:9000)
#  هر دستور دیگر: همان دستور اجرا می‌شود (php / sh / artisan …)
# ============================================================
set -e

ROLE="${1:-app}"
[ "$#" -gt 0 ] && shift
cd /var/www

# بوت اولِ این کانتینر: .env از قالب داکر ساخته می‌شود؛
# بوت‌های بعدی (restart) به فایل موجود دست نمی‌زنند تا APP_KEY و تنظیمات بمانند
if [ ! -f .env ]; then
    cp .env.docker .env
    echo "[entrypoint] .env ساخته شد (از .env.docker)"
fi

case "$ROLE" in

    web)
        # نقش وب به دیتابیس نیازی ندارد؛ فقط لینک تصاویر آپلودشده
        php artisan storage:link >/dev/null 2>&1 || true
        echo "[entrypoint] نقش web: nginx روی پورت ۸۰ بالا می‌آید"
        exec nginx -g 'daemon off;'
        ;;

    app)
        # ۱) صبر برای MySQL — depends_on فقط «ترتیب» را تضمین می‌کند، نه «آمادگی»؛
        #    healthcheck در compose بیشترش را می‌گیرد، این حلقه هم سپر دوم است
        echo "[entrypoint] نقش app: منتظر دیتابیس…"
        until php docker/await-db.php >/dev/null 2>&1; do
            echo "[entrypoint] دیتابیس هنوز آماده نیست — ۲ ثانیه دیگر…"
            sleep 2
        done

        # ۲) همان نصب‌کنندهٔ یک‌مرحله‌ای خود پروژه:
        #    key:generate (اگر نباشد) + migrate + seed و store-safe (همه idempotent)
        php artisan shop:install

        echo "[entrypoint] نقش app: php-fpm روی پورت ۹۰۰۰ بالا می‌آید"
        exec php-fpm -F
        ;;

    *)
        # نقش آزاد: هر دستوری که داده شده، همان‌طور اجرا شود
        exec "$@"
        ;;
esac

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/**
 * نصب یک‌مرحله‌ای فروشگاه
 *
 * استفاده: php artisan shop:install
 */
class ShopInstallCommand extends Command
{
    protected $signature = 'shop:install {--fresh : حذف کامل داده‌ها و نصب از صفر}';

    protected $description = 'نصب و راه‌اندازی کامل فروشگاه در یک مرحله';

    public function handle(): int
    {
        $this->components->info('🛒 نصب فروشگاه دیجی‌شاپ');

        // ۱) فایل .env
        if (! File::exists(base_path('.env'))) {
            File::copy(base_path('.env.example'), base_path('.env'));
            $this->components->info('✓ فایل .env از نمونه ساخته شد.');
        }

        // ۲) کلید برنامه
        if (empty(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
            $this->components->info('✓ کلید امنیتی برنامه ساخته شد.');
        }

        // ۳) دیتابیس sqlite در صورت نیاز
        if (config('database.default') === 'sqlite' && ! File::exists(database_path('database.sqlite'))) {
            File::put(database_path('database.sqlite'), '');
            $this->components->info('✓ فایل دیتابیس SQLite ساخته شد.');
        }

        // ۴) مهاجرت‌ها
        $fresh = $this->option('fresh') ? ['--fresh' => true] : [];
        Artisan::call('migrate', $fresh + ['--force' => true]);
        $this->components->info('✓ جداول دیتابیس ساخته شدند.');

        // ۵) داده‌های اولیه
        Artisan::call('db:seed', ['--force' => true]);
        $this->components->info('✓ دسته‌بندی‌ها و محصولات نمونه درج شدند.');

        // ۶) لینک storage
        if (! file_exists(public_path('storage'))) {
            Artisan::call('storage:link');
            $this->components->info('✓ لینک تصاویر (storage) ساخته شد.');
        }

        // ۷) کش‌ها
        Artisan::call('optimize:clear');
        $this->components->info('✓ کش‌ها پاک شدند.');

        $this->newLine();
        $this->components->info('🎉 نصب کامل شد!');
        $this->line('   • اجرای سرور توسعه:  <fg=cyan>php artisan serve</>');
        $this->line('   • ساخت مدیر پنل:      <fg=cyan>php artisan admin:create</>');
        $this->newLine();

        return self::SUCCESS;
    }
}

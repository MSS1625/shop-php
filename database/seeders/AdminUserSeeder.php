<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * ایجاد مدیر اولیه — بدون رمز پیش‌فرض ناامن!
     *
     * رمز از متغیر محیطی ADMIN_PASSWORD خوانده می‌شود؛
     * اگر تنظیم نشده باشد یک رمز تصادفی قوی ساخته و در خروجی کنسول نمایش داده می‌شود.
     */
    public function run(): void
    {
        $email = config('shop.admin_email', 'admin@digishop.local');
        $password = env('ADMIN_PASSWORD');

        if (User::where('email', $email)->exists()) {
            return;
        }

        if (! $password || strlen($password) < 8) {
            $password = Str::password(16, symbols: false);
            $this->command->warn('رمز تصادفی برای مدیر ساخته شد:');
        } else {
            $this->command->info('مدیر با رمز تنظیم‌شده در فایل .env ایجاد شد:');
        }

        User::create([
            'name' => 'مدیر فروشگاه',
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->table(
            ['آیتم', 'مقدار'],
            [
                ['ایمیل', $email],
                ['رمز عبور', $password],
            ]
        );
        $this->command->warn('⚠ این اطلاعات را در جای امنی ذخیره کنید و رمز را بعد از اولین ورود تغییر دهید.');
    }
}

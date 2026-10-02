<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * ساخت یا بروزرسانی کاربر مدیر — جایگزین امن رمزهای پیش‌فرض
 *
 * استفاده: php artisan admin:create
 */
class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create {--email=} {--name=} {--password=}';

    protected $description = 'ساخت یا بروزرسانی کاربر مدیر پنل';

    public function handle(): int
    {
        $email = $this->option('email') ?: text(
            label: 'ایمیل مدیر:',
            placeholder: 'admin@example.com',
            validate: fn ($v) => filter_var($v, FILTER_VALIDATE_EMAIL)
                ? null
                : 'ایمیل معتبر وارد کنید.',
        );

        $name = $this->option('name') ?: text(
            label: 'نام مدیر:',
            placeholder: 'مدیر فروشگاه',
            default: 'مدیر فروشگاه',
            validate: fn ($v) => mb_strlen($v) >= 2 ? null : 'نام باید حداقل ۲ کاراکتر باشد.',
        );

        $password = $this->option('password') ?: password(
            label: 'رمز عبور (حداقل ۸ کاراکتر):',
            validate: fn ($v) => strlen($v) >= 8 ? null : 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
        );

        if ($this->option('password') && strlen($password) < 8) {
            $this->error('رمز عبور باید حداقل ۸ کاراکتر باشد.');

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->info(($user->wasRecentlyCreated ? 'مدیر جدید ساخته شد: ' : 'مدیر موجود بروزرسانی شد: ').$email);
        $this->info('حالا می‌توانید از '.route('admin.login').' وارد شوید.');

        return self::SUCCESS;
    }
}

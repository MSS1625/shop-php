<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * کد تخفیف نمونه برای نمایش قابلیت
     */
    public function run(): void
    {
        $coupon = Coupon::firstOrCreate(
            ['code' => 'WELCOME10'],
            [
                'type' => Coupon::TYPE_PERCENT,
                'value' => 10,
                'min_order_amount' => 0,
                'usage_limit' => 100,
                'is_active' => true,
            ]
        );

        if ($coupon->wasRecentlyCreated) {
            $this->command?->info('کد تخفیف نمونه ساخته شد: WELCOME10 (۱۰٪ روی هر سبد، ۱۰۰ بار)');
        }
    }
}

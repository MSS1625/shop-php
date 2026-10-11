<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ثبت شفاف تخفیف روی سفارش:
     *   subtotal        جمع اقلام پیش از تخفیف
     *   discount_amount مبلغ تخفیف کد
     *   coupon_code     کد اعمال‌شده (نشانِ تاریخی — بدون FK تا حذف کوپن رکورد سفارش را نشکند)
     *   total           مبلغ نهایی قابل پرداخت = subtotal - discount
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('subtotal')->nullable()->after('note');
            $table->unsignedBigInteger('discount_amount')->default(0)->after('subtotal');
            $table->string('coupon_code', 32)->nullable()->after('discount_amount');
        });

        // سفارش‌های پیش از این قابلیت: جمع اقلام همان مبلغ نهایی است
        DB::table('orders')->whereNull('subtotal')->update(['subtotal' => DB::raw('total')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'discount_amount', 'coupon_code']);
        });
    }
};

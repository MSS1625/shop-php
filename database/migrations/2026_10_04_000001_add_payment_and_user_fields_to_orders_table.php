<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // سفارش می‌تواند متعلق به کاربر ثبت‌شده باشد (خرید مهمان هم مجاز است)
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->nullOnDelete();

            // روش پرداخت: zarinpal = درگاه آنلاین | cod = پرداخت در محل
            $table->string('payment_method', 20)->default('cod')->after('status');

            // وضعیت پرداخت: pending | paid | failed | cancelled
            $table->string('payment_status', 20)->default('pending')->after('payment_method');

            // شناسه یکتای تراکنش درگاه (Authority)
            $table->string('authority', 64)->nullable()->index()->after('payment_status');

            // شماره پیگیری بانکی پس از پرداخت موفق
            $table->unsignedBigInteger('ref_id')->nullable()->after('authority');

            $table->timestamp('paid_at')->nullable()->after('ref_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'payment_method',
                'payment_status',
                'authority',
                'ref_id',
                'paid_at',
            ]);
        });
    }
};

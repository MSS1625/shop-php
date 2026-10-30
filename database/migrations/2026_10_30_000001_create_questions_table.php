<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // متن پرسش مشتری
            $table->text('body');
            // پاسخ فروشگاه — تا زمان پاسخ مدیر خالی می‌ماند
            $table->text('answer')->nullable();
            // جریان پاسخ: pending → answered | rejected
            $table->string('status', 16)->default('pending');
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            // برخلاف نظرات، چند پرسش برای هر کاربر/محصول مجاز است
            $table->index(['status', 'created_at']);
            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};

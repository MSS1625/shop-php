<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // امتیاز ۱ تا ۵ ستاره
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            // جریان بررسی: pending → approved | rejected
            $table->string('status', 16)->default('pending');
            $table->timestamps();

            // هر کاربر برای هر محصول فقط یک نظر
            $table->unique(['product_id', 'user_id']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

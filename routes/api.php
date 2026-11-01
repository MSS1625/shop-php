<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\QuestionController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API نسخهٔ ۱ — آمادهٔ اپلیکیشن موبایل (توکن Sanctum)
|--------------------------------------------------------------------------
| احراز هویت با Personal Access Token است (هدر Authorization: Bearer)؛
| سشن و کوکی وب اینجا نقشی ندارد. خطاهای اعتبارسنجی همان ۴۲۲ استاندارد
| لاراول هستند و صفحه‌بندی‌ها با شکل {data, links, meta} برمی‌گردند.
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    /*
    |----------------------------------------------------------------------
    | احراز هویت — عمومی
    |----------------------------------------------------------------------
    */

    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('register', [AuthController::class, 'register'])
            ->middleware('throttle:10,1')
            ->name('register');

        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:10,1')
            ->name('login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', [AuthController::class, 'me'])->name('me');
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        });
    });

    /*
    |----------------------------------------------------------------------
    | فروشگاه — خواندن لیست‌ها (عمومی)
    |----------------------------------------------------------------------
    */

    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');

    Route::get('products', [ProductController::class, 'index'])->name('products.index');

    // فقط حروف کوچک و خط تیره — جلوگیری از هر نوع تزریق مسیر (همان قاعدهٔ وب)
    Route::get('products/{product:slug}', [ProductController::class, 'show'])
        ->name('products.show')
        ->where('product', '[a-z0-9-]+');

    Route::get('products/{product:slug}/reviews', [ReviewController::class, 'index'])
        ->name('products.reviews.index')
        ->where('product', '[a-z0-9-]+');

    Route::get('products/{product:slug}/questions', [QuestionController::class, 'index'])
        ->name('products.questions.index')
        ->where('product', '[a-z0-9-]+');

    /*
    |----------------------------------------------------------------------
    | ثبت نظر و پرسش — فقط با توکن
    |----------------------------------------------------------------------
    */

    Route::middleware(['auth:sanctum', 'throttle:5,1'])->group(function () {
        Route::post('products/{product:slug}/reviews', [ReviewController::class, 'store'])
            ->name('products.reviews.store')
            ->where('product', '[a-z0-9-]+');

        Route::post('products/{product:slug}/questions', [QuestionController::class, 'store'])
            ->name('products.questions.store')
            ->where('product', '[a-z0-9-]+');
    });

    /*
    |----------------------------------------------------------------------
    | علاقه‌مندی‌ها — فقط با توکن
    |----------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::post('/{product:slug}', [WishlistController::class, 'store'])
            ->name('store')
            ->where('product', '[a-z0-9-]+');
        Route::delete('/{product:slug}', [WishlistController::class, 'destroy'])
            ->name('destroy')
            ->where('product', '[a-z0-9-]+');
    });

    /*
    |----------------------------------------------------------------------
    | سفارش‌ها — لیست، ثبت مستقیم، جزئیات و لغو (فقط با توکن)
    |----------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::get('/{order:order_number}', [OrderController::class, 'show'])
            ->name('show')
            ->where('order', '[A-Za-z0-9-]+');
        Route::post('/{order:order_number}/cancel', [OrderController::class, 'cancel'])
            ->name('cancel')
            ->where('order', '[A-Za-z0-9-]+');
    });
});

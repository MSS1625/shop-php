<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| فروشگاه — بخش عمومی
|--------------------------------------------------------------------------
*/

Route::name('shop.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // محصولات با جستجو / فیلتر / مرتب‌سازی
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product:slug}', [ProductController::class, 'show'])
        ->name('products.show')
        // فقط حروف کوچک و خط تیره — جلوگیری از هر نوع تزریق مسیر
        ->where('product', '[a-z0-9-]+');
});

// سبد خرید
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add/{product:slug}', [CartController::class, 'store'])
        ->name('store')
        ->where('product', '[a-z0-9-]+');
    Route::post('/update/{product:slug}', [CartController::class, 'update'])
        ->name('update')
        ->where('product', '[a-z0-9-]+');
    Route::post('/remove/{product:slug}', [CartController::class, 'destroy'])
        ->name('destroy')
        ->where('product', '[a-z0-9-]+');
});

// تکمیل خرید
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/', [CheckoutController::class, 'store'])->name('store');
});

Route::get('/order/{order:order_number}', [CheckoutController::class, 'success'])
    ->name('order.success');

/*
|--------------------------------------------------------------------------
| ورود مدیر
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminLoginController::class, 'showLogin'])->name('login');
    Route::post('login', [AdminLoginController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login.attempt');
});

/*
|--------------------------------------------------------------------------
| پنل مدیریت — فقط مدیران
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::post('logout', [AdminLoginController::class, 'logout'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // مدیریت محصولات
        Route::resource('products', AdminProductController::class)
            ->except(['show'])
            ->names('products');

        // مدیریت دسته‌بندی‌ها
        Route::resource('categories', AdminCategoryController::class)
            ->except(['show'])
            ->names('categories');

        // مدیریت سفارش‌ها
        Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])
            ->name('orders.status');
    });

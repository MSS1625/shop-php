<?php

namespace App\Providers;

use App\Services\Cart;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // سبد خرید به‌صورت singleton تا در طول هر درخوست یک نمونه باشد
        $this->app->singleton(Cart::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // استایل صفحه‌بندی با بوت‌استرپ ۵
        Paginator::useBootstrapFive();

        // شمارنده سبد خرید در همه ویوهای لایوت فروشگاه
        View::composer('layouts.shop', function (\Illuminate\View\View $view) {
            $view->with('cartCount', app(Cart::class)->count());
        });
    }
}

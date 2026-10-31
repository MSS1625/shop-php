<?php

namespace App\Providers;

use App\Services\Cart;
use App\Services\Wishlist;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // سبد خرید و علاقه‌مندی‌ها به‌صورت singleton تا در طول هر درخواست یک نمونه باشند
        $this->app->singleton(Cart::class);
        $this->app->singleton(Wishlist::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // استایل صفحه‌بندی با بوت‌استرپ ۵
        Paginator::useBootstrapFive();

        // محدودیت نرخ گروه api — ۶۰ درخواست در دقیقه،
        // به تفکیک کاربر لاگین‌شده یا IP (مهمان)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });

        // شمارنده سبد خرید و علاقه‌مندی‌ها در همه ویوهای لایوت فروشگاه
        View::composer('layouts.shop', function (\Illuminate\View\View $view) {
            $view->with('cartCount', app(Cart::class)->count())
                ->with('wishlistCount', app(Wishlist::class)->count());
        });
    }
}

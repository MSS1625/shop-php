<?php

use App\Exceptions\StockUnavailableException;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // هدرهای امنیتی برای همه پاسخ‌ها
        $middleware->append(SecurityHeaders::class);

        // نام مستعار برای میدل‌ور بررسی مدیر
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);

        // ریدایرکت مهمان‌ها به صفحه ورود مدیر
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // خطای کمبود موجودی انبار هنگام ثبت سفارش
        $exceptions->render(function (StockUnavailableException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 409);
            }

            return redirect()
                ->route('cart.index')
                ->with('error', $e->getMessage());
        });
    })->create();

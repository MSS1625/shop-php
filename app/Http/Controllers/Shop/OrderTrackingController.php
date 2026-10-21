<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderTrackingRequest;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * پیگیری سفارش بدون حساب کاربری —
 * مشتری با «شماره سفارش + شماره موبایلی که هنگام خرید وارد کرده» سفارشش را می‌بیند
 */
class OrderTrackingController extends Controller
{
    /** مدت اعتبار نتیجهٔ پیگیری در سشن — ۳۰ دقیقه */
    private const SESSION_TTL = 1800;

    /**
     * فرم پیگیری + نتیجهٔ پیگیری قبلی (الگوی Post/Redirect/Get)
     */
    public function form(): View
    {
        return view('shop.track', [
            'order' => $this->trackedOrder(),
        ]);
    }

    /**
     * جستجوی سفارش — هر دو فیلد باید همزمان بخوانند
     */
    public function lookup(OrderTrackingRequest $request): RedirectResponse
    {
        $order = Order::query()
            ->where('order_number', $request->normalizedOrderNumber())
            ->where('customer_phone', $request->normalizedPhone())
            ->first();

        if (! $order) {
            // پیام یکسان برای «شماره سفارش غلط» و «موبایل غلط» —
            // هیچ سرنخی دربارهٔ اینکه کدام فیلد درست بود لو نمی‌رود (ضد enumeration)
            // (ریدایرکت صریح به‌جای back تا مستقل از referer همیشه درست کار کند)
            return redirect()
                ->route('shop.track')
                ->withInput()
                ->with('track_error', 'سفارشی با این شماره سفارش و شماره موبایل پیدا نشد. اطلاعات را بررسی کنید.');
        }

        $this->rememberTrackedOrder($request, $order);

        return redirect()->route('shop.track');
    }

    /**
     * ثبت نتیجه در سشن تا رفرش صفحه فرم را دوباره ارسال نکند +
     * دسترسی به «تلاش مجدد پرداخت» با همان مکانیزم viewed_orders تسویه‌حساب
     */
    private function rememberTrackedOrder(Request $request, Order $order): void
    {
        $request->session()->put('tracked_order_id', $order->id);
        $request->session()->put('tracked_at', now()->timestamp);

        $viewed = $request->session()->get('viewed_orders', []);
        $viewed[] = $order->order_number;
        $request->session()->put('viewed_orders', array_slice($viewed, -10));
    }

    /**
     * سفارش پیگیری‌شدهٔ معتبر از سشن — یا null
     */
    private function trackedOrder(): ?Order
    {
        $id = session('tracked_order_id');
        $at = session('tracked_at');

        if ($id === null || ! $at || now()->timestamp - $at > self::SESSION_TTL) {
            return null;
        }

        return Order::query()->with('items.product')->find($id);
    }
}

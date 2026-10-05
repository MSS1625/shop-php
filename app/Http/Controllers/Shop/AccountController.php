<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * داشبورد حساب کاربری — خلاصه وضعیت + آخرین سفارش‌ها
     */
    public function index(): View
    {
        $user = auth()->user();

        $orders = $user->orders()
            ->withCount('items')
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'total_orders' => $user->orders()->count(),
            'active_orders' => $user->orders()
                ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING, Order::STATUS_SHIPPED])
                ->count(),
            'total_spent' => (int) $user->orders()
                ->whereNot('status', Order::STATUS_CANCELLED)
                ->sum('total'),
        ];

        return view('shop.account.dashboard', [
            'user' => $user,
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }

    /**
     * تاریخچه کامل سفارش‌ها
     */
    public function orders(): View
    {
        $orders = auth()->user()->orders()
            ->withCount('items')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('shop.account.orders', compact('orders'));
    }

    /**
     * جزئیات یک سفارش (فقط مالک یا مدیر)
     */
    public function showOrder(Order $order): View
    {
        $this->authorizeOrder($order);

        $order->load(['items.product']);

        return view('shop.account.order-detail', compact('order'));
    }

    /**
     * لغو سفارش توسط مشتری — فقط سفارش‌های «در انتظار بررسی» و پرداخت‌نشده
     */
    public function cancelOrder(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

        abort_unless(
            $order->status === Order::STATUS_PENDING && ! $order->isPaid(),
            403
        );

        if ($order->cancelWithRestock()) {
            return back()->with('success', 'سفارش لغو شد و موجودی محصولات به انبار بازگشت.');
        }

        return back()->with('error', 'امکان لغو این سفارش وجود ندارد.');
    }

    /**
     * اطمینان از مالکیت سفارش
     */
    private function authorizeOrder(Order $order): void
    {
        abort_unless(
            $order->user_id !== null && $order->user_id === auth()->id(),
            403
        );
    }
}

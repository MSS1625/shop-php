<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * لیست سفارش‌ها با فیلتر وضعیت
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(Order::STATUSES)],
        ]);

        $orders = Order::withCount('items')
            ->status($validated['status'] ?? null)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statusCounts = collect(Order::STATUSES)
            ->mapWithKeys(fn ($s) => [$s => Order::where('status', $s)->count()]);

        return view('admin.orders.index', [
            'orders' => $orders,
            'statusCounts' => $statusCounts,
            'activeStatus' => $validated['status'] ?? null,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load('items.product');

        return view('admin.orders.show', compact('order'));
    }

    /**
     * تغییر وضعیت سفارش — با بازگشت موجودی در صورت لغو
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
        ], [
            'status.required' => 'وضعیت جدید را انتخاب کنید.',
            'status.in' => 'وضعیت انتخاب‌شده معتبر نیست.',
        ]);

        $newStatus = $validated['status'];

        if ($order->status === $newStatus) {
            return redirect()->route('admin.orders.show', $order);
        }

        // اگر سفارش لغو می‌شود، موجودی محصولات برمی‌گردد و وضعیت پرداخت ثبت می‌شود (اتمیک)
        if ($newStatus === Order::STATUS_CANCELLED && ! $order->isCancelled()) {
            $order->cancelWithRestock();
        }

        // اگر سفارش از حالت لغو خارج شد، موجودی دوباره کسر و وضعیت پرداخت ریست می‌شود
        if ($order->isCancelled() && $newStatus !== Order::STATUS_CANCELLED) {
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->decrement('stock', $item->quantity);
                }
            }

            $order->update([
                'status' => $newStatus,
                'payment_status' => $order->isOnlinePayment() && ! $order->isPaid()
                    ? Order::PAYMENT_PENDING
                    : $order->payment_status,
            ]);
        } elseif (! $order->isCancelled()) {
            $order->update(['status' => $newStatus]);
        }

        // رفرش برای نمایش پیام درست
        $order->refresh();

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'وضعیت سفارش به «'.$order->statusLabel().'» تغییر یافت.');
    }
}

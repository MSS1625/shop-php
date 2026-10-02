<?php

namespace App\Http\Controllers\Shop;

use App\Exceptions\StockUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\Cart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(protected Cart $cart) {}

    /**
     * صفحه تکمیل خرید
     */
    public function index(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'سبد خرید شما خالی است.');
        }

        return view('shop.checkout', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    /**
     * ثبت نهایی سفارش — تراکنش اتمیک + کاهش موجودی انبار
     */
    public function store(CheckoutRequest $request): RedirectResponse
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'سبد خرید شما خالی است.');
        }

        $order = DB::transaction(function () use ($request, $items) {
            // اعتبارسنجی نهایی موجودی انبار قبل از ثبت
            foreach ($items as $item) {
                $fresh = $item['product']->fresh();

                if (! $fresh || ! $fresh->is_active || $fresh->stock < $item['quantity']) {
                    throw new StockUnavailableException(
                        'موجودی «'.$item['product']->title.'» کافی نیست. سبد خود را بروزرسانی کنید.'
                    );
                }
            }

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_name' => trim($request->validated('customer_name')),
                'customer_phone' => $request->validated('customer_phone'),
                'customer_address' => trim($request->validated('customer_address')),
                'note' => $request->validated('note'),
                'total' => $items->sum('subtotal'),
                'status' => Order::STATUS_PENDING,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'product_title' => $item['product']->title,
                    'unit_price' => $item['product']->price,
                    'quantity' => $item['quantity'],
                ]);

                // کاهش موجودی انبار
                $item['product']->decrement('stock', $item['quantity']);
            }

            return $order;
        });

        $this->cart->clear();

        // ثبت شماره سفارش در سشن تا صفحه موفقیت فقط برای همین کاربر قابل مشاهده باشد
        $viewed = session('viewed_orders', []);
        $viewed[] = $order->order_number;
        session(['viewed_orders' => array_slice($viewed, -10)]);

        return redirect()
            ->route('order.success', $order->order_number)
            ->with('success', 'سفارش شما با موفقیت ثبت شد.');
    }

    /**
     * صفحه موفقیت سفارش
     */
    public function success(Order $order): View
    {
        // فقط سفارش‌های بدون احراز هویت از طریق سشن فعلی قابل مشاهده‌اند
        $viewedOrders = session('viewed_orders', []);

        abort_unless(
            in_array($order->order_number, $viewedOrders) ||
            (auth()->check() && auth()->user()->isAdmin()),
            404
        );

        $order->load('items');

        return view('shop.order-success', compact('order'));
    }
}

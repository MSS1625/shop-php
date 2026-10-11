<?php

namespace App\Http\Controllers\Shop;

use App\Exceptions\StockUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Coupon;
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
            'coupon' => $this->cart->coupon(),
            'discount' => $this->cart->discount(),
            'payable' => $this->cart->payable(),
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

        $subtotal = $items->sum('subtotal');

        // کد تخفیف سبد پیش از ثبت سفارش یک بار شفاف اعتبارسنجی می‌شود —
        // اگر کاربر سبد را بعد از اعمال تغییر داده باشد، به سبد برمی‌گردد
        $sessionCoupon = $this->cart->coupon();

        if ($sessionCoupon && ($reason = $sessionCoupon->invalidReason($subtotal)) !== null) {
            return redirect()->route('cart.index')
                ->with('error', 'کد تخفیف «'.$sessionCoupon->code.'» دیگر برای این سبد معتبر نیست: '.$reason);
        }

        $droppedCouponCode = null;

        $order = DB::transaction(function () use ($request, $items, $subtotal, $sessionCoupon, &$droppedCouponCode) {
            // اعتبارسنجی نهایی موجودی انبار قبل از ثبت
            foreach ($items as $item) {
                $fresh = $item['product']->fresh();

                if (! $fresh || ! $fresh->is_active || $fresh->stock < $item['quantity']) {
                    throw new StockUnavailableException(
                        'موجودی «'.$item['product']->title.'» کافی نیست. سبد خود را بروزرسانی کنید.'
                    );
                }
            }

            // کوپن دوباره با قفل ردیف اعتبارسنجی می‌شود (ضد رقابت روی سقف مصرف)
            $discount = 0;
            $couponCode = null;

            if ($sessionCoupon) {
                $locked = Coupon::whereKey($sessionCoupon->getKey())->lockForUpdate()->first();

                if ($locked && $locked->isValidFor($subtotal)) {
                    $discount = $locked->discountFor($subtotal);
                    $couponCode = $locked->code;
                    $locked->increment('used_count');
                } else {
                    // پنجره میلی‌ثانیه‌ای بین دو اعتبارسنجی — سفارش بدون تخفیف ثبت می‌شود
                    $droppedCouponCode = $sessionCoupon->code;
                }
            }

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => auth()->id(),
                'customer_name' => trim($request->validated('customer_name')),
                'customer_phone' => $request->validated('customer_phone'),
                'customer_address' => trim($request->validated('customer_address')),
                'note' => $request->validated('note'),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'coupon_code' => $couponCode,
                'total' => max(0, $subtotal - $discount),
                'status' => Order::STATUS_PENDING,
                'payment_method' => $request->paymentMethod(),
                'payment_status' => Order::PAYMENT_PENDING,
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

        if ($droppedCouponCode !== null) {
            session()->flash(
                'warning',
                'کد تخفیف «'.$droppedCouponCode.'» در لحظه پایانی معتبر نبود و سفارش بدون تخفیف ثبت شد.'
            );
        }

        // ثبت شماره سفارش در سشن تا صفحه موفقیت فقط برای همین کاربر قابل مشاهده باشد
        $viewed = session('viewed_orders', []);
        $viewed[] = $order->order_number;
        session(['viewed_orders' => array_slice($viewed, -10)]);

        // پرداخت آنلاین → هدایت به درگاه
        if ($order->isOnlinePayment()) {
            return redirect()->route('payment.start', $order->order_number);
        }

        return redirect()
            ->route('order.success', $order->order_number)
            ->with('success', 'سفارش شما با موفقیت ثبت شد.');
    }

    /**
     * صفحه موفقیت سفارش
     */
    public function success(Order $order): View
    {
        // فقط سفارش‌های همین سشن / مالک آن / مدیر قابل مشاهده‌اند
        abort_unless($order->isAccessibleByCurrentUser(), 404);

        $order->load('items');

        return view('shop.order-success', compact('order'));
    }
}

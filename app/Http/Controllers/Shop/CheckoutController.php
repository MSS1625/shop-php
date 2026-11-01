<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\Cart;
use App\Services\PlaceOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    public function __construct(
        protected Cart $cart,
        protected PlaceOrder $placeOrder,
    ) {}

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

        // ثبت سفارش — منطق مشترک وب و API (PlaceOrder)
        $result = $this->placeOrder->handle($items, [
            'user_id' => auth()->id(),
            'customer_name' => trim($request->validated('customer_name')),
            'customer_phone' => $request->validated('customer_phone'),
            'customer_email' => strtolower(trim((string) $request->validated('customer_email'))) ?: null,
            'customer_address' => trim($request->validated('customer_address')),
            'note' => $request->validated('note'),
            'payment_method' => $request->paymentMethod(),
        ], $sessionCoupon);

        $order = $result['order'];

        $this->cart->clear();

        if ($result['droppedCouponCode'] !== null) {
            session()->flash(
                'warning',
                'کد تخفیف «'.$result['droppedCouponCode'].'» در لحظه پایانی معتبر نبود و سفارش بدون تخفیف ثبت شد.'
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

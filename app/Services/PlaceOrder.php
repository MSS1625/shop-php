<?php

namespace App\Services;

use App\Contracts\OrderNotifier;
use App\Exceptions\StockUnavailableException;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * ثبت نهایی سفارش — منطق مشترک فرم خرید وب و API.
 *
 * همهٔ قواعد ثبت سفارش (اعتبارسنجی نهایی موجودی، اعتبارسنجی دوبارهٔ
 * کوپن با قفل ردیف، اسنپ‌شات عنوان و قیمت، کاهش انبار و اطلاع‌رسانی)
 * یک‌جا و برای هر دو واسط یکسان اجرا می‌شود — «یک قانون، همه جا»:
 * چه از مرورگر سفارش ثبت شود چه از اپلیکیشن موبایل، نتیجه یکی است.
 */
class PlaceOrder
{
    public function __construct(
        protected OrderNotifier $notifier,
    ) {}

    /**
     * ثبت سفارش به‌صورت اتمیک + اطلاع‌رسانی خارج از تراکنش
     *
     * @param  Collection<int, array{product: Product, quantity: int, subtotal: int}>  $items
     * @param  array{user_id: ?int, customer_name: string, customer_phone: string, customer_email: ?string, customer_address: string, note: ?string, payment_method: string}  $attributes
     * @param  Coupon|null  $coupon  کوپنِ از قبل اعتبارسنجی‌شده در سطح واسط
     * @return array{order: Order, droppedCouponCode: string|null}
     *
     * @throws StockUnavailableException
     */
    public function handle(Collection $items, array $attributes, ?Coupon $coupon): array
    {
        $subtotal = $items->sum('subtotal');

        $droppedCouponCode = null;

        $order = DB::transaction(function () use ($items, $attributes, $coupon, $subtotal, &$droppedCouponCode) {
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

            if ($coupon) {
                $locked = Coupon::whereKey($coupon->getKey())->lockForUpdate()->first();

                if ($locked && $locked->isValidFor($subtotal)) {
                    $discount = $locked->discountFor($subtotal);
                    $couponCode = $locked->code;
                    $locked->increment('used_count');
                } else {
                    // پنجره میلی‌ثانیه‌ای بین دو اعتبارسنجی — سفارش بدون تخفیف ثبت می‌شود
                    $droppedCouponCode = $coupon->code;
                }
            }

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $attributes['user_id'],
                'customer_name' => $attributes['customer_name'],
                'customer_phone' => $attributes['customer_phone'],
                'customer_email' => $attributes['customer_email'],
                'customer_address' => $attributes['customer_address'],
                'note' => $attributes['note'],
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'coupon_code' => $couponCode,
                'total' => max(0, $subtotal - $discount),
                'status' => Order::STATUS_PENDING,
                'payment_method' => $attributes['payment_method'],
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

        // اطلاع‌رسانی ثبت سفارش — خارج از تراکنش و هرگز مانع ثبت نمی‌شود
        $this->notifier->orderPlaced($order);

        return ['order' => $order, 'droppedCouponCode' => $droppedCouponCode];
    }
}

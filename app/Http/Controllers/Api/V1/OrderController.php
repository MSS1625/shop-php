<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Services\PlaceOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * سفارش‌های کاربر — جدیدترین اول، با تعداد اقلام هر سفارش
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()
            ->orders()
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return OrderResource::collection($orders);
    }

    /**
     * ثبت سفارش مستقیم — اپ موبایل سبد را با خودش دارد؛ اقلام،
     * اطلاعات مشتری، روش پرداخت و کد تخفیف همه در همین درخواست می‌آیند.
     *
     * قواعد دقیقاً همان فرم خرید وب است (سرویس PlaceOrder):
     * اعتبارسنجی نهایی موجودی، قفل کوپن، اسنپ‌شات قیمت و اطلاع‌رسانی.
     */
    public function store(ApiOrderRequest $request, PlaceOrder $placeOrder): JsonResponse
    {
        // اقلام درخواست → مجموعهٔ [product, quantity, subtotal] مثل items() سبد
        // (اسلاگ تکراری ادغام می‌شود — همان رفتار سبد id => quantity)
        $quantities = [];

        foreach ($request->validated('items') as $item) {
            $slug = $item['slug'];
            $quantities[$slug] = ($quantities[$slug] ?? 0) + (int) $item['quantity'];
        }

        $products = Product::query()
            ->where('is_active', true)
            ->whereIn('slug', array_keys($quantities))
            ->get()
            ->keyBy('slug');

        $items = collect($quantities)
            ->map(fn (int $quantity, string $slug) => $products->has($slug) ? [
                'product' => $products[$slug],
                'quantity' => $quantity,
                'subtotal' => $products[$slug]->price * $quantity,
            ] : null)
            ->filter()
            ->values();

        // بین اعتبارسنجی فرم و ثبت نهایی، محصولی حذف/غیرفعال شده باشد
        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => ['یکی از محصولات سفارش یافت نشد یا فعال نیست.'],
            ]);
        }

        // کد تخفیف پیش از ثبت سفارش یک بار شفاف اعتبارسنجی می‌شود —
        // همان قاعدهٔ سبد وب: نامعتبر بودن یعنی برگشت به کاربر، نه ثبت بی‌تخفیف
        $couponCode = trim((string) $request->validated('coupon_code', ''));
        $coupon = null;

        if ($couponCode !== '') {
            $coupon = Coupon::findByCode($couponCode);

            if (! $coupon) {
                throw ValidationException::withMessages([
                    'coupon_code' => ['کد تخفیف یافت نشد.'],
                ]);
            }

            if (($reason = $coupon->invalidReason($items->sum('subtotal'))) !== null) {
                throw ValidationException::withMessages([
                    'coupon_code' => [$reason],
                ]);
            }
        }

        $result = $placeOrder->handle($items, [
            'user_id' => $request->user()->getAuthIdentifier(),
            'customer_name' => trim($request->validated('customer_name')),
            'customer_phone' => $request->validated('customer_phone'),
            'customer_email' => strtolower(trim((string) $request->validated('customer_email'))) ?: null,
            'customer_address' => trim($request->validated('customer_address')),
            'note' => $request->validated('note'),
            'payment_method' => $request->paymentMethod(),
        ], $coupon);

        $order = $result['order']->load('items');

        return response()->json([
            'message' => 'سفارش شما با موفقیت ثبت شد.',
            'data' => new OrderResource($order),
            'dropped_coupon' => $result['droppedCouponCode'],
        ], 201);
    }

    /**
     * جزئیات سفارش — فقط سفارش‌های خودِ کاربرِ توکن
     */
    public function show(Request $request, Order $order): OrderResource
    {
        abort_unless($order->user_id === $request->user()->getAuthIdentifier(), 404);

        $order->load('items');

        return new OrderResource($order);
    }

    /**
     * لغو سفارش — فقط سفارش‌های «در انتظار بررسی» و پرداخت‌نشده؛
     * موجودی به‌صورت اتمیک به انبار برمی‌گردد (همان قاعدهٔ وب).
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->getAuthIdentifier(), 404);

        abort_unless(
            $order->status === Order::STATUS_PENDING && ! $order->isPaid(),
            403
        );

        if (! $order->cancelWithRestock()) {
            return response()->json([
                'message' => 'امکان لغو این سفارش وجود ندارد.',
            ], 409);
        }

        return response()->json([
            'message' => 'سفارش لغو شد و موجودی محصولات به انبار بازگشت.',
        ]);
    }
}

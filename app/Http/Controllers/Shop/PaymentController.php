<?php

namespace App\Http\Controllers\Shop;

use App\Contracts\PaymentGateway;
use App\Exceptions\PaymentGatewayException;
use App\Exceptions\PaymentVerificationFailedException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentGateway $gateway) {}

    /**
     * هدایت به درگاه پرداخت — هم برای پرداخت اولیه و هم تلاش مجدد
     */
    public function start(Order $order): RedirectResponse
    {
        abort_unless($order->isAccessibleByCurrentUser(), 404);

        if ($order->isPaid()) {
            return redirect()->route('order.success', $order->order_number);
        }

        abort_unless($order->isOnlinePayment(), 404);
        abort_if($order->isCancelled(), 404);

        try {
            // نتیجه تلاش قبلی ریست می‌شود و درخواست جدید ثبت می‌گردد
            $order->forceFill(['payment_status' => Order::PAYMENT_PENDING])->save();

            return redirect()->away($this->gateway->request($order));
        } catch (PaymentGatewayException $e) {
            return redirect()
                ->route('order.success', $order->order_number)
                ->with('error', 'اتصال به درگاه پرداخت برقرار نشد: '.$e->getMessage());
        }
    }

    /**
     * بازگشت از درگاه زرین‌پال (Authority + Status در کوئری‌استرینگ)
     */
    public function callback(Request $request): RedirectResponse
    {
        $authority = (string) $request->query('Authority', $request->query('authority', ''));
        $status = strtoupper((string) $request->query('Status', $request->query('status', '')));

        /** @var Order|null $order */
        $order = Order::where('authority', $authority)->first();

        if (! $order) {
            return redirect()
                ->route('shop.home')
                ->with('error', 'تراکنش موردنظر یافت نشد.');
        }

        // کاربر در درگاه پرداخت را لغو کرده است
        if ($status !== 'OK') {
            $order->forceFill(['payment_status' => Order::PAYMENT_CANCELLED])->save();

            return redirect()
                ->route('order.success', $order->order_number)
                ->with('warning', 'پرداخت لغو شد. هر زمان که خواستید می‌توانید دوباره تلاش کنید.');
        }

        // تایید نهایی همیشه سمت سرور با درگاه انجام می‌شود
        try {
            $refId = $this->gateway->verify($order);
        } catch (PaymentVerificationFailedException $e) {
            $order->forceFill(['payment_status' => Order::PAYMENT_FAILED])->save();

            return redirect()
                ->route('order.success', $order->order_number)
                ->with('error', $e->getMessage());
        } catch (PaymentGatewayException $e) {
            // خطای موقت ارتباط — وضعیت مالی دست نمی‌خورد تا با پشتیبانی پیگیری شود
            return redirect()
                ->route('order.success', $order->order_number)
                ->with('error', 'نتیجه پرداخت در حال حاضر قابل استعلام نیست. '
                    .'اگر مبلغ از حساب شما کسر شده، با پشتیبانی تماس بگیرید. ('.$e->getMessage().')');
        }

        if ($order->isPaid()) {
            // قبلا تایید شده — بازگشت دوباره به callback
            return redirect()->route('order.success', $order->order_number);
        }

        $order->markAsPaid($refId);

        return redirect()
            ->route('order.success', $order->order_number)
            ->with('success', 'پرداخت با موفقیت انجام شد. شماره پیگیری: '.fa_num($refId));
    }

    /**
     * صفحه درگاه آزمایشی (فقط حالت mock)
     */
    public function mockShow(Order $order): object
    {
        abort_unless(config('zarinpal.mode') === 'mock', 404);

        $authority = (string) request('authority', '');

        abort_unless($order->authority === $authority && $authority !== '', 404);
        abort_if($order->isPaid() || $order->isCancelled(), 404);

        return view('shop.payment.mock', [
            'order' => $order->load('items'),
            'gatewayName' => $this->gateway->name(),
        ]);
    }

    /**
     * شبیه‌سازی نتیجه درگاه آزمایشی و هدایت به callback استاندارد
     */
    public function mockResult(Request $request, Order $order): RedirectResponse
    {
        abort_unless(config('zarinpal.mode') === 'mock', 404);

        $validated = $request->validate([
            'result' => ['required', 'in:success,cancel'],
            'authority' => ['required', 'string'],
        ]);

        abort_unless($order->authority === $validated['authority'], 404);

        // دقیقاً همان فرمتی که زرین‌پال واقعی برمی‌گرداند
        return redirect()->route('payment.callback', [
            'Authority' => $order->authority,
            'Status' => $validated['result'] === 'success' ? 'OK' : 'NOK',
        ]);
    }
}

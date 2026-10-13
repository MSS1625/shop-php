<div dir="rtl" style="font-family:Tahoma,'Segoe UI',Arial,sans-serif;background:#f4f6f9;padding:24px 12px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(15,35,70,.08);">
        <!-- هدر -->
        <tr>
            <td style="background:linear-gradient(135deg,#0e2242,#1a3a6b);padding:28px 32px;text-align:center;">
                <div style="font-size:26px;font-weight:bold;color:#ffffff;letter-spacing:.5px;">دیجی‌شاپ</div>
                <div style="font-size:13px;color:#a9c3e8;margin-top:4px;">فروشگاه آنلاین تجهیزات شبکه</div>
            </td>
        </tr>

        <!-- بدنه -->
        <tr>
            <td style="padding:32px;">
                <h2 style="margin:0 0 8px;font-size:19px;color:#0e2242;">سفارش شما ثبت شد ✅</h2>
                <p style="margin:0 0 4px;font-size:14px;color:#44536b;line-height:2;">
                    {{ $order->customer_name }} عزیز، سفارش شما با موفقیت ثبت شد و پس از بررسی پردازش می‌شود.
                </p>
                <p style="margin:0 0 20px;font-size:14px;color:#44536b;line-height:2;">
                    شماره سفارش: <strong style="color:#0e2242;">{{ $order->order_number }}</strong>
                </p>

                @include('emails.partials.order-summary')

                @if($order->isOnlinePayment() && !$order->isPaid())
                    <div style="margin-top:20px;padding:14px 18px;background:#fff8e6;border:1px solid #f0d48a;border-radius:8px;font-size:13px;color:#7a5b12;line-height:1.9;">
                        این سفارش با پرداخت آنلاین ثبت شده است؛ تا پایان پرداخت، وضعیت مالی آن «در انتظار پرداخت» می‌ماند.
                    </div>
                @endif
            </td>
        </tr>

        <!-- فوتر -->
        <tr>
            <td style="padding:18px 32px;background:#f4f6f9;border-top:1px solid #e6ebf2;font-size:12px;color:#8493ab;text-align:center;line-height:2;">
                این ایمیل به‌صورت خودکار ارسال شده است — فروشگاه دیجی‌شاپ
            </td>
        </tr>
    </table>
</div>

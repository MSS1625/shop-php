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
                <h2 style="margin:0 0 8px;font-size:19px;color:#0e2242;">پرداخت شما تأیید شد 💳</h2>
                <p style="margin:0 0 4px;font-size:14px;color:#44536b;line-height:2;">
                    {{ $order->customer_name }} عزیز، پرداخت سفارش شما با موفقیت انجام و تأیید شد.
                </p>
                <p style="margin:0 0 20px;font-size:14px;color:#44536b;line-height:2;">
                    شماره سفارش: <strong style="color:#0e2242;">{{ $order->order_number }}</strong>
                    @if($order->ref_id)
                        &nbsp;|&nbsp; شماره پیگیری بانکی: <strong style="color:#0e2242;">{{ fa_num($order->ref_id) }}</strong>
                    @endif
                </p>

                @include('emails.partials.order-summary')

                <div style="margin-top:20px;padding:14px 18px;background:#e8f6ee;border:1px solid #9fd8b6;border-radius:8px;font-size:13px;color:#1d6b40;line-height:1.9;">
                    وضعیت مالی سفارش: <strong>پرداخت شده</strong> — سفارش شما پس از بررسی ارسال خواهد شد.
                </div>
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

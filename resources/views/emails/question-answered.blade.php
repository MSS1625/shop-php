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
                <h2 style="margin:0 0 8px;font-size:19px;color:#0e2242;">پرسش شما پاسخ داده شد ❓➡️✅</h2>
                <p style="margin:0 0 20px;font-size:14px;color:#44536b;line-height:2;">
                    {{ $question->user?->name ?? 'مشتری' }} عزیز، پرسش شما دربارهٔ
                    <strong style="color:#0e2242;">{{ $question->product->title }}</strong>
                    توسط کارشناسان دیجی‌شاپ پاسخ داده شد.
                </p>

                <div style="border-right:3px solid #1a3a6b;background:#f4f6f9;border-radius:8px;padding:14px 18px;margin:0 0 14px;">
                    <div style="font-size:12px;color:#8493ab;margin-bottom:6px;">پرسش شما</div>
                    <div style="font-size:14px;color:#0e2242;line-height:2;">{{ $question->body }}</div>
                </div>

                <div style="border-right:3px solid #34d399;background:#eafaf4;border-radius:8px;padding:14px 18px;margin:0 0 24px;">
                    <div style="font-size:12px;color:#3a9b78;margin-bottom:6px;">پاسخ دیجی‌شاپ</div>
                    <div style="font-size:14px;color:#0e2242;line-height:2;">{{ $question->answer }}</div>
                </div>

                <div style="text-align:center;">
                    <a href="{{ route('shop.products.show', $question->product) }}"
                       style="display:inline-block;padding:12px 32px;border-radius:8px;background:#1a3a6b;color:#ffffff;font-size:14px;font-weight:bold;text-decoration:none;">
                        مشاهدهٔ صفحهٔ محصول
                    </a>
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

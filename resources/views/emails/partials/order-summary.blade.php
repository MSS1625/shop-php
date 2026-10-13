{{-- خلاصهٔ سفارش — مشترک بین همهٔ ایمیل‌ها --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e6ebf2;border-radius:8px;overflow:hidden;font-size:13px;color:#44536b;">
    <tr style="background:#f4f6f9;">
        <th style="padding:10px 14px;text-align:right;font-size:12px;color:#8493ab;">محصول</th>
        <th style="padding:10px 14px;text-align:center;font-size:12px;color:#8493ab;">تعداد</th>
        <th style="padding:10px 14px;text-align:left;font-size:12px;color:#8493ab;">قیمت واحد</th>
    </tr>
    @foreach($order->items as $item)
        <tr style="border-top:1px solid #eef2f7;">
            <td style="padding:10px 14px;">{{ $item->product_title }}</td>
            <td style="padding:10px 14px;text-align:center;">{{ fa_num($item->quantity) }}</td>
            <td style="padding:10px 14px;text-align:left;">{{ fa_price($item->unit_price) }} تومان</td>
        </tr>
    @endforeach
    <tr style="border-top:1px solid #eef2f7;">
        <td colspan="2" style="padding:10px 14px;text-align:right;color:#8493ab;">جمع سفارش</td>
        <td style="padding:10px 14px;text-align:left;">{{ fa_price($order->subtotalAmount()) }} تومان</td>
    </tr>
    @if($order->hasDiscount())
        <tr style="border-top:1px solid #eef2f7;">
            <td colspan="2" style="padding:10px 14px;text-align:right;color:#1d6b40;">تخفیف ({{ $order->coupon_code }})</td>
            <td style="padding:10px 14px;text-align:left;color:#1d6b40;">−{{ fa_price($order->discount_amount) }} تومان</td>
        </tr>
    @endif
    <tr style="border-top:2px solid #e6ebf2;background:#f8fafc;">
        <td colspan="2" style="padding:12px 14px;text-align:right;font-weight:bold;color:#0e2242;">مبلغ قابل پرداخت</td>
        <td style="padding:12px 14px;text-align:left;font-weight:bold;color:#0e2242;">{{ fa_price($order->total) }} تومان</td>
    </tr>
</table>

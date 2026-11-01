<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * سفارش — وضعیت‌ها با کلید خام (برای منطق اپ) و برچسب فارسی (برای نمایش)
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'payment_method' => $this->payment_method,
            'payment_method_label' => $this->paymentMethodLabel(),
            'payment_status' => $this->payment_status,
            'payment_status_label' => $this->paymentStatusLabel(),
            'paid_at' => $this->paid_at?->toIso8601String(),

            'subtotal' => $this->subtotalAmount(),
            'discount_amount' => $this->discount_amount,
            'coupon_code' => $this->coupon_code,
            'total' => $this->total,
            'total_fa' => fa_price($this->total),

            'items_count' => $this->whenCounted('items', $this->items_count ?? 0),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),

            'created_at' => $this->created_at?->toIso8601String(),

            // لینک شروع پرداخت آنلاین — اپ موبایل آن را در WebView باز می‌کند
            'payment_url' => $this->when($this->canBePaidOnline(),
                fn () => route('payment.start', $this->order_number)),
        ];
    }
}

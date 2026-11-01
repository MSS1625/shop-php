<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * قلم سفارش — اسنپ‌شات عنوان و قیمت در لحظهٔ خرید
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_title' => $this->product_title,
            'unit_price' => $this->unit_price,
            'unit_price_fa' => fa_price($this->unit_price),
            'quantity' => $this->quantity,
            'subtotal' => $this->unit_price * $this->quantity,
        ];
    }
}

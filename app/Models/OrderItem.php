<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $sold تعداد فروش (selectRaw داشبورد)
 * @property int|null $revenue درآمد (selectRaw داشبورد)
 */
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_title',
        'unit_price',
        'quantity',
    ];

    protected $casts = [
        'unit_price' => 'integer',
        'quantity' => 'integer',
    ];

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * مبلغ کل این ردیف (قیمت واحد × تعداد)
     */
    public function subtotal(): int
    {
        return $this->unit_price * $this->quantity;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    /** وضعیت‌های مجاز سفارش */
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_SHIPPED,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    /** برچسب فارسی هر وضعیت */
    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'در انتظار بررسی',
        self::STATUS_PROCESSING => 'در حال پردازش',
        self::STATUS_SHIPPED => 'ارسال شده',
        self::STATUS_DELIVERED => 'تحویل داده شده',
        self::STATUS_CANCELLED => 'لغو شده',
    ];

    /** رنگ بوت‌استرپ هر وضعیت برای نمایش در پنل */
    public const STATUS_COLORS = [
        self::STATUS_PENDING => 'warning',
        self::STATUS_PROCESSING => 'info',
        self::STATUS_SHIPPED => 'primary',
        self::STATUS_DELIVERED => 'success',
        self::STATUS_CANCELLED => 'danger',
    ];

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_address',
        'note',
        'total',
        'status',
    ];

    protected $casts = [
        'total' => 'integer',
    ];

    /**
     * ساخت شماره سفارش یکتا و خوانا مثل: DS-140302-8F3K
     */
    public static function generateOrderNumber(): string
    {
        do {
            $number = 'DS-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when($status && in_array($status, self::STATUSES, true),
            fn (Builder $q) => $q->where('status', $status));
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}

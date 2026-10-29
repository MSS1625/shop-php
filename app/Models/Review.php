<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    /** وضعیت‌های بررسی نظر */
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    /** برچسب فارسی هر وضعیت */
    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'در انتظار تأیید',
        self::STATUS_APPROVED => 'تأییدشده',
        self::STATUS_REJECTED => 'ردشده',
    ];

    /** رنگ بوت‌استرپ هر وضعیت برای نمایش در پنل */
    public const STATUS_COLORS = [
        self::STATUS_PENDING => 'warning',
        self::STATUS_APPROVED => 'success',
        self::STATUS_REJECTED => 'danger',
    ];

    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'comment',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * فیلتر وضعیت — مقدار نامعتبر بی‌صدا نادیده گرفته می‌شود
     * (همان الگوی سفارش‌ها)
     */
    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when($status && in_array($status, self::STATUSES, true),
            fn (Builder $q) => $q->where('status', $status));
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    /**
     * خلاصهٔ متن نظر برای جدول مدیریت
     */
    public function excerpt(int $limit = 90): string
    {
        return mb_strlen($this->comment) > $limit
            ? mb_substr($this->comment, 0, $limit).'…'
            : $this->comment;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    /** وضعیت‌های پرسش و پاسخ */
    public const STATUS_PENDING = 'pending';

    public const STATUS_ANSWERED = 'answered';

    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ANSWERED,
        self::STATUS_REJECTED,
    ];

    /** برچسب فارسی هر وضعیت */
    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'در انتظار پاسخ',
        self::STATUS_ANSWERED => 'پاسخ داده شد',
        self::STATUS_REJECTED => 'ردشده',
    ];

    /** رنگ بوت‌استرپ هر وضعیت برای نمایش در پنل */
    public const STATUS_COLORS = [
        self::STATUS_PENDING => 'warning',
        self::STATUS_ANSWERED => 'success',
        self::STATUS_REJECTED => 'danger',
    ];

    protected $fillable = [
        'product_id',
        'user_id',
        'body',
        'answer',
        'status',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
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
     * (همان الگوی سفارش‌ها و نظرات)
     */
    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when($status && in_array($status, self::STATUSES, true),
            fn (Builder $q) => $q->where('status', $status));
    }

    /**
     * پاسخ‌داده‌شده‌ها — تنها پرسش‌هایی که در صفحهٔ محصول دیده می‌شوند
     */
    public function scopeAnswered(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ANSWERED);
    }

    public function isAnswered(): bool
    {
        return $this->status === self::STATUS_ANSWERED;
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
     * خلاصهٔ متن پرسش برای جدول مدیریت
     */
    public function excerpt(int $limit = 80): string
    {
        return mb_strlen($this->body) > $limit
            ? mb_substr($this->body, 0, $limit).'…'
            : $this->body;
    }
}

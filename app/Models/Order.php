<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

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

    /** روش‌های پرداخت */
    public const METHOD_ZARINPAL = 'zarinpal';

    public const METHOD_COD = 'cod';

    public const PAYMENT_METHODS = [
        self::METHOD_ZARINPAL,
        self::METHOD_COD,
    ];

    public const PAYMENT_METHOD_LABELS = [
        self::METHOD_ZARINPAL => 'پرداخت آنلاین (زرین‌پال)',
        self::METHOD_COD => 'پرداخت در محل',
    ];

    /** وضعیت‌های پرداخت */
    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_FAILED = 'failed';

    public const PAYMENT_CANCELLED = 'cancelled';

    public const PAYMENT_STATUSES = [
        self::PAYMENT_PENDING,
        self::PAYMENT_PAID,
        self::PAYMENT_FAILED,
        self::PAYMENT_CANCELLED,
    ];

    public const PAYMENT_STATUS_LABELS = [
        self::PAYMENT_PENDING => 'در انتظار پرداخت',
        self::PAYMENT_PAID => 'پرداخت شده',
        self::PAYMENT_FAILED => 'پرداخت ناموفق',
        self::PAYMENT_CANCELLED => 'پرداخت لغو شد',
    ];

    public const PAYMENT_STATUS_COLORS = [
        self::PAYMENT_PENDING => 'warning',
        self::PAYMENT_PAID => 'success',
        self::PAYMENT_FAILED => 'danger',
        self::PAYMENT_CANCELLED => 'secondary',
    ];

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'note',
        'total',
        'status',
        'payment_method',
        'payment_status',
        'authority',
        'ref_id',
        'paid_at',
    ];

    protected $casts = [
        'total' => 'integer',
        'ref_id' => 'integer',
        'paid_at' => 'datetime',
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

    /** کاربر مالک سفارش (اگر با حساب کاربری خرید شده باشد) */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when($status && in_array($status, self::STATUSES, true),
            fn (Builder $q) => $q->where('status', $status));
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->getKey());
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

    public function paymentMethodLabel(): string
    {
        return self::PAYMENT_METHOD_LABELS[$this->payment_method] ?? $this->payment_method;
    }

    public function isOnlinePayment(): bool
    {
        return $this->payment_method === self::METHOD_ZARINPAL;
    }

    public function paymentStatusLabel(): string
    {
        // برای سفارش «پرداخت در محل» وضعیت پرداخت معنا ندارد
        if (! $this->isOnlinePayment()) {
            return 'هنگام تحویل';
        }

        return self::PAYMENT_STATUS_LABELS[$this->payment_status] ?? $this->payment_status;
    }

    public function paymentStatusColor(): string
    {
        if (! $this->isOnlinePayment()) {
            return 'secondary';
        }

        return self::PAYMENT_STATUS_COLORS[$this->payment_status] ?? 'secondary';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    /** آیا امکان پرداخت/تلاش مجدد آنلاین وجود دارد؟ */
    public function canBePaidOnline(): bool
    {
        return $this->isOnlinePayment()
            && ! $this->isPaid()
            && ! $this->isCancelled();
    }

    /** آیا کاربر فعلی (یا سشن فعلی) حق دیدن این سفارش را دارد؟ */
    public function isAccessibleByCurrentUser(): bool
    {
        if (in_array($this->order_number, session('viewed_orders', []), true)) {
            return true;
        }

        $user = auth()->user();

        return $user && ($user->isAdmin() || $user->getAuthIdentifier() === $this->user_id);
    }

    /**
     * لغو سفارش + بازگشت موجودی به انبار — اتمیک
     * (فقط سفارش‌های در انتظار بررسی قابل لغو توسط مشتری هستند)
     */
    public function cancelWithRestock(): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

        return (bool) DB::transaction(function () {
            // قفل ردیف برای جلوگیری از لغو همزمان
            $fresh = static::whereKey($this->getKey())->lockForUpdate()->first();

            if (! $fresh || $fresh->isCancelled()) {
                return false;
            }

            foreach ($this->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            $this->status = self::STATUS_CANCELLED;
            $this->payment_status = $this->isPaid()
                ? self::PAYMENT_PAID   // پرداخت انجام شده — وضعیت مالی حفظ می‌شود تا بازگشت وجه مدیریت شود
                : self::PAYMENT_CANCELLED;

            return $this->save();
        });
    }

    /**
     * ثبت نتیجه پرداخت آنلاین موفق
     */
    public function markAsPaid(int $refId): void
    {
        $this->forceFill([
            'payment_status' => self::PAYMENT_PAID,
            'ref_id' => $refId,
            'paid_at' => now(),
        ])->save();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    public const TYPE_PERCENT = 'percent';

    public const TYPE_FIXED = 'fixed';

    public const TYPES = [
        self::TYPE_PERCENT,
        self::TYPE_FIXED,
    ];

    public const TYPE_LABELS = [
        self::TYPE_PERCENT => 'درصدی',
        self::TYPE_FIXED => 'مبلغ ثابت',
    ];

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'integer',
        'min_order_amount' => 'integer',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /** ارقام فارسی/عربی → لاتین */
    private const DIGIT_MAP = [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ];

    /**
     * نرمال‌سازی کد ورودی کاربر: ارقام فارسی→لاتین، حذف فاصله،
     * حروف بزرگ و فقط کاراکترهای مجاز (A-Z، 0-9 و خط تیره)
     */
    public static function normalizeCode(string $code): string
    {
        $code = strtr(trim($code), self::DIGIT_MAP);
        $code = strtoupper($code);
        $code = preg_replace('/[^A-Z0-9\-]/', '', $code) ?? '';

        return substr($code, 0, 32);
    }

    /** یافتن کوپن با کد نرمال‌شده */
    public static function findByCode(?string $code): ?self
    {
        $code = self::normalizeCode((string) $code);

        if ($code === '') {
            return null;
        }

        return static::query()->where('code', $code)->first();
    }

    public function hasStarted(): bool
    {
        return $this->starts_at === null || $this->starts_at->isPast();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isExhausted(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }

    /**
     * مبلغ تخفیف برای یک جمع سبد — همیشه سقف‌دار به مبلغ سبد
     */
    public function discountFor(int $subtotal): int
    {
        if ($subtotal <= 0) {
            return 0;
        }

        if ($this->type === self::TYPE_PERCENT) {
            $percent = min(max($this->value, 0), 100);

            return (int) floor($subtotal * $percent / 100);
        }

        return min($this->value, $subtotal);
    }

    /** برچسب کوتاه نوع تخفیف — «۱۰٪ تخفیف» یا «۵۰۰٬۰۰۰ تومان تخفیف» */
    public function discountLabel(): string
    {
        if ($this->type === self::TYPE_PERCENT) {
            return fa_num($this->value).'٪ تخفیف';
        }

        return fa_price($this->value).' تومان تخفیف';
    }

    /**
     * دلیل غیرمعتبر بودن کوپن برای این مبلغ سبد — null یعنی معتبر است
     */
    public function invalidReason(int $subtotal): ?string
    {
        if (! $this->is_active) {
            return 'این کد تخفیف غیرفعال شده است.';
        }

        if (! $this->hasStarted()) {
            return 'زمان اعتبار این کد هنوز آغاز نشده است.';
        }

        if ($this->isExpired()) {
            return 'این کد تخفیف منقضی شده است.';
        }

        if ($this->isExhausted()) {
            return 'ظرفیت استفاده از این کد تکمیل شده است.';
        }

        if ($subtotal < $this->min_order_amount) {
            return 'حداقل مبلغ سبد برای این کد '.fa_price($this->min_order_amount).' تومان است.';
        }

        return null;
    }

    public function isValidFor(int $subtotal): bool
    {
        return $this->invalidReason($subtotal) === null;
    }

    /** «۳ از ۱۰۰ بار» یا «۳ بار» (بدون سقف) */
    public function usageLabel(): string
    {
        $used = fa_num($this->used_count);

        return $this->usage_limit === null
            ? $used.' بار'
            : $used.' از '.fa_num($this->usage_limit).' بار';
    }

    /** وضعیت نمایشی برای پنل مدیریت */
    public function statusLabel(): string
    {
        return match (true) {
            ! $this->is_active => 'غیرفعال',
            ! $this->hasStarted() => 'شروع نشده',
            $this->isExpired() => 'منقضی',
            $this->isExhausted() => 'تکمیل ظرفیت',
            default => 'فعال',
        };
    }

    public function statusColor(): string
    {
        return match (true) {
            ! $this->is_active => 'secondary',
            ! $this->hasStarted() => 'info',
            $this->isExpired() => 'danger',
            $this->isExhausted() => 'warning',
            default => 'success',
        };
    }
}

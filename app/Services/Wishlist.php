<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * علاقه‌مندی‌های کاربر جاری — singleton، همان الگوی سبد خرید.
 *
 * فهرست شناسه‌ها یک بار در هر درخواست واکشی و memoize می‌شود تا
 * ده‌ها کارت محصول در یک صفحه فقط یک کوئری ببینند، نه یکی‌یکی.
 * فقط محصولات فعال حساب می‌شوند — محصولی که مدیر غیرفعال کرد
 * از فهرست و شمارنده بی‌صدا کنار می‌رود، همان قاعدهٔ بقیهٔ ویترین.
 */
class Wishlist
{
    /** @var Collection<int, int>|null */
    protected ?Collection $ids = null;

    /**
     * شناسهٔ محصولات فعالِ ذخیره‌شده توسط کاربر جاری — مهمان: خالی
     *
     * @return Collection<int, int>
     */
    public function ids(): Collection
    {
        if ($this->ids === null) {
            $user = auth()->user();

            $this->ids = $user
                ? $user->wishlistProducts()->where('is_active', true)->pluck('products.id')
                : collect();
        }

        return $this->ids;
    }

    /**
     * آیا این محصول در علاقه‌مندی‌های کاربر جاری است؟
     */
    public function has(int $productId): bool
    {
        return $this->ids()->contains($productId);
    }

    /**
     * تعداد علاقه‌مندی‌های فعال برای شمارندهٔ ناوبار
     */
    public function count(): int
    {
        return $this->ids()->count();
    }

    /**
     * باطل‌کردن حافظهٔ داخلی — بعد از تغییر فهرست در همین درخواست
     */
    public function forget(): void
    {
        $this->ids = null;
    }
}

<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * فهرست مقایسهٔ محصولات مبتنی بر سشن — مهمان هم می‌تواند مقایسه کند
 * (بدون نیاز به حساب کاربری، همان الگوی سبد خرید)
 * ساختار سشن: compare: [product_id, product_id, …] به ترتیب افزودن
 */
class Compare
{
    protected const SESSION_KEY = 'compare';

    /** سقف مقایسهٔ هم‌زمان — بیشتر از ۴ ستون جدول خوانا نمی‌ماند */
    public const MAX = 4;

    /**
     * شناسه‌های ذخیره‌شده در سشن به ترتیب افزودن
     *
     * @return array<int, int>
     */
    public function ids(): array
    {
        return array_values(array_map('intval', session(self::SESSION_KEY, [])));
    }

    /**
     * آیا این محصول در فهرست مقایسه است؟ (برای حالت فعال دکمه)
     */
    public function has(int $productId): bool
    {
        return in_array($productId, $this->ids(), true);
    }

    /**
     * تعداد محصولات فهرست
     */
    public function count(): int
    {
        return count($this->ids());
    }

    /**
     * افزودن یا حذف یک محصول با یک کلیک
     *
     * خروجی: added | removed | full
     * «full» یعنی فهرست پر است و محصول اضافه نشد
     */
    public function toggle(Product $product): string
    {
        $ids = $this->ids();

        if (in_array($product->id, $ids, true)) {
            $ids = array_values(array_diff($ids, [$product->id]));
            session([self::SESSION_KEY => $ids]);

            return 'removed';
        }

        if (count($ids) >= self::MAX) {
            return 'full';
        }

        $ids[] = (int) $product->id;
        session([self::SESSION_KEY => $ids]);

        return 'added';
    }

    /**
     * خالی کردن کل فهرست
     */
    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * محصولات فهرست برای صفحهٔ مقایسه — فقط فعال‌ها،
     * به ترتیب افزودن؛ اگر چیزی در راه غیرفعال/حذف شده باشد
     * سشن همان‌جا تمیز می‌شود تا شمارندهٔ ناوبار درست بماند
     *
     * @return Collection<int, Product>
     */
    public function products(): Collection
    {
        $ids = $this->ids();

        if ($ids === []) {
            return collect();
        }

        $products = Product::query()
            ->whereKey($ids)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        // سشن را با واقعیتِ دیتابیس هم‌تراز کن (حذف/غیرفعال‌شده‌ها بیرون می‌افتند)
        $alive = array_values(array_intersect($ids, $products->keys()->all()));

        if ($alive !== $ids) {
            session([self::SESSION_KEY => $alive]);
        }

        return collect($alive)
            ->map(fn (int $id) => $products[$id])
            ->values();
    }
}

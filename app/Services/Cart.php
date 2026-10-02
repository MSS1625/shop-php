<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * سبد خرید مبتنی بر سشن — بدون نیاز به لاگین کاربر
 * ساختار سشن: cart: [product_id => quantity]
 */
class Cart
{
    protected const SESSION_KEY = 'cart';

    /**
     * آیتم‌های سبد به‌همراه اطلاعات کامل محصولات
     *
     * @return Collection<int, array{product: Product, quantity: int, subtotal: int}>
     */
    public function items(): Collection
    {
        $raw = session(self::SESSION_KEY, []);

        if (empty($raw)) {
            return collect();
        }

        $products = Product::with('category')
            ->where('is_active', true)
            ->whereIn('id', array_keys($raw))
            ->get()
            ->keyBy('id');

        return collect($raw)
            ->filter(fn ($qty, $id) => $products->has((int) $id) && $qty > 0)
            ->map(function ($qty, $id) use ($products) {
                $product = $products[(int) $id];
                $quantity = min((int) $qty, max($product->stock, 1));

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $product->price * $quantity,
                ];
            })
            ->values();
    }

    /**
     * افزودن محصول به سبد (با رعایت موجودی انبار)
     *
     * @return int تعداد نهایی این محصول در سبد
     */
    public function add(Product $product, int $quantity = 1): int
    {
        $cart = session(self::SESSION_KEY, []);
        $id = (string) $product->id;

        $current = (int) ($cart[$id] ?? 0);
        $desired = max(1, $current + max(1, $quantity));

        // محدودسازی به موجودی انبار
        $desired = min($desired, max($product->stock, 1));

        $cart[$id] = $desired;
        session([self::SESSION_KEY => $cart]);

        return $desired;
    }

    /**
     * تنظیم مستقیم تعداد یک محصول
     */
    public function update(Product $product, int $quantity): void
    {
        $cart = session(self::SESSION_KEY, []);

        if ($quantity <= 0) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id] = min($quantity, max($product->stock, 1));
        }

        session([self::SESSION_KEY => $cart]);
    }

    /**
     * حذف یک محصول از سبد
     */
    public function remove(int $productId): void
    {
        $cart = session(self::SESSION_KEY, []);
        unset($cart[$productId]);
        session([self::SESSION_KEY => $cart]);
    }

    /**
     * خالی کردن کل سبد
     */
    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * تعداد کل اقلام سبد (مجموع تعداد هر محصول)
     */
    public function count(): int
    {
        return array_sum(array_map('intval', session(self::SESSION_KEY, [])));
    }

    /**
     * مبلغ کل سبد (تومان)
     */
    public function total(): int
    {
        return $this->items()->sum('subtotal');
    }

    /**
     * آیا سبد خالی است؟
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }
}

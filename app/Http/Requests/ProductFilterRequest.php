<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * فیلترهای صفحهٔ محصولات — جستجو، دسته، مرتب‌سازی، بازه قیمت و موجودی
 *
 * نرمال‌سازی قیمت‌ها مثل کد تخفیف و پیگیری سفارش کار می‌کند:
 * کاربر می‌تواند با کیبورد فارسی (۵۰۰۰۰۰) یا با جداکننده (500,000) هم عدد وارد کند.
 */
class ProductFilterRequest extends FormRequest
{
    /**
     * نرمال‌سازی ورودی‌ها قبل از اعتبارسنجی:
     * ارقام فارسی/عربی → لاتین، حذف جداکننده هزارگان و هر چیزی غیر از رقم
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'min' => $this->normalizePrice($this->input('min')),
            'max' => $this->normalizePrice($this->input('max')),
            'in_stock' => $this->boolean('in_stock') ? '1' : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'exists:categories,slug'],
            'sort' => ['nullable', 'in:newest,oldest,cheapest,expensive,popular'],
            'min' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'max' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'in_stock' => ['nullable', 'boolean'],
        ];
    }

    /**
     * فیلترهای اعمال‌شده به‌صورت یک آرایه تمیز برای کنترلر.
     *
     * اگر بازه برعکس وارد شود (حداقل > حداکثر) بی‌صدا جابه‌جا می‌شود
     * تا کاربر هرگز نتیجهٔ خالی از این بابت نبیند — همان رویهٔ رایج فروشگاه‌ها.
     *
     * @return array{term: string, category: ?string, sort: string, min: ?int, max: ?int, inStock: bool}
     */
    public function filters(): array
    {
        $validated = $this->validated();

        $min = isset($validated['min']) ? (int) $validated['min'] : null;
        $max = isset($validated['max']) ? (int) $validated['max'] : null;

        if ($min !== null && $max !== null && $min > $max) {
            [$min, $max] = [$max, $min];
        }

        return [
            'term' => trim((string) ($validated['q'] ?? '')),
            'category' => $validated['category'] ?? null,
            'sort' => $validated['sort'] ?? 'newest',
            'min' => $min,
            'max' => $max,
            'inStock' => ($validated['in_stock'] ?? null) === '1',
        ];
    }

    /**
     * قیمت ورودی → فقط رقم لاتین؛ خالی یا بدون هیچ رقمی → null (فیلتر اعمال نمی‌شود)
     */
    private function normalizePrice(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', en_num((string) $value));

        return $digits === '' ? null : $digits;
    }
}

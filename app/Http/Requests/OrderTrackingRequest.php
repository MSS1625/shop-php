<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderTrackingRequest extends FormRequest
{
    /**
     * نقشهٔ تبدیل ارقام فارسی/عربی به لاتین —
     * همان الگوی نرمال‌سازی کد تخفیف؛ کاربر می‌تواند با کیبورد فارسی هم شماره‌ها را وارد کند
     */
    private const DIGIT_MAP = [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ];

    /**
     * نرمال‌سازی ورودی‌ها قبل از اعتبارسنجی:
     * ارقام فارسی/عربی → لاتین، حروف بزرگ، حذف فاصله و کاراکترهای اضافه
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_number' => $this->normalize($this->input('order_number')),
            'phone' => $this->normalize($this->input('phone')),
        ]);
    }

    public function rules(): array
    {
        return [
            // قالب شماره سفارش: DS-yymmdd-XXXX (همان چیزی که generateOrderNumber می‌سازد)
            'order_number' => ['required', 'string', 'regex:/^DS-\d{6}-[A-Z0-9]{4}$/'],
            // شماره موبایل ایران — همان قاعدهٔ فرم تسویه
            'phone' => ['required', 'string', 'regex:/^09\d{9}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_number.required' => 'شماره سفارش الزامی است.',
            'order_number.regex' => 'قالب شماره سفارش درست نیست (مثال: DS-261021-A1B2).',
            'phone.required' => 'شماره موبایل الزامی است.',
            'phone.regex' => 'شماره موبایل معتبر نیست (مثال: 09123456789).',
        ];
    }

    /** شماره سفارش نرمال‌شده */
    public function normalizedOrderNumber(): string
    {
        return (string) $this->validated('order_number');
    }

    /** شماره موبایل نرمال‌شده */
    public function normalizedPhone(): string
    {
        return (string) $this->validated('phone');
    }

    private function normalize(mixed $value): string
    {
        $value = strtr(trim((string) $value), self::DIGIT_MAP);
        $value = strtoupper($value);

        return preg_replace('/[^A-Z0-9\-]/', '', $value) ?? '';
    }
}

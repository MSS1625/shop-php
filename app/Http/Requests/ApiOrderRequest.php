<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ثبت سفارش مستقیم از API — سبد خرید سشن‌محور وب اینجا نیست؛
 * اپلیکیشن موبایل آیتم‌ها را با خودش می‌فرستد.
 *
 * هر آیتم: slug محصول فعال + تعداد؛ اطلاعات مشتری همان قواعد
 * فرم خرید وب (CheckoutRequest) را دارد تا «یک قانون، همه جا» برقرار بماند.
 */
class ApiOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.slug' => [
                'required', 'string', 'max:255',
                Rule::exists('products', 'slug')->where('is_active', true),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],

            'customer_name' => ['required', 'string', 'min:3', 'max:100'],
            // شماره موبایل ایران: ۰۹xxxxxxxxx
            'customer_phone' => ['required', 'string', 'regex:/^09\d{9}$/', 'max:20'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['required', 'string', 'min:10', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in(Order::PAYMENT_METHODS)],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'فهرست اقلام سفارش الزامی است.',
            'items.min' => 'سفارش باید حداقل یک قلم داشته باشد.',
            'items.*.slug.required' => 'شناسهٔ محصول هر قلم الزامی است.',
            'items.*.slug.exists' => 'یکی از محصولات سفارش یافت نشد یا فعال نیست.',
            'items.*.quantity.required' => 'تعداد هر قلم الزامی است.',
            'items.*.quantity.min' => 'تعداد هر قلم باید حداقل ۱ باشد.',
            'items.*.quantity.max' => 'تعداد هر قلم حداکثر می‌تواند ۹۹ باشد.',
            'customer_name.required' => 'نام و نام خانوادگی الزامی است.',
            'customer_name.min' => 'نام باید حداقل :min کاراکتر باشد.',
            'customer_phone.required' => 'شماره موبایل الزامی است.',
            'customer_phone.regex' => 'شماره موبایل معتبر نیست (مثال: 09123456789).',
            'customer_email.email' => 'ایمیل واردشده معتبر نیست.',
            'customer_address.required' => 'آدرس تحویل سفارش الزامی است.',
            'customer_address.min' => 'آدرس باید حداقل :min کاراکتر باشد.',
            'note.max' => 'توضیحات حداکثر می‌تواند :max کاراکتر باشد.',
            'payment_method.required' => 'روش پرداخت را انتخاب کنید.',
            'payment_method.in' => 'روش پرداخت انتخاب‌شده معتبر نیست.',
            'coupon_code.max' => 'کد تخفیف حداکثر می‌تواند :max کاراکتر باشد.',
        ];
    }

    /**
     * روش پرداخت انتخاب‌شده — همان پیش‌فرض فرم وب
     */
    public function paymentMethod(): string
    {
        return $this->validated('payment_method') === Order::METHOD_ZARINPAL
            ? Order::METHOD_ZARINPAL
            : Order::METHOD_COD;
    }
}

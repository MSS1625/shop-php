<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'min:3', 'max:100'],
            // شماره موبایل ایران: ۰۹xxxxxxxxx
            'customer_phone' => ['required', 'string', 'regex:/^09\d{9}$/', 'max:20'],
            'customer_address' => ['required', 'string', 'min:10', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in(Order::PAYMENT_METHODS)],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'نام و نام خانوادگی الزامی است.',
            'customer_name.min' => 'نام باید حداقل :min کاراکتر باشد.',
            'customer_phone.required' => 'شماره موبایل الزامی است.',
            'customer_phone.regex' => 'شماره موبایل معتبر نیست (مثال: 09123456789).',
            'customer_address.required' => 'آدرس تحویل سفارش الزامی است.',
            'customer_address.min' => 'آدرس باید حداقل :min کاراکتر باشد.',
            'note.max' => 'توضیحات حداکثر می‌تواند :max کاراکتر باشد.',
            'payment_method.required' => 'روش پرداخت را انتخاب کنید.',
            'payment_method.in' => 'روش پرداخت انتخاب‌شده معتبر نیست.',
        ];
    }

    /**
     * روش پرداخت انتخاب‌شده — پیش‌فرض پرداخت در محل
     */
    public function paymentMethod(): string
    {
        return $this->validated('payment_method') === Order::METHOD_ZARINPAL
            ? Order::METHOD_ZARINPAL
            : Order::METHOD_COD;
    }
}

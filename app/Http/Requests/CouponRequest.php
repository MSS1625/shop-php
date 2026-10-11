<?php

namespace App\Http\Requests;

use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
{
    /**
     * نرمال‌سازی کد قبل از اعتبارسنجی (حروف بزرگ، بدون فاصله)
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('code')) {
            $this->merge([
                'code' => Coupon::normalizeCode((string) $this->input('code')),
            ]);
        }
    }

    /**
     * اعتبارسنجی کد تخفیف — هم برای ایجاد و هم برای ویرایش
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'min:3',
                'max:32',
                'regex:/^[A-Z0-9-]+$/',
                Rule::unique('coupons', 'code')->ignore($this->route('coupon')?->id),
            ],
            'type' => ['required', Rule::in(Coupon::TYPES)],
            'value' => [
                'required',
                'integer',
                'min:1',
                // درصد حداکثر ۱۰۰؛ مبلغ ثابت تا سقف منطقی
                $this->input('type') === Coupon::TYPE_PERCENT ? 'max:100' : 'max:99999999',
            ],
            'min_order_amount' => ['nullable', 'integer', 'min:0', 'max:99999999'],
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * مقایسه شروع/پایان فقط وقتی هر دو تاریخ پر شده‌اند
     */
    public function withValidator($validator): void
    {
        $validator->sometimes(
            'starts_at',
            'before_or_equal:expires_at',
            fn () => $this->filled(['starts_at', 'expires_at'])
        );
    }

    public function messages(): array
    {
        return [
            'code.required' => 'کد تخفیف الزامی است.',
            'code.min' => 'کد باید حداقل :min کاراکتر باشد.',
            'code.max' => 'کد حداکثر می‌تواند :max کاراکتر باشد.',
            'code.regex' => 'کد فقط می‌تواند شامل حروف انگلیسی، عدد و خط تیره باشد.',
            'code.unique' => 'این کد قبلاً ثبت شده است.',
            'type.required' => 'انتخاب نوع تخفیف الزامی است.',
            'type.in' => 'نوع تخفیف معتبر نیست.',
            'value.required' => 'مقدار تخفیف الزامی است.',
            'value.integer' => 'مقدار تخفیف باید عدد صحیح باشد.',
            'value.min' => 'مقدار تخفیف باید حداقل ۱ باشد.',
            'value.max' => 'درصد تخفیف حداکثر می‌تواند ۱۰۰ باشد.',
            'min_order_amount.integer' => 'حداقل مبلغ سبد باید عدد صحیح (تومان) باشد.',
            'min_order_amount.min' => 'حداقل مبلغ سبد نمی‌تواند منفی باشد.',
            'usage_limit.integer' => 'سقف تعداد استفاده باید عدد صحیح باشد.',
            'usage_limit.min' => 'سقف تعداد استفاده باید حداقل ۱ باشد.',
            'starts_at.date' => 'تاریخ شروع معتبر نیست.',
            'starts_at.before_or_equal' => 'تاریخ شروع باید قبل از تاریخ پایان باشد.',
            'expires_at.date' => 'تاریخ پایان معتبر نیست.',
        ];
    }

    /**
     * داده‌های آماده ثبت — تاریخ‌های خالی به null تبدیل می‌شوند
     */
    public function validatedData(): array
    {
        $data = $this->validated();
        $data['starts_at'] = $data['starts_at'] ?? null;
        $data['expires_at'] = $data['expires_at'] ?? null;
        $data['min_order_amount'] = (int) ($data['min_order_amount'] ?? 0);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }
}

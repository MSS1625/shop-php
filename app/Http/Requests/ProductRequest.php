<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * اعتبارسنجی محصول — هم برای ایجاد و هم برای ویرایش
     */
    public function rules(): array
    {
        $rules = [
            // فقط عنوان متنی با طول منطقی؛ تگ‌های HTML در Blade به‌صورت خودکار escape می‌شوند
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'integer', 'min:0', 'max:99999999999'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['sometimes', 'boolean'],

            // فقط تصویر واقعی با فرمت‌های مجاز و حداکثر ۲ مگابایت
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048',
                Rule::dimensions()->maxWidth(4000)->maxHeight(4000),
            ],

            // گالری تصاویر اضافی (حداکثر ۴ تصویر در هر ارسال)
            'images' => ['nullable', 'array', 'max:4'],
            'images.*' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048',
                Rule::dimensions()->maxWidth(4000)->maxHeight(4000),
            ],

            // حذف تصاویر موجود گالری (فقط ID های متعلق به همین محصول پاک می‌شوند)
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['nullable', 'integer'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان محصول الزامی است.',
            'title.min' => 'عنوان باید حداقل :min کاراکتر باشد.',
            'category_id.required' => 'انتخاب دسته‌بندی الزامی است.',
            'category_id.exists' => 'دسته‌بندی انتخاب‌شده معتبر نیست.',
            'price.required' => 'قیمت الزامی است.',
            'price.integer' => 'قیمت باید عدد صحیح (تومان) باشد.',
            'price.min' => 'قیمت نمی‌تواند منفی باشد.',
            'stock.required' => 'موجودی انبار الزامی است.',
            'stock.integer' => 'موجودی باید عدد صحیح باشد.',
            'image.image' => 'فایل ارسالی باید تصویر باشد.',
            'image.mimes' => 'فرمت‌های مجاز تصویر: JPG، PNG و WebP.',
            'image.max' => 'حجم تصویر حداکثر باید ۲ مگابایت باشد.',
            'image.dimensions' => 'ابعاد تصویر حداکثر باید ۴۰۰۰×۴۰۰۰ پیکسل باشد.',
            'images.max' => 'حداکثر ۴ تصویر برای گالری در هر ارسال مجاز است.',
            'images.*.image' => 'همه فایل‌های گالری باید تصویر باشند.',
            'images.*.mimes' => 'فرمت‌های مجاز گالری: JPG، PNG و WebP.',
            'images.*.max' => 'حجم هر تصویر گالری حداکثر ۲ مگابایت.',
            'images.*.dimensions' => 'ابعاد تصاویر گالری حداکثر ۴۰۰۰×۴۰۰۰ پیکسل.',
        ];
    }

    /**
     * داده‌های معتبر و امن برای ذخیره
     */
    public function validatedData(): array
    {
        return $this->safe()->only([
            'title',
            'category_id',
            'description',
            'price',
            'stock',
            'is_active',
        ]) + ['is_active' => $this->boolean('is_active')];
    }
}

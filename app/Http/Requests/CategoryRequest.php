<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('categories', 'name')->ignore($this->route('category')),
            ],
            'icon' => ['nullable', 'string', 'max:100', 'regex:/^fa[a-z0-9-]*$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام دسته الزامی است.',
            'name.min' => 'نام دسته باید حداقل :min کاراکتر باشد.',
            'name.unique' => 'این نام دسته قبلاً ثبت شده است.',
            'icon.regex' => 'آیکون باید یک کلاس معتبر Font Awesome باشد (مثال: fa-network-wired).',
        ];
    }
}

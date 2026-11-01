<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ثبت‌نام از طریق API — همان قواعد فرم وب (نام، ایمیل یکتا، رمز تأییدی)
 */
class ApiRegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام و نام خانوادگی الزامی است.',
            'name.min' => 'نام باید حداقل :min کاراکتر باشد.',
            'email.required' => 'ایمیل الزامی است.',
            'email.email' => 'آدرس ایمیل معتبر نیست.',
            'email.unique' => 'این ایمیل قبلا ثبت شده است؛ وارد شوید.',
            'password.required' => 'رمز عبور الزامی است.',
            'password.min' => 'رمز عبور باید حداقل :min کاراکتر باشد.',
            'password.confirmed' => 'تکرار رمز عبور با رمز عبور یکسان نیست.',
        ];
    }
}

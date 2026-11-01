<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ورود از طریق API — ایمیل و رمز عبور؛ پاسخ توکن Sanctum است
 */
class ApiLoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'ایمیل الزامی است.',
            'email.email' => 'آدرس ایمیل معتبر نیست.',
            'password.required' => 'رمز عبور الزامی است.',
        ];
    }
}

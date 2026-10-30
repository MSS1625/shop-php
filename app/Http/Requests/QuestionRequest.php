<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionRequest extends FormRequest
{
    /**
     * اعتبارسنجی پرسش جدید — فقط متن پرسش (پاسخ را مدیر می‌نویسد)
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'متن پرسش الزامی است.',
            'body.min' => 'متن پرسش باید حداقل :min کاراکتر باشد.',
            'body.max' => 'متن پرسش حداکثر می‌تواند :max کاراکتر باشد.',
        ];
    }
}

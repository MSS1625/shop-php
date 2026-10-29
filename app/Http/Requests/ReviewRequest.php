<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    /**
     * اعتبارسنجی نظر جدید — امتیاز ۱ تا ۵ + متن دیدگاه
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'انتخاب امتیاز الزامی است.',
            'rating.integer' => 'امتیاز باید عدد باشد.',
            'rating.between' => 'امتیاز باید بین ۱ تا ۵ ستاره باشد.',
            'comment.required' => 'متن نظر الزامی است.',
            'comment.min' => 'متن نظر باید حداقل :min کاراکتر باشد.',
            'comment.max' => 'متن نظر حداکثر می‌تواند :max کاراکتر باشد.',
        ];
    }
}

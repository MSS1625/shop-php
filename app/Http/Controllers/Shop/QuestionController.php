<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionRequest;
use App\Models\Product;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;

class QuestionController extends Controller
{
    /**
     * ثبت پرسش جدید برای محصول — همیشه «در انتظار پاسخ» شروع می‌شود
     * و تا پاسخ مدیر برای عموم نمایش داده نمی‌شود
     */
    public function store(QuestionRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $product->questions()->create([
            'user_id' => $request->user()->getAuthIdentifier(),
            'body' => trim((string) $request->validated('body')),
            'status' => Question::STATUS_PENDING,
        ]);

        return redirect()
            ->route('shop.products.show', $product)
            ->with('success', 'پرسش شما ثبت شد و پس از پاسخ کارشناسان، اینجا نمایش داده می‌شود.');
    }
}

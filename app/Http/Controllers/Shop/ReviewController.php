<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    /**
     * ثبت نظر جدید برای محصول — همیشه «در انتظار تأیید» شروع می‌شود
     * و تا تأیید مدیر برای خریداران نمایش داده نمی‌شود
     */
    public function store(ReviewRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $user = $request->user();

        // یک نظر برای هر کاربر/محصول — کلید یکتای دیتابیس هم پشتیبان است
        if ($product->reviews()->where('user_id', $user->getAuthIdentifier())->exists()) {
            return back()
                ->withErrors(['rating' => 'شما قبلاً برای این محصول نظر ثبت کرده‌اید.'])
                ->withInput();
        }

        try {
            $product->reviews()->create([
                'user_id' => $user->getAuthIdentifier(),
                'rating' => $request->validated('rating'),
                'comment' => trim((string) $request->validated('comment')),
                'status' => Review::STATUS_PENDING,
            ]);
        } catch (QueryException) {
            // ثبت همزمان از دو تب — کلید یکتا برخورد کرد
            return back()
                ->withErrors(['rating' => 'شما قبلاً برای این محصول نظر ثبت کرده‌اید.'])
                ->withInput();
        }

        return redirect()
            ->route('shop.products.show', $product)
            ->with('success', 'نظر شما ثبت شد و پس از تأیید، برای خریداران نمایش داده می‌شود.');
    }
}

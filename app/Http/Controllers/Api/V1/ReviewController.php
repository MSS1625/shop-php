<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    /**
     * نظرات تأییدشدهٔ یک محصول — همان چیزی که صفحهٔ وب نمایش می‌دهد؛
     * نشان «خریدار تأییدشده» با همان یک-کوئری، بدون N+1.
     */
    public function index(Request $request, Product $product): AnonymousResourceCollection
    {
        abort_unless($product->is_active, 404);

        $reviews = $product->approvedReviews()
            ->with('user:id,name')
            ->latest()
            ->paginate(10);

        $verifiedIds = $product->verifiedBuyerIds($reviews->pluck('user_id'));
        $reviews->getCollection()->transform(
            fn (Review $review) => $review->setAttribute('verified_buyer', $verifiedIds->contains($review->user_id))
        );

        return ReviewResource::collection($reviews);
    }

    /**
     * ثبت نظر جدید — همیشه «در انتظار تأیید» شروع می‌شود؛
     * یک نظر برای هر کاربر/محصول (کلید یکتای دیتابیس هم پشتیبان است).
     */
    public function store(ReviewRequest $request, Product $product): JsonResponse
    {
        abort_unless($product->is_active, 404);

        $user = $request->user();

        if ($product->reviews()->where('user_id', $user->getAuthIdentifier())->exists()) {
            throw ValidationException::withMessages([
                'rating' => ['شما قبلاً برای این محصول نظر ثبت کرده‌اید.'],
            ]);
        }

        try {
            $product->reviews()->create([
                'user_id' => $user->getAuthIdentifier(),
                'rating' => $request->validated('rating'),
                'comment' => trim((string) $request->validated('comment')),
                'status' => Review::STATUS_PENDING,
            ]);
        } catch (QueryException) {
            // ثبت همزمان از دو دستگاه — کلید یکتا برخورد کرد
            throw ValidationException::withMessages([
                'rating' => ['شما قبلاً برای این محصول نظر ثبت کرده‌اید.'],
            ]);
        }

        return response()->json([
            'message' => 'نظر شما ثبت شد و پس از تأیید، برای خریداران نمایش داده می‌شود.',
        ], 201);
    }
}

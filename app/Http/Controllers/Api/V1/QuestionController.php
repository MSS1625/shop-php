<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Product;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QuestionController extends Controller
{
    /**
     * پرسش‌های پاسخ‌داده‌شدهٔ یک محصول — پرسش بی‌جواب هرگز
     * بیرون از پنل مدیریت دیده نمی‌شود (همان قاعدهٔ وب).
     */
    public function index(Request $request, Product $product): AnonymousResourceCollection
    {
        abort_unless($product->is_active, 404);

        $questions = $product->answeredQuestions()
            ->with('user:id,name')
            ->latest()
            ->paginate(10);

        $verifiedIds = $product->verifiedBuyerIds($questions->pluck('user_id'));
        $questions->getCollection()->transform(
            fn (Question $question) => $question->setAttribute('verified_buyer', $verifiedIds->contains($question->user_id))
        );

        return QuestionResource::collection($questions);
    }

    /**
     * ثبت پرسش جدید — «در انتظار پاسخ» شروع می‌شود و پس از
     * پاسخ مدیر عمومی می‌شود و به پرسنده ایمیل می‌رود.
     */
    public function store(QuestionRequest $request, Product $product): JsonResponse
    {
        abort_unless($product->is_active, 404);

        $product->questions()->create([
            'user_id' => $request->user()->getAuthIdentifier(),
            'body' => trim((string) $request->validated('body')),
            'status' => Question::STATUS_PENDING,
        ]);

        return response()->json([
            'message' => 'پرسش شما ثبت شد و پس از پاسخ تیم فروشگاه، برای عموم نمایش داده می‌شود.',
        ], 201);
    }
}

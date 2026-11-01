<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * محصول — برای موبایل: مقدار خام (قیمت/موجودی به عدد) و
     * نسخهٔ نمایشی فارسی (price_fa) کنار هم.
     *
     * فیلدهای صفحهٔ جزئیات (گالری، مشابه‌ها، نظرات، پرسش‌ها، تفکیک امتیاز)
     * فقط وقتی بارگذاری شده باشند ظاهر می‌شوند — لیست سبک می‌ماند.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'price_fa' => fa_price($this->price),
            'stock' => $this->stock,
            'in_stock' => $this->inStock(),
            'image_url' => $this->imageUrl(),

            'category' => new CategoryResource($this->whenLoaded('category')),

            // خلاصهٔ امتیاز — فقط وقتی withRating/loadAvg اجرا شده
            'rating' => $this->when($this->reviews_count !== null, [
                'average' => $this->avg_rating !== null ? round((float) $this->avg_rating, 1) : null,
                'average_fa' => $this->avg_rating !== null ? $this->avgRatingFa() : null,
                'count' => (int) $this->reviews_count,
            ]),

            // فقط برای کاربر واردشده — از قبل توسط کنترلر محاسبه می‌شود (بدون N+1)
            'is_in_wishlist' => $this->when(isset($this->resource->is_in_wishlist),
                (bool) $this->resource->is_in_wishlist),

            // ---- فقط صفحهٔ جزئیات ----

            'images' => ProductImageResource::collection($this->whenLoaded('images')),

            'rating_breakdown' => $this->when(isset($this->resource->rating_breakdown),
                fn () => $this->resource->rating_breakdown),

            'related' => self::collection($this->whenLoaded('related')),

            'reviews' => $this->whenLoaded('reviews_page', fn () => [
                'data' => ReviewResource::collection($this->reviews_page->items()),
                'meta' => [
                    'current_page' => $this->reviews_page->currentPage(),
                    'last_page' => $this->reviews_page->lastPage(),
                    'per_page' => $this->reviews_page->perPage(),
                    'total' => $this->reviews_page->total(),
                ],
            ]),

            'questions' => $this->whenLoaded('questions_page', fn () => [
                'data' => QuestionResource::collection($this->questions_page->items()),
                'meta' => [
                    'current_page' => $this->questions_page->currentPage(),
                    'last_page' => $this->questions_page->lastPage(),
                    'per_page' => $this->questions_page->perPage(),
                    'total' => $this->questions_page->total(),
                ],
            ]),
        ];
    }
}

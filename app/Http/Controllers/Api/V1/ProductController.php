<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductFilterRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    /**
     * لیست محصولات — همان فیلترهای صفحهٔ وب (جستجو، دسته، بازهٔ قیمت،
     * موجودی، مرتب‌سازی) با همان نرمال‌سازی ارقام فارسی؛ ۱۲ محصول در صفحه.
     */
    public function index(ProductFilterRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();

        $products = Product::with('category')
            ->withRating()
            ->active()
            ->search($filters['term'])
            ->when(
                $filters['category'],
                fn ($q, $slug) => $q->whereRelation('category', 'slug', $slug)
            )
            ->priceBetween($filters['min'], $filters['max'])
            ->when($filters['inStock'], fn ($q) => $q->inStock())
            ->ordered($filters['sort'])
            ->paginate(12)
            ->withQueryString();

        $this->markWishlist($request, $products->getCollection());

        return ProductResource::collection($products);
    }

    /**
     * جزئیات محصول — یک فراخوانی برای یک صفحهٔ کامل موبایل:
     * گالری، خلاصه و تفکیک امتیازها، اولین صفحهٔ نظرات و پرسش‌ها
     * (با نشان خریدار تأییدشدهٔ یک-کوئری) و ۴ محصول مشابه.
     */
    public function show(Request $request, Product $product): ProductResource
    {
        abort_unless($product->is_active, 404);

        $product->load('category', 'images');

        // میانگین امتیاز و تعداد نظرات تأییدشده — همان دو subquery وب
        $product
            ->loadCount(['reviews as reviews_count' => fn ($q) => $q
                ->where('status', Review::STATUS_APPROVED)])
            ->loadAvg(['reviews as avg_rating' => fn ($q) => $q
                ->where('status', Review::STATUS_APPROVED)], 'rating');

        // تفکیک امتیازها برای نمودار ۵ → ۱ (یک کوئری گروهی)
        // Collection پاس می‌شود نه آرایه — JSON Resource آرایه‌های با کلید عددی
        // را re-index می‌کند و نقشهٔ «۵★ → تعداد» کلیدهایش را از دست می‌دهد
        $product->setAttribute('rating_breakdown', collect($product->ratingCounts()));

        // اولین صفحهٔ نظرات تأییدشده + نویسنده‌ها
        $reviews = $product->approvedReviews()
            ->with('user:id,name')
            ->latest()
            ->paginate(5);

        $verifiedIds = $product->verifiedBuyerIds($reviews->pluck('user_id'));
        $reviews->getCollection()->transform(
            fn (Review $review) => $review->setAttribute('verified_buyer', $verifiedIds->contains($review->user_id))
        );

        $product->setRelation('reviews_page', $reviews);

        // اولین صفحهٔ پرسش‌های پاسخ‌داده‌شده + پرسنده‌ها — همان یک-کوئری
        $questions = $product->answeredQuestions()
            ->with('user:id,name')
            ->latest()
            ->paginate(5);

        $questionBuyerIds = $product->verifiedBuyerIds($questions->pluck('user_id'));
        $questions->getCollection()->transform(
            fn ($question) => $question->setAttribute('verified_buyer', $questionBuyerIds->contains($question->user_id))
        );

        $product->setRelation('questions_page', $questions);

        // محصولات مشابه — هم‌دسته، فعال، اول موجودها؛ همان کوئری وب
        $related = Product::with('category')
            ->withRating()
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByRaw('stock > 0 DESC')
            ->latest()
            ->take(4)
            ->get();

        $this->markWishlist($request, $related);
        $product->setRelation('related', $related);

        // خود محصول هم برای کاربر واردشده پرچم علاقه‌مندی می‌گیرد
        $this->markWishlist($request, collect([$product]));

        return new ProductResource($product);
    }

    /**
     * پرچم علاقه‌مندی روی مجموعهٔ محصولات — یک کوئری برای کل صفحه، بدون N+1
     */
    private function markWishlist(Request $request, $products): void
    {
        $user = $request->user();

        if (! $user) {
            return;
        }

        $ids = $user->wishlistProducts()
            ->where('is_active', true)
            ->pluck('products.id');

        $products->each(
            fn (Product $product) => $product->setAttribute('is_in_wishlist', $ids->contains($product->id))
        );
    }
}

<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductFilterRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    /**
     * لیست محصولات با جستجو، فیلتر دسته/بازهٔ قیمت/موجودی و مرتب‌سازی
     */
    public function index(ProductFilterRequest $request): View
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

        $categories = Category::hasActiveProducts()
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();

        return view('shop.products.index', [
            'products' => $products,
            'categories' => $categories,
            'activeCategory' => $categories->firstWhere('slug', $filters['category']),
            'term' => $filters['term'],
            'sort' => $filters['sort'],
            'min' => $filters['min'],
            'max' => $filters['max'],
            'inStock' => $filters['inStock'],
        ]);
    }

    /**
     * صفحه جزئیات محصول + محصولات مشابه + نظرات تأییدشده
     * مشابه‌ها: هم‌دسته، فعال، به‌جز خود محصول — اول محصولات موجود، بعد جدید‌ترین‌ها
     */
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load('category', 'images');

        // میانگین امتیاز و تعداد نظرات تأییدشده (برای کارت بالای صفحه)
        $product
            ->loadCount(['reviews as reviews_count' => fn ($q) => $q
                ->where('status', Review::STATUS_APPROVED)])
            ->loadAvg(['reviews as avg_rating' => fn ($q) => $q
                ->where('status', Review::STATUS_APPROVED)], 'rating');

        // نظرات تأییدشده + نویسنده — صفحه‌بندی جدا از خلاصه
        $reviews = $product->approvedReviews()
            ->with('user:id,name')
            ->latest()
            ->paginate(5);

        // تفکیک امتیازها برای نمودار ۵ → ۱ (یک کوئری گروهی)
        $breakdown = $product->ratingCounts();

        // خریداران تأییدشدهٔ همین صفحه — یک کوئری، بدون N+1
        $verifiedBuyerIds = $product->verifiedBuyerIds($reviews->pluck('user_id'));

        // نظر خودِ کاربر فعلی — به‌جای فرم، وضعیتش نمایش داده می‌شود
        $userReview = auth()->check()
            ? $product->reviews()->where('user_id', auth()->id())->first()
            : null;

        $related = Product::with('category')
            ->withRating()
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByRaw('stock > 0 DESC')
            ->latest()
            ->take(4)
            ->get();

        return view('shop.products.show', compact(
            'product', 'related', 'reviews', 'breakdown', 'verifiedBuyerIds', 'userReview'
        ));
    }
}

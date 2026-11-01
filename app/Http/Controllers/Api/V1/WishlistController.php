<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WishlistController extends Controller
{
    /**
     * علاقه‌مندی‌های کاربر — فقط محصولات فعال، جدیدترین ذخیره اول
     * (همان ترتیب صفحهٔ /wishlist وب)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = $request->user()
            ->wishlistProducts()
            ->where('is_active', true)
            ->with('category')
            ->withRating()
            ->paginate(12);

        // همهٔ این صفحه در علاقه‌مندی است — پرچم یک‌کوئریِ لازم هم ندارد
        $products->getCollection()->each(
            fn (Product $product) => $product->setAttribute('is_in_wishlist', true)
        );

        return ProductResource::collection($products);
    }

    /**
     * افزودن به علاقه‌مندی‌ها — idempotent: افزودن دوباره خطا نیست؛
     * پاسخ همیشه وضعیت نهایی را برمی‌گرداند.
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        abort_unless($product->is_active, 404);

        $request->user()->wishlistProducts()->syncWithoutDetaching([$product->id]);

        return response()->json([
            'message' => 'محصول به علاقه‌مندی‌های شما اضافه شد.',
            'data' => [
                'slug' => $product->slug,
                'is_in_wishlist' => true,
            ],
        ]);
    }

    /**
     * حذف از علاقه‌مندی‌ها — idempotent: حذف چیزی که نیست هم موفق است
     */
    public function destroy(Request $request, Product $product): JsonResponse
    {
        $request->user()->wishlistProducts()->detach($product->id);

        return response()->json([
            'message' => 'محصول از علاقه‌مندی‌های شما حذف شد.',
            'data' => [
                'slug' => $product->slug,
                'is_in_wishlist' => false,
            ],
        ]);
    }
}

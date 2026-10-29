<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist as WishlistItem;
use App\Services\Wishlist;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * صفحهٔ علاقه‌مندی‌ها — فقط محصولات فعال،
     * جدیدترین ذخیره‌شده اول (همان ترتیب رابطهٔ پیوت)
     */
    public function index(Request $request): View
    {
        $products = $request->user()
            ->wishlistProducts()
            ->with('category')
            ->withRating()
            ->where('is_active', true)
            ->paginate(12);

        return view('shop.wishlist', ['products' => $products]);
    }

    /**
     * افزودن یا حذف یک محصول با یک کلیک — همیشه PRG؛
     * درخواست‌های Ajax پاسخ JSON می‌گیرند تا قلب همان‌جا بچرخد
     */
    public function toggle(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        abort_unless($product->is_active, 404);

        $user = $request->user();

        $existing = WishlistItem::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->where('product_id', $product->getKey())
            ->first();

        if ($existing) {
            $existing->delete();
            $added = false;
            $message = '«'.$product->title.'» از علاقه‌مندی‌ها حذف شد.';
        } else {
            WishlistItem::create([
                'user_id' => $user->getAuthIdentifier(),
                'product_id' => $product->getKey(),
            ]);
            $added = true;
            $message = '«'.$product->title.'» به علاقه‌مندی‌ها اضافه شد.';
        }

        // شمارندهٔ تازه برای پاسخ Ajax — حافظهٔ سرویس همین‌جا باطل می‌شود
        app(Wishlist::class)->forget();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'inWishlist' => $added,
                'count' => app(Wishlist::class)->count(),
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}

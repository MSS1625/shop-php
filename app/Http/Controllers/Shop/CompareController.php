<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Question;
use App\Services\Compare;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function __construct(protected Compare $compare) {}

    /**
     * صفحهٔ مقایسه — جدول کنار-به-کنار حداکثر ۴ محصول؛
     * امتیاز و تعداد پرسش‌های پاسخ‌داده با subquery کنار لیست (بدون N+1)
     */
    public function index(): View
    {
        $products = $this->compare->products();

        // ستون‌های مقایسه با subquery کنار لیست — بدون N+1
        if ($products->isNotEmpty()) {
            $ids = $products->pluck('id')->all();

            $products = Product::query()
                ->with('category')
                ->withRating()
                ->withCount(['questions as answered_questions' => fn ($q) => $q
                    ->where('status', Question::STATUS_ANSWERED)])
                ->whereKey($ids)
                ->get()
                ->sortBy(fn (Product $p) => array_search($p->id, $ids))
                ->values();
        }

        $cheapest = $products->isNotEmpty() ? $products->min('price') : null;

        return view('shop.compare', [
            'products' => $products,
            'cheapest' => $cheapest,
        ]);
    }

    /**
     * افزودن یا حذف یک محصول با یک کلیک — همیشه PRG؛
     * درخواست‌های Ajax پاسخ JSON می‌گیرند تا ترازو همان‌جا بچرخد.
     * فهرست پر باشد محصول اضافه نمی‌شود و پیام هشدار می‌گیرد
     */
    public function toggle(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        abort_unless($product->is_active, 404);

        $result = $this->compare->toggle($product);

        $message = match ($result) {
            'added' => '«'.$product->title.'» به فهرست مقایسه اضافه شد.',
            'removed' => '«'.$product->title.'» از فهرست مقایسه حذف شد.',
            default => 'فهرست مقایسه پر است — حداکثر '.fa_num(Compare::MAX).' محصول هم‌زمان قابل مقایسه است.',
        };

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'inCompare' => $result === 'added',
                'full' => $result === 'full',
                'count' => $this->compare->count(),
                'message' => $message,
            ]);
        }

        return redirect()
            ->back()
            ->with($result === 'full' ? 'warning' : 'success', $message);
    }

    /**
     * خالی کردن کل فهرست — از خود صفحهٔ مقایسه
     */
    public function clear(): RedirectResponse
    {
        $this->compare->clear();

        return redirect()
            ->route('compare.index')
            ->with('success', 'فهرست مقایسه خالی شد.');
    }
}

<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductFilterRequest;
use App\Models\Category;
use App\Models\Product;
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
     * صفحه جزئیات محصول + محصولات مشابه
     * مشابه‌ها: هم‌دسته، فعال، به‌جز خود محصول — اول محصولات موجود، بعد جدید‌ترین‌ها
     */
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load('category', 'images');

        $related = Product::with('category')
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByRaw('stock > 0 DESC')
            ->latest()
            ->take(4)
            ->get();

        return view('shop.products.show', compact('product', 'related'));
    }
}

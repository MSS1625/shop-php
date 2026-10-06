<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * لیست محصولات با جستجو، فیلتر دسته و مرتب‌سازی
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'exists:categories,slug'],
            'sort' => ['nullable', 'in:newest,oldest,cheapest,expensive'],
        ]);

        $term = trim($validated['q'] ?? '');
        $sort = $validated['sort'] ?? 'newest';

        $products = Product::with('category')
            ->active()
            ->search($term)
            ->when(
                $validated['category'] ?? null,
                fn ($q, $slug) => $q->whereRelation('category', 'slug', $slug)
            )
            ->ordered($sort)
            ->paginate(12)
            ->withQueryString();

        $categories = Category::hasActiveProducts()
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();

        return view('shop.products.index', [
            'products' => $products,
            'categories' => $categories,
            'activeCategory' => $categories->firstWhere('slug', $validated['category'] ?? null),
            'term' => $term,
            'sort' => $sort,
        ]);
    }

    /**
     * صفحه جزئیات محصول + محصولات مشابه
     */
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load('category', 'images');

        $related = Product::with('category')
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('shop.products.show', compact('product', 'related'));
    }
}

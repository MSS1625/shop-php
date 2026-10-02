<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    /**
     * صفحه اصلی فروشگاه
     */
    public function index()
    {
        $categories = Category::hasActiveProducts()
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();

        $latestProducts = Product::with('category')
            ->active()
            ->latest()
            ->take(8)
            ->get();

        return view('shop.home', compact('categories', 'latestProducts'));
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /**
     * دسته‌بندی‌هایی که محصول فعال دارند + تعداد محصولات هر کدام
     * — همان کوئری نوار دسته‌بندی صفحهٔ محصولات وب
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::hasActiveProducts()
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }
}

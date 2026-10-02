<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * لیست محصولات با جستجو و صفحه‌بندی
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $products = Product::with('category')
            ->search(trim($validated['q'] ?? ''))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'term' => trim($validated['q'] ?? ''),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(['stock' => 1, 'is_active' => true]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * ذخیره محصول جدید با آپلود امن تصویر
     */
    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validatedData();
        $data['slug'] = Product::generateUniqueSlug($data['title']);

        if ($request->hasFile('image')) {
            // نام تصادفی + ذخیره در storage (خارج از دسترسی اجرای مستقیم)
            $data['image'] = $request->file('image')->hashName();
            Storage::disk('public')->putFileAs('products', $request->file('image'), $data['image']);
        }

        Product::create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'محصول «'.$data['title'].'» با موفقیت ثبت شد.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * بروزرسانی محصول — حذف تصویر قبلی در صورت تعویض
     */
    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validatedData();

        if ($data['title'] !== $product->title) {
            $data['slug'] = Product::generateUniqueSlug($data['title']);
        }

        if ($request->hasFile('image')) {
            // حذف تصویر قدیمی برای جلوگیری از فایل‌های یتیم
            if ($product->image) {
                Storage::disk('public')->delete('products/'.$product->image);
            }

            $data['image'] = $request->file('image')->hashName();
            Storage::disk('public')->putFileAs('products', $request->file('image'), $data['image']);
        }

        $product->update($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'محصول با موفقیت بروزرسانی شد.');
    }

    /**
     * حذف محصول به‌همراه تصویرش
     */
    public function destroy(Product $product): RedirectResponse
    {
        $title = $product->title;

        if ($product->image) {
            Storage::disk('public')->delete('products/'.$product->image);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'محصول «'.$title.'» حذف شد.');
    }
}

@extends('layouts.shop')

@section('title', 'علاقه‌مندی‌ها')

@section('meta_description', 'محصولات ذخیره‌شدهٔ شما در دیجی‌شاپ — فهرست علاقه‌مندی‌ها')

@section('content')
    <section class="container mt-4" data-wishlist-page>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <h1 class="ds-section-title mb-0">
                <i class="fa-solid fa-heart ms-2"></i>علاقه‌مندی‌ها
            </h1>
            <span class="text-muted">
                {{ fa_num($products->total()) }} محصول ذخیره‌شده
            </span>
        </div>

        @if($products->isNotEmpty())
            <div class="row g-4">
                @foreach($products as $product)
                    @include('shop.partials.product-card', ['product' => $product])
                @endforeach
            </div>

            <div class="mt-4">
                {{ $products->links() }}
            </div>
        @else
            <div class="ds-card">
                <div class="ds-empty">
                    <i class="fa-regular fa-heart"></i>
                    <p class="mb-2 fs-5">هنوز محصولی به علاقه‌مندی‌ها اضافه نکرده‌اید.</p>
                    <p class="mb-4">با زدن آیکون قلب روی هر محصول، آن را برای بعد ذخیره کنید.</p>
                    <a href="{{ route('shop.products.index') }}" class="btn btn-ds">
                        <i class="fa-solid fa-store ms-1"></i> رفتن به محصولات
                    </a>
                </div>
            </div>
        @endif
    </section>
@endsection

@extends('layouts.shop')

@section('title', 'مقایسهٔ محصولات')

@section('meta_description', 'مقایسهٔ کنار-به-کنار محصولات دیجی‌شاپ — قیمت، موجودی، امتیاز کاربران و پرسش‌های پاسخ‌داده')

@section('content')
    <section class="container mt-4" data-compare-page>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <h1 class="ds-section-title mb-0">
                <i class="fa-solid fa-scale-balanced ms-2"></i>مقایسهٔ محصولات
            </h1>

            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">
                    {{ fa_num($products->count()) }} از {{ fa_num(\App\Services\Compare::MAX) }} محصول
                </span>

                @if($products->isNotEmpty())
                    <form action="{{ route('compare.clear') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-ds-outline btn-sm">
                            <i class="fa-solid fa-eraser ms-1"></i> خالی کردن فهرست
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if($products->isNotEmpty())
            <div class="ds-card p-3 p-md-4">
                <div class="table-responsive">
                    <table class="table ds-compare-table align-middle mb-0">
                        <tbody>
                            {{-- ردیف محصول: تصویر + عنوان + حذف از فهرست --}}
                            <tr>
                                <th class="ds-compare-label" scope="row">محصول</th>
                                @foreach($products as $product)
                                    <td class="ds-compare-head">
                                        <a href="{{ route('shop.products.show', $product) }}" class="ds-compare-thumb">
                                            <img src="{{ $product->imageUrl() }}" alt="{{ $product->title }}" loading="lazy">
                                        </a>
                                        <a href="{{ route('shop.products.show', $product) }}" class="ds-compare-title">
                                            {{ $product->title }}
                                        </a>
                                        <form action="{{ route('compare.toggle', $product) }}" method="POST"
                                              data-compare-toggle data-compare-product="{{ $product->slug }}">
                                            @csrf
                                            <button type="submit" class="ds-compare-remove"
                                                    aria-label="حذف از مقایسه" title="حذف از مقایسه">
                                                <i class="fa-solid fa-xmark"></i> حذف از فهرست
                                            </button>
                                        </form>
                                    </td>
                                @endforeach
                            </tr>

                            {{-- قیمت — ارزان‌ترین ستون برجسته می‌شود --}}
                            <tr>
                                <th class="ds-compare-label" scope="row">قیمت</th>
                                @foreach($products as $product)
                                    <td class="text-center {{ $cheapest !== null && $product->price === $cheapest ? 'ds-compare-best' : '' }}">
                                        <div class="ds-compare-price">
                                            {{ fa_price($product->price) }} <small class="text-muted">تومان</small>
                                        </div>
                                        @if($cheapest !== null && $product->price === $cheapest)
                                            <span class="ds-compare-best-badge">
                                                <i class="fa-solid fa-tag ms-1"></i> بهترین قیمت
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- دسته‌بندی --}}
                            <tr>
                                <th class="ds-compare-label" scope="row">دسته‌بندی</th>
                                @foreach($products as $product)
                                    <td class="text-center">
                                        {{ $product->category?->name ?? '—' }}
                                    </td>
                                @endforeach
                            </tr>

                            {{-- موجودی انبار --}}
                            <tr>
                                <th class="ds-compare-label" scope="row">موجودی انبار</th>
                                @foreach($products as $product)
                                    <td class="text-center">
                                        @if($product->inStock())
                                            <span class="text-success-emphasis">
                                                <i class="fa-solid fa-circle-check ms-1"></i>
                                                {{ fa_num($product->stock) }} عدد
                                            </span>
                                        @else
                                            <span class="text-danger"><i class="fa-solid fa-circle-xmark ms-1"></i> ناموجود</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- امتیاز کاربران --}}
                            <tr>
                                <th class="ds-compare-label" scope="row">امتیاز کاربران</th>
                                @foreach($products as $product)
                                    <td class="text-center">
                                        @if($product->hasRating())
                                            <span class="fw-bold">{{ $product->avgRatingFa() }}</span>
                                            <i class="fa-solid fa-star ds-compare-star ms-1"></i>
                                            <div class="text-muted small mt-1">({{ fa_num($product->reviews_count) }} نظر)</div>
                                        @else
                                            <span class="text-muted">هنوز نظری ندارد</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- پرسش‌های پاسخ‌داده --}}
                            <tr>
                                <th class="ds-compare-label" scope="row">پرسش‌های پاسخ‌داده</th>
                                @foreach($products as $product)
                                    <td class="text-center">
                                        {{ fa_num($product->answered_questions) }} پاسخ
                                    </td>
                                @endforeach
                            </tr>

                            {{-- افزودن به سبد --}}
                            <tr>
                                <th class="ds-compare-label" scope="row">خرید</th>
                                @foreach($products as $product)
                                    <td class="text-center">
                                        @if($product->inStock())
                                            <button class="btn btn-ds btn-sm"
                                                    data-add-to-cart="{{ route('cart.store', $product) }}">
                                                <i class="fa-solid fa-cart-plus ms-1"></i> افزودن به سبد
                                            </button>
                                        @else
                                            <button class="btn btn-ds btn-sm" disabled>
                                                <i class="fa-solid fa-ban ms-1"></i> ناموجود
                                            </button>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="text-muted small mt-3 mb-0">
                <i class="fa-solid fa-circle-info ms-1"></i>
                برای مقایسهٔ محصولات دیگر، روی صفحهٔ هر محصول دکمهٔ ترازو را بزنید — حداکثر {{ fa_num(\App\Services\Compare::MAX) }} محصول هم‌زمان.
            </p>
        @else
            <div class="ds-card">
                <div class="ds-empty">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <p class="mb-2 fs-5">هنوز محصولی برای مقایسه انتخاب نکرده‌اید.</p>
                    <p class="mb-4">با زدن آیکون ترازو روی هر محصول، آن را به فهرست مقایسه اضافه کنید.</p>
                    <a href="{{ route('shop.products.index') }}" class="btn btn-ds">
                        <i class="fa-solid fa-store ms-1"></i> رفتن به محصولات
                    </a>
                </div>
            </div>
        @endif
    </section>
@endsection

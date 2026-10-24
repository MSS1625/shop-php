@extends('layouts.shop')

@section('title', $activeCategory ? $activeCategory->name : 'محصولات')

@php
    // پارامترهای مشترک فیلترها — در همهٔ لینک‌ها (دسته‌ها، صفحه‌بندی و…) حفظ می‌شوند
    $filterParams = array_filter([
        'q' => $term,
        'min' => $min,
        'max' => $max,
        'in_stock' => $inStock ? '1' : null,
    ], fn ($v) => $v !== null && $v !== '');
@endphp

@section('content')
    <section class="container mt-4">
        {{-- عنوان و جستجو --}}
        <div class="ds-page-head">
            <div>
                <h1 class="ds-page-title">
                    @if($activeCategory)
                        {{ $activeCategory->name }}
                    @elseif($term)
                        نتایج جستجوی «{{ $term }}»
                    @else
                        همه محصولات
                    @endif
                </h1>
                <small class="text-muted">
                    {{ fa_num($products->total()) }} محصول یافت شد
                    @if($min !== null || $max !== null || $inStock)
                        <span class="ds-filter-on">· فیلتر فعال</span>
                    @endif
                </small>
            </div>

            <form class="ds-search d-none d-md-block" action="{{ route('shop.products.index') }}" method="GET" role="search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" name="q" value="{{ $term }}" placeholder="جستجوی محصول...">
            </form>
        </div>

        {{-- فیلتر دسته + مرتب‌سازی --}}
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <a href="{{ route('shop.products.index', $filterParams) }}"
               class="btn btn-sm {{ $activeCategory ? 'btn-ds-ghost' : 'btn-ds' }} rounded-pill">
                همه
            </a>
            @foreach($categories as $category)
                <a href="{{ route('shop.products.index', array_merge($filterParams, ['category' => $category->slug])) }}"
                   class="btn btn-sm {{ $activeCategory?->id === $category->id ? 'btn-ds' : 'btn-ds-ghost' }} rounded-pill">
                    <i class="fa-solid {{ $category->icon ?: 'fa-tag' }} ms-1"></i>
                    {{ $category->name }}
                </a>
            @endforeach

            <form action="{{ route('shop.products.index') }}" method="GET" class="d-flex gap-2 ms-auto">
                @if($term)<input type="hidden" name="q" value="{{ $term }}">@endif
                @if($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory->slug }}">@endif
                @if($min !== null)<input type="hidden" name="min" value="{{ $min }}">@endif
                @if($max !== null)<input type="hidden" name="max" value="{{ $max }}">@endif
                @if($inStock)<input type="hidden" name="in_stock" value="1">@endif
                <select name="sort" class="form-select form-select-sm" style="min-width:170px" onchange="this.form.submit()" aria-label="مرتب‌سازی">
                    <option value="newest" @selected($sort === 'newest')>جدیدترین</option>
                    <option value="oldest" @selected($sort === 'oldest')>قدیمی‌ترین</option>
                    <option value="cheapest" @selected($sort === 'cheapest')>ارزان‌ترین</option>
                    <option value="expensive" @selected($sort === 'expensive')>گران‌ترین</option>
                    <option value="popular" @selected($sort === 'popular')>پرفروش‌ترین</option>
                </select>
            </form>
        </div>

        {{-- فیلتر بازهٔ قیمت و موجودی --}}
        <div class="ds-filter mb-4">
            <form action="{{ route('shop.products.index') }}" method="GET" class="row g-2 g-md-3 align-items-center">
                @if($term)<input type="hidden" name="q" value="{{ $term }}">@endif
                @if($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory->slug }}">@endif
                <input type="hidden" name="sort" value="{{ $sort }}">

                <div class="col-12 col-md-auto">
                    <span class="ds-filter-title"><i class="fa-solid fa-sliders ms-1"></i> فیلترها</span>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="ds-filter-label" for="priceMin">حداقل قیمت (تومان)</label>
                    <input type="text" inputmode="numeric" id="priceMin" name="min"
                           value="{{ $min !== null ? fa_num($min) : '' }}"
                           class="form-control form-control-sm ds-filter-input" placeholder="مثلاً ۵۰۰٬۰۰۰">
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="ds-filter-label" for="priceMax">حداکثر قیمت (تومان)</label>
                    <input type="text" inputmode="numeric" id="priceMax" name="max"
                           value="{{ $max !== null ? fa_num($max) : '' }}"
                           class="form-control form-control-sm ds-filter-input" placeholder="مثلاً ۵٬۰۰۰٬۰۰۰">
                </div>
                <div class="col-12 col-md-auto">
                    <div class="form-check ds-filter-check">
                        <input class="form-check-input" type="checkbox" name="in_stock" value="1" id="inStockFilter" @checked($inStock)>
                        <label class="form-check-label" for="inStockFilter">فقط کالاهای موجود</label>
                    </div>
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-ds">
                        <i class="fa-solid fa-filter ms-1"></i> اعمال
                    </button>
                    @if($min !== null || $max !== null || $inStock)
                        <a href="{{ route('shop.products.index', array_filter(['q' => $term, 'category' => $activeCategory?->slug, 'sort' => $sort !== 'newest' ? $sort : null], fn ($v) => $v !== null && $v !== '')) }}"
                           class="btn btn-sm btn-ds-ghost">
                            <i class="fa-solid fa-xmark ms-1"></i> حذف فیلترها
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- جستجوی موبایل --}}
        <form action="{{ route('shop.products.index') }}" method="GET" class="ds-search d-md-none mb-4 w-100">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" name="q" value="{{ $term }}" placeholder="جستجوی محصول..." style="width:100%">
        </form>

        {{-- شبکه محصولات --}}
        @if($products->isNotEmpty())
            <div class="row g-3 g-md-4">
                @foreach($products as $product)
                    @include('shop.partials.product-card', ['product' => $product])
                @endforeach
            </div>

            {{-- صفحه‌بندی --}}
            <div class="mt-4 d-flex justify-content-center">
                {{ $products->onEachSide(1)->links() }}
            </div>
        @else
            <div class="ds-empty">
                <i class="fa-solid fa-magnifying-glass"></i>
                <p>محصولی مطابق جستجوی شما پیدا نشد.</p>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    @if($min !== null || $max !== null || $inStock)
                        <a href="{{ route('shop.products.index', array_filter(['q' => $term, 'category' => $activeCategory?->slug], fn ($v) => $v !== null && $v !== '')) }}" class="btn btn-ds-outline mt-2">
                            <i class="fa-solid fa-xmark ms-1"></i> حذف فیلترها
                        </a>
                    @endif
                    <a href="{{ route('shop.products.index') }}" class="btn btn-ds-outline mt-2">
                        <i class="fa-solid fa-rotate-right ms-1"></i> نمایش همه محصولات
                    </a>
                </div>
            </div>
        @endif
    </section>
@endsection

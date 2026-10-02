@extends('layouts.shop')

@section('title', $activeCategory ? $activeCategory->name : 'محصولات')

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
                <small class="text-muted">{{ fa_num($products->total()) }} محصول یافت شد</small>
            </div>

            <form class="ds-search d-none d-md-block" action="{{ route('shop.products.index') }}" method="GET" role="search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" name="q" value="{{ $term }}" placeholder="جستجوی محصول...">
            </form>
        </div>

        {{-- فیلتر دسته + مرتب‌سازی --}}
        <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
            <a href="{{ route('shop.products.index', array_filter(['q' => $term])) }}"
               class="btn btn-sm {{ $activeCategory ? 'btn-ds-ghost' : 'btn-ds' }} rounded-pill">
                همه
            </a>
            @foreach($categories as $category)
                <a href="{{ route('shop.products.index', array_filter(['category' => $category->slug, 'q' => $term])) }}"
                   class="btn btn-sm {{ $activeCategory?->id === $category->id ? 'btn-ds' : 'btn-ds-ghost' }} rounded-pill">
                    <i class="fa-solid {{ $category->icon ?: 'fa-tag' }} ms-1"></i>
                    {{ $category->name }}
                </a>
            @endforeach

            <form action="{{ route('shop.products.index') }}" method="GET" class="d-flex gap-2 ms-auto">
                @if($term)<input type="hidden" name="q" value="{{ $term }}">@endif
                @if($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory->slug }}">@endif
                <select name="sort" class="form-select form-select-sm" style="min-width:170px" onchange="this.form.submit()" aria-label="مرتب‌سازی">
                    <option value="newest" @selected($sort === 'newest')>جدیدترین</option>
                    <option value="oldest" @selected($sort === 'oldest')>قدیمی‌ترین</option>
                    <option value="cheapest" @selected($sort === 'cheapest')>ارزان‌ترین</option>
                    <option value="expensive" @selected($sort === 'expensive')>گران‌ترین</option>
                </select>
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
                <a href="{{ route('shop.products.index') }}" class="btn btn-ds-outline mt-2">
                    <i class="fa-solid fa-rotate-right ms-1"></i> نمایش همه محصولات
                </a>
            </div>
        @endif
    </section>
@endsection

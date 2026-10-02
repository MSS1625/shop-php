@extends('layouts.shop')

@section('title', 'فروشگاه آنلاین تجهیزات شبکه')
@section('meta_description', 'فروشگاه آنلاین تجهیزات شبکه — خرید آچار شبکه، مودم، سوییچ، کابل و ابزار شبکه با بهترین قیمت')

@section('content')
    {{-- هیرو --}}
    <section class="ds-hero">
        <div class="container position-relative">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <span class="ds-hero-badge mb-4">
                        <i class="fa-solid fa-circle-check"></i>
                        ارسال سریع به سراسر کشور
                    </span>
                    <h1 class="ds-hero-title mb-3">
                        تجهیزات شبکه را
                        <span class="grad">حرفه‌ای</span>
                        بخرید
                    </h1>
                    <p class="text-muted fs-5 mb-4">
                        از آچار شبکه تا سوییچ و فیبر نوری — همه چیز برای زیرساخت شبکه‌تان، با گارانتی اصالت کالا
                    </p>

                    <form action="{{ route('shop.products.index') }}" method="GET" class="ds-search mx-auto d-flex d-lg-none mb-3" style="max-width:420px">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="search" name="q" placeholder="جستجوی محصول..." aria-label="جستجو">
                    </form>

                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="{{ route('shop.products.index') }}" class="btn btn-ds px-4">
                            <i class="fa-solid fa-store ms-1"></i> مشاهده محصولات
                        </a>
                        <a href="#categories" class="btn btn-ds-ghost px-4">
                            <i class="fa-solid fa-layer-group ms-1"></i> دسته‌بندی‌ها
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- دسته‌بندی‌ها --}}
    @if($categories->isNotEmpty())
        <section id="categories" class="container mt-5">
            <h2 class="ds-section-title mb-4">خرید بر اساس دسته‌بندی</h2>
            <div class="row g-3">
                @foreach($categories as $category)
                    <div class="col-4 col-md-3 col-lg">
                        <a href="{{ route('shop.products.index', ['category' => $category->slug]) }}" class="ds-cat-card">
                            <i class="fa-solid {{ $category->icon ?: 'fa-tag' }}"></i>
                            <strong>{{ $category->name }}</strong>
                            <span class="ds-cat-count">{{ fa_num($category->products_count) }} محصول</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- جدیدترین محصولات --}}
    @if($latestProducts->isNotEmpty())
        <section class="container mt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="ds-section-title mb-0">جدیدترین محصولات</h2>
                <a href="{{ route('shop.products.index') }}" class="btn btn-ds-outline btn-sm">
                    مشاهده همه <i class="fa-solid fa-arrow-left me-1"></i>
                </a>
            </div>
            <div class="row g-3 g-md-4">
                @foreach($latestProducts as $product)
                    @include('shop.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @else
        <div class="container mt-5">
            <div class="ds-empty">
                <i class="fa-solid fa-box-open"></i>
                <p class="mb-0">هنوز محصولی ثبت نشده است.</p>
            </div>
        </div>
    @endif
@endsection

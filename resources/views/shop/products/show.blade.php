@extends('layouts.shop')

@section('title', $product->title)

@section('content')
    <section class="container mt-4">
        {{-- مسیر (breadcrumb) --}}
        <nav aria-label="مسیر" class="mb-4">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="{{ route('shop.home') }}" class="text-muted">خانه</a></li>
                <li class="breadcrumb-item">
                    <a href="{{ route('shop.products.index') }}" class="text-muted">محصولات</a>
                </li>
                @if($product->category)
                    <li class="breadcrumb-item">
                        <a href="{{ route('shop.products.index', ['category' => $product->category->slug]) }}" class="text-muted">
                            {{ $product->category->name }}
                        </a>
                    </li>
                @endif
                <li class="breadcrumb-item active text-light" aria-current="page">{{ $product->title }}</li>
            </ol>
        </nav>

        <div class="row g-4">
            {{-- تصویر + گالری --}}
            <div class="col-lg-6">
                <div class="ds-detail-img">
                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->title }}" data-gallery-main>
                </div>

                @if($product->images->isNotEmpty())
                    <div class="ds-gallery-thumbs mt-3" data-gallery-thumbs role="tablist" aria-label="گالری تصاویر محصول">
                        <button type="button" class="active" aria-label="تصویر اصلی">
                            <img src="{{ $product->imageUrl() }}" alt="تصویر اصلی {{ $product->title }}">
                        </button>
                        @foreach($product->images as $image)
                            <button type="button" aria-label="تصویر {{ fa_num($loop->iteration + 1) }}">
                                <img src="{{ $image->url() }}" alt="{{ $image->alt ?: $product->title }}">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- اطلاعات --}}
            <div class="col-lg-6">
                @if($product->category)
                    <span class="ds-product-cat mb-3 d-inline-block">
                        <i class="fa-solid {{ $product->category->icon ?: 'fa-tag' }} ms-1"></i>
                        {{ $product->category->name }}
                    </span>
                @endif

                <h1 class="fw-bold fs-3 mb-3">{{ $product->title }}</h1>

                <div class="mb-4">
                    <div class="ds-spec-row">
                        <span>موجودی انبار</span>
                        <span>
                            @if($product->inStock())
                                <span class="text-success-emphasis">
                                    <i class="fa-solid fa-circle-check ms-1"></i>
                                    {{ fa_num($product->stock) }} عدد
                                </span>
                            @else
                                <span class="text-danger"><i class="fa-solid fa-circle-xmark ms-1"></i> ناموجود</span>
                            @endif
                        </span>
                    </div>
                    <div class="ds-spec-row">
                        <span>شناسه محصول</span>
                        <span>{{ $product->slug }}</span>
                    </div>
                    <div class="ds-spec-row">
                        <span>ارسال</span>
                        <span class="text-success-emphasis"><i class="fa-solid fa-truck-fast ms-1"></i> ارسال سریع</span>
                    </div>
                </div>

                @if($product->description)
                    <p class="text-muted" style="line-height:2">{{ $product->description }}</p>
                @endif

                <div class="ds-price-box d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted small mb-1">قیمت محصول</div>
                        <div class="ds-price-big">
                            {{ fa_price($product->price) }} <small class="text-muted fs-6">تومان</small>
                        </div>
                    </div>

                    @if($product->inStock())
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="ds-qty" data-qty>
                                <button type="button" data-qty-minus><i class="fa-solid fa-minus"></i></button>
                                <input type="number" data-qty-input value="1" min="1" max="{{ min($product->stock, 99) }}" aria-label="تعداد">
                                <button type="button" data-qty-plus><i class="fa-solid fa-plus"></i></button>
                            </div>

                            <button class="btn btn-ds px-4"
                                    data-add-to-cart="{{ route('cart.store', $product) }}">
                                <i class="fa-solid fa-cart-plus ms-1"></i> افزودن به سبد
                            </button>
                        </div>
                    @else
                        <button class="btn btn-ds px-4" disabled>
                            <i class="fa-solid fa-ban ms-1"></i> ناموجود
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- محصولات مشابه --}}
        @if($related->isNotEmpty())
            <section class="mt-5">
                <h2 class="ds-section-title mb-4">محصولات مشابه</h2>
                <div class="row g-3 g-md-4">
                    @foreach($related as $item)
                        @include('shop.partials.product-card', ['product' => $item])
                    @endforeach
                </div>
            </section>
        @endif
    </section>
@endsection

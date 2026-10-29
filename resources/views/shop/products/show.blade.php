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

                {{-- امتیاز کلی + میانبر به بخش نظرات --}}
                @if($product->hasRating())
                    <a href="#reviews" class="ds-rating-link mb-3 d-inline-flex">
                        @include('shop.products.partials.stars', ['rating' => $product->avg_rating])
                        <strong>{{ $product->avgRatingFa() }}</strong>
                        <span class="text-muted">({{ fa_num($product->reviews_count) }} نظر)</span>
                    </a>
                @endif

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

                            @include('shop.partials.wishlist-button', ['big' => true])
                        </div>
                    @else
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <button class="btn btn-ds px-4" disabled>
                                <i class="fa-solid fa-ban ms-1"></i> ناموجود
                            </button>

                            @include('shop.partials.wishlist-button', ['big' => true])
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- نظرات و امتیازها --}}
        <section class="mt-5" id="reviews" aria-labelledby="reviews-title">
            <h2 class="ds-section-title mb-4" id="reviews-title">نظرات خریداران</h2>
            <div class="ds-card p-4">

                {{-- خلاصهٔ امتیازها --}}
                @if($product->hasRating())
                    <div class="ds-review-summary">
                        <div class="ds-review-avg">
                            <div class="ds-review-avg-num">{{ $product->avgRatingFa() }}</div>
                            @include('shop.products.partials.stars', ['rating' => $product->avg_rating])
                            <div class="text-muted small">{{ fa_num($product->reviews_count) }} نظر تأییدشده</div>
                        </div>
                        <div class="ds-review-bars">
                            @foreach([5, 4, 3, 2, 1] as $stars)
                                @php($count = $breakdown[$stars] ?? 0)
                                @php($percent = (int) round($count * 100 / max($product->reviews_count, 1)))
                                <div class="ds-review-bar-row">
                                    <span class="ds-review-bar-label">
                                        {{ fa_num($stars) }} <i class="fa-solid fa-star"></i>
                                    </span>
                                    <div class="ds-review-bar">
                                        <div class="ds-review-bar-fill" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <span class="ds-review-bar-count">{{ fa_num($count) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="ds-review-none">
                        <i class="fa-regular fa-comment-dots"></i>
                        هنوز نظری برای این محصول ثبت نشده است — اولین نفر باشید!
                    </div>
                @endif

                {{-- فهرست نظرات تأییدشده --}}
                @if($reviews->isNotEmpty())
                    <div class="ds-review-list">
                        @foreach($reviews as $review)
                            <article class="ds-review">
                                <header class="ds-review-head">
                                    <div class="d-flex align-items-center flex-wrap gap-2">
                                        <span class="ds-review-author">{{ $review->user?->name ?? 'کاربر حذف‌شده' }}</span>
                                        @if($verifiedBuyerIds->contains($review->user_id))
                                            <span class="ds-review-badge">
                                                <i class="fa-solid fa-circle-check ms-1"></i> خریدار تأییدشده
                                            </span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @include('shop.products.partials.stars', ['rating' => $review->rating])
                                        <time class="text-muted small" dir="ltr">{{ $review->created_at->format('Y-m-d') }}</time>
                                    </div>
                                </header>
                                <p class="ds-review-text mb-0">{{ $review->comment }}</p>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-3 d-flex justify-content-center">
                        {{ $reviews->onEachSide(1)->links() }}
                    </div>
                @endif

                {{-- ثبت نظر جدید --}}
                <div class="ds-review-form mt-4">
                    <h3 class="ds-review-form-title">ثبت نظر شما</h3>

                    @if(auth()->check())
                        @if($userReview)
                            <div class="ds-review-own">
                                <i class="fa-solid fa-clock ms-1"></i>
                                نظر شما با امتیاز {{ fa_num($userReview->rating) }} ثبت شده است —
                                وضعیت فعلی: <span class="badge text-bg-{{ $userReview->statusColor() }}">{{ $userReview->statusLabel() }}</span>
                            </div>
                        @else
                            <form action="{{ route('shop.reviews.store', $product) }}" method="POST" novalidate>
                                @csrf

                                <div class="mb-3">
                                    <label class="ds-filter-label d-block mb-2">امتیاز شما</label>
                                    <div class="ds-star-picker" data-star-picker>
                                        @foreach([5, 4, 3, 2, 1] as $star)
                                            <input type="radio"
                                                   name="rating"
                                                   value="{{ $star }}"
                                                   id="rating-{{ $star }}"
                                                   class="d-none"
                                                   @checked(old('rating') == $star)>
                                            <label for="rating-{{ $star }}" title="{{ fa_num($star) }} ستاره">
                                                <i class="fa-regular fa-star"></i>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('rating')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="comment" class="ds-filter-label d-block mb-2">دیدگاه شما</label>
                                    <textarea name="comment"
                                              id="comment"
                                              rows="4"
                                              class="ds-filter-input w-100"
                                              placeholder="تجربهٔ خود از این محصول را بنویسید…">{{ old('comment') }}</textarea>
                                    @error('comment')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button class="btn btn-ds px-4">
                                    <i class="fa-solid fa-paper-plane ms-1"></i> ثبت نظر
                                </button>
                            </form>
                        @endif
                    @else
                        <div class="ds-review-login">
                            برای ثبت نظر، ابتدا
                            <a href="{{ route('login') }}">وارد حساب کاربری</a>
                            شوید.
                        </div>
                    @endif
                </div>
            </div>
        </section>

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

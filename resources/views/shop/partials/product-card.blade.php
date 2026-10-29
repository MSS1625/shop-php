<div class="col-6 col-md-4 col-lg-3">
    <article class="ds-product-card">
        @unless($product->inStock())
            <span class="ds-badge ds-badge-out">ناموجود</span>
        @elseif($product->stock <= 3)
            <span class="ds-badge ds-badge-low">تنها {{ fa_num($product->stock) }} عدد</span>
        @endif

        <a href="{{ route('shop.products.show', $product) }}" class="ds-product-img">
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->title }}" loading="lazy">
        </a>

        {{-- قلب علاقه‌مندی روی گوشهٔ تصویر — مهمان: لینک ورود --}}
        @include('shop.partials.wishlist-button')

        <div class="ds-product-body">
            @if($product->category)
                <span class="ds-product-cat">{{ $product->category->name }}</span>
            @endif

            <a href="{{ route('shop.products.show', $product) }}" class="ds-product-title">
                {{ $product->title }}
            </a>

            @if($product->hasRating())
                <div class="ds-product-rating">
                    <i class="fa-solid fa-star"></i>
                    <span class="fw-bold">{{ $product->avgRatingFa() }}</span>
                    <span class="text-muted">({{ fa_num($product->reviews_count) }} نظر)</span>
                </div>
            @endif

            <div class="ds-product-price">
                {{ fa_price($product->price) }} <small>تومان</small>
            </div>

            @if($product->inStock())
                <button class="btn btn-ds w-100 mt-3" data-add-to-cart="{{ route('cart.store', $product) }}">
                    <i class="fa-solid fa-cart-plus ms-1"></i> افزودن به سبد
                </button>
            @else
                <button class="btn btn-ds w-100 mt-3" disabled>
                    <i class="fa-solid fa-ban ms-1"></i> ناموجود
                </button>
            @endif
        </div>
    </article>
</div>

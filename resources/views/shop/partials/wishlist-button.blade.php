{{-- دکمهٔ علاقه‌مندی — مشترک بین کارت محصول و صفحهٔ جزئیات --}}
@inject('wishlist', 'App\Services\Wishlist')

@php
    $inWishlist = $wishlist->has($product->id);
    $wishLabel = $inWishlist ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها';
    $big = isset($big);
@endphp

@auth
    <form action="{{ route('wishlist.toggle', $product) }}" method="POST"
          class="ds-wish-form {{ $big ? 'ds-wish-form-lg' : '' }}"
          data-wishlist-toggle data-wishlist-product="{{ $product->slug }}">
        @csrf
        <button type="submit"
                class="ds-wish-btn {{ $inWishlist ? 'active' : '' }} {{ $big ? 'ds-wish-btn-lg' : '' }}"
                aria-label="{{ $wishLabel }}" title="{{ $wishLabel }}">
            <i class="{{ $inWishlist ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
        </button>
    </form>
@else
    <a href="{{ route('login') }}"
       class="ds-wish-btn {{ $big ? 'ds-wish-btn-lg' : '' }}"
       title="برای ذخیره در علاقه‌مندی‌ها وارد شوید" aria-label="افزودن به علاقه‌مندی‌ها">
        <i class="fa-regular fa-heart"></i>
    </a>
@endauth

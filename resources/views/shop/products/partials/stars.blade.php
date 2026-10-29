{{--
    رندر ۵ ستاره بر اساس امتیاز داده‌شده (متغیر $rating — ۰ تا ۵)
    استفاده: @include('shop.products.partials.stars', ['rating' => $review->rating])
--}}
@php($filled = (int) round((float) ($rating ?? 0)))
<span class="ds-stars" role="img" aria-label="امتیاز {{ fa_num($filled) }} از ۵">
    @for($i = 1; $i <= 5; $i++)
        <i class="fa-{{ $i <= $filled ? 'solid' : 'regular' }} fa-star" aria-hidden="true"></i>
    @endfor
</span>

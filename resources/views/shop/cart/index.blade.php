@extends('layouts.shop')

@section('title', 'سبد خرید')

@section('content')
    <section class="container mt-4" data-cart-page>
        <h1 class="ds-page-title mb-4">
            <i class="fa-solid fa-cart-shopping ms-2 text-muted"></i> سبد خرید
        </h1>

        @if($items->isEmpty())
            <div class="ds-empty">
                <i class="fa-solid fa-cart-shopping"></i>
                <p class="fs-5">سبد خرید شما خالی است!</p>
                <a href="{{ route('shop.products.index') }}" class="btn btn-ds mt-2">
                    <i class="fa-solid fa-store ms-1"></i> شروع خرید
                </a>
            </div>
        @else
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="ds-card p-2 p-md-3">
                        @foreach($items as $item)
                            <div class="row g-3 align-items-center py-3 border-bottom {{ $loop->last ? 'border-0' : '' }}"
                                 data-cart-row="{{ $item['product']->slug }}">
                                <div class="col-4 col-md-3">
                                    <a href="{{ route('shop.products.show', $item['product']) }}">
                                        <img src="{{ $item['product']->imageUrl() }}"
                                             alt="{{ $item['product']->title }}"
                                             class="w-100 rounded-3" style="max-height:120px;object-fit:contain;background:var(--ds-bg-soft)">
                                    </a>
                                </div>
                                <div class="col-8 col-md-4">
                                    <a href="{{ route('shop.products.show', $item['product']) }}" class="fw-bold text-light d-block mb-1">
                                        {{ $item['product']->title }}
                                    </a>
                                    <small class="text-muted">
                                        {{ fa_price($item['product']->price) }} تومان
                                    </small>
                                </div>
                                <div class="col-6 col-md-3 mt-3 mt-md-0">
                                    <div class="ds-qty">
                                        <button type="button" data-cart-update="minus"><i class="fa-solid fa-minus"></i></button>
                                        <input type="number" data-qty-input value="{{ $item['quantity'] }}"
                                               min="0" max="{{ $item['product']->stock }}" readonly aria-label="تعداد">
                                        <button type="button" data-cart-update="plus"><i class="fa-solid fa-plus"></i></button>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2 mt-3 mt-md-0 text-start">
                                    <strong class="text-success">{{ fa_price($item['subtotal']) }}</strong>
                                    <small class="text-muted"> تومان</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="ds-price-box">
                        <h2 class="fs-6 fw-bold mb-3">خلاصه سفارش</h2>
                        <div class="ds-spec-row">
                            <span>تعداد اقلام</span>
                            <span>{{ fa_num($items->sum('quantity')) }} عدد</span>
                        </div>
                        <div class="ds-spec-row">
                            <span>جمع کل</span>
                            <strong class="text-success fs-5">{{ fa_price($total) }} تومان</strong>
                        </div>
                        <a href="{{ route('checkout.index') }}" class="btn btn-ds w-100 mt-4">
                            <i class="fa-solid fa-credit-card ms-1"></i> ادامه و تکمیل خرید
                        </a>
                        <a href="{{ route('shop.products.index') }}" class="btn btn-ds-ghost w-100 mt-2">
                            <i class="fa-solid fa-arrow-right ms-1"></i> بازگشت به فروشگاه
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </section>
@endsection

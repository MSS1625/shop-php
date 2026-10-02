@extends('layouts.shop')

@section('title', 'تکمیل خرید')

@section('content')
    <section class="container mt-4">
        <h1 class="ds-page-title mb-4">
            <i class="fa-solid fa-credit-card ms-2 text-muted"></i> تکمیل خرید
        </h1>

        <form action="{{ route('checkout.store') }}" method="POST" class="row g-4">
            @csrf

            <div class="col-lg-7">
                <div class="ds-card p-4">
                    <h2 class="fs-6 fw-bold mb-4">
                        <i class="fa-solid fa-truck-fast ms-1 text-muted"></i> اطلاعات گیرنده
                    </h2>

                    <div class="mb-3">
                        <label for="customer_name" class="form-label">نام و نام خانوادگی</label>
                        <input type="text" class="form-control @error('customer_name') is-invalid @enderror"
                               id="customer_name" name="customer_name"
                               value="{{ old('customer_name') }}" required autofocus>
                        @error('customer_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="customer_phone" class="form-label">شماره موبایل</label>
                        <input type="tel" class="form-control @error('customer_phone') is-invalid @enderror"
                               id="customer_phone" name="customer_phone"
                               value="{{ old('customer_phone') }}"
                               placeholder="09123456789" dir="ltr" required>
                        <div class="form-text">شماره‌ای که با آن در تماس هستید وارد کنید.</div>
                        @error('customer_phone')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="customer_address" class="form-label">آدرس تحویل سفارش</label>
                        <textarea class="form-control @error('customer_address') is-invalid @enderror"
                                  id="customer_address" name="customer_address" rows="3" required>{{ old('customer_address') }}</textarea>
                        @error('customer_address')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="note" class="form-label">توضیحات (اختیاری)</label>
                        <textarea class="form-control" id="note" name="note" rows="2">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="ds-price-box">
                    <h2 class="fs-6 fw-bold mb-3">
                        <i class="fa-solid fa-receipt ms-1 text-muted"></i> خلاصه سفارش
                    </h2>

                    @foreach($items as $item)
                        <div class="ds-spec-row">
                            <span class="text-truncate" style="max-width:65%">
                                {{ $item['product']->title }}
                                <span class="text-muted">× {{ fa_num($item['quantity']) }}</span>
                            </span>
                            <span>{{ fa_price($item['subtotal']) }}</span>
                        </div>
                    @endforeach

                    <div class="ds-spec-row mt-2">
                        <span>مبلغ قابل پرداخت</span>
                        <strong class="text-success fs-5">{{ fa_price($total) }} تومان</strong>
                    </div>

                    <button type="submit" class="btn btn-ds w-100 mt-4">
                        <i class="fa-solid fa-circle-check ms-1"></i> ثبت نهایی سفارش
                    </button>

                    <a href="{{ route('cart.index') }}" class="btn btn-ds-ghost w-100 mt-2">
                        <i class="fa-solid fa-arrow-right ms-1"></i> بازگشت به سبد
                    </a>
                </div>
            </div>
        </form>
    </section>
@endsection

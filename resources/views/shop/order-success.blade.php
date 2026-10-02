@extends('layouts.shop')

@section('title', 'سفارش با موفقیت ثبت شد')

@section('content')
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="ds-card p-5 text-center">
                    <div class="ds-stat-icon green mx-auto mb-4" style="width:86px;height:86px;font-size:2.4rem;border-radius:24px">
                        <i class="fa-solid fa-check"></i>
                    </div>

                    <h1 class="fw-bold mb-2">سفارش شما با موفقیت ثبت شد!</h1>
                    <p class="text-muted mb-4">
                        شماره سفارش خود را یادداشت کنید؛ همکاران ما به‌زودی برای هماهنگی ارسال با شما تماس می‌گیرند.
                    </p>

                    <div class="alert alert-ds-success d-inline-flex align-items-center gap-2 px-4 py-3">
                        <i class="fa-solid fa-hashtag"></i>
                        <strong class="fs-5" dir="ltr">{{ $order->order_number }}</strong>
                    </div>

                    <div class="ds-price-box text-start mt-4">
                        <div class="ds-spec-row">
                            <span>نام گیرنده</span>
                            <span>{{ $order->customer_name }}</span>
                        </div>
                        <div class="ds-spec-row">
                            <span>تعداد اقلام</span>
                            <span>{{ fa_num($order->items->sum('quantity')) }} عدد</span>
                        </div>
                        <div class="ds-spec-row">
                            <span>وضعیت سفارش</span>
                            <span class="badge text-bg-warning">{{ $order->statusLabel() }}</span>
                        </div>
                        <div class="ds-spec-row">
                            <span>مبلغ کل</span>
                            <strong class="text-success fs-5">{{ fa_price($order->total) }} تومان</strong>
                        </div>
                    </div>

                    <a href="{{ route('shop.home') }}" class="btn btn-ds mt-4 px-4">
                        <i class="fa-solid fa-house ms-1"></i> بازگشت به فروشگاه
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

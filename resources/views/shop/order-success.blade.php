@extends('layouts.shop')

@section('title', 'سفارش '.$order->order_number)

@section('content')
    <section class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="ds-card p-5 text-center">
                    @if($order->isPaid())
                        <div class="ds-stat-icon green mx-auto mb-4" style="width:86px;height:86px;font-size:2.4rem;border-radius:24px">
                            <i class="fa-solid fa-check-double"></i>
                        </div>
                        <h1 class="fw-bold mb-2">پرداخت با موفقیت انجام شد!</h1>
                        <p class="text-muted mb-4">
                            از خرید شما سپاسگزاریم. جزئیات سفارش:
                        </p>
                    @else
                        <div class="ds-stat-icon cyan mx-auto mb-4" style="width:86px;height:86px;font-size:2.4rem;border-radius:24px">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <h1 class="fw-bold mb-2">سفارش شما ثبت شد!</h1>
                        <p class="text-muted mb-4">
                            شماره سفارش خود را یادداشت کنید؛ همکاران ما به‌زودی برای هماهنگی ارسال با شما تماس می‌گیرند.
                        </p>
                    @endif

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
                            <span>روش پرداخت</span>
                            <span>{{ $order->paymentMethodLabel() }}</span>
                        </div>
                        @if($order->isOnlinePayment())
                            <div class="ds-spec-row">
                                <span>وضعیت پرداخت</span>
                                <span class="badge text-bg-{{ $order->paymentStatusColor() }}">{{ $order->paymentStatusLabel() }}</span>
                            </div>
                            @if($order->ref_id)
                                <div class="ds-spec-row">
                                    <span>شماره پیگیری بانکی</span>
                                    <strong class="text-info" dir="ltr">{{ fa_num($order->ref_id) }}</strong>
                                </div>
                            @endif
                        @endif
                        <div class="ds-spec-row">
                            <span>مبلغ کل</span>
                            <strong class="text-success fs-5">{{ fa_price($order->total) }} تومان</strong>
                        </div>
                    </div>

                    <div class="d-grid d-sm-flex justify-content-center gap-2 mt-4">
                        @if($order->canBePaidOnline())
                            <a href="{{ route('payment.start', $order->order_number) }}" class="btn btn-ds px-4">
                                <i class="fa-solid fa-rotate-right ms-1"></i>
                                {{ $order->payment_status === \App\Models\Order::PAYMENT_PENDING
                                    ? 'پرداخت آنلاین'
                                    : 'تلاش مجدد پرداخت' }}
                            </a>
                        @endif

                        @auth
                            <a href="{{ route('account.orders.show', $order) }}" class="btn btn-ds-outline px-4">
                                <i class="fa-solid fa-receipt ms-1"></i> پیگیری سفارش
                            </a>
                        @endauth

                        <a href="{{ route('shop.home') }}" class="btn btn-ds-ghost px-4">
                            <i class="fa-solid fa-house ms-1"></i> بازگشت به فروشگاه
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

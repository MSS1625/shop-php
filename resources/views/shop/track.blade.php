@extends('layouts.shop')

@section('title', 'پیگیری سفارش')

@section('content')
    <section class="container mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <h1 class="ds-page-title mb-0">
                <i class="fa-solid fa-truck-fast ms-2 text-muted"></i>
                پیگیری سفارش
            </h1>
        </div>

        {{-- فرم جستجو --}}
        <div class="row justify-content-center mb-4">
            <div class="col-md-8 col-lg-6">
                <div class="ds-card p-4 p-md-5">
                    <div class="text-center mb-4">
                        <span class="ds-stat-icon purple mx-auto mb-3" style="width:70px;height:70px;font-size:1.6rem;border-radius:20px">
                            <i class="fa-solid fa-magnifying-glass-location"></i>
                        </span>
                        <h2 class="fs-5 fw-bold mb-1">سفارشتان را پیدا کنید</h2>
                        <p class="text-muted small mb-0">
                            بدون نیاز به حساب کاربری — شماره سفارش و موبایلی که هنگام خرید وارد کردید
                        </p>
                    </div>

                    {{-- خطای جستجو (شماره سفارش/موبایل نامعتبر) --}}
                    @if(session('track_error'))
                        <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span>{{ session('track_error') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('shop.track.lookup') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="order_number" class="form-label">شماره سفارش</label>
                            <input type="text" class="form-control @error('order_number') is-invalid @enderror"
                                   id="order_number" name="order_number" value="{{ old('order_number') }}"
                                   dir="ltr" placeholder="DS-261021-A1B2" required autofocus>
                            @error('order_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="phone" class="form-label">شماره موبایل (موقع خرید)</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone') }}"
                                   dir="ltr" placeholder="09123456789" required>
                            @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-ds w-100">
                            <i class="fa-solid fa-magnifying-glass ms-1"></i> پیگیری سفارش
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- نتیجهٔ پیگیری --}}
        @if($order)
            @php
                $flow = [
                    \App\Models\Order::STATUS_PENDING,
                    \App\Models\Order::STATUS_PROCESSING,
                    \App\Models\Order::STATUS_SHIPPED,
                    \App\Models\Order::STATUS_DELIVERED,
                ];
                $icons = [
                    'pending' => 'fa-clock',
                    'processing' => 'fa-gears',
                    'shipped' => 'fa-truck-fast',
                    'delivered' => 'fa-house-circle-check',
                ];
                $currentIndex = array_search($order->status, $flow);
                $maskedPhone = fa_num(substr($order->customer_phone, 0, 4)).'***'.fa_num(substr($order->customer_phone, -3));
            @endphp

            <div class="ds-card p-4 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <h2 class="fs-5 fw-bold mb-0">
                        <i class="fa-solid fa-receipt ms-1 text-muted"></i>
                        سفارش <span class="text-info" dir="ltr">{{ $order->order_number }}</span>
                    </h2>
                    <span class="badge text-bg-{{ $order->statusColor() }} fs-6 px-3 py-2">
                        {{ $order->statusLabel() }}
                    </span>
                </div>

                {{-- تایم‌لاین وضعیت --}}
                @if($order->isCancelled())
                    <div class="text-center py-3">
                        <span class="badge text-bg-danger fs-6 px-4 py-2">
                            <i class="fa-solid fa-ban ms-1"></i> این سفارش لغو شده است
                        </span>
                    </div>
                @else
                    <div class="ds-timeline">
                        @foreach($flow as $i => $step)
                            @php
                                $class = $i < $currentIndex ? 'is-done' : ($i === $currentIndex ? 'is-active' : '');
                            @endphp
                            <div class="ds-timeline-step {{ $class }}">
                                <div class="ds-timeline-dot">
                                    @if($i < $currentIndex)
                                        <i class="fa-solid fa-check"></i>
                                    @else
                                        <i class="fa-solid {{ $icons[$step] }}"></i>
                                    @endif
                                </div>
                                <div class="ds-timeline-title">{{ \App\Models\Order::STATUS_LABELS[$step] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="row g-4">
                {{-- اقلام سفارش --}}
                <div class="col-lg-7">
                    <div class="ds-card p-4">
                        <h3 class="fs-6 fw-bold mb-3">
                            <i class="fa-solid fa-box ms-1 text-muted"></i> اقلام سفارش
                        </h3>

                        @foreach($order->items as $item)
                            <div class="d-flex align-items-center gap-3 py-3 {{ ! $loop->last ? 'border-bottom border-secondary-subtle' : '' }}">
                                @if($item->product?->image)
                                    <img src="{{ asset('storage/'.$item->product->image) }}"
                                         alt="{{ $item->product_title }}" class="rounded-3"
                                         style="width:64px;height:64px;object-fit:cover">
                                @else
                                    <span class="ds-stat-icon purple" style="width:64px;height:64px;border-radius:12px">
                                        <i class="fa-solid fa-ethernet"></i>
                                    </span>
                                @endif

                                <div class="flex-grow-1">
                                    @if($item->product)
                                        <a href="{{ route('shop.products.show', $item->product) }}" class="fw-bold text-decoration-none">
                                            {{ $item->product_title }}
                                        </a>
                                    @else
                                        <span class="fw-bold">{{ $item->product_title }}</span>
                                    @endif
                                    <div class="text-muted small mt-1">
                                        {{ fa_price($item->unit_price) }} تومان × {{ fa_num($item->quantity) }}
                                    </div>
                                </div>
                                <div class="fw-bold text-success">{{ fa_price($item->subtotal()) }}</div>
                            </div>
                        @endforeach

                        @if($order->hasDiscount())
                            <div class="ds-spec-row">
                                <span>جمع اقلام</span>
                                <span>{{ fa_price($order->subtotalAmount()) }} تومان</span>
                            </div>
                            <div class="ds-spec-row">
                                <span class="text-success">
                                    <i class="fa-solid fa-ticket ms-1"></i>
                                    تخفیف کد <code dir="ltr">{{ $order->coupon_code }}</code>
                                </span>
                                <span class="text-success">{{ fa_num('-').fa_price($order->discount_amount) }} تومان</span>
                            </div>
                        @endif
                        <div class="ds-spec-row mt-3">
                            <span class="fw-bold">مبلغ قابل پرداخت</span>
                            <strong class="text-success fs-5">{{ fa_price($order->total) }} تومان</strong>
                        </div>
                    </div>
                </div>

                {{-- وضعیت پرداخت و گیرنده --}}
                <div class="col-lg-5">
                    <div class="ds-price-box mb-4">
                        <h3 class="fs-6 fw-bold mb-3">
                            <i class="fa-solid fa-credit-card ms-1 text-muted"></i> وضعیت پرداخت
                        </h3>

                        <div class="ds-spec-row">
                            <span>روش پرداخت</span>
                            <span>{{ $order->paymentMethodLabel() }}</span>
                        </div>
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
                        @if($order->paid_at)
                            <div class="ds-spec-row">
                                <span>تاریخ پرداخت</span>
                                <span>{{ fa_num($order->paid_at->format('Y-m-d H:i')) }}</span>
                            </div>
                        @endif
                        <div class="ds-spec-row">
                            <span>تاریخ ثبت سفارش</span>
                            <span>{{ fa_num($order->created_at->format('Y-m-d H:i')) }}</span>
                        </div>
                    </div>

                    <div class="ds-card p-4">
                        <h3 class="fs-6 fw-bold mb-3">
                            <i class="fa-solid fa-user ms-1 text-muted"></i> گیرنده
                        </h3>
                        <div class="ds-spec-row">
                            <span>نام گیرنده</span>
                            <span>{{ $order->customer_name }}</span>
                        </div>
                        <div class="ds-spec-row">
                            <span>موبایل</span>
                            <span dir="ltr">{{ $maskedPhone }}</span>
                        </div>
                    </div>

                    {{-- اقدامات --}}
                    <div class="d-grid gap-2 mt-4">
                        @if($order->canBePaidOnline())
                            <a href="{{ route('payment.start', $order->order_number) }}" class="btn btn-ds">
                                <i class="fa-solid fa-credit-card ms-1"></i>
                                {{ $order->payment_status === \App\Models\Order::PAYMENT_PENDING ? 'پرداخت آنلاین' : 'تلاش مجدد پرداخت' }}
                            </a>
                        @endif
                    </div>

                    <p class="text-muted small mt-3 mb-0">
                        <i class="fa-solid fa-shield-halved ms-1"></i>
                        برای لغو سفارش یا مشاهدهٔ تاریخچهٔ کامل، از حساب کاربری خود استفاده کنید.
                    </p>
                </div>
            </div>
        @endif
    </section>
@endsection

@extends('layouts.shop')

@section('title', 'سفارش '.$order->order_number)

@section('content')
    <section class="container mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <h1 class="ds-page-title mb-0">
                <i class="fa-solid fa-receipt ms-2 text-muted"></i>
                جزئیات سفارش
                <span class="text-info" dir="ltr">{{ $order->order_number }}</span>
            </h1>
            <a href="{{ route('account.orders') }}" class="btn btn-ds-ghost btn-sm">
                <i class="fa-solid fa-arrow-right ms-1"></i> بازگشت به لیست
            </a>
        </div>

        {{-- تایم‌لاین وضعیت --}}
        <div class="ds-card p-4 mb-4">
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
            @endphp

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
                    <h2 class="fs-6 fw-bold mb-3">
                        <i class="fa-solid fa-box ms-1 text-muted"></i> اقلام سفارش
                    </h2>

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

                    <div class="ds-spec-row mt-3">
                        <span class="fw-bold">مبلغ کل سفارش</span>
                        <strong class="text-success fs-5">{{ fa_price($order->total) }} تومان</strong>
                    </div>
                </div>
            </div>

            {{-- اطلاعات پرداخت و ارسال --}}
            <div class="col-lg-5">
                <div class="ds-price-box mb-4">
                    <h2 class="fs-6 fw-bold mb-3">
                        <i class="fa-solid fa-credit-card ms-1 text-muted"></i> اطلاعات پرداخت
                    </h2>

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
                    <h2 class="fs-6 fw-bold mb-3">
                        <i class="fa-solid fa-truck ms-1 text-muted"></i> اطلاعات ارسال
                    </h2>

                    <div class="ds-spec-row">
                        <span>گیرنده</span>
                        <span>{{ $order->customer_name }}</span>
                    </div>
                    <div class="ds-spec-row">
                        <span>موبایل</span>
                        <span dir="ltr">{{ fa_num($order->customer_phone) }}</span>
                    </div>
                    <div class="ds-spec-row">
                        <span class="text-nowrap">آدرس</span>
                        <span class="text-start">{{ $order->customer_address }}</span>
                    </div>
                    @if($order->note)
                        <div class="ds-spec-row">
                            <span>یادداشت</span>
                            <span>{{ $order->note }}</span>
                        </div>
                    @endif
                </div>

                {{-- اقدامات --}}
                <div class="d-grid gap-2 mt-4">
                    @if($order->canBePaidOnline())
                        <a href="{{ route('payment.start', $order->order_number) }}" class="btn btn-ds">
                            <i class="fa-solid fa-credit-card ms-1"></i>
                            {{ $order->payment_status === \App\Models\Order::PAYMENT_PENDING ? 'پرداخت آنلاین' : 'تلاش مجدد پرداخت' }}
                        </a>
                    @endif

                    @if($order->status === \App\Models\Order::STATUS_PENDING && ! $order->isPaid())
                        <form action="{{ route('account.orders.cancel', $order) }}" method="POST"
                              data-confirm="آیا از لغو این سفارش مطمئن هستید؟ موجودی محصولات به انبار بازمی‌گردد.">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fa-solid fa-ban ms-1"></i> لغو سفارش
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        // تایید لغو سفارش
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (!window.confirm(form.dataset.confirm)) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endpush

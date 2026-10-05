@extends('layouts.admin')

@section('title', 'سفارش ' . $order->order_number)
@section('page_title', 'جزئیات سفارش')

@section('content')
    <div class="ds-page-head">
        <div>
            <h2 class="ds-page-title mb-1">
                سفارش <span dir="ltr" class="text-info">{{ $order->order_number }}</span>
            </h2>
            <small class="text-muted" dir="ltr">{{ $order->created_at->format('Y-m-d H:i') }}</small>
        </div>

        <a href="{{ route('admin.orders.index') }}" class="btn btn-ds-ghost">
            <i class="fa-solid fa-arrow-right ms-1"></i> بازگشت به لیست
        </a>
    </div>

    <div class="row g-4">
        {{-- اقلام سفارش --}}
        <div class="col-xl-8">
            <div class="ds-card p-2 p-md-3 mb-4">
                <h3 class="fs-6 fw-bold px-2 py-2 mb-0">اقلام سفارش</h3>
                <div class="table-responsive">
                    <table class="table ds-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>محصول</th>
                                <th>قیمت واحد</th>
                                <th>تعداد</th>
                                <th>جمع</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($item->product && $item->product->imageExists())
                                                <img src="{{ $item->product->imageUrl() }}" alt="{{ $item->product_title }}" class="ds-thumb">
                                            @endif
                                            <div>
                                                @if($item->product)
                                                    <a href="{{ route('shop.products.show', $item->product) }}" class="fw-bold text-light">
                                                        {{ $item->product_title }}
                                                    </a>
                                                @else
                                                    <span class="fw-bold">{{ $item->product_title }}</span>
                                                    <small class="text-muted d-block">(محصول حذف شده)</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ fa_price($item->unit_price) }}</td>
                                    <td>{{ fa_num($item->quantity) }}</td>
                                    <td class="text-success">{{ fa_price($item->subtotal()) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="fw-bold">مبلغ کل سفارش</td>
                                <td class="fw-bold text-success fs-5">{{ fa_price($order->total) }} تومان</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- آدرس --}}
            <div class="ds-card p-4">
                <h3 class="fs-6 fw-bold mb-3">
                    <i class="fa-solid fa-location-dot ms-1 text-muted"></i> اطلاعات گیرنده
                </h3>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="ds-spec-row">
                            <span>نام</span>
                            <span>{{ $order->customer_name }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="ds-spec-row">
                            <span>موبایل</span>
                            <span dir="ltr">{{ fa_num($order->customer_phone) }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="ds-spec-row">
                            <span>وضعیت</span>
                            <span class="badge text-bg-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="ds-spec-row">
                            <span>روش پرداخت</span>
                            <span>
                                {{ $order->paymentMethodLabel() }}
                                <span class="badge text-bg-{{ $order->paymentStatusColor() }} ms-2">{{ $order->paymentStatusLabel() }}</span>
                            </span>
                        </div>
                    </div>
                    @if($order->ref_id)
                        <div class="col-md-12">
                            <div class="ds-spec-row">
                                <span>شماره پیگیری بانکی</span>
                                <strong class="text-info" dir="ltr">{{ fa_num($order->ref_id) }}</strong>
                            </div>
                        </div>
                    @endif
                    @if($order->paid_at)
                        <div class="col-md-12">
                            <div class="ds-spec-row">
                                <span>تاریخ پرداخت</span>
                                <span>{{ fa_num($order->paid_at->format('Y-m-d H:i')) }}</span>
                            </div>
                        </div>
                    @endif
                    <div class="col-12">
                        <div class="ds-spec-row">
                            <span>آدرس</span>
                            <span class="text-start">{{ $order->customer_address }}</span>
                        </div>
                    </div>
                    @if($order->note)
                        <div class="col-12">
                            <div class="ds-spec-row">
                                <span>یادداشت مشتری</span>
                                <span>{{ $order->note }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- تغییر وضعیت --}}
        <div class="col-xl-4">
            <div class="ds-price-box">
                <h3 class="fs-6 fw-bold mb-3">
                    <i class="fa-solid fa-arrows-rotate ms-1 text-muted"></i> تغییر وضعیت سفارش
                </h3>

                <form action="{{ route('admin.orders.status', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <select name="status" class="form-select">
                            @foreach(\App\Models\Order::STATUS_LABELS as $status => $label)
                                <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-ds w-100">
                        <i class="fa-solid fa-check ms-1"></i> ثبت وضعیت جدید
                    </button>
                </form>

                <div class="mt-4 small text-muted">
                    <i class="fa-solid fa-circle-info ms-1"></i>
                    با لغو سفارش، موجودی محصولات به‌صورت خودکار به انبار برمی‌گردد.
                </div>
            </div>
        </div>
    </div>
@endsection

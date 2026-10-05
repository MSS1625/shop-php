@extends('layouts.shop')

@section('title', 'حساب کاربری')

@section('content')
    <section class="container mt-4">
        <div class="d-flex align-items-center gap-3 mb-4">
            <span class="ds-stat-icon purple" style="width:56px;height:56px;font-size:1.3rem;border-radius:16px">
                <i class="fa-solid fa-user"></i>
            </span>
            <div>
                <h1 class="ds-page-title mb-0">سلام، {{ $user->name }} 👋</h1>
                <span class="text-muted small" dir="ltr">{{ $user->email }}</span>
            </div>
        </div>

        {{-- آمار کلی --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="ds-card p-3 d-flex align-items-center gap-3">
                    <span class="ds-stat-icon purple" style="width:46px;height:46px;font-size:1.05rem;border-radius:14px">
                        <i class="fa-solid fa-box"></i>
                    </span>
                    <div>
                        <div class="fw-bold fs-5">{{ fa_num($stats['total_orders']) }}</div>
                        <div class="text-muted small">کل سفارش‌ها</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="ds-card p-3 d-flex align-items-center gap-3">
                    <span class="ds-stat-icon cyan" style="width:46px;height:46px;font-size:1.05rem;border-radius:14px">
                        <i class="fa-solid fa-truck-fast"></i>
                    </span>
                    <div>
                        <div class="fw-bold fs-5">{{ fa_num($stats['active_orders']) }}</div>
                        <div class="text-muted small">در جریان</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="ds-card p-3 d-flex align-items-center gap-3">
                    <span class="ds-stat-icon green" style="width:46px;height:46px;font-size:1.05rem;border-radius:14px">
                        <i class="fa-solid fa-wallet"></i>
                    </span>
                    <div>
                        <div class="fw-bold fs-5">{{ fa_price($stats['total_spent']) }}</div>
                        <div class="text-muted small">مجموع خرید (تومان)</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- آخرین سفارش‌ها --}}
        <div class="ds-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="fs-6 fw-bold mb-0">
                    <i class="fa-solid fa-clock-rotate-left ms-1 text-muted"></i> آخرین سفارش‌ها
                </h2>
                <a href="{{ route('account.orders') }}" class="btn btn-ds-ghost btn-sm">
                    مشاهده همه <i class="fa-solid fa-angle-left ms-1"></i>
                </a>
            </div>

            @if($orders->isNotEmpty())
                <div class="table-responsive">
                    <table class="table ds-table align-middle">
                        <thead>
                            <tr>
                                <th>شماره سفارش</th>
                                <th>تعداد اقلام</th>
                                <th>مبلغ (تومان)</th>
                                <th>وضعیت سفارش</th>
                                <th>پرداخت</th>
                                <th>تاریخ</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td dir="ltr" class="fw-bold text-info">{{ $order->order_number }}</td>
                                    <td>{{ fa_num($order->items_count) }}</td>
                                    <td class="text-success">{{ fa_price($order->total) }}</td>
                                    <td>
                                        <span class="badge text-bg-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $order->paymentStatusColor() }}">{{ $order->paymentStatusLabel() }}</span>
                                    </td>
                                    <td class="text-muted small" dir="ltr">{{ $order->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('account.orders.show', $order) }}" class="btn btn-ds-ghost btn-sm">
                                            جزئیات
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fa-solid fa-box-open text-muted mb-3" style="font-size:3rem"></i>
                    <p class="text-muted mb-3">هنوز سفارشی ثبت نکرده‌اید.</p>
                    <a href="{{ route('shop.products.index') }}" class="btn btn-ds">
                        <i class="fa-solid fa-store ms-1"></i> شروع خرید
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection

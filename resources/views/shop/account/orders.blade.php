@extends('layouts.shop')

@section('title', 'سفارش‌های من')

@section('content')
    <section class="container mt-4">
        <h1 class="ds-page-title mb-4">
            <i class="fa-solid fa-box-open ms-2 text-muted"></i> سفارش‌های من
        </h1>

        @if($orders->isNotEmpty())
            <div class="ds-card p-2 p-md-3">
                <div class="table-responsive">
                    <table class="table ds-table align-middle">
                        <thead>
                            <tr>
                                <th>شماره سفارش</th>
                                <th>اقلام</th>
                                <th>مبلغ (تومان)</th>
                                <th>وضعیت سفارش</th>
                                <th>پرداخت</th>
                                <th>تاریخ ثبت</th>
                                <th class="text-center">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td dir="ltr">
                                        <a href="{{ route('account.orders.show', $order) }}" class="text-info fw-bold">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>{{ fa_num($order->items_count) }}</td>
                                    <td class="text-success">{{ fa_price($order->total) }}</td>
                                    <td>
                                        <span class="badge text-bg-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $order->paymentStatusColor() }}">{{ $order->paymentStatusLabel() }}</span>
                                    </td>
                                    <td class="text-muted small" dir="ltr">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('account.orders.show', $order) }}" class="btn btn-ds-ghost btn-sm" title="جزئیات">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        @else
            <div class="ds-card p-5 text-center">
                <i class="fa-solid fa-box-open text-muted mb-3" style="font-size:3rem"></i>
                <p class="text-muted mb-3">هنوز سفارشی ثبت نکرده‌اید.</p>
                <a href="{{ route('shop.products.index') }}" class="btn btn-ds">
                    <i class="fa-solid fa-store ms-1"></i> شروع خرید
                </a>
            </div>
        @endif
    </section>
@endsection

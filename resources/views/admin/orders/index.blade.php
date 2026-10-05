@extends('layouts.admin')

@section('title', 'مدیریت سفارش‌ها')
@section('page_title', 'مدیریت سفارش‌ها')

@section('content')
    {{-- فیلتر وضعیت --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.orders.index') }}"
           class="btn btn-sm {{ $activeStatus ? 'btn-ds-ghost' : 'btn-ds' }} rounded-pill">
            همه ({{ fa_num($statusCounts->sum()) }})
        </a>
        @foreach(\App\Models\Order::STATUS_LABELS as $status => $label)
            <a href="{{ route('admin.orders.index', ['status' => $status]) }}"
               class="btn btn-sm {{ $activeStatus === $status ? 'btn-ds' : 'btn-ds-ghost' }} rounded-pill">
                {{ $label }} ({{ fa_num($statusCounts[$status]) }})
            </a>
        @endforeach
    </div>

    @if($orders->isNotEmpty())
        <div class="ds-card p-2 p-md-3">
            <div class="table-responsive">
                <table class="table ds-table align-middle">
                    <thead>
                        <tr>
                            <th>شماره سفارش</th>
                            <th>مشتری</th>
                            <th>موبایل</th>
                            <th>اقلام</th>
                            <th>مبلغ (تومان)</th>
                            <th>وضعیت</th>
                            <th>پرداخت</th>
                            <th>تاریخ ثبت</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td dir="ltr">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-info fw-bold">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td>{{ $order->customer_name }}</td>
                                <td dir="ltr" class="text-muted">{{ fa_num($order->customer_phone) }}</td>
                                <td>{{ fa_num($order->items_count) }}</td>
                                <td class="text-success">{{ fa_price($order->total) }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column align-items-start gap-1">
                                        <small class="text-muted">{{ $order->isOnlinePayment() ? 'زرین‌پال' : 'در محل' }}</small>
                                        <span class="badge text-bg-{{ $order->paymentStatusColor() }}">{{ $order->paymentStatusLabel() }}</span>
                                    </div>
                                </td>
                                <td class="text-muted small" dir="ltr">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-ds-ghost btn-sm" title="جزئیات">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $orders->onEachSide(1)->links() }}
        </div>
    @else
        <div class="ds-card">
            <div class="ds-empty">
                <i class="fa-solid fa-receipt"></i>
                <p class="mb-0">سفارشی با این فیلتر پیدا نشد.</p>
            </div>
        </div>
    @endif
@endsection

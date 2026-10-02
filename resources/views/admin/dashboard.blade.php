@extends('layouts.admin')

@section('title', 'داشبورد')
@section('page_title', 'داشبورد مدیریت')

@section('content')
    {{-- کارت‌های آمار --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="ds-stat-card">
                <div class="ds-stat-icon purple"><i class="fa-solid fa-box-open"></i></div>
                <div>
                    <div class="ds-stat-value">{{ fa_num($stats['products_count']) }}</div>
                    <div class="ds-stat-label">کل محصولات</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="ds-stat-card">
                <div class="ds-stat-icon cyan"><i class="fa-solid fa-receipt"></i></div>
                <div>
                    <div class="ds-stat-value">{{ fa_num($stats['orders_count']) }}</div>
                    <div class="ds-stat-label">کل سفارش‌ها</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="ds-stat-card">
                <div class="ds-stat-icon amber"><i class="fa-solid fa-hourglass-half"></i></div>
                <div>
                    <div class="ds-stat-value">{{ fa_num($stats['pending_orders_count']) }}</div>
                    <div class="ds-stat-label">سفارش در انتظار</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="ds-stat-card">
                <div class="ds-stat-icon green"><i class="fa-solid fa-sack-dollar"></i></div>
                <div>
                    <div class="ds-stat-value">{{ fa_price($stats['total_sales']) }}</div>
                    <div class="ds-stat-label">مجموع فروش (تومان)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- آخرین سفارش‌ها --}}
        <div class="col-xl-8">
            <div class="ds-card p-3">
                <div class="ds-page-head pb-2 mb-2 border-bottom border-secondary-subtle">
                    <h2 class="ds-page-title">آخرین سفارش‌ها</h2>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-ds-outline btn-sm">
                        همه سفارش‌ها <i class="fa-solid fa-arrow-left me-1"></i>
                    </a>
                </div>

                @if($latestOrders->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table ds-table align-middle">
                            <thead>
                                <tr>
                                    <th>شماره</th>
                                    <th>مشتری</th>
                                    <th>اقلام</th>
                                    <th>مبلغ</th>
                                    <th>وضعیت</th>
                                    <th>تاریخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestOrders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" class="text-info" dir="ltr">
                                                {{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td>{{ $order->customer_name }}</td>
                                        <td>{{ fa_num($order->items_count) }}</td>
                                        <td class="text-success">{{ fa_price($order->total) }}</td>
                                        <td>
                                            <span class="badge text-bg-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                                        </td>
                                        <td class="text-muted small" dir="ltr">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="ds-empty py-4">
                        <i class="fa-solid fa-receipt"></i>
                        <p class="mb-0">هنوز سفارشی ثبت نشده است.</p>
                    </div>
                @endif
            </div>

            {{-- فروش ۷ روز اخیر --}}
            <div class="ds-card p-3 mt-4">
                <h2 class="ds-page-title mb-3">فروش ۷ روز اخیر (تومان)</h2>
                <div class="px-2">
                    @php($maxTotal = max($salesChart->max('total'), 1))
                    <div class="ds-chart-bars">
                        @foreach($salesChart as $day)
                            <div class="d-flex flex-column align-items-center flex-fill">
                                <div class="ds-chart-bar w-100"
                                     style="height: {{ max(4, round($day['total'] / $maxTotal * 110)) }}px"
                                     title="{{ fa_price($day['total']) }} تومان"></div>
                                <div class="ds-chart-label">{{ $day['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- موجودی کم --}}
        <div class="col-xl-4">
            <div class="ds-card p-3 h-100">
                <div class="ds-page-head pb-2 mb-2 border-bottom border-secondary-subtle">
                    <h2 class="ds-page-title">موجودی رو به اتمام</h2>
                    <span class="badge text-bg-warning">{{ fa_num($stats['low_stock_count']) }}</span>
                </div>

                @if($lowStockProducts->isNotEmpty())
                    <div class="list-group list-group-flush">
                        @foreach($lowStockProducts as $product)
                            <div class="list-group-item bg-transparent border-secondary-subtle px-0 d-flex align-items-center gap-3">
                                <img src="{{ $product->imageUrl() }}" alt="{{ $product->title }}" class="ds-thumb">
                                <div class="flex-grow-1 text-truncate">
                                    <div class="fw-bold text-truncate">{{ $product->title }}</div>
                                    <small class="text-muted">{{ $product->category?->name }}</small>
                                </div>
                                <span class="badge {{ $product->stock === 0 ? 'text-bg-danger' : 'text-bg-warning' }}">
                                    {{ fa_num($product->stock) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="ds-empty py-4">
                        <i class="fa-solid fa-thumbs-up"></i>
                        <p class="mb-0">موجودی همه محصولات مناسب است.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

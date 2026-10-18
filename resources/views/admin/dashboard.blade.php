@extends('layouts.admin')

@section('title', 'داشبورد')
@section('page_title', 'داشبورد مدیریت')

@section('content')
    {{-- شاخص‌های فروش --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="ds-stat-card">
                <div class="ds-stat-icon green"><i class="fa-solid fa-sack-dollar"></i></div>
                <div>
                    <div class="ds-stat-value">{{ fa_price($kpis['today_sales']) }}</div>
                    <div class="ds-stat-label">فروش امروز (تومان)</div>
                    <div class="ds-stat-sub">{{ fa_num($kpis['today_orders']) }} سفارش امروز</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="ds-stat-card">
                <div class="ds-stat-icon cyan"><i class="fa-solid fa-chart-line"></i></div>
                <div>
                    <div class="ds-stat-value">{{ fa_price($kpis['month_sales']) }}</div>
                    <div class="ds-stat-label">فروش این ماه (تومان)</div>
                    <div class="ds-stat-sub">{{ fa_num($kpis['month_orders']) }} سفارش این ماه</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="ds-stat-card">
                <div class="ds-stat-icon amber"><i class="fa-solid fa-vault"></i></div>
                <div>
                    <div class="ds-stat-value">{{ fa_price($kpis['total_sales']) }}</div>
                    <div class="ds-stat-label">مجموع فروش کل (تومان)</div>
                    <div class="ds-stat-sub">{{ fa_num($kpis['orders_count']) }} سفارش در کل</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="ds-stat-card">
                <div class="ds-stat-icon purple"><i class="fa-solid fa-scale-balanced"></i></div>
                <div>
                    <div class="ds-stat-value">{{ fa_price($kpis['average_order']) }}</div>
                    <div class="ds-stat-label">میانگین ارزش سفارش (تومان)</div>
                    <div class="ds-stat-sub">{{ fa_num($kpis['effective_orders_count']) }} سفارش مؤثر</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- نمودار فروش --}}
        <div class="col-xl-8">
            <div class="ds-card p-3">
                <div class="ds-page-head pb-2 mb-3 border-bottom border-secondary-subtle">
                    <h2 class="ds-page-title">فروش {{ fa_num($salesChart->count()) }} روز اخیر</h2>
                    <span class="text-muted small">تومان</span>
                </div>

                @if($salesChart->sum('sales') > 0)
                    @php($maxTotal = max($salesChart->max('sales'), 1))
                    <div class="px-2">
                        <div class="ds-chart-bars" style="height:150px">
                            @foreach($salesChart as $day)
                                <div class="d-flex flex-column align-items-center flex-fill h-100">
                                    <div class="ds-chart-value">
                                        {{ $day['sales'] > 0 ? fa_price($day['sales']) : '&nbsp;' }}
                                    </div>
                                    <div class="d-flex align-items-end flex-fill w-100">
                                        <div class="ds-chart-bar w-100"
                                             style="height: {{ max(4, round($day['sales'] / $maxTotal * 118)) }}px"
                                             title="{{ fa_num($day['orders']) }} سفارش — {{ fa_price($day['sales']) }} تومان"></div>
                                    </div>
                                    <div class="ds-chart-label">{{ $day['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="ds-empty py-4">
                        <i class="fa-solid fa-chart-simple"></i>
                        <p class="mb-0">هنوز فروشی در این بازه ثبت نشده است.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- وضعیت سفارش‌ها --}}
        <div class="col-xl-4">
            <div class="ds-card p-3 h-100">
                <div class="ds-page-head pb-2 mb-2 border-bottom border-secondary-subtle">
                    <h2 class="ds-page-title">وضعیت سفارش‌ها</h2>
                    <span class="badge text-bg-secondary">{{ fa_num($kpis['orders_count']) }}</span>
                </div>

                @foreach($ordersByStatus as $row)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ $row['label'] }}</span>
                            <span class="text-muted">{{ fa_num($row['count']) }} · {{ fa_num($row['percent']) }}٪</span>
                        </div>
                        <div class="progress" style="height:6px" role="progressbar"
                             aria-valuenow="{{ $row['percent'] }}" aria-valuemin="0" aria-valuemax="100"
                             aria-label="{{ $row['label'] }}">
                            <div class="progress-bar bg-{{ $row['color'] }}" style="width: {{ $row['percent'] }}%"></div>
                        </div>
                    </div>
                @endforeach

                <div class="small text-muted border-top border-secondary-subtle pt-2 mt-2">
                    <i class="fa-solid fa-users ms-1"></i>
                    {{ fa_num($kpis['new_customers']) }} کاربر جدید این ماه
                    ·
                    {{ fa_num($kpis['products_count']) }} محصول
                    ({{ fa_num($kpis['active_products_count']) }} فعال)
                </div>
            </div>
        </div>

        {{-- پرفروش‌ترین محصولات --}}
        <div class="col-xl-8">
            <div class="ds-card p-3">
                <div class="ds-page-head pb-2 mb-2 border-bottom border-secondary-subtle">
                    <h2 class="ds-page-title">پرفروش‌ترین محصولات</h2>
                    <span class="text-muted small">بر اساس تعداد فروش</span>
                </div>

                @if($bestSellers->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table ds-table align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>محصول</th>
                                    <th>فروش</th>
                                    <th>درآمد</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bestSellers as $i => $row)
                                    <tr>
                                        <td>
                                            <span class="badge rounded-circle {{ $i === 0 ? 'text-bg-warning' : 'text-bg-secondary' }}">
                                                {{ fa_num($i + 1) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="{{ $row['product']->imageUrl() }}" alt="{{ $row['product']->title }}" class="ds-thumb">
                                                <div class="text-truncate">
                                                    <div class="fw-bold text-truncate">{{ $row['product']->title }}</div>
                                                    <small class="text-muted">{{ $row['product']->category?->name }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ fa_num($row['sold']) }} عدد</td>
                                        <td class="text-success">
                                            {{ fa_price($row['revenue']) }}
                                            <small class="text-muted">تومان</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="ds-empty py-4">
                        <i class="fa-solid fa-crown"></i>
                        <p class="mb-0">هنوز فروشی ثبت نشده است.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- موجودی کم --}}
        <div class="col-xl-4">
            <div class="ds-card p-3 h-100">
                <div class="ds-page-head pb-2 mb-2 border-bottom border-secondary-subtle">
                    <h2 class="ds-page-title">موجودی رو به اتمام</h2>
                    <div class="d-flex gap-1">
                        @if($kpis['out_of_stock_count'] > 0)
                            <span class="badge text-bg-danger">ناموجود: {{ fa_num($kpis['out_of_stock_count']) }}</span>
                        @endif
                        <span class="badge text-bg-warning">{{ fa_num($kpis['low_stock_count']) }}</span>
                    </div>
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

        {{-- آخرین سفارش‌ها --}}
        <div class="col-12">
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
        </div>
    </div>
@endsection

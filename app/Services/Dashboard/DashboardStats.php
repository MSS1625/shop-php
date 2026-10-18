<?php

namespace App\Services\Dashboard;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * آمار و گزارش‌های داشبورد مدیریت
 *
 * تمام کوئری‌های داشبورد در این سرویس جمع شده‌اند تا کنترلر لاغر بماند
 * و بتوان همهٔ اعداد را مستقیم و بدون مرورگر تست کرد.
 *
 * قاعدهٔ مشترک: سفارش‌های لغوشده در هیچ عدد فروشی حساب نمی‌شوند.
 */
class DashboardStats
{
    /** آستانهٔ هشدار موجودی — از config خوانده می‌شود */
    private function lowStockThreshold(): int
    {
        return max(0, (int) config('shop.dashboard.low_stock_threshold', 3));
    }

    /**
     * شاخص‌های کلیدی داشبورد — همهٔ مبالغ «تومان» و همهٔ اعداد صحیح
     *
     * @return array<string, int>
     */
    public function kpis(): array
    {
        $monthStart = now()->startOfMonth();

        $totals = Order::query()
            ->whereNot('status', Order::STATUS_CANCELLED)
            ->selectRaw('COUNT(*) as orders_count, COALESCE(SUM(total), 0) as sales_sum')
            ->first();

        $today = Order::query()
            ->whereNot('status', Order::STATUS_CANCELLED)
            ->whereDate('created_at', now()->toDateString())
            ->selectRaw('COUNT(*) as orders_count, COALESCE(SUM(total), 0) as sales_sum')
            ->first();

        $month = Order::query()
            ->whereNot('status', Order::STATUS_CANCELLED)
            ->whereBetween('created_at', [$monthStart, now()->endOfDay()])
            ->selectRaw('COUNT(*) as orders_count, COALESCE(SUM(total), 0) as sales_sum')
            ->first();

        $effectiveOrders = (int) ($totals->orders_count ?? 0);
        $totalSales = (int) ($totals->sales_sum ?? 0);

        return [
            'products_count' => Product::count(),
            'active_products_count' => Product::active()->count(),
            'orders_count' => Order::count(),
            'pending_orders_count' => Order::where('status', Order::STATUS_PENDING)->count(),

            'total_sales' => $totalSales,
            'today_sales' => (int) ($today->sales_sum ?? 0),
            'today_orders' => (int) ($today->orders_count ?? 0),
            'month_sales' => (int) ($month->sales_sum ?? 0),
            'month_orders' => (int) ($month->orders_count ?? 0),

            'effective_orders_count' => $effectiveOrders,
            'average_order' => (int) round($totalSales / max($effectiveOrders, 1)),

            'new_customers' => User::where('is_admin', false)
                ->whereBetween('created_at', [$monthStart, now()])
                ->count(),

            'low_stock_count' => Product::where('stock', '<=', $this->lowStockThreshold())->count(),
            'out_of_stock_count' => Product::where('stock', 0)->count(),
        ];
    }

    /**
     * سری فروش روزانه برای نمودار — یک کوئری گروهی به‌جای یک کوئری برای هر روز
     *
     * @param  int  $days  تعداد روزهای سری (بین ۲ تا ۹۰)
     * @return Collection<int, array{date: Carbon, label: string, sales: int, orders: int}>
     */
    public function salesSeries(int $days = 14): Collection
    {
        $days = max(2, min(90, $days));
        $start = now()->startOfDay()->subDays($days - 1);

        $rows = Order::query()
            ->whereNot('status', Order::STATUS_CANCELLED)
            ->whereBetween('created_at', [$start, now()->endOfDay()])
            ->selectRaw('DATE(created_at) as day_date, COUNT(*) as orders_count, COALESCE(SUM(total), 0) as sales_sum')
            ->groupBy('day_date')
            ->get()
            ->keyBy('day_date');

        return Collection::times($days, function (int $n) use ($rows, $days) {
            $day = now()->startOfDay()->subDays($days - $n);
            $row = $rows->get($day->toDateString());

            return [
                'date' => $day,
                'label' => fa_num($day->format('m/d')),
                'sales' => (int) ($row?->sales_sum ?? 0),
                'orders' => (int) ($row?->orders_count ?? 0),
            ];
        });
    }

    /**
     * پرفروش‌ترین محصولات — بر اساس تعداد فروش (اقلام سفارش‌های لغوشده حساب نمی‌شوند)
     *
     * @return Collection<int, array{product: Product, sold: int, revenue: int}>
     */
    public function bestSellers(int $limit = 5): Collection
    {
        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNot('orders.status', Order::STATUS_CANCELLED)
            ->selectRaw('order_items.product_id, SUM(order_items.quantity) as sold, COALESCE(SUM(order_items.quantity * order_items.unit_price), 0) as revenue')
            ->groupBy('order_items.product_id')
            ->orderByDesc('sold')
            ->limit(max(1, $limit))
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        $products = Product::with('category')
            ->whereIn('id', $rows->pluck('product_id'))
            ->get()
            ->keyBy('id');

        return $rows->map(function ($row) use ($products) {
            $product = $products->get($row->product_id);

            return $product === null ? null : [
                'product' => $product,
                'sold' => (int) $row->sold,
                'revenue' => (int) $row->revenue,
            ];
        })->filter()->values();
    }

    /**
     * توزیع سفارش‌ها بر اساس وضعیت — برای نوارهای پیشرفت داشبورد
     *
     * @return array<int, array{status: string, label: string, color: string, count: int, percent: int}>
     */
    public function ordersByStatus(): array
    {
        $counts = Order::query()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $total = (int) $counts->sum();

        return array_map(function (string $status) use ($counts, $total) {
            $count = (int) ($counts[$status] ?? 0);

            return [
                'status' => $status,
                'label' => Order::STATUS_LABELS[$status],
                'color' => Order::STATUS_COLORS[$status],
                'count' => $count,
                'percent' => $total > 0 ? (int) round($count / $total * 100) : 0,
            ];
        }, Order::STATUSES);
    }

    /** آخرین سفارش‌ها همراه تعداد اقلام */
    public function latestOrders(int $take = 8): Collection
    {
        return Order::query()
            ->withCount('items')
            ->latest()
            ->take(max(1, $take))
            ->get();
    }

    /** محصولات با موجودی کم — از کمترین موجودی به بالا */
    public function lowStock(int $take = 6): Collection
    {
        return Product::query()
            ->with('category')
            ->where('stock', '<=', $this->lowStockThreshold())
            ->orderBy('stock')
            ->take(max(1, $take))
            ->get();
    }
}

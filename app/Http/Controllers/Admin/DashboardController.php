<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * داشبورد مدیریت — آمار کلی و آخرین سفارش‌ها
     */
    public function index(): View
    {
        $stats = [
            'products_count' => Product::count(),
            'active_products_count' => Product::active()->count(),
            'orders_count' => Order::count(),
            'pending_orders_count' => Order::where('status', Order::STATUS_PENDING)->count(),
            'total_sales' => Order::whereNot('status', Order::STATUS_CANCELLED)->sum('total'),
            'low_stock_count' => Product::where('stock', '<=', 3)->count(),
        ];

        $latestOrders = Order::withCount('items')
            ->latest()
            ->take(8)
            ->get();

        $lowStockProducts = Product::with('category')
            ->where('stock', '<=', 3)
            ->orderBy('stock')
            ->take(6)
            ->get();

        // فروش ۷ روز اخیر برای نمودار
        $salesChart = collect(range(6, 0))->map(function ($daysAgo) {
            $day = now()->subDays($daysAgo);
            $total = Order::whereNot('status', Order::STATUS_CANCELLED)
                ->whereDate('created_at', $day->toDateString())
                ->sum('total');

            return [
                'label' => fa_num($day->format('m/d')),
                'total' => (int) $total,
            ];
        });

        return view('admin.dashboard', compact('stats', 'latestOrders', 'lowStockProducts', 'salesChart'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardStats;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * داشبورد مدیریت — گزارش فروش، وضعیت سفارش‌ها و هشدارهای موجودی
     *
     * تمام آمار از سرویس DashboardStats خوانده می‌شود؛
     * کنترلر فقط داده را به نمای مربوطه می‌رساند.
     */
    public function index(DashboardStats $stats): View
    {
        return view('admin.dashboard', [
            'kpis' => $stats->kpis(),
            'salesChart' => $stats->salesSeries((int) config('shop.dashboard.sales_chart_days', 14)),
            'bestSellers' => $stats->bestSellers(),
            'ordersByStatus' => $stats->ordersByStatus(),
            'latestOrders' => $stats->latestOrders(),
            'lowStockProducts' => $stats->lowStock(),
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Dashboard\DashboardStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /**
     * یک سفارش با اقلام دلخواه — created_at قابل تنظیم برای تست بازه‌های زمانی
     *
     * @param  array<int, array{product: Product, quantity: int}>  $items
     */
    private function orderWithItems(array $attrs, array $items, ?Carbon $at = null): Order
    {
        $order = Order::factory()->create($attrs);

        if ($at !== null) {
            $order->forceFill(['created_at' => $at])->save();
        }

        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product']->id,
                'product_title' => $item['product']->title,
                'unit_price' => $item['product']->price,
                'quantity' => $item['quantity'],
            ]);
        }

        return $order;
    }

    /**
     * دیتای مشترک گزارش‌ها:
     *  P1 «سرور تست یک»    ۵۰٬۰۰۰ تومان
     *  P2 «سوییچ تست دو»  ۲۰۰٬۰۰۰ تومان
     *  P3 «روتر تست سه»  ۱۰۰٬۰۰۰ تومان
     *  P4 «کابل تست چهار» ۵۰۰٬۰۰۰ تومان — فقط در سفارش لغوشده
     *
     * سفارش‌ها:
     *  A — امروز، در انتظار، ۱۰۰٬۰۰۰ (P1 × 2)
     *  B — امروز، لغوشده، ۵۰۰٬۰۰۰ (P4 × 1) → در هیچ عددی حساب نمی‌شود
     *  C — ابتدای ماه، تحویل‌شده، ۲۰۰٬۰۰۰ (P2 × 1)
     *  D — ۴۰ روز قبل، در حال پردازش، ۴۰۰٬۰۰۰ (P3 × 4) → فقط در مجموع کل
     */
    private function seedReportData(): array
    {
        User::factory()->create(); // مشتری جدید این ماه

        $p1 = Product::factory()->create(['title' => 'سرور تست یک', 'price' => 50000, 'stock' => 10]);
        $p2 = Product::factory()->create(['title' => 'سوییچ تست دو', 'price' => 200000, 'stock' => 10]);
        $p3 = Product::factory()->create(['title' => 'روتر تست سه', 'price' => 100000, 'stock' => 10]);
        $p4 = Product::factory()->create(['title' => 'کابل تست چهار', 'price' => 500000, 'stock' => 10]);

        $monthStart = now()->startOfMonth();

        $this->orderWithItems(
            ['status' => Order::STATUS_PENDING, 'total' => 100000],
            [['product' => $p1, 'quantity' => 2]],
        );
        $this->orderWithItems(
            ['status' => Order::STATUS_CANCELLED, 'total' => 500000],
            [['product' => $p4, 'quantity' => 1]],
        );
        $this->orderWithItems(
            ['status' => Order::STATUS_DELIVERED, 'total' => 200000],
            [['product' => $p2, 'quantity' => 1]],
            $monthStart,
        );
        $this->orderWithItems(
            ['status' => Order::STATUS_PROCESSING, 'total' => 400000],
            [['product' => $p3, 'quantity' => 4]],
            now()->subDays(40),
        );

        return ['p1' => $p1, 'p2' => $p2, 'p3' => $p3, 'p4' => $p4, 'monthStart' => $monthStart];
    }

    public function test_guest_is_redirected_from_the_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_customer_is_forbidden_from_the_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_dashboard_renders_all_report_sections(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeInOrder([
                'فروش امروز',
                'وضعیت سفارش‌ها',
                'پرفروش‌ترین محصولات',
                'موجودی رو به اتمام',
                'آخرین سفارش‌ها',
            ]);
    }

    public function test_kpis_exclude_cancelled_orders_and_split_by_period(): void
    {
        $this->seedReportData();

        // اگر امروز اولِ ماه باشد، سفارش C هم «امروز» حساب می‌شود
        $cIsToday = now()->startOfMonth()->isSameDay(now());
        $todaySales = 100000 + ($cIsToday ? 200000 : 0);
        $todayOrders = 1 + ($cIsToday ? 1 : 0);

        $stats = app(DashboardStats::class)->kpis();

        $this->assertSame(100000 + ($cIsToday ? 200000 : 0), $stats['today_sales']);
        $this->assertSame($todayOrders, $stats['today_orders']);
        $this->assertSame(300000, $stats['month_sales']);   // A + C — لغوشده و ماه قبل حساب نمی‌شوند
        $this->assertSame(2, $stats['month_orders']);
        $this->assertSame(700000, $stats['total_sales']);    // A + C + D
        $this->assertSame(4, $stats['orders_count']);        // شامل لغوشده
        $this->assertSame(1, $stats['pending_orders_count']);
        $this->assertSame(3, $stats['effective_orders_count']);
        $this->assertSame(233333, $stats['average_order']);  // round(700000 / 3)
        $this->assertSame(1, $stats['new_customers']);       // ادمین حساب نمی‌شود
        $this->assertSame(0, $stats['low_stock_count']);
        $this->assertSame(0, $stats['out_of_stock_count']);

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(fa_price($stats['today_sales']))
            ->assertSee(fa_price(300000))
            ->assertSee(fa_price(700000))
            ->assertSee(fa_price(233333));
    }

    public function test_sales_chart_covers_configured_days_with_one_row_per_day(): void
    {
        $this->seedReportData();

        $cIsToday = now()->startOfMonth()->isSameDay(now());
        $todaySales = 100000 + ($cIsToday ? 200000 : 0);

        $series = app(DashboardStats::class)->salesSeries(14);

        $this->assertCount(14, $series);

        // اولین روز = ۱۳ روز قبل، آخرین روز = امروز
        $this->assertTrue($series->first()['date']->isSameDay(now()->subDays(13)));
        $this->assertTrue($series->last()['date']->isSameDay(now()));

        // امروز: فقط سفارش A (لغوشده B هیچ‌جا ظاهر نمی‌شود)
        $this->assertSame($todaySales, $series->last()['sales']);
        $this->assertSame(1 + ($cIsToday ? 1 : 0), $series->last()['orders']);

        // روزِ سفارش C (ابتدای ماه) — فقط وقتی داخل پنجرهٔ ۱۴ روزه نمودار باشد دیده می‌شود
        $cWithinWindow = now()->startOfMonth()->gte(now()->startOfDay()->subDays(13));

        if ($cWithinWindow) {
            $cDay = $series->firstWhere('date', now()->startOfMonth());
            $this->assertNotNull($cDay);
            $this->assertSame($cIsToday ? $todaySales : 200000, $cDay['sales']);
        } else {
            $this->assertNull($series->firstWhere('date', now()->startOfMonth()));
        }

        // رندر صفحه: مقدار روی ستون امروز
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(fa_num(1 + ($cIsToday ? 1 : 0)).' سفارش — '.fa_price($todaySales).' تومان')
            ->assertSee('فروش '.fa_num(14).' روز اخیر');
    }

    public function test_best_sellers_rank_by_sold_and_skip_cancelled_items(): void
    {
        $this->seedReportData();

        $best = app(DashboardStats::class)->bestSellers();

        $this->assertSame(3, $best->count()); // P4 فقط در سفارش لغوشده است → حذف

        $this->assertSame('روتر تست سه', $best[0]['product']->title); // 4 عدد
        $this->assertSame(4, $best[0]['sold']);
        $this->assertSame(400000, $best[0]['revenue']);

        $this->assertSame('سرور تست یک', $best[1]['product']->title); // 2 عدد
        $this->assertSame(2, $best[1]['sold']);
        $this->assertSame(100000, $best[1]['revenue']);

        $this->assertSame('سوییچ تست دو', $best[2]['product']->title); // 1 عدد
        $this->assertSame(1, $best[2]['sold']);
        $this->assertSame(200000, $best[2]['revenue']);

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['روتر تست سه', 'سرور تست یک', 'سوییچ تست دو'])
            ->assertDontSee('کابل تست چهار');
    }

    public function test_orders_by_status_breakdown_with_percentages(): void
    {
        $this->seedReportData();

        $breakdown = app(DashboardStats::class)->ordersByStatus();

        $this->assertSame(5, count($breakdown));

        $byStatus = collect($breakdown)->keyBy('status');

        $this->assertSame(1, $byStatus['pending']['count']);
        $this->assertSame(1, $byStatus['processing']['count']);
        $this->assertSame(0, $byStatus['shipped']['count']);
        $this->assertSame(1, $byStatus['delivered']['count']);
        $this->assertSame(1, $byStatus['cancelled']['count']);

        // ۴ سفارش → هر وضعیت غیرصفر ۲۵٪
        $this->assertSame(25, $byStatus['pending']['percent']);
        $this->assertSame(0, $byStatus['shipped']['percent']);

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('۲۵٪')
            ->assertSee('۰٪');
    }

    public function test_low_stock_panel_respects_default_threshold(): void
    {
        $zero = Product::factory()->create(['title' => 'مودم تست صفر', 'stock' => 0]);
        $three = Product::factory()->create(['title' => 'مودم تست سه', 'stock' => 3]);
        Product::factory()->create(['title' => 'مودم تست چهار', 'stock' => 4]); // بالای آستانه
        Product::factory()->create(['title' => 'مودم تست نه', 'stock' => 9]);   // بالای آستانه

        $low = app(DashboardStats::class)->lowStock();
        $kpis = app(DashboardStats::class)->kpis();

        $this->assertSame(2, $low->count()); // ۰ و ۳ — مرتب از کمترین
        $this->assertSame($zero->id, $low[0]->id);
        $this->assertSame($three->id, $low[1]->id);
        $this->assertSame(2, $kpis['low_stock_count']);
        $this->assertSame(1, $kpis['out_of_stock_count']);

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['مودم تست صفر', 'مودم تست سه'])
            ->assertDontSee('مودم تست چهار')
            ->assertDontSee('مودم تست نه')
            ->assertSee('ناموجود: '.fa_num(1));
    }

    public function test_low_stock_threshold_is_configurable(): void
    {
        config(['shop.dashboard.low_stock_threshold' => 5]);

        Product::factory()->create(['title' => 'آنتن تست پنج', 'stock' => 4]); // حالا زیر آستانه
        Product::factory()->create(['title' => 'آنتن تست ده', 'stock' => 10]); // هنوز بالای آستانه

        $low = app(DashboardStats::class)->lowStock();

        $this->assertTrue($low->contains('title', 'آنتن تست پنج'));
        $this->assertFalse($low->contains('title', 'آنتن تست ده'));

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('آنتن تست پنج')
            ->assertDontSee('آنتن تست ده');
    }

    public function test_dashboard_renders_empty_states_on_a_fresh_install(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('هنوز فروشی در این بازه ثبت نشده است')
            ->assertSee('هنوز فروشی ثبت نشده است')
            ->assertSee('موجودی همه محصولات مناسب است')
            ->assertSee('هنوز سفارشی ثبت نشده است');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * کشف هوشمند محصولات (v2.8.0):
 * فیلتر بازهٔ قیمت، فیلتر موجودی، مرتب‌سازی پرفروش‌ترین و ارتقای محصولات مشابه
 */
class ProductDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------
    // فیلتر بازهٔ قیمت
    // ---------------------------------------------------------------------

    public function test_price_min_filter_excludes_cheaper_products(): void
    {
        $this->productAt('کابل ارزان', 100000);
        $this->productAt('سوییچ متوسط', 500000);
        $this->productAt('روتر گران', 1000000);

        $this->get('/products?min=500000')
            ->assertOk()
            ->assertSee('سوییچ متوسط')
            ->assertSee('روتر گران')
            ->assertDontSee('کابل ارزان');
    }

    public function test_price_max_filter_excludes_expensive_products(): void
    {
        $this->productAt('کابل ارزان', 100000);
        $this->productAt('سوییچ متوسط', 500000);
        $this->productAt('روتر گران', 1000000);

        $this->get('/products?max=500000')
            ->assertOk()
            ->assertSee('کابل ارزان')
            ->assertSee('سوییچ متوسط')
            ->assertDontSee('روتر گران');
    }

    public function test_price_range_combines_min_and_max(): void
    {
        $this->productAt('کابل ارزان', 100000);
        $this->productAt('سوییچ متوسط', 500000);
        $this->productAt('روتر گران', 1000000);

        $this->get('/products?min=200000&max=800000')
            ->assertOk()
            ->assertSee('سوییچ متوسط')
            ->assertDontSee('کابل ارزان')
            ->assertDontSee('روتر گران');
    }

    public function test_swapped_price_range_is_normalized_silently(): void
    {
        // حداقل > حداکثر → بدون خطا جابه‌جا می‌شود؛ کاربر نتیجهٔ خالی نمی‌بیند
        $this->productAt('کابل ارزان', 100000);
        $this->productAt('روتر گران', 1000000);

        $this->get('/products?min=1000000&max=100000')
            ->assertOk()
            ->assertSee('کابل ارزان')
            ->assertSee('روتر گران');
    }

    public function test_price_input_accepts_persian_digits_and_separators(): void
    {
        $this->productAt('کابل ارزان', 100000);
        $this->productAt('سوییچ متوسط', 500000);

        // ۵۰۰٬۰۰۰ با کیبورد فارسی + جداکنندهٔ هزارگان
        $this->get('/products?min='.rawurlencode('۵۰۰٬۰۰۰'))
            ->assertOk()
            ->assertSee('سوییچ متوسط')
            ->assertDontSee('کابل ارزان');
    }

    public function test_non_numeric_price_input_is_ignored_gracefully(): void
    {
        $this->productAt('کابل ارزان', 100000);

        // ورودی بی‌معنی → فیلتر نادیده گرفته می‌شود، نه خطای ۵۰۰
        $this->get('/products?min=abc')
            ->assertOk()
            ->assertSee('کابل ارزان');
    }

    // ---------------------------------------------------------------------
    // فیلتر موجودی
    // ---------------------------------------------------------------------

    public function test_in_stock_filter_hides_out_of_stock_products(): void
    {
        $this->productAt('کابل موجود', 100000, 5);
        $this->productAt('مودم ناموجود', 500000, 0);

        $this->get('/products?in_stock=1')
            ->assertOk()
            ->assertSee('کابل موجود')
            ->assertDontSee('مودم ناموجود');
    }

    // ---------------------------------------------------------------------
    // مرتب‌سازی پرفروش‌ترین
    // ---------------------------------------------------------------------

    public function test_popular_sort_ranks_by_units_sold(): void
    {
        $a = $this->productAt('پرفروش اول', 100000, 10);
        $b = $this->productAt('پرفروش دوم', 200000, 10);
        $c = $this->productAt('بی‌فروش سوم', 300000, 10);

        $this->orderWithItems([['product' => $b, 'quantity' => 2]]);
        $this->orderWithItems([['product' => $a, 'quantity' => 5]]);

        $this->get('/products?sort=popular')
            ->assertOk()
            ->assertSeeInOrder(['پرفروش اول', 'پرفروش دوم', 'بی‌فروش سوم']);
    }

    public function test_popular_sort_ignores_cancelled_order_items(): void
    {
        $real = $this->productAt('فروش واقعی', 100000, 10);
        $cancelledOnly = $this->productAt('فروش لغوشده', 200000, 10);

        $this->orderWithItems([['product' => $real, 'quantity' => 1]], Order::STATUS_PROCESSING);
        $this->orderWithItems([['product' => $cancelledOnly, 'quantity' => 50]], Order::STATUS_CANCELLED);

        // ۵۰ عددِ سفارش لغوشده حساب نمی‌شود → محصول اول همان فروش واقعی است
        $this->get('/products?sort=popular')
            ->assertOk()
            ->assertSeeInOrder(['فروش واقعی', 'فروش لغوشده']);
    }

    public function test_popular_sort_breaks_ties_with_newest(): void
    {
        $older = $this->productAt('قدیمی‌تر بی‌فروش', 100000, 10, now()->subDays(3));
        $newer = $this->productAt('جدیدتر بی‌فروش', 200000, 10, now()->subDay());

        $this->get('/products?sort=popular')
            ->assertOk()
            ->assertSeeInOrder(['جدیدتر بی‌فروش', 'قدیمی‌تر بی‌فروش']);
    }

    // ---------------------------------------------------------------------
    // ترکیب فیلترها + صفحه‌بندی
    // ---------------------------------------------------------------------

    public function test_filters_combine_with_search_category_stock_and_sort(): void
    {
        $cables = Category::factory()->create(['name' => 'کابل‌ها', 'slug' => 'cables']);
        $switches = Category::factory()->create(['name' => 'سوییچ‌ها', 'slug' => 'switches']);

        $cable1 = $this->productAt('کابل cat5', 100000, 5, null, $cables);
        $this->productAt('کابل cat6', 2000000, 0, null, $cables); // ناموجود → حذف با فیلتر موجودی
        $cable3 = $this->productAt('کابل cat7', 150000, 5, null, $cables);
        $this->productAt('سوییچ ۸ پورت', 300000, 5, null, $switches); // دستهٔ دیگر

        // جستجوی «کابل» + بازهٔ قیمت + فقط موجود + پرفروش‌ترین
        $this->orderWithItems([['product' => $cable1, 'quantity' => 1]]);
        $this->orderWithItems([['product' => $cable3, 'quantity' => 3]]);

        $this->get('/products?q='.rawurlencode('کابل').'&category=cables&min=50000&max=1000000&in_stock=1&sort=popular')
            ->assertOk()
            ->assertSeeInOrder(['کابل cat7', 'کابل cat5'])
            ->assertDontSee('کابل cat6')
            ->assertDontSee('سوییچ ۸ پورت');
    }

    public function test_pagination_keeps_filter_query_string(): void
    {
        // ۱۴ محصول در بازهٔ قیمت → دو صفحه
        for ($i = 0; $i < 14; $i++) {
            $this->productAt('محصول صفحه‌بندی '.$i, 600000);
        }

        $response = $this->get('/products?min=500000&in_stock=1');
        $response->assertOk();

        // لینک صفحهٔ ۲ باید فیلترها را حفظ کند (withQueryString) —
        // مستقل از ترتیب پارامترها
        $this->assertMatchesRegularExpression(
            '/min=500000&(?:amp;)?[^"\\s]*page=2/u',
            $response->getContent(),
        );
    }

    public function test_active_filter_indicator_and_reset_link_are_shown(): void
    {
        $this->productAt('کابل ارزان', 100000);

        $this->get('/products?min=50000')
            ->assertOk()
            ->assertSee('فیلتر فعال')
            ->assertSee('حذف فیلترها');

        $this->get('/products')
            ->assertOk()
            ->assertDontSee('فیلتر فعال');
    }

    public function test_empty_state_offers_filter_reset(): void
    {
        $this->productAt('کابل ارزان', 100000);

        $this->get('/products?min=99999999')
            ->assertOk()
            ->assertSee('محصولی مطابق جستجوی شما پیدا نشد')
            ->assertSee('حذف فیلترها');
    }

    // ---------------------------------------------------------------------
    // اعتبارسنجی و رگرسیون
    // ---------------------------------------------------------------------

    public function test_invalid_category_slug_fails_validation(): void
    {
        $this->get('/products?category=does-not-exist')
            ->assertSessionHasErrors('category');
    }

    public function test_invalid_sort_value_fails_validation(): void
    {
        $this->get('/products?sort=bogus')
            ->assertSessionHasErrors('sort');
    }

    public function test_inactive_products_never_appear_even_with_filters(): void
    {
        $this->productAt('کابل موجود', 100000, 5);
        Product::factory()->create([
            'title' => 'محصول مخفی گران',
            'price' => 100000,
            'stock' => 5,
            'is_active' => false,
        ]);

        $this->get('/products?min=50000&in_stock=1&sort=popular')
            ->assertOk()
            ->assertSee('کابل موجود')
            ->assertDontSee('محصول مخفی گران');
    }

    // ---------------------------------------------------------------------
    // محصولات مشابه — موجود اول، بعد جدیدترین‌ها
    // ---------------------------------------------------------------------

    public function test_related_products_prefer_in_stock_then_newest(): void
    {
        $category = Category::factory()->create();

        $this->productAt('مشابه قدیمی موجود', 100000, 5, now()->subDays(3), $category);
        $this->productAt('مشابه جدید موجود', 200000, 5, now()->subDays(2), $category);
        $this->productAt('مشابه قدیمی ناموجود', 300000, 0, now()->subDay(), $category);
        $this->productAt('مشابه جدید ناموجود', 400000, 0, now(), $category);

        $current = $this->productAt('محصول اصلی', 500000, 5, now(), $category);

        // موجودها (جدید به قدیم) قبل از ناموجودها (جدید به قدیم)
        $this->get('/products/'.$current->slug)
            ->assertOk()
            ->assertSeeInOrder([
                'مشابه جدید موجود',
                'مشابه قدیمی موجود',
                'مشابه جدید ناموجود',
                'مشابه قدیمی ناموجود',
            ]);
    }

    public function test_related_products_exclude_self_inactive_and_other_categories(): void
    {
        $own = Category::factory()->create();
        $other = Category::factory()->create();

        $current = $this->productAt('محصول اصلی', 100000, 5, null, $own);
        $this->productAt('هم‌دسته مخفی', 200000, 5, null, $own, false);
        $this->productAt('هم‌دسته مجاز', 300000, 5, null, $own);
        $this->productAt('دستهٔ دیگر', 400000, 5, null, $other);

        $response = $this->get('/products/'.$current->slug);

        $response->assertOk()
            ->assertSee('هم‌دسته مجاز')
            ->assertDontSee('هم‌دسته مخفی')
            ->assertDontSee('دستهٔ دیگر');
    }

    public function test_related_products_fall_back_to_out_of_stock_when_slots_remain(): void
    {
        $category = Category::factory()->create();

        $current = $this->productAt('محصول اصلی', 100000, 5, null, $category);
        $this->productAt('موجود یک', 200000, 5, null, $category);
        $this->productAt('ناموجود جانشین', 300000, 0, null, $category);

        // فقط یک هم‌دستهٔ موجود هست → ناموجود جای خالی چهارمین اسلات را پر می‌کند
        $this->get('/products/'.$current->slug)
            ->assertOk()
            ->assertSee('موجود یک')
            ->assertSee('ناموجود جانشین');
    }

    public function test_related_products_section_hidden_when_no_siblings(): void
    {
        $category = Category::factory()->create();
        $current = $this->productAt('محصول تنها', 100000, 5, null, $category);

        $this->get('/products/'.$current->slug)
            ->assertOk()
            ->assertDontSee('محصولات مشابه');
    }

    // ---------------------------------------------------------------------
    // هلپرها
    // ---------------------------------------------------------------------

    private function productAt(
        string $title,
        int $price,
        int $stock = 10,
        ?Carbon $createdAt = null,
        ?Category $category = null,
        bool $isActive = true,
    ): Product {
        static $counter = 0;

        $product = Product::factory()->make([
            'title' => $title,
            'slug' => 'discovery-'.++$counter,
            'price' => $price,
            'stock' => $stock,
            'is_active' => $isActive,
            'category_id' => $category?->id ?? Category::factory()->create()->id,
        ]);

        $product->saveQuietly();

        if ($createdAt !== null) {
            $product->forceFill(['created_at' => $createdAt])->save();
        }

        return $product->refresh();
    }

    /**
     * سفارش با اقلام مشخص — وضعیت پیش‌فرض: تحویل‌شده (فروش واقعی)
     *
     * @param  array<int, array{product: Product, quantity: int}>  $items
     */
    private function orderWithItems(array $items, string $status = Order::STATUS_DELIVERED): Order
    {
        $order = Order::factory()->create(['status' => $status]);

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
}

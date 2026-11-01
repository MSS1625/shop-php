<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Question;
use App\Models\Review;
use App\Models\User;
use App\Services\Compare;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * مقایسهٔ محصولات (v2.13.0):
 * فهرست سشن‌محور بدون نیاز به ورود، سقف ۴ محصول، جدول کنار-به-کنار با بهترین قیمت
 */
class CompareTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------
    // افزودن و حذف (toggle) — مهمان هم می‌تواند
    // ---------------------------------------------------------------------

    public function test_guest_can_add_product_to_compare_without_login(): void
    {
        $product = $this->product('کابل شبکه CAT6');

        $this->from(route('compare.index'))
            ->post(route('compare.toggle', $product))
            ->assertRedirect(route('compare.index'))
            ->assertSessionHas('success');

        $this->assertSame(1, app(Compare::class)->count());
        $this->assertTrue(app(Compare::class)->has($product->id));
    }

    public function test_second_toggle_removes_the_product(): void
    {
        $product = $this->product('مودم TD-LTE');

        $this->from(route('compare.index'))
            ->post(route('compare.toggle', $product))
            ->assertSessionHas('success');

        $this->from(route('compare.index'))
            ->post(route('compare.toggle', $product))
            ->assertRedirect(route('compare.index'))
            ->assertSessionHas('success');

        $this->assertSame(0, app(Compare::class)->count());
    }

    public function test_inactive_product_cannot_be_added(): void
    {
        $product = $this->product('سوییچ خاموش', false);

        $this->post(route('compare.toggle', $product))
            ->assertNotFound();

        $this->assertSame(0, app(Compare::class)->count());
    }

    public function test_fifth_product_is_rejected_with_warning(): void
    {
        $first = $this->product('کابل CAT6');
        $second = $this->product('کریمپر شبکه');
        $third = $this->product('تستر شبکه');
        $fourth = $this->product('سوییچ ۸ پورت');
        $fifth = $this->product('مودم TD-LTE');

        foreach ([$first, $second, $third, $fourth] as $product) {
            $this->post(route('compare.toggle', $product));
        }

        $this->assertSame(4, app(Compare::class)->count());

        $this->from(route('compare.index'))
            ->post(route('compare.toggle', $fifth))
            ->assertRedirect(route('compare.index'))
            ->assertSessionHas('warning');

        // فهرست همان ۴ تای اول می‌ماند؛ محصول پنجم وارد نشده است
        $this->assertSame(4, app(Compare::class)->count());
        $this->assertFalse(app(Compare::class)->has($fifth->id));

        $this->get(route('compare.index'))
            ->assertOk()
            ->assertSee($first->title)
            ->assertDontSee($fifth->title);
    }

    public function test_ajax_toggle_returns_json_shape(): void
    {
        $product = $this->product('کابل شبکه CAT6');

        $this->post(route('compare.toggle', $product), [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('inCompare', true)
            ->assertJsonPath('full', false)
            ->assertJsonPath('count', 1);

        $this->post(route('compare.toggle', $product), [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])
            ->assertOk()
            ->assertJsonPath('inCompare', false)
            ->assertJsonPath('count', 0);
    }

    public function test_full_ajax_response_marks_full_flag(): void
    {
        $products = [];
        foreach (['کابل CAT6', 'کریمپر شبکه', 'تستر شبکه', 'سوییچ ۸ پورت'] as $title) {
            $products[] = $this->product($title);
        }

        foreach ($products as $product) {
            $this->post(route('compare.toggle', $product));
        }

        $fifth = $this->product('مودم TD-LTE');

        $this->post(route('compare.toggle', $fifth), [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('inCompare', false)
            ->assertJsonPath('full', true)
            ->assertJsonPath('count', 4);
    }

    // ---------------------------------------------------------------------
    // صفحهٔ مقایسه
    // ---------------------------------------------------------------------

    public function test_compare_page_shows_empty_state(): void
    {
        $this->get(route('compare.index'))
            ->assertOk()
            ->assertSee('هنوز محصولی برای مقایسه انتخاب نکرده‌اید')
            ->assertSee('رفتن به محصولات');
    }

    public function test_compare_page_lists_products_side_by_side(): void
    {
        $catA = Category::factory()->create(['name' => 'ابزار شبکه']);
        $catB = Category::factory()->create(['name' => 'تجهیزات شبکه']);

        $first = $this->product('کابل CAT6', category: $catA, price: 500000, stock: 12);
        $second = $this->product('کریمپر شبکه', category: $catB, price: 1200000, stock: 3);
        $third = $this->product('مودم TD-LTE', category: $catB, price: 2500000, stock: 7);

        foreach ([$first, $second, $third] as $product) {
            $this->post(route('compare.toggle', $product));
        }

        $response = $this->get(route('compare.index'));
        $response->assertOk();

        foreach ([$first, $second, $third] as $product) {
            $response
                ->assertSee($product->title)
                ->assertSee($product->category->name)
                ->assertSee(route('shop.products.show', $product));
        }

        $response
            ->assertSee(fa_price(500000))
            ->assertSee(fa_price(1200000))
            ->assertSee(fa_price(2500000))
            ->assertSee('موجودی انبار')
            ->assertSee('امتیاز کاربران')
            ->assertSee('پرسش‌های پاسخ‌داده')
            ->assertSee(route('cart.store', $first));
    }

    public function test_cheapest_product_is_highlighted_as_best_price(): void
    {
        $first = $this->product('کابل CAT6', price: 900000);
        $second = $this->product('کریمپر شبکه', price: 1200000);
        $third = $this->product('مودم TD-LTE', price: 2500000);

        foreach ([$first, $second, $third] as $product) {
            $this->post(route('compare.toggle', $product));
        }

        $content = $this->get(route('compare.index'))->getContent();

        // فقط ارزان‌ترین ستون بج می‌گیرد — دقیقاً یک بج در جدول
        // (الگوی متنی «بهترین قیمت» به‌خاطر تگ‌لاین فوتر دوبار شمرده می‌شود؛
        //  «خرید امن، سریع و با بهترین قیمت» — پس کلاس بج را می‌شماریم)
        $this->assertSame(1, substr_count($content, 'ds-compare-best-badge'));
        $this->assertSame(1, substr_count($content, 'text-center ds-compare-best"'));

        // و قیمت ارزان‌ترین همان ستونِ برجسته است
        $this->assertStringContainsString(fa_price(900000), $content);
    }

    public function test_rating_summary_shows_on_compare_page(): void
    {
        $reviewer = User::factory()->create();

        $first = $this->product('کابل CAT6');
        $second = $this->product('مودم TD-LTE');
        $third = $this->product('تستر شبکه');

        $this->review($first, $reviewer, 4);
        $this->review($first, User::factory()->create(), 5);
        $this->review($second, $reviewer, 2);

        foreach ([$first, $second, $third] as $product) {
            $this->post(route('compare.toggle', $product));
        }

        $this->get(route('compare.index'))
            ->assertOk()
            ->assertSee('۴٫۵')   // میانگین (۴+۵)/۲ برای کابل
            ->assertSee('۲٫۰')   // تنها نظر مودم
            ->assertSee('هنوز نظری ندارد'); // تستر بدون نظر
    }

    public function test_answered_questions_count_shows_on_compare_page(): void
    {
        $asker = User::factory()->create();

        $first = $this->product('کابل CAT6');

        // ۲ پاسخ‌داده + ۱ در انتظار — شمارش فقط پاسخ‌داده‌ها
        $this->question($first, $asker, 'گارانتی دارد؟', 'دارد، یک‌ساله.');
        $this->question($first, $asker, 'طول کابل؟', '۲ متر.');
        Question::create([
            'product_id' => $first->id,
            'user_id' => $asker->id,
            'body' => 'رنگ دیگه داره؟',
            'status' => Question::STATUS_PENDING,
        ]);

        $second = $this->product('مودم TD-LTE');

        foreach ([$first, $second] as $product) {
            $this->post(route('compare.toggle', $product));
        }

        $content = $this->get(route('compare.index'))->getContent();

        // «۲ پاسخ» فقط برای محصول اول؛ «۰ پاسخ» برای دومی
        $this->assertSame(1, preg_match('/۲ پاسخ/u', $content), $content);
        $this->assertSame(1, preg_match('/۰ پاسخ/u', $content), $content);
    }

    public function test_out_of_stock_product_shows_namowjood_and_disabled_cart(): void
    {
        $first = $this->product('کابل CAT6', stock: 5);
        $second = $this->product('مودم TD-LTE', stock: 0);

        foreach ([$first, $second] as $product) {
            $this->post(route('compare.toggle', $product));
        }

        $content = $this->get(route('compare.index'))->getContent();

        // «ناموجود» دوبار رندر می‌شود: یک‌بار در ردیف موجودی، یک‌بار روی دکمهٔ خرید
        $this->assertSame(2, preg_match_all('/ناموجود/u', $content), $content);
    }

    // ---------------------------------------------------------------------
    // حذف و خالی کردن
    // ---------------------------------------------------------------------

    public function test_remove_from_compare_page_updates_the_table(): void
    {
        $first = $this->product('کابل CAT6');
        $second = $this->product('مودم TD-LTE');

        foreach ([$first, $second] as $product) {
            $this->post(route('compare.toggle', $product));
        }

        $this->from(route('compare.index'))
            ->post(route('compare.toggle', $first))
            ->assertRedirect(route('compare.index'));

        // پیام فلش سشن خودش عنوان محصول را دارد؛ ستون‌بودن را با URL تأیید می‌کنیم
        $this->get(route('compare.index'))
            ->assertOk()
            ->assertDontSee(route('shop.products.show', $first))
            ->assertSee(route('shop.products.show', $second));
    }

    public function test_clear_empties_the_whole_list(): void
    {
        $first = $this->product('کابل CAT6');
        $second = $this->product('مودم TD-LTE');

        foreach ([$first, $second] as $product) {
            $this->post(route('compare.toggle', $product));
        }

        $this->assertSame(2, app(Compare::class)->count());

        $this->post(route('compare.clear'))
            ->assertRedirect(route('compare.index'))
            ->assertSessionHas('success');

        $this->assertSame(0, app(Compare::class)->count());

        $this->get(route('compare.index'))
            ->assertOk()
            ->assertSee('هنوز محصولی برای مقایسه انتخاب نکرده‌اید');
    }

    // ---------------------------------------------------------------------
    // ناوبار و دکمه‌ها
    // ---------------------------------------------------------------------

    public function test_navbar_shows_compare_link_and_live_badge(): void
    {
        $product = $this->product('کابل CAT6');

        // خالی: بج مخفی — رشتهٔ خام (بدون escape)
        $this->get(route('shop.home'))
            ->assertOk()
            ->assertSee(route('compare.index'))
            ->assertSee('id="dsCompareCount" style="display:none"', false);

        // با یک محصول: بج نمایان — دیگر نسخهٔ مخفی در خروجی نیست
        $this->post(route('compare.toggle', $product));

        $this->get(route('shop.home'))
            ->assertOk()
            ->assertDontSee('id="dsCompareCount" style="display:none"', false);
    }

    public function test_product_cards_show_compare_button(): void
    {
        $this->product('کابل شبکه CAT6');

        $this->get(route('shop.products.index'))
            ->assertOk()
            ->assertSee('افزودن به مقایسه');
    }

    public function test_product_detail_page_shows_big_compare_button(): void
    {
        $product = $this->product('کابل شبکه CAT6');

        $this->get(route('shop.products.show', $product))
            ->assertOk()
            ->assertSee('افزودن به مقایسه')
            ->assertSee('ds-compare-btn-lg');
    }

    // ---------------------------------------------------------------------
    // یکپارچگی سشن
    // ---------------------------------------------------------------------

    public function test_inactive_product_is_pruned_on_view(): void
    {
        $first = $this->product('کابل CAT6');
        $second = $this->product('مودم TD-LTE');

        foreach ([$first, $second] as $product) {
            $this->post(route('compare.toggle', $product));
        }

        // محصول اول از فروشگاه برداشته شد
        $first->update(['is_active' => false]);

        $response = $this->get(route('compare.index'));

        $response->assertOk()
            ->assertDontSee($first->title)
            ->assertSee($second->title);

        // سشن همان‌جا تمیز شده — شمارندهٔ ناوبار همین پاسخ دیگر مخفی نیست
        $response->assertDontSee('id="dsCompareCount" style="display:none"', false);
        $this->assertSame(1, app(Compare::class)->count());
    }

    public function test_insertion_order_is_preserved_on_compare_page(): void
    {
        $third = $this->product('مودم TD-LTE');
        $first = $this->product('کابل CAT6');
        $second = $this->product('کریمپر شبکه');

        // سشن را مستقیم می‌نویسیم؛ مسیر POST توسط تست‌های قبلی پوشش داده شده و
        // پیام فلش آن عنوان آخرین محصول را بالای صفحه می‌آورد و جای‌ها را خراب می‌کند
        session()->put('compare', [$third->id, $first->id, $second->id]);

        $content = $this->get(route('compare.index'))->getContent();

        $posModem = mb_strpos($content, $third->title);
        $posCable = mb_strpos($content, $first->title);
        $posCrimp = mb_strpos($content, $second->title);

        $this->assertNotFalse($posModem);
        $this->assertNotFalse($posCable);
        $this->assertNotFalse($posCrimp);
        // assertLessThan(expected, actual): «actual کمتر از expected» — مودم، بعد کابل، بعد کریمپر
        $this->assertLessThan($posCable, $posModem);
        $this->assertLessThan($posCrimp, $posCable);
    }

    // ---------------------------------------------------------------------
    // هلپرها
    // ---------------------------------------------------------------------

    private function product(
        string $title,
        bool $isActive = true,
        ?Category $category = null,
        int $price = 500000,
        int $stock = 10,
    ): Product {
        static $counter = 0;

        return Product::factory()->create([
            'title' => $title,
            'slug' => 'cmp-'.++$counter,
            'price' => $price,
            'stock' => $stock,
            'is_active' => $isActive,
            'category_id' => ($category ?? Category::factory()->create())->id,
        ]);
    }

    private function review(Product $product, User $user, int $rating): Review
    {
        return Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => $rating,
            'comment' => 'کیفیت ساخت خوب بود و مطابق توضیح بود.',
            'status' => Review::STATUS_APPROVED,
        ]);
    }

    private function question(Product $product, User $user, string $body, string $answer): Question
    {
        return Question::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'body' => $body,
            'answer' => $answer,
            'status' => Question::STATUS_ANSWERED,
            'answered_at' => now(),
        ]);
    }
}

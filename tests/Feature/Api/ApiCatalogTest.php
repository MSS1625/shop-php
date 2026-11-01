<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\Question;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * کاتالوگ API (v2.12.0):
 * دسته‌بندی‌ها، لیست محصولات با همان فیلترهای وب، جزئیات کامل
 * یک-فراخوانی و زیرلیست نظرات/پرسش‌ها
 */
class ApiCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $title, array $attributes = []): Product
    {
        $category = Category::factory()->create();

        return Product::factory()->create(array_merge([
            'category_id' => $category->id,
            'title' => $title,
        ], $attributes));
    }

    // ---------------------------------------------------------------------
    // دسته‌بندی‌ها
    // ---------------------------------------------------------------------

    public function test_categories_only_return_those_with_active_products(): void
    {
        $withProducts = Category::factory()->create(['name' => 'کابل شبکه']);
        Product::factory()->create(['category_id' => $withProducts->id, 'is_active' => true]);
        Product::factory()->create(['category_id' => $withProducts->id, 'is_active' => false]);

        Category::factory()->create(['name' => 'دستهٔ خالی']);

        $this->getJson(route('api.v1.categories.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'کابل شبکه')
            ->assertJsonPath('data.0.products_count', 1);
    }

    // ---------------------------------------------------------------------
    // لیست محصولات
    // ---------------------------------------------------------------------

    public function test_products_list_returns_active_products_with_pagination_meta(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(15)->create(['category_id' => $category->id, 'is_active' => true]);
        Product::factory()->create(['category_id' => $category->id, 'is_active' => false]);

        $this->getJson(route('api.v1.products.index'))
            ->assertOk()
            ->assertJsonCount(12, 'data')
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonStructure([
                'data' => [[
                    'id', 'title', 'slug', 'price', 'price_fa', 'stock',
                    'in_stock', 'image_url', 'rating', 'category',
                ]],
                'links', 'meta',
            ]);
    }

    public function test_products_list_supports_search_category_price_and_sort(): void
    {
        $catA = Category::factory()->create();
        $catB = Category::factory()->create();

        $cheapA = Product::factory()->create(['category_id' => $catA->id, 'title' => 'کابل CAT6 دو متری', 'price' => 100_000]);
        $priceyA = Product::factory()->create(['category_id' => $catA->id, 'title' => 'سوئیچ ۲۴ پورت', 'price' => 9_000_000]);
        $cheapB = Product::factory()->create(['category_id' => $catB->id, 'title' => 'سوئیچ ۵ پورت رومیزی', 'price' => 200_000]);

        // جستجو
        $this->getJson(route('api.v1.products.index', ['q' => 'سوئیچ']))
            ->assertOk()
            ->assertJsonCount(2, 'data');

        // دسته
        $this->getJson(route('api.v1.products.index', ['category' => $catA->slug]))
            ->assertOk()
            ->assertJsonCount(2, 'data');

        // بازهٔ قیمت — با ارقام فارسی مثل خود فرم وب
        $this->getJson(route('api.v1.products.index', ['min' => '۱۵۰٬۰۰۰', 'max' => '500000']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $cheapB->id);

        // ارزان‌ترین
        $this->getJson(route('api.v1.products.index', ['sort' => 'cheapest']))
            ->assertOk()
            ->assertJsonPath('data.0.id', $cheapA->id)
            ->assertJsonPath('data.2.id', $priceyA->id);

        // فقط موجودها
        Product::factory()->create(['category_id' => $catA->id, 'stock' => 0]);
        $this->getJson(route('api.v1.products.index', ['in_stock' => '1']))
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_products_list_marks_wishlist_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $saved = $this->product('مودم TD-LTE');
        $other = $this->product('کابل CAT6');

        $user->wishlistProducts()->attach($saved->id);

        Sanctum::actingAs($user);

        $json = $this->getJson(route('api.v1.products.index'))
            ->assertOk()
            ->json('data');

        // برای کاربر واردشده هر محصولی این کلید را دارد؛ فقط همان ذخیره‌شده true است
        $flagged = collect($json)->where('is_in_wishlist', true);
        $this->assertCount(1, $flagged);
        $this->assertSame($saved->id, $flagged->first()['id']);
        $this->assertFalse(collect($json)->firstWhere('id', $other->id)['is_in_wishlist']);
    }

    // ---------------------------------------------------------------------
    // جزئیات محصول
    // ---------------------------------------------------------------------

    public function test_product_show_returns_full_detail_in_one_call(): void
    {
        $user = User::factory()->create();
        $buyer = User::factory()->create();
        $product = $this->product('مودم TD-LTE', ['price' => 2_500_000]);
        $product->images()->create(['path' => 'products/gallery-1.webp', 'alt' => 'نمای کنار', 'position' => 1]);

        // ۳ نظر تأییدشده + ۱ در انتظار
        $product->reviews()->createMany([
            ['user_id' => $user->id, 'rating' => 5, 'comment' => 'کیفیت عالی بود.', 'status' => Review::STATUS_APPROVED],
            ['user_id' => $buyer->id, 'rating' => 4, 'comment' => 'خوب بود.', 'status' => Review::STATUS_APPROVED],
            ['user_id' => User::factory()->create()->id, 'rating' => 1, 'comment' => 'بد بود.', 'status' => Review::STATUS_PENDING],
        ]);

        // ۱ پرسش پاسخ‌داده + ۱ بی‌جواب
        $product->questions()->create(['user_id' => $user->id, 'body' => 'گارانتی دارد؟', 'answer' => 'دارد، ۱۸ ماه.', 'status' => Question::STATUS_ANSWERED, 'answered_at' => now()]);
        $product->questions()->create(['user_id' => $user->id, 'body' => 'رنگ دیگری دارد؟', 'status' => Question::STATUS_PENDING]);

        // محصول مشابه هم‌دسته
        $related = Product::factory()->create(['category_id' => $product->category_id, 'title' => 'مودم دیگر']);

        Sanctum::actingAs($user);
        $user->wishlistProducts()->attach($product->id);

        $response = $this->getJson(route('api.v1.products.show', $product))
            ->assertOk()
            ->assertJsonPath('data.title', 'مودم TD-LTE')
            ->assertJsonPath('data.price', 2_500_000)
            ->assertJsonPath('data.price_fa', '۲٬۵۰۰٬۰۰۰')
            ->assertJsonPath('data.rating.average', 4.5)
            ->assertJsonPath('data.rating.count', 2)
            ->assertJsonPath('data.is_in_wishlist', true)
            ->assertJsonPath('data.images.0.alt', 'نمای کنار')
            ->assertJsonPath('data.reviews.meta.total', 2)
            ->assertJsonPath('data.questions.meta.total', 1)
            ->assertJsonPath('data.related.0.title', 'مودم دیگر');

        // تفکیک امتیازها: ۵★ یکبار، ۴★ یکبار (مستقل از ترتیب کلیدها)
        $this->assertEqualsCanonicalizing(
            [5 => 1, 4 => 1],
            $response->json('data.rating_breakdown')
        );

        // نظر در انتظار و پرسش بی‌جواب در پاسخ نیستند
        $this->assertNotContains('بد بود.', $response->json('data.reviews.data.*.comment'));
        $this->assertNotContains('رنگ دیگری دارد؟', $response->json('data.questions.data.*.body'));
    }

    public function test_product_show_hides_inactive_product(): void
    {
        $product = $this->product('محصول خاموش', ['is_active' => false]);

        $this->getJson(route('api.v1.products.show', $product))
            ->assertNotFound();
    }

    public function test_product_sub_lists_show_verified_buyer_badge(): void
    {
        $owner = User::factory()->create();
        $buyer = User::factory()->create();
        $product = $this->product('سوئیچ ۸ پورت');

        // خریدار واقعی → نظر + سفارش غیرلغوشده
        $product->reviews()->create(['user_id' => $buyer->id, 'rating' => 5, 'comment' => 'خریدار واقعی هستم.', 'status' => Review::STATUS_APPROVED]);
        $order = $buyer->orders()->create([
            'order_number' => 'DS-260101-TEST',
            'customer_name' => $buyer->name,
            'customer_phone' => '09120000000',
            'customer_address' => 'تهران',
            'subtotal' => $product->price,
            'total' => $product->price,
            'status' => 'delivered',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_title' => $product->title,
            'unit_price' => $product->price,
            'quantity' => 1,
        ]);

        $product->reviews()->create(['user_id' => $owner->id, 'rating' => 3, 'comment' => 'فقط تماشاچی بوده‌ام.', 'status' => Review::STATUS_APPROVED]);

        $reviews = $this->getJson(route('api.v1.products.reviews.index', $product))
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $byUser = collect($reviews->json('data'))->keyBy('user.id');
        $this->assertTrue($byUser[$buyer->id]['verified_buyer']);
        $this->assertFalse($byUser[$owner->id]['verified_buyer']);
    }

    public function test_questions_sub_list_only_returns_answered(): void
    {
        $user = User::factory()->create();
        $product = $this->product('تستر شبکه');

        $product->questions()->create(['user_id' => $user->id, 'body' => 'پاسخ دارد', 'answer' => 'بله', 'status' => Question::STATUS_ANSWERED, 'answered_at' => now()]);
        $product->questions()->create(['user_id' => $user->id, 'body' => 'بی‌جواب', 'status' => Question::STATUS_PENDING]);
        $product->questions()->create(['user_id' => $user->id, 'body' => 'ردشده', 'status' => Question::STATUS_REJECTED]);

        $this->getJson(route('api.v1.products.questions.index', $product))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.body', 'پاسخ دارد')
            ->assertJsonPath('data.0.answer', 'بله');
    }
}

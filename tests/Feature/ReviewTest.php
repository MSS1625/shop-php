<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * نظرات و امتیازدهی محصولات (v2.9.0):
 * ثبت نظر با جریان بررسی، نشان خریدار تأییدشده، میانگین امتیاز و مدیریت نظرات
 */
class ReviewTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------
    // ثبت نظر — دسترسی و اعتبارسنجی
    // ---------------------------------------------------------------------

    public function test_guest_is_redirected_to_login(): void
    {
        $product = $this->product('کابل شبکه CAT6');

        $this->post("/products/{$product->slug}/reviews", [
            'rating' => 5,
            'comment' => 'کیفیت عالی بود.',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_rating_is_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post("/products/{$this->product('مودم TD-LTE')->slug}/reviews", [
                'comment' => 'سرعت خوبی دارد.',
            ])
            ->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_rating_must_be_between_one_and_five(): void
    {
        $user = User::factory()->create();
        $product = $this->product('سوییچ ۸ پورت');

        foreach ([0, 6, 99] as $bad) {
            $this->actingAs($user)
                ->post("/products/{$product->slug}/reviews", [
                    'rating' => $bad,
                    'comment' => 'تست اعتبارسنجی امتیاز.',
                ])
                ->assertSessionHasErrors('rating');
        }

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_comment_is_required_with_minimum_length(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post("/products/{$this->product('کریمپر شبکه')->slug}/reviews", [
                'rating' => 4,
                'comment' => '',
            ])
            ->assertSessionHasErrors('comment');

        $this->actingAs($user)
            ->post("/products/{$this->product('کریمپر حرفه‌ای')->slug}/reviews", [
                'rating' => 4,
                'comment' => 'کم',
            ])
            ->assertSessionHasErrors('comment');
    }

    public function test_authenticated_user_can_submit_review_which_starts_as_pending(): void
    {
        $user = User::factory()->create();
        $product = $this->product('کابل شبکه CAT6');

        $this->actingAs($user)
            ->post("/products/{$product->slug}/reviews", [
                'rating' => 5,
                'comment' => 'کیفیت ساخت بسیار خوب بود و بدون مشکل کار می‌کند.',
            ])
            ->assertRedirect(route('shop.products.show', $product))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
            'status' => Review::STATUS_PENDING,
        ]);
    }

    public function test_user_cannot_submit_second_review_for_same_product(): void
    {
        $user = User::factory()->create();
        $product = $this->product('مودم TD-LTE');

        $product->reviews()->create([
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => 'نظر قبلی من دربارهٔ این محصول.',
            'status' => Review::STATUS_APPROVED,
        ]);

        $this->actingAs($user)
            ->post("/products/{$product->slug}/reviews", [
                'rating' => 1,
                'comment' => 'تلاش برای ثبت نظر دوم.',
            ])
            ->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_user_can_review_different_products(): void
    {
        $user = User::factory()->create();
        $a = $this->product('کابل شبکه CAT6');
        $b = $this->product('کابل فیبر نوری');

        $this->actingAs($user)
            ->post("/products/{$a->slug}/reviews", ['rating' => 5, 'comment' => 'نظر برای محصول اول.'])
            ->assertSessionHasNoErrors();

        $this->actingAs($user)
            ->post("/products/{$b->slug}/reviews", ['rating' => 4, 'comment' => 'نظر برای محصول دوم.'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('reviews', 2);
    }

    public function test_review_cannot_be_submitted_for_inactive_product(): void
    {
        $user = User::factory()->create();
        $product = $this->product('محصول غیرفعال', false);

        $this->actingAs($user)
            ->post("/products/{$product->slug}/reviews", [
                'rating' => 5,
                'comment' => 'این محصول دیگر فعال نیست.',
            ])
            ->assertNotFound();
    }

    public function test_review_submission_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $product = $this->product("محصول شماره {$i}");

            $this->actingAs($user)
                ->post("/products/{$product->slug}/reviews", [
                    'rating' => 5,
                    'comment' => "نظر شماره {$i} برای تست محدودیت نرخ.",
                ])
                ->assertSessionHasNoErrors();
        }

        $this->actingAs($user)
            ->post("/products/{$this->product('محصول ششمی')->slug}/reviews", [
                'rating' => 5,
                'comment' => 'این درخواست ششم باید مسدود شود.',
            ])
            ->assertStatus(429);
    }

    // ---------------------------------------------------------------------
    // نمایش — تأیید مدیر پیش‌نیاز دیده‌شدن است
    // ---------------------------------------------------------------------

    public function test_pending_review_is_not_visible_on_product_page(): void
    {
        $product = $this->product('مودم TD-LTE');

        $product->reviews()->create([
            'user_id' => User::factory()->create(['name' => 'کاربر آزمایشی'])->id,
            'rating' => 5,
            'comment' => 'این نظر هنوز تأیید نشده است.',
            'status' => Review::STATUS_PENDING,
        ]);

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertDontSee('این نظر هنوز تأیید نشده است.')
            ->assertDontSee('کاربر آزمایشی');
    }

    public function test_approved_review_is_visible_on_product_page(): void
    {
        $product = $this->product('مودم TD-LTE');

        $product->reviews()->create([
            'user_id' => User::factory()->create(['name' => 'سارا محمدی'])->id,
            'rating' => 5,
            'comment' => 'بعد از دو ماه استفاده هنوز بدون مشکل کار می‌کند.',
            'status' => Review::STATUS_APPROVED,
        ]);

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertSee('سارا محمدی')
            ->assertSee('بعد از دو ماه استفاده هنوز بدون مشکل کار می‌کند.');
    }

    public function test_rejected_review_is_not_visible_on_product_page(): void
    {
        $product = $this->product('سوییچ ۸ پورت');

        $product->reviews()->create([
            'user_id' => User::factory()->create()->id,
            'rating' => 1,
            'comment' => 'این نظر توسط مدیر رد شده است.',
            'status' => Review::STATUS_REJECTED,
        ]);

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertDontSee('این نظر توسط مدیر رد شده است.');
    }

    // ---------------------------------------------------------------------
    // خلاصهٔ امتیازها و کارت محصول
    // ---------------------------------------------------------------------

    public function test_product_page_shows_average_rating_and_count(): void
    {
        $product = $this->product('سوییچ ۸ پورت');

        foreach ([[5, 'عالی'], [4, 'خوب']] as [$rating, $word]) {
            $product->reviews()->create([
                'user_id' => User::factory()->create()->id,
                'rating' => $rating,
                'comment' => "نظر {$word} برای تست میانگین.",
                'status' => Review::STATUS_APPROVED,
            ]);
        }

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertSee('۴٫۵')
            ->assertSee('۲ نظر تأییدشده');
    }

    public function test_rating_breakdown_bars_are_rendered(): void
    {
        $product = $this->product('کابل فیبر نوری');

        foreach ([5, 5, 1] as $rating) {
            $product->reviews()->create([
                'user_id' => User::factory()->create()->id,
                'rating' => $rating,
                'comment' => 'نظر برای تست نمودار تفکیک.',
                'status' => Review::STATUS_APPROVED,
            ]);
        }

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertSee('width: 67%', false)
            ->assertSee('width: 33%', false);
    }

    public function test_only_approved_reviews_count_in_aggregates(): void
    {
        $product = $this->product('تستر شبکه');

        $statuses = [Review::STATUS_APPROVED, Review::STATUS_PENDING, Review::STATUS_REJECTED];

        foreach ($statuses as $status) {
            $product->reviews()->create([
                'user_id' => User::factory()->create()->id,
                'rating' => 5,
                'comment' => 'نظر با وضعیت‌های مختلف.',
                'status' => $status,
            ]);
        }

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertSee('۱ نظر تأییدشده');

        // کارت محصول در لیست هم فقط تأییدشده‌ها را می‌شمارد
        $this->get('/products')
            ->assertOk()
            ->assertSeeInOrder(['تستر شبکه', '۱ نظر']);
    }

    public function test_product_card_shows_rating_summary_on_listing(): void
    {
        $product = $this->product('مودم TD-LTE');

        $product->reviews()->create([
            'user_id' => User::factory()->create()->id,
            'rating' => 4,
            'comment' => 'نظر تأییدشده برای کارت محصول.',
            'status' => Review::STATUS_APPROVED,
        ]);

        $this->get('/products')
            ->assertOk()
            ->assertSee('۴٫۰')
            ->assertSee('۱ نظر');
    }

    // ---------------------------------------------------------------------
    // نشان خریدار تأییدشده
    // ---------------------------------------------------------------------

    public function test_verified_buyer_badge_is_shown_for_real_buyers(): void
    {
        $product = $this->product('مودم TD-LTE');
        $buyer = User::factory()->create(['name' => 'خریدار واقعی']);

        $order = Order::factory()->forUser($buyer)->create(['status' => Order::STATUS_DELIVERED]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'unit_price' => $product->price,
            'quantity' => 1,
        ]);

        $product->reviews()->create([
            'user_id' => $buyer->id,
            'rating' => 5,
            'comment' => 'واقعاً خریدم و راضی هستم.',
            'status' => Review::STATUS_APPROVED,
        ]);

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertSee('خریدار تأییدشده');
    }

    public function test_non_buyer_does_not_get_verified_badge(): void
    {
        $product = $this->product('مودم TD-LTE');

        $product->reviews()->create([
            'user_id' => User::factory()->create(['name' => 'فقط بازدیدکننده'])->id,
            'rating' => 3,
            'comment' => 'بدون خرید نظر داده‌ام.',
            'status' => Review::STATUS_APPROVED,
        ]);

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertDontSee('خریدار تأییدشده');
    }

    public function test_cancelled_order_does_not_make_buyer_verified(): void
    {
        $product = $this->product('سوییچ ۸ پورت');
        $buyer = User::factory()->create();

        $order = Order::factory()->forUser($buyer)->create(['status' => Order::STATUS_CANCELLED]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'unit_price' => $product->price,
            'quantity' => 1,
        ]);

        $product->reviews()->create([
            'user_id' => $buyer->id,
            'rating' => 5,
            'comment' => 'سفارشم لغو شده بود.',
            'status' => Review::STATUS_APPROVED,
        ]);

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertDontSee('خریدار تأییدشده');
    }

    // ---------------------------------------------------------------------
    // تجربهٔ کاربر — فرم / راهنمای ورود / وضعیت نظر خودِ کاربر
    // ---------------------------------------------------------------------

    public function test_guest_sees_login_prompt_instead_of_form(): void
    {
        $product = $this->product('مودم TD-LTE');

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertSee('وارد حساب کاربری')
            // فیلدهای فرم برای مهمان رندر نمی‌شود
            ->assertDontSee('امتیاز شما')
            ->assertDontSee('دیدگاه شما');
    }

    public function test_user_who_already_reviewed_sees_status_instead_of_form(): void
    {
        $user = User::factory()->create();
        $product = $this->product('مودم TD-LTE');

        $product->reviews()->create([
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => 'نظر ثبت‌شدهٔ قبلی.',
            'status' => Review::STATUS_PENDING,
        ]);

        $this->actingAs($user)
            ->get("/products/{$product->slug}")
            ->assertOk()
            ->assertSee('نظر شما با امتیاز ۴ ثبت شده است')
            ->assertSeeText(Review::STATUS_LABELS[Review::STATUS_PENDING]);
    }

    // ---------------------------------------------------------------------
    // پنل مدیریت — دسترسی و جریان بررسی
    // ---------------------------------------------------------------------

    public function test_admin_reviews_page_is_inaccessible_for_guests_and_customers(): void
    {
        // مهمان به ورود مدیر هدایت می‌شود (رفتار مستقر پنل)
        $this->get('/admin/reviews')->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get('/admin/reviews')
            ->assertForbidden();
    }

    public function test_admin_can_list_reviews_with_status_tabs(): void
    {
        $admin = $this->admin();
        $product = $this->product('مودم TD-LTE');

        foreach ([Review::STATUS_PENDING, Review::STATUS_APPROVED, Review::STATUS_REJECTED] as $status) {
            $product->reviews()->create([
                'user_id' => User::factory()->create()->id,
                'rating' => 4,
                'comment' => "نظر با وضعیت {$status}.",
                'status' => $status,
            ]);
        }

        $this->actingAs($admin)
            ->get('/admin/reviews')
            ->assertOk()
            ->assertSee('همه (۳)')
            ->assertSeeText(Review::STATUS_LABELS[Review::STATUS_PENDING].' (۱)');

        $this->actingAs($admin)
            ->get('/admin/reviews?status='.Review::STATUS_PENDING)
            ->assertOk()
            ->assertSee('نظر با وضعیت '.Review::STATUS_PENDING.'.')
            ->assertDontSee('نظر با وضعیت '.Review::STATUS_APPROVED.'.');
    }

    public function test_admin_can_approve_pending_review(): void
    {
        $admin = $this->admin();
        $product = $this->product('مودم TD-LTE');

        $review = $product->reviews()->create([
            'user_id' => User::factory()->create()->id,
            'rating' => 5,
            'comment' => 'نظر در انتظار تأیید.',
            'status' => Review::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->patch("/admin/reviews/{$review->id}/approve")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => Review::STATUS_APPROVED,
        ]);

        // حالا در صفحهٔ محصول دیده می‌شود
        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertSee('نظر در انتظار تأیید.');
    }

    public function test_admin_can_reject_approved_review(): void
    {
        $admin = $this->admin();
        $product = $this->product('مودم TD-LTE');

        $review = $product->reviews()->create([
            'user_id' => User::factory()->create()->id,
            'rating' => 2,
            'comment' => 'نظر تأییدشده برای رد شدن.',
            'status' => Review::STATUS_APPROVED,
        ]);

        $this->actingAs($admin)
            ->patch("/admin/reviews/{$review->id}/reject")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => Review::STATUS_REJECTED,
        ]);

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertDontSee('نظر تأییدشده برای رد شدن.');
    }

    public function test_admin_can_delete_review(): void
    {
        $admin = $this->admin();
        $product = $this->product('مودم TD-LTE');

        $review = $product->reviews()->create([
            'user_id' => User::factory()->create()->id,
            'rating' => 1,
            'comment' => 'نظر اسپم برای حذف.',
            'status' => Review::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->delete("/admin/reviews/{$review->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_moderation_actions_are_forbidden_for_customers(): void
    {
        $product = $this->product('مودم TD-LTE');

        $review = $product->reviews()->create([
            'user_id' => User::factory()->create()->id,
            'rating' => 5,
            'comment' => 'نظر قربانی تست دسترسی.',
            'status' => Review::STATUS_PENDING,
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->patch("/admin/reviews/{$review->id}/approve")
            ->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => Review::STATUS_PENDING,
        ]);
    }

    public function test_admin_sidebar_shows_pending_reviews_badge(): void
    {
        $product = $this->product('مودم TD-LTE');

        $product->reviews()->create([
            'user_id' => User::factory()->create()->id,
            'rating' => 3,
            'comment' => 'یک نظر در انتظار تأیید.',
            'status' => Review::STATUS_PENDING,
        ]);

        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertSee('نظرات');
    }

    // ---------------------------------------------------------------------
    // یکپارچگی داده
    // ---------------------------------------------------------------------

    public function test_deleting_product_cascades_to_reviews(): void
    {
        $admin = $this->admin();
        $product = $this->product('محصول موقتی');

        $product->reviews()->create([
            'user_id' => User::factory()->create()->id,
            'rating' => 5,
            'comment' => 'نظر همراه محصول حذف می‌شود.',
            'status' => Review::STATUS_APPROVED,
        ]);

        $product->delete();

        $this->assertDatabaseCount('reviews', 0);
    }

    // ---------------------------------------------------------------------
    // هلپرها
    // ---------------------------------------------------------------------

    private function product(string $title, bool $isActive = true): Product
    {
        static $counter = 0;

        return Product::factory()->create([
            'title' => $title,
            'slug' => 'review-'.++$counter,
            'price' => 500000,
            'stock' => 10,
            'is_active' => $isActive,
            'category_id' => Category::factory()->create()->id,
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }
}

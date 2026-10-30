<?php

namespace Tests\Feature;

use App\Mail\QuestionAnswered;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

/**
 * پرسش و پاسخ محصولات (v2.11.0):
 * ثبت پرسش، جریان پاسخ مدیر، ایمیل اطلاع‌رسانی و مدیریت پرسش‌ها
 */
class QuestionTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------------
    // ثبت پرسش — دسترسی و اعتبارسنجی
    // ---------------------------------------------------------------------

    public function test_guest_is_redirected_to_login(): void
    {
        $product = $this->product('کابل شبکه CAT6');

        $this->post("/products/{$product->slug}/questions", [
            'body' => 'این کابل برای گیکabit مناسب است؟',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('questions', 0);
    }

    public function test_body_is_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post("/products/{$this->product('مودم TD-LTE')->slug}/questions", [
                'body' => '',
            ])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('questions', 0);
    }

    public function test_body_has_minimum_length(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post("/products/{$this->product('سوییچ ۸ پورت')->slug}/questions", [
                'body' => 'کم',
            ])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('questions', 0);
    }

    public function test_authenticated_user_can_ask_which_starts_as_pending(): void
    {
        $user = User::factory()->create();
        $product = $this->product('کابل شبکه CAT6');

        $this->actingAs($user)
            ->post("/products/{$product->slug}/questions", [
                'body' => '  این کابل برای شبکهٔ گیگابیت مناسب است؟  ',
            ])
            ->assertRedirect(route('shop.products.show', $product))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('questions', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'body' => 'این کابل برای شبکهٔ گیگابیت مناسب است؟',
            'answer' => null,
            'status' => Question::STATUS_PENDING,
        ]);
    }

    public function test_user_can_ask_multiple_questions_for_same_product(): void
    {
        $user = User::factory()->create();
        $product = $this->product('مودم TD-LTE');

        // برخلاف نظرات، چند پرسش برای هر کاربر/محصول مجاز است
        $this->actingAs($user)
            ->post("/products/{$product->slug}/questions", [
                'body' => 'این مودم وای‌فای دارد؟',
            ])
            ->assertSessionHas('success');

        $this->actingAs($user)
            ->post("/products/{$product->slug}/questions", [
                'body' => 'گارانتی چند ساله است؟',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseCount('questions', 2);
    }

    public function test_inactive_product_returns_404(): void
    {
        $user = User::factory()->create();
        $product = $this->product('محصول غیرفعال', false);

        $this->actingAs($user)
            ->post("/products/{$product->slug}/questions", [
                'body' => 'این محصول دیگر فعال نیست.',
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('questions', 0);
    }

    public function test_submission_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $product = $this->product("محصول شماره {$i}");

            $this->actingAs($user)
                ->post("/products/{$product->slug}/questions", [
                    'body' => "پرسش شماره {$i} برای تست محدودیت نرخ.",
                ])
                ->assertSessionHasNoErrors();
        }

        $this->actingAs($user)
            ->post("/products/{$this->product('محصول ششمی')->slug}/questions", [
                'body' => 'این درخواست ششم باید مسدود شود.',
            ])
            ->assertStatus(429);
    }

    // ---------------------------------------------------------------------
    // نمایش — پاسخ مدیر پیش‌نیاز دیده‌شدن است
    // ---------------------------------------------------------------------

    public function test_pending_question_is_not_visible_on_product_page(): void
    {
        $product = $this->product('مودم TD-LTE');

        $product->questions()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'این پرسش هنوز پاسخ نگرفته است.',
            'status' => Question::STATUS_PENDING,
        ]);

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertDontSee('این پرسش هنوز پاسخ نگرفته است.');
    }

    public function test_rejected_question_is_not_visible_on_product_page(): void
    {
        $product = $this->product('سوییچ ۸ پورت');

        $product->questions()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'پرسشی که رد شده است.',
            'answer' => 'به دلایل مدیریت رد شد.',
            'status' => Question::STATUS_REJECTED,
        ]);

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertDontSee('پرسشی که رد شده است.');
    }

    public function test_answered_question_is_visible_on_product_page(): void
    {
        $product = $this->product('کابل شبکه CAT6');

        $product->questions()->create([
            'user_id' => User::factory()->create(['name' => 'پرسندهٔ نمونه'])->id,
            'body' => 'این کابل برای شبکهٔ گیگابیت مناسب است؟',
            'answer' => 'بله، تا سرعت یک گیگابیت پشتیبانی می‌کند.',
            'status' => Question::STATUS_ANSWERED,
            'answered_at' => now(),
        ]);

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertSee('این کابل برای شبکهٔ گیگابیت مناسب است؟')
            ->assertSee('بله، تا سرعت یک گیگابیت پشتیبانی می‌کند.')
            ->assertSee('پاسخ دیجی‌شاپ')
            ->assertSee('پرسندهٔ نمونه');
    }

    public function test_qa_heading_shows_answered_count(): void
    {
        $product = $this->product('مودم TD-LTE');

        foreach ([1, 2, 3] as $i) {
            $product->questions()->create([
                'user_id' => User::factory()->create()->id,
                'body' => "پرسش شماره {$i} برای شمارش.",
                'answer' => "پاسخ شماره {$i}.",
                'status' => Question::STATUS_ANSWERED,
                'answered_at' => now(),
            ]);
        }

        // پرسش پاسخ‌نگرفته در شمارنده حساب نمی‌شود
        $product->questions()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'پرسش در انتظار پاسخ.',
            'status' => Question::STATUS_PENDING,
        ]);

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertSee('۳ پرسش پاسخ‌داده‌شده');
    }

    public function test_qa_section_paginates_five_per_page(): void
    {
        $product = $this->product('سوییچ ۸ پورت');

        for ($i = 1; $i <= 6; $i++) {
            $product->questions()->create([
                'user_id' => User::factory()->create()->id,
                'body' => "متن پرسش شماره {$i} از شش پرسش.",
                'answer' => "پاسخ شماره {$i} صادر شد.",
                'status' => Question::STATUS_ANSWERED,
                'answered_at' => now(),
            ]);
        }

        // جدیدترین‌ها اول — پنج پرسش اخیر در صفحهٔ یک
        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertSee('پاسخ شماره 6 صادر شد.')
            ->assertDontSee('پاسخ شماره 1 صادر شد.');

        $this->get("/products/{$product->slug}?page=2")
            ->assertOk()
            ->assertSee('پاسخ شماره 1 صادر شد.');
    }

    // ---------------------------------------------------------------------
    // نشان خریدار تأییدشده — همان قانون نظرات، برای پرسنده‌ها
    // ---------------------------------------------------------------------

    public function test_verified_buyer_badge_is_shown_for_question_askers_who_bought(): void
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

        $product->questions()->create([
            'user_id' => $buyer->id,
            'body' => 'واقعاً خریدم، سرعتش چطور است؟',
            'answer' => 'سرعت بسیار رضایت‌بخش است.',
            'status' => Question::STATUS_ANSWERED,
            'answered_at' => now(),
        ]);

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertSee('خریدار تأییدشده');
    }

    public function test_non_buyer_asker_does_not_get_verified_badge(): void
    {
        $product = $this->product('مودم TD-LTE');

        $product->questions()->create([
            'user_id' => User::factory()->create(['name' => 'فقط بازدیدکننده'])->id,
            'body' => 'بدون خرید پرسیده‌ام.',
            'answer' => 'پاسخ فروشگاه.',
            'status' => Question::STATUS_ANSWERED,
            'answered_at' => now(),
        ]);

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertDontSee('خریدار تأییدشده');
    }

    public function test_cancelled_order_does_not_make_asker_verified(): void
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

        $product->questions()->create([
            'user_id' => $buyer->id,
            'body' => 'سفارشم لغو شده بود، بعداً می‌خرم.',
            'answer' => 'منتظر شما هستیم.',
            'status' => Question::STATUS_ANSWERED,
            'answered_at' => now(),
        ]);

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertDontSee('خریدار تأییدشده');
    }

    // ---------------------------------------------------------------------
    // تجربهٔ کاربر — فرم / راهنمای ورود / حالت خالی
    // ---------------------------------------------------------------------

    public function test_guest_sees_login_prompt_in_qa_section(): void
    {
        $product = $this->product('مودم TD-LTE');

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertSee('برای پرسیدن')
            ->assertDontSee('ثبت پرسش');
    }

    public function test_authenticated_user_sees_ask_form(): void
    {
        $product = $this->product('مودم TD-LTE');

        $this->actingAs(User::factory()->create())
            ->get("/products/{$product->slug}")
            ->assertOk()
            ->assertSee('پرسش شما از این محصول')
            ->assertSee('ثبت پرسش');
    }

    public function test_empty_state_is_shown_when_no_answered_questions(): void
    {
        $product = $this->product('کریمپر شبکه');

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertSee('هنوز پرسشی برای این محصول پاسخ داده نشده است');
    }

    // ---------------------------------------------------------------------
    // پنل مدیریت — دسترسی، فهرست و جریان پاسخ
    // ---------------------------------------------------------------------

    public function test_only_admins_can_access_questions_panel(): void
    {
        $this->get('/admin/questions')->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get('/admin/questions')
            ->assertForbidden();
    }

    public function test_admin_index_lists_questions_with_tabs_and_counts(): void
    {
        $admin = $this->admin();
        $product = $this->product('مودم TD-LTE');

        foreach ([Question::STATUS_PENDING, Question::STATUS_ANSWERED, Question::STATUS_REJECTED] as $status) {
            $product->questions()->create([
                'user_id' => User::factory()->create()->id,
                'body' => "پرسش با وضعیت {$status}.",
                'status' => $status,
                'answer' => $status === Question::STATUS_ANSWERED ? 'پاسخ داده شده.' : null,
                'answered_at' => $status === Question::STATUS_ANSWERED ? now() : null,
            ]);
        }

        $this->actingAs($admin)
            ->get('/admin/questions')
            ->assertOk()
            ->assertSeeInOrder(['همه', 'در انتظار پاسخ', 'پاسخ داده شد', 'ردشده'])
            ->assertSee('پرسش با وضعیت pending.');
    }

    public function test_admin_can_answer_pending_question(): void
    {
        $admin = $this->admin();
        $product = $this->product('مودم TD-LTE');
        $asker = User::factory()->create();

        $question = $product->questions()->create([
            'user_id' => $asker->id,
            'body' => 'گارانتی این محصول چند سال است؟',
            'status' => Question::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->patch("/admin/questions/{$question->id}/answer", [
                'answer' => 'یک سال گارانتی رسمی دارد.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'status' => Question::STATUS_ANSWERED,
            'answer' => 'یک سال گارانتی رسمی دارد.',
        ]);

        // حالا در صفحهٔ محصول دیده می‌شود
        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertSee('یک سال گارانتی رسمی دارد.');
    }

    public function test_answer_validation_requires_text(): void
    {
        $admin = $this->admin();
        $product = $this->product('مودم TD-LTE');

        $question = $product->questions()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'پرسش بدون پاسخ.',
            'status' => Question::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->patch("/admin/questions/{$question->id}/answer", ['answer' => ''])
            ->assertSessionHasErrors('answer');

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'status' => Question::STATUS_PENDING,
        ]);
    }

    // ---------------------------------------------------------------------
    // ایمیل اطلاع‌رسانی — یک بار برای پاسخ تازه، نه برای ویرایش
    // ---------------------------------------------------------------------

    public function test_answer_email_is_sent_to_asker(): void
    {
        Mail::fake();

        $admin = $this->admin();
        $product = $this->product('مودم TD-LTE');
        $asker = User::factory()->create(['email' => 'asker@example.com']);

        $question = $product->questions()->create([
            'user_id' => $asker->id,
            'body' => 'رنگ این محصول چیست؟',
            'status' => Question::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->patch("/admin/questions/{$question->id}/answer", [
                'answer' => 'رنگ مشکی مات است.',
            ]);

        Mail::assertSent(QuestionAnswered::class, fn ($mail) => $mail->hasTo('asker@example.com'));
    }

    public function test_rejecting_does_not_send_email(): void
    {
        Mail::fake();

        $admin = $this->admin();
        $product = $this->product('مودم TD-LTE');

        $question = $product->questions()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'پرسشی که رد می‌شود.',
            'status' => Question::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->patch("/admin/questions/{$question->id}/reject");

        Mail::assertNothingSent();
    }

    public function test_editing_answer_does_not_resend_email(): void
    {
        Mail::fake();

        $admin = $this->admin();
        $product = $this->product('مودم TD-LTE');

        $question = $product->questions()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'پرسش دو مرحله‌ای.',
            'status' => Question::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->patch("/admin/questions/{$question->id}/answer", ['answer' => 'پاسخ اولیه.']);

        $this->actingAs($admin)
            ->patch("/admin/questions/{$question->id}/answer", ['answer' => 'پاسخ ویرایش‌شده و کامل‌تر.']);

        Mail::assertSent(QuestionAnswered::class, 1);

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'answer' => 'پاسخ ویرایش‌شده و کامل‌تر.',
        ]);
    }

    public function test_email_failure_does_not_break_answering(): void
    {
        $admin = $this->admin();
        $product = $this->product('مودم TD-LTE');

        $question = $product->questions()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'پرسش همراه خطای ایمیل.',
            'status' => Question::STATUS_PENDING,
        ]);

        Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('SMTP down'));

        $this->actingAs($admin)
            ->patch("/admin/questions/{$question->id}/answer", [
                'answer' => 'پاسخ ثبت می‌شود حتی اگر ایمیل خطا بدهد.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'status' => Question::STATUS_ANSWERED,
            'answer' => 'پاسخ ثبت می‌شود حتی اگر ایمیل خطا بدهد.',
        ]);
    }

    public function test_editing_answer_keeps_first_answered_at(): void
    {
        $admin = $this->admin();
        $product = $this->product('مودم TD-LTE');

        $question = $product->questions()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'پرسش برای تاریخ پاسخ.',
            'status' => Question::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->patch("/admin/questions/{$question->id}/answer", ['answer' => 'پاسخ اول.']);

        // شبیه‌سازی گذر زمان — تاریخ پاسخ اولیه سه روز پیش
        $question->update(['answered_at' => now()->subDays(3)]);

        $this->actingAs($admin)
            ->patch("/admin/questions/{$question->id}/answer", ['answer' => 'پاسخ به‌روزشده.']);

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'answer' => 'پاسخ به‌روزشده.',
        ]);

        $this->assertTrue(
            $question->refresh()->answered_at->diffInDays(now()) >= 2,
            'answered_at باید تاریخ پاسخ اولیه را نگه دارد'
        );
    }

    public function test_answering_a_rejected_question_revives_it(): void
    {
        $admin = $this->admin();
        $product = $this->product('مودم TD-LTE');

        $question = $product->questions()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'پرسش ردشده که دوباره پاسخ می‌گیرد.',
            'status' => Question::STATUS_REJECTED,
        ]);

        $this->actingAs($admin)
            ->patch("/admin/questions/{$question->id}/answer", [
                'answer' => 'پس از بازبینی پاسخ می‌دهیم.',
            ]);

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'status' => Question::STATUS_ANSWERED,
        ]);
    }

    // ---------------------------------------------------------------------
    // رد / حذف و دسترسی مشتری‌ها
    // ---------------------------------------------------------------------

    public function test_admin_can_reject_question(): void
    {
        $admin = $this->admin();
        $product = $this->product('سوییچ ۸ پورت');

        $question = $product->questions()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'پرسشی که باید رد شود.',
            'status' => Question::STATUS_ANSWERED,
            'answer' => 'پاسخی که پنهان می‌شود.',
            'answered_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch("/admin/questions/{$question->id}/reject")
            ->assertSessionHas('success');

        $this->get("/products/{$product->slug}")
            ->assertOk()
            ->assertDontSee('پاسخی که پنهان می‌شود.');
    }

    public function test_admin_can_delete_question(): void
    {
        $admin = $this->admin();
        $product = $this->product('سوییچ ۸ پورت');

        $question = $product->questions()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'پرسش برای حذف.',
            'status' => Question::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->delete("/admin/questions/{$question->id}")
            ->assertSessionHas('success');

        $this->assertDatabaseCount('questions', 0);
    }

    public function test_customers_cannot_moderate_questions(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $product = $this->product('مودم TD-LTE');

        $question = $product->questions()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'پرسش محافظت‌شده.',
            'status' => Question::STATUS_PENDING,
        ]);

        $this->actingAs($customer)
            ->patch("/admin/questions/{$question->id}/answer", ['answer' => 'پاسخ غیرمجاز.'])
            ->assertForbidden();

        $this->actingAs($customer)
            ->patch("/admin/questions/{$question->id}/reject")
            ->assertForbidden();

        $this->actingAs($customer)
            ->delete("/admin/questions/{$question->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'status' => Question::STATUS_PENDING,
            'answer' => null,
        ]);
    }

    public function test_admin_sidebar_shows_pending_questions_badge(): void
    {
        $product = $this->product('مودم TD-LTE');

        $product->questions()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'یک پرسش در انتظار پاسخ.',
            'status' => Question::STATUS_PENDING,
        ]);

        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertSee('پرسش‌ها');
    }

    // ---------------------------------------------------------------------
    // یکپارچگی داده و یار Helpers مدل
    // ---------------------------------------------------------------------

    public function test_deleting_product_cascades_to_questions(): void
    {
        $product = $this->product('محصول موقتی');

        $product->questions()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'پرسش همراه محصول حذف می‌شود.',
            'status' => Question::STATUS_ANSWERED,
            'answer' => 'پاسخ هم حذف می‌شود.',
            'answered_at' => now(),
        ]);

        $product->delete();

        $this->assertDatabaseCount('questions', 0);
    }

    public function test_deleting_user_cascades_to_questions(): void
    {
        $product = $this->product('مودم TD-LTE');
        $asker = User::factory()->create();

        $product->questions()->create([
            'user_id' => $asker->id,
            'body' => 'پرسش همراه کاربر حذف می‌شود.',
            'status' => Question::STATUS_PENDING,
        ]);

        $asker->delete();

        $this->assertDatabaseCount('questions', 0);
    }

    public function test_excerpt_truncates_long_questions(): void
    {
        $question = new Question([
            'body' => str_repeat('پرسش طولانی برای آزمون برش متن در جدول مدیریت. ', 10),
        ]);

        $this->assertSame(80, mb_strlen(rtrim($question->excerpt(80), '…')));
        $this->assertStringEndsWith('…', $question->excerpt(80));
    }

    // ---------------------------------------------------------------------
    // هلپرها
    // ---------------------------------------------------------------------

    private function product(string $title, bool $isActive = true): Product
    {
        static $counter = 0;

        return Product::factory()->create([
            'title' => $title,
            'slug' => 'question-'.++$counter,
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

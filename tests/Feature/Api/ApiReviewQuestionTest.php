<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\Question;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * ثبت نظر و پرسش از API (v2.12.0):
 * همان قواعد وب — احراز هویت لازم، یک نظر برای هر کاربر/محصول،
 * نظر و پرسش هرگز بی‌بازبینی عمومی نمی‌شوند
 */
class ApiReviewQuestionTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $title): Product
    {
        return Product::factory()->create(['title' => $title]);
    }

    // ---------------------------------------------------------------------
    // ثبت نظر
    // ---------------------------------------------------------------------

    public function test_review_requires_token(): void
    {
        $product = $this->product('مودم TD-LTE');

        $this->postJson(route('api.v1.products.reviews.store', $product), [
            'rating' => 5,
            'comment' => 'عالی بود.',
        ])->assertUnauthorized();
    }

    public function test_user_can_submit_review_which_starts_pending(): void
    {
        $user = User::factory()->create();
        $product = $this->product('مودم TD-LTE');

        Sanctum::actingAs($user);

        $this->postJson(route('api.v1.products.reviews.store', $product), [
            'rating' => 5,
            'comment' => 'کیفیت ساخت فوق‌العاده بود.',
        ])->assertCreated()->assertJsonPath('message', 'نظر شما ثبت شد و پس از تأیید، برای خریداران نمایش داده می‌شود.');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'status' => Review::STATUS_PENDING,
        ]);
    }

    public function test_second_review_for_same_product_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = $this->product('سوئیچ ۸ پورت');

        $product->reviews()->create([
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => 'نظر قبلی من.',
            'status' => Review::STATUS_APPROVED,
        ]);

        Sanctum::actingAs($user);

        $this->postJson(route('api.v1.products.reviews.store', $product), [
            'rating' => 2,
            'comment' => 'نظر دوباره.',
        ])->assertUnprocessable()->assertJsonValidationErrors(['rating']);

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_review_validates_rating_and_comment(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = $this->product('تستر شبکه');

        $this->postJson(route('api.v1.products.reviews.store', $product), [
            'rating' => 9,
            'comment' => 'خوب',
        ])->assertUnprocessable()->assertJsonValidationErrors(['rating', 'comment']);
    }

    // ---------------------------------------------------------------------
    // ثبت پرسش
    // ---------------------------------------------------------------------

    public function test_question_requires_token(): void
    {
        $product = $this->product('مودم TD-LTE');

        $this->postJson(route('api.v1.products.questions.store', $product), [
            'body' => 'گارانتی دارد؟',
        ])->assertUnauthorized();
    }

    public function test_user_can_ask_question_which_starts_pending(): void
    {
        $user = User::factory()->create();
        $product = $this->product('مودم TD-LTE');

        Sanctum::actingAs($user);

        $this->postJson(route('api.v1.products.questions.store', $product), [
            'body' => 'این مودم آنتن خارجی می‌گیرد؟',
        ])->assertCreated();

        $this->assertDatabaseHas('questions', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => Question::STATUS_PENDING,
            'answer' => null,
        ]);
    }

    public function test_multiple_questions_per_user_are_allowed(): void
    {
        $user = User::factory()->create();
        $product = $this->product('کابل CAT6');

        Sanctum::actingAs($user);

        $this->postJson(route('api.v1.products.questions.store', $product), ['body' => 'پرسش اول دربارهٔ گارانتی؟'])
            ->assertCreated();
        $this->postJson(route('api.v1.products.questions.store', $product), ['body' => 'پرسش دوم دربارهٔ ارسال؟'])
            ->assertCreated();

        $this->assertDatabaseCount('questions', 2);
    }

    public function test_question_validates_body(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = $this->product('تستر شبکه');

        $this->postJson(route('api.v1.products.questions.store', $product), [
            'body' => 'کم',
        ])->assertUnprocessable()->assertJsonValidationErrors(['body']);
    }
}

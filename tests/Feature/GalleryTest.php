<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function productWithCategory(): Product
    {
        $category = Category::factory()->create();

        return Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_upload_gallery_images_on_create(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.products.store'), [
                'title' => 'مودم گالری‌دار',
                'category_id' => $category->id,
                'price' => 1000000,
                'stock' => 5,
                'is_active' => '1',
                'description' => 'تست گالری',
                'image' => UploadedFile::fake()->image('cover.jpg'),
                'images' => [
                    UploadedFile::fake()->image('g1.jpg'),
                    UploadedFile::fake()->image('g2.jpg'),
                ],
            ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('title', 'مودم گالری‌دار')->first();
        $this->assertNotNull($product);
        $this->assertCount(2, $product->images);

        foreach ($product->images as $image) {
            Storage::disk('public')->assertExists($image->path);
            $this->assertSame(0, strpos($image->path, 'products/'));
        }
    }

    public function test_admin_can_upload_gallery_images_on_update(): void
    {
        Storage::fake('public');
        $product = $this->productWithCategory();

        $this->actingAs($this->admin())
            ->put(route('admin.products.update', $product), [
                'title' => $product->title,
                'category_id' => $product->category_id,
                'price' => $product->price,
                'stock' => $product->stock,
                'is_active' => '1',
                'images' => [UploadedFile::fake()->image('extra.jpg')],
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertCount(1, $product->fresh()->images);
        Storage::disk('public')->assertExists($product->fresh()->images->first()->path);
    }

    public function test_admin_can_remove_gallery_images(): void
    {
        Storage::fake('public');

        $product = $this->productWithCategory();

        $first = $product->images()->create([
            'path' => 'products/first.jpg',
            'alt' => $product->title,
            'position' => 1,
        ]);
        $second = $product->images()->create([
            'path' => 'products/second.jpg',
            'alt' => $product->title,
            'position' => 2,
        ]);

        Storage::disk('public')->put('products/first.jpg', 'fake');
        Storage::disk('public')->put('products/second.jpg', 'fake');

        $this->actingAs($this->admin())
            ->put(route('admin.products.update', $product), [
                'title' => $product->title,
                'category_id' => $product->category_id,
                'price' => $product->price,
                'stock' => $product->stock,
                'is_active' => '1',
                'remove_images' => [$first->id],
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseMissing('product_images', ['id' => $first->id]);
        $this->assertDatabaseHas('product_images', ['id' => $second->id]);

        Storage::disk('public')->assertMissing('products/first.jpg');
        Storage::disk('public')->assertExists('products/second.jpg');
    }

    public function test_remove_images_cannot_touch_other_products(): void
    {
        Storage::fake('public');

        $mine = $this->productWithCategory();
        $other = $this->productWithCategory();

        $otherImage = $other->images()->create([
            'path' => 'products/other.jpg',
            'alt' => $other->title,
            'position' => 1,
        ]);
        Storage::disk('public')->put('products/other.jpg', 'fake');

        $this->actingAs($this->admin())
            ->put(route('admin.products.update', $mine), [
                'title' => $mine->title,
                'category_id' => $mine->category_id,
                'price' => $mine->price,
                'stock' => $mine->stock,
                'is_active' => '1',
                // تلاش برای حذف تصویر محصول دیگر با ID آن
                'remove_images' => [$otherImage->id],
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('product_images', ['id' => $otherImage->id]);
        Storage::disk('public')->assertExists('products/other.jpg');
        $this->assertCount(0, $mine->fresh()->images);
    }

    public function test_gallery_thumbnails_render_on_detail_page(): void
    {
        $product = $this->productWithCategory();

        $product->images()->create(['path' => 'products/a.jpg', 'alt' => $product->title, 'position' => 1]);
        $product->images()->create(['path' => 'products/b.jpg', 'alt' => $product->title, 'position' => 2]);

        $response = $this->get(route('shop.products.show', $product));

        $response->assertOk();
        $response->assertSee('data-gallery-thumbs');
        $response->assertSee('data-gallery-main');

        // یک دکمه برای تصویر اصلی + دو دکمه برای گالری — فقط داخل نوار بندانگشتی
        $content = $response->getContent();
        $this->assertSame(1, preg_match('/data-gallery-thumbs[\s\S]*?<\/div>/', $content, $thumbs));
        $this->assertSame(
            3,
            substr_count($thumbs[0], '<button type="button"'),
            'پیش از این ۳ تصویر (اصلی + ۲ گالری) باید رندر شود'
        );
    }

    public function test_detail_page_without_gallery_renders_no_thumbstrip(): void
    {
        $product = $this->productWithCategory();

        $this->get(route('shop.products.show', $product))
            ->assertOk()
            ->assertDontSee('data-gallery-thumbs');
    }

    public function test_gallery_upload_above_limit_is_rejected(): void
    {
        Storage::fake('public');
        $product = $this->productWithCategory();

        $response = $this->actingAs($this->admin())
            ->put(route('admin.products.update', $product), [
                'title' => $product->title,
                'category_id' => $product->category_id,
                'price' => $product->price,
                'stock' => $product->stock,
                'is_active' => '1',
                'images' => [
                    UploadedFile::fake()->image('1.jpg'),
                    UploadedFile::fake()->image('2.jpg'),
                    UploadedFile::fake()->image('3.jpg'),
                    UploadedFile::fake()->image('4.jpg'),
                    UploadedFile::fake()->image('5.jpg'),
                ],
            ]);

        $response->assertSessionHasErrors('images');
        $this->assertCount(0, $product->fresh()->images);
    }

    public function test_guest_cannot_upload_gallery(): void
    {
        $product = $this->productWithCategory();

        $this->put(route('admin.products.update', $product), [
            'title' => $product->title,
            'category_id' => $product->category_id,
            'price' => $product->price,
            'stock' => $product->stock,
            'images' => [UploadedFile::fake()->image('x.jpg')],
        ])
            ->assertRedirect(route('admin.login'));
    }
}

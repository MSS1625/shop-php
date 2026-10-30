<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'price',
        'stock',
        'is_active',
        'image',
    ];

    protected $casts = [
        'price' => 'integer',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * استفاده از اسلاگ در URL به‌جای id
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * تولید خودکار اسلاگ یکتا از روی عنوان محصول
     */
    public static function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: Str::random(8);
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * گالری تصاویر اضافی محصول — مرتب‌شده بر اساس موقعیت
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    /**
     * همهٔ نظرات این محصول (هر وضعیت)
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * فقط نظرات تأییدشده — همان چیزی که مشتری می‌بیند
     */
    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('status', Review::STATUS_APPROVED);
    }

    /**
     * همهٔ پرسش‌های این محصول (هر وضعیت)
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * فقط پرسش‌های پاسخ‌داده‌شده — همان چیزی که مشتری می‌بیند
     */
    public function answeredQuestions(): HasMany
    {
        return $this->questions()->answered();
    }

    /**
     * فقط محصولات فعال و نمایش‌داده‌شونده
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * جستجو در عنوان و توضیحات
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(
            fn (Builder $w) => $w
                ->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
        ));
    }

    /**
     * مرتب‌سازی بر اساس انتخاب کاربر — پرفروش‌ترین بر اساس تعداد
     * اقلام فروخته‌شده (سفارش‌های لغوشده حساب نمی‌شوند، همان قاعدهٔ داشبورد)
     */
    public function scopeOrdered(Builder $query, string $sort = 'newest'): Builder
    {
        return match ($sort) {
            'oldest' => $query->oldest(),
            'cheapest' => $query->orderBy('price'),
            'expensive' => $query->orderByDesc('price'),
            'popular' => $query
                ->withSum([
                    'orderItems as sold_units' => fn (Builder $q) => $q
                        ->whereRelation('order', 'status', '!=', Order::STATUS_CANCELLED),
                ], 'quantity')
                ->orderByDesc('sold_units')
                ->latest(),
            default => $query->latest(),
        };
    }

    /**
     * میانگین امتیاز و تعداد نظرات تأییدشده —
     * دو subquery کنار لیست/کارت‌ها (همان الگوی «پرفروش‌ترین»)
     */
    public function scopeWithRating(Builder $query): Builder
    {
        return $query
            ->withAvg(['reviews as avg_rating' => fn (Builder $q) => $q
                ->where('status', Review::STATUS_APPROVED)], 'rating')
            ->withCount(['reviews as reviews_count' => fn (Builder $q) => $q
                ->where('status', Review::STATUS_APPROVED)]);
    }

    /**
     * فقط محصولات موجود
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * محدودهٔ قیمت — هر کدام از دو حد می‌تواند به‌تنهایی بیاید
     */
    public function scopePriceBetween(Builder $query, ?int $min, ?int $max): Builder
    {
        return $query
            ->when($min !== null, fn (Builder $q) => $q->where('price', '>=', $min))
            ->when($max !== null, fn (Builder $q) => $q->where('price', '<=', $max));
    }

    public function inStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * آیا این محصول نظرِ تأییدشده‌ای دارد؟
     * (فقط وقتی معنا دارد که withRating/aggregate بارگذاری شده باشد)
     */
    public function hasRating(): bool
    {
        return $this->reviews_count !== null && $this->reviews_count > 0;
    }

    /**
     * میانگین امتیاز با یک رقم اعشار فارسی — ۴٫۵
     */
    public function avgRatingFa(): string
    {
        return str_replace('.', '٫', fa_num(number_format((float) $this->avg_rating, 1)));
    }

    /**
     * تعداد نظرات تأییدشده به تفکیک ستاره — [5 => 3, 4 => 1, …]
     * برای نمودار تفکیک امتیازها در صفحهٔ محصول (یک کوئری گروهی)
     *
     * @return array<int, int>
     */
    public function ratingCounts(): array
    {
        return $this->approvedReviews()
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    /**
     * شناسهٔ کاربران (از میان فهرست داده‌شده) که این محصول را در سفارشِ
     * غیرلغوشده خریده‌اند — برای نشان «خریدار تأییدشده» کنار نظرها
     * (یک کوئری برای کل صفحه، بدون N+1)
     *
     * @param  iterable<int, int|null>  $userIds
     * @return Collection<int, int>
     */
    public function verifiedBuyerIds(iterable $userIds): Collection
    {
        $ids = collect($userIds)->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return Order::query()
            ->whereNot('status', Order::STATUS_CANCELLED)
            ->whereHas('items', fn (Builder $q) => $q->where('product_id', $this->getKey()))
            ->whereIn('user_id', $ids)
            ->distinct()
            ->pluck('user_id');
    }

    /**
     * مسیر کامل تصویر محصول (با تصویر جایگزین در صورت نبود)
     */
    public function imageUrl(): string
    {
        if ($this->image && $this->imageExists()) {
            return asset('storage/products/'.$this->image);
        }

        return asset('assets/img/no-image.svg');
    }

    public function imageExists(): bool
    {
        return $this->image !== null
            && \Storage::disk('public')->exists('products/'.$this->image);
    }
}

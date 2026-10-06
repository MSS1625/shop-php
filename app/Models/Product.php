<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * مرتب‌سازی بر اساس انتخاب کاربر
     */
    public function scopeOrdered(Builder $query, string $sort = 'newest'): Builder
    {
        return match ($sort) {
            'oldest' => $query->oldest(),
            'cheapest' => $query->orderBy('price'),
            'expensive' => $query->orderByDesc('price'),
            default => $query->latest(),
        };
    }

    public function inStock(): bool
    {
        return $this->stock > 0;
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

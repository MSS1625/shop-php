<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'icon'];

    /**
     * محصولات این دسته
     *
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * فقط دسته‌هایی که محصول فعال دارند
     */
    public function scopeHasActiveProducts(Builder $query): Builder
    {
        return $query->whereHas('products', fn (Builder $q) => $q->where('is_active', true));
    }

    /**
     * تعداد محصولات فعال هر دسته (برای نمایش در فروشگاه)
     */
    public function activeProductsCount(): int
    {
        return $this->products()->where('is_active', true)->count();
    }
}

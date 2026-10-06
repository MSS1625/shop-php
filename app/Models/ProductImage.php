<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = ['path', 'alt', 'position'];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * تصویر متعلق به کدام محصول است
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * مسیر کامل تصویر برای نمایش
     */
    public function url(): string
    {
        return asset('storage/'.$this->path);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'sale_price',
        'cost',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sale_price' => 'decimal:2',
            'cost' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<ProductImage>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position')->orderBy('id');
    }

    /**
     * @return array<int, string>
     */
    public function getImageUrlsAttribute(): array
    {
        return $this->images
            ->map(fn (ProductImage $image) => Storage::disk('public')->url($image->path))
            ->all();
    }

    /**
     * @return array<int, array{id:int, url:string}>
     */
    public function getImageItemsAttribute(): array
    {
        return $this->images
            ->map(fn (ProductImage $image) => [
                'id' => $image->id,
                'url' => Storage::disk('public')->url($image->path),
            ])
            ->all();
    }

    /**
     * @var list<string>
     */
    protected $appends = [
        'image_urls',
        'image_items',
    ];
}

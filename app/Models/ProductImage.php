<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    /** @use HasFactory<\Database\Factories\ProductImageFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'path',
        'position',
    ];

    /**
     * @return BelongsTo<Product, ProductImage>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

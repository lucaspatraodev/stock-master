<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\Product $resource
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'sale_price' => $this->resource->sale_price,
            'cost' => $this->resource->cost,
            'is_active' => $this->resource->is_active,
            'created_by' => $this->resource->created_by,
            'updated_by' => $this->resource->updated_by,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'image_urls' => $this->resource->image_urls,
            'image_items' => $this->resource->image_items,
        ];
    }
}

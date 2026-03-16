<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductLog;
use Illuminate\Support\Facades\Auth;

class ProductObserver
{
    public function created(Product $product): void
    {
        ProductLog::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'action' => 'created',
            'changes' => [
                'new' => $this->loggableFields($product),
            ],
        ]);
    }

    public function updated(Product $product): void
    {
        $changes = $product->getChanges();
        unset($changes['updated_at']);

        if ($changes === []) {
            return;
        }

        $original = $product->getOriginal();
        $old = array_intersect_key($original, $changes);
        $new = array_intersect_key($product->toArray(), $changes);

        ProductLog::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'action' => 'updated',
            'changes' => [
                'old' => $old,
                'new' => $new,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function loggableFields(Product $product): array
    {
        return $product->only([
            'id',
            'title',
            'description',
            'sale_price',
            'cost',
            'is_active',
            'created_by',
            'updated_by',
        ]);
    }
}

<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductLog;
use Illuminate\Support\Facades\Auth;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
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

    /**
     * Handle the Product "updated" event.
     */
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
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
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

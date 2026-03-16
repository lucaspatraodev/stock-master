<?php

namespace App\Services;

class ProductPricing
{
    public static function isSalePriceValid(float $cost, float $salePrice): bool
    {
        $minimum = $cost * 1.1;

        return $salePrice + 1e-9 >= $minimum;
    }
}

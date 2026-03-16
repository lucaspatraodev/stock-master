<?php

namespace Tests\Unit;

use App\Services\ProductPricing;
use PHPUnit\Framework\TestCase;

class ProductPricingTest extends TestCase
{
    public function test_sale_price_below_minimum_margin_returns_false(): void
    {
        $this->assertFalse(ProductPricing::isSalePriceValid(100.0, 109.0));
    }

    public function test_sale_price_at_minimum_margin_returns_true(): void
    {
        $this->assertTrue(ProductPricing::isSalePriceValid(100.0, 110.0));
    }
}

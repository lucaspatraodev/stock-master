<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->optional()->sentence(12),
            'sale_price' => $this->faker->randomFloat(2, 0, 9999),
            'cost' => $this->faker->randomFloat(2, 0, 8000),
            'is_active' => true,
        ];
    }
}

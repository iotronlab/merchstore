<?php

namespace Database\Factories\Product;

use App\Models\Product\Product;
use App\Models\Product\ProductFlat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->word,
            'type' => Product::SIMPLE,
            'name' => $productName = fake()->unique()->word,
            'url_key' => $productName . '_' . fake()->unique()->word,
            'status' => Product::PUBLISHED,
            'base_price' => $basePrice = fake()->numberBetween(1000, 30000),
            'price' => $basePrice,
            'attribute_group_id' => fake()->randomElement([5, 6]),
            'quantity' => 0,
        ];
    }


    public function configure()
    {
        // Add Product Flat
        $this->afterMaking(function (Product $product){
            $product->flat()->create(ProductFlat::factory(1)->make());

        });
        return parent::configure(); // TODO: Change the autogenerated stub
    }


}

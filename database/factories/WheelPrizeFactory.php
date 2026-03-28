<?php

namespace Database\Factories;

use App\Models\WheelPrize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WheelPrize>
 */
class WheelPrizeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->sentence(4),
            'label_ngan' => fake()->lexify('????'),
            'color' => '#e91e63',
            'weight' => fake()->numberBetween(1, 10),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\WheelChoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WheelChoice>
 */
class WheelChoiceFactory extends Factory
{
    protected $model = WheelChoice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Hôn nhân', 'Tình yêu', 'Gia đình', 'Sự nghiệp']),
            'sort_order' => fake()->numberBetween(0, 100),
            'color' => '#e91e63',
        ];
    }
}

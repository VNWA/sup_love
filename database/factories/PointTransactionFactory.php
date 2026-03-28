<?php

namespace Database\Factories;

use App\Enums\PointTransactionType;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PointTransaction>
 */
class PointTransactionFactory extends Factory
{
    protected $model = PointTransaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'actor_id' => null,
            'type' => PointTransactionType::WheelSpin,
            'amount' => -1,
            'balance_after' => fake()->numberBetween(0, 100),
            'admin_note' => null,
            'meta' => [],
        ];
    }
}

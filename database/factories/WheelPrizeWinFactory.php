<?php

namespace Database\Factories;

use App\Enums\WheelPrizeWinStatus;
use App\Models\PointTransaction;
use App\Models\User;
use App\Models\WheelPrizeWin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WheelPrizeWin>
 */
class WheelPrizeWinFactory extends Factory
{
    protected $model = WheelPrizeWin::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'point_transaction_id' => PointTransaction::factory(),
            'wheel_prize_id' => null,
            'prize_label' => fake()->sentence(3),
            'prize_label_ngan' => fake()->lexify('????'),
            'color' => '#e91e63',
            'status' => WheelPrizeWinStatus::Pending,
            'received_at' => null,
            'handled_by' => null,
            'admin_note' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (WheelPrizeWin $win): void {
            PointTransaction::query()
                ->whereKey($win->point_transaction_id)
                ->update(['user_id' => $win->user_id]);
        });
    }
}

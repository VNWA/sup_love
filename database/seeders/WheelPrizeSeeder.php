<?php

namespace Database\Seeders;

use App\Models\WheelPrize;
use Illuminate\Database\Seeder;

class WheelPrizeSeeder extends Seeder
{
    /**
     * Nạp giải mặc định từ config (chỉ khi bảng trống).
     */
    public function run(): void
    {
        if (WheelPrize::query()->exists()) {
            return;
        }

        $rows = config('wheel.prizes', []);

        foreach ($rows as $index => $row) {
            WheelPrize::query()->create([
                'label' => (string) ($row['label'] ?? ''),
                'label_ngan' => (string) ($row['label_ngan'] ?? ''),
                'color' => (string) ($row['color'] ?? '#e91e63'),
                'weight' => max(0, (int) ($row['weight'] ?? 1)),
                'sort_order' => $index * 10,
                'is_active' => true,
            ]);
        }
    }
}

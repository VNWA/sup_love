<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WheelChoiceSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('wheel_choices')->exists()) {
            return;
        }

        $now = now();

        /** id 0 = ô an ủi hệ thống (không xóa; chỉ sửa). Luôn sort_order nhỏ nhất để nằm đầu vòng. */
        $rows = [
            [0, 'Chúc bạn may mắn lần sau', 0, '#90a4ae', true],
            [1, 'Lì Xì 200k', 10, '#d81b60', false],
            [2, '20.000.000 VND', 20, '#ffd54f', false],
            [3, '5.000.000 VND', 30, '#ffb300', false],
            [4, 'Du lịch thượng hải', 40, '#8e24aa', false],
            [5, 'Du lịch Singapore', 50, '#5e35b1', false],
            [6, 'Lì Xì 50k', 60, '#ec407a', false],
            [7, 'Du lịch đảo phú quốc', 70, '#00897b', false],
            [8, 'Lì Xì 100k', 80, '#c2185b', false],
        ];

        foreach ($rows as $row) {
            DB::table('wheel_choices')->insert([
                'id' => $row[0],
                'name' => $row[1],
                'sort_order' => $row[2],
                'color' => $row[3],
                'is_system' => $row[4],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}

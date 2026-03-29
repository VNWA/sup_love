<?php

namespace Database\Seeders;

use App\Models\WheelChoice;
use Illuminate\Database\Seeder;

class WheelChoiceSeeder extends Seeder
{
    public function run(): void
    {
        if (WheelChoice::query()->exists()) {
            return;
        }

        $names = config('wheel.default_choice_names', []);

        foreach ($names as $i => $name) {
            WheelChoice::query()->create([
                'name' => $name,
                'sort_order' => $i * 10,
            ]);
        }
    }
}

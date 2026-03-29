<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wheel_choices', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('color', 16)->default('#e91e63');
            $table->timestamps();
        });

        $now = now();
        /** Ô an ủi: id 8 (SQLite/MySQL không dùng PK = 0 ổn định). */
        $prizes = [
            [1, 'Lì Xì 200k', 10, '#d81b60'],
            [2, '20.000.000 VND', 20, '#ffd54f'],
            [3, '5.000.000 VND', 30, '#ffb300'],
            [4, 'Du lịch thượng hải', 40, '#8e24aa'],
            [5, 'Du lịch Singapore', 50, '#5e35b1'],
            [6, 'Lì Xì 50k', 60, '#ec407a'],
            [7, 'Du lịch đảo phú quốc', 70, '#00897b'],
            [8, 'Chúc bạn may mắn lần sau', 80, '#90a4ae'],
        ];

        foreach ($prizes as $row) {
            DB::table('wheel_choices')->insert([
                'id' => $row[0],
                'name' => $row[1],
                'sort_order' => $row[2],
                'color' => $row[3],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE wheel_choices AUTO_INCREMENT = 9');
        }

        Schema::create('wheel_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->foreignId('next_result_choice_id')
                ->nullable()
                ->constrained('wheel_choices')
                ->nullOnDelete();
            $table->timestamps();
        });

        DB::table('wheel_rooms')->insertGetId([
            'name' => 'Phòng chính',
            'slug' => 'default',
            'is_active' => true,
            'next_result_choice_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('wheel_spins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wheel_room_id')->constrained('wheel_rooms')->restrictOnDelete();
            $table->foreignId('bet_choice_id')->constrained('wheel_choices')->restrictOnDelete();
            $table->unsignedInteger('bet_amount');
            $table->foreignId('result_choice_id')->constrained('wheel_choices')->restrictOnDelete();
            $table->unsignedInteger('payout')->default(0);
            $table->boolean('was_rigged')->default(false);
            $table->foreignId('point_transaction_id')->unique()->constrained('point_transactions')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['wheel_room_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wheel_spins');
        Schema::dropIfExists('wheel_rooms');
        Schema::dropIfExists('wheel_choices');
    }
};

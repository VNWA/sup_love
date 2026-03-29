<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wheel_rounds', function (Blueprint $table) {
            $table->string('name')->default('');
            $table->unsignedInteger('min_bet_points')->default(0);
            $table->unsignedInteger('max_bet_points')->nullable();
        });

        $rounds = DB::table('wheel_rounds')->orderBy('id')->get();

        foreach ($rounds as $r) {
            $room = DB::table('wheel_rooms')->where('id', $r->wheel_room_id)->first();
            $roomName = $room->name ?? 'Phòng';
            $name = sprintf('Vòng %d · %s', $r->round_number, $roomName);

            DB::table('wheel_rounds')->where('id', $r->id)->update([
                'name' => $name,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('wheel_rounds', function (Blueprint $table) {
            $table->dropColumn(['name', 'min_bet_points', 'max_bet_points']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wheel_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wheel_room_id')->constrained('wheel_rooms')->cascadeOnDelete();
            $table->unsignedInteger('round_number');
            $table->string('status', 16);
            $table->foreignId('result_choice_id')->constrained('wheel_choices')->restrictOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['wheel_room_id', 'status']);
            $table->unique(['wheel_room_id', 'round_number']);
        });

        Schema::table('wheel_spins', function (Blueprint $table) {
            $table->foreignId('wheel_round_id')
                ->nullable()
                ->after('wheel_room_id')
                ->constrained('wheel_rounds')
                ->restrictOnDelete();
        });

        Schema::table('wheel_spins', function (Blueprint $table) {
            $table->unique(['wheel_round_id', 'user_id']);
        });

        Schema::table('wheel_rooms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('next_result_choice_id');
        });
    }

    public function down(): void
    {
        Schema::table('wheel_rooms', function (Blueprint $table) {
            $table->foreignId('next_result_choice_id')
                ->nullable()
                ->after('is_active')
                ->constrained('wheel_choices')
                ->nullOnDelete();
        });

        Schema::table('wheel_spins', function (Blueprint $table) {
            $table->dropUnique(['wheel_round_id', 'user_id']);
            $table->dropConstrainedForeignId('wheel_round_id');
        });

        Schema::dropIfExists('wheel_rounds');
    }
};

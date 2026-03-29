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
            $table->unsignedBigInteger('id')->primary();
            $table->string('name', 120);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('color', 16)->default('#e91e63');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('wheel_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('wheel_rooms')->insert([
            'name' => 'Phòng chính',
            'slug' => 'default',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('wheel_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wheel_room_id')->constrained('wheel_rooms')->cascadeOnDelete();
            $table->unsignedInteger('round_number');
            $table->string('name', 120)->default('');
            $table->string('status', 16);
            $table->foreignId('result_choice_id')->constrained('wheel_choices')->restrictOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['wheel_room_id', 'status']);
            $table->unique(['wheel_room_id', 'round_number']);
        });

        Schema::create('wheel_spins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wheel_room_id')->constrained('wheel_rooms')->restrictOnDelete();
            $table->foreignId('wheel_round_id')->constrained('wheel_rounds')->restrictOnDelete();
            $table->foreignId('bet_choice_id')->constrained('wheel_choices')->restrictOnDelete();
            $table->unsignedInteger('bet_amount');
            $table->string('wish_category', 64)->nullable();
            $table->foreignId('result_choice_id')->constrained('wheel_choices')->restrictOnDelete();
            $table->unsignedInteger('payout')->default(0);
            $table->boolean('was_rigged')->default(false);
            $table->foreignId('point_transaction_id')->unique()->constrained('point_transactions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['wheel_round_id', 'user_id']);
            $table->index(['user_id', 'created_at']);
            $table->index(['wheel_room_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wheel_spins');
        Schema::dropIfExists('wheel_rounds');
        Schema::dropIfExists('wheel_rooms');
        Schema::dropIfExists('wheel_choices');
    }
};

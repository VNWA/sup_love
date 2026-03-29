<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wheel_rounds', function (Blueprint $table) {
            $table->dropColumn(['name', 'min_bet_points', 'max_bet_points']);
        });
    }

    public function down(): void
    {
        Schema::table('wheel_rounds', function (Blueprint $table) {
            $table->string('name')->default('');
            $table->unsignedInteger('min_bet_points')->default(0);
            $table->unsignedInteger('max_bet_points')->nullable();
        });
    }
};

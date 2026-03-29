<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wheel_spins', function (Blueprint $table) {
            $table->string('wish_category', 64)->nullable()->after('bet_amount');
        });
    }

    public function down(): void
    {
        Schema::table('wheel_spins', function (Blueprint $table) {
            $table->dropColumn('wish_category');
        });
    }
};

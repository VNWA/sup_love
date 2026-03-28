<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wheel_prize_wins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('point_transaction_id')->unique()->constrained('point_transactions')->cascadeOnDelete();
            $table->foreignId('wheel_prize_id')->nullable()->constrained('wheel_prizes')->nullOnDelete();
            $table->string('prize_label', 500);
            $table->string('prize_label_ngan', 80);
            $table->string('color', 32);
            $table->string('status', 20)->default('pending');
            $table->timestamp('received_at')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wheel_prize_wins');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bank_name', 120)->nullable()->after('point');
            $table->string('bank_account_number', 64)->nullable()->after('bank_name');
            $table->string('bank_account_holder', 120)->nullable()->after('bank_account_number');
        });

        Schema::create('lixi_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->string('status', 24)->default('pending');
            $table->foreignId('point_transaction_id')->nullable()->constrained('point_transactions')->nullOnDelete();
            $table->foreignId('refund_point_transaction_id')->nullable()->constrained('point_transactions')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lixi_withdrawals');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'bank_account_number',
                'bank_account_holder',
            ]);
        });
    }
};

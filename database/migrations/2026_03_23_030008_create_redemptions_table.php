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
    Schema::create('redemptions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('reward_id')->constrained()->cascadeOnDelete();
        $table->unsignedInteger('points_used');
        $table->string('redemption_code')->unique();
        $table->enum('status', ['pending','claimed','expired'])->default('pending');
        $table->timestamp('claimed_at')->nullable();
        $table->timestamp('expires_at')->nullable();
        $table->timestamps();
        $table->index(['user_id', 'status']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redemptions');
    }
};

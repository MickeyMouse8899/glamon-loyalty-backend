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
    Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
        $table->string('invoice_number')->unique();
        $table->decimal('amount', 12, 2);
        $table->unsignedInteger('points_earned')->default(0);
        $table->enum('source', ['instore','inapp','webstore']);
        $table->enum('status', ['pending','completed','cancelled'])->default('completed');
        $table->string('wc_order_id')->nullable();
        $table->json('meta')->nullable();
        $table->timestamps();
        $table->index(['user_id', 'brand_id']);
        $table->index('wc_order_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

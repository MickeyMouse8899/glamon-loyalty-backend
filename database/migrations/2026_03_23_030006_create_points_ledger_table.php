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
    Schema::create('points_ledger', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
        $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
        $table->integer('points');
        $table->enum('type', ['earn','redeem','expire','adjustment']);
        $table->string('description')->nullable();
        $table->unsignedBigInteger('balance_after');
        $table->timestamps();
        $table->index(['user_id', 'brand_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_ledger');
    }
};

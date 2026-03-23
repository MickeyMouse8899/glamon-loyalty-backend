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
    Schema::create('rewards', function (Blueprint $table) {
        $table->id();
        $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->text('description')->nullable();
        $table->string('image_url')->nullable();
        $table->unsignedInteger('points_required');
        $table->unsignedInteger('stock')->default(0);
        $table->boolean('unlimited_stock')->default(false);
        $table->boolean('is_active')->default(true);
        $table->timestamp('valid_until')->nullable();
        $table->timestamps();
        $table->index('brand_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};

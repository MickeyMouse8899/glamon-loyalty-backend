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
    Schema::create('brand_point_rules', function (Blueprint $table) {
        $table->id();
        $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
        $table->enum('source', ['instore','inapp','webstore'])->default('instore');
        $table->unsignedInteger('rp_per_point')->default(10000);
        $table->decimal('multiplier', 4, 2)->default(1.00);
        $table->unsignedInteger('min_transaction')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamp('valid_from')->nullable();
        $table->timestamp('valid_until')->nullable();
        $table->timestamps();
        $table->index(['brand_id', 'source']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_point_rules');
    }
};

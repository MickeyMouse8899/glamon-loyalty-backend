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
    Schema::create('pending_points', function (Blueprint $table) {
        $table->id();
        $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
        $table->string('wc_order_id');
        $table->decimal('order_total', 12, 2);
        $table->string('customer_email')->nullable();
        $table->string('customer_phone', 20)->nullable();
        $table->unsignedInteger('points_to_credit');
        $table->enum('status', ['pending','credited','expired'])->default('pending');
        $table->timestamp('expires_at')->nullable();
        $table->timestamps();
        $table->index(['customer_email', 'status']);
        $table->index(['customer_phone', 'status']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_points');
    }
};

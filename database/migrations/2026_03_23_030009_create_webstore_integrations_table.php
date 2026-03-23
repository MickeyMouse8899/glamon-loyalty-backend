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
    Schema::create('webstore_integrations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
        $table->enum('platform', ['woocommerce','shopify','custom'])->default('woocommerce');
        $table->string('store_url');
        $table->text('consumer_key');
        $table->text('consumer_secret');
        $table->text('webhook_secret')->nullable();
        $table->unsignedInteger('sync_interval')->default(60);
        $table->timestamp('last_synced_at')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webstore_integrations');
    }
};

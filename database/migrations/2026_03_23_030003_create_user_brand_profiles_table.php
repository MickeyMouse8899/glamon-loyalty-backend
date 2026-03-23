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
    Schema::create('user_brand_profiles', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
        $table->string('member_code')->unique();
        $table->unsignedBigInteger('total_points')->default(0);
        $table->enum('tier', ['bronze','silver','gold','platinum'])->default('bronze');
        $table->timestamp('joined_at')->useCurrent();
        $table->timestamps();
        $table->unique(['user_id', 'brand_id']);
        $table->index(['user_id', 'brand_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_brand_profiles');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tier_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->string('tier');
            $table->unsignedInteger('min_points');
            $table->unsignedInteger('max_points')->nullable();
            $table->string('color', 7)->default('#92400e');
            $table->string('benefits')->nullable();
            $table->timestamps();
            $table->unique(['brand_id', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tier_rules');
    }
};

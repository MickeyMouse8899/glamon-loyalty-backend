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
    Schema::table('users', function (Blueprint $table) {
        $table->string('phone', 20)->nullable()->unique()->after('email');
        $table->string('google_id')->nullable()->unique()->after('phone');
        $table->string('avatar')->nullable()->after('google_id');
        $table->date('birth_date')->nullable()->after('avatar');
        $table->enum('gender', ['male', 'female'])->nullable()->after('birth_date');
        $table->boolean('is_active')->default(true)->after('gender');
        $table->index('phone');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['phone','google_id','avatar','birth_date','gender','is_active']);
    });
}
};

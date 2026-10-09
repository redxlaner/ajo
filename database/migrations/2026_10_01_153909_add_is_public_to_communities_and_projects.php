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
    Schema::table('communities', function (Blueprint $table) {
        $table->boolean('is_public')->default(false);
    });
    Schema::table('projects', function (Blueprint $table) {
        $table->boolean('is_public')->default(false);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }
};

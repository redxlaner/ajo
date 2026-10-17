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
        $table->foreignId('featured_project_id')->nullable()->constrained('projects')->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('communities', function (Blueprint $table) {
        $table->dropConstrainedForeignId('featured_project_id');
    });
}
};

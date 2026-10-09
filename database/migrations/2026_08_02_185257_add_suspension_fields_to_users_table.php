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
        $table->boolean('is_suspended')->default(false)->after('remember_token');
        $table->unsignedInteger('missed_contributions_count')->default(0)->after('is_suspended');
        $table->year('suspension_year')->nullable()->after('missed_contributions_count');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['is_suspended', 'missed_contributions_count', 'suspension_year']);
    });
}
};

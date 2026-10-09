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
    Schema::table('monthly_contributions', function (Blueprint $table) {
        $table->decimal('penalty_amount', 12, 2)->default(0)->after('operations_amount');
        $table->boolean('is_late')->default(false)->after('penalty_amount');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('monthly_contributions', function (Blueprint $table) {
        $table->dropColumn(['penalty_amount', 'is_late']);
    });
}
};

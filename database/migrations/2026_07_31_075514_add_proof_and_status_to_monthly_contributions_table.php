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
        $table->string('proof_of_payment')->nullable()->after('status');
        $table->foreignId('approved_by')->nullable()->constrained('users')->after('proof_of_payment');
        $table->timestamp('approved_at')->nullable()->after('approved_by');
        $table->text('rejection_reason')->nullable()->after('approved_at');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_contributions', function (Blueprint $table) {
            //
        });
    }
};

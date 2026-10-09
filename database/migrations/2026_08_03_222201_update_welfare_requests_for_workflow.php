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
    Schema::table('welfare_requests', function (Blueprint $table) {
        $table->decimal('approved_amount', 12, 2)->nullable()->after('amount_requested');
        $table->foreignId('disbursed_by')->nullable()->constrained('users')->nullOnDelete()->after('reviewed_by');
        $table->timestamp('disbursed_at')->nullable()->after('decision_date');
        $table->string('rejection_reason')->nullable()->after('disbursed_at');
    });

    // Expand status enum if needed
    // pending → approved → disbursed / rejected
    DB::statement("ALTER TABLE welfare_requests MODIFY COLUMN status ENUM('pending', 'approved', 'disbursed', 'rejected') DEFAULT 'pending'");
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('welfare_requests', function (Blueprint $table) {
        $table->dropConstrainedForeignId('disbursed_by');
        $table->dropColumn(['approved_amount', 'disbursed_at', 'rejection_reason']);
    });
}
};

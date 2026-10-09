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
    Schema::create('welfare_requests', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->enum('request_type', ['medical', 'bereavement', 'emergency', 'education', 'others']);
        $table->decimal('amount_requested', 15, 2);
        $table->text('description');
        $table->string('supporting_docs')->nullable();
        $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
        $table->foreignId('reviewed_by')->nullable()->constrained('users');
        $table->text('review_notes')->nullable();
        $table->timestamp('decision_date')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welfare_requests');
    }
};

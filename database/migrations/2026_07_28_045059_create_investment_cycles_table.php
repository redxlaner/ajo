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
    Schema::create('investment_cycles', function (Blueprint $table) {
        $table->id();
        $table->string('name');                    // e.g. "2026 Cycle 1"
        $table->date('start_date');
        $table->date('end_date');
        $table->enum('status', ['upcoming', 'active', 'closed'])->default('upcoming');
        $table->decimal('total_profit', 15, 2)->nullable();
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_cycles');
    }
};

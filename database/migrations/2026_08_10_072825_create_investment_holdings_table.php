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
    Schema::create('investment_holdings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('offering_id')->constrained('investment_offerings')->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->decimal('units', 12, 2)->default(0);
        $table->decimal('average_cost', 12, 2)->default(0);
        $table->timestamps();

        $table->unique(['offering_id', 'user_id']);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::dropIfExists('investment_holdings');
}
};

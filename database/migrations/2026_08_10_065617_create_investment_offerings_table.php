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
    Schema::create('investment_offerings', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('type'); // treasury_bill, bond, real_estate, etc.
        $table->text('description')->nullable();
        $table->decimal('target_capital', 14, 2);
        $table->decimal('target_units', 12, 2);
        $table->decimal('min_units', 12, 2)->default(1);
        $table->decimal('unit_price_at_open', 12, 2);
        $table->date('start_date')->nullable();
        $table->date('maturity_date')->nullable();
        $table->enum('status', ['draft', 'open', 'funded', 'closed'])->default('draft');
        $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::dropIfExists('investment_offerings');
}
};

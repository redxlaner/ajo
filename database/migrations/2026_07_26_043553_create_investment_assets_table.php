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
    Schema::create('investment_assets', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->enum('type', [
            'treasury_bill', 'bond', 'mutual_fund', 'stock', 'etf',
            'real_estate', 'agriculture', 'business', 'fixed_income', 'others'
        ]);
        $table->decimal('purchase_value', 15, 2);
        $table->decimal('current_value', 15, 2)->nullable();
        $table->date('purchase_date');
        $table->date('maturity_date')->nullable();
        $table->string('asset_manager')->nullable();
        $table->enum('status', ['active', 'matured', 'sold'])->default('active');
        $table->text('description')->nullable();
        $table->foreignId('added_by')->constrained('users');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_assets');
    }
};

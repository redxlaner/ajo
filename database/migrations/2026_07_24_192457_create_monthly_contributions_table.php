+<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('monthly_contributions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->decimal('total_amount', 15, 2);
        $table->decimal('welfare_amount', 15, 2);
        $table->decimal('investment_amount', 15, 2);
        $table->decimal('operations_amount', 15, 2);
        $table->date('month');
        $table->enum('status', ['pending', 'allocated'])->default('pending');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_contributions');
    }
};

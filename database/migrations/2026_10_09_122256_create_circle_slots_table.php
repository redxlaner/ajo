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
    Schema::create('circle_slots', function (Blueprint $table) {
        $table->id();
        $table->foreignId('circle_id')->constrained()->cascadeOnDelete();
        $table->unsignedSmallInteger('position');
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        $table->enum('status', ['open', 'reserved', 'active', 'paid', 'defaulted'])->default('open');
        $table->timestamp('reserved_until')->nullable();
        $table->date('payout_due_on')->nullable();
        $table->timestamps();

        $table->unique(['circle_id', 'position']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('circle_slots');
    }
};

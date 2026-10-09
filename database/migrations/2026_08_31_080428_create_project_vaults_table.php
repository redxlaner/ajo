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
    Schema::create('project_vaults', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
        $table->decimal('balance', 14, 2)->default(0);
        $table->enum('status', ['locked', 'unlocked'])->default('locked');
        $table->timestamp('opened_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_vaults');
    }
};

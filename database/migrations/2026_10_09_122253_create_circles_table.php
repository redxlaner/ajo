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
        Schema::create('circles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->unsignedInteger('contribution_amount');
            $table->enum('frequency', ['daily', 'weekly', 'monthly']);
            $table->unsignedSmallInteger('slots_total');
            $table->date('start_date');
            $table->enum('status', ['draft', 'open', 'full', 'running', 'closed'])->default('open');
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->enum('kyc_level', ['none', 'phone', 'id', 'bank'])->default('phone');
            $table->boolean('collateral_required')->default(false);
            $table->enum('payout_order', ['fixed', 'bid'])->default('fixed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('circles');
    }
};

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
        Schema::create('team_poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_poll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_poll_option_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['team_poll_id', 'user_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('team_poll_votes'); }
};

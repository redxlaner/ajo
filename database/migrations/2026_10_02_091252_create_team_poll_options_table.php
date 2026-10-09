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
        Schema::create('team_poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_poll_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('team_poll_options'); }
};

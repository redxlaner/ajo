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
        Schema::table('circle_slots', function (Blueprint $table) {
            $table->timestamp('paid_out_at')->nullable();
            $table->foreignId('paid_out_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('circle_slots', function (Blueprint $table) {
            //
        });
    }
};

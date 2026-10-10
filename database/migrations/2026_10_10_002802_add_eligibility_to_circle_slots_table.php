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
            $table->enum('eligibility', ['known', 'open'])->default('open')->after('status');
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

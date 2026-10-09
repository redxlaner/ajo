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
        Schema::table('vault_actions', function (Blueprint $table) {
            $table->decimal('fee_amount', 14, 2)->nullable()->after('amount');
            $table->decimal('net_amount', 14, 2)->nullable()->after('fee_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vault_actions', function (Blueprint $table) {
            //
        });
    }
};

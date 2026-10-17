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
        Schema::table('project_payout_slots', function (Blueprint $table) {
            if (!Schema::hasColumn('project_payout_slots', 'status')) {
                $table->string('status')->default('open')->after('position'); // open | reserved | active | paid
            }
            if (!Schema::hasColumn('project_payout_slots', 'reserved_until')) {
                $table->timestamp('reserved_until')->nullable()->after('status');
            }
            if (!Schema::hasColumn('project_payout_slots', 'paid_out_at')) {
                $table->timestamp('paid_out_at')->nullable()->after('reserved_until');
            }
            if (!Schema::hasColumn('project_payout_slots', 'eligibility')) {
                $table->string('eligibility')->default('new')->after('paid_out_at'); // known | new
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_payout_slots', function (Blueprint $table) {
            //
        });
    }
};

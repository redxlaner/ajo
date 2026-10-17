<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('visibility')->default('private')->after('status'); // public | private
            $table->string('frequency')->nullable()->after('visibility');     // daily | weekly | monthly
            $table->decimal('contribution_amount', 12, 2)->nullable()->after('frequency');
            $table->unsignedInteger('slots_total')->nullable()->after('contribution_amount');
            $table->date('start_date')->nullable()->after('slots_total');
            $table->string('bank_name')->nullable()->after('start_date');
            $table->string('account_name')->nullable()->after('bank_name');
            $table->string('account_number')->nullable()->after('account_name');
            $table->text('summary')->nullable()->after('account_number');
            $table->unsignedBigInteger('legacy_circle_id')->nullable()->after('summary');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'visibility',
                'frequency',
                'contribution_amount',
                'slots_total',
                'start_date',
                'bank_name',
                'account_name',
                'account_number',
                'summary',
                'legacy_circle_id',
            ]);
        });
    }
};
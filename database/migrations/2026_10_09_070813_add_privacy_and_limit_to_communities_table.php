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
        Schema::table('communities', function (Blueprint $table) {
            $table->string('visibility')->default('public')->after('description'); // public | private
            $table->unsignedInteger('member_limit')->nullable()->after('visibility');
            $table->boolean('invites_open')->default(true)->after('member_limit');
        });
    }

    public function down(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->dropColumn(['visibility', 'member_limit', 'invites_open']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['community_id']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->unsignedBigInteger('community_id')->nullable()->change();
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->foreign('community_id')
                ->references('id')
                ->on('communities')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['community_id']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->unsignedBigInteger('community_id')->nullable(false)->change();
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->foreign('community_id')
                ->references('id')
                ->on('communities')
                ->cascadeOnDelete();
        });
    }
};
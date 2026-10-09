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
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('phone')->nullable()->after('avatar');
            $table->string('occupation')->nullable()->after('phone');
            $table->string('marital_status')->nullable()->after('occupation'); // single, married, divorced, widowed, prefer_not
            $table->date('date_of_birth')->nullable()->after('marital_status');
            $table->string('gender')->nullable()->after('date_of_birth'); // male, female, other, prefer_not
            $table->string('city')->nullable()->after('gender');
            $table->string('state')->nullable()->after('city');
            $table->text('bio')->nullable()->after('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar', 'phone', 'occupation', 'marital_status',
                'date_of_birth', 'gender', 'city', 'state', 'bio',
            ]);
        });
    }
};

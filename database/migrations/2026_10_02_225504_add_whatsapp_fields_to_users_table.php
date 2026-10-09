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
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->string('whatsapp_opt_in')->default('off')->after('phone_verified_at'); // off, on
            $table->string('phone_otp', 6)->nullable()->after('whatsapp_opt_in');
            $table->timestamp('phone_otp_expires_at')->nullable()->after('phone_otp');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_verified_at',
                'whatsapp_opt_in',
                'phone_otp',
                'phone_otp_expires_at',
            ]);
        });
    }
};

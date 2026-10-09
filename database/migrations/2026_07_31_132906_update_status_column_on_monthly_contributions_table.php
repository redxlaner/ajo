<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE monthly_contributions MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'allocated') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE monthly_contributions MODIFY COLUMN status ENUM('pending', 'allocated') DEFAULT 'pending'");
    }
}; 
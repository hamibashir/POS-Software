<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the ENUM to include adjustment_in and adjustment_out
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN type ENUM('sale','purchase','adjustment','adjustment_in','adjustment_out','return') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN type ENUM('sale','purchase','adjustment','return') NOT NULL");
    }
};

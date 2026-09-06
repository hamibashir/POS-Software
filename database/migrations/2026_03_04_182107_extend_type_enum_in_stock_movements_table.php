<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE stock_movements MODIFY COLUMN type ENUM('sale','purchase','adjustment','adjustment_in','adjustment_out','return') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE stock_movements MODIFY COLUMN type ENUM('sale','purchase','adjustment','return') NOT NULL");
        }
    }
};

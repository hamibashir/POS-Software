<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetProductStockCommand extends Command
{
    protected $signature   = 'products:reset-stock {--force : Force reset without confirmation}';
    protected $description = 'Set stock quantity of all products to 0 and clear stock movements';

    public function handle(): int
    {
        if (!$this->option('force') && !$this->confirm('Are you sure you want to set stock quantity to 0 for ALL products?')) {
            $this->warn('Operation cancelled.');
            return 1;
        }

        $this->info('Resetting product stock quantities...');

        $updatedCount = 0;
        $clearedMovements = 0;

        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('products')) {
            $updatedCount = DB::table('products')->count();
            DB::table('products')->update(['stock_quantity' => 0]);
        }

        if (Schema::hasTable('stock_movements')) {
            $clearedMovements = DB::table('stock_movements')->count();
            DB::table('stock_movements')->delete();
            try { DB::statement('ALTER TABLE stock_movements AUTO_INCREMENT = 1;'); } catch (\Throwable $e) {}
        }

        Schema::enableForeignKeyConstraints();

        $this->info("✓ Set stock_quantity = 0 for {$updatedCount} product(s).");
        $this->info("✓ Cleared {$clearedMovements} stock movement log(s).");
        $this->info("All product inventory is now 0!");

        return 0;
    }
}

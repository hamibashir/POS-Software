<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetLowStockThresholdCommand extends Command
{
    protected $signature   = 'products:set-threshold {threshold=1 : The low stock alert threshold value} {--force : Force update without confirmation}';
    protected $description = 'Set low stock alert threshold for all products';

    public function handle(): int
    {
        $threshold = (int) $this->argument('threshold');

        if (!$this->option('force') && !$this->confirm("Are you sure you want to set low stock alert threshold to {$threshold} for ALL products?")) {
            $this->warn('Operation cancelled.');
            return 1;
        }

        $this->info("Setting low_stock_threshold = {$threshold} for all products...");

        $updatedCount = 0;

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'low_stock_threshold')) {
            $updatedCount = DB::table('products')->count();
            DB::table('products')->update(['low_stock_threshold' => $threshold]);
        }

        $this->info("✓ Successfully updated {$updatedCount} product(s) to low stock threshold = {$threshold}.");

        return 0;
    }
}

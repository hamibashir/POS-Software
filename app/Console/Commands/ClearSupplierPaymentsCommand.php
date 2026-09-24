<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearSupplierPaymentsCommand extends Command
{
    protected $signature   = 'suppliers:clear-payments {--force : Force deletion without confirmation}';
    protected $description = 'Safely clear all supplier payments and reset purchase paid amounts to 0';

    public function handle(): int
    {
        if (!$this->option('force') && !$this->confirm('Are you sure you want to delete ALL supplier payments? This cannot be undone.')) {
            $this->warn('Operation cancelled.');
            return 1;
        }

        $this->info('Starting supplier payments reset...');

        $deletedPayments = 0;
        $updatedPurchases = 0;

        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('supplier_payments')) {
            $deletedPayments = DB::table('supplier_payments')->count();
            DB::table('supplier_payments')->delete();
            try { DB::statement('ALTER TABLE supplier_payments AUTO_INCREMENT = 1;'); } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'paid_amount')) {
            $updatedPurchases = DB::table('purchases')->where('paid_amount', '>', 0)->count();
            DB::table('purchases')->update(['paid_amount' => 0]);
        }

        Schema::enableForeignKeyConstraints();

        $this->info("✓ Cleared {$deletedPayments} supplier payment transaction(s).");
        $this->info("✓ Reset paid amounts on {$updatedPurchases} purchase order(s).");
        $this->info("All supplier payments successfully reset!");

        return 0;
    }
}

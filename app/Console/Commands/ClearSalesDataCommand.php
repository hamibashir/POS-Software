<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearSalesDataCommand extends Command
{
    protected $signature   = 'sales:clear {--force : Force deletion without confirmation}';
    protected $description = 'Safely clear all sales, sale items, customer returns, and credit clearance payments';

    public function handle(): int
    {
        if (!$this->option('force') && !$this->confirm('Are you sure you want to delete ALL sales and return data? This cannot be undone.')) {
            $this->warn('Operation cancelled.');
            return 1;
        }

        $this->info('Starting sales data reset...');

        $deletedSaleReturnItems = 0;
        $deletedSaleReturns     = 0;
        $deletedSaleItems       = 0;
        $deletedSales           = 0;
        $deletedPayments        = 0;
        $deletedMovements       = 0;

        Schema::disableForeignKeyConstraints();

        // 1. Delete sale return items
        if (Schema::hasTable('sale_return_items')) {
            $deletedSaleReturnItems = DB::table('sale_return_items')->count();
            DB::table('sale_return_items')->delete();
            try { DB::statement('ALTER TABLE sale_return_items AUTO_INCREMENT = 1;'); } catch (\Throwable $e) {}
        }

        // 2. Delete sale returns
        if (Schema::hasTable('sale_returns')) {
            $deletedSaleReturns = DB::table('sale_returns')->count();
            DB::table('sale_returns')->delete();
            try { DB::statement('ALTER TABLE sale_returns AUTO_INCREMENT = 1;'); } catch (\Throwable $e) {}
        }

        // 3. Delete sale items
        if (Schema::hasTable('sale_items')) {
            $deletedSaleItems = DB::table('sale_items')->count();
            DB::table('sale_items')->delete();
            try { DB::statement('ALTER TABLE sale_items AUTO_INCREMENT = 1;'); } catch (\Throwable $e) {}
        }

        // 4. Delete sales
        if (Schema::hasTable('sales')) {
            $deletedSales = DB::table('sales')->count();
            DB::table('sales')->delete();
            try { DB::statement('ALTER TABLE sales AUTO_INCREMENT = 1;'); } catch (\Throwable $e) {}
        }

        // 5. Delete employee / customer credit clearance payments against sales
        if (Schema::hasTable('employee_payments')) {
            $deletedPayments = DB::table('employee_payments')->count();
            DB::table('employee_payments')->delete();
            try { DB::statement('ALTER TABLE employee_payments AUTO_INCREMENT = 1;'); } catch (\Throwable $e) {}
        }

        // 6. Delete sale and customer return stock movements
        if (Schema::hasTable('stock_movements')) {
            $query = DB::table('stock_movements')
                ->whereNotNull('sale_id')
                ->orWhereIn('type', ['sale', 'return'])
                ->orWhere('notes', 'like', '%Sale%')
                ->orWhere('notes', 'like', '%Return%');

            $deletedMovements = $query->count();
            $query->delete();
        }

        Schema::enableForeignKeyConstraints();

        $this->info("✓ Cleared {$deletedSaleReturnItems} sale return item(s).");
        $this->info("✓ Cleared {$deletedSaleReturns} sale return voucher(s).");
        $this->info("✓ Cleared {$deletedSaleItems} sale line item(s).");
        $this->info("✓ Cleared {$deletedSales} sale invoice(s).");
        $this->info("✓ Cleared {$deletedPayments} customer credit payment(s).");
        $this->info("✓ Cleared {$deletedMovements} sale stock movement audit log(s).");
        $this->info("All sales data successfully reset to clean state!");

        return 0;
    }
}

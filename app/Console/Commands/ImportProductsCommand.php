<?php

namespace App\Console\Commands;

use App\Services\CsvProductImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-products {--file= : Path to CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from authentic CSV preserving exact codes as IDs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $file = $this->option('file') ?: database_path('data/products.csv');

        if (!File::exists($file)) {
            $this->error("CSV file not found: {$file}");
            return Command::FAILURE;
        }

        $this->info("Importing products from {$file}...");
        $content = File::get($file);
        $result = CsvProductImporter::import($content);

        $this->info("Product import completed successfully!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Inserted', $result['inserted']],
                ['Updated', $result['updated']],
                ['Skipped', $result['skipped']],
                ['Total Products in Database', $result['total']],
                ['Min ID / Code', $result['min_id']],
                ['Max ID / Code', $result['max_id']],
            ]
        );

        return Command::SUCCESS;
    }
}

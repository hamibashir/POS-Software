<?php

namespace Database\Seeders;

use App\Services\CsvProductImporter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProductCsvSeeder extends Seeder
{
    /**
     * Seed products from authentic CSV.
     */
    public function run(): void
    {
        $csvPath = database_path('data/products.csv');
        if (!File::exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            return;
        }

        $this->command->info("Starting product import from {$csvPath}...");
        $content = File::get($csvPath);
        $result = CsvProductImporter::import($content);

        $this->command->info("Product import completed successfully!");
        $this->command->table(
            ['Metric', 'Count'],
            [
                ['Inserted', $result['inserted']],
                ['Updated', $result['updated']],
                ['Skipped', $result['skipped']],
                ['Total Products', $result['total']],
                ['Min Product Code / ID', $result['min_id']],
                ['Max Product Code / ID', $result['max_id']],
            ]
        );
    }
}

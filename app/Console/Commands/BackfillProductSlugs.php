<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class BackfillProductSlugs extends Command
{
    protected $signature   = 'products:backfill-slugs';
    protected $description = 'Generate slugs for products that do not have one yet';

    public function handle(): int
    {
        $products = Product::withTrashed()->whereNull('slug')->orWhere('slug', '')->get();
        foreach ($products as $product) {
            $product->slug = Product::generateSlug($product->name, $product->id);
            $product->saveQuietly();
        }
        $this->info("Backfilled slugs for {$products->count()} product(s).");
        return 0;
    }
}

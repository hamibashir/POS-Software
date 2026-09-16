<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'supplier_id')) {
                $table->foreignId('supplier_id')
                    ->nullable()
                    ->after('category_id')
                    ->constrained('suppliers')
                    ->nullOnDelete();
            }
        });

        // Backfill supplier_id from purchases if purchase_items exist
        try {
            $purchaseItems = DB::table('purchase_items')
                ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                ->whereNotNull('purchases.supplier_id')
                ->select('purchase_items.product_id', 'purchases.supplier_id')
                ->orderBy('purchases.id', 'desc')
                ->get();

            foreach ($purchaseItems as $item) {
                DB::table('products')
                    ->where('id', $item->product_id)
                    ->whereNull('supplier_id')
                    ->update(['supplier_id' => $item->supplier_id]);
            }
        } catch (\Throwable $e) {
            // Ignore if tables empty or not populated yet
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'supplier_id')) {
                $table->dropConstrainedForeignId('supplier_id');
            }
        });
    }
};

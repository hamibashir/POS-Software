<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_id')
                ->constrained('purchases')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            // Snapshot of product data at time of purchase
            $table->string('product_name', 200);
            $table->string('product_sku', 100);

            // Quantities and cost
            $table->integer('quantity');
            $table->decimal('unit_cost', 12, 2);         // Cost per unit at time of purchase
            $table->decimal('total_cost', 12, 2);        // quantity * unit_cost

            $table->timestamps();

            // Indexes
            $table->index('purchase_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                ->constrained('sales')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            // Snapshot of product data at time of sale (for integrity)
            $table->string('product_name', 200);
            $table->string('product_sku', 100);
            $table->string('product_unit', 30)->default('pc');

            // Quantities and pricing
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);        // Sale price at time of sale
            $table->decimal('cost_price', 12, 2);        // Cost at time of sale (for profit)
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('total_price', 12, 2);       // quantity * unit_price - discount

            $table->timestamps();

            // Indexes
            $table->index('sale_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};

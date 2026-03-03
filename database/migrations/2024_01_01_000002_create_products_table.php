<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            // Identification
            $table->string('name', 200);
            $table->string('sku', 100)->unique();
            $table->string('barcode', 100)->nullable()->unique();
            $table->text('description')->nullable();

            // Unit & Pricing
            $table->string('unit', 30)->default('pc');  // pc, kg, meter, box, etc.
            $table->decimal('cost_price', 12, 2)->default(0.00);
            $table->decimal('sale_price', 12, 2)->default(0.00);

            // Stock
            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(10);

            // Catalog
            $table->string('image', 255)->nullable();
            $table->boolean('show_in_catalog')->default(true);
            $table->boolean('is_active')->default(true);

            // Soft delete + timestamps
            $table->softDeletes();
            $table->timestamps();

            // Indexes for fast POS search
            $table->index('name');
            $table->index('sku');
            $table->index('barcode');
            $table->index('is_active');
            $table->index('category_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

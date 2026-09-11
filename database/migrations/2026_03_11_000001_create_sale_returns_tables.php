<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Sale returns table
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number', 50)->unique();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->string('customer_name', 150)->default('Walk-in Customer');
            $table->string('customer_phone', 30)->nullable();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('total_return_amount', 12, 2)->default(0);
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->string('refund_method', 30)->default('cash'); // cash, card, credit_adjustment
            $table->string('refund_status', 20)->default('completed'); // completed, pending
            $table->date('returned_at')->nullable();
            $table->string('reason', 150)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('sale_id');
            $table->index('employee_id');
            $table->index('user_id');
            $table->index('returned_at');
        });

        // 2. Sale return items table
        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')->constrained('sale_returns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('sale_item_id')->nullable()->constrained('sale_items')->nullOnDelete();
            $table->string('product_name', 150);
            $table->string('product_sku', 60);
            $table->string('product_unit', 30)->default('pcs');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2); // Exact sales rate item was sold at
            $table->decimal('total_price', 12, 2); // quantity * unit_price
            $table->string('reason', 150)->nullable();
            $table->timestamps();

            $table->index('sale_return_id');
            $table->index('product_id');
            $table->index('sale_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sale_returns');
    }
};

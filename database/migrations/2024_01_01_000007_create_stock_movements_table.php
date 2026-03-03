<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            // Product being tracked
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            // Movement type
            $table->enum('type', [
                'sale',         // stock decreased via POS sale
                'purchase',     // stock increased via purchase entry
                'adjustment',   // manual stock adjustment by admin
                'return',       // future: stock returned on refund
            ]);

            // Direction: positive = stock in, negative = stock out
            $table->integer('quantity');            // Can be negative for outgoing stock
            $table->integer('stock_before');        // Stock level before this movement
            $table->integer('stock_after');         // Stock level after this movement

            // Source references (nullable — not always linked)
            $table->foreignId('sale_id')
                ->nullable()
                ->constrained('sales')
                ->nullOnDelete();

            $table->foreignId('purchase_id')
                ->nullable()
                ->constrained('purchases')
                ->nullOnDelete();

            // Who triggered the movement
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for reports and audit trail
            $table->index('product_id');
            $table->index('type');
            $table->index('sale_id');
            $table->index('purchase_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

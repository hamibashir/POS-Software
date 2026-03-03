<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            // Invoice
            $table->string('invoice_number', 50)->unique();

            // Cashier who processed the sale
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Customer (optional — Guest Walk-in by default)
            $table->string('customer_name', 150)->default('Walk-in Customer');
            $table->string('customer_phone', 30)->nullable();

            // Financials
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('change_amount', 12, 2)->default(0.00);

            // Payment
            $table->enum('payment_method', ['cash', 'card'])->default('cash');

            // Status
            $table->enum('status', ['completed', 'voided', 'held'])->default('completed');

            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('invoice_number');
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};

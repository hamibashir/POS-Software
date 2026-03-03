<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            // Reference
            $table->string('reference_number', 50)->unique();

            // Who recorded it
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Supplier (optional text — no supplier table in v1)
            $table->string('supplier_name', 150)->nullable();

            // Financials
            $table->decimal('total_amount', 12, 2)->default(0.00);

            // Dates
            $table->date('received_at');

            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('reference_number');
            $table->index('user_id');
            $table->index('received_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};

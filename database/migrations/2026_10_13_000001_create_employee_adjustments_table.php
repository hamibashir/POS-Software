<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('previous_due', 12, 2)->default(0.00);
            $table->decimal('new_due', 12, 2)->default(0.00);
            $table->decimal('amount', 12, 2); // Delta applied to due (new_due - previous_due)
            $table->text('reason');
            $table->boolean('affect_sales')->default(false); // Whether to impact store sales/ledger (default false = balance only)
            $table->boolean('is_active')->default(true); // true = applied, false = undone
            $table->timestamps();

            $table->index('employee_id');
            $table->index('is_active');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_adjustments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Employees table
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('phone', 30);
            $table->text('address');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('phone');
            $table->index('is_active');
        });

        // 2. Employee payments table (for clearing pending credit dues)
        Schema::create('employee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30)->default('cash'); // cash, bank, salary_deduction
            $table->date('payment_date');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('payment_date');
        });

        // 3. Update sales table for credit sale & employee link
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('user_id')->constrained('employees')->nullOnDelete();
            $table->string('payment_method', 30)->default('cash')->change();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_id');
            $table->string('payment_method', 20)->default('cash')->change();
        });

        Schema::dropIfExists('employee_payments');
        Schema::dropIfExists('employees');
    }
};

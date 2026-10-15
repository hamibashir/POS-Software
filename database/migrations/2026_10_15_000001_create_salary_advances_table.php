<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create salary_advances table
        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('payment_method', 30)->default('cash'); // cash, bank, cheque, other
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('pending'); // pending, deducted
            $table->foreignId('salary_payment_id')->nullable()->constrained('salary_payments')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('user_id');
            $table->index('date');
            $table->index('status');
        });

        // 2. Add advance_deduction to salary_payments table
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->decimal('advance_deduction', 12, 2)->default(0.00)->after('deduction_amount');
        });
    }

    public function down(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->dropColumn('advance_deduction');
        });

        Schema::dropIfExists('salary_advances');
    }
};

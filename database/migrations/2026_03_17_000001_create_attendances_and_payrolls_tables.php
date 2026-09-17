<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add salary and holiday threshold fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('salary', 12, 2)->default(0.00)->after('role');
            $table->unsignedSmallInteger('allowed_leaves')->default(4)->after('salary');
            $table->string('phone', 30)->nullable()->after('allowed_leaves');
            $table->string('designation', 100)->nullable()->after('phone');
        });

        // 2. Create attendances table
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->string('status', 30)->default('present'); // present, absent, half_day, leave, holiday
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('notes', 255)->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index('user_id');
            $table->index('date');
            $table->index('status');
        });

        // 3. Create salary_payments table
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('month_year', 7); // e.g. 2026-09
            $table->decimal('base_salary', 12, 2)->default(0.00);
            $table->unsignedSmallInteger('total_days')->default(30);
            $table->decimal('present_days', 4, 1)->default(0.0);
            $table->decimal('absent_days', 4, 1)->default(0.0);
            $table->unsignedSmallInteger('allowed_leaves')->default(4);
            $table->decimal('excess_absences', 4, 1)->default(0.0);
            $table->decimal('deduction_amount', 12, 2)->default(0.00);
            $table->decimal('bonus_amount', 12, 2)->default(0.00);
            $table->decimal('net_payable', 12, 2)->default(0.00);
            $table->date('payment_date');
            $table->string('payment_method', 30)->default('cash'); // cash, bank, etc.
            $table->string('status', 30)->default('paid'); // paid, pending
            $table->text('notes')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'month_year']);
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
        Schema::dropIfExists('attendances');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['salary', 'allowed_leaves', 'phone', 'designation']);
        });
    }
};

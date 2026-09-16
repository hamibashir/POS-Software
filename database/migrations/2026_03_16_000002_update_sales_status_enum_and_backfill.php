<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('status', 50)->default('completed')->change();
        });

        // Backfill existing credit sales where paid_amount < total_amount to 'pending'
        DB::table('sales')
            ->where('payment_method', 'credit')
            ->where(function($q) {
                $q->where('paid_amount', '<', DB::raw('total_amount'))
                  ->orWhereNull('paid_amount')
                  ->orWhere('paid_amount', 0);
            })
            ->where('status', '!=', 'voided')
            ->update(['status' => 'pending']);
    }

    public function down(): void
    {
        DB::table('sales')
            ->where('status', 'pending')
            ->update(['status' => 'completed']);
    }
};

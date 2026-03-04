<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('supplier_phone', 30)->nullable()->after('supplier_name');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('total_amount');
            $table->string('payment_method', 20)->default('cash')->after('paid_amount');
            $table->string('status', 20)->default('received')->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['supplier_phone', 'paid_amount', 'payment_method', 'status']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 16, 4)->change();
        });

        if (Schema::hasTable('purchase_return_items')) {
            Schema::table('purchase_return_items', function (Blueprint $table) {
                $table->decimal('unit_cost', 16, 4)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 12, 2)->change();
        });

        if (Schema::hasTable('purchase_return_items')) {
            Schema::table('purchase_return_items', function (Blueprint $table) {
                $table->decimal('unit_cost', 12, 2)->change();
            });
        }
    }
};

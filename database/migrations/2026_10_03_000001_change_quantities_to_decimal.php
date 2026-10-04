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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('stock_quantity', 12, 3)->default(0.000)->change();
            $table->decimal('low_stock_threshold', 12, 3)->default(1.000)->change();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('quantity', 12, 3)->change();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('quantity', 12, 3)->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('quantity', 12, 3)->change();
            $table->decimal('stock_before', 12, 3)->change();
            $table->decimal('stock_after', 12, 3)->change();
        });

        if (Schema::hasTable('sale_return_items')) {
            Schema::table('sale_return_items', function (Blueprint $table) {
                $table->decimal('quantity', 12, 3)->change();
            });
        }

        if (Schema::hasTable('purchase_return_items')) {
            Schema::table('purchase_return_items', function (Blueprint $table) {
                $table->decimal('quantity', 12, 3)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock_quantity')->default(0)->change();
            $table->integer('low_stock_threshold')->default(1)->change();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->integer('quantity')->change();
            $table->integer('stock_before')->change();
            $table->integer('stock_after')->change();
        });

        if (Schema::hasTable('sale_return_items')) {
            Schema::table('sale_return_items', function (Blueprint $table) {
                $table->integer('quantity')->change();
            });
        }

        if (Schema::hasTable('purchase_return_items')) {
            Schema::table('purchase_return_items', function (Blueprint $table) {
                $table->integer('quantity')->change();
            });
        }
    }
};

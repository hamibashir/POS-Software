<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create suppliers table
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('company_name', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->decimal('opening_balance', 12, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('phone');
            $table->index('is_active');
        });

        // 2. Add supplier_id to purchases
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('user_id')->constrained('suppliers')->nullOnDelete();
        });

        // 3. Add supplier_id to purchase_returns
        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('purchase_id')->constrained('suppliers')->nullOnDelete();
        });

        // 4. Create supplier_payments table
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30)->default('cash'); // cash, bank, cheque, online
            $table->date('payment_date');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference_number', 60)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('supplier_id');
            $table->index('payment_date');
            $table->index('user_id');
        });

        // 5. Backfill existing suppliers from purchases and purchase_returns
        $existingSupplierNames = DB::table('purchases')
            ->whereNotNull('supplier_name')
            ->where('supplier_name', '!=', '')
            ->distinct()
            ->pluck('supplier_name');

        foreach ($existingSupplierNames as $name) {
            $firstPurchase = DB::table('purchases')
                ->where('supplier_name', $name)
                ->first();

            $supplierId = DB::table('suppliers')->insertGetId([
                'name'            => $name,
                'phone'           => $firstPurchase->supplier_phone ?? null,
                'address'         => null,
                'opening_balance' => 0.00,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // Link purchases
            DB::table('purchases')
                ->where('supplier_name', $name)
                ->update(['supplier_id' => $supplierId]);

            // Link purchase_returns
            DB::table('purchase_returns')
                ->where('supplier_name', $name)
                ->update(['supplier_id' => $supplierId]);

            // For existing purchases with paid_amount > 0, generate historical payment records
            $paidPurchases = DB::table('purchases')
                ->where('supplier_id', $supplierId)
                ->where('paid_amount', '>', 0)
                ->get();

            foreach ($paidPurchases as $p) {
                DB::table('supplier_payments')->insert([
                    'supplier_id'      => $supplierId,
                    'purchase_id'      => $p->id,
                    'amount'           => $p->paid_amount,
                    'payment_method'   => $p->payment_method ?? 'cash',
                    'payment_date'     => $p->received_at ? date('Y-m-d', strtotime($p->received_at)) : date('Y-m-d', strtotime($p->created_at)),
                    'user_id'          => $p->user_id,
                    'reference_number' => $p->reference_number,
                    'notes'            => "Initial payment recorded with purchase {$p->reference_number}",
                    'created_at'       => $p->created_at,
                    'updated_at'       => $p->updated_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');

        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
        });

        Schema::dropIfExists('suppliers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('employees', 'opening_balance')) {
            \Illuminate\Support\Facades\Schema::table('employees', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->decimal('opening_balance', 12, 2)->default(0.00)->after('address');
            });
        }

        // 1. Reset all opening balances to 0 first to ensure clean state
        DB::table('employees')->update(['opening_balance' => 0.00]);

        // 2. Exact customer balance mapping from the provided Hassan Traders ledger
        $customerBalances = [
            ['name' => 'Arslan Plumber', 'balance' => 1750.00],
            ['name' => 'Aficer Plumber', 'balance' => 1850.00],
            ['name' => 'Afzal Plumber', 'balance' => 280.00],
            ['name' => 'Javed Plumber (Shekhupura)', 'balance' => 2660.00],
            ['name' => 'Bashir Electrician', 'balance' => 500.00],
            ['name' => 'Tariq Paint (Irfan)', 'balance' => 380.00],
            ['name' => 'Tariq Sanitary (Supply)', 'balance' => 900.00],
            ['name' => 'Universal Traders', 'balance' => 300.00],
            ['name' => 'Pakistan Electrician', 'balance' => 8140.00],
            ['name' => 'Kashif Sahab (Construction)', 'balance' => 1400.00],
            ['name' => 'Imran AC Technician', 'balance' => 2840.00],
            ['name' => 'Tariq Sanitary (Shop)', 'balance' => 6700.00],
            ['name' => 'Hanan Plumber', 'balance' => 1000.00],
            ['name' => 'Mohsin Suply', 'balance' => 2000.00],
            ['name' => 'Sajid Bulider', 'balance' => 5440.00],
            ['name' => 'Istambol Sanitary', 'balance' => 5890.00],
            ['name' => 'Takht Hazara', 'balance' => 1005.00],
            ['name' => 'Waseem Plumber', 'balance' => 1067.00],
            ['name' => 'Universal Sanitary NPF', 'balance' => 22660.00],
            ['name' => 'Eman Traders Waqas', 'balance' => 2980.00],
            ['name' => 'Mughal Traders PWD', 'balance' => 16560.00],
            ['name' => 'Fida Hassan', 'balance' => 7444.00],
            ['name' => 'GFC Sanitary', 'balance' => 1170.00],
            ['name' => 'New Khyber Corporation', 'balance' => 3220.00],
            ['name' => 'Khyber Corporation', 'balance' => 4500.00],
            ['name' => 'Mazhar Plumber', 'balance' => 50.00],
            ['name' => 'Ijaz Sahab', 'balance' => 200.00],
            ['name' => 'Zahid Electrician + Plumber', 'balance' => 2050.00],
            ['name' => 'Shop', 'balance' => 1020.33],
        ];

        foreach ($customerBalances as $item) {
            $customer = DB::table('employees')->where('name', $item['name'])->first();

            if ($customer) {
                DB::table('employees')->where('id', $customer->id)->update([
                    'opening_balance' => $item['balance'],
                    'updated_at'      => now(),
                ]);
            } else {
                DB::table('employees')->insert([
                    'name'            => $item['name'],
                    'phone'           => '051-8891930',
                    'address'         => 'Rawalpindi / Islamabad',
                    'opening_balance' => $item['balance'],
                    'is_active'       => true,
                    'notes'           => 'Imported balance',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to preserve customer data
    }
};

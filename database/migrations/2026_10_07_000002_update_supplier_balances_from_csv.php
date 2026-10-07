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
        // 1. Reset all supplier opening balances to 0 first
        DB::table('suppliers')->update(['opening_balance' => 0.00]);

        // 2. Exact supplier balance mapping from Hassan Traders Ledger
        $supplierBalances = [
            ['name' => 'Cash Purchase', 'balance' => 0.00],
            ['name' => 'Malik Idrees', 'balance' => 153592.00],
            ['name' => 'Ghulam Nabi & Sons', 'balance' => 179658.00],
            ['name' => 'Riaz Motor', 'balance' => 3000.00],
            ['name' => 'Sea Star', 'balance' => 4690.00],
            ['name' => 'Cooker Ruber (Baba Sports)', 'balance' => 2000.00],
            ['name' => 'ZT POP', 'balance' => 3645.00],
            ['name' => 'Asif Butt', 'balance' => 6080.00],
            ['name' => 'TC Trading', 'balance' => 20019.00],
            ['name' => 'Waheed Khan', 'balance' => 77115.00],
            ['name' => 'Popular', 'balance' => 10657.00],
            ['name' => 'Noman', 'balance' => 46784.00],
            ['name' => 'Azad Traders (Factory)', 'balance' => 39528.00],
            ['name' => 'Naflo Waqas', 'balance' => 22208.00],
            ['name' => 'Wiper Supply', 'balance' => 30196.00],
            ['name' => 'Butt Corporation', 'balance' => 40012.00],
            ['name' => 'HA Traders', 'balance' => 53792.00],
            ['name' => 'Rana Mubarak', 'balance' => 21725.00],
            ['name' => 'Umair Traders', 'balance' => 0.00],
            ['name' => 'Chitral / Hope Chemicals (Amjad)', 'balance' => 5120.00],
            ['name' => 'Nasir Corporation', 'balance' => 99.00],
            ['name' => 'Zahid Enterprises (Eflux)', 'balance' => 130482.00],
            ['name' => 'Tesla (Imran Sahab)', 'balance' => 7660.00],
            ['name' => 'Shamsher Khan', 'balance' => 2980.00],
            ['name' => 'Water Filter Supply', 'balance' => 6640.00],
            ['name' => 'Cable 2 Core', 'balance' => 19886.00],
            ['name' => 'Arslan Trading Rawat', 'balance' => 241688.51],
            ['name' => 'Non Switches', 'balance' => 20798.00],
            ['name' => 'Zeeshan Cool PWD', 'balance' => 24019.00],
            ['name' => 'MT Screw 2468', 'balance' => 34428.50],
            ['name' => 'Shakoor Sahab (English Cable)', 'balance' => 4950.00],
            ['name' => 'Bahram Khan', 'balance' => 500.00],
            ['name' => 'Hi-Tech (Geyser)', 'balance' => 9400.00],
            ['name' => 'Imran Meter Box', 'balance' => 16560.00],
            ['name' => 'Chenab Motor', 'balance' => 84750.00],
            ['name' => 'Afzal & Sons (Screw + Nails)', 'balance' => 13848.00],
            ['name' => 'Mughal Traders', 'balance' => 82185.00],
            ['name' => 'Siddique Traders (Azeem)', 'balance' => 9140.00],
            ['name' => 'SMT Tools', 'balance' => 21732.00],
            ['name' => 'Wood Touch', 'balance' => 24929.00],
            ['name' => 'Aluminium Ladder', 'balance' => 160.00],
            ['name' => 'Bhatti Traders', 'balance' => 0.00],
            ['name' => 'Faco Industries (Karachi)', 'balance' => 6400.00],
            ['name' => 'Spray Paint Supply', 'balance' => 12605.00],
            ['name' => 'Yasir Shah', 'balance' => 5440.00],
            ['name' => 'Panasonic Cell', 'balance' => 0.00],
            ['name' => 'Deer Ceiling POP', 'balance' => 3700.00],
            ['name' => 'Baitun Conduit', 'balance' => 40857.00],
            ['name' => 'Ali Akber (Irfan)', 'balance' => 2900.00],
            ['name' => 'Hitachi Tools', 'balance' => 0.00],
            ['name' => 'Digits (Zeeshan)', 'balance' => 17660.00],
            ['name' => 'ITC Ilyas Trading', 'balance' => 76015.00],
            ['name' => 'Adnan Fancy Latoo', 'balance' => 300.00],
            ['name' => 'Super Master', 'balance' => 11570.00],
            ['name' => 'Lahore Tools', 'balance' => 15835.00],
            ['name' => 'Sabir Sahab', 'balance' => 595.00],
            ['name' => 'Abdul Islam Traders (China Supply)', 'balance' => 27030.00],
            ['name' => 'Jali Supply (Farooq Sahab)', 'balance' => 0.00],
            ['name' => 'Shahid Sahab (Tester + Tape)', 'balance' => 0.00],
            ['name' => 'Star SMD & LED Bulb', 'balance' => 12260.00],
            ['name' => 'Shaban Traders', 'balance' => 14941.00],
            ['name' => 'Usman Sanitary (China Item)', 'balance' => 10.00],
            ['name' => 'Nawaz Pipe', 'balance' => 82000.00],
            ['name' => 'General Sanitary', 'balance' => 7250.00],
            ['name' => 'Adnan China Supply', 'balance' => 240.00],
            ['name' => 'Hood Pipe Usman', 'balance' => 12100.00],
            ['name' => 'MB Master Haider', 'balance' => 1165.00],
            ['name' => 'Tile Bond 3 Star', 'balance' => 1350.00],
            ['name' => 'Unique Traders Jahangir', 'balance' => 75.00],
            ['name' => 'Huzaifa Traders', 'balance' => 5879.46],
            ['name' => 'Dada Pump', 'balance' => 0.00],
            ['name' => 'Adamjee Tariq', 'balance' => 0.00],
            ['name' => 'Faco Commercial Pipe', 'balance' => 106562.00],
            ['name' => 'Cell Toshiba (Gawalmandi)', 'balance' => 6840.00],
            ['name' => 'Khalid Traders Stove', 'balance' => 26300.00],
            ['name' => 'Camy Wajid', 'balance' => 1375.00],
            ['name' => 'Ejaz Sahab (Parda Pipe)', 'balance' => 62925.00],
            ['name' => 'Extension My Home', 'balance' => 58680.00],
            ['name' => 'Limart LED', 'balance' => 11785.00],
            ['name' => 'Gujranwala Fan (Khurram)', 'balance' => 13190.00],
            ['name' => 'Jeko Traders (Eden City)', 'balance' => 18780.00],
            ['name' => 'Arshad Hook Patti', 'balance' => 0.00],
            ['name' => 'Khokhar Supply', 'balance' => 0.00],
            ['name' => 'Aqib s/o Rabnawaz', 'balance' => 0.00],
            ['name' => 'Usman Solution', 'balance' => 750.00],
            ['name' => 'Nova Products', 'balance' => 40.00],
            ['name' => 'Laiya Professional Tools', 'balance' => 0.00],
            ['name' => 'Muzamil CP Cleaner', 'balance' => 0.00],
            ['name' => 'Life Cable', 'balance' => 0.00],
            ['name' => 'Bismillah', 'balance' => 80.00],
            ['name' => 'Dura HM Traders', 'balance' => 87.00],
            ['name' => 'Sabro Fittings', 'balance' => 2410.00],
            ['name' => 'B Clean Pindi Chemicals', 'balance' => 2770.00],
            ['name' => 'Shoukat Bukhari', 'balance' => 0.00],
            ['name' => 'Camelion Products', 'balance' => 12270.00],
            ['name' => 'Sova LED', 'balance' => 10850.00],
            ['name' => 'Kohenoor Traders PWD', 'balance' => 0.00],
            ['name' => 'Mian Fiaz Sahab (Accessories Set)', 'balance' => 15000.00],
        ];

        foreach ($supplierBalances as $item) {
            $supplier = DB::table('suppliers')->where('name', $item['name'])->first();

            if ($supplier) {
                DB::table('suppliers')->where('id', $supplier->id)->update([
                    'opening_balance' => $item['balance'],
                    'updated_at'      => now(),
                ]);
            } else {
                DB::table('suppliers')->insert([
                    'name'            => $item['name'],
                    'phone'           => null,
                    'address'         => null,
                    'opening_balance' => $item['balance'],
                    'is_active'       => true,
                    'notes'           => 'Imported from ledger',
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
        // No-op to preserve supplier data
    }
};

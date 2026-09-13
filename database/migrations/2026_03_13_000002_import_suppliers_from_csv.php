<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $suppliers = [
            ['name' => 'Cash Purchase', 'address' => 'Anywhere', 'phone' => null],
            ['name' => 'Malik Idrees', 'address' => 'Ali Block, Rawalpindi', 'phone' => '0321-5329474'],
            ['name' => 'Ghulam Nabi & Sons', 'address' => 'Committee Chowk, Rawalpindi', 'phone' => '051-5559439'],
            ['name' => 'Riaz Motor', 'address' => 'Khanna Pul, Rawalpindi', 'phone' => '0333-8238414'],
            ['name' => 'Sea Star', 'address' => 'Soan Gardens, Islamabad', 'phone' => '0321-5570075'],
            ['name' => 'Cooker Ruber (Baba Sports)', 'address' => 'Sialkot', 'phone' => '0321-9397044'],
            ['name' => 'ZT POP', 'address' => 'Sabir Khan, Rawalpindi', 'phone' => '0310-1564035'],
            ['name' => 'Asif Butt', 'address' => 'Gujranwala', 'phone' => '0312-7667663'],
            ['name' => 'TC Trading', 'address' => 'Supply, Rawalpindi', 'phone' => '0335-5866798'],
            ['name' => 'Waheed Khan', 'address' => 'Rawalpindi', 'phone' => '0300-9837568'],
            ['name' => 'Popular', 'address' => 'Rawalpindi', 'phone' => '0346-8603786'],
            ['name' => 'Noman', 'address' => 'Ali Block, Rawalpindi', 'phone' => '0333-9010747'],
            ['name' => 'Azad Traders (Factory)', 'address' => 'Azad Traders, Rawalpindi', 'phone' => '0306-5158894'],
            ['name' => 'Naflo Waqas', 'address' => 'Gujranwala', 'phone' => '0345-7921091'],
            ['name' => 'Wiper Supply', 'address' => 'Rawalpindi', 'phone' => '0333-9696890'],
            ['name' => 'Butt Corporation', 'address' => 'City Sadar Road, Rawalpindi', 'phone' => '0313-9618392'],
            ['name' => 'HA Traders', 'address' => 'Adyala Road, Rawalpindi', 'phone' => '0312-7965607'],
            ['name' => 'Rana Mubarak', 'address' => 'Gujranwala', 'phone' => '0300-8562244'],
            ['name' => 'Umair Traders', 'address' => 'PWD, Islamabad', 'phone' => '0300-5576104'],
            ['name' => 'Chitral / Hope Chemicals (Amjad)', 'address' => 'Model Town, Rawalpindi', 'phone' => '0330-2815550'],
            ['name' => 'Meter Box (Ehtasham)', 'address' => 'Rawalpindi', 'phone' => '0315-0732580'],
            ['name' => 'Khyber Bulb', 'address' => 'Rawalpindi', 'phone' => '051-5532214'],
            ['name' => 'Murtaza Supply', 'address' => 'Humak, Islamabad', 'phone' => '0300-7978378'],
            ['name' => 'Leiya Tool', 'address' => 'Rawalpindi', 'phone' => '051-5530109'],
            ['name' => 'Sajid Tools', 'address' => 'Khanna, Rawalpindi', 'phone' => '0312-5380612'],
            ['name' => 'Nasir Corporation', 'address' => 'City Sadar Road, Rawalpindi', 'phone' => '051-5538777'],
            ['name' => 'Zahid Enterprises (Eflux)', 'address' => 'Rawalpindi', 'phone' => '0333-1040957'],
            ['name' => 'Tesla (Imran Sahab)', 'address' => 'Rawalpindi', 'phone' => '0333-5637550'],
            ['name' => 'Shamsher Khan', 'address' => 'Rawalpindi', 'phone' => '0334-1531349'],
            ['name' => 'Water Filter Supply', 'address' => 'Supply, Rawalpindi', 'phone' => '0335-0058438'],
            ['name' => 'Cable 2 Core', 'address' => 'Rawalpindi', 'phone' => '0341-5037205'],
            ['name' => 'Arslan Trading Rawat', 'address' => 'GT Road, Rawat', 'phone' => '0327-6024349'],
            ['name' => 'Wiper Plastic', 'address' => 'Rawalpindi', 'phone' => '0305-8647884'],
            ['name' => 'Non Switches', 'address' => 'Gujranwala', 'phone' => '0348-4762837'],
            ['name' => 'Zeeshan Cool PWD', 'address' => 'PWD, Islamabad', 'phone' => '0345-5101479'],
            ['name' => 'Master Fit', 'address' => 'Humak, Islamabad', 'phone' => '051-4493264'],
            ['name' => 'MT Screw 2468', 'address' => 'Rawalpindi', 'phone' => '0347-5358574'],
            ['name' => 'Shakoor Sahab (English Cable)', 'address' => 'English Cable, Rawalpindi', 'phone' => '0345-5580786'],
            ['name' => 'Polo-Lite', 'address' => 'Rawalpindi', 'phone' => '0321-5080405'],
            ['name' => 'Bahram Khan', 'address' => 'Supply, Rawalpindi', 'phone' => '0300-5345826'],
            ['name' => 'Hi-Tech (Geyser)', 'address' => 'Supply, Rawalpindi', 'phone' => '0331-6366775'],
            ['name' => 'Imran Meter Box', 'address' => 'Usman Block, Rawalpindi', 'phone' => '0334-5345298'],
            ['name' => 'Chenab Motor', 'address' => 'Gujranwala', 'phone' => '0300-8648648'],
            ['name' => 'Afzal & Sons (Screw + Nails)', 'address' => 'Car Chowk, Rawalpindi', 'phone' => '0314-5260226'],
            ['name' => 'Mughal Traders', 'address' => 'Supply, Rawalpindi', 'phone' => '0331-5352027'],
            ['name' => 'Siddique Traders (Azeem)', 'address' => 'Supply, Rawalpindi', 'phone' => '0335-0082460'],
            ['name' => 'SMT Tools', 'address' => 'City Sadar Road, Rawalpindi', 'phone' => '0332-8818356'],
            ['name' => 'Wood Touch', 'address' => 'Scheme 3, Rawalpindi', 'phone' => '0300-5185810'],
            ['name' => 'Aluminium Ladder', 'address' => 'Gujranwala', 'phone' => '0300-6465350'],
            ['name' => 'Bhatti Traders', 'address' => 'Adyala Road, Rawalpindi', 'phone' => '0311-4700282'],
            ['name' => 'Faco Industries (Karachi)', 'address' => 'Karachi', 'phone' => '0306-2680620'],
            ['name' => 'Spray Paint Supply', 'address' => 'Supply, Rawalpindi', 'phone' => '0333-0554407'],
            ['name' => 'Board Hassan', 'address' => 'Supply, Rawalpindi', 'phone' => '0330-5859396'],
            ['name' => 'Yasir Shah', 'address' => 'Supply, Rawalpindi', 'phone' => '0342-5144440'],
            ['name' => 'Panasonic Cell', 'address' => 'Supply, Rawalpindi', 'phone' => '0336-5046970'],
            ['name' => 'Umer Pipe', 'address' => 'Adyala, Rawalpindi', 'phone' => '0311-9611916'],
            ['name' => 'Deer Ceiling POP', 'address' => 'Supply, Rawalpindi', 'phone' => '0344-8172777'],
            ['name' => 'Baitun Conduit', 'address' => 'Gujranwala', 'phone' => '0300-0604050'],
            ['name' => 'Ali Akber (Irfan)', 'address' => 'Supply, Rawalpindi', 'phone' => '0346-4993038'],
            ['name' => 'Hitachi Tools', 'address' => 'Car Chowk, Rawalpindi', 'phone' => '0333-7585558'],
            ['name' => 'Yasir Malik (China Items)', 'address' => 'Supply, Rawalpindi', 'phone' => '0333-5406686'],
            ['name' => 'Digits (Zeeshan)', 'address' => 'Rafi Block, Rawalpindi', 'phone' => '0333-5531154'],
            ['name' => 'ITC Ilyas Trading', 'address' => 'Rawalpindi', 'phone' => '0333-9741297'],
            ['name' => 'Adnan Fancy Latoo', 'address' => 'Gujranwala', 'phone' => '0313-7481064'],
            ['name' => 'Super Master', 'address' => 'Gujranwala', 'phone' => '055-4218522'],
            ['name' => 'Lahore Tools', 'address' => 'Saddar, Rawalpindi', 'phone' => '0333-5692284'],
            ['name' => 'Ussama Supply', 'address' => 'Supply, Rawalpindi', 'phone' => '0312-7965607'],
            ['name' => 'Sabir Sahab', 'address' => 'Mochi Bazar, Rawalpindi', 'phone' => '0333-5316487'],
            ['name' => 'Abdul Islam Traders (China Supply)', 'address' => 'China Supply, Rawalpindi', 'phone' => '0300-8428756'],
            ['name' => 'Limart LED', 'address' => 'Supply, Rawalpindi', 'phone' => '0318-8157265'],
            ['name' => 'Jali Supply (Farooq Sahab)', 'address' => 'Rawalpindi', 'phone' => '0345-5947285'],
            ['name' => 'Yousaf Butt', 'address' => 'Rawalpindi', 'phone' => '0331-5574944'],
            ['name' => 'Shahid Sahab (Tester + Tape)', 'address' => 'Rawalpindi', 'phone' => '0344-5009455'],
            ['name' => 'Star SMD & LED Bulb', 'address' => 'Supply, Rawalpindi', 'phone' => '0336-9893905'],
            ['name' => 'Shaban Traders', 'address' => 'City Sadar Road, Rawalpindi', 'phone' => '0332-0444831'],
            ['name' => 'Usman Sanitary (China Item)', 'address' => 'Rawalpindi', 'phone' => '0300-7796918'],
            ['name' => 'Spacer Supply (Yasir Nawaz)', 'address' => 'Rawalpindi', 'phone' => '0315-7709412'],
            ['name' => 'Nawaz Pipe', 'address' => 'DHA, Islamabad', 'phone' => null],
            ['name' => 'General Sanitary', 'address' => 'Khanna Pul, Rawalpindi', 'phone' => '0334-5258035'],
            ['name' => 'Adnan China Supply', 'address' => 'Karachi', 'phone' => '0332-2568973'],
            ['name' => 'Hood Pipe Usman', 'address' => 'Rawalpindi', 'phone' => '0323-5063608'],
            ['name' => 'MB Master Haider', 'address' => 'Gujranwala', 'phone' => '0311-0734030'],
            ['name' => 'Tile Bond 3 Star', 'address' => 'Supply, Rawalpindi', 'phone' => '0313-8699149'],
            ['name' => 'Unique Traders Jahangir', 'address' => 'Lahore', 'phone' => '0321-4681014'],
            ['name' => 'Mazhar Supply', 'address' => 'Karachi', 'phone' => '0300-0926052'],
            ['name' => 'Huzaifa Traders', 'address' => 'City Sadar Road, Rawalpindi', 'phone' => '0335-5532553'],
            ['name' => 'Dada Pump', 'address' => 'Islamabad', 'phone' => '0340-5387886'],
            ['name' => 'Ibrahim Corporation', 'address' => 'Bahria Town, Rawalpindi', 'phone' => '0304-5239824'],
            ['name' => 'Adamjee Tariq', 'address' => 'Rawalpindi', 'phone' => '0301-5002203'],
            ['name' => 'Faco Commercial Pipe', 'address' => 'Lahore', 'phone' => '0316-3337812'],
            ['name' => 'Cell Toshiba (Gawalmandi)', 'address' => 'Gawalmandi, Rawalpindi', 'phone' => '0336-0510005'],
            ['name' => 'Khalid Traders Stove', 'address' => 'Gujranwala', 'phone' => '0301-6475408'],
            ['name' => 'Camy Wajid', 'address' => 'Rawalpindi', 'phone' => '0331-5012636'],
            ['name' => 'Ejaz Sahab (Parda Pipe)', 'address' => 'Rawalpindi', 'phone' => '0346-3131804'],
            ['name' => 'Extension My Home', 'address' => 'Rawalpindi', 'phone' => '0336-1505757'],
            ['name' => 'Lismart LED', 'address' => 'Rawalpindi', 'phone' => null],
            ['name' => 'Gujranwala Fan (Khurram)', 'address' => 'Gujranwala', 'phone' => null],
            ['name' => 'Jeko Traders (Eden City)', 'address' => 'Adyala Road, Rawalpindi', 'phone' => '0344-5527001'],
            ['name' => 'Arshad Hook Patti', 'address' => 'Rawalpindi', 'phone' => '0302-8038431'],
            ['name' => 'Khokhar Supply', 'address' => 'Gujranwala', 'phone' => '0311-3749397'],
            ['name' => 'Aqib s/o Rabnawaz', 'address' => 'City Sadar Road, Rawalpindi', 'phone' => '0317-5478039'],
            ['name' => 'Usman Solution', 'address' => 'PWD, Islamabad', 'phone' => '0348-7720151'],
            ['name' => 'Nova Products', 'address' => 'Rawalpindi', 'phone' => '0333-5008096'],
            ['name' => 'Laiya Professional Tools', 'address' => 'City Sadar Road, Rawalpindi', 'phone' => '0300-5863865'],
            ['name' => 'Muzamil CP Cleaner', 'address' => 'Gujranwala', 'phone' => '0302-2317916'],
            ['name' => 'Life Cable', 'address' => 'Supply, Rawalpindi', 'phone' => '0333-9741297'],
            ['name' => 'Bismillah', 'address' => 'Khaza-e-Akhrat, Rawalpindi', 'phone' => null],
            ['name' => 'Dura HM Traders', 'address' => 'Rawalpindi', 'phone' => '0304-2834172'],
            ['name' => 'Sabro Fittings', 'address' => 'Rawalpindi', 'phone' => '0308-5394694'],
            ['name' => 'B Clean Pindi Chemicals', 'address' => 'Rawalpindi', 'phone' => '0336-1503644'],
            ['name' => 'Shoukat Bukhari', 'address' => 'Supply, Rawalpindi', 'phone' => null],
            ['name' => 'Camelion Products', 'address' => 'Islamabad', 'phone' => '0331-5042691'],
            ['name' => 'Rope Light Taha', 'address' => 'Rawalpindi', 'phone' => '0316-5685121'],
            ['name' => 'Sova LED', 'address' => 'Adyala, Rawalpindi', 'phone' => '0310-1344044'],
            ['name' => 'Kohenoor Traders PWD', 'address' => 'PWD, Islamabad', 'phone' => '0321-5009670'],
            ['name' => 'Mian Fiaz Sahab (Accessories Set)', 'address' => 'Rawalpindi', 'phone' => '0300-4597477'],
        ];

        foreach ($suppliers as $s) {
            $existing = DB::table('suppliers')
                ->where('name', $s['name'])
                ->orWhere(function ($query) use ($s) {
                    if (!empty($s['phone'])) {
                        $query->where('phone', $s['phone']);
                    }
                })
                ->first();

            if ($existing) {
                DB::table('suppliers')->where('id', $existing->id)->update([
                    'name'         => $s['name'],
                    'company_name' => $s['name'],
                    'address'      => $s['address'] ?? $existing->address,
                    'phone'        => $s['phone'] ?? $existing->phone,
                    'is_active'    => true,
                    'updated_at'   => now(),
                ]);
            } else {
                DB::table('suppliers')->insert([
                    'name'            => $s['name'],
                    'company_name'    => $s['name'],
                    'phone'           => $s['phone'],
                    'email'           => null,
                    'address'         => $s['address'],
                    'opening_balance' => 0.00,
                    'is_active'       => true,
                    'notes'           => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Keep data intact on rollback
    }
};

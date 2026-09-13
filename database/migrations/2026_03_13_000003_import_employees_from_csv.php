<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $employees = [
            ['name' => 'Arslan Plumber', 'phone' => '0306-5059413', 'address' => 'Kahuta'],
            ['name' => 'Sadaqat Plumber', 'phone' => '0309-0442673', 'address' => 'Sahiwal'],
            ['name' => 'Ramzan Electrician', 'phone' => '0300-6561019', 'address' => 'Pakpattan'],
            ['name' => 'Aficer Plumber', 'phone' => '0317-0508021', 'address' => 'Haripur'],
            ['name' => 'Afzal Plumber', 'phone' => '0317-1520340', 'address' => 'Bagh Rajgan, Rawalpindi'],
            ['name' => 'Fahad Plumber', 'phone' => '0333-9151795', 'address' => 'Bagh Rajgan, Rawalpindi'],
            ['name' => 'Baber Plumber', 'phone' => '0313-7905331', 'address' => 'Bagh Rajgan, Rawalpindi'],
            ['name' => 'Husnain Plumber', 'phone' => '0311-0886356', 'address' => 'Bagh Rajgan, Rawalpindi'],
            ['name' => 'Javed Plumber (Shekhupura)', 'phone' => '0347-1645430', 'address' => 'Sheikhupura'],
            ['name' => 'Maqsood Plumber', 'phone' => '0331-5023641', 'address' => 'Bagh Rajgan, Rawalpindi'],
            ['name' => 'Shakeel Electrician', 'phone' => '0301-6646449', 'address' => 'Okara'],
            ['name' => 'Naeem Plumber', 'phone' => '0304-1379788', 'address' => 'Okara'],
            ['name' => 'Bashir Electrician', 'phone' => '0331-2400606', 'address' => 'Commercial Plaza, Rawalpindi'],
            ['name' => 'Usman AC Technician', 'phone' => '0343-7764522', 'address' => 'Kamalia'],
            ['name' => 'Kaleem Ullah Plumber', 'phone' => '0333-5328447', 'address' => 'Sahiwal'],
            ['name' => 'Faisal Khokhar Electrician', 'phone' => '0333-5388902', 'address' => 'Khanna Pul, Rawalpindi'],
            ['name' => 'Mubashir Electrician', 'phone' => '0315-5218166', 'address' => 'Kasur'],
            ['name' => 'Tariq Paint (Irfan)', 'phone' => '0333-5560991', 'address' => 'Rafi Block, Rawalpindi'],
            ['name' => 'Hafeez Plumber', 'phone' => '0336-0451355', 'address' => 'Sialkot'],
            ['name' => 'Asim AC Technician', 'phone' => '0304-0944711', 'address' => 'Lahore'],
            ['name' => 'Munir Plumber', 'phone' => '0333-8732513', 'address' => 'Daska'],
            ['name' => 'Tariq Sanitary (Supply)', 'phone' => '0313-2300250', 'address' => 'Rafi Block, Rawalpindi'],
            ['name' => 'Qadeer Plumber', 'phone' => '0331-5535830', 'address' => 'Haripur'],
            ['name' => 'Ashraf Baba Plumber', 'phone' => '0343-5464314', 'address' => 'Sialkot'],
            ['name' => 'Universal Traders', 'phone' => '0334-3230575', 'address' => 'Rafi Block, Rawalpindi'],
            ['name' => 'Shahid Ali Carpenter', 'phone' => '0305-8121202', 'address' => 'Car Chowk, Rawalpindi'],
            ['name' => 'Ghulam Abbas (Mitha)', 'phone' => '0345-5946003', 'address' => 'Bahria Town, Rawalpindi'],
            ['name' => 'Kashif Plumber', 'phone' => '0348-5012142', 'address' => 'Haripur'],
            ['name' => 'Assetz Mart', 'phone' => '0330-9356174', 'address' => 'Rafi Block, Rawalpindi'],
            ['name' => 'Pakistan Electrician', 'phone' => '0313-5894556', 'address' => 'Rafi Block, Rawalpindi'],
            ['name' => 'Khan Corporation', 'phone' => '0333-9578218', 'address' => 'Rafi Block, Rawalpindi'],
            ['name' => 'Waqar Ahmed Plumber', 'phone' => '0310-5960803', 'address' => 'Bagh Rajgan, Rawalpindi'],
            ['name' => 'Shah Corporation', 'phone' => '0321-5621950', 'address' => 'Rafi Block, Rawalpindi'],
            ['name' => 'Shakeel Khan Watch Man', 'phone' => '0318-5656975', 'address' => 'c/o Kashif Sahab, Bahria Town'],
            ['name' => 'Attique Plumber', 'phone' => '0310-5203898', 'address' => 'Bahria Town, Rawalpindi'],
            ['name' => 'Usman Sahab', 'phone' => '0333-6363543', 'address' => 'Rafi Block, Rawalpindi'],
            ['name' => 'Kashif Sahab (Construction)', 'phone' => '0343-6667777', 'address' => 'Bahria Town, Rawalpindi'],
            ['name' => 'Imran AC Technician', 'phone' => '0333-5637550', 'address' => 'Kamalia'],
            ['name' => 'Jan Muhammad Electrician', 'phone' => '0331-7792468', 'address' => 'Mamu Kanjan'],
            ['name' => 'Tariq Sanitary (Shop)', 'phone' => '0313-2300250', 'address' => 'Rafi Block, Rawalpindi'],
            ['name' => 'CH. Amin Sahab', 'phone' => '0333-5228389', 'address' => 'Assetz Tower, Rawalpindi'],
            ['name' => 'AG Rana', 'phone' => '0334-5259955', 'address' => 'Rafi Block, Rawalpindi'],
            ['name' => 'Mueez Sales', 'phone' => '0304-5143703', 'address' => 'Shop, Rawalpindi'],
            ['name' => 'Zahid Sahab', 'phone' => '0331-8222475', 'address' => 'DHA, Islamabad'],
            ['name' => 'Ali Hassan Plumber', 'phone' => '0336-6666129', 'address' => 'Bahria Town, Rawalpindi'],
            ['name' => 'Faisal Plumber', 'phone' => '0307-6473580', 'address' => 'Head Office, Rawalpindi'],
            ['name' => 'Khalid Amin', 'phone' => '0300-5502990', 'address' => 'Sector A, Bahria Town'],
            ['name' => 'Waqar Sahab ICI', 'phone' => '0301-8449938', 'address' => 'Sector C, Bahria Town'],
            ['name' => 'Jameel Carpenter', 'phone' => '0307-6834138', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Zahid Driver (Bahria)', 'phone' => '0303-5070131', 'address' => '45/12-L, Sahiwal'],
            ['name' => 'Ghufran', 'phone' => '0336-8397724', 'address' => 'Kahuta'],
            ['name' => 'Malik Idrees', 'phone' => '0336-5329473', 'address' => 'Ali Block, Rawalpindi'],
            ['name' => 'Noman Supply', 'phone' => '0333-9010747', 'address' => 'Ali Block, Rawalpindi'],
            ['name' => 'Faizan Electrician', 'phone' => '0315-5992678', 'address' => 'Takht Pari, Rawalpindi'],
            ['name' => 'Service Station', 'phone' => '051-8891930', 'address' => 'Rafi Block, Rawalpindi'],
            ['name' => 'Sarfraz Plumber', 'phone' => '0311-5907359', 'address' => 'Haripur'],
            ['name' => 'Ibrar Plumber', 'phone' => '0307-5466327', 'address' => 'Rawalpindi'],
            ['name' => 'Arbaz Plumber', 'phone' => '0300-909363', 'address' => 'c/o Aficer, Haripur'],
            ['name' => 'Latif Plumber', 'phone' => '0312-5949670', 'address' => 'Rawalpindi'],
            ['name' => 'Sajid Plumber', 'phone' => '0321-8502652', 'address' => 'Rawalpindi'],
            ['name' => 'Asad Carpenter', 'phone' => '0324-5431911', 'address' => 'Rawalpindi'],
            ['name' => 'Asghar Plumber Bahria', 'phone' => '0310-9185621', 'address' => 'Bahria Town, Rawalpindi'],
            ['name' => 'Faisal Electrician Solar', 'phone' => '0331-5146128', 'address' => 'Scheme 3, Rawalpindi'],
            ['name' => 'Naveed', 'phone' => '051-8891930', 'address' => 'Supply, Rawalpindi'],
            ['name' => 'Arslan + Ghufran', 'phone' => '051-8891930', 'address' => 'Kahuta'],
            ['name' => 'Ramzan + Sadaqat', 'phone' => '051-8891930', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Hanan Plumber', 'phone' => '051-8891930', 'address' => 'Bahawalpur'],
            ['name' => 'Mohsin Suply', 'phone' => '051-8891930', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Awais Corporation', 'phone' => '051-8891930', 'address' => 'Rafi Block, Rawalpindi'],
            ['name' => 'Sajid Bulider', 'phone' => '051-8891930', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Istambol Sanitary', 'phone' => '051-8891930', 'address' => 'Dolphin Chowk, Rawalpindi'],
            ['name' => 'Takht Hazara', 'phone' => '051-8891930', 'address' => 'Overseas 5, Bahria Town'],
            ['name' => 'Waseem Plumber', 'phone' => '051-8891930', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Imperial Sanitary NPF', 'phone' => '051-8891930', 'address' => 'NPF, Islamabad'],
            ['name' => 'Nibras Corporation NPF', 'phone' => '051-8891930', 'address' => 'NPF, Islamabad'],
            ['name' => 'Universal Sanitary NPF', 'phone' => '051-8891930', 'address' => 'NPF, Islamabad'],
            ['name' => 'Eman Traders Waqas', 'phone' => '051-8891930', 'address' => 'Dolphin Chowk, Rawalpindi'],
            ['name' => 'Mughal Traders PWD', 'phone' => '051-8891930', 'address' => 'PWD, Islamabad'],
            ['name' => 'Home Solution T&T', 'phone' => '051-8891930', 'address' => 'Dolphin Chowk, Rawalpindi'],
            ['name' => 'Fida Hassan', 'phone' => '051-8891930', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'GFC Sanitary', 'phone' => '051-8891930', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'New Khyber Corporation', 'phone' => '051-8891930', 'address' => 'Hub Commercial, Bahria Town'],
            ['name' => 'Khyber Corporation', 'phone' => '051-8891930', 'address' => 'Hub Commercial, Bahria Town'],
            ['name' => 'High Land Sanitary', 'phone' => '0333-0973176', 'address' => 'Sector F-8/1, Islamabad'],
            ['name' => 'Electrician Shafiq', 'phone' => '0307-8671576', 'address' => 'Okara'],
            ['name' => 'Faisal Javed C/o Arslan', 'phone' => '0312-5371935', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Arshad Maseh Plumber & Electrician', 'phone' => '0333-5583126', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Adnan Plumber', 'phone' => '0336-8397724', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Plaza Nabeel', 'phone' => '051-8891930', 'address' => 'Linear Commercial, Bahria Town'],
            ['name' => 'Faisal Shop', 'phone' => '051-8891930', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Aftab Hotel', 'phone' => '0300-0578394', 'address' => 'Rafi Block, Rawalpindi'],
            ['name' => 'Mazhar Plumber', 'phone' => '0332-5189492', 'address' => 'Bahria Town, Rawalpindi'],
            ['name' => 'Welder Noman', 'phone' => '0334-5014274', 'address' => 'Gali Gaun, Rawalpindi'],
            ['name' => 'Rashid Plumber', 'phone' => '0302-7167208', 'address' => 'Gojra'],
            ['name' => 'Saleem Plumber', 'phone' => '0342-6871722', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Taufeeq', 'phone' => '0346-3887663', 'address' => 'Haripur'],
            ['name' => 'Adam Corp', 'phone' => '0321-5622136', 'address' => 'Hub Commercial, Bahria Town'],
            ['name' => 'M. Sheraz Plumber', 'phone' => '0346-2618669', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Allah Nawaz Electrician', 'phone' => '0314-9454504', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Ejaz Plumber Malik Riaz Masjid', 'phone' => '0334-5772307', 'address' => 'Malik Riaz Masjid, Bahria Town'],
            ['name' => 'Saeed Electrician', 'phone' => '0347-0506232', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Assetz Mobile', 'phone' => '0316-8847247', 'address' => 'Rafi Block, Rawalpindi'],
            ['name' => 'M. Azam Plumber & Electrician', 'phone' => '0307-6900723', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Ijaz Sahab', 'phone' => '051-8891930', 'address' => 'Rafi Block Mosque, Bahria Town'],
            ['name' => 'Awais Sahab', 'phone' => '0331-6553996', 'address' => 'Rafi Block, Rawalpindi'],
            ['name' => 'Zahid Electrician + Plumber', 'phone' => '0316-5645447', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'AD Plumber + Electrician', 'phone' => '0332-1653894', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Adil Iqbal Sahab', 'phone' => '0333-9045779', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Javed (Pindi)', 'phone' => '0345-5364567', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Shop', 'phone' => '051-8891930', 'address' => 'Rawalpindi / Islamabad'],
            ['name' => 'Zafar', 'phone' => '0317-5635281', 'address' => 'Bagh Rajgan, Rawalpindi'],
        ];

        foreach ($employees as $emp) {
            $existing = DB::table('employees')
                ->where('name', $emp['name'])
                ->first();

            if ($existing) {
                DB::table('employees')->where('id', $existing->id)->update([
                    'name'       => $emp['name'],
                    'phone'      => $emp['phone'] !== '051-8891930' ? $emp['phone'] : $existing->phone,
                    'address'    => $emp['address'] !== 'Rawalpindi / Islamabad' ? $emp['address'] : $existing->address,
                    'is_active'  => true,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('employees')->insert([
                    'name'       => $emp['name'],
                    'phone'      => $emp['phone'],
                    'address'    => $emp['address'],
                    'is_active'  => true,
                    'notes'      => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Keep data intact on rollback
    }
};
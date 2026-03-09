<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Categories ────────────────────────────────────────
        $categories = [
            ['name' => 'Power Tools',  'icon' => 'handyman',     'description' => 'Drills, saws, grinders and more'],
            ['name' => 'Hand Tools',   'icon' => 'construction', 'description' => 'Hammers, wrenches, screwdrivers'],
            ['name' => 'Electrical',   'icon' => 'bolt',         'description' => 'Wiring, switches, breakers'],
            ['name' => 'Plumbing',     'icon' => 'plumbing',     'description' => 'Pipes, fittings, valves'],
            ['name' => 'Paint',        'icon' => 'format_paint', 'description' => 'Paints, primers, brushes'],
            ['name' => 'Safety Gear',  'icon' => 'shield',       'description' => 'Helmets, gloves, goggles'],
            ['name' => 'Fasteners',    'icon' => 'settings',     'description' => 'Screws, bolts, nails, anchors'],
            ['name' => 'Garden',       'icon' => 'yard',         'description' => 'Hoses, tools, fertilizers'],
        ];

        $catIds = [];
        foreach ($categories as $cat) {
            // Skip if already exists
            $existing = DB::table('categories')->where('name', $cat['name'])->first();
            if ($existing) { $catIds[$cat['name']] = $existing->id; continue; }

            $id = DB::table('categories')->insertGetId([
                'name'        => $cat['name'],
                'slug'        => Str::slug($cat['name']),
                'icon'        => $cat['icon'],
                'description' => $cat['description'],
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $catIds[$cat['name']] = $id;
        }

        // ── 2. Products ──────────────────────────────────────────
        $products = [
            // Power Tools
            ['cat' => 'Power Tools', 'name' => 'DeWalt 20V Cordless Drill',      'sku' => 'DW-20V-D',    'cost' => 5500,  'sale' => 7500,  'stock' => 15, 'thresh' => 5,  'desc' => 'High performance brushless motor delivers up to 57% more run time. Includes 2 batteries and charger.'],
            ['cat' => 'Power Tools', 'name' => 'Bosch Circular Saw 7¼ inch',     'sku' => 'BS-CIR-7',    'cost' => 8000,  'sale' => 11500, 'stock' => 8,  'thresh' => 3,  'desc' => '15-Amp motor for ripping through hardwood with ease. Laser guide included for precision cuts.'],
            ['cat' => 'Power Tools', 'name' => 'Makita Random Orbital Sander',   'sku' => 'MK-ROS-5',    'cost' => 3200,  'sale' => 4800,  'stock' => 12, 'thresh' => 4,  'desc' => '3-Amp motor, 5-inch pad, variable speed control for smooth finishes on wood and metal.'],
            ['cat' => 'Power Tools', 'name' => 'Milwaukee Angle Grinder 4½"',    'sku' => 'MW-AG-45',    'cost' => 4500,  'sale' => 6200,  'stock' => 0,  'thresh' => 3,  'desc' => '7.5-Amp motor, tool-free wheel changes, slide switch with lock-on feature for continuous use.'],
            ['cat' => 'Power Tools', 'name' => 'Ryobi 18V Impact Driver Kit',    'sku' => 'RY-ID-18',    'cost' => 3800,  'sale' => 5500,  'stock' => 6,  'thresh' => 4,  'desc' => '1,800 in-lbs of torque, 3-speed settings, and LED work light for superior visibility.'],

            // Hand Tools
            ['cat' => 'Hand Tools',  'name' => 'Estwing Claw Hammer 16oz',       'sku' => 'EW-HMR-16',   'cost' => 900,   'sale' => 1500,  'stock' => 45, 'thresh' => 10, 'desc' => 'One-piece steel construction with genuine leather grip. Balanced for driving and pulling nails.'],
            ['cat' => 'Hand Tools',  'name' => 'Stanley Tape Measure 25ft',      'sku' => 'ST-TM-25',    'cost' => 500,   'sale' => 850,   'stock' => 60, 'thresh' => 15, 'desc' => 'Nylon-coated blade against wear and tear. Magnetic tip for hands-free measuring convenience.'],
            ['cat' => 'Hand Tools',  'name' => 'Craftsman Wrench Set 20pc',      'sku' => 'CM-WR-SET',   'cost' => 2500,  'sale' => 3800,  'stock' => 22, 'thresh' => 5,  'desc' => 'Full-polish chrome finish, combination of SAE and metric sizes. Meets ASME standards.'],
            ['cat' => 'Hand Tools',  'name' => 'Klein Tools Pliers Set 3pc',     'sku' => 'KL-PL-3PC',   'cost' => 1800,  'sale' => 2700,  'stock' => 3,  'thresh' => 5,  'desc' => 'Diagonal cutting, needle-nose, and slip-joint pliers. High-leverage design reduces hand fatigue.'],
            ['cat' => 'Hand Tools',  'name' => 'Husky 6-in-1 Screwdriver Set',   'sku' => 'HU-SD-6IN1',  'cost' => 350,   'sale' => 650,   'stock' => 80, 'thresh' => 20, 'desc' => 'Convert from Phillips to slotted tip instantly. Magnetic tip holds screws firmly in place.'],

            // Electrical
            ['cat' => 'Electrical',  'name' => '12/2 Romex Wire 250ft',          'sku' => 'EL-RMX-250',  'cost' => 7500,  'sale' => 10800, 'stock' => 10, 'thresh' => 4,  'desc' => 'Standard NM-B copper building wire for residential wiring. UL listed. Solid conductors included.'],
            ['cat' => 'Electrical',  'name' => 'Legrand Outlet 20A White',       'sku' => 'EL-OUT-20A',  'cost' => 280,   'sale' => 450,   'stock' => 120,'thresh' => 25, 'desc' => 'Commercial grade tamper-resistant receptacle. 20-amp, 125-volt, 2-pole 3-wire grounding.'],
            ['cat' => 'Electrical',  'name' => 'Square D 20A Circuit Breaker',   'sku' => 'EL-CB-20A',   'cost' => 650,   'sale' => 950,   'stock' => 35, 'thresh' => 10, 'desc' => 'Plug-in breaker for QO load centers. Thermal-magnetic trip mechanism for overload protection.'],
            ['cat' => 'Electrical',  'name' => 'LED Bulb 9W Warm White 10-Pack', 'sku' => 'EL-LED-9WW',  'cost' => 900,   'sale' => 1400,  'stock' => 2,  'thresh' => 10, 'desc' => '800 lumens, 2700K warm white, equivalent to 60W incandescent. E26 base. 15,000 hr rated life.'],
            ['cat' => 'Electrical',  'name' => 'Conduit Pipe ½" EMT 10ft',       'sku' => 'EL-EMT-05',   'cost' => 380,   'sale' => 580,   'stock' => 55, 'thresh' => 12, 'desc' => 'Electrical metallic tubing for protecting and routing electrical wiring in walls and ceilings.'],

            // Plumbing
            ['cat' => 'Plumbing',    'name' => 'PVC Pipe 2" Schedule 40 10ft',   'sku' => 'PL-PVC-2IN',  'cost' => 480,   'sale' => 750,   'stock' => 80, 'thresh' => 15, 'desc' => '10ft length, Schedule 40 wall thickness. For drain, waste and vent (DWV) applications.'],
            ['cat' => 'Plumbing',    'name' => 'Ball Valve ¾" Brass 2-Pack',     'sku' => 'PL-BV-75',    'cost' => 900,   'sale' => 1400,  'stock' => 40, 'thresh' => 10, 'desc' => 'Full port forged brass body. 600 WOG rated. Lead-free, suitable for potable water systems.'],
            ['cat' => 'Plumbing',    'name' => 'Moen Single Handle Kitchen Faucet','sku'=> 'PL-FAU-MO',   'cost' => 4800,  'sale' => 7200,  'stock' => 7,  'thresh' => 3,  'desc' => 'Spot resist stainless finish, Reflex system for easy movement, pull-down spray with dock.'],
            ['cat' => 'Plumbing',    'name' => 'Teflon Thread Seal Tape 3-Pack',  'sku' => 'PL-TFE-3PK', 'cost' => 220,   'sale' => 380,   'stock' => 150,'thresh' => 30, 'desc' => 'PTFE tape for sealing pipe threads. ½" width, 260" length per roll. Prevents water leaks.'],

            // Paint
            ['cat' => 'Paint',       'name' => 'Behr Premium Interior Paint 1G', 'sku' => 'PT-BHR-1G',   'cost' => 2800,  'sale' => 4200,  'stock' => 32, 'thresh' => 8,  'desc' => 'Interior satin finish, ultra pure white, 1 gallon. Excellent hide and coverage for interior walls.'],
            ['cat' => 'Paint',       'name' => 'Rust-Oleum Chalk Paint 30oz',    'sku' => 'PT-RO-30',    'cost' => 1200,  'sale' => 1800,  'stock' => 18, 'thresh' => 6,  'desc' => 'Ultra-matte finish for furniture and décor. No priming or sanding required. Dries in 30 minutes.'],
            ['cat' => 'Paint',       'name' => 'Purdy XL Paint Roller Kit',      'sku' => 'PT-RLR-KIT',  'cost' => 650,   'sale' => 980,   'stock' => 0,  'thresh' => 5,  'desc' => 'Includes 9" frame, handle, and two ¾" nap roller covers. Reduces splatter for clean results.'],
            ['cat' => 'Paint',       'name' => 'Wooster 2" Angle Sash Brush',    'sku' => 'PT-WST-2',    'cost' => 380,   'sale' => 580,   'stock' => 44, 'thresh' => 10, 'desc' => 'Synthetic blend filament for excellent paint pickup and release. Stainless steel ferrule lasts longer.'],

            // Safety Gear
            ['cat' => 'Safety Gear', 'name' => 'MSA V-Gard Hard Hat Yellow',    'sku' => 'SF-HH-YLW',   'cost' => 1200,  'sale' => 1800,  'stock' => 25, 'thresh' => 6,  'desc' => 'ANSI Z89.1 Type I Class E certified. Ratchet suspension for fast, comfortable adjustment.'],
            ['cat' => 'Safety Gear', 'name' => 'Mechanix Wear M-Pact Gloves',   'sku' => 'SF-GL-MPN',   'cost' => 850,   'sale' => 1350,  'stock' => 5,  'thresh' => 8,  'desc' => 'TPR palm padding absorbs impact. D30 knuckle protection. Machine washable, full dexterity.'],
            ['cat' => 'Safety Gear', 'name' => 'Safety Glasses Anti-Fog 3-Pack','sku' => 'SF-SG-3PK',   'cost' => 450,   'sale' => 720,   'stock' => 60, 'thresh' => 15, 'desc' => 'ANSI Z87.1 compliant. Scratch and fog resistant polycarbonate lens. Adjustable side temples.'],

            // Fasteners
            ['cat' => 'Fasteners',   'name' => 'Deck Screws 3" 5lb Box',         'sku' => 'FT-DSC-3IN', 'cost' => 900,   'sale' => 1400,  'stock' => 28, 'thresh' => 8,  'desc' => 'Coated exterior wood screws #9 x 3 in. Corrosion resistant coating. Approx 230 screws per lb.'],
            ['cat' => 'Fasteners',   'name' => 'Hex Bolt Assortment 200pc',      'sku' => 'FT-HBK-200', 'cost' => 1100,  'sale' => 1650,  'stock' => 15, 'thresh' => 5,  'desc' => 'Assorted ¼"-20 thread lengths. Grade 5 zinc plated. Includes matching nuts and flat washers.'],
            ['cat' => 'Fasteners',   'name' => 'Concrete Wedge Anchors 50pc',    'sku' => 'FT-CAB-50',  'cost' => 750,   'sale' => 1100,  'stock' => 40, 'thresh' => 10, 'desc' => 'Wedge anchors for securing structural elements to concrete. ½"×3¾". Hot-dip galvanized steel.'],

            // Garden
            ['cat' => 'Garden',      'name' => 'Flexzilla Garden Hose 50ft',     'sku' => 'GD-HSE-50',  'cost' => 3500,  'sale' => 5200,  'stock' => 18, 'thresh' => 4,  'desc' => 'Lightweight hybrid polymer hose. Remains flexible in extreme temperatures. Leak-free guarantee.'],
            ['cat' => 'Garden',      'name' => 'Corona Bypass Pruner 1" Cut',    'sku' => 'GD-PRN-BP',  'cost' => 1100,  'sale' => 1750,  'stock' => 22, 'thresh' => 5,  'desc' => 'Professional grade fully hardened, precision ground high-carbon steel blades for clean cuts.'],
        ];

        $productIds = [];
        foreach ($products as $p) {
            $existing = DB::table('products')->where('sku', $p['sku'])->first();
            if ($existing) { $productIds[$p['sku']] = $existing->id; continue; }

            $slug = \App\Models\Product::generateSlug($p['name']);
            $id = DB::table('products')->insertGetId([
                'category_id'        => $catIds[$p['cat']],
                'name'               => $p['name'],
                'slug'               => $slug,
                'sku'                => $p['sku'],
                'barcode'            => null,
                'description'        => $p['desc'],
                'unit'               => 'pc',
                'cost_price'         => $p['cost'],
                'sale_price'         => $p['sale'],
                'stock_quantity'     => $p['stock'],
                'low_stock_threshold'=> $p['thresh'],
                'image'              => null,
                'show_in_catalog'    => true,
                'is_active'          => true,
                'created_at'         => now()->subDays(rand(1, 60)),
                'updated_at'         => now(),
            ]);
            $productIds[$p['sku']] = $id;
        }

        // ── 3. Sales — 30 days of history ───────────────────────
        $adminUser = DB::table('users')->where('role', 'admin')->first()
                  ?? DB::table('users')->first();
        if (!$adminUser) {
            $this->command->warn('No users found — skipping sales seeding.');
            return;
        }

        $customers = [
            ['name' => 'Ahmed Khan',          'phone' => '0300-1234567'],
            ['name' => 'Sara Ali',            'phone' => '0321-9876543'],
            ['name' => 'Muhammad Asif',       'phone' => '0333-4567890'],
            ['name' => 'Builders Supply Co.', 'phone' => '0311-1122334'],
            ['name' => 'Walk-in Customer',    'phone' => null],
            ['name' => 'Zara Interiors',      'phone' => '0345-6677889'],
        ];
        $allSkus = array_keys($productIds);

        for ($day = 29; $day >= 0; $day--) {
            $date     = Carbon::now()->subDays($day);
            $numSales = rand(2, 7);

            for ($s = 0; $s < $numSales; $s++) {
                $customer  = $customers[array_rand($customers)];
                $method    = rand(0, 1) ? 'cash' : 'card';
                $invoiceNo = 'INV-' . $date->format('Ymd') . '-' . str_pad(($day * 10 + $s + 1), 4, '0', STR_PAD_LEFT);

                // Check if invoice already exists
                if (DB::table('sales')->where('invoice_number', $invoiceNo)->exists()) continue;

                // 1–4 random products per sale
                shuffle($allSkus);
                $pickedSkus = array_slice($allSkus, 0, rand(1, 4));

                $subtotal  = 0;
                $saleItems = [];
                foreach ($pickedSkus as $sku) {
                    $prod = DB::table('products')->find($productIds[$sku]);
                    if (!$prod) continue;
                    $qty   = rand(1, 5);
                    $price = $prod->sale_price;
                    $cost  = $prod->cost_price;
                    $total = $qty * $price;
                    $subtotal += $total;
                    $saleItems[] = [
                        'product_id'      => $prod->id,
                        'product_name'    => $prod->name,
                        'product_sku'     => $prod->sku,
                        'product_unit'    => 'pc',
                        'quantity'        => $qty,
                        'unit_price'      => $price,
                        'cost_price'      => $cost,
                        'discount_amount' => 0,
                        'total_price'     => $total,
                        'created_at'      => $date->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59)),
                        'updated_at'      => now(),
                    ];
                }

                if (empty($saleItems)) continue;

                $discount  = rand(0, 1) ? rand(50, 300) : 0;
                $total_amt = $subtotal - $discount;
                $saleTime  = $date->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59));

                $saleId = DB::table('sales')->insertGetId([
                    'invoice_number'  => $invoiceNo,
                    'user_id'         => $adminUser->id,
                    'customer_name'   => $customer['name'],
                    'customer_phone'  => $customer['phone'],
                    'subtotal'        => $subtotal,
                    'discount_amount' => $discount,
                    'tax_amount'      => 0,
                    'total_amount'    => $total_amt,
                    'paid_amount'     => $total_amt,
                    'change_amount'   => 0,
                    'payment_method'  => $method,
                    'status'          => 'completed',
                    'notes'           => null,
                    'created_at'      => $saleTime,
                    'updated_at'      => now(),
                ]);

                foreach ($saleItems as $item) {
                    $item['sale_id'] = $saleId;
                    DB::table('sale_items')->insert($item);
                }
            }
        }

        // ── 4. Purchases ─────────────────────────────────────────
        $suppliers = ['Al-Baraka Traders', 'National Hardware', 'Pak Electrical Supplies', 'City Builders Mart'];

        foreach ($suppliers as $i => $supplier) {
            $date    = Carbon::now()->subDays(rand(5, 25));
            $refNum  = 'PO-' . $date->format('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);

            if (DB::table('purchases')->where('reference_number', $refNum)->exists()) continue;

            $purchaseId = DB::table('purchases')->insertGetId([
                'reference_number' => $refNum,
                'user_id'          => $adminUser->id,
                'supplier_name'    => $supplier,
                'total_amount'     => 0,
                'received_at'      => $date->toDateString(),
                'notes'            => 'Demo purchase entry — ' . $supplier,
                'created_at'       => $date,
                'updated_at'       => now(),
            ]);

            $total = 0;
            shuffle($allSkus);
            $pickedSkus = array_slice($allSkus, 0, rand(3, 6));

            foreach ($pickedSkus as $sku) {
                $prod = DB::table('products')->find($productIds[$sku]);
                if (!$prod) continue;
                $qty  = rand(10, 50);
                $cost = $prod->cost_price;
                $sub  = $qty * $cost;
                $total += $sub;

                DB::table('purchase_items')->insert([
                    'purchase_id'  => $purchaseId,
                    'product_id'   => $prod->id,
                    'product_name' => $prod->name,
                    'product_sku'  => $prod->sku,
                    'quantity'     => $qty,
                    'unit_cost'    => $cost,
                    'total_cost'   => $sub,
                    'created_at'   => $date,
                    'updated_at'   => now(),
                ]);
            }

            DB::table('purchases')->where('id', $purchaseId)->update(['total_amount' => $total]);
        }

        $this->command->info('✅ Demo seeded: ' . count($categories) . ' categories, ' . count($products) . ' products, 30 days of sales, 4 purchases.');
    }
}

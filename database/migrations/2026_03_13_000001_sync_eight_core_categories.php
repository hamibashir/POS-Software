<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Synchronize and restrict store categories strictly to the 8 core categories.
     */
    public function up(): void
    {
        $targetCategories = [
            'sanitary' => [
                'name'        => 'Sanitary',
                'slug'        => 'sanitary',
                'icon'        => 'bi-droplet-half',
                'description' => 'Sanitary ware, pipes, fittings, valves, taps, bathroom fixtures and plumbing accessories',
            ],
            'paint' => [
                'name'        => 'Paint',
                'slug'        => 'paint',
                'icon'        => 'bi-paint-bucket',
                'description' => 'Paints, primers, brushes, rollers, varnishes, sealants and adhesives',
            ],
            'hardware' => [
                'name'        => 'Hardware',
                'slug'        => 'hardware',
                'icon'        => 'bi-tools',
                'description' => 'Fasteners, locks, hinges, bolts, screws, construction supplies and architectural hardware',
            ],
            'hand-tools' => [
                'name'        => 'Hand Tools',
                'slug'        => 'hand-tools',
                'icon'        => 'bi-hammer',
                'description' => 'Hammers, wrenches, screwdrivers, pliers, cutters, spanners and measuring tools',
            ],
            'appliances' => [
                'name'        => 'Appliances',
                'slug'        => 'appliances',
                'icon'        => 'bi-plug-fill',
                'description' => 'Home, kitchen, electrical and plumbing appliances, motors and pumps',
            ],
            'electric-tools' => [
                'name'        => 'Electric Tools',
                'slug'        => 'electric-tools',
                'icon'        => 'bi-wrench-adjustable',
                'description' => 'Drills, grinders, cutters, power saws, sanders, rotary tools and power equipment',
            ],
            'electric' => [
                'name'        => 'Electric',
                'slug'        => 'electric',
                'icon'        => 'bi-lightning-charge-fill',
                'description' => 'Wiring, switches, breakers, cables, conduit, DB boxes and distribution equipment',
            ],
            'lights' => [
                'name'        => 'Lights',
                'slug'        => 'lights',
                'icon'        => 'bi-lightbulb-fill',
                'description' => 'LED bulbs, SMD lights, panel lights, flood lights, fixtures and lamp holders',
            ],
        ];

        // 1. Create or update target categories
        $catIds = [];
        foreach ($targetCategories as $key => $data) {
            $existing = DB::table('categories')
                ->where('slug', $data['slug'])
                ->orWhere('name', $data['name'])
                ->first();

            if ($existing) {
                DB::table('categories')->where('id', $existing->id)->update([
                    'name'        => $data['name'],
                    'slug'        => $data['slug'],
                    'icon'        => $data['icon'],
                    'description' => $data['description'],
                    'is_active'   => 1,
                    'updated_at'  => now(),
                ]);
                $catIds[$key] = $existing->id;
            } else {
                $id = DB::table('categories')->insertGetId([
                    'name'        => $data['name'],
                    'slug'        => $data['slug'],
                    'icon'        => $data['icon'],
                    'description' => $data['description'],
                    'is_active'   => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $catIds[$key] = $id;
            }
        }

        // 2. Remap products to the 8 core categories
        $products = DB::table('products')->get();
        foreach ($products as $p) {
            $lname = strtolower($p->name);
            $targetCatKey = 'hardware'; // default fallback

            if (preg_match('/bulb|led|smd|light|lamp|tube|ceiling light|panel light|flood light|holder|spot light/i', $lname)) {
                $targetCatKey = 'lights';
            } elseif (preg_match('/drill|grinder|cutter machine|sander|saw electric|marble cutter|heat gun|blower|rotary hammer|welding|jigsaw|machine|electric tool/i', $lname)) {
                $targetCatKey = 'electric-tools';
            } elseif (preg_match('/switch|breaker|socket|plug|cable|wire|capacitor|dimer|bell|conduit|db box|board|tape electric|mcb|main switch|insulation|electric/i', $lname)) {
                $targetCatKey = 'electric';
            } elseif (preg_match('/fan|geyser|heater|exhaust|pump|motor|cooler|dispenser|filter|iron|kettle|stove|appliance/i', $lname)) {
                $targetCatKey = 'appliances';
            } elseif (preg_match('/paint|glue|elfy|silicone|varnish|thinner|seal|tape|bond|solution|cement|putty|brush|roller|primer|spray paint/i', $lname)) {
                $targetCatKey = 'paint';
            } elseif (preg_match('/plier|cutter|wrench|chabi|spaner|hammer|hamer|screwdriver|test pen|tape measure|hacksaw|level|trowel|allen key|pipe wrench|hand tool|saw/i', $lname)) {
                $targetCatKey = 'hand-tools';
            } elseif (preg_match('/sink|basin|waste|seat cover|flush|toilet|comode|man hole|trap|cabinet|mirror|pvc|ppr|pipe|socket|elbow|tee|yee|bend|union|end cap|reducer|u clamp|valve|cock|mixer|faucet|spindal|nozzle|bib|shower|sanitary|plumb/i', $lname)) {
                $targetCatKey = 'sanitary';
            } elseif (preg_match('/screw|bolt|nut|lock|hinges|padlock|handle|anchor|fastener|clamp|wire mesh|chain|bracket|curtain rod|rivet|washer|hardware/i', $lname)) {
                $targetCatKey = 'hardware';
            }

            DB::table('products')->where('id', $p->id)->update(['category_id' => $catIds[$targetCatKey]]);
        }

        // 3. Remove all other categories
        $validIds = array_values($catIds);
        DB::table('categories')->whereNotIn('id', $validIds)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Category synchronization does not reverse destructive deletions
    }
};

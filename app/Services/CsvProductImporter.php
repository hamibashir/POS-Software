<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CsvProductImporter
{
    /**
     * Import products from CSV content preserving exact codes as IDs.
     *
     * @param string $csvContent Full CSV content or path to file
     * @return array Summary of import results
     */
    public static function import(string $csvContent): array
    {
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $csvContent));
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $now = now();

        // Target 8 categories definition
        $categoryMap = [
            'sanitary'       => ['name' => 'Sanitary',       'slug' => 'sanitary',       'icon' => 'bi-droplet-half'],
            'paint'          => ['name' => 'Paint',          'slug' => 'paint',          'icon' => 'bi-paint-bucket'],
            'hardware'       => ['name' => 'Hardware',       'slug' => 'hardware',       'icon' => 'bi-tools'],
            'hand_tools'     => ['name' => 'Hand Tools',     'slug' => 'hand-tools',     'icon' => 'bi-hammer'],
            'appliances'     => ['name' => 'Appliances',     'slug' => 'appliances',     'icon' => 'bi-plug-fill'],
            'electric_tools' => ['name' => 'Electric Tools', 'slug' => 'electric-tools', 'icon' => 'bi-wrench-adjustable'],
            'electric'       => ['name' => 'Electric',       'slug' => 'electric',       'icon' => 'bi-lightning-charge-fill'],
            'lights'         => ['name' => 'Lights',         'slug' => 'lights',         'icon' => 'bi-lightbulb-fill'],
        ];

        $catIds = [];
        foreach ($categoryMap as $key => $data) {
            $c = DB::table('categories')->where('slug', $data['slug'])->first();
            if (!$c) {
                $catIds[$key] = DB::table('categories')->insertGetId([
                    'name'        => $data['name'],
                    'slug'        => $data['slug'],
                    'icon'        => $data['icon'],
                    'description' => $data['name'] . ' products and supplies',
                    'is_active'   => 1,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            } else {
                $catIds[$key] = $c->id;
            }
        }

        $defaultCatId = $catIds['hardware'];

        DB::beginTransaction();
        try {
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || str_contains($line, 'Product List') || str_contains($line, 'Page 1 of 1')) {
                    continue;
                }

                $row = str_getcsv($line);
                if (empty($row) || !isset($row[0])) {
                    continue;
                }

                $rawCode = trim($row[0]);
                if (!is_numeric($rawCode)) {
                    continue;
                }

                $codeInt = (int)$rawCode;
                if ($codeInt <= 0) {
                    continue;
                }

                // Find Item Name (first non-empty column after code)
                $name = '';
                $itemIndex = -1;
                for ($i = 1; $i < count($row); $i++) {
                    if (!empty(trim($row[$i]))) {
                        $name = trim($row[$i]);
                        $itemIndex = $i;
                        break;
                    }
                }

                if (empty($name)) {
                    $skipped++;
                    continue;
                }

                // Numeric columns: packing, cost, sale
                $remaining = array_slice($row, $itemIndex + 1);
                $numbers = [];
                foreach ($remaining as $v) {
                    $clean = trim($v);
                    if (is_numeric($clean)) {
                        $numbers[] = (float)$clean;
                    }
                }

                $packing = 1;
                $cost = 0.0;
                $sale = 0.0;
                if (count($numbers) >= 3) {
                    $packing = (int)$numbers[0];
                    $cost = $numbers[1];
                    $sale = $numbers[2];
                } elseif (count($numbers) === 2) {
                    $cost = $numbers[0];
                    $sale = $numbers[1];
                } elseif (count($numbers) === 1) {
                    $sale = $numbers[0];
                }

                $lname = strtolower($name);
                $isDummy = in_array($lname, ['aa', 'cc']) && $cost == 0 && $sale == 0;

                // Categorize into the 8 core categories
                $catId = $defaultCatId;
                if (preg_match('/bulb|led|smd|light|lamp|tube|ceiling light|panel light|flood light|holder|spot light/i', $lname)) {
                    $catId = $catIds['lights'];
                } elseif (preg_match('/drill|grinder|cutter machine|sander|saw electric|marble cutter|heat gun|blower|rotary hammer|welding|jigsaw|machine|electric tool/i', $lname)) {
                    $catId = $catIds['electric_tools'];
                } elseif (preg_match('/switch|breaker|socket|plug|cable|wire|capacitor|dimer|bell|conduit|db box|board|tape electric|mcb|main switch|insulation|electric/i', $lname)) {
                    $catId = $catIds['electric'];
                } elseif (preg_match('/fan|geyser|heater|exhaust|pump|motor|cooler|dispenser|filter|iron|kettle|stove|appliance/i', $lname)) {
                    $catId = $catIds['appliances'];
                } elseif (preg_match('/paint|glue|elfy|silicone|varnish|thinner|seal|tape|bond|solution|cement|putty|brush|roller|primer|spray paint/i', $lname)) {
                    $catId = $catIds['paint'];
                } elseif (preg_match('/plier|cutter|wrench|chabi|spaner|hammer|hamer|screwdriver|test pen|tape measure|hacksaw|level|trowel|allen key|pipe wrench|hand tool|saw/i', $lname)) {
                    $catId = $catIds['hand_tools'];
                } elseif (preg_match('/sink|basin|waste|seat cover|flush|toilet|comode|man hole|trap|cabinet|mirror|pvc|ppr|pipe|socket|elbow|tee|yee|bend|union|end cap|reducer|u clamp|valve|cock|mixer|faucet|spindal|nozzle|bib|shower|sanitary|plumb/i', $lname)) {
                    $catId = $catIds['sanitary'];
                } elseif (preg_match('/screw|bolt|nut|lock|hinges|padlock|handle|anchor|fastener|clamp|wire mesh|chain|bracket|curtain rod|rivet|washer|hardware/i', $lname)) {
                    $catId = $catIds['hardware'];
                }

                $sku = 'PRD-' . str_pad((string)$codeInt, 4, '0', STR_PAD_LEFT);
                $slug = Str::slug($name);
                if (empty($slug)) {
                    $slug = 'item-' . $codeInt;
                } else {
                    $slug .= '-' . $codeInt;
                }

                // Check existing product
                $existing = DB::table('products')->where('id', $codeInt)->first();
                if ($existing) {
                    DB::table('products')->where('id', $codeInt)->update([
                        'category_id'         => $catId,
                        'name'                => $name,
                        'slug'                => $slug,
                        'sku'                 => $sku,
                        'unit'                => 'pc',
                        'cost_price'          => $cost,
                        'sale_price'          => $sale,
                        'stock_quantity'      => $existing->stock_quantity > 0 ? $existing->stock_quantity : 50,
                        'low_stock_threshold' => 10,
                        'is_active'           => $isDummy ? 0 : 1,
                        'show_in_catalog'     => $isDummy ? 0 : 1,
                        'updated_at'          => $now,
                    ]);
                    $updated++;
                } else {
                    DB::table('products')->insert([
                        'id'                  => $codeInt,
                        'category_id'         => $catId,
                        'name'                => $name,
                        'slug'                => $slug,
                        'sku'                 => $sku,
                        'barcode'             => null,
                        'description'         => null,
                        'unit'                => 'pc',
                        'cost_price'          => $cost,
                        'sale_price'          => $sale,
                        'stock_quantity'      => 50,
                        'low_stock_threshold' => 10,
                        'image'               => null,
                        'show_in_catalog'     => $isDummy ? 0 : 1,
                        'is_active'           => $isDummy ? 0 : 1,
                        'created_at'          => $now,
                        'updated_at'          => $now,
                    ]);
                    $inserted++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'inserted' => $inserted,
            'updated'  => $updated,
            'skipped'  => $skipped,
            'total'    => DB::table('products')->count(),
            'max_id'   => DB::table('products')->max('id'),
            'min_id'   => DB::table('products')->min('id'),
        ];
    }
}

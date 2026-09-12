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

        // Default category
        $defaultCat = DB::table('categories')->where('slug', 'hardware-sanitary')->first();
        if (!$defaultCat) {
            $defaultCatId = DB::table('categories')->insertGetId([
                'name' => 'Hardware & Sanitary',
                'slug' => 'hardware-sanitary',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $defaultCatId = $defaultCat->id;
        }

        // Category mapping
        $categoryMap = [
            'pipes'      => ['name' => 'Pipes & Fittings', 'slug' => 'pipes-fittings'],
            'valves'     => ['name' => 'Valves & Taps', 'slug' => 'valves-taps'],
            'electrical' => ['name' => 'Electrical & Lighting', 'slug' => 'electrical-lighting'],
            'tools'      => ['name' => 'Tools & Hardware', 'slug' => 'tools-hardware'],
            'paints'     => ['name' => 'Paints & Adhesives', 'slug' => 'paints-adhesives'],
            'bathroom'   => ['name' => 'Bathroom & Sanitary', 'slug' => 'bathroom-sanitary'],
        ];

        $catIds = [];
        foreach ($categoryMap as $key => $data) {
            $c = DB::table('categories')->where('slug', $data['slug'])->first();
            if (!$c) {
                $catIds[$key] = DB::table('categories')->insertGetId([
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $catIds[$key] = $c->id;
            }
        }

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

                // Categorize
                $catId = $defaultCatId;
                if (preg_match('/pvc|ppr|pipe|socket|elbow|tee|yee|bend|union|end cap|reducer|u clamp/i', $lname)) {
                    $catId = $catIds['pipes'];
                } elseif (preg_match('/valve|cock|mixer|faucet|spindal|nozzle|bib|shower/i', $lname)) {
                    $catId = $catIds['valves'];
                } elseif (preg_match('/bulb|led|smd|light|switch|breaker|socket|holder|plug|cable|wire|capacitor|dimer|bell/i', $lname)) {
                    $catId = $catIds['electrical'];
                } elseif (preg_match('/screw|bolt|nut|plier|cutter|drill|warma|disc|grinder|spaner|chabi|wrench|lock|hinges|hammer|hamer/i', $lname)) {
                    $catId = $catIds['tools'];
                } elseif (preg_match('/paint|glue|elfy|silicone|varnish|thinner|seal|tape|bond|solution|cement|putty/i', $lname)) {
                    $catId = $catIds['paints'];
                } elseif (preg_match('/sink|basin|waste|seat cover|flush|toilet|comode|man hole|trap|cabinet|mirror/i', $lname)) {
                    $catId = $catIds['bathroom'];
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

<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseReturnService
{
    /**
     * Generate the next purchase return reference number.
     * Format: PR-YYYYMMDD-XXXX
     */
    public function generateReferenceNumber(): string
    {
        $prefix = 'PR-' . now()->format('Ymd') . '-';
        $last   = PurchaseReturn::where('reference_number', 'like', $prefix . '%')
                                ->orderByDesc('reference_number')
                                ->value('reference_number');

        $next = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Process a purchase return: validate stock, create return records, decrement stock, log movements.
     *
     * @param  array  $returnData  Supplier, refund, and purchase header data
     * @param  array  $items       [ { product_id, quantity, unit_cost, reason?, purchase_item_id? } ]
     * @param  int    $userId      User processing the return
     * @return PurchaseReturn
     * @throws \Throwable
     */
    public function create(array $returnData, array $items, int $userId): PurchaseReturn
    {
        return DB::transaction(function () use ($returnData, $items, $userId) {
            $totalReturnAmount = 0;
            $processedItems    = [];

            foreach ($items as $item) {
                $product  = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty      = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];
                $lineCost = round($unitCost * $qty, 2);

                if ($product->stock_quantity < $qty) {
                    throw new RuntimeException(
                        "Cannot return {$qty} units of '{$product->name}' — only {$product->stock_quantity} units currently in stock."
                    );
                }

                $totalReturnAmount += $lineCost;

                $processedItems[] = [
                    'product'          => $product,
                    'purchase_item_id' => $item['purchase_item_id'] ?? null,
                    'quantity'         => $qty,
                    'unit_cost'        => $unitCost,
                    'total_cost'       => $lineCost,
                    'reason'           => $item['reason'] ?? $returnData['reason'] ?? null,
                ];
            }

            $refundAmount = isset($returnData['refund_amount']) && $returnData['refund_amount'] !== ''
                ? (float) $returnData['refund_amount']
                : $totalReturnAmount;

            // Resolve supplier
            $supplierId = $returnData['supplier_id'] ?? null;
            $supplierName = $returnData['supplier_name'] ?? null;
            $supplierPhone = $returnData['supplier_phone'] ?? null;

            if ($supplierId) {
                $supplier = Supplier::find($supplierId);
                if ($supplier) {
                    $supplierName = $supplier->name;
                    $supplierPhone = $supplier->phone ?? $supplierPhone;
                }
            } elseif (!empty($supplierName)) {
                $supplier = Supplier::firstOrCreate(
                    ['name' => trim($supplierName)],
                    ['phone' => $supplierPhone]
                );
                $supplierId = $supplier->id;
            }

            // Create return record
            $purchaseReturn = PurchaseReturn::create([
                'reference_number'    => $this->generateReferenceNumber(),
                'purchase_id'         => !empty($returnData['purchase_id']) ? (int) $returnData['purchase_id'] : null,
                'supplier_id'         => $supplierId,
                'supplier_name'       => $supplierName,
                'supplier_phone'      => $supplierPhone,
                'user_id'             => $userId,
                'total_return_amount' => $totalReturnAmount,
                'refund_amount'       => $refundAmount,
                'refund_method'       => $returnData['refund_method'] ?? 'cash',
                'refund_status'       => $returnData['refund_status'] ?? 'completed',
                'returned_at'         => $returnData['returned_at'] ?? now()->toDateString(),
                'reason'              => $returnData['reason'] ?? 'Supplier Return',
                'notes'               => $returnData['notes'] ?? null,
            ]);

            // Process items, deduct stock, create audit trail
            foreach ($processedItems as $item) {
                /** @var Product $product */
                $product     = $item['product'];
                $stockBefore = $product->stock_quantity;
                $stockAfter  = $stockBefore - $item['quantity'];

                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'product_id'         => $product->id,
                    'purchase_item_id'   => $item['purchase_item_id'],
                    'product_name'       => $product->name,
                    'product_sku'        => $product->sku,
                    'product_unit'       => $product->unit ?? 'pcs',
                    'quantity'           => $item['quantity'],
                    'unit_cost'          => $item['unit_cost'],
                    'total_cost'         => $item['total_cost'],
                    'reason'             => $item['reason'],
                ]);

                // Decrement inventory
                $product->decrement('stock_quantity', $item['quantity']);

                // Audit in stock_movements
                StockMovement::create([
                    'product_id'   => $product->id,
                    'type'         => 'return',
                    'quantity'     => -$item['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockAfter,
                    'purchase_id'  => $purchaseReturn->purchase_id,
                    'user_id'      => $userId,
                    'notes'        => "Stock return {$purchaseReturn->reference_number} to {$purchaseReturn->supplier_name}" . ($item['reason'] ? " - {$item['reason']}" : ""),
                ]);
            }

            return $purchaseReturn;
        });
    }
}

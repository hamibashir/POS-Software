<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class SaleReturnService
{
    /**
     * Generate the next return voucher number.
     * Format: RET-YYYYMMDD-XXXX
     */
    public function generateReturnNumber(): string
    {
        $prefix = 'RET-' . now()->format('Ymd') . '-';
        $last   = SaleReturn::where('return_number', 'like', $prefix . '%')
            ->orderByDesc('return_number')
            ->value('return_number');

        $next = $last ? (int)substr($last, -4) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Process a customer sale return:
     * - Adds returned quantities back to inventory stock
     * - Records StockMovement audit trail (type: return)
     * - Preserves the exact sales rate item was sold at
     * - Creates SaleReturn and SaleReturnItem records
     *
     * @param array $returnData Header data (sale_id, customer_name, customer_phone, employee_id, refund_method, reason, notes)
     * @param array $items Array of { product_id, sale_item_id, quantity, unit_price, reason }
     * @param int   $cashierId Cashier processing the return
     * @return SaleReturn
     * @throws \Throwable
     */
    public function processReturn(array $returnData, array $items, int $cashierId): SaleReturn
    {
        return DB::transaction(function () use ($returnData, $items, $cashierId) {
            $sale = !empty($returnData['sale_id']) ? Sale::with('items')->find($returnData['sale_id']) : null;
            $employeeId = $returnData['employee_id'] ?? ($sale?->employee_id ?? null);

            $totalReturnAmount = 0;
            $processedItems    = [];

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty     = (int) $item['quantity'];

                if ($qty <= 0) {
                    continue;
                }

                $unitPrice = (float) $item['unit_price'];

                // If linked to a sale item, enforce max returnable quantity and exact sold rate
                $saleItem = null;
                if (!empty($item['sale_item_id'])) {
                    $saleItem = SaleItem::with('returnItems')->findOrFail($item['sale_item_id']);
                    $returnable = $saleItem->returnable_quantity;

                    if ($qty > $returnable) {
                        throw new \RuntimeException(
                            "Cannot return {$qty} units of '{$product->name}'. Maximum returnable quantity is {$returnable}."
                        );
                    }

                    // Ensure exact sales rate from original sale is used
                    $unitPrice = (float) $saleItem->unit_price;
                }

                $lineTotal = $unitPrice * $qty;
                $totalReturnAmount += $lineTotal;

                $processedItems[] = [
                    'product'      => $product,
                    'sale_item_id' => $saleItem?->id,
                    'quantity'     => $qty,
                    'unit_price'   => $unitPrice,
                    'total_price'  => $lineTotal,
                    'reason'       => $item['reason'] ?? $returnData['reason'] ?? null,
                ];
            }

            if (empty($processedItems)) {
                throw new \RuntimeException("No valid return items selected.");
            }

            $refundAmount = isset($returnData['refund_amount']) && $returnData['refund_amount'] !== ''
                ? (float) $returnData['refund_amount']
                : $totalReturnAmount;

            $customerName  = $returnData['customer_name'] ?? ($sale?->customer_name ?? 'Walk-in Customer');
            $customerPhone = $returnData['customer_phone'] ?? ($sale?->customer_phone ?? null);

            // Create return record
            $saleReturn = SaleReturn::create([
                'return_number'       => $this->generateReturnNumber(),
                'sale_id'             => $sale?->id,
                'customer_name'       => $customerName,
                'customer_phone'      => $customerPhone,
                'employee_id'         => $employeeId,
                'user_id'             => $cashierId,
                'total_return_amount' => $totalReturnAmount,
                'refund_amount'       => $refundAmount,
                'refund_method'       => $returnData['refund_method'] ?? 'cash',
                'refund_status'       => $returnData['refund_status'] ?? 'completed',
                'returned_at'         => $returnData['returned_at'] ?? now()->toDateString(),
                'reason'              => $returnData['reason'] ?? 'Customer Return',
                'notes'               => $returnData['notes'] ?? null,
            ]);

            // Add stock back and record items & stock movements
            foreach ($processedItems as $pItem) {
                /** @var Product $product */
                $product = $pItem['product'];
                $stockBefore = $product->stock_quantity;
                $stockAfter  = $stockBefore + $pItem['quantity'];

                // Create SaleReturnItem
                SaleReturnItem::create([
                    'sale_return_id' => $saleReturn->id,
                    'product_id'     => $product->id,
                    'sale_item_id'   => $pItem['sale_item_id'],
                    'product_name'   => $product->name,
                    'product_sku'    => $product->sku,
                    'product_unit'   => $product->unit,
                    'quantity'       => $pItem['quantity'],
                    'unit_price'     => $pItem['unit_price'],
                    'total_price'    => $pItem['total_price'],
                    'reason'         => $pItem['reason'],
                ]);

                // Restore stock back into inventory
                $product->increment('stock_quantity', $pItem['quantity']);

                // Record stock movement audit
                StockMovement::create([
                    'product_id'   => $product->id,
                    'type'         => 'return',
                    'quantity'     => $pItem['quantity'], // positive for incoming stock
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockAfter,
                    'sale_id'      => $sale?->id,
                    'user_id'      => $cashierId,
                    'notes'        => "Customer Return #{$saleReturn->return_number}" . ($sale ? " (Invoice #{$sale->invoice_number})" : "") . " @ Rate: PKR " . number_format($pItem['unit_price'], 2),
                ]);
            }

            return $saleReturn->load(['items', 'sale', 'employee', 'user']);
        });
    }
}

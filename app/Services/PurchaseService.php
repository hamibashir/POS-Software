<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    /**
     * Generate the next purchase reference number.
     * Format: PO-YYYYMMDD-XXXX
     */
    public function generateReferenceNumber(): string
    {
        $prefix = 'PO-' . now()->format('Ymd') . '-';
        $last   = Purchase::where('reference_number', 'like', $prefix . '%')
                          ->orderByDesc('reference_number')
                          ->value('reference_number');

        $next = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Process a purchase: create records, increase stock, log movements.
     *
     * @param  array  $purchaseData   Supplier + payment header data
     * @param  array  $items          [ { product_id, quantity, unit_cost } ]
     * @param  int    $userId         Authenticated user creating the purchase
     * @return Purchase
     * @throws \Throwable
     */
    public function create(array $purchaseData, array $items, int $userId): Purchase
    {
        return DB::transaction(function () use ($purchaseData, $items, $userId) {

            $totalAmount    = 0;
            $processedItems = [];

            // Prepare items and calculate total
            foreach ($items as $item) {
                $product   = Product::lockForUpdate()->findOrFail($item['product_id']);
                $lineCost  = round($item['unit_cost'] * $item['quantity'], 2);
                $totalAmount += $lineCost;

                $processedItems[] = [
                    'product'    => $product,
                    'quantity'   => (int) $item['quantity'],
                    'unit_cost'  => (float) $item['unit_cost'],
                    'total_cost' => $lineCost,
                ];
            }

            // Create purchase header
            $purchase = Purchase::create([
                'reference_number' => $this->generateReferenceNumber(),
                'user_id'          => $userId,
                'supplier_name'    => $purchaseData['supplier_name'],
                'supplier_phone'   => $purchaseData['supplier_phone'] ?? null,
                'total_amount'     => $totalAmount,
                'paid_amount'      => $purchaseData['paid_amount'] ?? $totalAmount,
                'payment_method'   => $purchaseData['payment_method'] ?? 'cash',
                'status'           => 'received',
                'received_at'      => $purchaseData['received_at'] ?? now(),
                'notes'            => $purchaseData['notes'] ?? null,
            ]);

            // Create line items, increase stock, log movements
            foreach ($processedItems as $item) {
                /** @var Product $product */
                $product     = $item['product'];
                $stockBefore = $product->stock_quantity;
                $stockAfter  = $stockBefore + $item['quantity'];

                // Purchase item — snaps product name/sku/unit at time of purchase
                PurchaseItem::create([
                    'purchase_id'    => $purchase->id,
                    'product_id'     => $product->id,
                    'product_name'   => $product->name,
                    'product_sku'    => $product->sku,
                    'product_unit'   => $product->unit,
                    'quantity'       => $item['quantity'],
                    'unit_cost'      => $item['unit_cost'],
                    'total_cost'     => $item['total_cost'],
                ]);

                // Increase stock
                $product->increment('stock_quantity', $item['quantity']);

                // Stock movement audit row
                StockMovement::create([
                    'product_id'   => $product->id,
                    'type'         => 'purchase',
                    'quantity'     => $item['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockAfter,
                    'purchase_id'  => $purchase->id,
                    'user_id'      => $userId,
                    'notes'        => "Purchase #{$purchase->reference_number}",
                ]);
            }

            return $purchase->load('items');
        });
    }
}

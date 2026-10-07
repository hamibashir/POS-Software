<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierPayment;
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
                    'quantity'   => (float) $item['quantity'],
                    'unit_cost'  => (float) $item['unit_cost'],
                    'total_cost' => $lineCost,
                ];
            }

            // Resolve or create supplier
            $supplierId = $purchaseData['supplier_id'] ?? null;
            $supplierName = $purchaseData['supplier_name'] ?? null;
            $supplierPhone = $purchaseData['supplier_phone'] ?? null;

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

            $paymentMethod = $purchaseData['payment_method'] ?? 'cash';
            $paidAmount = isset($purchaseData['paid_amount']) 
                ? (float) $purchaseData['paid_amount'] 
                : ($paymentMethod === 'credit' ? 0.00 : $totalAmount);

            // Create purchase header
            $purchase = Purchase::create([
                'reference_number' => $this->generateReferenceNumber(),
                'user_id'          => $userId,
                'supplier_id'      => $supplierId,
                'supplier_name'    => $supplierName,
                'supplier_phone'   => $supplierPhone,
                'total_amount'     => $totalAmount,
                'paid_amount'      => $paidAmount,
                'payment_method'   => $paymentMethod,
                'status'           => 'received',
                'received_at'      => $purchaseData['received_at'] ?? now(),
                'notes'            => $purchaseData['notes'] ?? null,
            ]);

            // If an upfront payment was made, record it in supplier_payments
            if ($paidAmount > 0 && $supplierId) {
                SupplierPayment::create([
                    'supplier_id'      => $supplierId,
                    'purchase_id'      => $purchase->id,
                    'amount'           => $paidAmount,
                    'payment_method'   => $paymentMethod,
                    'payment_date'     => $purchaseData['received_at'] ?? now(),
                    'user_id'          => $userId,
                    'reference_number' => $purchase->reference_number,
                    'notes'            => "Payment recorded on purchase {$purchase->reference_number}",
                ]);
            }

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

    /**
     * Update an existing purchase, reconcile line items and stock movements.
     */
    public function update(Purchase $purchase, array $purchaseData, array $items, int $userId): Purchase
    {
        return DB::transaction(function () use ($purchase, $purchaseData, $items, $userId) {
            $totalAmount    = 0;
            $processedItems = [];

            // 1. Process new items
            foreach ($items as $item) {
                $product  = Product::lockForUpdate()->findOrFail($item['product_id']);
                $lineCost = round($item['unit_cost'] * $item['quantity'], 2);
                $totalAmount += $lineCost;

                $processedItems[] = [
                    'product'    => $product,
                    'product_id' => $product->id,
                    'quantity'   => (float) $item['quantity'],
                    'unit_cost'  => (float) $item['unit_cost'],
                    'total_cost' => $lineCost,
                ];
            }

            // 2. Reconcile existing purchase items & inventory stock
            $oldItems = $purchase->items()->get()->keyBy('product_id');
            $newProductIds = collect($processedItems)->pluck('product_id')->toArray();

            // Handle removed products: revert stock added previously
            foreach ($oldItems as $productId => $oldItem) {
                if (!in_array($productId, $newProductIds)) {
                    $product = Product::lockForUpdate()->find($productId);
                    if ($product) {
                        $stockBefore = $product->stock_quantity;
                        $product->decrement('stock_quantity', $oldItem->quantity);
                        $stockAfter = $product->stock_quantity;

                        StockMovement::create([
                            'product_id'   => $product->id,
                            'type'         => 'adjustment',
                            'quantity'     => -$oldItem->quantity,
                            'stock_before' => $stockBefore,
                            'stock_after'  => $stockAfter,
                            'purchase_id'  => $purchase->id,
                            'user_id'      => $userId,
                            'notes'        => "Purchase #{$purchase->reference_number} edited: Item removed",
                        ]);
                    }
                }
            }

            // Handle updated & added products
            foreach ($processedItems as $newItem) {
                /** @var Product $product */
                $product = $newItem['product'];
                $oldItem = $oldItems->get($product->id);
                $oldQty  = $oldItem ? (float) $oldItem->quantity : 0.0;
                $newQty  = (float) $newItem['quantity'];
                $qtyDiff = $newQty - $oldQty;

                if ($qtyDiff != 0) {
                    $stockBefore = $product->stock_quantity;
                    if ($qtyDiff > 0) {
                        $product->increment('stock_quantity', $qtyDiff);
                    } else {
                        $product->decrement('stock_quantity', abs($qtyDiff));
                    }
                    $stockAfter = $product->stock_quantity;

                    StockMovement::create([
                        'product_id'   => $product->id,
                        'type'         => $qtyDiff > 0 ? 'purchase' : 'adjustment',
                        'quantity'     => $qtyDiff,
                        'stock_before' => $stockBefore,
                        'stock_after'  => $stockAfter,
                        'purchase_id'  => $purchase->id,
                        'user_id'      => $userId,
                        'notes'        => "Purchase #{$purchase->reference_number} edited: Qty changed from {$oldQty} to {$newQty}",
                    ]);
                }
            }

            // Replace purchase_items
            $purchase->items()->delete();
            foreach ($processedItems as $item) {
                PurchaseItem::create([
                    'purchase_id'    => $purchase->id,
                    'product_id'     => $item['product']->id,
                    'product_name'   => $item['product']->name,
                    'product_sku'    => $item['product']->sku,
                    'product_unit'   => $item['product']->unit,
                    'quantity'       => $item['quantity'],
                    'unit_cost'      => $item['unit_cost'],
                    'total_cost'     => $item['total_cost'],
                ]);
            }

            // Resolve supplier
            $supplierId    = $purchaseData['supplier_id'] ?? null;
            $supplierName  = $purchaseData['supplier_name'] ?? null;
            $supplierPhone = $purchaseData['supplier_phone'] ?? null;

            if ($supplierId) {
                $supplier = Supplier::find($supplierId);
                if ($supplier) {
                    $supplierName  = $supplier->name;
                    $supplierPhone = $supplier->phone ?? $supplierPhone;
                }
            } elseif (!empty($supplierName)) {
                $supplier = Supplier::firstOrCreate(
                    ['name' => trim($supplierName)],
                    ['phone' => $supplierPhone]
                );
                $supplierId = $supplier->id;
            }

            $paymentMethod = $purchaseData['payment_method'] ?? $purchase->payment_method;
            $paidAmount    = isset($purchaseData['paid_amount']) 
                ? (float) $purchaseData['paid_amount'] 
                : ($paymentMethod === 'credit' ? 0.00 : $totalAmount);

            // Update purchase header
            $purchase->update([
                'supplier_id'    => $supplierId,
                'supplier_name'  => $supplierName,
                'supplier_phone' => $supplierPhone,
                'total_amount'   => $totalAmount,
                'paid_amount'    => $paidAmount,
                'payment_method' => $paymentMethod,
                'received_at'    => $purchaseData['received_at'] ?? $purchase->received_at,
                'notes'          => $purchaseData['notes'] ?? null,
            ]);

            // If an upfront payment exists for this purchase, sync it
            $initialPayment = $purchase->payments()->first();
            if ($paidAmount > 0 && $supplierId) {
                if ($initialPayment) {
                    $initialPayment->update([
                        'supplier_id'    => $supplierId,
                        'amount'         => $paidAmount,
                        'payment_method' => $paymentMethod,
                        'payment_date'   => $purchaseData['received_at'] ?? $initialPayment->payment_date,
                        'notes'          => "Payment updated on purchase {$purchase->reference_number}",
                    ]);
                } else {
                    SupplierPayment::create([
                        'supplier_id'      => $supplierId,
                        'purchase_id'      => $purchase->id,
                        'amount'           => $paidAmount,
                        'payment_method'   => $paymentMethod,
                        'payment_date'     => $purchaseData['received_at'] ?? now(),
                        'user_id'          => $userId,
                        'reference_number' => $purchase->reference_number,
                        'notes'            => "Payment recorded on purchase {$purchase->reference_number}",
                    ]);
                }
            } elseif ($paidAmount <= 0 && $initialPayment) {
                $initialPayment->delete();
            }

            return $purchase->fresh(['items', 'supplier', 'payments']);
        });
    }

    /**
     * Delete / cancel a purchase order and reverse inventory stock.
     */
    public function delete(Purchase $purchase, int $userId): void
    {
        DB::transaction(function () use ($purchase, $userId) {
            // Revert stock for all items
            foreach ($purchase->items as $item) {
                $product = Product::lockForUpdate()->find($item->product_id);
                if ($product) {
                    $stockBefore = $product->stock_quantity;
                    $product->decrement('stock_quantity', $item->quantity);
                    $stockAfter = $product->stock_quantity;

                    StockMovement::create([
                        'product_id'   => $product->id,
                        'type'         => 'adjustment',
                        'quantity'     => -$item->quantity,
                        'stock_before' => $stockBefore,
                        'stock_after'  => $stockAfter,
                        'purchase_id'  => $purchase->id,
                        'user_id'      => $userId,
                        'notes'        => "Purchase #{$purchase->reference_number} deleted / cancelled",
                    ]);
                }
            }

            // Delete associated supplier payments
            $purchase->payments()->delete();

            // Delete items and purchase
            $purchase->items()->delete();
            $purchase->delete();
        });
    }
}

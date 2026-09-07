<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class SaleService
{
    /**
     * Generate the next invoice number.
     * Format: INV-YYYYMMDD-XXXX
     */
    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';
        $last   = Sale::where('invoice_number', 'like', $prefix . '%')
                      ->orderByDesc('invoice_number')
                      ->value('invoice_number');

        $next = $last ? (int)substr($last, -4) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Process and record a completed sale.
     *
     * @param array $saleData    Sale header data
     * @param array $cartItems   Array of { product_id, quantity, unit_price, discount_amount }
     * @param int   $cashierId   User ID of cashier
     * @return Sale
     * @throws \Throwable
     */
    public function complete(array $saleData, array $cartItems, int $cashierId): Sale
    {
        return DB::transaction(function () use ($saleData, $cartItems, $cashierId) {

            $subtotal       = 0;
            $totalDiscount  = $saleData['discount_amount'] ?? 0;
            $processedItems = [];

            // Validate stock and prepare items
            foreach ($cartItems as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if ($product->stock_quantity < $item['quantity']) {
                    throw new \RuntimeException(
                        "Insufficient stock for \"{$product->name}\". Available: {$product->stock_quantity}"
                    );
                }

                $lineTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount_amount'] ?? 0);
                $subtotal += $lineTotal;

                $processedItems[] = [
                    'product'         => $product,
                    'quantity'        => $item['quantity'],
                    'unit_price'      => $item['unit_price'],
                    'cost_price'      => $product->cost_price,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'total_price'     => $lineTotal,
                ];
            }

            $taxAmount   = $saleData['tax_amount']   ?? 0;
            $totalAmount = $subtotal - $totalDiscount + $taxAmount;
            $paidAmount  = $saleData['paid_amount']  ?? $totalAmount;
            $change      = max(0, $paidAmount - $totalAmount);

            // Create the sale
            $sale = Sale::create([
                'invoice_number'  => $this->generateInvoiceNumber(),
                'user_id'         => $cashierId,
                'employee_id'     => $saleData['employee_id'] ?? null,
                'customer_name'   => $saleData['customer_name'] ?? 'Walk-in Customer',
                'customer_phone'  => $saleData['customer_phone'] ?? null,
                'subtotal'        => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount'      => $taxAmount,
                'total_amount'    => $totalAmount,
                'paid_amount'     => $paidAmount,
                'change_amount'   => $change,
                'payment_method'  => $saleData['payment_method'] ?? 'cash',
                'status'          => 'completed',
                'notes'           => $saleData['notes'] ?? null,
            ]);

            // Create line items and deduct stock
            foreach ($processedItems as $item) {
                /** @var Product $product */
                $product = $item['product'];
                $stockBefore = $product->stock_quantity;
                $stockAfter  = $stockBefore - $item['quantity'];

                // Sale item
                SaleItem::create([
                    'sale_id'         => $sale->id,
                    'product_id'      => $product->id,
                    'product_name'    => $product->name,
                    'product_sku'     => $product->sku,
                    'product_unit'    => $product->unit,
                    'quantity'        => $item['quantity'],
                    'unit_price'      => $item['unit_price'],
                    'cost_price'      => $item['cost_price'],
                    'discount_amount' => $item['discount_amount'],
                    'total_price'     => $item['total_price'],
                ]);

                // Deduct stock
                $product->decrement('stock_quantity', $item['quantity']);

                // Stock movement audit
                StockMovement::create([
                    'product_id'   => $product->id,
                    'type'         => 'sale',
                    'quantity'     => -$item['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockAfter,
                    'sale_id'      => $sale->id,
                    'user_id'      => $cashierId,
                    'notes'        => "Sale #{$sale->invoice_number}",
                ]);
            }

            return $sale->load('items');
        });
    }
}

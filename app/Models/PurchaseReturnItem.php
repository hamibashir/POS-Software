<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReturnItem extends Model
{
    protected $fillable = [
        'purchase_return_id',
        'product_id',
        'purchase_item_id',
        'product_name',
        'product_sku',
        'product_unit',
        'quantity',
        'unit_cost',
        'total_cost',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity'   => 'integer',
            'unit_cost'  => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'product_sku',
        'product_unit',
        'quantity',
        'unit_price',
        'cost_price',
        'discount_amount',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity'        => 'decimal:3',
            'unit_price'      => 'decimal:2',
            'cost_price'      => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_price'     => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function returnItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function getReturnedQuantityAttribute(): float
    {
        return (float) $this->returnItems()->sum('quantity');
    }

    public function getReturnableQuantityAttribute(): float
    {
        return max(0.0, (float) $this->quantity - (float) $this->returned_quantity);
    }
}

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

    public function getReturnedQuantityAttribute(): int
    {
        return (int) $this->returnItems()->sum('quantity');
    }

    public function getReturnableQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->returned_quantity);
    }
}

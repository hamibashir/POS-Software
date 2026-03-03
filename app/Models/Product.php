<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'barcode',
        'description',
        'unit',
        'cost_price',
        'sale_price',
        'stock_quantity',
        'low_stock_threshold',
        'image',
        'show_in_catalog',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_price'          => 'decimal:2',
            'sale_price'          => 'decimal:2',
            'stock_quantity'      => 'integer',
            'low_stock_threshold' => 'integer',
            'show_in_catalog'     => 'boolean',
            'is_active'           => 'boolean',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                     ->where('stock_quantity', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', 0);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold && $this->stock_quantity > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock())  return 'out_of_stock';
        if ($this->isLowStock())    return 'low_stock';
        return 'in_stock';
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'Out of Stock',
            'low_stock'    => 'Low Stock',
            default        => 'In Stock',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'supplier_id',
        'name',
        'slug',
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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get the supplier name (assigned supplier or from purchase history).
     */
    public function getSupplierNameAttribute(): ?string
    {
        if ($this->supplier) {
            return $this->supplier->name;
        }

        $latestPurchase = Purchase::whereHas('items', fn($q) => $q->where('product_id', $this->id))
            ->with('supplier:id,name')
            ->latest()
            ->first();

        return $latestPurchase?->supplier?->name ?? $latestPurchase?->supplier_name;
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInCatalog($query)
    {
        return $query->where('is_active', true)->where('show_in_catalog', true);
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

    /**
     * Generate a unique slug from a given name.
     */
    public static function generateSlug(string $name, ?int $ignoreId = null): string
    {
        $slug     = Str::slug($name);
        $original = $slug;
        $count    = 1;

        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }

    /**
     * Image URL accessor — returns a default placeholder when no image stored.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image && file_exists(storage_path('app/public/' . $this->image))) {
            return asset('storage/' . $this->image);
        }
        return asset('images/no-image.png');
    }
}

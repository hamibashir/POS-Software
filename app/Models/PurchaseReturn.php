<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'reference_number',
        'purchase_id',
        'supplier_id',
        'supplier_name',
        'supplier_phone',
        'user_id',
        'total_return_amount',
        'refund_amount',
        'refund_method',
        'refund_status',
        'returned_at',
        'reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_return_amount' => 'decimal:2',
            'refund_amount'       => 'decimal:2',
            'returned_at'         => 'date',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }
}

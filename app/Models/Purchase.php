<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'reference_number',
        'user_id',
        'supplier_name',
        'supplier_phone',
        'total_amount',
        'paid_amount',
        'payment_method',
        'status',
        'received_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'paid_amount'  => 'decimal:2',
            'received_at'  => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function getTotalReturnedAmountAttribute(): float
    {
        return (float) $this->returns()->sum('total_return_amount');
    }

    public function getNetTotalAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - $this->total_returned_amount);
    }
}

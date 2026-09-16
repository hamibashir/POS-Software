<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'invoice_number',
        'user_id',
        'employee_id',
        'customer_name',
        'customer_phone',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'total_amount'    => 'decimal:2',
            'paid_amount'     => 'decimal:2',
            'change_amount'   => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function getTotalReturnedAmountAttribute(): float
    {
        return (float) $this->returns()->sum('total_return_amount');
    }

    /**
     * Get computed status for display (checks if credit sale is still unpaid/pending).
     */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === 'voided') {
            return 'voided';
        }

        if ($this->payment_method === 'credit') {
            if ($this->status === 'pending' || (float)$this->paid_amount <= 0) {
                return 'pending';
            }
            if ((float)$this->paid_amount < (float)$this->total_amount) {
                return 'pending';
            }
        }

        return $this->status ?? 'completed';
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeValid($query)
    {
        return $query->where('status', '!=', 'voided');
    }
}

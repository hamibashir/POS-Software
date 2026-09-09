<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'phone',
        'email',
        'address',
        'opening_balance',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'is_active'       => 'boolean',
        ];
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class)->latest('payment_date');
    }

    /**
     * Total amount of purchases recorded for this supplier.
     */
    public function getTotalPurchasesAttribute(): float
    {
        return (float) $this->purchases()->sum('total_amount');
    }

    /**
     * Total payments cleared for this supplier.
     */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    /**
     * Total refund/credit amount from purchase returns.
     */
    public function getTotalReturnsAttribute(): float
    {
        return (float) $this->returns()->sum('total_return_amount');
    }

    /**
     * Current net pending balance payable to the supplier.
     * (Opening balance + Total Purchases) - (Total Payments + Total Returns)
     */
    public function getPendingBalanceAttribute(): float
    {
        $totalDebt = (float) $this->opening_balance + $this->total_purchases;
        $totalCleared = $this->total_paid + $this->total_returns;
        return max(0, round($totalDebt - $totalCleared, 2));
    }
}

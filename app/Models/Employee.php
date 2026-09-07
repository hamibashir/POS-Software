<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(EmployeePayment::class);
    }

    /**
     * Total amount of items taken on credit.
     */
    public function getTotalCreditAttribute(): float
    {
        return (float) $this->sales()
            ->where('payment_method', 'credit')
            ->where('status', 'completed')
            ->sum('total_amount');
    }

    /**
     * Total payments cleared by the employee.
     */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    /**
     * Current pending payment that needs to be cleared.
     */
    public function getPendingPaymentAttribute(): float
    {
        return max(0, $this->total_credit - $this->total_paid);
    }
}

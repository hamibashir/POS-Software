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

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(EmployeePayment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    /**
     * Total amount of items returned on credit.
     */
    public function getTotalReturnedAttribute(): float
    {
        return (float) $this->returns()->sum('total_return_amount');
    }

    /**
     * Total amount of credit taken (Opening Balance + Credit Sales - Returns).
     */
    public function getTotalCreditAttribute(): float
    {
        $grossCredit = (float) $this->opening_balance + (float) $this->sales()
            ->where('payment_method', 'credit')
            ->where('status', '!=', 'voided')
            ->sum('total_amount');

        return max(0, $grossCredit - $this->total_returned);
    }

    /**
     * Total payments cleared by the employee / customer.
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
        return max(0, round($this->total_credit - $this->total_paid, 2));
    }

    /**
     * Reconcile all credit sales and payments for this employee (FIFO).
     */
    public function reconcileCreditSales(): void
    {
        $creditSales = $this->sales()
            ->where('payment_method', 'credit')
            ->where('status', '!=', 'voided')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $totalPayments = (float) $this->payments()->sum('amount');
        $remainingPayment = $totalPayments;

        foreach ($creditSales as $sale) {
            $total = (float) $sale->total_amount;
            $upfront = (float) ($sale->paid_amount ?? 0);
            $dueOnInvoice = max(0, $total - $upfront);

            if ($dueOnInvoice <= 0) {
                if ($sale->status !== 'completed') {
                    $sale->update(['status' => 'completed']);
                }
            } elseif ($remainingPayment >= $dueOnInvoice) {
                if ($sale->status !== 'completed') {
                    $sale->update(['status' => 'completed']);
                }
                $remainingPayment -= $dueOnInvoice;
            } else {
                if ($sale->status !== 'pending') {
                    $sale->update(['status' => 'pending']);
                }
                $remainingPayment = 0;
            }
        }
    }
}
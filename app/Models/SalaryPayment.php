<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month_year',
        'base_salary',
        'total_days',
        'present_days',
        'absent_days',
        'allowed_leaves',
        'excess_absences',
        'deduction_amount',
        'bonus_amount',
        'net_payable',
        'payment_date',
        'payment_method',
        'status',
        'notes',
        'paid_by',
    ];

    protected function casts(): array
    {
        return [
            'base_salary'      => 'decimal:2',
            'present_days'     => 'decimal:1',
            'absent_days'      => 'decimal:1',
            'allowed_leaves'   => 'integer',
            'excess_absences'  => 'decimal:1',
            'deduction_amount' => 'decimal:2',
            'bonus_amount'     => 'decimal:2',
            'net_payable'      => 'decimal:2',
            'payment_date'     => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}

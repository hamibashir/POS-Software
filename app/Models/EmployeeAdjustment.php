<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAdjustment extends Model
{
    protected $fillable = [
        'employee_id',
        'user_id',
        'previous_due',
        'new_due',
        'amount',
        'reason',
        'affect_sales',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'previous_due' => 'decimal:2',
            'new_due'      => 'decimal:2',
            'amount'       => 'decimal:2',
            'affect_sales' => 'boolean',
            'is_active'    => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

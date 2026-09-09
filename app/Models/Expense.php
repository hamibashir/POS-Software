<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'amount',
        'expense_date',
        'payment_method',
        'reference_no',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public const CATEGORIES = [
        'Utilities'              => ['icon' => 'bi-lightning-charge', 'color' => '#f59e0b', 'bg' => '#fef3c7'],
        'Tea & Refreshments'     => ['icon' => 'bi-cup-hot',          'color' => '#8b5cf6', 'bg' => '#ede9fe'],
        'Fuel & Generator'       => ['icon' => 'bi-fuel-pump',        'color' => '#ef4444', 'bg' => '#fee2e2'],
        'Daily Wages & Labor'    => ['icon' => 'bi-person-badge',     'color' => '#0284c7', 'bg' => '#e0f2fe'],
        'Store Maintenance'      => ['icon' => 'bi-tools',            'color' => '#0d9488', 'bg' => '#ccfbf1'],
        'Shop Supplies & Bags'   => ['icon' => 'bi-bag',              'color' => '#64748b', 'bg' => '#f1f5f9'],
        'Transport & Freight'    => ['icon' => 'bi-truck',            'color' => '#ea580c', 'bg' => '#ffedd5'],
        'Shop Rent'              => ['icon' => 'bi-building',         'color' => '#15803d', 'bg' => '#dcfce7'],
        'Internet & Telephone'   => ['icon' => 'bi-wifi',             'color' => '#3b82f6', 'bg' => '#dbeafe'],
        'Miscellaneous'          => ['icon' => 'bi-three-dots',       'color' => '#475569', 'bg' => '#f1f5f9'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('expense_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('expense_date', now()->month)
                     ->whereYear('expense_date', now()->year);
    }
}

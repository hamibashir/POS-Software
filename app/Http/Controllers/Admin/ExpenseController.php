<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $preset   = $request->get('preset', 'this_month');
        $from     = $request->get('from');
        $to       = $request->get('to');
        $category = $request->get('category');
        $search   = $request->get('search');

        $now = Carbon::now();

        // Determine date bounds based on preset if custom range not explicitly given
        if ($preset === 'today') {
            $from = $now->toDateString();
            $to   = $now->toDateString();
        } elseif ($preset === 'yesterday') {
            $from = $now->copy()->subDay()->toDateString();
            $to   = $now->copy()->subDay()->toDateString();
        } elseif ($preset === 'this_week') {
            $from = $now->copy()->startOfWeek()->toDateString();
            $to   = $now->copy()->endOfWeek()->toDateString();
        } elseif ($preset === 'this_month') {
            $from = $now->copy()->startOfMonth()->toDateString();
            $to   = $now->copy()->endOfMonth()->toDateString();
        } elseif ($preset === 'all') {
            $from = null;
            $to   = null;
        } else {
            // custom preset default fallback
            if (!$from && !$to) {
                $from = $now->copy()->startOfMonth()->toDateString();
                $to   = $now->copy()->endOfMonth()->toDateString();
                $preset = 'this_month';
            } else {
                $preset = 'custom';
            }
        }

        // Query builder for filtered list
        $query = Expense::with('user');

        if ($from && $to) {
            $query->whereBetween('expense_date', [$from, $to]);
        } elseif ($from) {
            $query->where('expense_date', '>=', $from);
        } elseif ($to) {
            $query->where('expense_date', '<=', $to);
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $expenses = $query->orderByDesc('expense_date')
                          ->orderByDesc('id')
                          ->paginate(20)
                          ->withQueryString();

        // ── KPIs ──────────────────────────────────────────
        $todayExpenses = Expense::today()->sum('amount');
        $thisMonthExpenses = Expense::thisMonth()->sum('amount');
        $filteredTotal = (clone $query)->sum('amount');

        // Category breakdown in selected period
        $categoryBreakdown = Expense::when($from && $to, fn($q) => $q->whereBetween('expense_date', [$from, $to]))
            ->when(!$from && $to, fn($q) => $q->where('expense_date', '<=', $to))
            ->when($from && !$to, fn($q) => $q->where('expense_date', '>=', $from))
            ->selectRaw('category, SUM(amount) as total_amount, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('total_amount')
            ->get();

        $categories = Expense::CATEGORIES;

        return view('admin.expenses.index', compact(
            'expenses',
            'todayExpenses',
            'thisMonthExpenses',
            'filteredTotal',
            'categoryBreakdown',
            'categories',
            'preset',
            'from',
            'to',
            'category',
            'search'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'category'       => ['required', 'string', 'max:100'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'expense_date'   => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:50'],
            'reference_no'   => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['created_by'] = auth()->id();

        Expense::create($validated);

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Daily expenditure of Rs. ' . number_format($validated['amount'], 2) . ' recorded successfully.');
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'category'       => ['required', 'string', 'max:100'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'expense_date'   => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:50'],
            'reference_no'   => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        $expense->update($validated);

        return redirect()->back()
            ->with('success', 'Expenditure record updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->back()
            ->with('success', 'Expenditure record deleted successfully.');
    }
}

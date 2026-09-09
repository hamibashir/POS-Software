@extends('layouts.admin')

@section('title', 'Daily Expenditures')

@push('styles')
<style>
    .kpi-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .kpi-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .kpi-label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        margin-bottom: 2px;
    }
    .kpi-value {
        font-size: 22px;
        font-weight: 800;
        color: #111827;
        line-height: 1.2;
    }
    .kpi-sub {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 2px;
    }

    .cat-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 8px;
        white-space: nowrap;
    }

    .preset-pill {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        color: #4b5563;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        transition: all 0.15s;
        display: inline-flex;
        align-items: center;
    }
    .preset-pill:hover {
        background: #e5e7eb;
        color: #111827;
    }
    .preset-pill.active {
        background: var(--pos-primary);
        color: #ffffff;
        border-color: var(--pos-primary);
    }

    .progress-track {
        height: 8px;
        border-radius: 4px;
        background: #f3f4f6;
        overflow: hidden;
        margin-top: 6px;
    }
    .progress-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
    }
</style>
@endpush

@section('content')

{{-- ── Page Hero ─────────────────────────────────── --}}
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1><i class="bi bi-wallet2 text-teal me-2" style="color:var(--pos-primary);"></i>Daily Expenditures</h1>
        <p>Record, categorize, and track all store daily expenses, utility bills, refreshments, and operational costs.</p>
    </div>
    <div>
        <button type="button" class="btn-pos" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="bi bi-plus-circle me-1"></i>Record New Expense
        </button>
    </div>
</div>

{{-- ── Flash Messages ────────────────────────────── --}}
@if(session('success'))
    <div class="pos-alert pos-alert-success mb-4">
        <i class="bi bi-check-circle-fill text-success fs-5"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

@if($errors->any())
    <div class="pos-alert pos-alert-error mb-4">
        <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
        <div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- ── KPI Cards ─────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="kpi-card">
            <div class="kpi-icon-box" style="background:#ccfbf1; color:#0f766e;">
                <i class="bi bi-calendar-event"></i>
            </div>
            <div>
                <div class="kpi-label">Today's Expenses</div>
                <div class="kpi-value">Rs. {{ number_format($todayExpenses, 2) }}</div>
                <div class="kpi-sub">{{ now()->format('D, d M Y') }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card">
            <div class="kpi-icon-box" style="background:#e0f2fe; color:#0284c7;">
                <i class="bi bi-calendar-month"></i>
            </div>
            <div>
                <div class="kpi-label">This Month ({{ now()->format('F') }})</div>
                <div class="kpi-value">Rs. {{ number_format($thisMonthExpenses, 2) }}</div>
                <div class="kpi-sub">Total monthly spend</div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card">
            <div class="kpi-icon-box" style="background:#ede9fe; color:#7c3aed;">
                <i class="bi bi-funnel"></i>
            </div>
            <div>
                <div class="kpi-label">Filtered Period Total</div>
                <div class="kpi-value">Rs. {{ number_format($filteredTotal, 2) }}</div>
                <div class="kpi-sub">{{ $expenses->total() }} recorded entries</div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="kpi-card">
            <div class="kpi-icon-box" style="background:#fef3c7; color:#d97706;">
                <i class="bi bi-pie-chart"></i>
            </div>
            <div>
                <div class="kpi-label">Top Expense Category</div>
                <div class="kpi-value" style="font-size:16px; margin-top:3px;">
                    {{ $categoryBreakdown->first()->category ?? 'None' }}
                </div>
                <div class="kpi-sub">
                    @if($categoryBreakdown->first())
                        Rs. {{ number_format($categoryBreakdown->first()->total_amount, 2) }}
                    @else
                        No data in period
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Filter Section ────────────────────────────── --}}
<div class="pos-card p-4 mb-4">
    {{-- Quick Presets --}}
    <div class="d-flex align-items-center gap-2 flex-wrap mb-3 pb-3 border-bottom">
        <span class="text-muted fw-bold small text-uppercase me-2"><i class="bi bi-clock-history me-1"></i>Quick Date:</span>
        <a href="{{ route('admin.expenses.index', array_merge(request()->except(['page', 'from', 'to']), ['preset' => 'today'])) }}"
           class="preset-pill {{ $preset === 'today' ? 'active' : '' }}">Today</a>
        <a href="{{ route('admin.expenses.index', array_merge(request()->except(['page', 'from', 'to']), ['preset' => 'yesterday'])) }}"
           class="preset-pill {{ $preset === 'yesterday' ? 'active' : '' }}">Yesterday</a>
        <a href="{{ route('admin.expenses.index', array_merge(request()->except(['page', 'from', 'to']), ['preset' => 'this_week'])) }}"
           class="preset-pill {{ $preset === 'this_week' ? 'active' : '' }}">This Week</a>
        <a href="{{ route('admin.expenses.index', array_merge(request()->except(['page', 'from', 'to']), ['preset' => 'this_month'])) }}"
           class="preset-pill {{ $preset === 'this_month' ? 'active' : '' }}">This Month</a>
        <a href="{{ route('admin.expenses.index', array_merge(request()->except(['page', 'from', 'to']), ['preset' => 'all'])) }}"
           class="preset-pill {{ $preset === 'all' ? 'active' : '' }}">All Time</a>
    </div>

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('admin.expenses.index') }}" class="row g-3 align-items-end">
        <input type="hidden" name="preset" value="custom">

        <div class="col-md-3 col-sm-6">
            <label class="form-label small fw-bold text-muted mb-1">From Date</label>
            <input type="date" name="from" value="{{ $from }}" class="pos-input">
        </div>

        <div class="col-md-3 col-sm-6">
            <label class="form-label small fw-bold text-muted mb-1">To Date</label>
            <input type="date" name="to" value="{{ $to }}" class="pos-input">
        </div>

        <div class="col-md-3 col-sm-6">
            <label class="form-label small fw-bold text-muted mb-1">Category</label>
            <select name="category" class="pos-input">
                <option value="">All Categories</option>
                @foreach($categories as $catName => $catMeta)
                    <option value="{{ $catName }}" {{ $category === $catName ? 'selected' : '' }}>
                        {{ $catName }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 col-sm-6">
            <label class="form-label small fw-bold text-muted mb-1">Search Details / Ref</label>
            <input type="text" name="search" value="{{ $search }}" class="pos-input" placeholder="e.g. Electric bill, Tea...">
        </div>

        <div class="col-12 d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.expenses.index') }}" class="btn-pos-outline">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
            </a>
            <button type="submit" class="btn-pos">
                <i class="bi bi-filter me-1"></i>Apply Filters
            </button>
        </div>
    </form>
</div>

{{-- ── Main Layout: Table + Category Breakdown ────── --}}
<div class="row g-4">

    {{-- Expenditures Table --}}
    <div class="col-lg-8">
        <div class="pos-card overflow-hidden">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
                <div class="fw-bold" style="color:#111827;">
                    <i class="bi bi-list-check me-1 text-teal" style="color:var(--pos-primary);"></i>
                    Expense Records ({{ $expenses->total() }})
                </div>
                <div class="fw-bold text-muted small">
                    Total: <span style="color:var(--pos-primary); font-size:15px;">Rs. {{ number_format($filteredTotal, 2) }}</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="pos-table w-100 mb-0">
                    <thead>
                        <tr>
                            <th style="width:110px;">Date</th>
                            <th>Expense Title & Ref</th>
                            <th>Category</th>
                            <th>Method</th>
                            <th style="text-align:right;">Amount (Rs.)</th>
                            <th style="width:90px; text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $exp)
                            @php
                                $catConfig = $categories[$exp->category] ?? ['icon' => 'bi-tag', 'color' => '#475569', 'bg' => '#f1f5f9'];
                            @endphp
                            <tr>
                                <td class="fw-semibold text-muted" style="font-size:13px;">
                                    {{ $exp->expense_date->format('d M Y') }}
                                    <div style="font-size:11px; opacity:0.7;">{{ $exp->created_at->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $exp->title }}</div>
                                    @if($exp->reference_no)
                                        <div style="font-size:11.5px; color:#6b7280;">
                                            <span class="badge bg-light text-dark border">Ref: {{ $exp->reference_no }}</span>
                                        </div>
                                    @endif
                                    @if($exp->notes)
                                        <div style="font-size:11.5px; color:#6b7280; margin-top:2px;">
                                            {{ Str::limit($exp->notes, 60) }}
                                        </div>
                                    @endif
                                    <div style="font-size:11px; color:#9ca3af; margin-top:2px;">
                                        By: {{ $exp->user->name ?? 'System' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="cat-badge" style="color:{{ $catConfig['color'] }}; background:{{ $catConfig['bg'] }};">
                                        <i class="bi {{ $catConfig['icon'] }}"></i>
                                        {{ $exp->category }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border text-capitalize">
                                        {{ str_replace('_', ' ', $exp->payment_method) }}
                                    </span>
                                </td>
                                <td style="text-align:right; font-weight:800; font-size:15px; color:#0f766e;">
                                    Rs. {{ number_format($exp->amount, 2) }}
                                </td>
                                <td style="text-align:center;">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-light border"
                                                onclick="openEditModal({{ json_encode($exp) }})"
                                                title="Edit Expense">
                                            <i class="bi bi-pencil text-primary"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.expenses.destroy', $exp) }}"
                                              onsubmit="return confirm('Delete this expenditure record of Rs. {{ number_format($exp->amount, 2) }}?');"
                                              style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete Expense">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-wallet2 fs-1 d-block text-secondary opacity-50 mb-2"></i>
                                    <h5>No Expenditures Recorded</h5>
                                    <p class="small text-muted mb-3">No expenses found for the selected period.</p>
                                    <button type="button" class="btn-pos" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                                        <i class="bi bi-plus-circle me-1"></i>Record First Expense
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($expenses->hasPages())
                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Category Breakdown Sidebar --}}
    <div class="col-lg-4">
        <div class="pos-card p-4">
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color:#111827;">
                <i class="bi bi-pie-chart text-teal" style="color:var(--pos-primary);"></i>
                Category Breakdown
            </h5>
            <p class="text-muted small mb-4">Expenditure distribution in the selected period.</p>

            @if($categoryBreakdown->count() > 0 && $filteredTotal > 0)
                <div class="d-flex flex-column gap-3">
                    @foreach($categoryBreakdown as $breakdown)
                        @php
                            $catMeta = $categories[$breakdown->category] ?? ['icon' => 'bi-tag', 'color' => '#475569', 'bg' => '#f1f5f9'];
                            $pct = ($breakdown->total_amount / $filteredTotal) * 100;
                        @endphp
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="d-flex align-items-center gap-2 fw-semibold" style="font-size:13px; color:#374151;">
                                    <span class="cat-badge" style="color:{{ $catMeta['color'] }}; background:{{ $catMeta['bg'] }}; padding:2px 8px; font-size:11px;">
                                        <i class="bi {{ $catMeta['icon'] }}"></i>
                                    </span>
                                    {{ $breakdown->category }}
                                </span>
                                <span class="fw-bold" style="font-size:13px; color:#111827;">
                                    Rs. {{ number_format($breakdown->total_amount, 2) }}
                                    <small class="text-muted fw-normal" style="font-size:11px;">({{ round($pct, 1) }}%)</small>
                                </span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill" style="width:{{ $pct }}%; background:{{ $catMeta['color'] }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-muted small">
                    No breakdown data available for this range.
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ── ADD EXPENSE MODAL ──────────────────────────── --}}
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.expenses.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-wallet2 text-teal me-2" style="color:var(--pos-primary);"></i>
                        Record Daily Expense
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body d-flex flex-column gap-3">
                    <div>
                        <label class="form-label small fw-bold text-muted mb-1">Expense Title / Description <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="pos-input" placeholder="e.g. Shop electricity bill, Tea & snacks for guests" required autofocus>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Category <span class="text-danger">*</span></label>
                            <select name="category" class="pos-input" required>
                                @foreach($categories as $catName => $catMeta)
                                    <option value="{{ $catName }}">{{ $catName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Amount (Rs.) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="pos-input" step="0.01" min="0.01" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" class="pos-input" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="pos-input" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer / Online</option>
                                <option value="card">Debit / Credit Card</option>
                                <option value="cheque">Cheque</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-muted mb-1">Bill / Receipt Reference # (Optional)</label>
                        <input type="text" name="reference_no" class="pos-input" placeholder="e.g. IESCO-18294, Invoice #502">
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-muted mb-1">Additional Notes (Optional)</label>
                        <textarea name="notes" class="pos-input" rows="2" placeholder="Any extra remarks, vendor name, etc."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-pos">
                        <i class="bi bi-check-lg me-1"></i>Save Expenditure
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── EDIT EXPENSE MODAL ─────────────────────────── --}}
<div class="modal fade" id="editExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editExpenseForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square text-teal me-2" style="color:var(--pos-primary);"></i>
                        Edit Expenditure Record
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body d-flex flex-column gap-3">
                    <div>
                        <label class="form-label small fw-bold text-muted mb-1">Expense Title / Description <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="edit_title" class="pos-input" required>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Category <span class="text-danger">*</span></label>
                            <select name="category" id="edit_category" class="pos-input" required>
                                @foreach($categories as $catName => $catMeta)
                                    <option value="{{ $catName }}">{{ $catName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Amount (Rs.) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="edit_amount" class="pos-input" step="0.01" min="0.01" required>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" id="edit_expense_date" class="pos-input" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted mb-1">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" id="edit_payment_method" class="pos-input" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer / Online</option>
                                <option value="card">Debit / Credit Card</option>
                                <option value="cheque">Cheque</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-muted mb-1">Bill / Receipt Reference # (Optional)</label>
                        <input type="text" name="reference_no" id="edit_reference_no" class="pos-input">
                    </div>

                    <div>
                        <label class="form-label small fw-bold text-muted mb-1">Additional Notes (Optional)</label>
                        <textarea name="notes" id="edit_notes" class="pos-input" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-pos">
                        <i class="bi bi-check-lg me-1"></i>Update Expenditure
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openEditModal(exp) {
    const form = document.getElementById('editExpenseForm');
    form.action = `/admin/expenses/${exp.id}`;

    document.getElementById('edit_title').value = exp.title || '';
    document.getElementById('edit_category').value = exp.category || 'Miscellaneous';
    document.getElementById('edit_amount').value = parseFloat(exp.amount).toFixed(2);
    
    // Format date YYYY-MM-DD
    const dateVal = exp.expense_date ? exp.expense_date.substring(0, 10) : '';
    document.getElementById('edit_expense_date').value = dateVal;

    document.getElementById('edit_payment_method').value = exp.payment_method || 'cash';
    document.getElementById('edit_reference_no').value = exp.reference_no || '';
    document.getElementById('edit_notes').value = exp.notes || '';

    const modal = new bootstrap.Modal(document.getElementById('editExpenseModal'));
    modal.show();
}
</script>
@endpush

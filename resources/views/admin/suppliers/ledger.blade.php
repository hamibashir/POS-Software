@extends('layouts.admin')

@section('title', "Supplier Ledger — {$supplier->name}")

@push('styles')
<style>
    .ledger-header-card {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #e5e7eb;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .badge-type {
        font-weight: 700;
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-type-purchase { background: #fee2e2; color: #991b1b; }
    .badge-type-return   { background: #fef3c7; color: #92400e; }
    .badge-type-payment  { background: #d1fae5; color: #065f46; }
    .badge-type-ob       { background: #e0f2fe; color: #0369a1; }

    @media print {
        .pos-topbar, .btn-no-print, .filter-bar { display: none !important; }
        .pos-page { margin-top: 0 !important; padding: 0 !important; }
        body { background: #fff !important; }
        .pos-card, .ledger-header-card { border: none !important; box-shadow: none !important; }
    }
</style>
@endpush

@section('content')

{{-- ── Flash messages ─────────────────────────────── --}}
@if(session('success'))
    <div class="pos-alert pos-alert-success mb-4 btn-no-print">
        <i class="bi bi-check-circle-fill"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

{{-- ── Header & Action Bar ─────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <a href="{{ route('admin.suppliers.index') }}" class="text-decoration-none text-muted small fw-semibold btn-no-print">
            <i class="bi bi-arrow-left me-1"></i> Back to Suppliers
        </a>
        <h1 class="fs-4 fw-bold text-dark mt-1 mb-0">
            <i class="bi bi-journal-text text-primary me-2"></i>Supplier Ledger: {{ $supplier->name }}
        </h1>
    </div>
    <div class="d-flex gap-2 btn-no-print">
        <button type="button" class="btn-pos-outline" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Statement
        </button>
        <button type="button" class="btn-pos" onclick="openPayModal()">
            <i class="bi bi-cash-stack me-1"></i> Make Payment
        </button>
    </div>
</div>

{{-- ── Supplier Details & Financial Overview ────────── --}}
<div class="ledger-header-card">
    <div class="row g-4 align-items-center">
        <div class="col-lg-5 border-end">
            <h4 class="fw-bold text-dark mb-1">{{ $supplier->name }}</h4>
            @if($supplier->company_name)
                <div class="text-muted fw-semibold mb-2"><i class="bi bi-building me-1"></i>{{ $supplier->company_name }}</div>
            @endif
            <div class="d-flex flex-column gap-1 text-muted small">
                @if($supplier->phone)
                    <div><i class="bi bi-telephone text-primary me-2"></i><span class="text-dark fw-semibold">{{ $supplier->phone }}</span></div>
                @endif
                @if($supplier->email)
                    <div><i class="bi bi-envelope text-primary me-2"></i>{{ $supplier->email }}</div>
                @endif
                @if($supplier->address)
                    <div><i class="bi bi-geo-alt text-primary me-2"></i>{{ $supplier->address }}</div>
                @endif
                @if($supplier->notes)
                    <div class="mt-1 fst-italic"><i class="bi bi-info-circle text-primary me-2"></i>{{ $supplier->notes }}</div>
                @endif
            </div>
        </div>

        <div class="col-lg-7">
            <div class="row g-3 text-center">
                <div class="col-sm-3">
                    <div class="text-muted small fw-semibold">Total Invoiced</div>
                    <div class="fs-5 fw-bold text-dark">{{ pkr($supplier->total_purchases + (float)$supplier->opening_balance, 2) }}</div>
                    @if((float)$supplier->opening_balance > 0)
                        <div class="text-muted" style="font-size:10.5px;">incl. {{ pkr($supplier->opening_balance, 2) }} OB</div>
                    @endif
                </div>
                <div class="col-sm-3">
                    <div class="text-muted small fw-semibold">Total Returns</div>
                    <div class="fs-5 fw-bold text-warning">{{ pkr($supplier->total_returns, 2) }}</div>
                    <div class="text-muted" style="font-size:10.5px;">{{ $supplier->returns->count() }} return(s)</div>
                </div>
                <div class="col-sm-3">
                    <div class="text-muted small fw-semibold">Total Paid</div>
                    <div class="fs-5 fw-bold text-success">{{ pkr($supplier->total_paid, 2) }}</div>
                    <div class="text-muted" style="font-size:10.5px;">{{ $supplier->payments->count() }} payment(s)</div>
                </div>
                <div class="col-sm-3">
                    <div class="text-muted small fw-semibold">Remaining Balance</div>
                    @if($supplier->pending_balance > 0)
                        <div class="fs-4 fw-bold text-danger">{{ pkr($supplier->pending_balance, 2) }}</div>
                        <div class="badge bg-danger-subtle text-danger" style="font-size:10px;">Pending Due</div>
                    @else
                        <div class="fs-4 fw-bold text-success">{{ pkr(0, 2) }}</div>
                        <div class="badge bg-success-subtle text-success" style="font-size:10px;">Fully Cleared</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Ledger Statement Table ──────────────────────── --}}
<div class="pos-card">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold text-dark mb-0 fs-6">
            <i class="bi bi-clock-history me-1 text-primary"></i> Transaction Statement & Running Balance
        </h5>
        <span class="text-muted small">{{ $ledgerRows->count() }} total record(s)</span>
    </div>

    <div class="table-responsive">
        <table class="pos-table w-100 align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:120px;">Date</th>
                    <th style="width:110px;">Type</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th class="text-end" style="width:130px;">Debit (+) <br><small class="fw-normal text-muted" style="font-size:10px;">Store Owes</small></th>
                    <th class="text-end" style="width:130px;">Credit (-) <br><small class="fw-normal text-muted" style="font-size:10px;">Paid / Returned</small></th>
                    <th class="text-end" style="width:140px;">Balance (PKR)</th>
                    <th>Recorded By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ledgerRows as $row)
                <tr>
                    <td class="text-dark fw-semibold" style="font-size:13px;">
                        {{ $row['date'] instanceof \Carbon\Carbon ? $row['date']->format('d M Y') : date('d M Y', strtotime($row['date'])) }}
                    </td>
                    <td>
                        @if($row['type'] === 'purchase')
                            <span class="badge-type badge-type-purchase">Purchase</span>
                        @elseif($row['type'] === 'return')
                            <span class="badge-type badge-type-return">Return</span>
                        @elseif($row['type'] === 'payment')
                            <span class="badge-type badge-type-payment">Payment</span>
                        @else
                            <span class="badge-type badge-type-ob">Opening</span>
                        @endif
                    </td>
                    <td>
                        @if(!empty($row['link']))
                            <a href="{{ $row['link'] }}" class="fw-bold text-primary text-decoration-none">
                                {{ $row['ref'] }} <i class="bi bi-box-arrow-up-right small"></i>
                            </a>
                        @else
                            <span class="fw-bold text-dark">{{ $row['ref'] }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="text-dark">{{ $row['description'] }}</span>
                        @if(!empty($row['method']) && $row['method'] !== '—')
                            <span class="text-muted small">· {{ $row['method'] }}</span>
                        @endif
                    </td>
                    <td class="text-end fw-semibold {{ $row['debit'] > 0 ? 'text-danger' : 'text-muted' }}">
                        {{ $row['debit'] > 0 ? pkr($row['debit'], 2) : '—' }}
                    </td>
                    <td class="text-end fw-semibold {{ $row['credit'] > 0 ? 'text-success' : 'text-muted' }}">
                        {{ $row['credit'] > 0 ? pkr($row['credit'], 2) : '—' }}
                    </td>
                    <td class="text-end fw-bold {{ $row['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                        {{ pkr($row['balance'], 2) }}
                    </td>
                    <td class="text-muted small">
                        <i class="bi bi-person me-1"></i>{{ $row['user'] }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        No transactions recorded yet for this supplier.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($ledgerRows->isNotEmpty())
            <tfoot>
                <tr class="fw-bold bg-light">
                    <td colspan="4" class="text-end">Total Summary:</td>
                    <td class="text-end text-danger">{{ pkr($ledgerRows->sum('debit'), 2) }}</td>
                    <td class="text-end text-success">{{ pkr($ledgerRows->sum('credit'), 2) }}</td>
                    <td class="text-end fs-6 {{ $supplier->pending_balance > 0 ? 'text-danger' : 'text-success' }}">
                        {{ pkr($supplier->pending_balance, 2) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- ══════════════ PAYMENT MODAL ══════════════ --}}
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.suppliers.payments', $supplier) }}">
                @csrf
                <div class="modal-header bg-teal text-white" style="background:#0f766e; color:#fff; border-radius:14px 14px 0 0;">
                    <h5 class="modal-title text-white"><i class="bi bi-cash-stack me-2"></i>Record Payment to {{ $supplier->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="p-3 mb-3 rounded" style="background:#f0fdfa; border:1px solid #ccfbf1;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Current Pending Balance:</span>
                            <span class="fs-6 fw-bold text-danger">{{ pkr($supplier->pending_balance, 2) }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">Payment Amount (PKR) <span class="text-danger">*</span></label>
                            @if($supplier->pending_balance > 0)
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none fw-bold" onclick="document.getElementById('pay_amount_input').value='{{ number_format($supplier->pending_balance, 2, '.', '') }}'">
                                    Pay Full Balance
                                </button>
                            @endif
                        </div>
                        <input type="number" step="0.01" min="0.01" name="amount" id="pay_amount_input"
                               class="pos-input fs-5 fw-bold text-success"
                               value="{{ $supplier->pending_balance > 0 ? number_format($supplier->pending_balance, 2, '.', '') : '' }}" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="pos-input" required>
                                <option value="cash" selected>💵 Cash</option>
                                <option value="bank">🏦 Bank Transfer</option>
                                <option value="cheque">📝 Cheque</option>
                                <option value="online">📱 Online / Card</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="pos-input" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cheque # / Bank Reference</label>
                        <input type="text" name="reference_number" class="pos-input" placeholder="e.g. CHQ-10499 or Trx-8821">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes / Remarks</label>
                        <input type="text" name="notes" class="pos-input" placeholder="e.g. Cleared via office cash">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-pos-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-pos" style="background:#0f766e;"><i class="bi bi-check2-circle me-1"></i>Submit Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openPayModal() {
        new bootstrap.Modal(document.getElementById('payModal')).show();
    }
</script>
@endpush

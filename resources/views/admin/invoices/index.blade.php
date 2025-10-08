@extends('layouts.admin')
@section('title', 'Invoices')

@section('admin-content')
<div class="admin-page-header">
    <div class="admin-page-header-content">
        <div class="admin-page-title">
            <h1>{{ trans('app.Invoice Management') }}</h1>
            <p class="admin-page-subtitle">{{ trans('app.Manage system invoices and payments') }}</p>
        </div>
        <div class="admin-page-actions">
            <a href="{{ route('admin.invoices.create') }}" class="admin-btn admin-btn-info admin-btn-m">
                <i class="fas fa-plus me-2"></i>
                {{ trans('app.Create Invoice') }}
            </a>
        </div>
    </div>
</div>

<!-- Enhanced Filters Section -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <div class="d-flex align-items-center">
            <i class="fas fa-filter me-3 text-primary"></i>
            <div>
                <h5 class="card-title mb-0">{{ trans('app.Filters') }}</h5>
                <small class="text-muted">{{ trans('app.Filter and search invoices') }}</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="searchInvoices" class="form-label">{{ trans('app.Search') }}</label>
                <input type="text" id="searchInvoices" class="form-control" 
                       placeholder="{{ trans('app.Search by invoice number or user') }}">
            </div>
            <div class="col-md-3">
                <label for="status-filter" class="form-label">{{ trans('app.Status') }}</label>
                <select id="status-filter" class="form-select">
                    <option value="">{{ trans('app.All Statuses') }}</option>
                    <option value="pending">{{ trans('app.Pending') }}</option>
                    <option value="paid">{{ trans('app.Paid') }}</option>
                    <option value="overdue">{{ trans('app.Overdue') }}</option>
                    <option value="cancelled">{{ trans('app.Cancelled') }}</option>
                    <option value="suspended">{{ trans('app.Suspended') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date-from" class="form-label">{{ trans('app.Date From') }}</label>
                <input type="date" id="date-from" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="date-to" class="form-label">{{ trans('app.Date To') }}</label>
                <input type="date" id="date-to" class="form-control">
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Statistics Section -->
<div class="dashboard-grid dashboard-grid-4 stats-grid-enhanced">
    <!-- Total Invoices Stats Card -->
    <div class="stats-card stats-card-primary animate-slide-up">
        <div class="stats-card-background">
            <div class="stats-card-pattern"></div>
        </div>
        <div class="stats-card-content">
            <div class="stats-card-header">
                <div class="stats-card-icon invoices"></div>
                <div class="stats-card-menu">
                    <button class="stats-menu-btn">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </div>
            <div class="stats-card-body">
                <div class="stats-card-value">{{ $invoices->total() }}</div>
                <div class="stats-card-label">{{ trans('app.Total Invoices') }}</div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span>{{ trans('app.all_invoices') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Paid Invoices Stats Card -->
    <div class="stats-card stats-card-success animate-slide-up animate-delay-200">
        <div class="stats-card-background">
            <div class="stats-card-pattern"></div>
        </div>
        <div class="stats-card-content">
            <div class="stats-card-header">
                <div class="stats-card-icon paid"></div>
                <div class="stats-card-menu">
                    <button class="stats-menu-btn">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </div>
            <div class="stats-card-body">
                <div class="stats-card-value">{{ $invoices->where('status', 'paid')->count() }}</div>
                <div class="stats-card-label">{{ trans('app.Paid Invoices') }}</div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span>{{ number_format(($invoices->where('status', 'paid')->count() / max($invoices->count(), 1)) * 100, 1) }}% {{ trans('app.of_total') }}</span> </div>
            </div>
        </div>
    </div>

    <!-- Pending Invoices Stats Card -->
    <div class="stats-card stats-card-warning animate-slide-up animate-delay-300">
        <div class="stats-card-background">
            <div class="stats-card-pattern"></div>
        </div>
        <div class="stats-card-content">
            <div class="stats-card-header">
                <div class="stats-card-icon tickets"></div>
                <div class="stats-card-menu">
                    <button class="stats-menu-btn">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </div>
            <div class="stats-card-body">
                <div class="stats-card-value">{{ $invoices->where('status', 'pending')->count() }}</div>
                <div class="stats-card-label">{{ trans('app.Pending Invoices') }}</div>
                <div class="stats-card-trend negative">
                    <i class="stats-trend-icon negative"></i>
                    <span>{{ number_format(($invoices->where('status', 'pending')->count() / max($invoices->count(), 1)) * 100, 1) }}% {{ trans('app.of_total') }}</span> </div>
            </div>
        </div>
    </div>

    <!-- Overdue Invoices Stats Card -->
    <div class="stats-card stats-card-danger animate-slide-up animate-delay-400">
        <div class="stats-card-background">
            <div class="stats-card-pattern"></div>
        </div>
        <div class="stats-card-content">
            <div class="stats-card-header">
                <div class="stats-card-icon articles"></div>
                <div class="stats-card-menu">
                    <button class="stats-menu-btn">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </div>
            <div class="stats-card-body">
                <div class="stats-card-value">{{ $invoices->where('status', 'overdue')->count() }}</div>
                <div class="stats-card-label">{{ trans('app.Overdue Invoices') }}</div>
                <div class="stats-card-trend negative">
                    <i class="stats-trend-icon negative"></i>
                    <span>{{ number_format(($invoices->where('status', 'overdue')->count() / max($invoices->count(), 1)) * 100, 1) }}% {{ trans('app.of_total') }}</span> </div>
            </div>
        </div>
    </div>
</div>

<!-- Invoices Table -->
<div class="card">
    <div class="card-header bg-light">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-file-invoice-dollar me-3 text-primary"></i>
                <div>
                    <h5 class="card-title mb-0">{{ trans('app.All Invoices') }}</h5>
                    <small class="text-muted">{{ trans('app.Manage and monitor all system invoices') }}</small>
                </div>
            </div>
            <div>
                <span class="badge bg-info fs-6">{{ $invoices->total() }} {{ trans('app.Invoices') }}</span>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        @if($invoices->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">{{ trans('app.Avatar') }}</th>
                        <th>{{ trans('app.Invoice') }}</th>
                        <th>{{ trans('app.User') }}</th>
                        <th class="text-center">{{ trans('app.Product') }}</th>
                        <th class="text-end">{{ trans('app.Amount') }}</th>
                        <th class="text-center">{{ trans('app.Status') }}</th>
                        <th class="text-center">{{ trans('app.Due Date') }}</th>
                        <th class="text-center">{{ trans('app.Created') }}</th>
                        <th class="text-center">{{ trans('app.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr class="invoice-row" data-number="{{ strtolower($invoice->invoice_number) }}" data-user="{{ strtolower($invoice->user->name ?? '') }}" data-status="{{ $invoice->status }}">
                        <td class="text-center">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center invoice-avatar">
                                <span class="text-muted small fw-bold">{{ strtoupper(substr($invoice->invoice_number, 0, 1)) }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $invoice->invoice_number }}</div>
                            <small class="text-muted">ID: {{ $invoice->id }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $invoice->user->name ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $invoice->user->email ?? '' }}</small>
                        </td>
                        <td class="text-center">
                            <div class="fw-semibold text-dark">{{ $invoice->license->product->name ?? 'N/A' }}</div>
                            @if($invoice->license)
                            <small class="text-muted">{{ $invoice->license->license_type ?? '' }}</small>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="fw-semibold text-dark">${{ number_format($invoice->amount, 2) }}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $invoice->status === 'paid' ? 'bg-success' : ($invoice->status === 'overdue' ? 'bg-danger' : ($invoice->status === 'pending' ? 'bg-warning' : ($invoice->status === 'cancelled' ? 'bg-secondary' : 'bg-info'))) }}">
                                @if($invoice->status === 'paid')
                                    <i class="fas fa-check-circle me-1"></i>{{ trans('app.Paid') }}
                                @elseif($invoice->status === 'pending')
                                    <i class="fas fa-clock me-1"></i>{{ trans('app.Pending') }}
                                @elseif($invoice->status === 'overdue')
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ trans('app.Overdue') }}
                                @elseif($invoice->status === 'cancelled')
                                    <i class="fas fa-times-circle me-1"></i>{{ trans('app.Cancelled') }}
                                @else
                                    <i class="fas fa-pause-circle me-1"></i>{{ ucfirst($invoice->status) }}
                                @endif
                            </span>
                        </td>
                        <td class="text-center">
                            @if($invoice->due_date)
                                <div class="fw-semibold text-dark">{{ $invoice->due_date->format('M d, Y') }}</div>
                                @if($invoice->due_date->isPast() && $invoice->status === 'pending')
                                    <small class="text-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ trans('app.Overdue') }}
                                    </small>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="fw-semibold text-dark">{{ $invoice->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $invoice->created_at->diffForHumans() }}</small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group-vertical btn-group-sm" role="group">
                                <a href="{{ route('admin.invoices.show', $invoice) }}"
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-eye me-1"></i>
                                    {{ trans('app.View') }}
                                </a>

                                @if($invoice->status === 'pending')
                                <form method="POST" action="{{ route('admin.invoices.mark-paid', $invoice) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-success btn-sm w-100"
                                            data-confirm="{{ trans('app.Are you sure you want to mark this invoice as paid?') }}">
                                        <i class="fas fa-check me-1"></i>
                                        {{ trans('app.Paid') }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.invoices.cancel', $invoice) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                                            data-confirm="{{ trans('app.Are you sure you want to cancel this invoice?') }}">
                                        <i class="fas fa-times me-1"></i>
                                        {{ trans('app.Cancel') }}
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-center">
                {{ $invoices->links() }}
            </div>
        </div>
        @endif
        @else
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-file-invoice-dollar text-muted empty-state-icon"></i>
            </div>
            <h4 class="text-muted">{{ trans('app.No Invoices Found') }}</h4>
            <p class="text-muted mb-4">{{ trans('app.Create your first invoice to get started') }}</p>
            <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-plus me-2"></i>
                {{ trans('app.Create Your First Invoice') }}
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
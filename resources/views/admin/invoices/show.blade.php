@extends('layouts.admin')
@section('title', 'Show Invoice')

@section('admin-content')
<div class="container-fluid invoice-show">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-eye text-info me-2"></i>
                                {{ trans('app.View Invoice') }}
                            </h1>
                            <p class="text-muted mb-0">#{{ $invoice->invoice_number ?? $invoice->id }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-primary me-2">
                                <i class="fas fa-edit me-1"></i>
                                {{ trans('app.Edit Invoice') }}
                            </a>
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ trans('app.Back to Invoices') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Invoice Overview -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-invoice-dollar me-2"></i>
                        {{ trans('app.Invoice Overview') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-hashtag text-primary me-1"></i>
                                {{ trans('app.Invoice Number') }}
                            </label>
                            <p class="text-muted">{{ $invoice->invoice_number ?? '#' . $invoice->id }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-user text-success me-1"></i>
                                {{ trans('app.Customer') }}
                            </label>
                            <p class="text-muted">
                                @if($invoice->user)
                                    <a href="{{ route('admin.users.show', $invoice->user) }}" class="text-decoration-none">
                                        {{ $invoice->user->name }} ({{ $invoice->user->email }})
                                    </a>
                                @else
                                    {{ trans('app.No Customer') }}
                                @endif
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-tag text-warning me-1"></i>
                                {{ trans('app.Invoice Type') }}
                            </label>
                            <p class="text-muted">
                                <span class="badge bg-{{ $invoice->type == 'custom' ? 'info' : 'primary' }}">
                                    {{ trans('app.' . ucfirst($invoice->type)) }}
                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-info-circle text-info me-1"></i>
                                {{ trans('app.Status') }}
                            </label>
                            <p class="text-muted">
                                <span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'overdue' ? 'danger' : ($invoice->status == 'cancelled' ? 'secondary' : 'warning')) }}">
                                    {{ trans('app.' . ucfirst($invoice->status)) }}
                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-dollar-sign text-success me-1"></i>
                                {{ trans('app.Amount') }}
                            </label>
                            <p class="text-muted fs-5 fw-bold">{{ $invoice->amount }} {{ $invoice->currency }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar text-danger me-1"></i>
                                {{ trans('app.Due Date') }}
                            </label>
                            <p class="text-muted">
                                {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : trans('app.No Due Date') }}
                            </p>
                        </div>

                        @if($invoice->paid_at)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-check text-success me-1"></i>
                                {{ trans('app.Paid At') }}
                            </label>
                            <p class="text-muted">{{ $invoice->paid_at->format('M d, Y H:i') }}</p>
                        </div>
                        @endif

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar text-info me-1"></i>
                                {{ trans('app.Created At') }}
                            </label>
                            <p class="text-muted">{{ $invoice->created_at->format('M d, Y H:i') }}</p>
                        </div>

                        @if($invoice->license)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-key text-warning me-1"></i>
                                {{ trans('app.License') }}
                            </label>
                            <p class="text-muted">
                                <a href="{{ route('admin.licenses.show', $invoice->license) }}" class="text-decoration-none">
                                    {{ $invoice->license->product->name }} - {{ $invoice->license->license_type }}
                                </a>
                            </p>
                        </div>
                        @endif

                        @if($invoice->custom_product_name)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-shopping-cart text-primary me-1"></i>
                                {{ trans('app.Product/Service') }}
                            </label>
                            <p class="text-muted">{{ $invoice->custom_product_name }}</p>
                        </div>
                        @endif

                        @if($invoice->custom_invoice_type)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-cog text-info me-1"></i>
                                {{ trans('app.Custom Invoice Type') }}
                            </label>
                            <p class="text-muted">{{ trans('app.' . ucfirst(str_replace('_', ' ', $invoice->custom_invoice_type))) }}</p>
                        </div>
                        @endif

                        @if($invoice->expiration_date)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-times text-danger me-1"></i>
                                {{ trans('app.Expiration Date') }}
                            </label>
                            <p class="text-muted">{{ $invoice->expiration_date->format('M d, Y') }}</p>
                        </div>
                        @endif
                    </div>

                    @if($invoice->notes)
                    <div class="mt-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-sticky-note text-warning me-1"></i>
                            {{ trans('app.Notes') }}
                        </label>
                        <div class="bg-light p-3 rounded">
                            <p class="text-muted mb-0">{{ $invoice->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Invoice Statistics -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        {{ trans('app.Invoice Statistics') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-primary">{{ $invoice->amount }}</h3>
                                <p class="text-muted mb-0">{{ trans('app.Total Amount') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-success">{{ $invoice->currency }}</h3>
                                <p class="text-muted mb-0">{{ trans('app.Currency') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-info">{{ $invoice->user->invoices_count ?? 0 }}</h3>
                                <p class="text-muted mb-0">{{ trans('app.Customer Invoices') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-warning">{{ $invoice->days_remaining ?? 0 }}</h3>
                                <p class="text-muted mb-0">{{ trans('app.Days Remaining') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        {{ trans('app.Recent Activity') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ trans('app.Invoice Created') }}</h6>
                                <p class="timeline-text text-muted">{{ $invoice->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        @if($invoice->updated_at != $invoice->created_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ trans('app.Last Updated') }}</h6>
                                <p class="timeline-text text-muted">{{ $invoice->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        @endif
                        @if($invoice->paid_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ trans('app.Invoice Paid') }}</h6>
                                <p class="timeline-text text-muted">{{ $invoice->paid_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        @endif
                        @if($invoice->status == 'overdue')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ trans('app.Invoice Overdue') }}</h6>
                                <p class="timeline-text text-muted">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : trans('app.Due Date Passed') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Invoice Status -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-invoice me-2"></i>
                        {{ trans('app.Invoice Status') }}
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="invoice-status-display mb-3">
                        <i class="fas fa-file-invoice-dollar fs-1 text-primary mb-2"></i>
                        <h5>#{{ $invoice->invoice_number ?? $invoice->id }}</h5>
                        <span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'overdue' ? 'danger' : ($invoice->status == 'cancelled' ? 'secondary' : 'warning')) }} fs-6">
                            {{ trans('app.' . ucfirst($invoice->status)) }}
                        </span>
                    </div>
                    <div class="invoice-amount-display">
                        <h3 class="text-primary">{{ $invoice->amount }} {{ $invoice->currency }}</h3>
                        <p class="text-muted small mb-0">{{ trans('app.Total Amount') }}</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        {{ trans('app.Quick Actions') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            {{ trans('app.Edit Invoice') }}
                        </a>
                        @if($invoice->user)
                        <a href="{{ route('admin.users.show', $invoice->user) }}" class="btn btn-outline-success">
                            <i class="fas fa-user me-1"></i>
                            {{ trans('app.View Customer') }}
                        </a>
                        @endif
                        @if($invoice->license)
                        <a href="{{ route('admin.licenses.show', $invoice->license) }}" class="btn btn-outline-primary">
                            <i class="fas fa-key me-1"></i>
                            {{ trans('app.View License') }}
                        </a>
                        @endif
                        <button class="btn btn-outline-info" id="print-invoice-btn">
                            <i class="fas fa-print me-1"></i>
                            {{ trans('app.Print Invoice') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Invoice Details -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ trans('app.Invoice Details') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-info">{{ $invoice->created_at->format('M Y') }}</h4>
                                <p class="text-muted small mb-0">{{ trans('app.Created') }}</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-warning">{{ $invoice->updated_at->format('M Y') }}</h4>
                                <p class="text-muted small mb-0">{{ trans('app.Updated') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        {{ trans('app.Payment Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-dollar-sign text-success me-1"></i>
                            {{ trans('app.Amount') }}
                        </label>
                        <p class="text-muted fs-5 fw-bold">{{ $invoice->amount }} {{ $invoice->currency }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-calendar text-danger me-1"></i>
                            {{ trans('app.Due Date') }}
                        </label>
                        <p class="text-muted">
                            {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : trans('app.No Due Date') }}
                        </p>
                    </div>
                    @if($invoice->paid_at)
                    <div class="mb-0">
                        <label class="form-label fw-bold">
                            <i class="fas fa-check text-success me-1"></i>
                            {{ trans('app.Paid At') }}
                        </label>
                        <p class="text-muted">{{ $invoice->paid_at->format('M d, Y H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
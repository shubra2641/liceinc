@extends('layouts.admin')
@section('title', 'Create Invoice')

@section('admin-content')
<div class="container-fluid products-form">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-plus-circle text-primary me-2"></i>
                                {{ trans('app.Create Invoice') }}
                            </h1>
                            <p class="text-muted mb-0">{{ trans('app.Create a new invoice for a customer') }}</p>
        </div>
                        <div>
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

@if($errors->any())
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger">
                <h5 class="alert-heading">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ trans('app.Validation Errors') }}
                </h5>
                <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endif

    <form method="POST" action="{{ route('admin.invoices.store') }}" class="needs-validation" novalidate>
    @csrf

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Invoice Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice-dollar me-2"></i>
                            {{ trans('app.Invoice Information') }}
                            <span class="badge bg-light text-primary ms-2">{{ trans('app.Required') }}</span>
                        </h5>
    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_id" class="form-label">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    {{ trans('app.Customer') }} <span class="text-danger">*</span>
                </label>
                                <select class="form-select @error('user_id') is-invalid @enderror" 
                                        id="user_id" name="user_id" required>
                    <option value="">{{ trans('app.Select Customer') }}</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" 
                            data-name="{{ $user->name }}"
                            data-email="{{ $user->email }}"
                            {{ old('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                    @endforeach
                </select>
                                @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
            </div>

                            <div class="col-md-6 mb-3">
                                <label for="license_id" class="form-label">
                                    <i class="fas fa-key text-success me-1"></i>
                                    {{ trans('app.License') }} <span class="text-danger">*</span>
                </label>
                                <select class="form-select @error('license_id') is-invalid @enderror" 
                                        id="license_id" name="license_id" required>
                    <option value="">{{ trans('app.Select License') }}</option>
                    <option value="custom">{{ trans('app.Custom Invoice (No License)') }}</option>
                </select>
                <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    {{ trans('app.Select a customer first to load their licenses') }}
                </div>
                                @error('license_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">
                                    <i class="fas fa-tag text-warning me-1"></i>
                                    {{ trans('app.Invoice Type') }} <span class="text-danger">*</span>
                </label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" name="type" required>
                                    <option value="initial" {{ old('type') == 'initial' ? 'selected' : '' }}>
                                        {{ trans('app.Initial Purchase') }}
                                    </option>
                                    <option value="renewal" {{ old('type') == 'renewal' ? 'selected' : '' }}>
                                        {{ trans('app.Renewal') }}
                                    </option>
                                    <option value="upgrade" {{ old('type') == 'upgrade' ? 'selected' : '' }}>
                                        {{ trans('app.Upgrade') }}
                                    </option>
                                    <option value="custom" {{ old('type') == 'custom' ? 'selected' : '' }}>
                                        {{ trans('app.Custom') }}
                                    </option>
                </select>
                                @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">
                                    <i class="fas fa-info-circle text-info me-1"></i>
                                    {{ trans('app.Status') }} <span class="text-danger">*</span>
                    </label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>
                                        {{ trans('app.Pending') }}
                                    </option>
                                    <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>
                                        {{ trans('app.Paid') }}
                                    </option>
                                    <option value="overdue" {{ old('status') == 'overdue' ? 'selected' : '' }}>
                                        {{ trans('app.Overdue') }}
                                    </option>
                                    <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>
                                        {{ trans('app.Cancelled') }}
                                    </option>
                    </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                </div>

                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">
                                    <i class="fas fa-dollar-sign text-success me-1"></i>
                                    {{ trans('app.Amount') }} <span class="text-danger">*</span>
                    </label>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" name="amount" value="{{ old('amount') }}" 
                                       step="0.01" min="0" required>
                                @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                </div>

                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label">
                                    <i class="fas fa-money-bill text-warning me-1"></i>
                                    {{ trans('app.Currency') }} <span class="text-danger">*</span>
                    </label>
                                <select class="form-select @error('currency') is-invalid @enderror" 
                                        id="currency" name="currency" required>
                    <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>USD</option>
                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                    <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP</option>
                    <option value="SAR" {{ old('currency') == 'SAR' ? 'selected' : '' }}>SAR</option>
                    <option value="AED" {{ old('currency') == 'AED' ? 'selected' : '' }}>AED</option>
                </select>
                                @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
            </div>

                            <div class="col-md-6 mb-3">
                                <label for="due_date" class="form-label">
                                    <i class="fas fa-calendar text-danger me-1"></i>
                                    {{ trans('app.Due Date') }}
                </label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                       id="due_date" name="due_date" 
                       value="{{ old('due_date', now()->addDays(1)->format('Y-m-d')) }}">
                                @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3" id="paid_at_group" class="hidden-field">
                                <label for="paid_at" class="form-label">
                                    <i class="fas fa-check text-success me-1"></i>
                                    {{ trans('app.Paid At') }}
                                </label>
                                <input type="date" class="form-control @error('paid_at') is-invalid @enderror" 
                                       id="paid_at" name="paid_at" 
                                       value="{{ old('paid_at', now()->format('Y-m-d')) }}">
                                @error('paid_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note text-warning me-1"></i>
                                {{ trans('app.Notes') }}
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4"
                                      placeholder="{{ trans('app.Add any additional notes for this invoice') }}">{{ old('notes') }}</textarea>
                            @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
            </div>

                <!-- Custom Invoice Fields -->
                <div class="card mb-4" id="custom_invoice_fields" class="hidden-field">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog me-2"></i>
                            {{ trans('app.Custom Invoice Settings') }}
                            <span class="badge bg-light text-success ms-2">{{ trans('app.Required for Custom') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="custom_invoice_type" class="form-label">
                                    <i class="fas fa-cog text-warning me-1"></i>
                                    {{ trans('app.Custom Invoice Type') }} <span class="text-danger">*</span>
                </label>
                                <select class="form-select @error('custom_invoice_type') is-invalid @enderror" 
                                        id="custom_invoice_type" name="custom_invoice_type">
                                    <option value="one_time" {{ old('custom_invoice_type') == 'one_time' ? 'selected' : '' }}>
                                        {{ trans('app.One-time Payment') }}
                                    </option>
                                    <option value="monthly" {{ old('custom_invoice_type') == 'monthly' ? 'selected' : '' }}>
                                        {{ trans('app.Monthly') }}
                                    </option>
                                    <option value="quarterly" {{ old('custom_invoice_type') == 'quarterly' ? 'selected' : '' }}>
                                        {{ trans('app.Quarterly') }}
                                    </option>
                                    <option value="semi_annual" {{ old('custom_invoice_type') == 'semi_annual' ? 'selected' : '' }}>
                                        {{ trans('app.Semi-Annual') }}
                                    </option>
                                    <option value="annual" {{ old('custom_invoice_type') == 'annual' ? 'selected' : '' }}>
                                        {{ trans('app.Annual') }}
                                    </option>
                                    <option value="custom_recurring" {{ old('custom_invoice_type', 'custom_recurring') == 'custom_recurring' ? 'selected' : '' }}>
                                        {{ trans('app.Custom Recurring') }}
                                    </option>
                </select>
                                @error('custom_invoice_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="custom_product_name" class="form-label">
                                    <i class="fas fa-shopping-cart text-primary me-1"></i>
                                    {{ trans('app.Product/Service Description') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('custom_product_name') is-invalid @enderror" 
                                       id="custom_product_name" name="custom_product_name" 
                                       value="{{ old('custom_product_name') }}" 
                                       placeholder="{{ trans('app.Enter product or service description') }}">
                                @error('custom_product_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3" id="expiration_date_group">
                                <label for="expiration_date" class="form-label">
                                    <i class="fas fa-calendar-times text-danger me-1"></i>
                                    {{ trans('app.Expiration Date') }}
                                </label>
                                <input type="date" class="form-control @error('expiration_date') is-invalid @enderror" 
                                       id="expiration_date" name="expiration_date" 
                                       value="{{ old('expiration_date') }}">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Leave empty for one-time payment') }}
                                </div>
                                @error('expiration_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Invoice Preview -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-eye me-2"></i>
                            {{ trans('app.Invoice Preview') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div id="invoice-preview" class="p-3 rounded border">
                                <i class="fas fa-file-invoice-dollar fs-1 text-primary mb-2"></i>
                                <h5 id="preview-customer">{{ trans('app.Customer Name') }}</h5>
                                <p id="preview-amount" class="text-muted small mb-0">$0.00 USD</p>
                                <span id="preview-status" class="badge bg-warning mt-2">{{ trans('app.Pending') }}</span>
                            </div>
                            <p class="text-muted small mt-2">{{ trans('app.Live Preview') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            {{ trans('app.Quick Stats') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-primary">{{ $users->count() }}</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Customers') }}</p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-success">0</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Total Invoices') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Information -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ trans('app.Invoice Information') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-hashtag text-primary me-1"></i>
                                {{ trans('app.Invoice Number') }}
                            </label>
                            <p class="text-muted small" id="preview-invoice-number">{{ trans('app.Auto Generated') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar text-success me-1"></i>
                                {{ trans('app.Created At') }}
                            </label>
                            <p class="text-muted small">{{ now()->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar text-danger me-1"></i>
                                {{ trans('app.Due Date') }}
                </label>
                            <p class="text-muted small" id="preview-due-date">{{ now()->addDays(30)->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Invoice Tips -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-lightbulb me-2"></i>
                            {{ trans('app.Invoice Tips') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ trans('app.Choose the right invoice type') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ trans('app.Set appropriate due date') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ trans('app.Verify amount and currency') }}
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ trans('app.Add relevant notes') }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>{{ trans('app.Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ trans('app.Create Invoice') }}
                            </button>
                        </div>
                    </div>
        </div>
    </div>
    </div>
</form>
</div>

@endsection
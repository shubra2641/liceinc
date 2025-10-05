@extends('layouts.admin')

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
                                {{ trans('app.Create Ticket for User') }}
                            </h1>
                            <p class="text-muted mb-0">{{ trans('app.Create New Support Ticket') }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ trans('app.Back to Tickets') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <form method="post" action="{{ route('admin.tickets.store') }}" class="needs-validation" novalidate>
        @csrf

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- User Selection -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user me-2"></i>
                            {{ trans('app.User Selection') }}
                            <span class="badge bg-light text-primary ms-2">{{ trans('app.Required') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">
                                <i class="fas fa-user-circle text-primary me-1"></i>
                                {{ trans('app.Select User') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('user_id') is-invalid @enderror" 
                                    id="user_id" name="user_id" required data-action="update-user-licenses">
                                <option value="">{{ trans('app.select_a_user') }}</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" 
                                        data-licenses='{{ $user->licenses->toJson() }}'
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

                        <!-- License Information Section -->
                        <div id="license-info" class="mb-3 hidden-field">
                            <label class="form-label">
                                <i class="fas fa-key text-success me-1"></i>
                                {{ trans('app.License Information') }}
                            </label>
                            <div id="license-details" class="bg-light p-3 rounded border">
                                <!-- License details will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ticket Details -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-ticket-alt me-2"></i>
                            {{ trans('app.Ticket Details') }}
                            <span class="badge bg-light text-warning ms-2">{{ trans('app.Required') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">
                                    <i class="fas fa-tag text-purple me-1"></i>
                                    {{ trans('app.Category') }} <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id" required>
                                    <option value="">{{ trans('app.Select a Category') }}</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">
                                    <i class="fas fa-exclamation-triangle text-danger me-1"></i>
                                    {{ trans('app.Priority') }} <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('priority') is-invalid @enderror" 
                                        id="priority" name="priority" required>
                                    <option value="">{{ trans('app.Select Priority') }}</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>
                                        <i class="fas fa-arrow-down text-success me-1"></i>{{ trans('app.Low') }}
                                    </option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>
                                        <i class="fas fa-minus text-warning me-1"></i>{{ trans('app.Medium') }}
                                    </option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>
                                        <i class="fas fa-arrow-up text-danger me-1"></i>{{ trans('app.High') }}
                                    </option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>
                                        <i class="fas fa-exclamation text-danger me-1"></i>{{ trans('app.Urgent') }}
                                    </option>
                                </select>
                                @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">
                                <i class="fas fa-heading text-indigo me-1"></i>
                                {{ trans('app.Subject') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                   id="subject" name="subject" value="{{ old('subject') }}" 
                                   placeholder="{{ trans('app.Enter ticket subject') }}" required>
                            @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">
                                <i class="fas fa-align-left text-success me-1"></i>
                                {{ trans('app.Message') }} <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" name="content" rows="8"
                                      data-summernote="true" data-toolbar="standard"
                                      data-placeholder="{{ trans('app.Enter ticket message') }}"
                                      placeholder="{{ trans('app.Enter ticket message') }}" required>{{ old('content') }}</textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.use_the_rich_text_editor_to_format_your_message_with_headings_lists_links_and_more.') }}
                            </div>
                            @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Optional: Create Invoice for User -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice-dollar me-2"></i>
                            {{ trans('app.Create Invoice (optional)') }}
                            <span class="badge bg-light text-info ms-2">{{ trans('app.Optional') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="create_invoice" name="create_invoice" value="1"
                                   {{ old('create_invoice') ? 'checked' : '' }}>
                            <label class="form-check-label" for="create_invoice">
                                <i class="fas fa-file-invoice text-info me-1"></i>
                                {{ trans('app.Create invoice for this user') }}
                            </label>
                        </div>

                        <div id="invoice-section" class="hidden-field invoice-section">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="invoice_product_id" class="form-label">
                                        <i class="fas fa-box text-primary me-1"></i>
                                        {{ trans('app.Product') }}
                                    </label>
                                    <select class="form-select" id="invoice_product_id" name="invoice_product_id">
                                        <option value="">{{ trans('app.Select Product') }}</option>
                                        <option value="custom" {{ old('invoice_product_id') == 'custom' ? 'selected' : '' }}>
                                            {{ trans('app.Custom Invoice') }}
                                        </option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                                data-price="{{ $product->price }}" 
                                                data-duration="{{ $product->duration_days }}"
                                                {{ old('invoice_product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="billing_type" class="form-label">
                                        <i class="fas fa-credit-card text-warning me-1"></i>
                                        {{ trans('app.Billing Type') }}
                                    </label>
                                    <select class="form-select" id="billing_type" name="billing_type">
                                        <option value="one_time" {{ old('billing_type') == 'one_time' ? 'selected' : '' }}>
                                            {{ trans('app.One-time (no renewal)') }}
                                        </option>
                                        <option value="monthly" {{ old('billing_type') == 'monthly' ? 'selected' : '' }}>
                                            {{ trans('app.Monthly') }}
                                        </option>
                                        <option value="quarterly" {{ old('billing_type') == 'quarterly' ? 'selected' : '' }}>
                                            {{ trans('app.Quarterly') }}
                                        </option>
                                        <option value="semi_annual" {{ old('billing_type') == 'semi_annual' ? 'selected' : '' }}>
                                            {{ trans('app.Semi-annual') }}
                                        </option>
                                        <option value="annual" {{ old('billing_type') == 'annual' ? 'selected' : '' }}>
                                            {{ trans('app.Annual') }}
                                        </option>
                                        <option value="custom_recurring" {{ old('billing_type') == 'custom_recurring' ? 'selected' : '' }}>
                                            {{ trans('app.Custom (recurring)') }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="invoice_amount" class="form-label">
                                        <i class="fas fa-dollar-sign text-success me-1"></i>
                                        {{ trans('app.Amount') }}
                                    </label>
                                    <input type="number" class="form-control" id="invoice_amount" name="invoice_amount" 
                                           value="{{ old('invoice_amount') }}" placeholder="0.00" step="0.01" min="0.01" max="999999.99">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="invoice_due_date" class="form-label">
                                        <i class="fas fa-calendar text-info me-1"></i>
                                        {{ trans('app.Due Date') }}
                                    </label>
                                    <input type="date" class="form-control" id="invoice_due_date" name="invoice_due_date" 
                                           value="{{ old('invoice_due_date') }}">
                                </div>

                                <div class="col-md-6 mb-3" id="invoice-duration-group">
                                    <label for="invoice_duration_days" class="form-label">
                                        <i class="fas fa-clock text-purple me-1"></i>
                                        {{ trans('app.Duration (days)') }}
                                    </label>
                                    <input type="number" class="form-control" id="invoice_duration_days" name="invoice_duration_days" 
                                           value="{{ old('invoice_duration_days') }}" min="0" placeholder="e.g. 365">
                                </div>

                                <div class="col-md-6 mb-3" id="invoice-renewal-group" class="hidden-field">
                                    <label for="invoice_renewal_price" class="form-label">
                                        <i class="fas fa-redo text-warning me-1"></i>
                                        {{ trans('app.Renewal Price') }}
                                    </label>
                                    <input type="text" class="form-control" id="invoice_renewal_price" name="invoice_renewal_price" 
                                           value="{{ old('invoice_renewal_price') }}" placeholder="0.00">
                                </div>

                                <div class="col-md-6 mb-3" id="invoice-renewal-period-group" class="hidden-field">
                                    <label for="invoice_renewal_period_days" class="form-label">
                                        <i class="fas fa-calendar-alt text-danger me-1"></i>
                                        {{ trans('app.Renewal Period (days)') }}
                                    </label>
                                    <input type="number" class="form-control" id="invoice_renewal_period_days" name="invoice_renewal_period_days" 
                                           value="{{ old('invoice_renewal_period_days') }}" min="1" placeholder="e.g. 30">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="invoice_status" class="form-label">
                                        <i class="fas fa-flag text-primary me-1"></i>
                                        {{ trans('app.Status') }}
                                    </label>
                                    <select class="form-select" id="invoice_status" name="invoice_status">
                                        <option value="pending" {{ old('invoice_status') == 'pending' ? 'selected' : '' }}>
                                            {{ trans('app.Pending') }}
                                        </option>
                                        <option value="paid" {{ old('invoice_status') == 'paid' ? 'selected' : '' }}>
                                            {{ trans('app.Paid') }}
                                        </option>
                                        <option value="overdue" {{ old('invoice_status') == 'overdue' ? 'selected' : '' }}>
                                            {{ trans('app.Overdue') }}
                                        </option>
                                        <option value="cancelled" {{ old('invoice_status') == 'cancelled' ? 'selected' : '' }}>
                                            {{ trans('app.Cancelled') }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="invoice_notes" class="form-label">
                                    <i class="fas fa-sticky-note text-secondary me-1"></i>
                                    {{ trans('app.Notes') }}
                                </label>
                                <textarea class="form-control" id="invoice_notes" name="invoice_notes" rows="3"
                                          placeholder="{{ trans('app.Enter invoice notes') }}">{{ old('invoice_notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Ticket Preview -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-eye me-2"></i>
                            {{ trans('app.Ticket Preview') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div id="ticket-preview" class="p-3 rounded border">
                                <i class="fas fa-ticket-alt fs-1 text-primary mb-2"></i>
                                <h5 id="preview-subject">{{ trans('app.Ticket Subject') }}</h5>
                                <p id="preview-priority" class="text-muted small mb-0">{{ trans('app.Priority') }}</p>
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
                                    <p class="text-muted small mb-0">{{ trans('app.Users') }}</p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-success">{{ $categories->count() }}</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Categories') }}</p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-info">{{ $products->count() }}</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Products') }}</p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-warning">0</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Tickets') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Priority Guide -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ trans('app.Priority Guide') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="badge bg-success me-2">Low</span>
                            <small class="text-muted">{{ trans('app.General questions and minor issues') }}</small>
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-warning me-2">Medium</span>
                            <small class="text-muted">{{ trans('app.Standard support requests') }}</small>
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-danger me-2">High</span>
                            <small class="text-muted">{{ trans('app.Urgent issues affecting functionality') }}</small>
                        </div>
                        <div class="mb-0">
                            <span class="badge bg-dark me-2">Urgent</span>
                            <small class="text-muted">{{ trans('app.Critical issues requiring immediate attention') }}</small>
                        </div>
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
                            <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>{{ trans('app.Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ trans('app.Create Ticket') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
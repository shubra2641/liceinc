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
                                {{ trans('app.Create License') }}
                            </h1>
                            <p class="text-muted mb-0">{{ trans('app.Create a new license for a customer') }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.licenses.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ trans('app.Back to Licenses') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <form method="POST" action="{{ route('admin.licenses.store') }}" class="needs-validation" novalidate>
        @csrf

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- License Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-key me-2"></i>
                            {{ trans('app.License Information') }}
                            <span class="badge bg-light text-primary ms-2">{{ trans('app.Required') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_id" class="form-label">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    {{ trans('app.User (Owner)') }} <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('user_id') is-invalid @enderror" 
                                        id="user_id" name="user_id" required>
                                    <option value="">{{ trans('app.Select a User') }}</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" 
                                            data-name="{{ $user->name }}"
                                            data-email="{{ $user->email }}"
                                        {{ (old('user_id', $selectedUserId) == $user->id) ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="product_id" class="form-label">
                                    <i class="fas fa-box text-success me-1"></i>
                                    {{ trans('app.Product') }} <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('product_id') is-invalid @enderror" 
                                        id="product_id" name="product_id" required>
                                    <option value="">{{ trans('app.Select a Product') }}</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                        {{ old('product_id') == $product->id ? 'selected' : '' }}
                                        data-duration-days="{{ $product->duration_days ?? 365 }}"
                                        data-support-days="{{ $product->support_days ?? 365 }}"
                                        data-max-domains="{{ $product->max_domains ?? 1 }}"
                                        data-license-type="{{ $product->license_type ?? 'single' }}">
                                        {{ $product->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="license_key" class="form-label">
                                    <i class="fas fa-key text-primary me-1"></i>
                                    {{ trans('app.License Key') }}
                                    <small class="text-muted">({{ trans('app.Auto Generated') }})</small>
                                </label>
                                <input type="text" class="form-control" id="license_key_display"
                                       value="{{ old('license_key', 'Will be generated automatically') }}"
                                       readonly disabled>
                                <input type="hidden" name="license_key" id="license_key_hidden" value="{{ old('license_key') }}">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.License key will be auto-generated when creating the license') }}
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="license_type" class="form-label">
                                    <i class="fas fa-tag text-warning me-1"></i>
                                    {{ trans('app.License Type') }}
                                    <small class="text-muted">({{ trans('app.Auto-filled from product') }})</small>
                                </label>
                                <select class="form-select @error('license_type') is-invalid @enderror" 
                                        id="license_type" name="license_type">
                                    <option value="">{{ trans('app.Select License Type') }}</option>
                                    <option value="single" {{ old('license_type') == 'single' ? 'selected' : '' }}>
                                        {{ trans('app.Single Site') }}
                                    </option>
                                    <option value="multi" {{ old('license_type') == 'multi' ? 'selected' : '' }}>
                                        {{ trans('app.Multi Site') }}
                                    </option>
                                    <option value="developer" {{ old('license_type') == 'developer' ? 'selected' : '' }}>
                                        {{ trans('app.Developer') }}
                                    </option>
                                    <option value="extended" {{ old('license_type') == 'extended' ? 'selected' : '' }}>
                                        {{ trans('app.Extended') }}
                                    </option>
                                </select>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Will be auto-filled from selected product') }}
                                </div>
                                @error('license_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">
                                    <i class="fas fa-toggle-on text-info me-1"></i>
                                    {{ trans('app.Status') }} <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="">{{ trans('app.Select Status') }}</option>
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>
                                        {{ trans('app.Active') }}
                                    </option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
                                        {{ trans('app.Inactive') }}
                                    </option>
                                    <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>
                                        {{ trans('app.Suspended') }}
                                    </option>
                                    <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>
                                        {{ trans('app.Expired') }}
                                    </option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="max_domains" class="form-label">
                                    <i class="fas fa-globe text-success me-1"></i>
                                    {{ trans('app.Max Domains') }}
                                    <small class="text-muted">({{ trans('app.Auto-calculated') }})</small>
                                </label>
                                <input type="number" class="form-control @error('max_domains') is-invalid @enderror" 
                                       id="max_domains" name="max_domains" value="{{ old('max_domains', 1) }}" 
                                       min="1" placeholder="{{ trans('app.Maximum allowed domains') }}" readonly>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Calculated automatically based on license type') }}
                                </div>
                                @error('max_domains')
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
                                      placeholder="{{ trans('app.Enter any additional notes') }}">{{ old('notes') }}</textarea>
                            @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Invoice Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice-dollar me-2"></i>
                            {{ trans('app.Invoice Settings') }}
                            <span class="badge bg-light text-warning ms-2">{{ trans('app.Automatic') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="invoice_payment_status" class="form-label">
                                    <i class="fas fa-credit-card text-primary me-1"></i>
                                    {{ trans('app.Invoice Payment Status') }} <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('invoice_payment_status') is-invalid @enderror" 
                                        id="invoice_payment_status" name="invoice_payment_status" required>
                                    <option value="">{{ trans('app.Select Payment Status') }}</option>
                                    <option value="paid" {{ old('invoice_payment_status', 'paid') == 'paid' ? 'selected' : '' }}>
                                        <i class="fas fa-check-circle text-success me-1"></i>
                                        {{ trans('app.Paid') }}
                                    </option>
                                    <option value="pending" {{ old('invoice_payment_status') == 'pending' ? 'selected' : '' }}>
                                        <i class="fas fa-clock text-warning me-1"></i>
                                        {{ trans('app.Pending') }}
                                    </option>
                                </select>
                                @error('invoice_payment_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Choose whether the invoice should be marked as paid or pending') }}
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="invoice_due_date" class="form-label">
                                    <i class="fas fa-calendar-alt text-info me-1"></i>
                                    {{ trans('app.Invoice Due Date') }}
                                </label>
                                <input type="date" class="form-control @error('invoice_due_date') is-invalid @enderror" 
                                       id="invoice_due_date" name="invoice_due_date" value="{{ old('invoice_due_date') }}">
                                @error('invoice_due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Leave empty to use current date for paid invoices') }}
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>{{ trans('app.Note:') }}</strong> {{ trans('app.An invoice will be automatically created based on the product price and duration when the license is created.') }}
                        </div>
                    </div>
                </div>

                <!-- License Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog me-2"></i>
                            {{ trans('app.License Settings') }}
                            <span class="badge bg-light text-success ms-2">{{ trans('app.Optional') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label for="order_number" class="form-label">
                                    <i class="fas fa-receipt text-info me-1"></i>
                                    {{ trans('app.Order Number') }}
                                </label>
                                <input type="text" class="form-control @error('order_number') is-invalid @enderror" 
                                       id="order_number" name="order_number" value="{{ old('order_number') }}" 
                                       placeholder="{{ trans('app.Enter order number') }}">
                                @error('order_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="license_expires_at" class="form-label">
                                    <i class="fas fa-calendar-times text-danger me-1"></i>
                                    {{ trans('app.License Expires At') }}
                                    <small class="text-muted">({{ trans('app.Auto-calculated') }})</small>
                                </label>
                                <input type="date" class="form-control @error('license_expires_at') is-invalid @enderror" 
                                       id="license_expires_at" name="license_expires_at" value="{{ old('license_expires_at') }}" readonly>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Calculated from product duration') }}
                                </div>
                                @error('license_expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="support_expires_at" class="form-label">
                                    <i class="fas fa-headset text-success me-1"></i>
                                    {{ trans('app.Support Expires At') }}
                                    <small class="text-muted">({{ trans('app.Auto-calculated') }})</small>
                                </label>
                                <input type="date" class="form-control @error('support_expires_at') is-invalid @enderror" 
                                       id="support_expires_at" name="support_expires_at" value="{{ old('support_expires_at') }}" readonly>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Calculated from product support days') }}
                                </div>
                                @error('support_expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- License Preview -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-eye me-2"></i>
                            {{ trans('app.License Preview') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div id="license-preview" class="p-3 rounded border">
                                <i class="fas fa-key fs-1 text-primary mb-2"></i>
                                <h5 id="preview-product">{{ trans('app.Product Name') }}</h5>
                                <p id="preview-user" class="text-muted small mb-0">{{ trans('app.User Name') }}</p>
                                <span id="preview-status" class="badge bg-success mt-2">{{ trans('app.Active') }}</span>
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
                                    <h4 class="text-success">{{ $products->count() }}</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Products') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- License Information -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ trans('app.License Information') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-key text-primary me-1"></i>
                                {{ trans('app.License Key') }}
                            </label>
                            <p class="text-muted small" id="preview-license-key">{{ trans('app.Auto Generated') }}</p>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-action="generate-preview">
                                    <i class="fas fa-refresh me-1"></i>{{ trans('app.Generate Preview') }}
                                </button>
                            </div>
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
                                <i class="fas fa-globe text-info me-1"></i>
                                {{ trans('app.Max Domains') }}
                            </label>
                            <p class="text-muted small" id="preview-domains">1</p>
                        </div>
                    </div>
                </div>

                <!-- License Tips -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-lightbulb me-2"></i>
                            {{ trans('app.License Tips') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ trans('app.Choose the right license type') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ trans('app.Set appropriate expiration date') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ trans('app.Configure domain limits') }}
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
                            <a href="{{ route('admin.licenses.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>{{ trans('app.Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ trans('app.Create License') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>


@endsection

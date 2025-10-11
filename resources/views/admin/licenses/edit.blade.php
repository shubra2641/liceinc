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
                                <i class="fas fa-edit text-primary me-2"></i>
                                {{ trans('app.Edit License') }}
                            </h1>
                            <p class="text-muted mb-0">{{ $license->license_key }}</p>
            </div>
                        <div>
                            <a href="{{ route('admin.licenses.show', $license) }}" class="btn btn-info me-2">
                                <i class="fas fa-eye me-1"></i>
                                {{ trans('app.View License') }}
                            </a>
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

    <form method="POST" action="{{ route('admin.licenses.update', $license) }}" class="needs-validation" novalidate>
                @csrf
                @method('PUT')

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
                                        {{ old('user_id', $license->user_id) == $user->id ? 'selected' : '' }}>
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
                                        {{ old('product_id', $license->product_id) == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                                    <option value="single" {{ old('license_type', $license->license_type) == 'single' ? 'selected' : '' }}>
                                        {{ trans('app.Single Site') }}
                                    </option>
                                    <option value="multi" {{ old('license_type', $license->license_type) == 'multi' ? 'selected' : '' }}>
                                        {{ trans('app.Multi Site') }}
                                    </option>
                                    <option value="developer" {{ old('license_type', $license->license_type) == 'developer' ? 'selected' : '' }}>
                                        {{ trans('app.Developer') }}
                                    </option>
                                    <option value="extended" {{ old('license_type', $license->license_type) == 'extended' ? 'selected' : '' }}>
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
                                    <option value="active" {{ old('status', $license->status) == 'active' ? 'selected' : '' }}>
                                {{ trans('app.Active') }}
                            </option>
                                    <option value="inactive" {{ old('status', $license->status) == 'inactive' ? 'selected' : '' }}>
                                {{ trans('app.Inactive') }}
                            </option>
                                    <option value="suspended" {{ old('status', $license->status) == 'suspended' ? 'selected' : '' }}>
                                {{ trans('app.Suspended') }}
                            </option>
                                    <option value="expired" {{ old('status', $license->status) == 'expired' ? 'selected' : '' }}>
                                {{ trans('app.Expired') }}
                            </option>
                        </select>
                        @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                            <div class="col-md-6 mb-3">
                                <label for="expires_at" class="form-label">
                                    <i class="fas fa-calendar text-danger me-1"></i>
                                    {{ trans('app.Expires At') }}
                                </label>
                                <input type="datetime-local" class="form-control @error('expires_at') is-invalid @enderror" 
                                       id="expires_at" name="expires_at" 
                                       value="{{ old('expires_at', $license->expires_at ? $license->expires_at->format('Y-m-d\TH:i') : '') }}">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Leave empty for lifetime license') }}
                                </div>
                        @error('expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                            <div class="col-md-6 mb-3">
                                <label for="max_domains" class="form-label">
                                    <i class="fas fa-globe text-success me-1"></i>
                                    {{ trans('app.Max Domains') }}
                                </label>
                                <input type="number" class="form-control @error('max_domains') is-invalid @enderror" 
                                       id="max_domains" name="max_domains" value="{{ old('max_domains', $license->max_domains) }}" 
                                       min="1" placeholder="{{ trans('app.Maximum allowed domains') }}">
                                @if($license->hasReachedDomainLimit())
                                    <div class="form-text text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ trans('app.Warning: This license has reached its domain limit') }}
                                    </div>
                                @endif
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
                        placeholder="{{ trans('app.Enter any additional notes') }}">{{ old('notes', $license->notes) }}</textarea>
                    @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                <label for="purchase_code" class="form-label">
                                    <i class="fas fa-shopping-cart text-primary me-1"></i>
                                    {{ trans('app.Purchase Code') }}
                                </label>
                                <input type="text" class="form-control @error('purchase_code') is-invalid @enderror" 
                                       id="purchase_code" name="purchase_code" value="{{ old('purchase_code', $license->purchase_code) }}" 
                                       placeholder="{{ trans('app.Enter purchase code') }}">
                                @error('purchase_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="order_number" class="form-label">
                                    <i class="fas fa-receipt text-info me-1"></i>
                                    {{ trans('app.Order Number') }}
                                </label>
                                <input type="text" class="form-control @error('order_number') is-invalid @enderror" 
                                       id="order_number" name="order_number" value="{{ old('order_number', $license->order_number) }}" 
                                       placeholder="{{ trans('app.Enter order number') }}">
                                @error('order_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="license_key" class="form-label">
                                    <i class="fas fa-key text-warning me-1"></i>
                                    {{ trans('app.License Key') }}
                                </label>
                                <input type="text" class="form-control @error('license_key') is-invalid @enderror" 
                                       id="license_key" name="license_key" value="{{ old('license_key', $license->license_key) }}" 
                                       placeholder="{{ trans('app.Enter license key') }}">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Unique license key') }}
                                </div>
                                @error('license_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                            <div class="col-md-6 mb-3">
                                <label for="support_expires_at" class="form-label">
                                    <i class="fas fa-headset text-success me-1"></i>
                                    {{ trans('app.Support Expires At') }}
                                </label>
                                <input type="datetime-local" class="form-control @error('support_expires_at') is-invalid @enderror" 
                                       id="support_expires_at" name="support_expires_at" 
                                       value="{{ old('support_expires_at', $license->support_expires_at ? $license->support_expires_at->format('Y-m-d\TH:i') : '') }}">
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
                                <h5 id="preview-product">{{ $license->product->name ?? trans('app.Product Name') }}</h5>
                                <p id="preview-user" class="text-muted small mb-0">{{ $license->user->name ?? trans('app.User Name') }}</p>
                                <span id="preview-status" class="badge bg-{{ $license->status == 'active' ? 'success' : 'danger' }} mt-2">
                                    {{ trans('app.' . ucfirst($license->status)) }}
                                </span>
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
                            <div class="col-4 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-primary">{{ $license->active_domains_count }}</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Used Domains') }}</p>
                                </div>
                            </div>
                            <div class="col-4 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-success">{{ $license->max_domains ?? 1 }}</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Max Domains') }}</p>
                                </div>
                            </div>
                            <div class="col-4 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-info">{{ $license->remaining_domains }}</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Remaining') }}</p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-info">{{ $license->created_at->format('M Y') }}</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Created') }}</p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-warning">{{ $license->updated_at->format('M Y') }}</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Updated') }}</p>
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
                            <p class="text-muted small" id="preview-license-key">{{ $license->license_key }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar text-success me-1"></i>
                                {{ trans('app.Created At') }}
                            </label>
                            <p class="text-muted small">{{ $license->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold">
                                <i class="fas fa-globe text-info me-1"></i>
                                {{ trans('app.Max Domains') }}
                            </label>
                            <p class="text-muted small" id="preview-domains">{{ $license->max_domains ?? 1 }}</p>
                </div>
        </div>
    </div>

                <!-- License Actions -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>
                            {{ trans('app.License Actions') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary copy-btn" data-text="{{ $license->license_key }}">
                                <i class="fas fa-copy me-1"></i>
                                {{ trans('app.Copy License Key') }}
                            </button>
                            <a href="{{ route('admin.licenses.show', $license) }}" class="btn btn-outline-info">
                                <i class="fas fa-eye me-1"></i>
                                {{ trans('app.View License') }}
                            </a>
                            @if($license->user)
                            <a href="{{ route('admin.users.show', $license->user) }}" class="btn btn-outline-success">
                                <i class="fas fa-user me-1"></i>
                                {{ trans('app.View User') }}
                            </a>
                            @endif
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
                            <a href="{{ route('admin.licenses.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>{{ trans('app.Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ trans('app.Save Changes') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Danger Zone -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ trans('app.Danger Zone') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">{{ trans('app.Delete License Warning') }}</p>
                    <form method="post" action="{{ route('admin.licenses.destroy', $license) }}" 
                          data-confirm="delete-license">
                @csrf
                @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash me-1"></i>{{ trans('app.Delete License') }}
                </button>
            </form>
        </div>
    </div>
</div>
    </div>
</div>

@endsection
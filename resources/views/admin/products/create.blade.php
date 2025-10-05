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
                                {{ trans('app.Create New Product') }}
                            </h1>
                            <p class="text-muted mb-0">{{ trans('app.Add New Product') }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ trans('app.Back to Products') }}
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
            <div class="alert alert-danger border-0 shadow-sm">
                <div class="d-flex">
                    <i class="fas fa-exclamation-triangle text-danger mt-1 me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-2">{{ trans('app.Validation Errors') }}</h5>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <form method="post" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
        @csrf
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Basic Information (Required) -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            {{ trans('app.Basic Information') }}
                            <span class="badge bg-danger ms-2">{{ trans('app.Required') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-tag me-1"></i>
                                    {{ trans('app.Product Name') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required
                                       placeholder="{{ trans('app.Enter Product Name') }}">
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">
                                    <i class="fas fa-folder me-1"></i>
                                    {{ trans('app.Category') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id" required>
                                    <option value="">{{ trans('app.Select Category') }}</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="programming_language" class="form-label">
                                    <i class="fas fa-code me-1"></i>
                                    {{ trans('app.Programming Language') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('programming_language') is-invalid @enderror" 
                                        id="programming_language" name="programming_language" required>
                                    <option value="">{{ trans('app.Select Programming Language') }}</option>
                                    @foreach($programmingLanguages as $language)
                                    <option value="{{ $language->id }}" {{ old('programming_language') == $language->id ? 'selected' : '' }}>
                                        {{ $language->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('programming_language')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">
                                    <i class="fas fa-dollar-sign me-1"></i>
                                    {{ trans('app.Price') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" 
                                           id="price" name="price" value="{{ old('price') }}" required>
                                    @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="slug" class="form-label">
                                    <i class="fas fa-link me-1"></i>
                                    {{ trans('app.Slug') }}
                                </label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                       id="slug" name="slug" value="{{ old('slug') }}"
                                       placeholder="{{ trans('app.Enter product slug') }}">
                                @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="slug" class="form-label">
                                    <i class="fas fa-link me-1"></i>
                                    {{ trans('app.Slug') }}
                                </label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                       id="slug" name="slug" value="{{ old('slug') }}"
                                       placeholder="{{ trans('app.Enter product slug') }}">
                                @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="version" class="form-label">
                                    <i class="fas fa-code-branch me-1"></i>
                                    {{ trans('app.Version') }}
                                </label>
                                <input type="text" class="form-control @error('version') is-invalid @enderror" 
                                       id="version" name="version" value="{{ old('version') }}"
                                       placeholder="1.0.0">
                                @error('version')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-globe me-1"></i>
                                    {{ trans('app.Requires Domain') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="requires_domain" value="1"
                                               id="requires_domain_yes" {{ old('requires_domain') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requires_domain_yes">
                                            {{ trans('app.Yes') }}
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="requires_domain" value="0"
                                               id="requires_domain_no" {{ old('requires_domain') == '0' || !old('requires_domain') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requires_domain_no">
                                            {{ trans('app.No') }}
                                        </label>
                                    </div>
                                </div>
                                @error('requires_domain')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="license_type" class="form-label">
                                    <i class="fas fa-key me-1"></i>
                                    {{ trans('app.License Type') }}
                                </label>
                                <select class="form-select @error('license_type') is-invalid @enderror" 
                                        id="license_type" name="license_type">
                                    <option value="">{{ trans('app.Select License Type') }}</option>
                                    <option value="single" {{ old('license_type') == 'single' ? 'selected' : '' }}>{{ trans('app.Single Site') }}</option>
                                    <option value="multi" {{ old('license_type') == 'multi' ? 'selected' : '' }}>{{ trans('app.Multi Site') }}</option>
                                    <option value="developer" {{ old('license_type') == 'developer' ? 'selected' : '' }}>{{ trans('app.Developer') }}</option>
                                    <option value="extended" {{ old('license_type') == 'extended' ? 'selected' : '' }}>{{ trans('app.Extended') }}</option>
                                </select>
                                @error('license_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left me-1"></i>
                                    {{ trans('app.Product Description') }}
                                </label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4"
                                          data-summernote="true" data-toolbar="standard"
                                          data-placeholder="{{ trans('app.Enter Product Description') }}"
                                          placeholder="{{ trans('app.Enter Product Description') }}">{{ old('description') }}</textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Use the rich text editor to format your product description with headings, lists, links, and more.') }}
                                </div>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information (Optional) -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-plus-circle text-info me-2"></i>
                            {{ trans('app.Additional Information') }}
                            <span class="badge bg-info ms-2">{{ trans('app.Optional') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="envato_item_id" class="form-label">
                                    <i class="fab fa-envato me-1"></i>
                                    {{ trans('app.Envato Item ID') }}
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('envato_item_id') is-invalid @enderror" 
                                           id="envato_item_id" name="envato_item_id" value="{{ old('envato_item_id') }}"
                                           placeholder="{{ trans('app.Product Envato Item ID') }}">
                                    <button type="button" class="btn btn-outline-secondary" id="fetch-envato-data">
                                        <i class="fas fa-download me-1"></i>
                                        {{ trans('app.Fetch') }}
                                    </button>
                                </div>
                                <div id="envato-loading" class="hidden mt-2">
                                    <i class="fas fa-spinner fa-spin text-primary me-2"></i>
                                    {{ trans('app.Fetching data from Envato...') }}
                                </div>
                                <div id="envato-error" class="hidden mt-2 text-danger small"></div>
                                @error('envato_item_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="purchase_url_envato" class="form-label">
                                    <i class="fab fa-envato me-1"></i>
                                    {{ trans('app.Purchase on Envato URL') }}
                                </label>
                                <input type="url" class="form-control @error('purchase_url_envato') is-invalid @enderror" 
                                       id="purchase_url_envato" name="purchase_url_envato" value="{{ old('purchase_url_envato') }}"
                                       placeholder="https://themeforest.net/item/...">
                                @error('purchase_url_envato')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="purchase_url_buy" class="form-label">
                                    <i class="fas fa-shopping-cart me-1"></i>
                                    {{ trans('app.Buy Now URL') }}
                                </label>
                                <input type="url" class="form-control @error('purchase_url_buy') is-invalid @enderror" 
                                       id="purchase_url_buy" name="purchase_url_buy" value="{{ old('purchase_url_buy') }}"
                                       placeholder="https://yourshop.example/checkout/...">
                                @error('purchase_url_buy')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="support_days" class="form-label">
                                    <i class="fas fa-headset me-1"></i>
                                    {{ trans('app.Support Days') }}
                                </label>
                                <input type="number" class="form-control @error('support_days') is-invalid @enderror" 
                                       id="support_days" name="support_days" value="{{ old('support_days', 180) }}" min="0"
                                       placeholder="180">
                                <div class="form-text">{{ trans('app.Number of Support Days') }}</div>
                                @error('support_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="stock_quantity" class="form-label">
                                    <i class="fas fa-boxes me-1"></i>
                                    {{ trans('app.Stock Quantity') }}
                                </label>
                                <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" 
                                       id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', -1) }}" min="-1"
                                       placeholder="-1">
                                <div class="form-text">-1 = {{ trans('app.Unlimited Stock') }}</div>
                                @error('stock_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="renewal_price" class="form-label">
                                    <i class="fas fa-redo me-1"></i>
                                    {{ trans('app.Renewal Price') }}
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control @error('renewal_price') is-invalid @enderror" 
                                           id="renewal_price" name="renewal_price" value="{{ old('renewal_price') }}"
                                           placeholder="0.00">
                                </div>
                                <div class="form-text">{{ trans('app.Leave empty to use regular price') }}</div>
                                @error('renewal_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="renewal_period" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ trans('app.Renewal Period') }}
                                </label>
                                <select class="form-select @error('renewal_period') is-invalid @enderror" 
                                        id="renewal_period" name="renewal_period">
                                    <option value="">{{ trans('app.Select Renewal Period') }}</option>
                                    <option value="monthly" {{ old('renewal_period') == 'monthly' ? 'selected' : '' }}>{{ trans('app.Monthly') }}</option>
                                    <option value="quarterly" {{ old('renewal_period') == 'quarterly' ? 'selected' : '' }}>{{ trans('app.Quarterly') }}</option>
                                    <option value="semi-annual" {{ old('renewal_period') == 'semi-annual' ? 'selected' : '' }}>{{ trans('app.Semi-Annual') }}</option>
                                    <option value="annual" {{ old('renewal_period') == 'annual' ? 'selected' : '' }}>{{ trans('app.Annual') }}</option>
                                    <option value="three-years" {{ old('renewal_period') == 'three-years' ? 'selected' : '' }}>{{ trans('app.Three Years') }}</option>
                                    <option value="lifetime" {{ old('renewal_period') == 'lifetime' ? 'selected' : '' }}>{{ trans('app.Lifetime') }}</option>
                                </select>
                                @error('renewal_period')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="duration_days" class="form-label">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ trans('app.License Duration (Days)') }}
                                </label>
                                <input type="number" class="form-control @error('duration_days') is-invalid @enderror" 
                                       id="duration_days" name="duration_days" value="{{ old('duration_days', 365) }}" min="1"
                                       placeholder="365">
                                <div class="form-text">{{ trans('app.License validity period in days') }}</div>
                                @error('duration_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tax_rate" class="form-label">
                                    <i class="fas fa-percentage me-1"></i>
                                    {{ trans('app.Tax Rate (%)') }}
                                </label>
                                <input type="number" step="0.01" class="form-control @error('tax_rate') is-invalid @enderror" 
                                       id="tax_rate" name="tax_rate" value="{{ old('tax_rate', 0) }}" min="0" max="100"
                                       placeholder="0.00">
                                <div class="form-text">{{ trans('app.Tax percentage applied to product price') }}</div>
                                @error('tax_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="extended_support_price" class="form-label">
                                    <i class="fas fa-headset me-1"></i>
                                    {{ trans('app.Extended Support Price') }}
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control @error('extended_support_price') is-invalid @enderror" 
                                           id="extended_support_price" name="extended_support_price" value="{{ old('extended_support_price') }}"
                                           placeholder="0.00">
                                </div>
                                <div class="form-text">{{ trans('app.Price for extended support') }}</div>
                                @error('extended_support_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="extended_support_days" class="form-label">
                                    <i class="fas fa-calendar-plus me-1"></i>
                                    {{ trans('app.Extended Support Days') }}
                                </label>
                                <input type="number" class="form-control @error('extended_support_days') is-invalid @enderror" 
                                       id="extended_support_days" name="extended_support_days" value="{{ old('extended_support_days') }}" min="0"
                                       placeholder="365">
                                <div class="form-text">{{ trans('app.Additional support days') }}</div>
                                @error('extended_support_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="renewal_reminder_days" class="form-label">
                                    <i class="fas fa-bell me-1"></i>
                                    {{ trans('app.Renewal Reminder Days') }}
                                </label>
                                <input type="number" class="form-control @error('renewal_reminder_days') is-invalid @enderror" 
                                       id="renewal_reminder_days" name="renewal_reminder_days" value="{{ old('renewal_reminder_days', 30) }}" min="1"
                                       placeholder="30">
                                <div class="form-text">{{ trans('app.Days before expiry to send reminder') }}</div>
                                @error('renewal_reminder_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Status') }}
                                </label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status">
                                    <option value="">{{ trans('app.Select Status') }}</option>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>{{ trans('app.Active') }}</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>{{ trans('app.Inactive') }}</option>
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>{{ trans('app.Draft') }}</option>
                                    <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>{{ trans('app.Archived') }}</option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">
                                    <i class="fas fa-warehouse me-1"></i>
                                    {{ trans('app.Stock') }}
                                </label>
                                <input type="number" class="form-control @error('stock') is-invalid @enderror" 
                                       id="stock" name="stock" value="{{ old('stock', -1) }}" min="-1"
                                       placeholder="-1">
                                <div class="form-text">-1 = {{ trans('app.Unlimited Stock') }}</div>
                                @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="supported_until" class="form-label">
                                    <i class="fas fa-calendar-times me-1"></i>
                                    {{ trans('app.Supported Until') }}
                                </label>
                                <input type="date" class="form-control @error('supported_until') is-invalid @enderror" 
                                       id="supported_until" name="supported_until" value="{{ old('supported_until') }}" readonly>
                                <div class="form-text">{{ trans('app.Support end date') }} ({{ trans('app.Auto-calculated based on support days') }})</div>
                                @error('supported_until')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="extended_supported_until" class="form-label">
                                    <i class="fas fa-calendar-plus me-1"></i>
                                    {{ trans('app.Extended Supported Until') }}
                                </label>
                                <input type="date" class="form-control @error('extended_supported_until') is-invalid @enderror" 
                                       id="extended_supported_until" name="extended_supported_until" value="{{ old('extended_supported_until') }}" readonly>
                                <div class="form-text">
                                    {{ trans('app.Extended support end date') }} ({{ trans('app.Auto-calculated based on renewal period') }})
                                    <br><small class="text-muted">{{ trans('app.For lifetime renewal, this field will be empty') }}</small>
                                </div>
                                @error('extended_supported_until')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features and Requirements -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-star text-warning me-2"></i>
                            {{ trans('app.Features Requirements') }}
                            <span class="badge bg-info ms-2">{{ trans('app.Optional') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="features" class="form-label">
                                    <i class="fas fa-list-check me-1"></i>
                                    {{ trans('app.Features') }}
                                </label>
                                <textarea class="form-control @error('features') is-invalid @enderror" 
                                          id="features" name="features" rows="6"
                                          data-summernote="true" data-toolbar="basic"
                                          data-placeholder="{{ trans('app.List product features with formatting') }}"
                                          placeholder="{{ trans('app.List product features with formatting') }}">{{ old('features') }}</textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Use lists and formatting to highlight product features.') }}
                                </div>
                                @error('features')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="requirements" class="form-label">
                                    <i class="fas fa-clipboard-check me-1"></i>
                                    {{ trans('app.Requirements') }}
                                </label>
                                <textarea class="form-control @error('requirements') is-invalid @enderror" 
                                          id="requirements" name="requirements" rows="6"
                                          data-summernote="true" data-toolbar="basic"
                                          data-placeholder="{{ trans('app.List system requirements with formatting') }}"
                                          placeholder="{{ trans('app.List system requirements with formatting') }}">{{ old('requirements') }}</textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Use lists and formatting to clearly show system requirements.') }}
                                </div>
                                @error('requirements')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="installation_guide" class="form-label">
                                    <i class="fas fa-book me-1"></i>
                                    {{ trans('app.Installation Guide') }}
                                </label>
                                <textarea class="form-control @error('installation_guide') is-invalid @enderror" 
                                          id="installation_guide" name="installation_guide" rows="4"
                                          data-summernote="true" data-toolbar="standard"
                                          data-placeholder="{{ trans('app.Create step-by-step installation guide') }}"
                                          placeholder="{{ trans('app.Create step-by-step installation guide') }}">{{ old('installation_guide') }}</textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.create_detailed_installation_instructions_with_headings_lists_and_formatting.') }}
                                </div>
                                @error('installation_guide')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Files -->
                <div class="card border-0 shadow-sm mb-4 product-files-section" id="product-files-section">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-upload text-success me-2"></i>
                            {{ trans('app.Product Files') }}
                            <span class="badge bg-success ms-2">{{ trans('app.Required for Downloadable Products') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ trans('app.Product files will be encrypted and stored securely. You can add files after creating the product.') }}
                        </div>
                        
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="product_files" class="form-label">
                                    <i class="fas fa-file-upload me-1"></i>
                                    {{ trans('app.Product Files') }}
                                </label>
                                <input type="file" class="form-control @error('product_files') is-invalid @enderror" 
                                       id="product_files" name="product_files[]" accept=".zip,.rar,.pdf,.php,.js,.css,.html,.json,.xml,.sql,.jpg,.jpeg,.png,.gif,.svg" multiple>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Allowed file types: ZIP, RAR, PDF, PHP, JS, CSS, HTML, JSON, XML, SQL, Images') }}
                                    <br>
                                    <i class="fas fa-shield-alt me-1"></i>
                                    {{ trans('app.Maximum file size: 50MB per file') }}
                                </div>
                                @error('product_files')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12">
                                <div class="form-text">
                                    <i class="fas fa-lightbulb me-1"></i>
                                    <strong>{{ trans('app.Tip:') }}</strong> {{ trans('app.You can also add files after creating the product by going to the product files management page.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Media and Assets -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-images text-pink me-2"></i>
                            {{ trans('app.Media and Assets') }}
                            <span class="badge bg-info ms-2">{{ trans('app.Optional') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label">
                                    <i class="fas fa-image me-1"></i>
                                    {{ trans('app.Main Image') }}
                                </label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                       id="image" name="image" accept="image/*">
                                <div class="form-text">{{ trans('app.Recommended Size') }}</div>
                                @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="gallery_images" class="form-label">
                                    <i class="fas fa-images me-1"></i>
                                    {{ trans('app.Gallery Images') }}
                                </label>
                                <input type="file" class="form-control @error('gallery_images') is-invalid @enderror" 
                                       id="gallery_images" name="gallery_images[]" accept="image/*" multiple>
                                <div class="form-text">{{ trans('app.Select Multiple Images') }}</div>
                                @error('gallery_images')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO Optimization -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-search text-indigo me-2"></i>
                            {{ trans('app.SEO') }}
                            <span class="badge bg-info ms-2">{{ trans('app.Optional') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="meta_title" class="form-label">
                                    <i class="fas fa-heading me-1"></i>
                                    {{ trans('app.Meta Title') }}
                                </label>
                                <input type="text" class="form-control @error('meta_title') is-invalid @enderror" 
                                       id="meta_title" name="meta_title" value="{{ old('meta_title') }}" maxlength="255"
                                       placeholder="{{ trans('app.SEO Title Placeholder') }}">
                                @error('meta_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tags" class="form-label">
                                    <i class="fas fa-tags me-1"></i>
                                    {{ trans('app.Tags') }}
                                </label>
                                <input type="text" class="form-control @error('tags') is-invalid @enderror" 
                                       id="tags" name="tags" value="{{ old('tags') }}"
                                       placeholder="{{ trans('app.Tags Comma Separated') }}">
                                @error('tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="meta_description" class="form-label">
                                    <i class="fas fa-file-alt me-1"></i>
                                    {{ trans('app.Meta Description') }}
                                </label>
                                <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                          id="meta_description" name="meta_description" rows="3" maxlength="500"
                                          placeholder="{{ trans('app.SEO Description Placeholder') }}">{{ old('meta_description') }}</textarea>
                                @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Product Settings -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cogs me-2"></i>
                            {{ trans('app.Product Settings') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <i class="fas fa-toggle-on me-1"></i>
                                    {{ trans('app.Active') }}
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                       value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">
                                    <i class="fas fa-star me-1"></i>
                                    {{ trans('app.Featured') }}
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_downloadable" name="is_downloadable" 
                                       value="1" {{ old('is_downloadable', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_downloadable">
                                    <i class="fas fa-download me-1"></i>
                                    {{ trans('app.Downloadable') }}
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_popular" name="is_popular" 
                                       value="1" {{ old('is_popular') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_popular">
                                    <i class="fas fa-fire me-1"></i>
                                    {{ trans('app.Popular') }}
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="auto_renewal" name="auto_renewal" 
                                       value="1" {{ old('auto_renewal') ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_renewal">
                                    <i class="fas fa-sync me-1"></i>
                                    {{ trans('app.Auto Renewal') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tools me-2"></i>
                            {{ trans('app.Actions') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ trans('app.Create Product') }}
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                {{ trans('app.Cancel') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>


@endsection

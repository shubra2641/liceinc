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
                                {{ trans('app.Edit Product') }}
                            </h1>
                            <p class="text-muted mb-0">{{ $product->name }}</p>
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

    

    <form method="post" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ trans('app.Basic Information') }}
                            <span class="badge bg-light text-primary ms-2">{{ trans('app.Required') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-tag text-primary me-1"></i>
                                    {{ trans('app.Product Name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $product->name) }}" 
                                       placeholder="{{ trans('app.Enter Product Name') }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">
                                    <i class="fas fa-folder text-success me-1"></i>
                                    {{ trans('app.Category') }} <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id" required>
                                    <option value="">{{ trans('app.Select Category') }}</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
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
                                    <i class="fas fa-code text-purple me-1"></i>
                                    {{ trans('app.Programming Language') }} <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('programming_language') is-invalid @enderror" 
                                        id="programming_language" name="programming_language" required>
                                    <option value="">{{ trans('app.Select Programming Language') }}</option>
                                    @foreach($programmingLanguages as $language)
                                    <option value="{{ $language->id }}"
                                        {{ old('programming_language', $product->programming_language) == $language->id ? 'selected' : '' }}>
                                        <i class="{{ $language->icon ?? 'fas fa-code' }} me-2"></i>{{ $language->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('programming_language')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-globe text-warning me-1"></i>
                                    {{ trans('app.Requires Domain') }} <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="requires_domain" 
                                               id="requires_domain_yes" value="1"
                                               {{ old('requires_domain', $product->requires_domain) == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requires_domain_yes">
                                            {{ trans('app.Yes') }}
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="requires_domain" 
                                               id="requires_domain_no" value="0"
                                               {{ old('requires_domain', $product->requires_domain) == 0 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requires_domain_no">
                                            {{ trans('app.No') }}
                                        </label>
                                    </div>
                                </div>
                                @error('requires_domain')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left text-secondary me-1"></i>
                                {{ trans('app.Product Description') }}
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4"
                                      data-summernote="true" data-toolbar="standard"
                                      data-placeholder="{{ trans('app.Enter Product Description') }}"
                                      placeholder="{{ trans('app.Enter Product Description') }}">{{ old('description', $product->description) }}</textarea>
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

                <!-- Additional Information -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-plus-circle me-2"></i>
                            {{ trans('app.Additional Information') }}
                            <span class="badge bg-light text-info ms-2">{{ trans('app.Optional') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="price" class="form-label">
                                    <i class="fas fa-dollar-sign text-success me-1"></i>
                                    {{ trans('app.Price') }}
                                </label>
                                <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                       id="price" name="price" value="{{ old('price', $product->price) }}" 
                                       step="0.01" min="0" placeholder="0.00">
                                @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="slug" class="form-label">
                                    <i class="fas fa-link text-info me-1"></i>
                                    {{ trans('app.Slug') }}
                                </label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                       id="slug" name="slug" value="{{ old('slug', $product->slug) }}" 
                                       placeholder="{{ trans('app.Enter product slug') }}">
                                @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="version" class="form-label">
                                    <i class="fas fa-code-branch text-primary me-1"></i>
                                    {{ trans('app.Version') }}
                                </label>
                                <input type="text" class="form-control @error('version') is-invalid @enderror" 
                                       id="version" name="version" value="{{ old('version', $product->version) }}" 
                                       placeholder="1.0.0">
                                @error('version')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="envato_item_id" class="form-label">
                                    <i class="fab fa-envato text-warning me-1"></i>
                                    {{ trans('app.Envato Item ID') }}
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('envato_item_id') is-invalid @enderror" 
                                           id="envato_item_id" name="envato_item_id" 
                                           value="{{ old('envato_item_id', $product->envato_item_id) }}"
                                           placeholder="{{ trans('app.Product Envato Item ID') }}">
                                    <button type="button" class="btn btn-outline-secondary" id="fetch-envato-data">
                                        <i class="fas fa-download me-1"></i>{{ trans('app.Fetch') }}
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

                            <div class="col-md-6 mb-3">
                                <label for="purchase_url_envato" class="form-label">
                                    <i class="fab fa-envato text-warning me-1"></i>
                                    {{ trans('app.Purchase on Envato URL') }}
                                </label>
                                <input type="url" class="form-control @error('purchase_url_envato') is-invalid @enderror" 
                                       id="purchase_url_envato" name="purchase_url_envato" 
                                       value="{{ old('purchase_url_envato', $product->purchase_url_envato) }}"
                                       placeholder="https://themeforest.net/item/...">
                                @error('purchase_url_envato')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="purchase_url_buy" class="form-label">
                                    <i class="fas fa-shopping-cart text-success me-1"></i>
                                    {{ trans('app.Buy Now URL') }}
                                </label>
                                <input type="url" class="form-control @error('purchase_url_buy') is-invalid @enderror" 
                                       id="purchase_url_buy" name="purchase_url_buy" 
                                       value="{{ old('purchase_url_buy', $product->purchase_url_buy) }}"
                                       placeholder="https://yourshop.example/checkout/...">
                                @error('purchase_url_buy')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="support_days" class="form-label">
                                    <i class="fas fa-headset text-danger me-1"></i>
                                    {{ trans('app.Support Days') }}
                                </label>
                                <input type="number" class="form-control @error('support_days') is-invalid @enderror" 
                                       id="support_days" name="support_days" 
                                       value="{{ old('support_days', $product->support_days) }}" 
                                       min="0" placeholder="180">
                                <div class="form-text">{{ trans('app.Number of Support Days') }}</div>
                                @error('support_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="stock_quantity" class="form-label">
                                    <i class="fas fa-boxes text-warning me-1"></i>
                                    {{ trans('app.Stock Quantity') }}
                                </label>
                                <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" 
                                       id="stock_quantity" name="stock_quantity" 
                                       value="{{ old('stock_quantity', $product->stock_quantity) }}" 
                                       min="-1" placeholder="-1">
                                <div class="form-text">-1 = {{ trans('app.Unlimited Stock') }}</div>
                                @error('stock_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="license_type" class="form-label">
                                    <i class="fas fa-key text-primary me-1"></i>
                                    {{ trans('app.License Type') }}
                                </label>
                                <select class="form-select @error('license_type') is-invalid @enderror" 
                                        id="license_type" name="license_type">
                                    <option value="">{{ trans('app.Select License Type') }}</option>
                                    <option value="single" {{ old('license_type', $product->license_type) == 'single' ? 'selected' : '' }}>{{ trans('app.Single Site') }}</option>
                                    <option value="multi" {{ old('license_type', $product->license_type) == 'multi' ? 'selected' : '' }}>{{ trans('app.Multi Site') }}</option>
                                    <option value="developer" {{ old('license_type', $product->license_type) == 'developer' ? 'selected' : '' }}>{{ trans('app.Developer') }}</option>
                                    <option value="extended" {{ old('license_type', $product->license_type) == 'extended' ? 'selected' : '' }}>{{ trans('app.Extended') }}</option>
                                </select>
                                @error('license_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="renewal_price" class="form-label">
                                    <i class="fas fa-redo text-success me-1"></i>
                                    {{ trans('app.Renewal Price') }}
                                </label>
                                <input type="number" step="0.01" class="form-control @error('renewal_price') is-invalid @enderror" 
                                       id="renewal_price" name="renewal_price" 
                                       value="{{ old('renewal_price', $product->renewal_price) }}"
                                       placeholder="0.00">
                                <div class="form-text">{{ trans('app.Leave empty to use regular price') }}</div>
                                @error('renewal_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="renewal_period" class="form-label">
                                    <i class="fas fa-calendar text-info me-1"></i>
                                    {{ trans('app.Renewal Period') }}
                                </label>
                                <select class="form-select @error('renewal_period') is-invalid @enderror" 
                                        id="renewal_period" name="renewal_period">
                                    <option value="">{{ trans('app.Select Renewal Period') }}</option>
                                    <option value="monthly" {{ old('renewal_period', $product->renewal_period) == 'monthly' ? 'selected' : '' }}>{{ trans('app.Monthly') }}</option>
                                    <option value="quarterly" {{ old('renewal_period', $product->renewal_period) == 'quarterly' ? 'selected' : '' }}>{{ trans('app.Quarterly') }}</option>
                                    <option value="semi-annual" {{ old('renewal_period', $product->renewal_period) == 'semi-annual' ? 'selected' : '' }}>{{ trans('app.Semi-Annual') }}</option>
                                    <option value="annual" {{ old('renewal_period', $product->renewal_period) == 'annual' ? 'selected' : '' }}>{{ trans('app.Annual') }}</option>
                                    <option value="three-years" {{ old('renewal_period', $product->renewal_period) == 'three-years' ? 'selected' : '' }}>{{ trans('app.Three Years') }}</option>
                                    <option value="lifetime" {{ old('renewal_period', $product->renewal_period) == 'lifetime' ? 'selected' : '' }}>{{ trans('app.Lifetime') }}</option>
                                </select>
                                @error('renewal_period')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="duration_days" class="form-label">
                                    <i class="fas fa-clock text-warning me-1"></i>
                                    {{ trans('app.License Duration (Days)') }}
                                </label>
                                <input type="number" class="form-control @error('duration_days') is-invalid @enderror" 
                                       id="duration_days" name="duration_days" 
                                       value="{{ old('duration_days', $product->duration_days) }}" min="1"
                                       placeholder="365">
                                <div class="form-text">{{ trans('app.License validity period in days') }}</div>
                                @error('duration_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tax_rate" class="form-label">
                                    <i class="fas fa-percentage text-danger me-1"></i>
                                    {{ trans('app.Tax Rate (%)') }}
                                </label>
                                <input type="number" step="0.01" class="form-control @error('tax_rate') is-invalid @enderror" 
                                       id="tax_rate" name="tax_rate" 
                                       value="{{ old('tax_rate', $product->tax_rate) }}" min="0" max="100"
                                       placeholder="0.00">
                                <div class="form-text">{{ trans('app.Tax percentage applied to product price') }}</div>
                                @error('tax_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="extended_support_price" class="form-label">
                                    <i class="fas fa-headset text-info me-1"></i>
                                    {{ trans('app.Extended Support Price') }}
                                </label>
                                <input type="number" step="0.01" class="form-control @error('extended_support_price') is-invalid @enderror" 
                                       id="extended_support_price" name="extended_support_price" 
                                       value="{{ old('extended_support_price', $product->extended_support_price) }}"
                                       placeholder="0.00">
                                <div class="form-text">{{ trans('app.Price for extended support') }}</div>
                                @error('extended_support_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="extended_support_days" class="form-label">
                                    <i class="fas fa-calendar-plus text-success me-1"></i>
                                    {{ trans('app.Extended Support Days') }}
                                </label>
                                <input type="number" class="form-control @error('extended_support_days') is-invalid @enderror" 
                                       id="extended_support_days" name="extended_support_days" 
                                       value="{{ old('extended_support_days', $product->extended_support_days) }}" min="0"
                                       placeholder="365">
                                <div class="form-text">{{ trans('app.Additional support days') }}</div>
                                @error('extended_support_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="renewal_reminder_days" class="form-label">
                                    <i class="fas fa-bell text-warning me-1"></i>
                                    {{ trans('app.Renewal Reminder Days') }}
                                </label>
                                <input type="number" class="form-control @error('renewal_reminder_days') is-invalid @enderror" 
                                       id="renewal_reminder_days" name="renewal_reminder_days" 
                                       value="{{ old('renewal_reminder_days', $product->renewal_reminder_days) }}" min="1"
                                       placeholder="30">
                                <div class="form-text">{{ trans('app.Days before expiry to send reminder') }}</div>
                                @error('renewal_reminder_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">
                                    <i class="fas fa-info-circle text-info me-1"></i>
                                    {{ trans('app.Status') }}
                                </label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status">
                                    <option value="">{{ trans('app.Select Status') }}</option>
                                    <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>{{ trans('app.Active') }}</option>
                                    <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>{{ trans('app.Inactive') }}</option>
                                    <option value="draft" {{ old('status', $product->status) == 'draft' ? 'selected' : '' }}>{{ trans('app.Draft') }}</option>
                                    <option value="archived" {{ old('status', $product->status) == 'archived' ? 'selected' : '' }}>{{ trans('app.Archived') }}</option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">
                                    <i class="fas fa-warehouse text-warning me-1"></i>
                                    {{ trans('app.Stock') }}
                                </label>
                                <input type="number" class="form-control @error('stock') is-invalid @enderror" 
                                       id="stock" name="stock" 
                                       value="{{ old('stock', $product->stock) }}" min="-1"
                                       placeholder="-1">
                                <div class="form-text">-1 = {{ trans('app.Unlimited Stock') }}</div>
                                @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="supported_until" class="form-label">
                                    <i class="fas fa-calendar-times text-danger me-1"></i>
                                    {{ trans('app.Supported Until') }}
                                </label>
                                <input type="date" class="form-control @error('supported_until') is-invalid @enderror" 
                                       id="supported_until" name="supported_until" 
                                       value="{{ old('supported_until', $product->supported_until ? $product->supported_until->format('Y-m-d') : '') }}" readonly>
                                <div class="form-text">{{ trans('app.Support end date') }} ({{ trans('app.Auto-calculated based on support days') }})</div>
                                @error('supported_until')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="extended_supported_until" class="form-label">
                                    <i class="fas fa-calendar-plus text-success me-1"></i>
                                    {{ trans('app.Extended Supported Until') }}
                                </label>
                                <input type="date" class="form-control @error('extended_supported_until') is-invalid @enderror" 
                                       id="extended_supported_until" name="extended_supported_until" 
                                       value="{{ old('extended_supported_until', $product->extended_supported_until ? $product->extended_supported_until->format('Y-m-d') : '') }}" readonly>
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
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-star me-2"></i>
                            {{ trans('app.Features Requirements') }}
                            <span class="badge bg-light text-warning ms-2">{{ trans('app.Optional') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="features" class="form-label">
                                    <i class="fas fa-list-check text-success me-1"></i>
                                    {{ trans('app.Features') }}
                                </label>
                                <textarea class="form-control @error('features') is-invalid @enderror" 
                                          id="features" name="features" rows="6"
                                          data-summernote="true" data-toolbar="basic"
                                          data-placeholder="{{ trans('app.List product features with formatting') }}"
                                          placeholder="{{ trans('app.Features') }}">{{ old('features', is_array($product->features) ? implode("\n", $product->features) : $product->features) }}</textarea>
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
                                    <i class="fas fa-clipboard-check text-primary me-1"></i>
                                    {{ trans('app.Requirements') }}
                                </label>
                                <textarea class="form-control @error('requirements') is-invalid @enderror" 
                                          id="requirements" name="requirements" rows="6"
                                          data-summernote="true" data-toolbar="basic"
                                          data-placeholder="{{ trans('app.List system requirements with formatting') }}"
                                          placeholder="{{ trans('app.Requirements') }}">{{ old('requirements', is_array($product->requirements) ? implode("\n", $product->requirements) : $product->requirements) }}</textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Use lists and formatting to clearly show system requirements.') }}
                                </div>
                                @error('requirements')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="installation_guide" class="form-label">
                                <i class="fas fa-book text-purple me-1"></i>
                                {{ trans('app.Installation Guide') }}
                            </label>
                            <textarea class="form-control @error('installation_guide') is-invalid @enderror" 
                                      id="installation_guide" name="installation_guide" rows="4"
                                      data-summernote="true" data-toolbar="standard"
                                      data-placeholder="{{ trans('app.Create step-by-step installation guide') }}"
                                      placeholder="{{ trans('app.Step By Step Installation') }}">{{ old('installation_guide', is_array($product->installation_guide) ? implode("\n", $product->installation_guide) : $product->installation_guide) }}</textarea>
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

                <!-- Product Files -->
                <div class="card mb-4 product-files-section {{ $product->is_downloadable ? 'show' : '' }}" id="product-files-section">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-upload me-2"></i>
                            {{ trans('app.Product Files') }}
                            <span class="badge bg-light text-success ms-2">{{ trans('app.Required for Downloadable Products') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ trans('app.Product files will be encrypted and stored securely. You can manage files from the product files page.') }}
                            <br><small class="text-muted">
                                <i class="fas fa-lightbulb me-1"></i>
                                {{ trans('app.Tip: Enable "Downloadable" option above to show this section.') }}
                            </small>
                        </div>
                        
                        @if($product->files && $product->files->count() > 0)
                        <div class="alert alert-success">
                            <h6 class="alert-heading">
                                <i class="fas fa-files me-2"></i>
                                {{ trans('app.Existing Files') }} ({{ $product->files->count() }})
                            </h6>
                            <div class="row">
                                @foreach($product->files as $file)
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file me-2 text-primary"></i>
                                        <div class="flex-grow-1">
                                            <strong>{{ $file->original_name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $file->formatted_size }} • 
                                                {{ $file->download_count }} {{ trans('app.downloads') }} • 
                                                <span class="badge badge-{{ $file->is_active ? 'success' : 'danger' }} badge-sm">
                                                    {{ $file->is_active ? trans('app.Active') : trans('app.Inactive') }}
                                                </span>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="product_files" class="form-label">
                                    <i class="fas fa-file-upload me-1"></i>
                                    {{ trans('app.Add New Files') }}
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
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="form-text">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        <strong>{{ trans('app.Tip:') }}</strong> {{ trans('app.You can manage all product files from the dedicated files management page.') }}
                                    </div>
                                    <a href="{{ route('admin.products.files.index', $product) }}" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-cog me-1"></i>
                                        {{ trans('app.Manage Files') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Media and Assets -->
                <div class="card mb-4">
                    <div class="card-header bg-pink text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-images me-2"></i>
                            {{ trans('app.Media and Assets') }}
                            <span class="badge bg-light text-pink ms-2">{{ trans('app.Optional') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label">
                                    <i class="fas fa-image text-primary me-1"></i>
                                    {{ trans('app.Main Image') }}
                                </label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                       id="image" name="image" accept="image/*">
                                <div class="form-text">{{ trans('app.Recommended Size') }}</div>
                                @if($product->image)
                                <div class="mt-2">
                                    <img src="{{ Storage::url($product->image) }}" alt="{{ trans('app.Current Image') }}"
                                         class="img-thumbnail product-image">
                                    <p class="text-muted small mt-1">{{ trans('app.Current Image Will Be Replaced') }}</p>
                                </div>
                                @endif
                                @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="gallery_images" class="form-label">
                                    <i class="fas fa-images text-success me-1"></i>
                                    {{ trans('app.Gallery Images') }}
                                </label>
                                <input type="file" class="form-control @error('gallery_images') is-invalid @enderror" 
                                       id="gallery_images" name="gallery_images[]" accept="image/*" multiple>
                                <div class="form-text">{{ trans('app.Select Multiple Images') }}</div>
                                @if($product->gallery_images && count($product->gallery_images) > 0)
                                <div class="mt-2">
                                    <p class="text-muted small mb-2">{{ trans('app.Current Gallery Images') }}:</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($product->gallery_images as $galleryImage)
                                        <img src="{{ Storage::url($galleryImage) }}" alt="{{ trans('app.Gallery Image') }}"
                                             class="img-thumbnail product-gallery-image">
                                        @endforeach
                                    </div>
                                    <p class="text-muted small mt-1">{{ trans('app.New Images Will Be Added') }}</p>
                                </div>
                                @endif
                                @error('gallery_images')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO Optimization -->
                <div class="card mb-4">
                    <div class="card-header bg-indigo text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-search me-2"></i>
                            {{ trans('app.SEO') }}
                            <span class="badge bg-light text-indigo ms-2">{{ trans('app.Optional') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="meta_title" class="form-label">
                                    <i class="fas fa-heading text-primary me-1"></i>
                                    {{ trans('app.Meta Title') }}
                                </label>
                                <input type="text" class="form-control @error('meta_title') is-invalid @enderror" 
                                       id="meta_title" name="meta_title" 
                                       value="{{ old('meta_title', $product->meta_title) }}" 
                                       maxlength="255" placeholder="{{ trans('app.SEO Title Placeholder') }}">
                                @error('meta_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tags" class="form-label">
                                    <i class="fas fa-tags text-warning me-1"></i>
                                    {{ trans('app.Tags') }}
                                </label>
                                <input type="text" class="form-control @error('tags') is-invalid @enderror" 
                                       id="tags" name="tags" 
                                       value="{{ old('tags', is_array($product->tags) ? implode(', ', $product->tags) : $product->tags) }}"
                                       placeholder="{{ trans('app.Tags Comma Separated') }}">
                                @error('tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="meta_description" class="form-label">
                                <i class="fas fa-file-alt text-success me-1"></i>
                                {{ trans('app.Meta Description') }}
                            </label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror" 
                                      id="meta_description" name="meta_description" rows="3" maxlength="500"
                                      placeholder="{{ trans('app.SEO Description Placeholder') }}">{{ old('meta_description', $product->meta_description) }}</textarea>
                            @error('meta_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Product Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog me-2"></i>
                            {{ trans('app.Product Settings') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <i class="fas fa-toggle-on text-success me-1"></i>
                                {{ trans('app.Active') }}
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1"
                                   {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                <i class="fas fa-star text-warning me-1"></i>
                                {{ trans('app.Featured') }}
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_downloadable" name="is_downloadable" value="1"
                                   {{ old('is_downloadable', $product->is_downloadable) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_downloadable">
                                <i class="fas fa-download text-info me-1"></i>
                                {{ trans('app.Downloadable') }}
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_popular" name="is_popular" value="1"
                                   {{ old('is_popular', $product->is_popular) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_popular">
                                <i class="fas fa-fire text-warning me-1"></i>
                                {{ trans('app.Popular') }}
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="auto_renewal" name="auto_renewal" value="1"
                                   {{ old('auto_renewal', $product->auto_renewal) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_renewal">
                                <i class="fas fa-sync text-success me-1"></i>
                                {{ trans('app.Auto Renewal') }}
                            </label>
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
                                    <h4 class="text-primary">{{ $product->licenses()->count() }}</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Licenses') }}</p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-success">{{ $product->invoices()->count() }}</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Invoices') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- License Integration -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-code me-2"></i>
                            {{ trans('app.License Integration') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($product->programmingLanguage)
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="fas fa-check-circle me-1"></i>
                                {{ trans('app.Integration File Generated') }}
                            </h6>
                            <p class="mb-2">
                                <strong>{{ trans('app.Language') }}:</strong> {{ $product->programmingLanguage->name }}<br>
                                <strong>{{ trans('app.File') }}:</strong> {{ basename($product->integration_file_path ?? trans('app.Not generated')) }}
                            </p>
                            <div class="d-grid gap-2">
                                @if($product->integration_file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->integration_file_path))
                                <a href="{{ route('admin.products.download-integration', $product) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download me-1"></i>{{ trans('app.Download') }}
                                </a>
                                @endif
                                <button type="submit" form="regenerate-form" class="btn btn-sm btn-outline-secondary w-100">
                                    <i class="fas fa-sync me-1"></i>{{ trans('app.Regenerate') }}
                                </button>
                            </div>
                        </div>
                        @else
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                {{ trans('app.Programming Language Required') }}
                            </h6>
                            <p class="mb-0">{{ trans('app.Set Programming Language Message') }}</p>
                        </div>
                        @endif
                    </div>
                </div>


                <!-- Danger Zone -->
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ trans('app.Danger Zone') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">{{ trans('app.Delete Product Warning') }}</p>
                        <button type="submit" form="delete-form" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-1"></i>{{ trans('app.Delete Product') }}
                        </button>
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
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>{{ trans('app.Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ trans('app.Update Product') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
    <!-- Hidden forms for separate actions -->
    <form id="regenerate-form" method="post" action="{{ route('admin.products.regenerate-integration', $product) }}" class="hidden">
        @csrf
    </form>
    
    
    <form id="delete-form" method="post" action="{{ route('admin.products.destroy', $product) }}" data-confirm="delete-product" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

<!-- Create Test License Section - Separate Form -->
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-purple text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>
                        {{ trans('app.Create Test License') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">{{ trans('app.Create Test License Description') }}</p>
                    <form method="post" action="{{ route('admin.products.generate-license', $product) }}" class="needs-validation" novalidate>
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="test-domain" class="form-label">
                                        <i class="fas fa-globe text-primary me-1"></i>
                                        {{ trans('app.Domain') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="test-domain" name="domain" 
                                           placeholder="example.com" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="test-email" class="form-label">
                                        <i class="fas fa-envelope text-success me-1"></i>
                                        {{ trans('app.Customer Email') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control" id="test-email" name="email" 
                                           placeholder="customer@example.com" required>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-purple">
                                <i class="fas fa-plus me-1"></i>{{ trans('app.Create Test License') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
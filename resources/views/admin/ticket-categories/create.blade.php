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
                                {{ trans('app.Create Ticket Category') }}
                            </h1>
                            <p class="text-muted mb-0">{{ trans('app.Add New Ticket Category') }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.ticket-categories.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ trans('app.Back to Categories') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <form method="post" action="{{ route('admin.ticket-categories.store') }}" class="needs-validation" novalidate>
        @csrf

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
                                    {{ trans('app.Category Name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="{{ trans('app.Enter Category Name') }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="slug" class="form-label">
                                    <i class="fas fa-link text-purple me-1"></i>
                                    {{ trans('app.Slug') }}
                                </label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                       id="slug" name="slug" value="{{ old('slug') }}" 
                                       placeholder="{{ trans('app.Auto Generated from Name') }}">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Leave empty to auto generate') }}
                                </div>
                                @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left text-success me-1"></i>
                                {{ trans('app.Description') }}
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4"
                                      data-summernote="true" data-toolbar="basic"
                                      data-placeholder="{{ trans('app.Enter Category Description') }}"
                                      placeholder="{{ trans('app.Enter Category Description') }}">{{ old('description') }}</textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.Use the rich text editor to format your category description.') }}
                            </div>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Category Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog me-2"></i>
                            {{ trans('app.Category Settings') }}
                            <span class="badge bg-light text-warning ms-2">{{ trans('app.Required') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="color" class="form-label">
                                    <i class="fas fa-palette text-primary me-1"></i>
                                    {{ trans('app.Category Color') }} <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" 
                                           id="color" name="color" value="{{ old('color', '#3b82f6') }}" required>
                                    <input type="text" class="form-control" id="color-text" 
                                           value="{{ old('color', '#3b82f6') }}" readonly>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Choose a color to represent this category') }}
                                </div>
                                @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">
                                    <i class="fas fa-sort-numeric-up text-info me-1"></i>
                                    {{ trans('app.Sort Order') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" 
                                       min="0" placeholder="0" required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Lower numbers appear first') }}
                                </div>
                                @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <i class="fas fa-toggle-on text-success me-1"></i>
                                {{ trans('app.Active') }}
                            </label>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.Category will be visible to users') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Access Requirements -->
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-shield-alt me-2"></i>
                            {{ trans('app.Access Requirements') }}
                            <span class="badge bg-light text-danger ms-2">{{ trans('app.Security') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="requires_login" value="0">
                                    <input class="form-check-input" type="checkbox" id="requires_login" name="requires_login" value="1"
                                           {{ old('requires_login', false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="requires_login">
                                        <i class="fas fa-user-lock text-warning me-1"></i>
                                        {{ trans('app.Requires Login') }}
                                    </label>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        {{ trans('app.Users must be logged in to create tickets in this category') }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="requires_valid_purchase_code" value="0">
                                    <input class="form-check-input" type="checkbox" id="requires_valid_purchase_code" name="requires_valid_purchase_code" value="1"
                                           {{ old('requires_valid_purchase_code', false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="requires_valid_purchase_code">
                                        <i class="fas fa-key text-danger me-1"></i>
                                        {{ trans('app.Requires Valid Purchase Code') }}
                                    </label>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        {{ trans('app.Users must have a valid purchase code to create tickets in this category') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO Optimization -->
                <div class="card mb-4">
                    <div class="card-header bg-indigo text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-search me-2"></i>
                            {{ trans('app.SEO Optimization') }}
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
                                       id="meta_title" name="meta_title" value="{{ old('meta_title') }}" 
                                       maxlength="255" placeholder="{{ trans('app.SEO Title Placeholder') }}">
                                @error('meta_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="meta_keywords" class="form-label">
                                    <i class="fas fa-tags text-warning me-1"></i>
                                    {{ trans('app.Meta Keywords') }}
                                </label>
                                <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" 
                                       id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords') }}" 
                                       placeholder="{{ trans('app.Keywords Comma Separated') }}">
                                @error('meta_keywords')
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
                                      id="meta_description" name="meta_description" rows="3"
                                      maxlength="500" placeholder="{{ trans('app.SEO Description Placeholder') }}">{{ old('meta_description') }}</textarea>
                            @error('meta_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Category Preview -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-eye me-2"></i>
                            {{ trans('app.Category Preview') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div id="category-preview" class="p-3 rounded category-preview" data-color="#3b82f6">
                                <i class="fas fa-ticket-alt fs-1 mb-2"></i>
                                <h5 id="preview-name">{{ trans('app.Category Name') }}</h5>
                                <p id="preview-description" class="mb-0 small">{{ trans('app.Category Description') }}</p>
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
                                    <h4 class="text-primary">0</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Tickets') }}</p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-success">0</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Replies') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category Icon -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-icons me-2"></i>
                            {{ trans('app.Category Icon') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="icon" class="form-label">
                                <i class="fas fa-star text-warning me-1"></i>
                                {{ trans('app.Icon Class') }}
                            </label>
                            <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                                   id="icon" name="icon" value="{{ old('icon', 'fas fa-ticket-alt') }}" 
                                   placeholder="fas fa-ticket-alt">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.Use Font Awesome icon classes') }}
                            </div>
                            @error('icon')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-center">
                            <div id="icon-preview" class="fs-1 text-primary">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <p class="text-muted small mt-2">{{ trans('app.Icon Preview') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Priority Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ trans('app.Priority Settings') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="priority" class="form-label">
                                <i class="fas fa-flag text-danger me-1"></i>
                                {{ trans('app.Default Priority') }}
                            </label>
                            <select class="form-select @error('priority') is-invalid @enderror" 
                                    id="priority" name="priority">
                                <option value="low" {{ old('priority', 'medium') == 'low' ? 'selected' : '' }}>
                                    <i class="fas fa-arrow-down text-success me-1"></i>{{ trans('app.Low') }}
                                </option>
                                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>
                                    <i class="fas fa-minus text-warning me-1"></i>{{ trans('app.Medium') }}
                                </option>
                                <option value="high" {{ old('priority', 'medium') == 'high' ? 'selected' : '' }}>
                                    <i class="fas fa-arrow-up text-danger me-1"></i>{{ trans('app.High') }}
                                </option>
                                <option value="urgent" {{ old('priority', 'medium') == 'urgent' ? 'selected' : '' }}>
                                    <i class="fas fa-exclamation text-danger me-1"></i>{{ trans('app.Urgent') }}
                                </option>
                            </select>
                            @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                            <a href="{{ route('admin.ticket-categories.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>{{ trans('app.Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ trans('app.Create Category') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
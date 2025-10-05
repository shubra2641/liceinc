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
                                {{ trans('app.Create KB Category') }}
                            </h1>
                            <p class="text-muted mb-0">{{ trans('app.Add New Knowledge Base Category') }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.kb-categories.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ trans('app.Back to Categories') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <form method="post" action="{{ route('admin.kb-categories.store') }}" class="needs-validation" novalidate>
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
                            <label for="parent_id" class="form-label">
                                <i class="fas fa-sitemap text-success me-1"></i>
                                {{ trans('app.Parent Category') }}
                            </label>
                            <select class="form-select @error('parent_id') is-invalid @enderror" 
                                    id="parent_id" name="parent_id">
                                <option value="">{{ trans('app.None (Top Level)') }}</option>
                                @foreach($parents as $id => $name)
                                <option value="{{ $id }}" {{ old('parent_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.Select a parent category to create a subcategory') }}
                            </div>
                            @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="product_id" class="form-label">
                                <i class="fas fa-box text-info me-1"></i>
                                {{ trans('app.Linked Product') }}
                            </label>
                            <select class="form-select @error('product_id') is-invalid @enderror" 
                                    id="product_id" name="product_id">
                                <option value="">{{ trans('app.Select Product (Optional)') }}</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.Link this category to a product for access control') }}
                            </div>
                            @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                {{ trans('app.Use the rich text editor to format your category description') }}
                            </div>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Serial Protection -->
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-lock me-2"></i>
                            {{ trans('app.Serial Protection') }}
                            <span class="badge bg-light text-danger ms-2">{{ trans('app.Optional') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="requires_serial" value="0">
                            <input class="form-check-input" type="checkbox" id="requires_serial" name="requires_serial" value="1"
                                   {{ old('requires_serial') ? 'checked' : '' }}>
                            <label class="form-check-label" for="requires_serial">
                                <i class="fas fa-key text-danger me-1"></i>
                                {{ trans('app.Require Serial for Category') }}
                            </label>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.All articles in this category will require a serial number') }}
                            </div>
                        </div>

                        <div id="serial-fields" class="hidden-field">
                            <div class="mb-3">
                                <label for="serial" class="form-label">
                                    <i class="fas fa-key text-danger me-1"></i>
                                    {{ trans('app.Serial Code') }}
                                </label>
                                <input type="text" class="form-control @error('serial') is-invalid @enderror" 
                                       id="serial" name="serial" value="{{ old('serial') }}" 
                                       placeholder="{{ trans('app.Enter Serial Code') }}">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Serial required to access category articles') }}
                                </div>
                                @error('serial')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="serial_message" class="form-label">
                                    <i class="fas fa-comment text-info me-1"></i>
                                    {{ trans('app.Serial Message') }}
                                </label>
                                <textarea class="form-control @error('serial_message') is-invalid @enderror" 
                                          id="serial_message" name="serial_message" rows="3"
                                          placeholder="{{ trans('app.Message shown when serial required') }}">{{ old('serial_message') }}</textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Message displayed before serial input') }}
                                </div>
                                @error('serial_message')
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
                            <div id="category-preview" class="p-3 rounded border">
                                <i class="{{ old('icon', 'fas fa-folder') }} fs-1 text-primary mb-2"></i>
                                <h5 id="preview-name">{{ trans('app.Category Name') }}</h5>
                                <p id="preview-description" class="text-muted small mb-0">{{ trans('app.Category Description') }}</p>
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
                                    <p class="text-muted small mb-0">{{ trans('app.Articles') }}</p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-success">0</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Subcategories') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog me-2"></i>
                            {{ trans('app.Category Settings') }}
                        </h5>
                    </div>
                    <div class="card-body">
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

                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="is_featured" value="0">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1"
                                   {{ old('is_featured', false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                <i class="fas fa-star text-warning me-1"></i>
                                {{ trans('app.Featured Category') }}
                            </label>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.Featured categories appear prominently') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category Icon -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
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
                                   id="icon" name="icon" value="{{ old('icon', 'fas fa-folder') }}" 
                                   placeholder="fas fa-folder">
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
                                <i class="{{ old('icon', 'fas fa-folder') }}"></i>
                            </div>
                            <p class="text-muted small mt-2">{{ trans('app.Icon Preview') }}</p>
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
                            <a href="{{ route('admin.kb-categories.index') }}" class="btn btn-outline-secondary">
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
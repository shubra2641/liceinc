@extends('layouts.admin')
@section('title', 'Create KB Article')

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
                                {{ trans('app.Create KB Article') }}
                            </h1>
                            <p class="text-muted mb-0">{{ trans('app.Add New Knowledge Base Article') }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.kb-articles.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ trans('app.Back to Articles') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <form method="post" action="{{ route('admin.kb-articles.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
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
                                <label for="kb_category_id" class="form-label">
                                    <i class="fas fa-tag text-purple me-1"></i>
                                    {{ trans('app.Category') }} <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('kb_category_id') is-invalid @enderror" 
                                        id="kb_category_id" name="kb_category_id" required>
                                    <option value="">{{ trans('app.Select a Category') }}</option>
                                    @foreach($categories as $id => $name)
                                    <option value="{{ $id }}" {{ old('kb_category_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('kb_category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="slug" class="form-label">
                                    <i class="fas fa-link text-indigo me-1"></i>
                                    {{ trans('app.Slug') }}
                                </label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                       id="slug" name="slug" value="{{ old('slug') }}" 
                                       placeholder="{{ trans('app.Auto Generated from Title') }}">
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
                            <label for="title" class="form-label">
                                <i class="fas fa-heading text-primary me-1"></i>
                                {{ trans('app.Title') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" 
                                   placeholder="{{ trans('app.Enter Article Title') }}" required>
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="excerpt" class="form-label">
                                <i class="fas fa-file-text text-success me-1"></i>
                                {{ trans('app.Excerpt') }}
                            </label>
                            <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                                      id="excerpt" name="excerpt" rows="3"
                                      placeholder="{{ trans('app.Brief summary of article') }}">{{ old('excerpt') }}</textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.Brief summary of article') }}
                            </div>
                            @error('excerpt')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Article Content -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-align-left me-2"></i>
                            {{ trans('app.Article Content') }}
                            <span class="badge bg-light text-warning ms-2">{{ trans('app.Required') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="image" class="form-label">
                                <i class="fas fa-image text-success me-1"></i>
                                {{ trans('app.Featured Image') }}
                            </label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.Optional featured image for the article') }}
                            </div>
                            @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">
                                <i class="fas fa-edit text-warning me-1"></i>
                                {{ trans('app.Content') }} <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" name="content" rows="12"
                                      data-summernote="true" data-toolbar="advanced"
                                      data-placeholder="{{ trans('app.Enter Article Content') }}"
                                      placeholder="{{ trans('app.Enter Article Content') }}" required>{{ old('content') }}</textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.Use the rich text editor to create comprehensive articles with headings, lists, links, images, and more') }}
                            </div>
                            @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1"
                                   {{ old('is_published', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_published">
                                <i class="fas fa-eye text-success me-1"></i>
                                {{ trans('app.Publish Immediately') }}
                            </label>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.Article will be visible publicly') }}
                            </div>
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
                            <input class="form-check-input" type="checkbox" id="requires_serial" name="requires_serial" value="1"
                                   {{ old('requires_serial') ? 'checked' : '' }}>
                            <label class="form-check-label" for="requires_serial">
                                <i class="fas fa-key text-danger me-1"></i>
                                {{ trans('app.Require Serial to View') }}
                            </label>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.Users must enter serial to access content') }}
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
                                    {{ trans('app.Serial required to access article') }}
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
                <!-- Article Preview -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-eye me-2"></i>
                            {{ trans('app.Article Preview') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div id="article-preview" class="p-3 rounded border">
                                <i class="fas fa-file-alt fs-1 text-primary mb-2"></i>
                                <h5 id="preview-title">{{ trans('app.Article Title') }}</h5>
                                <p id="preview-excerpt" class="text-muted small mb-0">{{ trans('app.Article Excerpt') }}</p>
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
                                    <h4 class="text-primary">{{ $categories->count() }}</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Categories') }}</p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-success">0</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Articles') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Article Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog me-2"></i>
                            {{ trans('app.Article Settings') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1"
                                   {{ old('is_featured', false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                <i class="fas fa-star text-warning me-1"></i>
                                {{ trans('app.Featured Article') }}
                            </label>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.Featured articles appear prominently') }}
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="allow_comments" name="allow_comments" value="1"
                                   {{ old('allow_comments', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_comments">
                                <i class="fas fa-comments text-success me-1"></i>
                                {{ trans('app.Allow Comments') }}
                            </label>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.Users can comment on this article') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Writing Tips -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-lightbulb me-2"></i>
                            {{ trans('app.Writing Tips') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ trans('app.Use clear and descriptive titles') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ trans('app.Write comprehensive content') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ trans('app.Use headings and lists for structure') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ trans('app.Add relevant images when helpful') }}
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ trans('app.Test your article before publishing') }}
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
                            <a href="{{ route('admin.kb-articles.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>{{ trans('app.Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ trans('app.Create Article') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
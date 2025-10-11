@extends('layouts.admin')

@section('admin-content')
<div class="admin-programming-languages-create">
    <!-- Page Header -->
    <div class="admin-page-header modern-header">
        <div class="d-flex align-items-center justify-content-between">
            <div class="flex-grow-1">
                <h1 class="gradient-text">{{ __('app.create_new_programming_language') }}</h1>
                <p class="admin-page-subtitle">{{ __('app.add_new_programming_language') }}</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.programming-languages.index') }}" class="admin-btn admin-btn-secondary admin-btn-m">
                    <i class="fas fa-arrow-left me-2"></i>
                    {{ __('app.back_to_languages') }}
                </a>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="admin-alert admin-alert-error">
        <div class="admin-alert-content">
            <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
            <div>
                <h4 class="admin-alert-title">{{ __('validation_errors') }}</h4>
                <ul class="admin-alert-message">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Form -->
    <div class="admin-section">
        <div class="admin-section-content">
            <form method="post" action="{{ route('admin.programming-languages.store') }}" class="needs-validation" novalidate>
                @csrf

                <!-- Basic Information -->
                <div class="admin-card mb-4">
                    <div class="admin-section-content">
                        <div class="admin-card-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="admin-card-title">
                            <h3>{{ __('app.Basic_Information') }}</h3>
                            <p class="admin-card-subtitle">{{ __('app.enter_language_basic_details') }}</p>
                        </div>
                        <span class="admin-badge admin-badge-required">{{ __('app.Required') }}</span>
                    </div>
                    <div class="admin-card-content">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label required" for="name">
                                        <i class="fas fa-code me-2"></i>{{ __('app.language_name') }}
                                    </label>
                                    <input type="text" id="name" name="name" class="admin-form-input"
                                           value="{{ old('name') }}" required placeholder="{{ __('app.enter_language_name') }}">
                                    @error('name')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="slug">
                                        <i class="fas fa-link me-2"></i>{{ __('slug') }}
                                    </label>
                                    <input type="text" id="slug" name="slug" class="admin-form-input"
                                           value="{{ old('slug') }}" placeholder="{{ __('app.auto_generated_from_name') }}">
                                    <small class="admin-form-help">{{ __('app.leave_empty_auto_generate') }}</small>
                                    @error('slug')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="file_extension">
                                        <i class="fas fa-file-code me-2"></i>{{ __('app.file_extension') }}
                                    </label>
                                    <input type="text" id="file_extension" name="file_extension" class="admin-form-input"
                                           value="{{ old('file_extension') }}" placeholder="php, js, py, etc.">
                                    @error('file_extension')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="icon">
                                        <i class="fas fa-icons me-2"></i>{{ __('app.icon_class') }}
                                    </label>
                                    <div class="input-group">
                                        <input type="text" id="icon" name="icon" class="admin-form-input"
                                               value="{{ old('icon') }}" placeholder="fab fa-php, fas fa-code">
                                        <div class="input-group-text">
                                            <i id="icon-preview" class="fas fa-code"></i>
                                        </div>
                                    </div>
                                    <small class="admin-form-help">{{ __('app.fontawesome_icon_class') }}</small>
                                    @error('icon')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="sort_order">
                                        <i class="fas fa-sort-numeric-up me-2"></i>{{ __('app.sort_order') }}
                                    </label>
                                    <input type="number" id="sort_order" name="sort_order" class="admin-form-input"
                                           value="{{ old('sort_order', 0) }}" min="0">
                                    @error('sort_order')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <div class="admin-checkbox-group">
                                        <input type="checkbox" id="is_active" name="is_active" value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }} class="admin-checkbox">
                                        <label for="is_active" class="admin-checkbox-label">
                                            <span class="admin-checkbox-text">{{ __('app.Active') }}</span>
                                            <small class="admin-checkbox-description">{{ __('app.language_will_be_available') }}</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="description">
                                    <i class="fas fa-align-left me-2"></i>{{ __('app.Description') }}
                                </label>
                                <textarea id="description" name="description" class="admin-form-textarea" rows="3"
                                          placeholder="{{ __('app.brief_description_language') }}">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="admin-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- License Template -->
                <div class="admin-card mb-4">
                    <div class="admin-section-content">
                        <div class="admin-card-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <div class="admin-card-title">
                            <h3>{{ __('app.license_template') }}</h3>
                            <p class="admin-card-subtitle">{{ __('app.custom_license_verification_template') }}</p>
                        </div>
                    </div>
                    <div class="admin-card-content">
                        <div class="admin-form-group">
                            <label class="admin-form-label" for="license_template">
                                <i class="fas fa-code me-2"></i>{{ __('app.license_template') }}
                            </label>
                            <textarea id="license_template" name="license_template" class="admin-form-textarea" rows="15"
                                      placeholder="{{ __('app.enter_license_verification_code') }}">{{ old('license_template') }}</textarea>
                            <small class="admin-form-help">
                                {{ __('app.available_placeholders') }}<br>
                                {{ __('app.leave_empty_use_default') }}
                            </small>
                            @error('license_template')
                                <div class="admin-form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="admin-card admin-card-info">
                            <div class="admin-section-content">
                                <div class="admin-card-icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <div class="admin-card-title">
                                    <h4>{{ __('app.template_preview') }}</h4>
                                </div>
                            </div>
                            <div class="admin-card-content">
                                <div class="admin-code-block">
                                    <h4>{{ __('app.template_preview') }}</h4>
                                    <pre class="admin-code-pre" id="template-preview">{{ __('app.template_generated_based_language') }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="admin-card">
                    <div class="admin-card-content">
                        <div class="d-flex gap-3 justify-content-end">
                            <a href="{{ route('admin.programming-languages.index') }}" class="admin-btn admin-btn-secondary admin-btn-lg">
                                <i class="fas fa-times me-2"></i>
                                {{ __('app.Cancel') }}
                            </a>
                            <button type="submit" class="admin-btn admin-btn-primary admin-btn-lg">
                                <i class="fas fa-save me-2"></i>
                                {{ __('app.create_language') }}
                                <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
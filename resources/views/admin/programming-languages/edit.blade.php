@extends('layouts.admin')

@section('admin-content')
<div class="admin-programming-languages-edit admin-programming-languages-show" data-language-id="{{ $programming_language->id }}" data-language-slug="{{ $programming_language->slug }}">
    <!-- Page Header -->
    <div class="admin-page-header modern-header">
        <div class="d-flex align-items-center justify-content-between">
            <div class="flex-grow-1">
                <h1 class="gradient-text">{{ __('app.Edit_programming_language') }}</h1>
                <p class="admin-page-subtitle">{{ __('app.modify_language_settings_and_templates') }}</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.programming-languages.index') }}" class="admin-btn admin-btn-secondary admin-btn-m">
                    <i class="fas fa-arrow-left me-2"></i>
                    {{ __('app.back_to_languages') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Language Overview -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-info-circle me-2"></i>{{ __('app.language_overview') }}</h2>
            <div class="admin-section-actions">
                @if($programming_language->is_active)
                <span class="admin-badge admin-badge-success">
                    <i class="fas fa-check-circle me-1"></i>{{ __('app.Active') }}
                </span>
                @else
                <span class="admin-badge admin-badge-secondary">
                    <i class="fas fa-pause-circle me-1"></i>{{ __('app.inactive') }}
                </span>
                @endif
            </div>
        </div>
        <div class="admin-section-content">
            <div class="row g-4">
                <!-- Language Info Card -->
                <div class="col-md-4">
                    <div class="stats-card stats-card-neutral animate-slide-up">
                        <div class="stats-card-background">
                            <div class="stats-card-pattern"></div>
                        </div>
                        <div class="stats-card-content">
                            <div class="stats-card-header">
                                <div class="stats-card-icon language"></div>
                                <div class="stats-card-menu">
                                    <button class="stats-menu-btn">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="stats-card-body">
                                <div class="stats-card-value">
                                    @if($programming_language->icon)
                                        <i class="{{ $programming_language->icon }} text-primary"></i>
                                    @else
                                        <i class="fas fa-code text-primary"></i>
                                    @endif
                                </div>
                                <div class="stats-card-label">{{ $programming_language->name }}</div>
                                <div class="stats-card-trend positive">
                                    <i class="stats-trend-icon positive"></i>
                                    <span>{{ $programming_language->slug }} • {{ $programming_language->file_extension ?? 'N/A' }}</span>
                                </div>
                                @if($programming_language->description)
                                <div class="stats-card-subvalue mt-2">
                                    <small class="text-muted">{{ Str::limit($programming_language->description, 80) }}</small>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Statistics Card -->
                <div class="col-md-4">
                    <div class="stats-card stats-card-success animate-slide-up animate-delay-100">
                        <div class="stats-card-background">
                            <div class="stats-card-pattern"></div>
                        </div>
                        <div class="stats-card-content">
                            <div class="stats-card-header">
                                <div class="stats-card-icon products"></div>
                                <div class="stats-card-menu">
                                    <button class="stats-menu-btn">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="stats-card-body">
                                <div class="stats-card-value">{{ $programming_language->products()->count() }}</div>
                                <div class="stats-card-label">{{ __('app.Total_products') }}</div>
                                <div class="stats-card-trend positive">
                                    <i class="stats-trend-icon positive"></i>
                                    <span>{{ $programming_language->products()->where('is_active', true)->count() }} {{ __('app.Active') }}</span>
                                </div>
                                @if($programming_language->products()->count() > 0)
                                <div class="stats-card-subvalue mt-2">
                                    <small class="text-success">
                                        <i class="fas fa-chart-line me-1"></i>{{ __('app.products_using_language') }}
                                    </small>
                                </div>
                                @else
                                <div class="stats-card-subvalue mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>{{ __('app.no_products_yet') }}
                                    </small>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Template Status Card -->
                <div class="col-md-4">
                    <div class="stats-card stats-card-warning animate-slide-up animate-delay-200">
                        <div class="stats-card-background">
                            <div class="stats-card-pattern"></div>
                        </div>
                        <div class="stats-card-content">
                            <div class="stats-card-header">
                                <div class="stats-card-icon template"></div>
                                <div class="stats-card-menu">
                                    <button class="stats-menu-btn">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="stats-card-body">
                                <div class="stats-card-value">
                                    @if($programming_language->hasTemplateFile())
                                        <i class="fas fa-check-circle text-success"></i>
                                    @else
                                        <i class="fas fa-file-alt text-warning"></i>
                                    @endif
                                </div>
                                <div class="stats-card-label">{{ __('app.template_status') }}</div>
                                <div class="stats-card-trend {{ $programming_language->hasTemplateFile() ? 'positive' : 'neutral' }}">
                                    <i class="stats-trend-icon {{ $programming_language->hasTemplateFile() ? 'positive' : 'neutral' }}"></i>
                                    <span>
                                        @if($programming_language->hasTemplateFile())
                                            {{ __('app.custom_template') }}
                                        @else
                                            {{ __('app.default_template') }}
                                        @endif
                                    </span>
                                </div>
                                @if($programming_language->hasTemplateFile())
                                <div class="stats-card-subvalue mt-2">
                                    <small class="text-success">
                                        <i class="fas fa-file-code me-1"></i>{{ __('app.template_configured') }}
                                    </small>
                                </div>
                                @else
                                <div class="stats-card-subvalue mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>{{ __('app.using_default_template') }}
                                    </small>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="admin-section">
        <div class="admin-section-content">
            <nav class="admin-tabs-nav" role="tablist" aria-label="{{ __('app.navigation_tabs') }}">
                <button type="button" data-action="show-tab" data-tab="basic-content" id="basic-tab"
                    class="admin-tab-btn admin-tab-btn-active"
                    role="tab" aria-selected="true" aria-controls="basic-content" tabindex="0">
                    <i class="fas fa-info-circle me-2" aria-hidden="true"></i>
                    <span>{{ __('app.Basic_Information') }}</span>
                </button>
                <button type="button" data-action="show-tab" data-tab="template-content" id="template-tab"
                    class="admin-tab-btn"
                    role="tab" aria-selected="false" aria-controls="template-content" tabindex="-1">
                    <i class="fas fa-file-contract me-2" aria-hidden="true"></i>
                    <span>{{ __('app.license_template') }}</span>
                </button>
                <button type="button" data-action="show-tab" data-tab="usage-content" id="usage-tab"
                    class="admin-tab-btn"
                    role="tab" aria-selected="false" aria-controls="usage-content" tabindex="-1">
                    <i class="fas fa-chart-bar me-2" aria-hidden="true"></i>
                    <span>{{ __('app.usage_stats') }}</span>
                </button>
            </nav>
            <noscript>
                <div class="admin-alert admin-alert-info mt-4">
                    <div class="admin-alert-content">
                        <i class="fas fa-info-circle admin-alert-icon"></i>
                        <div>
                            <h4 class="admin-alert-title">{{ trans('app.javascript_required') }}</h4>
                            <p class="admin-alert-message">{{ trans('app.tab_navigation_requires_javascript') }}</p>
                        </div>
                    </div>
                </div>
            </noscript>
        </div>
    </div>

    <!-- Basic Information Tab -->
    <div id="basic-content" class="admin-tab-panel" role="tabpanel" aria-labelledby="basic-tab">
        <form method="post" action="{{ route('admin.programming-languages.update', $programming_language) }}"
            id="basic-form" class="needs-validation" novalidate>
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Main Form Section -->
                <div class="col-lg-8">
                    <!-- Basic Details -->
                    <div class="admin-section">
                        <div class="admin-section-header">
                            <h3><i class="fas fa-edit me-2"></i>{{ __('app.basic_details') }}</h3>
                            <span class="admin-badge admin-badge-danger">{{ __('app.Required') }}</span>
                        </div>
                        <div class="admin-section-content">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label" for="name">
                                            <i class="fas fa-code me-1"></i>{{ __('app.language_name') }}
                                        </label>
                                        <input type="text" id="name" name="name" class="admin-form-input"
                                            value="{{ old('app.Name', $programming_language->name) }}" required
                                            placeholder="{{ __('app.enter_language_name') }}">
                                        @error('app.Name')
                                        <div class="admin-form-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label" for="slug">
                                            <i class="fas fa-link me-1"></i>{{ __('app.Slug') }}
                                        </label>
                                        <input type="text" id="slug" name="slug" class="admin-form-input"
                                            value="{{ old('app.Slug', $programming_language->slug) }}"
                                            placeholder="{{ __('app.auto_generated_from_name') }}">
                                        <small class="admin-form-help">{{ __('app.leave_empty_auto_generate') }}</small>
                                        @error('app.Slug')
                                        <div class="admin-form-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label" for="file_extension">
                                            <i class="fas fa-file-code me-1"></i>{{ __('app.file_extension') }}
                                        </label>
                                        <input type="text" id="file_extension" name="file_extension"
                                            class="admin-form-input"
                                            value="{{ old('app.file_extension', $programming_language->file_extension) }}"
                                            placeholder="php, js, py, java, cs, cpp">
                                        @error('app.file_extension')
                                        <div class="admin-form-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label" for="icon">
                                            <i class="fas fa-icons me-1"></i>{{ __('app.icon_class') }}
                                        </label>
                                        <div class="input-group">
                                            <input type="text" id="icon" name="icon" class="admin-form-input"
                                                value="{{ old('icon', $programming_language->icon) }}"
                                                placeholder="fab fa-php, fas fa-code">
                                            <span class="input-group-text">
                                                <i id="icon-preview" class="{{ $programming_language->icon ?: 'fas fa-code' }}"></i>
                                            </span>
                                        </div>
                                        <small class="admin-form-help">{{ __('app.fontawesome_icon_class') }}</small>
                                        @error('icon')
                                        <div class="admin-form-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label" for="description">
                                            <i class="fas fa-align-left me-1"></i>{{ __('app.Description') }}
                                        </label>
                                        <textarea id="description" name="description" class="admin-form-textarea" rows="4"
                                            placeholder="{{ __('app.brief_description_language') }}">{{ old('app.Description', $programming_language->description) }}</textarea>
                                        @error('app.Description')
                                        <div class="admin-form-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Settings -->
                    <div class="admin-section">
                        <div class="admin-section-header">
                            <h3><i class="fas fa-cogs me-2"></i>{{ __('app.advanced_settings') }}</h3>
                        </div>
                        <div class="admin-section-content">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label" for="sort_order">
                                            <i class="fas fa-sort-numeric-up me-1"></i>{{ __('app.sort_order') }}
                                        </label>
                                        <input type="number" id="sort_order" name="sort_order" class="admin-form-input"
                                            value="{{ old('app.sort_order', $programming_language->sort_order) }}" min="0">
                                        @error('app.sort_order')
                                        <div class="admin-form-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                                {{ old('is_active', $programming_language->is_active) ? 'checked' : '' }}
                                                class="form-check-input">
                                            <label class="form-check-label" for="is_active">
                                                <i class="fas fa-toggle-on me-1"></i>{{ __('app.Active_language') }}
                                            </label>
                                        </div>
                                        <small class="admin-form-help">{{ __('app.language_will_be_available') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Information -->
                <div class="col-lg-4">
                    <!-- Quick Stats -->
                    <div class="admin-section">
                        <div class="admin-section-header">
                            <h3><i class="fas fa-chart-pie me-2"></i>{{ __('app.quick_stats') }}</h3>
                        </div>
                        <div class="admin-section-content">
                            <div class="admin-stats-grid">
                                <div class="admin-stat-item">
                                    <div class="admin-stat-icon">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div class="admin-stat-content">
                                        <div class="admin-stat-value">{{ $programming_language->products()->count() }}</div>
                                        <div class="admin-stat-label">{{ __('app.Products') }}</div>
                                    </div>
                                </div>

                                <div class="admin-stat-item">
                                    <div class="admin-stat-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="admin-stat-content">
                                        <div class="admin-stat-value">{{ $programming_language->products()->where('is_active', true)->count() }}</div>
                                        <div class="admin-stat-label">{{ __('app.Active_products') }}</div>
                                    </div>
                                </div>

                                <div class="admin-stat-item">
                                    <div class="admin-stat-icon">
                                        <i class="fas fa-key"></i>
                                    </div>
                                    <div class="admin-stat-content">
                                        <div class="admin-stat-value">{{ $programming_language->products()->withCount('licenses')->get()->sum('licenses_count') }}</div>
                                        <div class="admin-stat-label">{{ __('app.Licenses') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Meta Information -->
                    <div class="admin-section">
                        <div class="admin-section-header">
                            <h3><i class="fas fa-info me-2"></i>{{ __('app.meta_information') }}</h3>
                        </div>
                        <div class="admin-section-content">
                            <div class="admin-info-list">
                                <div class="admin-info-item">
                                    <span class="admin-info-label">{{ __('app.Created_at') }}:</span>
                                    <span class="admin-info-value">{{ $programming_language->created_at->format('M d, Y') }}</span>
                                </div>
                                <div class="admin-info-item">
                                    <span class="admin-info-label">{{ __('app.updated_at') }}:</span>
                                    <span class="admin-info-value">{{ $programming_language->updated_at->format('M d, Y') }}</span>
                                </div>
                                <div class="admin-info-item">
                                    <span class="admin-info-label">{{ __('app.sort_order') }}:</span>
                                    <span class="admin-info-value">#{{ $programming_language->sort_order }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="admin-section">
                <div class="admin-section-content">
                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('admin.programming-languages.index') }}"
                            class="admin-btn admin-btn-secondary admin-btn-m">
                            <i class="fas fa-times me-2"></i>
                            {{ __('app.Cancel') }}
                        </a>
                        <button type="submit" class="admin-btn admin-btn-primary admin-btn-m" id="submit-basic-btn">
                            <i class="fas fa-save me-2"></i>
                            {{ __('app.Update') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Template Tab -->
    <div id="template-content" class="admin-tab-panel admin-tab-panel-hidden" role="tabpanel" aria-labelledby="template-tab">
        <form method="post" action="{{ route('admin.programming-languages.update', $programming_language) }}"
            id="template-form" class="admin-form" novalidate>
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Template Editor -->
                <div class="col-lg-8">
                    <div class="admin-section admin-section-warning">
                        <div class="admin-section-header">
                            <h3>
                                <i class="fas fa-file-contract admin-section-icon"></i>
                                {{ __('app.license_template_editor') }}
                            </h3>
                            <div class="admin-section-actions">
                                <button type="button" data-action="load-template"
                                    class="admin-btn admin-btn-sm admin-btn-primary">
                                    <i class="fas fa-upload me-2"></i>{{ __('app.load_template') }}
                                </button>
                                <noscript>
                                    <a href="{{ route('admin.programming-languages.template-content', $programming_language) }}" 
                                       class="admin-btn admin-btn-sm admin-btn-primary">
                                        <i class="fas fa-upload me-2"></i>{{ __('app.load_template') }}
                                    </a>
                                </noscript>
                                <button type="button" data-action="save-template"
                                    class="admin-btn admin-btn-sm admin-btn-success">
                                    <i class="fas fa-save me-2"></i>{{ __('app.save_template') }}
                                </button>
                                <noscript>
                                    <button type="submit" form="template-form" 
                                            class="admin-btn admin-btn-sm admin-btn-success">
                                        <i class="fas fa-save me-2"></i>{{ __('app.save_template') }}
                                    </button>
                                </noscript>
                                <button type="button" data-action="preview-template"
                                    class="admin-btn admin-btn-sm admin-btn-info">
                                    <i class="fas fa-eye me-2"></i>{{ __('app.preview') }}
                                </button>
                                <noscript>
                                    <div class="admin-notification admin-notification-warning">
                                        <div class="admin-notification-content">
                                            <i class="fas fa-exclamation-triangle admin-notification-icon"></i>
                                            <div class="admin-notification-text">
                                                <h4>{{ __('app.javascript_required') }}</h4>
                                                <p>{{ __('app.javascript_required_for_preview') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </noscript>
                                <button type="button" data-action="validate-templates"
                                    data-url="{{ route('admin.programming-languages.validate-templates') }}"
                                    class="admin-btn admin-btn-sm admin-btn-warning">
                                    <i class="fas fa-check-circle me-2"></i>{{ __('app.validate') }}
                                </button>
                            </div>
                        </div>
                        <div class="admin-section-content">
                            <div class="admin-form-group">
                                <label class="admin-form-label" for="license_template">
                                    <i class="fas fa-code admin-form-label-icon"></i>{{ __('app.template_code') }}
                                </label>
                                <div class="admin-code-editor-container">
                                    <textarea id="license_template" name="license_template"
                                        class="admin-form-textarea admin-code-editor" rows="20"
                                        placeholder="{{ __('app.enter_license_verification_code') }}"
                                        data-help="{{ __('app.help_license_template') }}">{{ old('app.license_template', $programming_language->license_template) }}</textarea>
                                    <div class="admin-code-editor-actions">
                                        <button type="button" data-action="toggle-code-view"
                                            class="admin-code-action-btn">
                                            <i class="fas fa-expand-arrows-alt"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="admin-form-help admin-form-help-info">
                                    <i class="fas fa-info-circle admin-form-help-icon"></i>
                                    {{ __('app.available_placeholders') }}: {LICENSE_KEY}, {PRODUCT_NAME}, {CUSTOMER_EMAIL}
                                </div>
                                @error('app.license_template')
                                <p class="admin-form-error-text">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Template Sidebar -->
                <div class="col-lg-4">
                    <!-- Template Preview -->
                    <div class="admin-section">
                        <div class="admin-section-header">
                            <h3>
                                <i class="fas fa-eye me-2"></i>
                                {{ __('app.live_preview') }}
                            </h3>
                        </div>
                        <div class="admin-section-content">
                            <div class="admin-code-block" id="template-preview">
                                @if($programming_language->license_template)
                                {{ $programming_language->license_template }}
                                @else
                                <span class="text-muted">{{ __('app.No_custom_template') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Template Actions -->
                    <div class="admin-section">
                        <div class="admin-section-header">
                            <h3>
                                <i class="fas fa-tools me-2"></i>
                                {{ __('app.template_actions') }}
                            </h3>
                        </div>
                        <div class="admin-section-content">
                            <div class="d-grid gap-2">
                                <button type="button" data-action="refresh-templates"
                                    class="admin-btn admin-btn-info admin-btn-m w-100">
                                    <i class="fas fa-download me-2"></i>{{ __('app.download_template') }}
                                </button>
                                <button type="button" data-action="view-template"
                                    class="admin-btn admin-btn-secondary admin-btn-m w-100">
                                    <i class="fas fa-eye me-2"></i>{{ __('app.view_template_file') }}
                                </button>
                                <button type="button" data-action="create-template"
                                    class="admin-btn admin-btn-primary admin-btn-m w-100"
                                    data-label-not-implemented="{{ __('app.Not_implemented_yet') }}"
                                    data-label-prompt="{{ __('app.please_enter_template_name') }}">
                                    <i class="fas fa-plus me-2"></i>{{ __('app.create_template_file') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Template Info -->
                    <div class="admin-section">
                        <div class="admin-section-header">
                            <h3>
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('app.template_info') }}
                            </h3>
                        </div>
                        <div class="admin-section-content">
                            <div class="admin-info-list">
                                <div class="admin-info-item">
                                    <span class="admin-info-label">{{ __('app.Status') }}:</span>
                                    @if($programming_language->hasTemplateFile())
                                    <span class="admin-badge admin-badge-success">{{ __('app.custom') }}</span>
                                    @else
                                    <span class="admin-badge admin-badge-secondary">{{ __('app.default') }}</span>
                                    @endif
                                </div>
                                @if($programming_language->hasTemplateFile())
                                <div class="admin-info-item">
                                    <span class="admin-info-label">{{ __('app.file_size') }}:</span>
                                    <span class="admin-info-value">{{ number_format($programming_language->getTemplateInfo()['file_size'] / 1024, 1) }} KB</span>
                                </div>
                                <div class="admin-info-item">
                                    <span class="admin-info-label">{{ __('app.last_modified') }}:</span>
                                    <span class="admin-info-value">{{ $programming_language->getTemplateInfo()['last_modified'] }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Template Form Actions -->
            <div class="admin-section">
                <div class="admin-section-content">
                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('admin.programming-languages.index') }}"
                            class="admin-btn admin-btn-secondary admin-btn-m">
                            <i class="fas fa-times me-2"></i>
                            {{ __('app.Cancel') }}
                        </a>
                        <button type="submit" class="admin-btn admin-btn-primary admin-btn-m">
                            <i class="fas fa-save me-2"></i>
                            {{ __('app.save_template') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>


    <!-- Usage Stats Tab -->
    <div id="usage-content" class="admin-tab-panel admin-tab-panel-hidden">
        <!-- Usage Statistics -->
        <div class="admin-section">
            <div class="admin-section-header">
                <h3><i class="fas fa-chart-bar me-2"></i>{{ __('app.usage_statistics') }}</h3>
            </div>
            <div class="admin-section-content">
                <div class="dashboard-grid dashboard-grid-3 stats-grid-enhanced">
                    <!-- Total Products Stats Card -->
                    <div class="stats-card stats-card-primary animate-slide-up">
                        <div class="stats-card-background">
                            <div class="stats-card-pattern"></div>
                        </div>
                        <div class="stats-card-content">
                            <div class="stats-card-header">
                                <div class="stats-card-icon products"></div>
                                <div class="stats-card-menu">
                                    <button class="stats-menu-btn">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="stats-card-body">
                                <div class="stats-card-value">{{ $programming_language->products()->count() }}</div>
                                <div class="stats-card-label">{{ __('app.Total_products') }}</div>
                                <div class="stats-card-trend positive">
                                    <i class="stats-trend-icon positive"></i>
                                    <span>{{ $programming_language->products()->where('is_active', true)->count() }} {{ __('app.Active') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Products Stats Card -->
                    <div class="stats-card stats-card-success animate-slide-up animate-delay-200">
                        <div class="stats-card-background">
                            <div class="stats-card-pattern"></div>
                        </div>
                        <div class="stats-card-content">
                            <div class="stats-card-header">
                                <div class="stats-card-icon active"></div>
                                <div class="stats-card-menu">
                                    <button class="stats-menu-btn">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="stats-card-body">
                                <div class="stats-card-value">{{ $programming_language->products()->where('is_active', true)->count() }}</div>
                                <div class="stats-card-label">{{ __('app.Active_products') }}</div>
                                <div class="stats-card-trend positive">
                                    <i class="stats-trend-icon positive"></i>
                                    <span>{{ round(($programming_language->products()->where('is_active', true)->count() / max($programming_language->products()->count(), 1)) * 100) }}% {{ __('app.of_total') }}</span> </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Licenses Stats Card -->
                    <div class="stats-card stats-card-info animate-slide-up animate-delay-400">
                        <div class="stats-card-background">
                            <div class="stats-card-pattern"></div>
                        </div>
                        <div class="stats-card-content">
                            <div class="stats-card-header">
                                <div class="stats-card-icon licenses"></div>
                                <div class="stats-card-menu">
                                    <button class="stats-menu-btn">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="stats-card-body">
                                <div class="stats-card-value">{{ $programming_language->products()->withCount('licenses')->get()->sum('licenses_count') }}</div>
                                <div class="stats-card-label">{{ __('app.Total_licenses_issued') }}</div>
                                <div class="stats-card-trend positive">
                                    <i class="stats-trend-icon positive"></i>
                                    <span>{{ __('app.Across all products') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Using This Language -->
        <div class="admin-section">
            <div class="admin-section-header">
                <h3><i class="fas fa-list me-2"></i>{{ __('app.products_using_language') }}</h3>
            </div>
            <div class="admin-section-content">
                @if($programming_language->products()->count() > 0)
                <div class="admin-table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('app.Product') }}</th>
                                    <th>{{ __('app.Licenses') }}</th>
                                    <th>{{ __('app.Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($programming_language->products()->take(10)->get() as $product)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($product->image)
                                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                                                class="product-image me-3">
                                            @else
                                            <div class="product-avatar me-3">
                                                <i class="fas fa-box"></i>
                                            </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $product->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge-info">{{ $product->licenses()->count() }}</span>
                                    </td>
                                    <td>
                                        @if($product->is_active)
                                        <span class="admin-badge admin-badge-success">{{ __('app.Active') }}</span>
                                        @else
                                        <span class="admin-badge admin-badge-secondary">{{ __('app.inactive') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($programming_language->products()->count() > 10)
                    <div class="text-center p-3">
                        <span class="text-muted">
                            {{ __('app.and_more_products', ['count' => $programming_language->products()->count() - 10]) }}
                        </span>
                    </div>
                    @endif
                </div>
                @else
                <div class="admin-empty-state">
                    <div class="admin-empty-state-content">
                        <i class="fas fa-inbox admin-empty-state-icon"></i>
                        <h4 class="admin-empty-state-title">{{ __('app.No_products_yet') }}</h4>
                        <p class="admin-empty-state-description">{{ __('app.No_products_using_language') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
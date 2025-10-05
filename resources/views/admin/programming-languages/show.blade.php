@extends('layouts.admin')

@section('admin-content')
<!-- Programming Language Show Page -->
<div class="admin-programming-languages-show">
    <!-- Page Header -->
    <div class="admin-page-header modern-header">
        <div class="d-flex align-items-center justify-content-between">
            <div class="flex-grow-1">
                <h1 class="gradient-text">{{ $programmingLanguage->name }}</h1>
                <p class="admin-page-subtitle">{{ __('app.programming_language_details') }}</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.programming-languages.edit', $programmingLanguage) }}" class="admin-btn admin-btn-primary admin-btn-m">
                    <i class="fas fa-edit me-2"></i>
                    {{ __('app.Edit') }}
                </a>
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
            <h2><i class="fas fa-code me-2"></i>{{ __('app.language_overview') }}</h2>
            <div class="admin-section-actions">
                <span class="admin-badge admin-badge-info">{{ $programmingLanguage->name }}</span>
            </div>
        </div>
        <div class="admin-section-content">
            <div class="row g-4">
                <!-- Quick Stats -->
                <div class="col-md-3">
                    <div class="admin-card">
                        <div class="admin-card-content">
                            <div class="d-flex align-items-center mb-3">
                                @if($programmingLanguage->icon)
                                <i class="{{ $programmingLanguage->icon }} admin-card-icon me-3"></i>
                                @else
                                <i class="fas fa-code admin-card-icon me-3"></i>
                                @endif
                                <div>
                                    <h4 class="admin-card-title">{{ $programmingLanguage->name }}</h4>
                                    <p class="admin-card-subtitle">{{ __('app.language_name') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="admin-card">
                        <div class="admin-card-content">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-box admin-card-icon me-3"></i>
                                <div>
                                    <h4 class="admin-card-title">{{ $programmingLanguage->products()->count() }}</h4>
                                    <p class="admin-card-subtitle">{{ __('app.total_products') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="admin-card">
                        <div class="admin-card-content">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle admin-card-icon me-3"></i>
                                <div>
                                    <h4 class="admin-card-title">{{ $programmingLanguage->products()->where('is_active', true)->count() }}</h4>
                                    <p class="admin-card-subtitle">{{ __('app.active_products') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="admin-card">
                        <div class="admin-card-content">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-file-contract admin-card-icon me-3"></i>
                                <div>
                                    <h4 class="admin-card-title">
                                        @if($programmingLanguage->hasTemplateFile())
                                        <span class="admin-badge admin-badge-success">{{ __('app.custom') }}</span>
                                        @else
                                        <span class="admin-badge admin-badge-info">{{ __('app.default') }}</span>
                                        @endif
                                    </h4>
                                    <p class="admin-card-subtitle">{{ __('app.template_status') }}</p>
                                </div>
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
                <button type="button" data-action="show-tab" data-tab="details-content" id="details-tab"
                    class="admin-tab-btn admin-tab-btn-active"
                    role="tab" aria-selected="true" aria-controls="details-content" tabindex="0">
                    <i class="fas fa-info-circle me-2" aria-hidden="true"></i>
                    <span>{{ __('app.details') }}</span>
                </button>
                <button type="button" data-action="show-tab" data-tab="template-content" id="template-tab"
                    class="admin-tab-btn"
                    role="tab" aria-selected="false" aria-controls="template-content" tabindex="-1">
                    <i class="fas fa-file-contract me-2" aria-hidden="true"></i>
                    <span>{{ __('app.license_template') }}</span>
                </button>
                <button type="button" data-action="show-tab" data-tab="products-content" id="products-tab"
                    class="admin-tab-btn"
                    role="tab" aria-selected="false" aria-controls="products-content" tabindex="-1">
                    <i class="fas fa-box me-2" aria-hidden="true"></i>
                    <span>{{ __('app.related_products') }}</span>
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

    <!-- Details Tab Content -->
    <div id="details-content" class="admin-tab-panel" role="tabpanel" aria-labelledby="details-tab" aria-hidden="false">
        <div class="admin-section">
            <div class="admin-section-header">
                <h3><i class="fas fa-info-circle me-2"></i>{{ __('app.language_details') }}</h3>
                <div class="admin-section-actions">
                    <a href="{{ route('admin.programming-languages.edit', $programmingLanguage) }}" class="admin-btn admin-btn-primary admin-btn-m">
                        <i class="fas fa-edit me-2"></i>
                        {{ __('app.Edit') }}
                    </a>
                </div>
            </div>
            <div class="admin-section-content">
                <!-- Language Information Grid -->
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="admin-card">
                            <div class="admin-card-content">
                                <div class="d-flex align-items-center mb-3">
                                    @if($programmingLanguage->icon)
                                    <i class="{{ $programmingLanguage->icon }} admin-card-icon me-3"></i>
                                    @else
                                    <i class="fas fa-code admin-card-icon me-3"></i>
                                    @endif
                                    <div class="flex-grow-1">
                                        <h4 class="admin-card-title">{{ $programmingLanguage->name }}</h4>
                                        <p class="admin-card-subtitle">{{ $programmingLanguage->slug }}</p>
                                    </div>
                                    <div>
                                        @if($programmingLanguage->is_active)
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

                                @if($programmingLanguage->description)
                                <p class="admin-card-text">{{ Str::limit($programmingLanguage->description, 200) }}</p>
                                @endif

                                <div class="admin-info-list">
                                    <div class="admin-info-item">
                                        <span class="admin-info-label">{{ __('app.extension') }}:</span>
                                        <span class="admin-info-value">{{ $programmingLanguage->file_extension ?: __('not_specified') }}</span>
                                    </div>
                                    <div class="admin-info-item">
                                        <span class="admin-info-label">{{ __('app.Products') }}:</span>
                                        <span class="admin-info-value">{{ $programmingLanguage->products()->count() }}</span>
                                    </div>
                                    <div class="admin-info-item">
                                        <span class="admin-info-label">{{ __('app.sort_order') }}:</span>
                                        <span class="admin-info-value">#{{ $programmingLanguage->sort_order }}</span>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2 mt-3">
                                    <a href="{{ route('admin.programming-languages.edit', $programmingLanguage) }}" 
                                       class="admin-btn admin-btn-primary admin-btn-sm flex-grow-1">
                                        <i class="fas fa-edit me-1"></i>{{ __('app.Edit') }}
                                    </a>
                                    <a href="{{ route('admin.programming-languages.index') }}" 
                                       class="admin-btn admin-btn-secondary admin-btn-sm">
                                        <i class="fas fa-arrow-left me-1"></i>{{ __('app.back_to_languages') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Language Statistics -->
                        <div class="admin-card">
                            <div class="admin-card-content">
                                <h5 class="admin-card-title mb-3">{{ __('app.statistics') }}</h5>
                                <div class="admin-info-list">
                                    <div class="admin-info-item">
                                        <span class="admin-info-label">{{ __('app.total_products') }}:</span>
                                        <span class="admin-info-value">{{ $programmingLanguage->products()->count() }}</span>
                                    </div>
                                    <div class="admin-info-item">
                                        <span class="admin-info-label">{{ __('app.active_products') }}:</span>
                                        <span class="admin-info-value">{{ $programmingLanguage->products()->where('is_active', true)->count() }}</span>
                                    </div>
                                    <div class="admin-info-item">
                                        <span class="admin-info-label">{{ __('app.template_status') }}:</span>
                                        <span class="admin-info-value">
                                            @if($programmingLanguage->hasTemplateFile())
                                            <span class="admin-badge admin-badge-success">{{ __('app.custom') }}</span>
                                            @else
                                            <span class="admin-badge admin-badge-info">{{ __('app.default') }}</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="admin-info-item">
                                        <span class="admin-info-label">{{ __('app.Created_at') }}:</span>
                                        <span class="admin-info-value">{{ $programmingLanguage->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="admin-card">
                            <div class="admin-card-content">
                                <h5 class="admin-card-title mb-3">{{ __('app.quick_actions') }}</h5>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('admin.programming-languages.edit', $programmingLanguage) }}" class="admin-btn admin-btn-primary admin-btn-sm">
                                        <i class="fas fa-edit me-2"></i>{{ __('app.Edit') }}
                                    </a>
                                    <a href="{{ route('admin.programming-languages.create') }}" class="admin-btn admin-btn-success admin-btn-sm">
                                        <i class="fas fa-plus me-2"></i>{{ __('app.add_new_language') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Tab Content -->
    <div id="template-content" class="admin-tab-panel admin-tab-panel-hidden" role="tabpanel" aria-labelledby="template-tab" aria-hidden="true">
        <div class="admin-section">
            <div class="admin-section-header">
                <h3><i class="fas fa-file-contract me-2"></i>{{ __('app.license_template') }}</h3>
                <div class="admin-section-actions">
                    <a href="{{ route('admin.programming-languages.edit', $programmingLanguage) }}" class="admin-btn admin-btn-primary admin-btn-m">
                        <i class="fas fa-edit me-2"></i>
                        {{ __('app.Edit') }}
                    </a>
                </div>
            </div>
            <div class="admin-section-content">
                @if($programmingLanguage->license_template)
                <div class="admin-card">
                    <div class="admin-card-content">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-file-contract admin-card-icon me-3"></i>
                            <div class="flex-grow-1">
                                <h4 class="admin-card-title">{{ __('app.license_template') }}</h4>
                                <p class="admin-card-subtitle">{{ __('app.custom_license_verification_template') }}</p>
                            </div>
                            <span class="admin-badge admin-badge-success">
                                <i class="fas fa-check-circle me-1"></i>{{ __('app.custom') }}
                            </span>
                        </div>

                        <div class="admin-code-block">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="text-white">
                                    <i class="fas fa-code me-2"></i>{{ __('app.template_code') }}
                                </h5>
                                <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" data-copy-target="template-code">
                                    <i class="fas fa-copy me-1"></i>{{ __('app.copy') }}
                                </button>
                            </div>
                            <pre class="admin-code-pre" id="template-code">{{ $programmingLanguage->license_template }}</pre>
                        </div>
                    </div>
                </div>
                @else
                <div class="admin-card">
                    <div class="admin-card-content">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-file-contract admin-card-icon me-3"></i>
                            <div class="flex-grow-1">
                                <h4 class="admin-card-title">{{ __('app.license_template') }}</h4>
                                <p class="admin-card-subtitle">{{ __('app.no_custom_template_available') }}</p>
                            </div>
                            <span class="admin-badge admin-badge-info">
                                <i class="fas fa-info-circle me-1"></i>{{ __('app.default') }}
                            </span>
                        </div>

                        <div class="admin-empty-state">
                            <div class="admin-empty-state-content">
                                <i class="fas fa-file-contract admin-empty-state-icon"></i>
                                <h4 class="admin-empty-state-title">{{ __('app.No_template_available') }}</h4>
                                <p class="admin-empty-state-description">{{ __('app.create_template_to_get_started') }}</p>
                                <a href="{{ route('admin.programming-languages.edit', $programmingLanguage) }}" class="admin-btn admin-btn-primary admin-btn-m">
                                    <i class="fas fa-plus me-2"></i>
                                    {{ __('app.create_template') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Products Tab Content -->
    <div id="products-content" class="admin-tab-panel admin-tab-panel-hidden" role="tabpanel" aria-labelledby="products-tab" aria-hidden="true">
        <div class="admin-section">
            <div class="admin-section-header">
                <h3><i class="fas fa-box me-2"></i>{{ __('app.related_products') }}</h3>
                <div class="admin-section-actions">
                    <span class="admin-badge admin-badge-info">{{ $programmingLanguage->products()->count() }} {{ __('app.products') }}</span>
                </div>
            </div>
            <div class="admin-section-content">
                @if($programmingLanguage->products()->count() > 0)
                <div class="row g-4">
                    @foreach($programmingLanguage->products()->take(6)->get() as $product)
                    <div class="col-lg-4 col-md-6">
                        <div class="admin-card">
                            <div class="admin-card-content">
                                <div class="d-flex align-items-center mb-3">
                                    @if($product->image)
                                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="product-image me-3">
                                    @else
                                    <div class="product-avatar me-3">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <h4 class="admin-card-title">{{ $product->name }}</h4>
                                        <p class="admin-card-subtitle">{{ Str::limit($product->description, 50) }}</p>
                                    </div>
                                    <div>
                                        @if($product->is_active)
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
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.products.show', $product) }}" class="admin-btn admin-btn-primary admin-btn-sm flex-grow-1">
                                        <i class="fas fa-eye me-1"></i>{{ __('app.View') }}
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="admin-btn admin-btn-secondary admin-btn-sm">
                                        <i class="fas fa-edit me-1"></i>{{ __('app.Edit') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                @if($programmingLanguage->products()->count() > 6)
                <div class="text-center mt-4">
                    <a href="{{ route('admin.products.index', ['programming_language' => $programmingLanguage->id]) }}" class="admin-btn admin-btn-outline-primary admin-btn-m">
                        <i class="fas fa-eye me-2"></i>
                        {{ __('app.view_all_products') }}
                    </a>
                </div>
                @endif
                @else
                <div class="admin-card">
                    <div class="admin-card-content">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-box admin-card-icon me-3"></i>
                            <div class="flex-grow-1">
                                <h4 class="admin-card-title">{{ __('app.related_products') }}</h4>
                                <p class="admin-card-subtitle">{{ __('app.no_products_using_language') }}</p>
                            </div>
                            <span class="admin-badge admin-badge-secondary">
                                <i class="fas fa-info-circle me-1"></i>{{ __('app.empty') }}
                            </span>
                        </div>

                        <div class="admin-empty-state">
                            <div class="admin-empty-state-content">
                                <i class="fas fa-box admin-empty-state-icon"></i>
                                <h4 class="admin-empty-state-title">{{ __('app.No_products_yet') }}</h4>
                                <p class="admin-empty-state-description">{{ __('app.No_products_using_language') }}</p>
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="{{ route('admin.products.create') }}" class="admin-btn admin-btn-primary admin-btn-m">
                                        <i class="fas fa-plus me-2"></i>
                                        {{ __('app.create_product') }}
                                    </a>
                                    <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-secondary admin-btn-m">
                                        <i class="fas fa-list me-2"></i>
                                        {{ __('app.view_all_products') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.user')

@section('title', $product->meta_title ?? $product->name)
@section('meta_description', $product->meta_description ?? Str::limit(strip_tags($product->description), 160))
@section('page-title', $product->name)
@section('page-subtitle', trans('app.Product Details'))

@if(!empty($product->meta_title))
@section('og:title', $product->meta_title)
@endif
@if(!empty($product->meta_description))
@section('og:description', $product->meta_description)
@endif
@if(!empty($product->image))
@section('og:image', Storage::url($product->image))
@endif


@section('content')
<div class="user-dashboard-container">
    <!-- Product Header -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-box"></i>
                {{ $product->name }}
                @if($product->is_featured || $product->is_popular)
                <span class="user-premium-badge">
                    <i class="fas fa-crown"></i>
                    {{ trans('app.Premium') }}
                </span>
                @endif
            </div>
            <p class="user-card-subtitle">{{ trans('app.Product Details and Purchase Information') }}</p>
        </div>
        <div class="user-card-content">
            <!-- Product Overview -->
            <div class="product-overview">
                <div class="product-main-info">
                    <div class="product-image-section">
                        @if($product->image)
                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="product-image">
                        @else
                        <div class="product-image-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                        @endif
                    </div>
                    <div class="product-details">
                        <div class="product-badges">
                            @if($product->category)
                            <span class="product-category-badge">
                                <i class="fas fa-tag"></i>
                                {{ $product->category->name }}
                            </span>
                            @endif
                            @if($product->programmingLanguage)
                            <span class="product-language-badge">
                                <i class="fas fa-code"></i>
                                {{ $product->programmingLanguage->name }}
                            </span>
                            @endif
                            <span class="product-version-badge">
                                <i class="fas fa-tag"></i>
                                v{{ $product->latest_version }}
                            </span>
                        </div>

                        <h1 class="product-title">{{ $product->name }}</h1>

                        <div class="product-meta">
                            <div class="product-meta-item">
                                <i class="fas fa-calendar"></i>
                                <span>{{ trans('app.Updated') }}: {{ $product->updated_at->format('M d, Y') }}</span>
                            </div>
                            <div class="product-meta-item">
                                <i class="fas fa-download"></i>
                                <span>{{ trans('app.Downloads') }}: {{ $licenseCount ?? 0 }}</span>
                            </div>
                            <div class="product-meta-item">
                                <i class="fas fa-star"></i>
                                <span>{{ trans('app.Rating') }}: {{ $product->rating ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="product-description">
                            {{ $product->description }}
                        </div>
                    </div>
                </div>

                <!-- Purchase Section -->
                <div class="product-purchase-section">
                    <div class="purchase-card">
                        <div class="purchase-header">
                            <div class="product-price">
                                <span class="price-currency">$</span>
                                <span class="price-amount">{{ number_format($product->price, 2) }}</span>
                                @if($product->price > 0)
                                <span class="price-period">{{ $product->renewalPeriodLabel() }}</span>
                                @else
                                <span class="price-free">{{ trans('app.Free') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="purchase-features">
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <span>{{ trans('app.Lifetime License') }}</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <span>{{ trans('app.Free Updates') }}</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <span>{{ trans('app.Premium Support') }}</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <span>{{ trans('app.Source Code Included') }}</span>
                            </div>
                        </div>

                        <div class="purchase-actions">
                            @if($product->price > 0)
                            @if(auth()->check())
                            @if($userHasPurchasedBefore)
                            <!-- User has purchased before, show Buy Again -->
                            <a href="{{ route('payment.gateways', $product) }}" class="purchase-button primary">
                                <i class="fas fa-redo"></i>
                                {{ trans('app.Buy Again') }}
                            </a>
                            @else
                            <!-- First time buyer, show Buy Now -->
                            <a href="{{ route('payment.gateways', $product) }}" class="purchase-button primary">
                                <i class="fas fa-shopping-cart"></i>
                                {{ trans('app.Buy Now') }}
                            </a>
                            @endif

                            @if($userOwnsProduct)
                            <!-- User owns this product - show license management -->
                            <a href="{{ route('user.licenses.index') }}" class="purchase-button secondary">
                                <i class="fas fa-key"></i>
                                {{ trans('app.View Licenses') }}
                            </a>
                            @endif
                            @else
                            <a href="{{ route('login') }}" class="purchase-button primary">
                                <i class="fas fa-sign-in-alt"></i>
                                {{ trans('app.Login to Buy') }}
                            </a>
                            @endif
                            @else
                            <!-- Free product -->
                            @if($userOwnsProduct)
                            <!-- User owns this product - show license management -->
                            <a href="{{ route('user.licenses.index') }}" class="purchase-button primary">
                                <i class="fas fa-key"></i>
                                {{ trans('app.View Licenses') }}
                            </a>
                            @endif

                            <button class="purchase-button secondary" onclick="downloadProduct()">
                                <i class="fas fa-download"></i>
                                {{ trans('app.Download Free') }}
                            </button>
                            @endif

                            @if($userOwnsProduct && $product->is_downloadable)
                            @if(isset($userCanDownload) && $userCanDownload)
                            <a href="{{ route('user.products.files.index', $product) }}"
                                class="purchase-button secondary">
                                <i class="fas fa-download"></i>
                                {{ trans('app.Download Files') }}
                            </a>
                            @else
                            <button class="purchase-button secondary" disabled
                                title="{{ $downloadMessage ?? trans('app.You must pay the invoice first') }}">
                                <i class="fas fa-download"></i>
                                {{ trans('app.Download Files') }}
                            </button>
                            @if(isset($downloadMessage) && $downloadMessage)
                            <small class="text-warning d-block mt-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ $downloadMessage }}
                            </small>
                            @endif
                            @endif
                            @endif
                        </div>

                        <div class="purchase-guarantee">
                            <i class="fas fa-shield-alt"></i>
                            <span>{{ trans('app.30-Day Money Back Guarantee') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features and Requirements/Installation Layout -->
    <div class="user-dashboard-grid">
        <!-- Features Section (Main Content) -->
        <div class="user-dashboard-main">
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-star"></i>
                        {{ trans('app.Features') }}
                    </div>
                    <p class="user-card-subtitle">{{ trans('app.Key features and capabilities of this product') }}</p>
                </div>
                <div class="user-card-content">
                    @if($product->features && !empty($product->features))
                    @if(is_string($product->features))
                    <div class="features-content">
                        {{ $product->features }}
                    </div>
                    @elseif(is_array($product->features))
                    <div class="user-features-list">
                        @foreach($product->features as $feature)
                        <div class="user-feature-item">
                            <div class="user-feature-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="user-feature-content">
                                <span class="user-feature-text">{{ $feature }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="user-empty-state-title">
                            {{ trans('app.No features available') }}
                        </h3>
                        <p class="user-empty-state-description">
                            {{ trans('app.Product features will be added soon') }}
                        </p>
                    </div>
                    @endif
                    @else
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="user-empty-state-title">
                            {{ trans('app.No features available') }}
                        </h3>
                        <p class="user-empty-state-description">
                            {{ trans('app.Product features will be added soon') }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Requirements & Installation Sidebar -->
        @if(($product->requirements && !empty($product->requirements)) || ($product->installation_guide &&
        !empty($product->installation_guide)))
        <div class="user-dashboard-sidebar">
            <!-- Installation Guide Section (Top) -->
            @if($product->installation_guide && !empty($product->installation_guide))
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-tools"></i>
                        {{ trans('app.Installation Guide') }}
                    </div>
                    <p class="user-card-subtitle">{{ trans('app.Step-by-step installation instructions') }}</p>
                </div>
                <div class="user-card-content">
                    @if(is_string($product->installation_guide))
                    @if($product->installation_guide_has_html)
                    <div class="installation-content">
                        {{ $product->installation_guide }}
                    </div>
                    @else
                    <div class="installation-content">
                        {{ nl2br(e($product->installation_guide)) }}
                    </div>
                    @endif
                    @elseif(is_array($product->installation_guide))
                    <div class="user-installation-steps">
                        @foreach($product->installation_guide as $index => $step)
                        <div class="user-installation-step">
                            <div class="user-step-number">{{ $index + 1 }}</div>
                            <div class="user-step-content">
                                @if(is_string($step))
                                @if(strip_tags($step) !== $step)
                                {{ Purify::clean($step) }}
                                @else
                                <p>{{ nl2br(e($step)) }}</p>
                                @endif
                                @else
                                <p>{{ $step }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h4 class="user-empty-state-title">
                            {{ trans('app.No installation guide available') }}
                        </h4>
                        <p class="user-empty-state-description">
                            {{ trans('app.Installation guide will be added soon') }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Requirements Section (Bottom) -->
            @if($product->requirements && !empty($product->requirements))
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-cogs"></i>
                        {{ trans('app.Requirements') }}
                    </div>
                    <p class="user-card-subtitle">{{ trans('app.System requirements and dependencies') }}</p>
                </div>
                <div class="user-card-content">
                    @if(is_string($product->requirements))
                    @if($product->requirements_has_html)
                    <div class="requirements-content">
                        {{ $product->requirements }}
                    </div>
                    @else
                    <div class="requirements-content">
                        {{ nl2br(e($product->requirements)) }}
                    </div>
                    @endif
                    @elseif(is_array($product->requirements))
                    <div class="user-requirements-grid">
                        @foreach($product->requirements as $requirement)
                        <div class="user-requirement-item">
                            <div class="user-requirement-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="user-requirement-content">
                                <span class="user-requirement-text">{{ $requirement }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h4 class="user-empty-state-title">
                            {{ trans('app.No requirements specified') }}
                        </h4>
                        <p class="user-empty-state-description">
                            {{ trans('app.Product requirements will be added soon') }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>

    <!-- Product Screenshots -->
    @if($product->screenshots && !empty($product->screenshots))
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-images"></i>
                {{ trans('app.Screenshots') }}
            </div>
        </div>
        <div class="user-card-content">
            <div class="screenshots-grid">
                @if(is_array($screenshots) && count($screenshots) > 0)
                @foreach($screenshots as $screenshot)
                <div class="screenshot-item">
                    <img src="{{ Storage::url($screenshot) }}" alt="{{ $product->name }} Screenshot"
                        class="screenshot-image">
                </div>
                @endforeach
                @else
                <p>{{ trans('app.No screenshots available') }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Product Documentation -->
    @if($product->documentation && !empty($product->documentation))
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-book"></i>
                {{ trans('app.Documentation') }}
            </div>
        </div>
        <div class="user-card-content">
            <div class="documentation-content">
                @if(is_string($product->documentation))
                {{ $product->documentation }}
                @else
                <p>{{ trans('app.No documentation available') }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-th-large"></i>
                {{ trans('app.Related Products') }}
            </div>
        </div>
        <div class="user-card-content">
            <div class="related-products-grid">
                @foreach($relatedProducts as $relatedProduct)
                <div class="related-product-card">
                    <div class="related-product-image">
                        @if($relatedProduct->image)
                        <img src="{{ Storage::url($relatedProduct->image) }}" alt="{{ $relatedProduct->name }}">
                        @else
                        <div class="related-product-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                        @endif
                    </div>
                    <div class="related-product-info">
                        <div class="related-product-title-row">
                            <h3 class="related-product-title">{{ $relatedProduct->name }}</h3>
                            @if($relatedProduct->is_featured || $relatedProduct->is_popular)
                            <span class="user-premium-badge">
                                <i class="fas fa-crown"></i>
                                {{ trans('app.Premium') }}
                            </span>
                            @endif
                        </div>
                        <p class="related-product-description">{{ Str::limit($relatedProduct->description, 100) }}</p>
                        <div class="related-product-price">
                            @if($relatedProduct->price > 0)
                            ${{ number_format($relatedProduct->price, 2) }}
                            @else
                            {{ trans('app.Free') }}
                            @endif
                        </div>
                        <a href="{{ route('public.products.show', $relatedProduct->slug) }}"
                            class="related-product-link">
                            <i class="fas fa-eye"></i>
                            {{ trans('app.View Details') }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

@endsection
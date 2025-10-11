@extends('layouts.user')

@section('page-title', trans('app.Browse Products'))
@section('page-subtitle', trans('app.Discover and purchase new products'))

@section('seo_title', $siteSeoTitle ?? trans('app.Browse Products'))
@section('meta_description', $siteSeoDescription ?? trans('app.Discover and purchase new products'))

@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-box"></i>
                {{ trans('app.Available Products') }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.Discover and purchase new products to enhance your projects') }}
            </p>
        </div>

        <div class="user-card-content">
            <!-- Stats Cards -->
            <div class="user-stats-grid">
                <!-- Total Products -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Total Products') }}</div>
                        <div class="user-stat-icon blue">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ $products->total() }}</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.Available products') }}</p>
                </div>

                <!-- Categories -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Categories') }}</div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-folder"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ $categories->count() }}</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.Product categories') }}</p>
                </div>

                <!-- Free Products -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Free Products') }}</div>
                        <div class="user-stat-icon purple">
                            <i class="fas fa-gift"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ $products->where('price', 0)->count() }}</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.No cost products') }}</p>
                </div>

                <!-- Paid Products -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Paid Products') }}</div>
                        <div class="user-stat-icon yellow">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ $products->where('price', '>', 0)->count() }}</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.Premium products') }}</p>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-search"></i>
                        {{ trans('app.Search & Filter') }}
                    </div>
                    <p class="user-card-subtitle">{{ trans('app.Find the perfect product for your needs') }}</p>
                </div>
                <div class="user-card-content">
                    <!-- Search Form -->
                    <form action="{{ route('public.products.index') }}" method="get" class="user-search-form">
                        <div class="user-search-input-group">
                            <i class="fas fa-search user-search-icon"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="user-search-input"
                                placeholder="{{ trans('app.Search products...') }}">
                            <button type="submit" class="user-search-button">
                                <i class="fas fa-search"></i>
                                {{ trans('app.Search') }}
                            </button>
                        </div>
                    </form>

                    <!-- Filters -->
                    <div class="user-filters-row">
                        <div class="user-filters-group">
                            <!-- Category Filter -->
                            <div class="user-form-group">
                                <label class="user-form-label">{{ trans('app.Category') }}</label>
                                <select name="category" data-action="submit-on-change" form="filterForm"
                                    class="user-form-select">
                                    <option value="">{{ trans('app.All Categories') }}</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Language Filter -->
                            <div class="user-form-group">
                                <label class="user-form-label">{{ trans('app.Language') }}</label>
                                <select name="language" data-action="submit-on-change" form="filterForm"
                                    class="user-form-select">
                                    <option value="">{{ trans('app.All Languages') }}</option>
                                    @foreach($programmingLanguages as $language)
                                    <option value="{{ $language->id }}"
                                        {{ request('language') == $language->id ? 'selected' : '' }}>
                                        {{ $language->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Price Filter -->
                            <div class="user-form-group">
                                <label class="user-form-label">{{ trans('app.Price') }}</label>
                                <select name="price_filter" data-action="submit-on-change" form="filterForm"
                                    class="user-form-select">
                                    <option value="">{{ trans('app.All Prices') }}</option>
                                    <option value="free" {{ request('price_filter') == 'free' ? 'selected' : '' }}>
                                        {{ trans('app.Free Only') }}</option>
                                    <option value="paid" {{ request('price_filter') == 'paid' ? 'selected' : '' }}>
                                        {{ trans('app.Paid Only') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Sort Options -->
                        <div class="user-sort-group">
                            <label class="user-form-label">{{ trans('app.Sort by') }}</label>
                            <select name="sort" data-action="submit-on-change" form="filterForm"
                                class="user-form-select">
                                <option value="name" {{ request('sort', 'name') == 'name' ? 'selected' : '' }}>
                                    {{ trans('app.Name') }}</option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>
                                    {{ trans('app.Price: Low to High') }}</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>
                                    {{ trans('app.Price: High to Low') }}</option>
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>
                                    {{ trans('app.Newest') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Hidden form for filters -->
                    <form id="filterForm" method="get" class="hidden">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    </form>
                    <noscript>
                        <div class="user-alert user-alert-warning">
                            <div class="user-alert-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="user-alert-content">
                                <h4 class="user-alert-title">{{ trans('app.JavaScript Required') }}</h4>
                                <p class="user-alert-text">
                                    {{ trans('app.Filtering and sorting functionality requires JavaScript to be enabled. Please enable JavaScript or use the search form above.') }}
                                </p>
                            </div>
                        </div>
                    </noscript>
                </div>
            </div>

            <!-- Products Section -->
            <div class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-shopping-cart"></i>
                        {{ trans('app.Products') }}
                    </div>
                    <p class="user-card-subtitle">{{ trans('app.Browse and purchase products') }}</p>
                </div>
                <div class="user-card-content">
                    @if($products->count() > 0)
                    <div class="user-products-grid">
                        @foreach($products as $product)
                        <div class="user-product-card">
                            <div class="user-product-header">
                                <div>
                                    <div class="user-product-title-row">
                                        <h3 class="user-product-title">{{ $product->name }}</h3>
                                        @if($product->is_featured || $product->is_popular)
                                        <span class="user-premium-badge">
                                            <i class="fas fa-crown"></i>
                                            {{ trans('app.Premium') }}
                                        </span>
                                        @endif
                                    </div>
                                    <p class="user-product-version">v{{ $product->latest_version ?? '-' }}</p>
                                </div>
                                <div class="user-product-price">
                                    @if($product->price > 0)
                                    <div class="user-product-price-value">${{ number_format($product->price, 2) }}</div>
                                    @else
                                    <div class="user-product-price-free">
                                        <i class="fas fa-gift"></i>
                                        {{ trans('app.Free') }}
                                    </div>
                                    @endif
                                </div>
                            </div>

                            @if($product->description)
                            <p class="user-product-description">
                                {{ Str::limit($product->description, 100) }}
                            </p>
                            @endif

                            <div class="user-product-badges">
                                @if($product->is_featured || $product->is_popular)
                                <span class="user-badge user-badge-premium">
                                    <i class="fas fa-crown"></i>
                                    {{ trans('app.Premium') }}
                                </span>
                                @endif
                                @if($product->is_downloadable)
                                <span class="user-badge user-badge-success">
                                    <i class="fas fa-download"></i>
                                    {{ trans('app.Downloadable') }}
                                </span>
                                @endif
                                @if($product->category)
                                <span class="user-badge user-badge-primary">
                                    <i class="fas fa-folder"></i>
                                    {{ $product->category->name }}
                                </span>
                                @endif
                                @if($product->programmingLanguage)
                                <span class="user-badge user-badge-secondary">
                                    <i class="fas fa-code"></i>
                                    {{ $product->programmingLanguage->name }}
                                </span>
                                @endif
                            </div>

                            <div class="user-product-meta">
                                @if($product->updated_at)
                                <div class="user-meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <span>{{ $product->updated_at->format('M d, Y') }}</span>
                                </div>
                                @endif
                            </div>

                            <div class="user-product-actions">
                                <a href="{{ route('public.products.show', $product->slug) }}" class="user-product-button">
                                    <i class="fas fa-eye"></i>
                                    {{ trans('app.View Details') }}
                                </a>
                                @if($product->price > 0 && auth()->check())
                                    <a href="{{ route('payment.gateways', $product) }}" class="user-product-button primary">
                                        <i class="fas fa-shopping-cart"></i>
                                        {{ trans('app.Buy Now') }}
                                    </a>
                                @elseif($product->price > 0 && !auth()->check())
                                    <a href="{{ route('login') }}" class="user-product-button primary">
                                        <i class="fas fa-sign-in-alt"></i>
                                        {{ trans('app.Login to Buy') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($products->hasPages())
                    <div class="user-pagination">
                        {{ $products->links() }}
                    </div>
                    @endif
                    @else
                    <!-- Empty State -->
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h3 class="user-empty-state-title">
                            {{ trans('app.No products found') }}
                        </h3>
                        <p class="user-empty-state-description">
                            @if(request('search') || request('category') || request('language') || request('price_filter'))
                            {{ trans('app.No products match your current filters. Try adjusting your search criteria.') }}
                            @else
                            {{ trans('app.No products are currently available. Check back later for new products.') }}
                            @endif
                        </p>
                        @if(request('search') || request('category') || request('language') || request('price_filter'))
                        <a href="{{ route('public.products.index') }}" class="user-btn user-btn-info">
                            <i class="fas fa-refresh"></i>
                            {{ trans('app.Clear Filters') }}
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
            </div>


        </div>
    </div>
</div>
@endsection

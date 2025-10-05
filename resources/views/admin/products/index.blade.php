@extends('layouts.admin')

@section('title', trans('app.Products'))

@section('admin-content')
<!-- Admin Products Page -->
<div class="admin-products-page">
    <div class="admin-page-header modern-header">
        <div class="admin-page-header-content">
            <div class="admin-page-title">
                <h1 class="gradient-text">{{ trans('app.Products') }}</h1>
                <p class="admin-page-subtitle">{{ trans('app.Manage your products catalog') }}</p>
            </div>
            <div class="admin-page-actions">
                <a href="{{ route('admin.products.create') }}" class="admin-btn admin-btn-primary admin-btn-m">
                    <i class="fas fa-plus me-2"></i>
                    {{ trans('app.Add Product') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-filter me-2"></i>{{ trans('app.Filters') }}</h2>
            <div class="admin-section-actions">
                <div class="admin-search-box">
                    <input type="text" class="admin-form-input" id="search" name="q" value="{{ request('q') }}" 
                           placeholder="{{ trans('app.Search products') }}">
                    <i class="fas fa-search admin-search-icon"></i>
                </div>
            </div>
        </div>
        <div class="admin-section-content">
            <form action="{{ route('admin.products.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label class="admin-form-label" for="category_id">
                                <i class="fas fa-folder me-1"></i>{{ trans('app.Category') }}
                            </label>
                            <select id="category_id" name="category_id" class="admin-form-input">
                                <option value="">{{ trans('app.All Categories') }}</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category_id')==$category->id)>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-form-group">
                            <label class="admin-form-label" for="status-filter">
                                <i class="fas fa-toggle-on me-1"></i>{{ trans('app.Status') }}
                            </label>
                            <select id="status-filter" class="admin-form-input">
                                <option value="">{{ trans('app.All Status') }}</option>
                                <option value="active">{{ trans('app.Active') }}</option>
                                <option value="inactive">{{ trans('app.Inactive') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-cube me-2"></i>{{ trans('app.All Products') }}</h2>
            <span class="admin-badge admin-badge-info">{{ $products->total() }} {{ trans('app.Products') }}</span>
        </div>
        <div class="admin-section-content">
            @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0 products-table">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">{{ trans('app.Image') }}</th>
                                <th>{{ trans('app.Name') }}</th>
                                <th>{{ trans('app.Category') }}</th>
                                <th>{{ trans('app.Language') }}</th>
                                <th class="text-end">{{ trans('app.Price') }}</th>
                                <th class="text-center">{{ trans('app.Stock') }}</th>
                                <th class="text-center">{{ trans('app.Status') }}</th>
                                <th class="text-center">{{ trans('app.Flags') }}</th>
                                <th class="text-center">{{ trans('app.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr class="product-row" data-name="{{ strtolower($product->name) }}" data-category="{{ $product->category_id ?? '' }}" data-status="{{ $product->is_active ? 'active' : 'inactive' }}">
                                <td class="text-center">
                                    @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                        class="rounded product-image">
                                    @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center product-avatar">
                                        <span class="text-muted small fw-bold">{{ substr($product->name, 0, 1) }}</span>
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <a href="{{ route('admin.products.show', $product) }}" class="text-decoration-none">
                                            {{ $product->name }}
                                        </a>
                                    </div>
                                    <small class="text-muted">{{ $product->slug }}</small>
                                </td>
                                <td>
                                    <span class="text-muted">{{ optional($product->category)->name ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ optional($product->programmingLanguage)->name ?? '—' }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="fw-semibold">{{ $product->formatted_price }}</div>
                                    @if($product->tax_rate)
                                    <small class="text-muted">{{ trans('app.Tax') }}:
                                        {{ rtrim(rtrim(number_format($product->tax_rate, 2, '.', ''), '0'), '.') }}%</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $product->isInStock() ? 'bg-success' : 'bg-danger' }}">
                                        {{ $product->stock_status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $product->is_active ? trans('app.Active') : trans('app.Inactive') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                        @if($product->is_featured)
                                        <span class="badge bg-warning text-dark">{{ trans('app.Featured') }}</span>
                                        @endif
                                        @if($product->is_popular)
                                        <span class="badge bg-info">{{ trans('app.Popular') }}</span>
                                        @endif
                                        @if($product->requires_domain)
                                        <span class="badge bg-secondary">{{ trans('app.Requires Domain') }}</span>
                                        @endif
                                        @if($product->is_downloadable)
                                        <span class="badge bg-success">{{ trans('app.Downloadable') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group-vertical btn-group-sm" role="group">
                                        <a href="{{ route('admin.products.show', $product) }}" 
                                           class="btn btn-info btn-sm" title="View Product Details">
                                            <i class="fas fa-eye"></i>
                                            <span class="ms-1">View</span>
                                        </a>
                                        
                                        <a href="{{ route('admin.products.edit', $product) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit me-1"></i>
                                            {{ trans('app.Edit') }}
                                        </a>
                                        
                                        <a href="{{ route('admin.products.logs', $product) }}" 
                                           class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-list me-1"></i>
                                            {{ trans('app.Logs') }}
                                        </a>
                                        
                                        <a href="{{ route('admin.products.files.index', $product) }}" 
                                           class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-file-upload me-1"></i>
                                            {{ trans('app.Files') }}
                                        </a>

                                        @if($product->integration_file_path)
                                        <a href="{{ route('admin.products.download-integration', $product) }}" 
                                           class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-download me-1"></i>
                                            {{ trans('app.Download') }}
                                        </a>
                                        @endif

                                        <form action="{{ route('admin.products.regenerate-integration', $product) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning btn-sm w-100">
                                                <i class="fas fa-sync me-1"></i>
                                                {{ trans('app.Regenerate') }}
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" 
                                              class="d-inline" data-confirm="delete-product">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                                <i class="fas fa-trash me-1"></i>
                                                {{ trans('app.Delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @if($products->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $products->links() }}
            </div>
            @endif
            @else
            <!-- Enhanced Empty State -->
            <div class="admin-empty-state products-empty-state">
                <div class="admin-empty-state-content">
                    <div class="admin-empty-state-icon">
                        <i class="fas fa-cube"></i>
                    </div>
                    <div class="admin-empty-state-text">
                        <h3 class="admin-empty-state-title">{{ trans('app.No Products Found') }}</h3>
                        <p class="admin-empty-state-description">
                            {{ trans('app.Get started by adding your first product') }}
                        </p>
                    </div>
                    <div class="admin-empty-state-actions">
                        <a href="{{ route('admin.products.create') }}" class="admin-btn admin-btn-primary admin-btn-m">
                            <i class="fas fa-plus me-2"></i>
                            {{ trans('app.Add Your First Product') }}
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="admin-btn admin-btn-secondary admin-btn-m">
                            <i class="fas fa-arrow-left me-2"></i>
                            {{ trans('app.Back to Dashboard') }}
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
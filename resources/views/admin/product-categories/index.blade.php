@extends('layouts.admin')

@section('admin-content')
<!-- Admin Product Categories Page -->
<div class="admin-product-categories-page">
    <div class="admin-page-header modern-header">
        <div class="admin-page-header-content">
            <div class="admin-page-title">
                <h1 class="gradient-text">{{ trans('app.product_categories') }}</h1>
                <p class="admin-page-subtitle">{{ trans('app.manage_product_categories') }}</p>
            </div>
            <div class="admin-page-actions">
                <a href="{{ route('admin.product-categories.create') }}" class="admin-btn admin-btn-primary admin-btn-m">
                    <i class="fas fa-plus me-2"></i>
                    {{ trans('app.new_category') }}
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
                    <input type="text" class="admin-form-input" id="search" 
                           placeholder="{{ trans('app.search_categories') }}">
                    <i class="fas fa-search admin-search-icon"></i>
                </div>
            </div>
        </div>
        <div class="admin-section-content">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="admin-form-group">
                        <label class="admin-form-label" for="status">
                            <i class="fas fa-toggle-on me-1"></i>{{ trans('app.Status') }}
                        </label>
                        <select id="status" class="admin-form-input">
                            <option value="">{{ trans('app.All Status') }}</option>
                            <option value="active">{{ trans('app.Active') }}</option>
                            <option value="inactive">{{ trans('app.Inactive') }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="admin-form-group">
                        <label class="admin-form-label" for="sort">
                            <i class="fas fa-sort me-1"></i>{{ trans('app.Sort By') }}
                        </label>
                        <select id="sort" class="admin-form-input">
                            <option value="name">{{ trans('app.Name') }}</option>
                            <option value="products">{{ trans('app.Products Count') }}</option>
                            <option value="sort_order">{{ trans('app.sort_order') }}</option>
                            <option value="created_at">{{ trans('app.Created Date') }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="admin-form-group">
                        <label class="admin-form-label">&nbsp;</label>
                        <button type="button" class="admin-btn admin-btn-secondary admin-btn-m w-100" id="reset-filters-btn">
                            <i class="fas fa-refresh me-2"></i>
                            {{ trans('app.Reset') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Categories Table -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-folder me-2"></i>{{ trans('app.all_categories') }}</h2>
            <span class="admin-badge admin-badge-info">{{ $categories->count() }} {{ trans('app.Categories') }}</span>
        </div>
        <div class="admin-section-content">
            @if($categories->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0 product-categories-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">{{ trans('app.Image') }}</th>
                                    <th>{{ trans('app.Name') }}</th>
                                    <th>{{ trans('app.Slug') }}</th>
                                    <th class="text-center">{{ trans('app.Products') }}</th>
                                    <th class="text-center">{{ trans('app.Status') }}</th>
                                    <th class="text-center">{{ trans('app.sort_order') }}</th>
                                    <th class="text-center">{{ trans('app.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                <tr class="category-row" data-name="{{ strtolower($category->name) }}" data-status="{{ $category->is_active ? 'active' : 'inactive' }}">
                                    <td class="text-center">
                                        @if($category->image)
                                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                                            class="rounded category-image">
                                        @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center category-avatar">
                                            <span class="text-muted small fw-bold">{{ substr($category->name, 0, 1) }}</span>
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $category->name }}</div>
                                        <small class="text-muted">{{ $category->slug }}</small>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $category->slug }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $category->products->count() }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $category->is_active ? trans('app.Active') : trans('app.Inactive') }}
                                            </span>
                                            @if($category->is_featured)
                                            <span class="badge bg-warning text-dark">{{ trans('app.Featured') }}</span>
                                            @endif
                                            @if($category->show_in_menu)
                                            <span class="badge bg-info">{{ trans('app.In Menu') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-muted">{{ $category->sort_order ?? '—' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                            <a href="{{ route('admin.product-categories.edit', $category) }}"
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-edit me-1"></i>
                                                {{ trans('app.Edit') }}
                                            </a>

                                            <form action="{{ route('admin.product-categories.destroy', $category) }}" method="POST"
                                                  class="d-inline" data-confirm="delete-category">
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
            @else
            <!-- Enhanced Empty State -->
            <div class="admin-empty-state product-categories-empty-state">
                <div class="admin-empty-state-content">
                    <div class="admin-empty-state-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="admin-empty-state-text">
                        <h3 class="admin-empty-state-title">{{ trans('app.No Categories Found') }}</h3>
                        <p class="admin-empty-state-description">
                            {{ trans('app.Create your first product category to get started') }}
                        </p>
                    </div>
                    <div class="admin-empty-state-actions">
                        <a href="{{ route('admin.product-categories.create') }}" class="admin-btn admin-btn-primary admin-btn-m">
                            <i class="fas fa-plus me-2"></i>
                            {{ trans('app.Create Category') }}
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

    @if($categories->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $categories->links() }}
    </div>
    @endif
</div>

<!-- JavaScript is now handled by admin-categories.js -->
@endsection
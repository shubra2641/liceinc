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
                                <i class="fas fa-eye text-primary me-2"></i>
                                {{ trans('app.View Product') }}
                            </h1>
                            <p class="text-muted mb-0">{{ $product->name }}</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>
                                {{ trans('app.Edit Product') }}
                            </a>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ trans('app.Back to Products') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ trans('app.Basic Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-tag text-primary me-1"></i>
                                {{ trans('app.Product Name') }}
                            </label>
                            <p class="form-control-plaintext">{{ $product->name }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-folder text-success me-1"></i>
                                {{ trans('app.Category') }}
                            </label>
                            <p class="form-control-plaintext">
                                @if($product->category)
                                <span class="badge bg-success">{{ $product->category->name }}</span>
                                @else
                                <span class="text-muted">{{ trans('app.No Category') }}</span>
                                @endif
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-code text-purple me-1"></i>
                                {{ trans('app.Programming Language') }}
                            </label>
                            <p class="form-control-plaintext">
                                @if($product->programmingLanguage)
                                <i class="{{ $product->programmingLanguage->icon ?? 'fas fa-code' }} me-1"></i>
                                {{ $product->programmingLanguage->name }}
                                @else
                                <span class="text-muted">{{ trans('app.No Language') }}</span>
                                @endif
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-globe text-warning me-1"></i>
                                {{ trans('app.Requires Domain') }}
                            </label>
                            <p class="form-control-plaintext">
                                @if($product->requires_domain)
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>{{ trans('app.Yes') }}
                                </span>
                                @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-times me-1"></i>{{ trans('app.No') }}
                                </span>
                                @endif
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-book text-info me-1"></i>
                                {{ trans('app.Knowledge Base Section') }}
                            </label>
                            <p class="form-control-plaintext">
                                @if($product->kbCategory)
                                <span class="badge bg-info">{{ $product->kbCategory->name }}</span>
                                @else
                                <span class="text-muted">{{ trans('app.No KB Section') }}</span>
                                @endif
                            </p>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-align-left text-secondary me-1"></i>
                                {{ trans('app.Product Description') }}
                            </label>
                            <div class="form-control-plaintext">
                                {{ $product->description ?: '<span class="text-muted">' . trans('app.No Description') .
                                    '</span>' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        {{ trans('app.Additional Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-dollar-sign text-success me-1"></i>
                                {{ trans('app.Price') }}
                            </label>
                            <p class="form-control-plaintext">
                                @if($product->price)
                                ${{ number_format($product->price, 2) }}
                                @else
                                <span class="text-muted">{{ trans('app.Free') }}</span>
                                @endif
                            </p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-code-branch text-primary me-1"></i>
                                {{ trans('app.Version') }}
                            </label>
                            <p class="form-control-plaintext">
                                {{ $product->latest_version ?: '<span class="text-muted">' . trans('app.No Version') .
                                    '</span>' }}
                            </p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fab fa-envato text-warning me-1"></i>
                                {{ trans('app.Envato Item ID') }}
                            </label>
                            <p class="form-control-plaintext">
                                {{ $product->envato_item_id ?: '<span class="text-muted">' . trans('app.No Envato ID') .
                                    '</span>' }}
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-headset text-danger me-1"></i>
                                {{ trans('app.Support Days') }}
                            </label>
                            <p class="form-control-plaintext">
                                {{ $product->support_days ? $product->support_days . ' ' . trans('app.days') : '<span
                                    class="text-muted">' . trans('app.No Support') . '</span>' }}
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-boxes text-warning me-1"></i>
                                {{ trans('app.Stock Quantity') }}
                            </label>
                            <p class="form-control-plaintext">
                                @if($product->stock_quantity == -1)
                                <span class="badge bg-success">{{ trans('app.Unlimited Stock') }}</span>
                                @else
                                {{ $product->stock_quantity ?: 0 }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features and Requirements -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-star me-2"></i>
                        {{ trans('app.Features Requirements') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-list-check text-success me-1"></i>
                                {{ trans('app.Features') }}
                            </label>
                            <div class="form-control-plaintext">
                                @if($product->features && is_array($product->features) && count($product->features) > 0)
                                <ul class="list-unstyled mb-0">
                                    @foreach($product->features as $feature)
                                    <li class="mb-1">
                                        <i class="fas fa-check text-success me-2"></i>
                                        {{ $feature }}
                                    </li>
                                    @endforeach
                                </ul>
                                @else
                                <span class="text-muted">{{ trans('app.No Features') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-clipboard-check text-primary me-1"></i>
                                {{ trans('app.Requirements') }}
                            </label>
                            <div class="form-control-plaintext">
                                @if($product->requirements && is_array($product->requirements) &&
                                count($product->requirements) > 0)
                                <ul class="list-unstyled mb-0">
                                    @foreach($product->requirements as $requirement)
                                    <li class="mb-1">
                                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                        {{ $requirement }}
                                    </li>
                                    @endforeach
                                </ul>
                                @else
                                <span class="text-muted">{{ trans('app.No Requirements') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-book text-purple me-1"></i>
                            {{ trans('app.Installation Guide') }}
                        </label>
                        <div class="form-control-plaintext">
                            @if($product->installation_guide && is_array($product->installation_guide) &&
                            count($product->installation_guide) > 0)
                            <ol class="list-unstyled mb-0">
                                @foreach($product->installation_guide as $step)
                                <li class="mb-2">
                                    <i class="fas fa-arrow-right text-primary me-2"></i>
                                    {{ $step }}
                                </li>
                                @endforeach
                            </ol>
                            @else
                            <span class="text-muted">{{ trans('app.No Installation Guide') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Media and Assets -->
            <div class="card mb-4">
                <div class="card-header bg-pink text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-images me-2"></i>
                        {{ trans('app.Media and Assets') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-image text-primary me-1"></i>
                                {{ trans('app.Main Image') }}
                            </label>
                            <div class="form-control-plaintext">
                                @if($product->image)
                                <img src="{{ Storage::url($product->image) }}" alt="{{ trans('app.Product Image') }}"
                                    class="img-thumbnail product-image">
                                @else
                                <span class="text-muted">{{ trans('app.No Image') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-images text-success me-1"></i>
                                {{ trans('app.Gallery Images') }}
                            </label>
                            <div class="form-control-plaintext">
                                @if($product->gallery_images && count($product->gallery_images) > 0)
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($product->gallery_images as $galleryImage)
                                    <img src="{{ Storage::url($galleryImage) }}" alt="{{ trans('app.Gallery Image') }}"
                                        class="img-thumbnail product-gallery-image">
                                    @endforeach
                                </div>
                                @else
                                <span class="text-muted">{{ trans('app.No Gallery Images') }}</span>
                                @endif
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
                        {{ trans('app.SEO') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-heading text-primary me-1"></i>
                                {{ trans('app.Meta Title') }}
                            </label>
                            <p class="form-control-plaintext">
                                {{ $product->meta_title ?: '<span class="text-muted">' . trans('app.No Meta Title') .
                                    '</span>' }}
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-tags text-warning me-1"></i>
                                {{ trans('app.Tags') }}
                            </label>
                            <p class="form-control-plaintext">
                                @if($product->tags && is_array($product->tags) && count($product->tags) > 0)
                                @foreach($product->tags as $tag)
                                <span class="badge bg-secondary me-1">{{ $tag }}</span>
                                @endforeach
                                @else
                                <span class="text-muted">{{ trans('app.No Tags') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-file-alt text-success me-1"></i>
                            {{ trans('app.Meta Description') }}
                        </label>
                        <p class="form-control-plaintext">
                            {{ $product->meta_description ?: '<span class="text-muted">' . trans('app.No Meta
                                Description') . '</span>' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Product Settings -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog me-2"></i>
                        {{ trans('app.Product Settings') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">
                            <i class="fas fa-toggle-on text-success me-1"></i>
                            {{ trans('app.Active') }}
                        </span>
                        @if($product->is_active)
                        <span class="badge bg-success">{{ trans('app.Yes') }}</span>
                        @else
                        <span class="badge bg-secondary">{{ trans('app.No') }}</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">
                            <i class="fas fa-star text-warning me-1"></i>
                            {{ trans('app.Featured') }}
                        </span>
                        @if($product->is_featured)
                        <span class="badge bg-warning">{{ trans('app.Yes') }}</span>
                        @else
                        <span class="badge bg-secondary">{{ trans('app.No') }}</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">
                            <i class="fas fa-download text-info me-1"></i>
                            {{ trans('app.Downloadable') }}
                        </span>
                        @if($product->is_downloadable)
                        <span class="badge bg-info">{{ trans('app.Yes') }}</span>
                        @else
                        <span class="badge bg-secondary">{{ trans('app.No') }}</span>
                        @endif
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
                                <h4 class="text-primary">{{ $product->licenses()->count() }}</h4>
                                <p class="text-muted small mb-0">{{ trans('app.Licenses') }}</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-success">{{ $product->invoices()->count() }}</h4>
                                <p class="text-muted small mb-0">{{ trans('app.Invoices') }}</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-info">{{ $product->created_at->format('M Y') }}</h4>
                                <p class="text-muted small mb-0">{{ trans('app.Created') }}</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-warning">{{ $product->updated_at->format('M Y') }}</h4>
                                <p class="text-muted small mb-0">{{ trans('app.Updated') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- License Integration -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-code me-2"></i>
                        {{ trans('app.License Integration') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($product->programmingLanguage)
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-check-circle me-1"></i>
                            {{ trans('app.Integration File Generated') }}
                        </h6>
                        <p class="mb-2">
                            <strong>{{ trans('app.Language') }}:</strong> {{ $product->programmingLanguage->name }}<br>
                            <strong>{{ trans('app.File') }}:</strong> {{ basename($product->integration_file_path ??
                            trans('app.Not generated')) }}
                        </p>
                        <div class="d-grid gap-2">
                            @if($product->integration_file_path &&
                            \Illuminate\Support\Facades\Storage::disk('public')->exists($product->integration_file_path))
                            <a href="{{ route('admin.products.download-integration', $product) }}"
                                class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i>{{ trans('app.Download') }}
                            </a>
                            @endif
                            <form method="post" action="{{ route('admin.products.regenerate-integration', $product) }}"
                                class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                                    <i class="fas fa-sync me-1"></i>{{ trans('app.Regenerate') }}
                                </button>
                            </form>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            {{ trans('app.Programming Language Required') }}
                        </h6>
                        <p class="mb-0">{{ trans('app.Set Programming Language Message') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Create Test License -->
            <div class="card mb-4">
                <div class="card-header bg-purple text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>
                        {{ trans('app.Create Test License') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">{{ trans('app.Create Test License Description') }}</p>
                    <form method="post" action="{{ route('admin.products.generate-license', $product) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="domain" class="form-label">
                                <i class="fas fa-globe text-primary me-1"></i>
                                {{ trans('app.Domain') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="domain" name="domain" placeholder="example.com"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope text-success me-1"></i>
                                {{ trans('app.Customer Email') }} <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="customer@example.com" required>
                        </div>
                        <button type="submit" class="btn btn-purple w-100">
                            <i class="fas fa-plus me-1"></i>{{ trans('app.Create Test License') }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ trans('app.Danger Zone') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">{{ trans('app.Delete Product Warning') }}</p>
                    <form method="post" action="{{ route('admin.products.destroy', $product) }}"
                        data-confirm="delete-product">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-1"></i>{{ trans('app.Delete Product') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Updates Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-sync-alt me-2"></i>
                            {{ trans('app.Product Updates') }}
                        </h5>
                        <a href="{{ route('admin.product-updates.create', ['product_id' => $product->id]) }}"
                            class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            {{ trans('app.Add Update') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="productUpdatesTable">
                            <thead>
                                <tr>
                                    <th>{{ trans('app.Version') }}</th>
                                    <th>{{ trans('app.Title') }}</th>
                                    <th>{{ trans('app.Type') }}</th>
                                    <th>{{ trans('app.File Size') }}</th>
                                    <th>{{ trans('app.Released') }}</th>
                                    <th>{{ trans('app.Status') }}</th>
                                    <th>{{ trans('app.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($product->updates as $update)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">{{ $update->version }}</span>
                                        @if($update->is_major)
                                        <span class="badge bg-warning ms-1">{{ trans('app.Major') }}</span>
                                        @endif
                                        @if($update->is_required)
                                        <span class="badge bg-danger ms-1">{{ trans('app.Required') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $update->title }}</td>
                                    <td>
                                        @if($update->is_major)
                                        <span class="text-warning">{{ trans('app.Major Update') }}</span>
                                        @else
                                        <span class="text-info">{{ trans('app.Minor Update') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $update->formatted_file_size }}</td>
                                    <td>{{ $update->released_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $update->is_active ? 'success' : 'secondary' }}">
                                            {{ $update->is_active ? trans('app.Active') : trans('app.Inactive') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.product-updates.show', $update) }}"
                                                class="btn btn-outline-info" title="{{ trans('app.View') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.product-updates.edit', $update) }}"
                                                class="btn btn-outline-primary" title="{{ trans('app.Edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-outline-{{ $update->is_active ? 'warning' : 'success' }}"
                                                onclick="toggleUpdateStatus({{ (int)$update->id }})"
                                                title="{{ $update->is_active ? trans('app.Deactivate') : trans('app.Activate') }}">
                                                <i class="fas fa-{{ $update->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                            <form method="POST"
                                                action="{{ route('admin.product-updates.destroy', $update) }}"
                                                class="inline-form"
                                                onsubmit="return confirm('{{ trans('app.Are you sure you want to delete this update?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger"
                                                    title="{{ trans('app.Delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle fa-2x mb-3 d-block"></i>
                                        {{ trans('app.No updates available for this product') }}
                                        <br>
                                        <a href="{{ route('admin.product-updates.create', ['product_id' => $product->id]) }}"
                                            class="btn btn-primary btn-sm mt-2">
                                            <i class="fas fa-plus me-1"></i>
                                            {{ trans('app.Add First Update') }}
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
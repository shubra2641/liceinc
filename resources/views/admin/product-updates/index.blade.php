@extends('layouts.admin')

@section('title', trans('app.Product Updates'))

@section('admin-content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-sync-alt text-success me-2"></i>
                                {{ trans('app.Product Updates') }}
                            </h1>
                            @if($product)
                                <p class="text-muted mb-0">{{ $product->name }}</p>
                            @else
                                <p class="text-muted mb-0">{{ trans('app.All Products') }}</p>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            @if($product)
                                <a href="{{ route('admin.product-updates.create', ['product_id' => $product->id]) }}" class="btn btn-success">
                                    <i class="fas fa-plus me-1"></i>
                                    {{ trans('app.Add Update') }}
                                </a>
                            @else
                                <a href="{{ route('admin.product-updates.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus me-1"></i>
                                    {{ trans('app.Add Update') }}
                                </a>
                            @endif
                            @if($product)
                                <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>
                                    {{ trans('app.Back to Product') }}
                                </a>
                            @else
                                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>
                                    {{ trans('app.Back to Products') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-sync-alt me-3 text-success"></i>
                            <div>
                                <h5 class="card-title mb-0">{{ trans('app.All Updates') }}</h5>
                                <small class="text-muted">{{ trans('app.Manage product updates') }}</small>
                            </div>
                        </div>
                        @if($product)
                            <div>
                                <span class="badge bg-info fs-6">{{ $product->updates->count() }} {{ trans('app.Updates') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-body p-0">
                    @if($product && $product->updates->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">{{ trans('app.Version') }}</th>
                                    <th>{{ trans('app.Title') }}</th>
                                    <th class="text-center">{{ trans('app.Type') }}</th>
                                    <th class="text-center">{{ trans('app.File Size') }}</th>
                                    <th class="text-center">{{ trans('app.Released') }}</th>
                                    <th class="text-center">{{ trans('app.Status') }}</th>
                                    <th class="text-center">{{ trans('app.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->updates as $update)
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $update->version }}</span>
                                        @if($update->is_major)
                                            <span class="badge bg-warning ms-1">{{ trans('app.Major') }}</span>
                                        @endif
                                        @if($update->is_required)
                                            <span class="badge bg-danger ms-1">{{ trans('app.Required') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $update->title }}</div>
                                        @if($update->description)
                                        <small class="text-muted">{{ Str::limit($update->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($update->is_major)
                                            <span class="text-warning">{{ trans('app.Major Update') }}</span>
                                        @else
                                            <span class="text-info">{{ trans('app.Minor Update') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ $update->formatted_file_size ?? 'N/A' }}
                                    </td>
                                    <td class="text-center">
                                        {{ $update->released_at?->format('Y-m-d H:i') ?? 'N/A' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $update->is_active ? 'success' : 'secondary' }}">
                                            {{ $update->is_active ? trans('app.Active') : trans('app.Inactive') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.product-updates.show', $update) }}" class="btn btn-outline-info" title="{{ trans('app.View') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.product-updates.edit', $update) }}" class="btn btn-outline-primary" title="{{ trans('app.Edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-{{ $update->is_active ? 'warning' : 'success' }}" 
                                                    onclick="toggleUpdateStatus({{ (int)$update->id }})" 
                                                    title="{{ $update->is_active ? trans('app.Deactivate') : trans('app.Activate') }}">
                                                <i class="fas fa-{{ $update->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                            <form method="POST" action="{{ route('admin.product-updates.destroy', $update) }}" 
                                                  class="inline-form" 
                                                  onsubmit="return confirm('{{ trans('app.Are you sure you want to delete this update?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="{{ trans('app.Delete') }}">
                                                    <i class="fas fa-trash"></i>
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
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-sync-alt text-muted empty-state-icon"></i>
                        </div>
                        <h4 class="text-muted">{{ trans('app.No Updates Found') }}</h4>
                        <p class="text-muted mb-4">{{ trans('app.Get started by adding your first update') }}</p>
                        @if($product)
                            <a href="{{ route('admin.product-updates.create', ['product_id' => $product->id]) }}" class="btn btn-success btn-lg">
                        @else
                            <a href="{{ route('admin.product-updates.create') }}" class="btn btn-success btn-lg">
                        @endif
                            <i class="fas fa-plus me-2"></i>
                            {{ trans('app.Add Your First Update') }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

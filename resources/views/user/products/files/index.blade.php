@extends('layouts.user')

@section('title', trans('app.Product Files') . ' - ' . $product->name)
@section('page-title', trans('app.Product Files'))
@section('page-subtitle', $product->name)

@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-download"></i>
                {{ trans('app.Product Files') }}: {{ $product->name }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.Download and manage your product files') }}
            </p>
        </div>

        <div class="user-card-content">
            <!-- Action Buttons -->
            <div class="user-action-buttons">
                <a href="{{ route('user.products.show', $product) }}" class="user-action-button secondary">
                    <i class="fas fa-arrow-left"></i>
                    {{ trans('app.Back to Product') }}
                </a>
                @if(isset($latestFile) && $latestFile)
                    <a href="{{ route('user.products.files.download-latest', $product) }}" class="user-action-button">
                        <i class="fas fa-download"></i>
                        @if(isset($latestUpdate) && $latestUpdate)
                            {{ trans('app.Download Latest Update') }} (v{{ $latestUpdate->version }})
                        @else
                            {{ trans('app.Download Product') }}
                        @endif
                    </a>
                @endif
                @if(isset($allVersions) && count($allVersions) > 1)
                    <a href="{{ route('user.products.files.download-all', $product) }}" class="user-action-button">
                        <i class="fas fa-download"></i>
                        {{ trans('app.Download All as ZIP') }}
                    </a>
                @endif
            </div>
            @if(isset($permissions) && !$permissions['can_download'])
                <div class="user-alert user-alert-warning">
                    <div class="user-alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="user-alert-content">
                        <h4 class="user-alert-title">{{ trans('app.Cannot Download Files') }}</h4>
                        <p class="user-alert-message">{{ $permissions['message'] }}</p>
                        
                        @if(!$permissions['has_license'])
                            <div class="user-alert-actions">
                                <a href="{{ route('user.products.show', $product) }}" class="user-btn user-btn-primary">
                                    <i class="fas fa-shopping-cart"></i>
                                    {{ trans('app.Purchase Product') }}
                                </a>
                            </div>
                        @elseif(!$permissions['has_paid_invoice'])
                            <div class="user-alert-actions">
                                <a href="{{ route('user.invoices.index') }}" class="user-btn user-btn-warning">
                                    <i class="fas fa-credit-card"></i>
                                    {{ trans('app.Pay Invoice') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif(isset($allVersions) && count($allVersions) > 0)
                <!-- Update Information -->
                @if(isset($latestUpdate) && $latestUpdate)
                    <div class="user-alert user-alert-info">
                        <div class="user-alert-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <div class="user-alert-content">
                            <h4 class="user-alert-title">{{ trans('app.Update Available') }}</h4>
                            <p class="user-alert-message">
                                {{ trans('app.A new update is available') }}: <strong>{{ $latestUpdate->title }} v{{ $latestUpdate->version }}</strong>
                                @if($latestUpdate->description)
                                    <br><small class="text-muted">{{ Str::limit($latestUpdate->description, 100) }}</small>
                                @endif
                            </p>
                        </div>
                    </div>
                @else
                    <div class="user-alert user-alert-success">
                        <div class="user-alert-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="user-alert-content">
                            <h4 class="user-alert-title">{{ trans('app.Files Available') }}</h4>
                            <p class="user-alert-message">{{ trans('app.You can download the following files because you have a valid license and paid invoice for this product.') }}</p>
                        </div>
                    </div>
                @endif

                <!-- All Versions Grid -->
                <div class="user-stats-grid">
                    @foreach($allVersions as $file)
                        <div class="user-stat-card flex-column h-100">
                            <!-- File Header -->
                            <div class="user-stat-header">
                                <div class="user-stat-title">
                                    {{ is_array($file) ? ($file['original_name'] ?? 'Unknown') : ($file->original_name ?? 'Unknown') }}
                                    @if(isset($file->is_update) && $file->is_update)
                                        <span class="inline-flex items-center p-2-8 bg-orange-500 rounded-12 fs-11 fw-500 ml-8">
                                            <i class="fas fa-sync-alt mr-4"></i>
                                            {{ trans('app.Update') }}
                                        </span>
                                        @if(isset($file->update_info) && $file->update_info->is_required)
                                            <span class="inline-flex items-center p-2-8 bg-red-500 rounded-12 fs-11 fw-500 ml-4">
                                                <i class="fas fa-exclamation-triangle mr-4"></i>
                                                {{ trans('app.Required') }}
                                            </span>
                                        @endif
                                    @endif
                                </div>
                                <div class="user-stat-icon {{ isset($file->is_update) && $file->is_update ? 'orange' : 'blue' }}">
                                    @php
                                        $fileExtension = is_array($file) ? ($file['file_extension'] ?? '') : ($file->file_extension ?? '');
                                    @endphp
                                    @if($fileExtension == 'zip')
                                        <i class="fas fa-file-archive"></i>
                                    @elseif(in_array($fileExtension, ['pdf']))
                                        <i class="fas fa-file-pdf"></i>
                                    @elseif(in_array($fileExtension, ['doc', 'docx']))
                                        <i class="fas fa-file-word"></i>
                                    @elseif(in_array($fileExtension, ['xls', 'xlsx']))
                                        <i class="fas fa-file-excel"></i>
                                    @elseif(in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']))
                                        <i class="fas fa-file-image"></i>
                                    @else
                                        <i class="fas fa-file"></i>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Content Area (flexible) -->
                            <div class="flex-1 flex-column">
                                <!-- File Info -->
                                <div class="user-stat-value">
                                    @php
                                        $fileSize = is_array($file) ? ($file['file_size'] ?? 0) : ($file->file_size ?? 0);
                                        $formattedSize = is_array($file) ? ($file['formatted_size'] ?? null) : ($file->formatted_size ?? null);
                                    @endphp
                                    {{ $formattedSize ?? number_format($fileSize / 1024 / 1024, 2) . ' MB' }}
                                </div>
                                <p class="fs-14 text-gray-500 margin-0">
                                    {{ strtoupper($fileExtension) }} {{ trans('app.File') }}
                                    @if(isset($file->is_update) && $file->is_update && isset($file->update_info))
                                        <br><span class="text-orange-500">v{{ $file->update_info->version }}</span>
                                    @endif
                                </p>
                                
                                <!-- File Description -->
                                @php
                                    $fileDescription = is_array($file) ? ($file['description'] ?? null) : ($file->description ?? null);
                                @endphp
                                @if($fileDescription)
                                    <div class="mt-12">
                                        <p class="fs-12 text-gray-400 margin-0">{{ Str::limit($fileDescription, 80) }}</p>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Bottom Section (always at bottom) -->
                            <div class="mt-auto">
                                <!-- File Stats -->
                                <div class="mt-12 d-flex justify-content-between fs-12 text-gray-500">
                                    @php
                                        $downloadCount = is_array($file) ? ($file['download_count'] ?? 0) : ($file->download_count ?? 0);
                                        $createdAt = is_array($file) ? ($file['created_at'] ?? null) : ($file->created_at ?? null);
                                    @endphp
                                    <span><i class="fas fa-download mr-4"></i>{{ $downloadCount }} {{ trans('app.Downloads') }}</span>
                                    <span><i class="fas fa-calendar mr-4"></i>{{ $createdAt ? (is_string($createdAt) ? $createdAt : $createdAt->format('M d, Y')) : 'N/A' }}</span>
                                </div>
                                
                                <!-- Download Button -->
                                <div class="mt-3">
                                    @if(isset($file->is_update) && $file->is_update)
                                        @if(isset($file->update_info) && $file->update_info->file_path)
                                            <a href="{{ route('user.products.files.download-update', [$product, $file->update_info->id]) }}" 
                                               class="user-action-button"
                                               title="{{ trans('app.Download Update') }} {{ is_array($file) ? ($file['original_name'] ?? 'Unknown') : ($file->original_name ?? 'Unknown') }}">
                                                <i class="fas fa-sync-alt"></i>
                                                {{ trans('app.Download Update') }}
                                            </a>
                                        @else
                                            <button class="user-action-button secondary" disabled
                                                    title="{{ trans('app.Update file not available') }}">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ trans('app.File Not Available') }}
                                            </button>
                                        @endif
                                    @else
                                        @php
                                            $fileId = is_array($file) ? ($file['id'] ?? null) : ($file->id ?? null);
                                        @endphp
                                        @if($fileId)
                                            <a href="{{ route('user.product-files.download', $fileId) }}" 
                                               class="user-action-button"
                                               title="{{ trans('app.Download') }} {{ is_array($file) ? ($file['original_name'] ?? 'Unknown') : ($file->original_name ?? 'Unknown') }}">
                                                <i class="fas fa-download"></i>
                                                {{ trans('app.Download File') }}
                                            </a>
                                        @else
                                            <button class="user-action-button secondary" disabled>
                                                <i class="fas fa-download"></i>
                                                {{ trans('app.Download File') }}
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Download Statistics -->
                <div class="user-stats-grid">
                    <div class="user-stat-card">
                        <div class="user-stat-header">
                            <div class="user-stat-title">{{ trans('app.Total Versions') }}</div>
                            <div class="user-stat-icon blue">
                                <i class="fas fa-file"></i>
                            </div>
                        </div>
                        <div class="user-stat-value">{{ count($allVersions) }}</div>
                        <p class="fs-14 text-gray-500 margin-0">{{ trans('app.Available versions') }}</p>
                    </div>

                    <div class="user-stat-card">
                        <div class="user-stat-header">
                            <div class="user-stat-title">{{ trans('app.Updates Available') }}</div>
                            <div class="user-stat-icon orange">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                        </div>
                        <div class="user-stat-value">{{ collect($allVersions)->where('is_update', true)->count() }}</div>
                        <p class="fs-14 text-gray-500 margin-0">{{ trans('app.Update files') }}</p>
                    </div>

                    <div class="user-stat-card">
                        <div class="user-stat-header">
                            <div class="user-stat-title">{{ trans('app.Base Files') }}</div>
                            <div class="user-stat-icon green">
                                <i class="fas fa-file-archive"></i>
                            </div>
                        </div>
                        <div class="user-stat-value">{{ collect($allVersions)->where('is_update', false)->count() }}</div>
                        <p class="fs-14 text-gray-500 margin-0">{{ trans('app.Original files') }}</p>
                    </div>

                    @if(isset($latestUpdate) && $latestUpdate)
                        <div class="user-stat-card">
                            <div class="user-stat-header">
                                <div class="user-stat-title">{{ trans('app.Latest Version') }}</div>
                                <div class="user-stat-icon orange">
                                    <i class="fas fa-sync-alt"></i>
                                </div>
                            </div>
                            <div class="user-stat-value">v{{ $latestUpdate->version }}</div>
                            <p class="fs-14 text-gray-500 margin-0">{{ $latestUpdate->title }}</p>
                        </div>

                        <div class="user-stat-card">
                            <div class="user-stat-header">
                                <div class="user-stat-title">{{ trans('app.Update Size') }}</div>
                                <div class="user-stat-icon purple">
                                    <i class="fas fa-hdd"></i>
                                </div>
                            </div>
                            <div class="user-stat-value">{{ $latestUpdate->file_size ? number_format($latestUpdate->file_size / 1024 / 1024, 2) . ' MB' : 'N/A' }}</div>
                            <p class="fs-14 text-gray-500 margin-0">{{ trans('app.Latest update file') }}</p>
                        </div>
                    @else
                        <div class="user-stat-card">
                            <div class="user-stat-header">
                                <div class="user-stat-title">{{ trans('app.Total Size') }}</div>
                                <div class="user-stat-icon purple">
                                    <i class="fas fa-hdd"></i>
                                </div>
                            </div>
                            <div class="user-stat-value">{{ collect($allVersions)->sum('file_size') > 0 ? number_format(collect($allVersions)->sum('file_size') / 1024 / 1024, 2) . ' MB' : '0 MB' }}</div>
                            <p class="fs-14 text-gray-500 margin-0">{{ trans('app.Combined size') }}</p>
                        </div>

                        <div class="user-stat-card">
                            <div class="user-stat-header">
                                <div class="user-stat-title">{{ trans('app.Last Updated') }}</div>
                                <div class="user-stat-icon orange">
                                    <i class="fas fa-calendar"></i>
                                </div>
                            </div>
                            <div class="user-stat-value">{{ collect($allVersions)->sortByDesc('created_at')->first() ? collect($allVersions)->sortByDesc('created_at')->first()->created_at->format('Y-m-d') : 'N/A' }}</div>
                            <p class="fs-14 text-gray-500 margin-0">{{ trans('app.Most recent file') }}</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="user-empty-state">
                    <div class="user-empty-state-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h4 class="user-empty-state-title">{{ trans('app.No files available') }}</h4>
                    <p class="user-empty-state-message">{{ trans('app.No files have been uploaded for this product yet.') }}</p>
                    <div class="user-empty-state-actions">
                        <a href="{{ route('user.products.show', $product) }}" class="user-btn user-btn-primary">
                            <i class="fas fa-arrow-left"></i>
                            {{ trans('app.Back to Product') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

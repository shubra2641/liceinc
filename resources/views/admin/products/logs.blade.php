@extends('layouts.admin')

@section('admin-content')
<!-- Enhanced Product Logs Page -->
<div class="admin-page">
    <!-- Modern Page Header -->
    <div class="admin-page-header modern-header">
        <div class="admin-page-header-content">
            <div class="admin-page-title">
                <h1 class="gradient-text">{{ trans('app.license_logs') }}: {{ $product->name }}</h1>
                <p class="admin-page-subtitle">{{ trans('app.verification_attempts_and_license_usage_logs') }}</p>
            </div>
            <div class="admin-page-actions">
                <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-secondary admin-btn-m">
                    <i class="fas fa-arrow-left w-4 h-4 mr-2"></i>
                    {{ trans('app.back_to_products') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Logs Statistics Cards -->
    <div class="dashboard-grid dashboard-grid-4 stats-grid-enhanced mb-6">
        <div class="stats-card stats-card-primary animate-slide-up">
            <div class="stats-card-background">
                <div class="stats-card-pattern"></div>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-header">
                    <div class="stats-card-icon logs"></div>
                </div>
                <div class="stats-card-body">
                    <div class="stats-card-value">{{ $logs->total() }}</div>
                    <div class="stats-card-label">{{ trans('app.Total Logs') }}</div>
                </div>
            </div>
        </div>

        <div class="stats-card stats-card-success animate-slide-up animate-delay-200">
            <div class="stats-card-background">
                <div class="stats-card-pattern"></div>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-header">
                    <div class="stats-card-icon success"></div>
                </div>
                <div class="stats-card-body">
                    <div class="stats-card-value">{{ $logs->where('status', 'success')->count() }}</div>
                    <div class="stats-card-label">{{ trans('app.Successful Verifications') }}</div>
                </div>
            </div>
        </div>

        <div class="stats-card stats-card-danger animate-slide-up animate-delay-300">
            <div class="stats-card-background">
                <div class="stats-card-pattern"></div>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-header">
                    <div class="stats-card-icon failed"></div>
                </div>
                <div class="stats-card-body">
                    <div class="stats-card-value">{{ $logs->where('status', 'failed')->count() }}</div>
                    <div class="stats-card-label">{{ trans('app.Failed Verifications') }}</div>
                </div>
            </div>
        </div>

        <div class="stats-card stats-card-info animate-slide-up animate-delay-400">
            <div class="stats-card-background">
                <div class="stats-card-pattern"></div>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-header">
                    <div class="stats-card-icon domains"></div>
                </div>
                <div class="stats-card-body">
                    <div class="stats-card-value">{{ $logs->pluck('domain')->unique()->count() }}</div>
                    <div class="stats-card-label">{{ trans('app.Unique Domains') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Logs Table -->
    <div class="admin-card">
        <div class="admin-section-content">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-list-alt w-5 h-5 mr-2 text-blue-500"></i>
                    <h3 class="admin-card-title">{{ trans('app.verification_logs') }}</h3>
                </div>
                <div class="flex items-center gap-3">
                    <div class="admin-form-group mb-0">
                        <input type="text" id="searchLogs" class="admin-form-input" 
                               placeholder="{{ trans('app.search_logs') }}" 
                               data-action="search-logs">
                    </div>
                    <button class="admin-btn admin-btn-success admin-btn-m" data-action="export-logs">
                        <i class="fas fa-download w-4 h-4 mr-2"></i>
                        {{ trans('app.Export') }}
                    </button>
                </div>
            </div>
        </div>
        <div class="admin-card-content">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <i class="fas fa-calendar-alt mr-1"></i>
                                {{ trans('app.date') }}
                            </th>
                            <th>
                                <i class="fas fa-key mr-1"></i>
                                {{ trans('app.serial') }}
                            </th>
                            <th>
                                <i class="fas fa-globe mr-1"></i>
                                {{ trans('app.domain') }}
                            </th>
                            <th>
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                {{ trans('app.ip_address') }}
                            </th>
                            <th>
                                <i class="fas fa-check-circle mr-1"></i>
                                {{ trans('app.status') }}
                            </th>
                            <th>
                                <i class="fas fa-user mr-1"></i>
                                {{ trans('app.user_agent') }}
                            </th>
                            <th>
                                <i class="fas fa-cog mr-1"></i>
                                {{ trans('app.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>
                                <div>
                                    <div class="fw-bold">{{ $log->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                </div>
                            </td>
                            <td>
                                <code class="bg-primary text-white px-2 py-1 rounded">{{ $log->serial }}</code>
                            </td>
                            <td>
                                <i class="fas fa-globe text-primary mr-1"></i>
                                <span>{{ $log->domain }}</span>
                            </td>
                            <td>
                                <i class="fas fa-map-marker-alt text-muted mr-1"></i>
                                <span class="font-monospace">{{ $log->ip_address }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $log->status === 'success' ? 'success' : ($log->status === 'failed' ? 'danger' : 'warning') }}">
                                    <i class="fas fa-{{ $log->status === 'success' ? 'check' : ($log->status === 'failed' ? 'times' : 'exclamation') }} mr-1"></i>
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted" title="{{ $log->user_agent }}">
                                    {{ Str::limit($log->user_agent, 30) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" 
                                        data-action="view-log-details" 
                                        data-log-id="{{ $log->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-list-alt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">{{ trans('app.no_logs_found') }}</h5>
                                <p class="text-muted">{{ trans('app.no_logs_found_for_this_product') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logDetailsModalLabel">{{ trans('app.log_details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="logDetailsContent">
                    <!-- Log details will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
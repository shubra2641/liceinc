@extends('layouts.admin')

@section('title', trans('license-logs.title'))

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
                                <i class="fas fa-shield-alt text-primary me-2"></i>
                                {{ trans('license-logs.title') }}
                            </h1>
                            <p class="text-muted mb-0">{{ trans('license-logs.subtitle') }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.license-verification-logs.export', request()->query()) }}" 
                               class="btn btn-outline-primary me-2">
                                <i class="fas fa-download me-1"></i>
                                {{ trans('license-logs.export_csv') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Statistics Section -->
    <div class="dashboard-grid dashboard-grid-4 stats-grid-enhanced mb-4">
        <!-- Total Attempts Stats Card -->
        <div class="stats-card stats-card-primary animate-slide-up">
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
                    <div class="stats-card-value">{{ number_format($stats['total_attempts']) }}</div>
                    <div class="stats-card-label">{{ trans('license-logs.total_attempts') }}</div>
                    <div class="stats-card-trend positive">
                        <i class="stats-trend-icon positive"></i>
                        <span>{{ trans('app.all_verification_attempts') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Successful Attempts Stats Card -->
        <div class="stats-card stats-card-success animate-slide-up animate-delay-200">
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
                    <div class="stats-card-value">{{ number_format($stats['successful_attempts']) }}</div>
                    <div class="stats-card-label">{{ trans('license-logs.successful_attempts') }}</div>
                    <div class="stats-card-trend positive">
                        <i class="stats-trend-icon positive"></i>
                        <span>{{ $stats['total_attempts'] > 0 ? round(($stats['successful_attempts'] / $stats['total_attempts']) * 100, 1) : 0 }}% {{ trans('app.of_total') }}</span> </div>
                </div>
            </div>
        </div>

        <!-- Failed Attempts Stats Card -->
        <div class="stats-card stats-card-danger animate-slide-up animate-delay-300">
            <div class="stats-card-background">
                <div class="stats-card-pattern"></div>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-header">
                    <div class="stats-card-icon tickets"></div>
                    <div class="stats-card-menu">
                        <button class="stats-menu-btn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
                <div class="stats-card-body">
                    <div class="stats-card-value">{{ number_format($stats['failed_attempts']) }}</div>
                    <div class="stats-card-label">{{ trans('license-logs.failed_attempts') }}</div>
                    <div class="stats-card-trend negative">
                        <i class="stats-trend-icon negative"></i>
                        <span>{{ $stats['total_attempts'] > 0 ? round(($stats['failed_attempts'] / $stats['total_attempts']) * 100, 1) : 0 }}% {{ trans('app.of_total') }}</span> </div>
                </div>
            </div>
        </div>

        <!-- Recent Failed Attempts Stats Card -->
        <div class="stats-card stats-card-warning animate-slide-up animate-delay-400">
            <div class="stats-card-background">
                <div class="stats-card-pattern"></div>
            </div>
            <div class="stats-card-content">
                <div class="stats-card-header">
                    <div class="stats-card-icon articles"></div>
                    <div class="stats-card-menu">
                        <button class="stats-menu-btn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
                <div class="stats-card-body">
                    <div class="stats-card-value">{{ number_format($stats['recent_failed_attempts']) }}</div>
                    <div class="stats-card-label">{{ trans('license-logs.recent_failed_attempts') }}</div>
                    <div class="stats-card-trend negative">
                        <i class="stats-trend-icon negative"></i>
                        <span>{{ trans('app.last_24_hours') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Suspicious Activity Alert -->
    @if(count($suspiciousActivity) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning border-0 shadow-sm">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle fs-4 text-warning"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="alert-heading mb-2">{{ trans('license-logs.suspicious_activity_detected') }}</h5>
                        <p class="mb-3">{{ trans('license-logs.suspicious_activity_description') }}</p>
                        <ul class="mb-0">
                            @foreach($suspiciousActivity as $activity)
                            <li class="mb-1">
                                <strong>{{ $activity['ip_address'] }}</strong> - 
                                {{ $activity['attempt_count'] }} {{ trans('license-logs.failed_attempts_from') }} 
                                ({{ trans('license-logs.last_attempt') }}: {{ \Carbon\Carbon::parse($activity['last_attempt'])->diffForHumans() }})
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter text-primary me-2"></i>
                        {{ trans('license-logs.filters') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">{{ trans('license-logs.status') }}</label>
                                <select name="status" class="form-select">
                                    <option value="">{{ trans('license-logs.all_status') }}</option>
                                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>{{ trans('license-logs.status_success') }}</option>
                                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ trans('license-logs.status_failed') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ trans('license-logs.source') }}</label>
                                <select name="source" class="form-select">
                                    <option value="">{{ trans('license-logs.all_sources') }}</option>
                                    @foreach($sources as $source)
                                    <option value="{{ $source }}" {{ request('source') === $source ? 'selected' : '' }}>
                                        {{ trans('license-logs.source_' . $source) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ trans('license-logs.domain') }}</label>
                                <input type="text" name="domain" class="form-control" placeholder="{{ trans('license-logs.domain') }}" 
                                       value="{{ request('domain') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ trans('license-logs.ip_address') }}</label>
                                <input type="text" name="ip" class="form-control" placeholder="{{ trans('license-logs.ip_address') }}" 
                                       value="{{ request('ip') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ trans('license-logs.date_from') }}</label>
                                <input type="date" name="date_from" class="form-control" 
                                       value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ trans('license-logs.date_to') }}</label>
                                <input type="date" name="date_to" class="form-control" 
                                       value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter me-1"></i> {{ trans('license-logs.apply_filters') }}
                                </button>
                                <a href="{{ route('admin.license-verification-logs.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> {{ trans('license-logs.clear_filters') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list text-primary me-2"></i>
                        {{ trans('license-logs.title') }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">{{ trans('license-logs.id') }}</th>
                                    <th class="border-0">{{ trans('license-logs.purchase_code') }}</th>
                                    <th class="border-0">{{ trans('license-logs.domain') }}</th>
                                    <th class="border-0">{{ trans('license-logs.ip_address') }}</th>
                                    <th class="border-0">{{ trans('license-logs.status') }}</th>
                                    <th class="border-0">{{ trans('license-logs.source') }}</th>
                                    <th class="border-0">{{ trans('license-logs.message') }}</th>
                                    <th class="border-0">{{ trans('license-logs.date') }}</th>
                                    <th class="border-0">{{ trans('license-logs.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td class="align-middle">{{ $log->id }}</td>
                                    <td class="align-middle">
                                        <code class="bg-light px-2 py-1 rounded">{{ $log->masked_purchase_code }}</code>
                                    </td>
                                    <td class="align-middle">{{ $log->domain }}</td>
                                    <td class="align-middle">
                                        <span class="badge bg-info">{{ $log->ip_address }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge {{ $log->status_badge_class }}">
                                            {{ trans('license-logs.status_' . $log->status) }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge {{ $log->source_badge_class }}">
                                            {{ trans('license-logs.source_' . $log->verification_source) }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="text-truncate d-inline-block license-response-message" 
                                              title="{{ $log->response_message }}">
                                            {{ $log->response_message }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <small class="text-muted">
                                            {{ $log->created_at->format('M d, Y H:i') }}
                                        </small>
                                    </td>
                                    <td class="align-middle">
                                        <a href="{{ route('admin.license-verification-logs.show', $log) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="{{ trans('license-logs.tooltip_view_details') }}">
                                            <i class="fas fa-eye me-1"></i> {{ trans('license-logs.view_details') }}
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
                                        <h5 class="text-muted">{{ trans('license-logs.empty_logs_title') }}</h5>
                                        <p class="text-muted">{{ trans('license-logs.empty_logs_description') }}</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($logs->hasPages())
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-center">
                        {{ $logs->appends(request()->query())->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

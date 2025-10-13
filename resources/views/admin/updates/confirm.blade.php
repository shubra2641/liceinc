@extends('layouts.admin')

@section('admin-content')
<div class="admin-page-header">
    <div class="admin-page-header-content">
        <div class="admin-page-title">
            <h1>{{ trans('app.Confirm System Update') }}</h1>
            <p class="admin-page-subtitle">{{ trans('app.Review update details and confirm the system update') }}</p>
        </div>
        <div class="admin-page-actions">
            <a href="{{ route('admin.updates.index') }}" class="admin-btn admin-btn-secondary admin-btn-m">
                <i class="fas fa-arrow-left me-2"></i>
                {{ trans('app.Back to Updates') }}
            </a>
        </div>
    </div>
</div>

<!-- Update Confirmation -->
<div class="row g-4 mb-5">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ trans('app.Update Confirmation') }} - {{ $version }}
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>{{ trans('app.Warning') }}:</strong> {{ trans('app.System update will perform the following actions') }}:
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <h5 class="text-dark mb-3">{{ $versionInfo['title'] ?? 'System Update' }}</h5>

                        @if(isset($versionInfo['description']) && $versionInfo['description'])
                            <p class="text-muted mb-3">{{ $versionInfo['description'] }}</p>
                        @endif

                        <div class="mb-4">
                            <h6 class="text-dark">{{ trans('app.Update Actions') }}:</h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-database text-primary me-2"></i>
                                    {{ trans('app.Run database migrations') }}
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-broom text-info me-2"></i>
                                    {{ trans('app.Clear all caches') }}
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-sync text-success me-2"></i>
                                    {{ trans('app.Optimize application') }}
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-tag text-warning me-2"></i>
                                    {{ trans('app.Update version number') }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">{{ trans('app.Update Details') }}</h6>

                                <div class="mb-2">
                                    <small class="text-muted">{{ trans('app.Current Version') }}:</small>
                                    <div class="fw-bold">{{ $currentVersion }}</div>
                                </div>

                                <div class="mb-2">
                                    <small class="text-muted">{{ trans('app.Target Version') }}:</small>
                                    <div class="fw-bold text-warning">{{ $version }}</div>
                                </div>

                                <div class="mb-2">
                                    <small class="text-muted">{{ trans('app.Type') }}:</small>
                                    <div>
                                        @if(($versionInfo['is_major'] ?? false))
                                            <span class="badge bg-warning">{{ trans('app.Major') }}</span>
                                        @else
                                            <span class="badge bg-info">{{ trans('app.Minor') }}</span>
                                        @endif
                                    </div>
                                </div>

                                @if(isset($versionInfo['file_size']))
                                <div class="mb-3">
                                    <small class="text-muted">{{ trans('app.File Size') }}:</small>
                                    <div class="fw-bold">{{ number_format($versionInfo['file_size'] / 1024 / 1024, 2) }} MB</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Confirmation Form -->
                <div class="mt-4">
                    <form method="POST" action="{{ route('admin.updates.update') }}">
                        @csrf
                        <input type="hidden" name="version" value="{{ $version }}">
                        <input type="hidden" name="confirm" value="true">

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirmUpdate" name="confirmed" value="1" required>
                            <label class="form-check-label" for="confirmUpdate">
                                {{ trans('app.I understand the risks and want to proceed with the update') }}
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.updates.index') }}" class="btn btn-secondary">
                                {{ trans('app.Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-download me-2"></i>
                                {{ trans('app.Proceed with Update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
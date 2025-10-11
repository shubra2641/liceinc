@extends('layouts.admin')

@section('admin-content')
<div class="container-fluid license-show">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-eye text-info me-2"></i>
                                {{ trans('app.View License') }}
                            </h1>
                            <p class="text-muted mb-0">{{ $license->license_key }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.licenses.edit', $license) }}" class="btn btn-primary me-2">
                                <i class="fas fa-edit me-1"></i>
                                {{ trans('app.Edit License') }}
                            </a>
                            <a href="{{ route('admin.licenses.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ trans('app.Back to Licenses') }}
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
            <!-- License Overview -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>
                        {{ trans('app.License Overview') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-key text-primary me-1"></i>
                                {{ trans('app.License Key') }}
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ $license->license_key }}" readonly>
                                <button class="btn btn-outline-secondary copy-btn" type="button" data-text="{{ $license->license_key }}">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-box text-success me-1"></i>
                                {{ trans('app.Product') }}
                            </label>
                            <p class="text-muted">{{ $license->product->name ?? trans('app.No Product') }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-user text-primary me-1"></i>
                                {{ trans('app.Owner') }}
                            </label>
                            <p class="text-muted">
                                @if($license->user)
                                    <a href="{{ route('admin.users.show', $license->user) }}" class="text-decoration-none">
                                        {{ $license->user->name }} ({{ $license->user->email }})
                                    </a>
                                @else
                                    {{ trans('app.No Owner') }}
                                @endif
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-tag text-warning me-1"></i>
                                {{ trans('app.License Type') }}
                            </label>
                            <p class="text-muted">
                                <span class="badge bg-{{ $license->license_type == 'extended' ? 'success' : 'primary' }}">
                                    {{ trans('app.' . ucfirst($license->license_type)) }}
                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-toggle-on text-info me-1"></i>
                                {{ trans('app.Status') }}
                            </label>
                            <p class="text-muted">
                                <span class="badge bg-{{ $license->status == 'active' ? 'success' : ($license->status == 'expired' ? 'danger' : 'warning') }}">
                                    {{ trans('app.' . ucfirst($license->status)) }}
                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-globe text-success me-1"></i>
                                {{ trans('app.Domains') }}
                            </label>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-{{ $license->hasReachedDomainLimit() ? 'warning' : 'success' }} me-2">
                                    {{ $license->active_domains_count }} / {{ $license->max_domains ?? 1 }}
                                </span>
                                @if($license->hasReachedDomainLimit())
                                    <small class="text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ trans('app.Limit Reached') }}
                                    </small>
                                @else
                                    <small class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>{{ $license->remaining_domains }} {{ trans('app.remaining') }}
                                    </small>
                                @endif
                            </div>
                        </div>

                        @if($license->expires_at)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar text-danger me-1"></i>
                                {{ trans('app.Expires At') }}
                            </label>
                            <p class="text-muted">{{ $license->expires_at->format('M d, Y H:i') }}</p>
                        </div>
                        @endif

                        @if($license->support_expires_at)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-headset text-success me-1"></i>
                                {{ trans('app.Support Expires At') }}
                            </label>
                            <p class="text-muted">{{ $license->support_expires_at->format('M d, Y H:i') }}</p>
                        </div>
                        @endif

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar text-info me-1"></i>
                                {{ trans('app.Created At') }}
                            </label>
                            <p class="text-muted">{{ $license->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    @if($license->notes)
                    <div class="mt-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-sticky-note text-warning me-1"></i>
                            {{ trans('app.Notes') }}
                        </label>
                        <div class="bg-light p-3 rounded">
                            <p class="text-muted mb-0">{{ $license->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- License Statistics -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        {{ trans('app.License Statistics') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-primary">{{ $license->active_domains_count }}</h3>
                                <p class="text-muted mb-0">{{ trans('app.Used Domains') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-success">{{ $license->max_domains ?? 1 }}</h3>
                                <p class="text-muted mb-0">{{ trans('app.Max Domains') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-info">{{ $license->remaining_domains }}</h3>
                                <p class="text-muted mb-0">{{ trans('app.Remaining Domains') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <h3 class="text-warning">{{ $license->logs_count ?? 0 }}</h3>
                                <p class="text-muted mb-0">{{ trans('app.Activity Logs') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- License Domains -->
            @if($license->domains && $license->domains->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-globe me-2"></i>
                        {{ trans('app.License Domains') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ trans('app.Domain') }}</th>
                                    <th>{{ trans('app.Status') }}</th>
                                    <th>{{ trans('app.Verified At') }}</th>
                                    <th>{{ trans('app.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($license->domains as $domain)
                                <tr>
                                    <td>{{ $domain->domain }}</td>
                                    <td>
                                        <span class="badge bg-{{ $domain->is_verified ? 'success' : 'warning' }}">
                                            {{ $domain->is_verified ? trans('app.Verified') : trans('app.Pending') }}
                                        </span>
                                    </td>
                                    <td>{{ $domain->verified_at ? $domain->verified_at->format('M d, Y H:i') : '-' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger remove-domain-btn" data-domain-id="{{ $domain->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Recent Activity -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        {{ trans('app.Recent Activity') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ trans('app.License Created') }}</h6>
                                <p class="timeline-text text-muted">{{ $license->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        @if($license->updated_at != $license->created_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ trans('app.Last Updated') }}</h6>
                                <p class="timeline-text text-muted">{{ $license->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        @endif
                        @if($license->domains_count > 0)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ trans('app.Domains Added') }}</h6>
                                <p class="timeline-text text-muted">{{ $license->domains_count }} {{ trans('app.domains') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- License Key -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>
                        {{ trans('app.License Key') }}
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="license-key-display mb-3">
                        <code class="fs-6">{{ $license->license_key }}</code>
                    </div>
                    <button class="btn btn-primary copy-btn" data-text="{{ $license->license_key }}">
                        <i class="fas fa-copy me-1"></i>
                        {{ trans('app.Copy License Key') }}
                    </button>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        {{ trans('app.Quick Actions') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.licenses.edit', $license) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            {{ trans('app.Edit License') }}
                        </a>
                        @if($license->user)
                        <a href="{{ route('admin.users.show', $license->user) }}" class="btn btn-outline-success">
                            <i class="fas fa-user me-1"></i>
                            {{ trans('app.View User') }}
                        </a>
                        @endif
                        @if($license->product)
                        <a href="{{ route('admin.products.show', $license->product) }}" class="btn btn-outline-info">
                            <i class="fas fa-box me-1"></i>
                            {{ trans('app.View Product') }}
                        </a>
                        @endif
                        <button class="btn btn-outline-warning" id="regenerate-license-key-btn">
                            <i class="fas fa-sync me-1"></i>
                            {{ trans('app.Regenerate Key') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- License Details -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ trans('app.License Details') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-info">{{ $license->created_at->format('M Y') }}</h4>
                                <p class="text-muted small mb-0">{{ trans('app.Created') }}</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-warning">{{ $license->updated_at->format('M Y') }}</h4>
                                <p class="text-muted small mb-0">{{ trans('app.Updated') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- License Status -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        {{ trans('app.License Status') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-toggle-on text-success me-1"></i>
                            {{ trans('app.Status') }}
                        </label>
                        <p class="text-muted">
                            <span class="badge bg-{{ $license->status == 'active' ? 'success' : ($license->status == 'expired' ? 'danger' : 'warning') }}">
                                {{ trans('app.' . ucfirst($license->status)) }}
                            </span>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-globe text-info me-1"></i>
                            {{ trans('app.Domain Usage') }}
                        </label>
                        <div class="progress mb-2">
                            <div class="progress-bar" role="progressbar" 
                                 data-width="{{ $license->max_domains > 0 ? ($license->active_domains_count / $license->max_domains) * 100 : 0 }}">
                            </div>
                        </div>
                        <p class="text-muted small mb-0">
                            {{ $license->active_domains_count }} / {{ $license->max_domains ?? 1 }} {{ trans('app.domains used') }}
                            @if($license->remaining_domains > 0)
                                ({{ $license->remaining_domains }} {{ trans('app.remaining') }})
                            @endif
                        </p>
                    </div>
                    @if($license->expires_at)
                    <div class="mb-0">
                        <label class="form-label fw-bold">
                            <i class="fas fa-calendar text-danger me-1"></i>
                            {{ trans('app.Expiration') }}
                        </label>
                        <p class="text-muted small mb-0">
                            {{ $license->expires_at->format('M d, Y') }}
                            @if($license->expires_at->isFuture())
                                ({{ $license->expires_at->diffForHumans() }})
                            @else
                                ({{ trans('app.Expired') }})
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
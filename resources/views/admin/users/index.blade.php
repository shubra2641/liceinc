@extends('layouts.admin')

@section('admin-content')
<div class="admin-page-header">
    <div class="admin-page-header-content">
        <div class="admin-page-title">
            <h1>{{ trans('app.User Management') }}</h1>
            <p class="admin-page-subtitle">{{ trans('app.Manage system users and their permissions') }}</p>
        </div>
        <div class="admin-page-actions">
            <a href="{{ route('admin.users.create') }}" class="admin-btn admin-btn-info admin-btn-m">
                <i class="fas fa-plus me-2"></i>
                {{ trans('app.Add New User') }}
            </a>
        </div>
    </div>
</div>




<!-- Enhanced Filters Section -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <div class="d-flex align-items-center">
            <i class="fas fa-filter me-3 text-primary"></i>
            <div>
                <h5 class="card-title mb-0">{{ trans('app.Filters') }}</h5>
                <small class="text-muted">{{ trans('app.Filter and search users') }}</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="searchUsers" class="form-label">{{ trans('app.Search') }}</label>
                <input type="text" id="searchUsers" class="form-control" 
                       placeholder="{{ trans('app.Search by name, email or role') }}">
            </div>
            <div class="col-md-4">
                <label for="role-filter" class="form-label">{{ trans('app.Role') }}</label>
                <select id="role-filter" class="form-select">
                    <option value="">{{ trans('app.All Roles') }}</option>
                    <option value="admin">{{ trans('app.Admin') }}</option>
                    <option value="user">{{ trans('app.User') }}</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="status-filter" class="form-label">{{ trans('app.Status') }}</label>
                <select id="status-filter" class="form-select">
                    <option value="">{{ trans('app.All Statuses') }}</option>
                    <option value="verified">{{ trans('app.Verified') }}</option>
                    <option value="unverified">{{ trans('app.Unverified') }}</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Statistics Section -->
<div class="dashboard-grid dashboard-grid-4 stats-grid-enhanced">
    <!-- Total Users Stats Card -->
    <div class="stats-card stats-card-primary animate-slide-up">
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
                <div class="stats-card-value">{{ $users->total() }}</div>
                <div class="stats-card-label">{{ trans('app.Total Users') }}</div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span>{{ trans('app.all_registered_users') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Administrators Stats Card -->
    <div class="stats-card stats-card-danger animate-slide-up animate-delay-200">
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
                <div class="stats-card-value">{{ $users->where('is_admin', '1')->count() }}</div>
                <div class="stats-card-label">{{ trans('app.Administrators') }}</div>
                <div class="stats-card-trend negative">
                    <i class="stats-trend-icon negative"></i>
                    <span>{{ number_format(($users->where('is_admin', '1')->count() / max($users->count(), 1)) * 100, 1) }}% {{ trans('app.of_total') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Regular Users Stats Card -->
    <div class="stats-card stats-card-info animate-slide-up animate-delay-300">
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
                <div class="stats-card-value">{{ $users->where('role', '!=', 'admin')->count() }}</div>
                <div class="stats-card-label">{{ trans('app.Regular Users') }}</div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span>{{ number_format(($users->where('role', '!=', 'admin')->count() / max($users->count(), 1)) * 100, 1) }}% {{ trans('app.of_total') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Licenses Stats Card -->
    <div class="stats-card stats-card-success animate-slide-up animate-delay-400">
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
                <div class="stats-card-value">{{ $users->sum('licenses_count') }}</div>
                <div class="stats-card-label">{{ trans('app.Total Licenses') }}</div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span>{{ trans('app.active_licenses') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header bg-light">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-users me-3 text-primary"></i>
                <div>
                    <h5 class="card-title mb-0">{{ trans('app.All Users') }}</h5>
                    <small class="text-muted">{{ trans('app.Manage system users and their permissions') }}</small>
                </div>
            </div>
            <div>
                <span class="badge bg-info fs-6">{{ $users->total() }} {{ trans('app.Users') }}</span>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        @if($users->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">{{ trans('app.Avatar') }}</th>
                        <th>{{ trans('app.User') }}</th>
                        <th>{{ trans('app.Email') }}</th>
                        <th class="text-center">{{ trans('app.Company') }}</th>
                        <th class="text-center">{{ trans('app.Location') }}</th>
                        <th class="text-center">{{ trans('app.Role') }}</th>
                        <th class="text-center">{{ trans('app.Joined') }}</th>
                        <th class="text-center">{{ trans('app.Licenses') }}</th>
                        <th class="text-center">{{ trans('app.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr class="user-row" data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}" data-role="{{ $user->hasRole('admin') ? 'admin' : 'user' }}" data-status="{{ $user->email_verified_at ? 'verified' : 'unverified' }}">
                        <td class="text-center">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center user-avatar">
                                <span class="text-muted small fw-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $user->name }}</div>
                            <small class="text-muted">ID: {{ $user->id }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $user->email }}</div>
                            @if($user->email_verified_at)
                            <small class="text-success">
                                <i class="fas fa-check-circle me-1"></i>{{ trans('app.Verified') }}
                            </small>
                            @else
                            <small class="text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>{{ trans('app.Unverified') }}
                            </small>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($user->companyname)
                                <span class="text-muted">{{ $user->companyname }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($user->city || $user->country)
                                <span class="text-muted">
                                @if($user->city && $user->country)
                                {{ $user->city }}, {{ $user->country }}
                                @elseif($user->city)
                                {{ $user->city }}
                                @elseif($user->country)
                                {{ $user->country }}
                                @endif
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $user->hasRole('admin') ? 'bg-danger' : 'bg-info' }}">
                                @if($user->hasRole('admin'))
                                    <i class="fas fa-user-shield me-1"></i>{{ trans('app.Admin') }}
                                @else
                                    <i class="fas fa-user me-1"></i>{{ trans('app.User') }}
                                @endif
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="fw-semibold text-dark">{{ $user->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success">
                                <i class="fas fa-key me-1"></i>{{ $user->licenses_count ?? 0 }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group-vertical btn-group-sm" role="group">
                                <a href="{{ route('admin.users.show', $user) }}"
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-eye me-1"></i>
                                    {{ trans('app.View') }}
                                </a>

                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit me-1"></i>
                                    {{ trans('app.Edit') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <div class="d-flex justify-content-center">
                {{ $users->links() }}
            </div>
        </div>
        @else
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-users text-muted empty-state-icon"></i>
            </div>
            <h4 class="text-muted">{{ trans('app.No Users Found') }}</h4>
            <p class="text-muted mb-4">{{ trans('app.Create your first user to get started') }}</p>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-plus me-2"></i>
                {{ trans('app.Add New User') }}
            </a>
        </div>
        @endif
    </div>
</div>

<!-- JavaScript is now handled by admin-categories.js -->

@endsection
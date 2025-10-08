@extends('layouts.admin')

@section('admin-content')
<div class="admin-page-header">
    <div class="admin-page-header-content">
        <div class="admin-page-title">
            <h1>{{ trans('app.Email Templates') }}</h1>
            <p class="admin-page-subtitle">{{ trans('app.Manage email templates for the system') }}</p>
        </div>
        <div class="admin-page-actions">
            <a href="{{ route('admin.email-templates.create') }}" class="admin-btn admin-btn-info admin-btn-m">
                <i class="fas fa-plus me-2"></i>
                {{ trans('app.Create New Template') }}
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
                <small class="text-muted">{{ trans('app.Filter and search email templates') }}</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="searchTemplates" class="form-label">{{ trans('app.Search') }}</label>
                <input type="text" id="searchTemplates" class="form-control" 
                       placeholder="{{ trans('app.Search by name or subject') }}">
            </div>
            <div class="col-md-4">
                <label for="type-filter" class="form-label">{{ trans('app.Type') }}</label>
                <select id="type-filter" class="form-select">
                    <option value="">{{ trans('app.All Types') }}</option>
                    <option value="user">{{ trans('app.User') }}</option>
                    <option value="admin">{{ trans('app.Admin') }}</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="category-filter" class="form-label">{{ trans('app.Category') }}</label>
                <select id="category-filter" class="form-select">
                    <option value="">{{ trans('app.All Categories') }}</option>
                    <option value="registration">{{ trans('app.Registration') }}</option>
                    <option value="authentication">{{ trans('app.Authentication') }}</option>
                    <option value="license">{{ trans('app.License') }}</option>
                    <option value="ticket">{{ trans('app.Ticket') }}</option>
                    <option value="invoice">{{ trans('app.Invoice') }}</option>
                    <option value="product">{{ trans('app.Product') }}</option>
                    <option value="other">{{ trans('app.Other') }}</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Statistics Section -->
<div class="dashboard-grid dashboard-grid-4 stats-grid-enhanced">
    <!-- Total Templates Stats Card -->
    <div class="stats-card stats-card-primary animate-slide-up">
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
                <div class="stats-card-value">{{ $templates->total() }}</div>
                <div class="stats-card-label">{{ trans('app.Total Templates') }}</div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span>{{ trans('app.all_email_templates') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Templates Stats Card -->
    <div class="stats-card stats-card-success animate-slide-up animate-delay-200">
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
                <div class="stats-card-value">{{ $templates->where('is_active', true)->count() }}</div>
                <div class="stats-card-label">{{ trans('app.Active Templates') }}</div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span>{{ number_format(($templates->where('is_active', true)->count() / max($templates->count(), 1)) * 100, 1) }}% {{ trans('app.of_total') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- User Templates Stats Card -->
    <div class="stats-card stats-card-info animate-slide-up animate-delay-300">
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
                <div class="stats-card-value">{{ $templates->where('type', 'user')->count() }}</div>
                <div class="stats-card-label">{{ trans('app.User Templates') }}</div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span>{{ number_format(($templates->where('type', 'user')->count() / max($templates->count(), 1)) * 100, 1) }}% {{ trans('app.of_total') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Templates Stats Card -->
    <div class="stats-card stats-card-warning animate-slide-up animate-delay-400">
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
                <div class="stats-card-value">{{ $templates->where('type', 'admin')->count() }}</div>
                <div class="stats-card-label">{{ trans('app.Admin Templates') }}</div>
                <div class="stats-card-trend positive">
                    <i class="stats-trend-icon positive"></i>
                    <span>{{ number_format(($templates->where('type', 'admin')->count() / max($templates->count(), 1)) * 100, 1) }}% {{ trans('app.of_total') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Email Templates Table -->
<div class="card">
    <div class="card-header bg-light">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-envelope me-3 text-primary"></i>
                <div>
                    <h5 class="card-title mb-0">{{ trans('app.All Email Templates') }}</h5>
                    <small class="text-muted">{{ trans('app.Manage and customize email templates') }}</small>
                </div>
            </div>
            <div>
                <span class="badge bg-info fs-6">{{ $templates->total() }} {{ trans('app.Templates') }}</span>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        @if($templates->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0 email-templates-table">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">{{ trans('app.Avatar') }}</th>
                        <th>{{ trans('app.Template') }}</th>
                        <th>{{ trans('app.Subject') }}</th>
                        <th class="text-center">{{ trans('app.Type') }}</th>
                        <th class="text-center">{{ trans('app.Category') }}</th>
                        <th class="text-center">{{ trans('app.Status') }}</th>
                        <th class="text-center">{{ trans('app.Created') }}</th>
                        <th class="text-center">{{ trans('app.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $template)
                    <tr class="template-row" data-name="{{ strtolower($template->name) }}" data-subject="{{ strtolower($template->subject) }}" data-type="{{ $template->type }}" data-category="{{ $template->category }}">
                        <td class="text-center">
                            <div class="bg-light rounded d-flex align-items-center justify-content-center template-avatar">
                                <span class="text-muted small fw-bold">{{ strtoupper(substr($template->name, 0, 1)) }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $template->name }}</div>
                            @if($template->description)
                            <small class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ Str::limit($template->subject, 40) }}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $template->type === 'user' ? 'bg-info' : 'bg-warning' }}">
                                @if($template->type === 'user')
                                    <i class="fas fa-user me-1"></i>{{ trans('app.User') }}
                                @else
                                    <i class="fas fa-user-shield me-1"></i>{{ trans('app.Admin') }}
                                @endif
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">
                                <i class="fas fa-tag me-1"></i>{{ trans('app.' . ucfirst($template->category)) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <form method="POST" action="{{ route('admin.email-templates.toggle', $template) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $template->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                    @if($template->is_active)
                                        <i class="fas fa-toggle-on me-1"></i>{{ trans('app.Active') }}
                                    @else
                                        <i class="fas fa-toggle-off me-1"></i>{{ trans('app.Inactive') }}
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td class="text-center">
                            <div class="fw-semibold text-dark">{{ $template->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $template->created_at->format('g:i A') }}</small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group-vertical btn-group-sm" role="group">
                                <a href="{{ route('admin.email-templates.show', $template) }}"
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-eye me-1"></i>
                                    {{ trans('app.View') }}
                                </a>

                                <a href="{{ route('admin.email-templates.edit', $template) }}"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit me-1"></i>
                                    {{ trans('app.Edit') }}
                                </a>

                                <a href="{{ route('admin.email-templates.test', $template) }}"
                                   class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-paper-plane me-1"></i>
                                    {{ trans('app.Test') }}
                                </a>

                                <form method="POST" action="{{ route('admin.email-templates.destroy', $template) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100 delete-template-btn"
                                            data-confirm="{{ trans('app.Are you sure you want to delete this template?') }}">
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

        @if($templates->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-center">
                {{ $templates->links() }}
            </div>
        </div>
        @endif
        @else
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-envelope text-muted empty-state-icon"></i>
            </div>
            <h4 class="text-muted">{{ trans('app.No Email Templates Found') }}</h4>
            <p class="text-muted mb-4">{{ trans('app.Create your first email template to get started') }}</p>
            <a href="{{ route('admin.email-templates.create') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-plus me-2"></i>
                {{ trans('app.Create Your First Template') }}
            </a>
        </div>
        @endif
    </div>
</div>

<!-- JavaScript is now handled by admin-categories.js -->

@endsection
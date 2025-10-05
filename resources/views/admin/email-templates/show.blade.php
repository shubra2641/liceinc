@extends('layouts.admin')
@section('title', 'Show Email Template')

@section('admin-content')
<div class="container-fluid email-template-show">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-eye text-info me-2"></i>
                                {{ trans('app.View Email Template') }}
                            </h1>
                            <p class="text-muted mb-0">{{ $email_template->name }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.email-templates.test', $email_template) }}"
                                class="btn btn-warning me-2">
                                <i class="fas fa-paper-plane me-1"></i>
                                {{ trans('app.Test Template') }}
                            </a>
                            <a href="{{ route('admin.email-templates.edit', $email_template) }}"
                                class="btn btn-primary me-2">
                                <i class="fas fa-edit me-1"></i>
                                {{ trans('app.Edit Template') }}
                            </a>
                            <a href="{{ route('admin.email-templates.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ trans('app.Back to Templates') }}
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
            <!-- Template Overview -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-envelope me-2"></i>
                        {{ trans('app.Template Overview') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-tag text-primary me-1"></i>
                                {{ trans('app.Template Name') }}
                            </label>
                            <p class="text-muted">{{ $email_template->name }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-list text-success me-1"></i>
                                {{ trans('app.Template Type') }}
                            </label>
                            <p class="text-muted">
                                <span class="badge bg-{{ $email_template->type === 'user' ? 'primary' : 'secondary' }}">
                                    {{ trans('app.' . ucfirst($email_template->type)) }}
                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-folder text-warning me-1"></i>
                                {{ trans('app.Template Category') }}
                            </label>
                            <p class="text-muted">
                                <span class="badge bg-info">
                                    {{ trans('app.' . ucfirst($email_template->category)) }}
                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-toggle-on text-info me-1"></i>
                                {{ trans('app.Status') }}
                            </label>
                            <p class="text-muted">
                                <span class="badge bg-{{ $email_template->is_active ? 'success' : 'danger' }}">
                                    {{ $email_template->is_active ? trans('app.Active') : trans('app.Inactive') }}
                                </span>
                            </p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-plus text-success me-1"></i>
                                {{ trans('app.Created At') }}
                            </label>
                            <p class="text-muted">{{ $email_template->created_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-edit text-warning me-1"></i>
                                {{ trans('app.Updated At') }}
                            </label>
                            <p class="text-muted">{{ $email_template->updated_at->format('M d, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Template Description -->
            @if($email_template->description)
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        {{ trans('app.Template Description') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">{{ $email_template->description }}</p>
                </div>
            </div>
            @endif

            <!-- Email Subject -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-heading me-2"></i>
                        {{ trans('app.Email Subject') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded">
                        <code class="text-dark">{{ $email_template->subject }}</code>
                    </div>
                </div>
            </div>

            <!-- Email Body -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-align-left me-2"></i>
                        {{ trans('app.Email Body') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="email-preview border rounded p-3">
                        {{ $email_template->body }}
                    </div>
                </div>
            </div>

            <!-- Template Variables -->
            @if($email_template->variables && count($email_template->variables) > 0)
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-code me-2"></i>
                        {{ trans('app.Variables Used') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($email_template->variables as $variable)
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center p-2 border rounded">
                                <div class="me-3">
                                    <i class="fas fa-code text-primary"></i>
                                </div>
                                <div>
                                    <code class="text-primary">{{ $variable }}</code>
                                    <div class="text-muted small">
                                        @switch($variable)
                                        @case('app_name')
                                        {{ trans('app.Application name') }}
                                        @break
                                        @case('app_url')
                                        {{ trans('app.Application URL') }}
                                        @break
                                        @case('user_name')
                                        {{ trans('app.User name') }}
                                        @break
                                        @case('user_email')
                                        {{ trans('app.User email') }}
                                        @break
                                        @case('license_code')
                                        {{ trans('app.License code') }}
                                        @break
                                        @case('product_name')
                                        {{ trans('app.Product name') }}
                                        @break
                                        @case('ticket_id')
                                        {{ trans('app.Ticket ID') }}
                                        @break
                                        @case('invoice_id')
                                        {{ trans('app.Invoice ID') }}
                                        @break
                                        @default
                                        {{ ucfirst(str_replace('_', ' ', $variable)) }}
                                        @endswitch
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Template Actions -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools me-2"></i>
                        {{ trans('app.Template Actions') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.email-templates.test', $email_template) }}" class="btn btn-warning">
                            <i class="fas fa-paper-plane me-1"></i>
                            {{ trans('app.Test Template') }}
                        </a>
                        <a href="{{ route('admin.email-templates.edit', $email_template) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            {{ trans('app.Edit Template') }}
                        </a>
                        <form method="POST" action="{{ route('admin.email-templates.toggle', $email_template) }}"
                            class="d-inline">
                            @csrf
                            <button type="submit"
                                class="btn btn-{{ $email_template->is_active ? 'warning' : 'success' }} w-100">
                                <i class="fas fa-toggle-{{ $email_template->is_active ? 'off' : 'on' }} me-1"></i>
                                {{ $email_template->is_active ? trans('app.Deactivate') : trans('app.Activate') }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.email-templates.destroy', $email_template) }}"
                            class="d-inline" data-confirm="delete-template">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-1"></i>
                                {{ trans('app.Delete Template') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Template Statistics -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        {{ trans('app.Template Statistics') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-primary mb-1">0</h4>
                                <small class="text-muted">{{ trans('app.Times Used') }}</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-success mb-1">0</h4>
                                <small class="text-muted">{{ trans('app.Test Emails') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        {{ trans('app.Quick Actions') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary copy-btn"
                            data-text="{{ $email_template->name }}">
                            <i class="fas fa-copy me-1"></i>
                            {{ trans('app.Copy Template Name') }}
                        </button>
                        <button type="button" class="btn btn-outline-info copy-btn"
                            data-text="{{ $email_template->subject }}">
                            <i class="fas fa-copy me-1"></i>
                            {{ trans('app.Copy Subject') }}
                        </button>
                        <a href="{{ route('admin.email-templates.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-1"></i>
                            {{ trans('app.All Templates') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
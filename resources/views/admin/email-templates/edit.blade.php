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
                                <i class="fas fa-edit text-primary me-2"></i>
                                {{ trans('app.Edit Email Template') }}
                            </h1>
                            <p class="text-muted mb-0">{{ $email_template->name }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.email-templates.show', $email_template) }}"
                                class="btn btn-info me-2">
                                <i class="fas fa-eye me-1"></i>
                                {{ trans('app.View Template') }}
                            </a>
                            <a href="{{ route('admin.email-templates.test', $email_template) }}"
                                class="btn btn-warning me-2">
                                <i class="fas fa-paper-plane me-1"></i>
                                {{ trans('app.Test Template') }}
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

    @if($errors->any())
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger border-0 shadow-sm">
                <div class="d-flex">
                    <i class="fas fa-exclamation-triangle text-danger mt-1 me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-2">{{ trans('app.Validation Errors') }}</h5>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.email-templates.update', $email_template) }}" class="needs-validation"
        novalidate>
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            {{ trans('app.Basic Information') }}
                            <span class="badge bg-danger ms-2">{{ trans('app.Required') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-tag me-1"></i>
                                    {{ trans('app.Template Name') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name', $email_template->name) }}" required
                                    placeholder="e.g., user_welcome_template">
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">{{ trans('app.Unique identifier for the template') }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">
                                    <i class="fas fa-list me-1"></i>
                                    {{ trans('app.Template Type') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type"
                                    required>
                                    <option value="">{{ trans('app.Select Type') }}</option>
                                    @foreach($types as $type)
                                    <option value="{{ $type }}" {{ old('type', $email_template->type) === $type ?
                                        'selected' : '' }}>
                                        {{ trans('app.' . ucfirst($type)) }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">
                                    <i class="fas fa-folder me-1"></i>
                                    {{ trans('app.Template Category') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('category') is-invalid @enderror" id="category"
                                    name="category" required>
                                    <option value="">{{ trans('app.Select Category') }}</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ old('category', $email_template->category) ===
                                        $category ? 'selected' : '' }}>
                                        {{ trans('app.' . ucfirst($category)) }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-toggle-on me-1"></i>
                                    {{ trans('app.Template Status') }}
                                </label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_active" value="1"
                                            id="is_active_yes" {{ old('is_active', $email_template->is_active) ?
                                        'checked' : '' }}>
                                        <label class="form-check-label" for="is_active_yes">
                                            {{ trans('app.Active') }}
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_active" value="0"
                                            id="is_active_no" {{ old('is_active', $email_template->is_active) == 0 ?
                                        'checked' : '' }}>
                                        <label class="form-check-label" for="is_active_no">
                                            {{ trans('app.Inactive') }}
                                        </label>
                                    </div>
                                </div>
                                @error('is_active')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Content -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-envelope text-info me-2"></i>
                            {{ trans('app.Email Content') }}
                            <span class="badge bg-danger ms-2">{{ trans('app.Required') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="subject" class="form-label">
                                    <i class="fas fa-heading me-1"></i>
                                    {{ trans('app.Email Subject') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('subject') is-invalid @enderror"
                                    id="subject" name="subject" value="{{ old('subject', $email_template->subject) }}"
                                    required placeholder="e.g., Welcome to @{{app_name}}!">
                                @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">{{ trans('app.Use variables like {{app_name}}, {{user_name}},
                                    etc.') }}</div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="body" class="form-label">
                                    <i class="fas fa-align-left me-1"></i>
                                    {{ trans('app.Email Body') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body"
                                    rows="15" required data-summernote="true" data-toolbar="standard"
                                    data-placeholder="{{ trans('app.Enter your email content here...') }}"
                                    placeholder="{{ trans('app.Enter your email content here...') }}">{{ old('body', $email_template->body) }}</textarea>
                                @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">{{ trans('app.HTML is supported. Use variables like {{app_name}},
                                    {{user_name}}, etc.') }}</div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">
                                    <i class="fas fa-file-alt me-1"></i>
                                    {{ trans('app.Template Description') }}
                                </label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                    id="description" name="description" rows="3"
                                    placeholder="{{ trans('app.Brief description of this template...') }}">{{ old('description', $email_template->description) }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Variables -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-code text-warning me-2"></i>
                            {{ trans('app.Available Variables') }}
                            <span class="badge bg-info ms-2">{{ trans('app.Help') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">{{ trans('app.Click on any variable to copy it to your clipboard') }}
                        </p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="variable-item" data-variable="@{{app_name}}">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="me-3">
                                            <i class="fas fa-building text-primary"></i>
                                        </div>
                                        <div>
                                            <code class="text-primary">@{{app_name}}</code>
                                            <div class="text-muted small">{{ trans('app.Application name') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="variable-item" data-variable="@{{app_url}}">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="me-3">
                                            <i class="fas fa-link text-info"></i>
                                        </div>
                                        <div>
                                            <code class="text-primary">@{{app_url}}</code>
                                            <div class="text-muted small">{{ trans('app.Application URL') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="variable-item" data-variable="@{{user_name}}">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="me-3">
                                            <i class="fas fa-user text-success"></i>
                                        </div>
                                        <div>
                                            <code class="text-primary">@{{user_name}}</code>
                                            <div class="text-muted small">{{ trans('app.User name') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="variable-item" data-variable="@{{user_email}}">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="me-3">
                                            <i class="fas fa-envelope text-warning"></i>
                                        </div>
                                        <div>
                                            <code class="text-primary">@{{user_email}}</code>
                                            <div class="text-muted small">{{ trans('app.User email') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="variable-item" data-variable="@{{license_code}}">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="me-3">
                                            <i class="fas fa-key text-danger"></i>
                                        </div>
                                        <div>
                                            <code class="text-primary">@{{license_code}}</code>
                                            <div class="text-muted small">{{ trans('app.License code') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="variable-item" data-variable="@{{product_name}}">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="me-3">
                                            <i class="fas fa-box text-purple"></i>
                                        </div>
                                        <div>
                                            <code class="text-primary">@{{product_name}}</code>
                                            <div class="text-muted small">{{ trans('app.Product name') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="variable-item" data-variable="@{{ticket_id}}">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="me-3">
                                            <i class="fas fa-ticket-alt text-secondary"></i>
                                        </div>
                                        <div>
                                            <code class="text-primary">@{{ticket_id}}</code>
                                            <div class="text-muted small">{{ trans('app.Ticket ID') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="variable-item" data-variable="@{{invoice_id}}">
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="me-3">
                                            <i class="fas fa-file-invoice text-dark"></i>
                                        </div>
                                        <div>
                                            <code class="text-primary">@{{invoice_id}}</code>
                                            <div class="text-muted small">{{ trans('app.Invoice ID') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Template Preview -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-eye text-info me-2"></i>
                            {{ trans('app.Template Preview') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="preview-container">
                            <div class="preview-header">
                                <div class="preview-subject">
                                    <strong>{{ trans('app.Subject') }}:</strong>
                                    <span id="preview-subject">-</span>
                                </div>
                            </div>
                            <div class="preview-content-wrapper">
                                <div id="preview-content" class="preview-content"></div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="button" id="refresh-preview" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-sync-alt me-1"></i>
                                {{ trans('app.Refresh Preview') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tools me-2"></i>
                            {{ trans('app.Actions') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ trans('app.Update Template') }}
                            </button>
                            <a href="{{ route('admin.email-templates.show', $email_template) }}" class="btn btn-info">
                                <i class="fas fa-eye me-1"></i>
                                {{ trans('app.View Template') }}
                            </a>
                            <a href="{{ route('admin.email-templates.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                {{ trans('app.Cancel') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
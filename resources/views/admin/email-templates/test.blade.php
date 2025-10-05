@extends('layouts.admin')
@section('title', 'Test Email Template')

@section('admin-content')
<div class="container-fluid email-template-test">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-paper-plane text-warning me-2"></i>
                                {{ trans('app.Test Email Template') }}
                            </h1>
                            <p class="text-muted mb-0">{{ $email_template->name }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.email-templates.show', $email_template) }}"
                                class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ trans('app.Back to Template') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Test Data -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-flask me-2"></i>
                        {{ trans('app.Test Data') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ trans('app.Template Type') }}</label>
                        <div class="form-control-plaintext">
                            <span class="badge bg-primary">{{ $email_template->type }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ trans('app.Template Name') }}</label>
                        <div class="form-control-plaintext">{{ $email_template->name }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ trans('app.Template Subject') }}</label>
                        <div class="form-control-plaintext">{{ $email_template->subject }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ trans('app.Template Status') }}</label>
                        <div class="form-control-plaintext">
                            @if($email_template->is_active)
                            <span class="badge bg-success">{{ trans('app.Active') }}</span>
                            @else
                            <span class="badge bg-secondary">{{ trans('app.Inactive') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ trans('app.Last Updated') }}</label>
                        <div class="form-control-plaintext">{{ $email_template->updated_at->format('Y-m-d H:i:s') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Actions -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        {{ trans('app.Test Actions') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.email-templates.send-test', $email_template) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="test_email" class="form-label">{{ trans('app.Test Email Address') }}</label>
                            <input type="email" class="form-control" id="test_email" name="test_email"
                                value="{{ auth()->user()->email }}" required>
                            <div class="form-text">{{ trans('app.Enter email address to send test email') }}</div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-paper-plane me-1"></i>
                            {{ trans('app.Send Test Email') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Rendered Preview -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-eye me-2"></i>
                        {{ trans('app.Rendered Preview') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($rendered['subject']))
                    <div class="mb-4">
                        <label class="form-label fw-bold">{{ trans('app.Subject') }}</label>
                        <div class="alert alert-light border">
                            {{ $rendered['subject'] }}
                        </div>
                    </div>
                    @endif

                    @if(isset($rendered['body']))
                    <div class="mb-4">
                        <label class="form-label fw-bold">{{ trans('app.Body') }}</label>
                        <div class="border rounded p-3 email-template-preview">
                            {{ $rendered['body'] }}
                        </div>
                    </div>
                    @endif

                    @if(isset($rendered['error']))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>{{ trans('app.Rendering Error') }}:</strong>
                        {{ $rendered['error'] }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Template Variables -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-code me-2"></i>
                        {{ trans('app.Template Variables') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">{{ trans('app.Available Variables') }}</h6>
                            <ul class="list-unstyled">
                                <li><code>@{{ $user->name }}</code> - {{ trans('app.User Name') }}</li>
                                <li><code>@{{ $user->email }}</code> - {{ trans('app.User Email') }}</li>
                                <li><code>@{{ $site_name }}</code> - {{ trans('app.Site Name') }}</li>
                                <li><code>@{{ $site_url }}</code> - {{ trans('app.Site URL') }}</li>
                                <li><code>@{{ $current_date }}</code> - {{ trans('app.Current Date') }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">{{ trans('app.Test Values') }}</h6>
                            <ul class="list-unstyled">
                                <li><strong>{{ trans('app.User Name') }}:</strong> {{ $testData['user']['name'] ?? 'Test
                                    User' }}</li>
                                <li><strong>{{ trans('app.User Email') }}:</strong> {{ $testData['user']['email'] ??
                                    'test@example.com' }}</li>
                                <li><strong>{{ trans('app.Site Name') }}:</strong> {{ $testData['site_name'] ??
                                    config('app.name') }}</li>
                                <li><strong>{{ trans('app.Site URL') }}:</strong> {{ $testData['site_url'] ?? url('/')
                                    }}</li>
                                <li><strong>{{ trans('app.Current Date') }}:</strong> {{ $testData['current_date'] ??
                                    now()->format('Y-m-d') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
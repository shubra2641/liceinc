@extends('install.layout', ['step' => 3])

@section('title', trans('install.requirements_title'))

@section('content')
<div class="install-card">
    <div class="install-card-header">
        <div class="install-card-icon">
            <i class="fas fa-clipboard-check"></i>
        </div>
        <h1 class="install-card-title">{{ trans('install.requirements_title') }}</h1>
        <p class="install-card-subtitle">{{ trans('install.requirements_subtitle') }}</p>
    </div>

    <div class="install-card-body">

        <div class="requirements-list">
            @foreach($requirements as $key => $requirement)
                <div class="requirement-item {{ $requirement['passed'] ? 'passed' : 'failed' }}">
                    <div class="requirement-status">
                        <i class="fas {{ $requirement['passed'] ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                    </div>
                    <div class="requirement-details">
                        <div class="requirement-name">{{ $requirement['name'] }}</div>
                        <div class="requirement-info">
                            <span class="requirement-required">{{ trans('install.required') }}: {{ $requirement['required'] }}</span>
                            <span class="requirement-current">{{ trans('install.current') }}: {{ $requirement['current'] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="requirements-summary {{ $allPassed ? 'success' : 'error' }}">
            <div class="summary-icon">
                <i class="fas {{ $allPassed ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
            </div>
            <div class="summary-text">
                @if($allPassed)
                    <h3>{{ trans('install.requirements_all_passed') }}</h3>
                    <p>{{ trans('install.requirements_success_message') }}</p>
                @else
                    <h3>{{ trans('install.requirements_failed') }}</h3>
                    <p>{{ trans('install.requirements_failed_message') }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="install-actions">
        <a href="{{ route('install.welcome') }}" class="install-btn install-btn-secondary">
            <i class="fas fa-arrow-left"></i>
            <span>{{ trans('install.back') }}</span>
        </a>
        
        @if($allPassed)
            <a href="{{ route('install.database') }}" class="install-btn install-btn-primary">
                <i class="fas fa-arrow-right"></i>
                <span>{{ trans('install.continue') }}</span>
            </a>
        @else
            <button class="install-btn install-btn-primary" disabled>
                <i class="fas fa-exclamation-triangle"></i>
                <span>{{ trans('install.fix_requirements') }}</span>
            </button>
        @endif
    </div>
</div>
@endsection

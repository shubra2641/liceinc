@extends('install.layout', ['step' => 5])

@section('title', trans('install.admin_title'))

@section('content')
<div class="install-card">
    <div class="install-card-header">
        <div class="install-card-icon">
            <i class="fas fa-user-shield"></i>
        </div>
        <h1 class="install-card-title">{{ trans('install.admin_title') }}</h1>
        <p class="install-card-subtitle">{{ trans('install.admin_subtitle') }}</p>
    </div>

    <form method="POST" action="{{ route('install.admin.store') }}" class="install-form" id="admin-form">
        @csrf
        
        <div class="install-card-body">
            <div class="form-group">
                <label for="name" class="form-label">
                    <i class="fas fa-user"></i>
                    {{ trans('install.admin_name') }}
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       class="form-input" 
                       value="{{ old('name') }}" 
                       required>
                @error('name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope"></i>
                    {{ trans('install.admin_email') }}
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-input" 
                       value="{{ old('email') }}" 
                       required>
                @error('email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock"></i>
                    {{ trans('install.admin_password') }}
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-input" 
                       required
                       minlength="8">
                @error('password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-hint">{{ trans('install.password_hint') }}</div>
                <noscript>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        {{ trans('install.javascript_required_for_password_validation') }}
                    </div>
                </noscript>
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">
                    <i class="fas fa-lock"></i>
                    {{ trans('install.admin_password_confirmation') }}
                </label>
                <input type="password" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       class="form-input" 
                       required>
            </div>
        </div>

        <div class="install-actions">
            <a href="{{ route('install.database') }}" class="install-btn install-btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>{{ trans('install.back') }}</span>
            </a>
            
            <button type="submit" class="install-btn install-btn-primary">
                <i class="fas fa-arrow-right"></i>
                <span>{{ trans('install.continue') }}</span>
            </button>
        </div>
    </form>
</div>
@endsection


@extends('install.layout', ['step' => 4])

@section('title', trans('install.database_title'))

@section('content')
<div class="install-card">
    <div class="install-card-header">
        <div class="install-card-icon">
            <i class="fas fa-database"></i>
        </div>
        <h1 class="install-card-title">{{ trans('install.database_title') }}</h1>
        <p class="install-card-subtitle">{{ trans('install.database_subtitle') }}</p>
    </div>

    <form method="POST" action="{{ route('install.database.store') }}" class="install-form" id="database-form">
        @csrf
        @method('POST')
        
        <div class="install-card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="form-group">
                <label for="db_host" class="form-label">
                    <i class="fas fa-server"></i>
                    {{ trans('install.database_host') }}
                </label>
                <input type="text" 
                       id="db_host" 
                       name="db_host" 
                       class="form-input" 
                       value="{{ old('db_host', '127.0.0.1') }}" 
                       required>
                @error('db_host')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="db_port" class="form-label">
                    <i class="fas fa-plug"></i>
                    {{ trans('install.database_port') }}
                </label>
                <input type="text" 
                       id="db_port" 
                       name="db_port" 
                       class="form-input" 
                       value="{{ old('db_port', '3306') }}" 
                       required>
                @error('db_port')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="db_name" class="form-label">
                    <i class="fas fa-database"></i>
                    {{ trans('install.database_name') }}
                </label>
                <input type="text" 
                       id="db_name" 
                       name="db_name" 
                       class="form-input" 
                       value="{{ old('db_name') }}" 
                       required>
                @error('db_name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="db_username" class="form-label">
                    <i class="fas fa-user"></i>
                    {{ trans('install.database_username') }}
                </label>
                <input type="text" 
                       id="db_username" 
                       name="db_username" 
                       class="form-input" 
                       value="{{ old('db_username') }}" 
                       required>
                @error('db_username')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="db_password" class="form-label">
                    <i class="fas fa-lock"></i>
                    {{ trans('install.database_password') }}
                </label>
                <input type="password" 
                       id="db_password" 
                       name="db_password" 
                       class="form-input" 
                       value="{{ old('db_password') }}">
                @error('db_password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="test-connection-section">
                <button type="button" 
                        id="test-connection-btn" 
                        class="install-btn install-btn-outline">
                    <i class="fas fa-plug"></i>
                    <span>{{ trans('install.test_connection') }}</span>
                </button>
                <div id="connection-result" class="connection-result"></div>
            </div>
        </div>

        <div class="install-actions">
            <a href="{{ route('install.requirements') }}" class="install-btn install-btn-secondary">
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



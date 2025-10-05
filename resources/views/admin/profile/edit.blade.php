@extends('layouts.admin')

@section('admin-content')
<!-- Admin Profile Edit Page -->
<div class="admin-profile-edit">
<div class="admin-page-header modern-header">
    <div class="admin-page-header-content">
        <div class="admin-page-title">
            <h1 class="gradient-text">{{ trans('app.My Profile') }}</h1>
            <p class="admin-page-subtitle">{{ trans('app.Manage your account information and settings') }}</p>
        </div>
        <div class="admin-page-actions">
            <a href="{{ route('admin.dashboard') }}" class="admin-btn admin-btn-secondary admin-btn-m">
                <i class="fas fa-arrow-left w-4 h-4 mr-2"></i>
                {{ trans('app.Back to Dashboard') }}
            </a>
        </div>
    </div>
</div>

@if($errors->any())
<div class="admin-alert admin-alert-error mb-6">
    <div class="admin-alert-content">
        <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
        <div class="admin-alert-text">
            <h4>{{ trans('app.Validation Errors') }}</h4>
            <ul class="mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endif

@if(session('success'))
<div class="admin-alert admin-alert-success mb-6">
    <div class="admin-alert-content">
        <i class="fas fa-check-circle admin-alert-icon"></i>
        <div class="admin-alert-text">
            <h4>{{ trans('app.Success') }}</h4>
            <p>{{ session('success') }}</p>
        </div>
    </div>
</div>
@endif

@if(session('error'))
<div class="admin-alert admin-alert-error mb-6">
    <div class="admin-alert-content">
        <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
        <div class="admin-alert-text">
            <h4>{{ trans('app.Error') }}</h4>
            <p>{{ session('error') }}</p>
        </div>
    </div>
</div>
@endif

<!-- Main Content Grid -->
<div class="admin-content">
    <div class="row g-4">
        <!-- Main Content Area (2/3 width) -->
        <div class="col-lg-8">
            <!-- Profile Information Section -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-user text-primary me-2"></i>
                        {{ trans('app.Profile Information') }}
                    </h3>
                    <span class="admin-badge admin-badge-primary">{{ trans('app.Required') }}</span>
                </div>
                <div class="admin-card-content">
                    <form method="POST" action="{{ route('admin.profile.update') }}" id="profile-form" class="needs-validation" novalidate>
                        @csrf
                        @method('patch')
                        
                        <!-- Personal Information -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-user me-2"></i>{{ trans('app.Personal Information') }}
                                </h5>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label required" for="name">
                                        <i class="fas fa-user text-blue-500 me-1"></i>{{ trans('app.Full Name') }}
                                    </label>
                                    <input type="text" id="name" name="name" class="admin-form-input"
                                           value="{{ old('name', $user->name) }}" required placeholder="{{ trans('app.Enter full name') }}">
                                    @error('name')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label required" for="email">
                                        <i class="fas fa-envelope text-green-500 me-1"></i>{{ trans('app.Email Address') }}
                                    </label>
                                    <input type="email" id="email" name="email" class="admin-form-input"
                                           value="{{ old('email', $user->email) }}" required placeholder="{{ trans('app.Enter email address') }}">
                                    @error('email')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="firstname">
                                        <i class="fas fa-user text-blue-500 me-1"></i>{{ trans('app.First Name') }}
                                    </label>
                                    <input type="text" id="firstname" name="firstname" class="admin-form-input"
                                           value="{{ old('firstname', $user->firstname) }}" placeholder="{{ trans('app.Enter first name') }}">
                                    @error('firstname')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="lastname">
                                        <i class="fas fa-user text-blue-500 me-1"></i>{{ trans('app.Last Name') }}
                                    </label>
                                    <input type="text" id="lastname" name="lastname" class="admin-form-input"
                                           value="{{ old('lastname', $user->lastname) }}" placeholder="{{ trans('app.Enter last name') }}">
                                    @error('lastname')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="companyname">
                                        <i class="fas fa-building text-purple-500 me-1"></i>{{ trans('app.Company Name') }}
                                    </label>
                                    <input type="text" id="companyname" name="companyname" class="admin-form-input"
                                           value="{{ old('companyname', $user->companyname) }}" placeholder="{{ trans('app.Enter company name') }}">
                                    @error('companyname')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="phonenumber">
                                        <i class="fas fa-phone text-green-500 me-1"></i>{{ trans('app.Phone Number') }}
                                    </label>
                                    <input type="text" id="phonenumber" name="phonenumber" class="admin-form-input"
                                           value="{{ old('phonenumber', $user->phonenumber) }}" placeholder="{{ trans('app.Enter phone number') }}">
                                    @error('phonenumber')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-map-marker-alt me-2"></i>{{ trans('app.Contact Information') }}
                                </h5>
                            </div>
                            
                            <div class="col-12">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="address1">
                                        <i class="fas fa-map-marker-alt text-orange-500 me-1"></i>{{ trans('app.Address Line 1') }}
                                    </label>
                                    <input type="text" id="address1" name="address1" class="admin-form-input"
                                           value="{{ old('address1', $user->address1) }}" placeholder="{{ trans('app.Enter address line 1') }}">
                                    @error('address1')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="address2">
                                        <i class="fas fa-map-marker-alt text-orange-500 me-1"></i>{{ trans('app.Address Line 2') }}
                                    </label>
                                    <input type="text" id="address2" name="address2" class="admin-form-input"
                                           value="{{ old('address2', $user->address2) }}" placeholder="{{ trans('app.Enter address line 2 (optional)') }}">
                                    @error('address2')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="city">
                                        <i class="fas fa-city text-indigo-500 me-1"></i>{{ trans('app.City') }}
                                    </label>
                                    <input type="text" id="city" name="city" class="admin-form-input"
                                           value="{{ old('city', $user->city) }}" placeholder="{{ trans('app.Enter city') }}">
                                    @error('city')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="state">
                                        <i class="fas fa-map text-indigo-500 me-1"></i>{{ trans('app.State/Province') }}
                                    </label>
                                    <input type="text" id="state" name="state" class="admin-form-input"
                                           value="{{ old('state', $user->state) }}" placeholder="{{ trans('app.Enter state or province') }}">
                                    @error('state')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="postcode">
                                        <i class="fas fa-mailbox text-red-500 me-1"></i>{{ trans('app.Postal Code') }}
                                    </label>
                                    <input type="text" id="postcode" name="postcode" class="admin-form-input"
                                           value="{{ old('postcode', $user->postcode) }}" placeholder="{{ trans('app.Enter postal code') }}">
                                    @error('postcode')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="country">
                                        <i class="fas fa-globe text-red-500 me-1"></i>{{ trans('app.Country') }}
                                    </label>
                                    <input type="text" id="country" name="country" class="admin-form-input"
                                           value="{{ old('country', $user->country) }}" placeholder="{{ trans('app.Enter country') }}">
                                    @error('country')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="admin-form-actions">
                            <button type="submit" class="admin-btn admin-btn-primary admin-btn-m">
                                <i class="fas fa-save me-2"></i>{{ trans('app.Save Changes') }}
                            </button>
                            <a href="{{ route('admin.dashboard') }}" class="admin-btn admin-btn-secondary admin-btn-m">
                                <i class="fas fa-times me-2"></i>{{ trans('app.Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Settings Section -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-shield-alt text-red-500 me-2"></i>
                        {{ trans('app.Security Settings') }}
                    </h3>
                    <span class="admin-badge admin-badge-warning">{{ trans('app.Change Password') }}</span>
                </div>
                <div class="admin-card-content">
                    <form method="POST" action="{{ route('admin.profile.update-password') }}" id="password-form" class="needs-validation" novalidate>
                        @csrf
                        @method('patch')
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="current_password">
                                        <i class="fas fa-lock text-orange-500 me-1"></i>{{ trans('app.Current Password') }}
                                    </label>
                                    <input type="password" id="current_password" name="current_password" class="admin-form-input"
                                           placeholder="{{ trans('app.Enter current password') }}" required>
                                    @error('current_password')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="password">
                                        <i class="fas fa-lock text-green-500 me-1"></i>{{ trans('app.New Password') }}
                                    </label>
                                    <input type="password" id="password" name="password" class="admin-form-input"
                                           placeholder="{{ trans('app.Enter new password') }}" required>
                                    @error('password')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="password_confirmation">
                                        <i class="fas fa-lock text-green-500 me-1"></i>{{ trans('app.Confirm New Password') }}
                                    </label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="admin-form-input"
                                           placeholder="{{ trans('app.Confirm new password') }}" required>
                                    @error('password_confirmation')
                                        <div class="admin-form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="admin-form-actions">
                            <button type="submit" class="admin-btn admin-btn-primary admin-btn-m">
                                <i class="fas fa-save me-2"></i>{{ trans('app.Update Password') }}
                            </button>
                            <a href="{{ route('admin.dashboard') }}" class="admin-btn admin-btn-secondary admin-btn-m">
                                <i class="fas fa-times me-2"></i>{{ trans('app.Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar (1/3 width) -->
        <div class="col-lg-4">
            <!-- Envato Integration Card -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-bolt text-orange-500 me-2"></i>
                        {{ trans('app.Envato Integration') }}
                    </h3>
                </div>
                <div class="admin-card-content">

                    @if($user->envato_username)
                        <div class="admin-alert admin-alert-success">
                            <div class="admin-alert-content">
                                <i class="fas fa-check-circle admin-alert-icon"></i>
                                <div class="admin-alert-text">
                                    <h4>{{ trans('app.Connected to Envato') }}</h4>
                                    <p><strong>{{ trans('app.Username') }}:</strong> {{ $user->envato_username }}</p>
                                    @if($user->envato_id)
                                        <p><strong>{{ trans('app.Envato ID') }}:</strong> {{ $user->envato_id }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.profile.disconnect-envato') }}" class="mt-3">
                            @csrf
                            <button type="submit" class="admin-btn admin-btn-danger admin-btn-s w-100" 
                                    data-confirm="{{ trans('app.Are you sure you want to disconnect from Envato?') }}">
                                <i class="fas fa-unlink me-1"></i>
                                {{ trans('app.Disconnect Envato Account') }}
                            </button>
                        </form>
                    @elseif($hasApiConfig)
                        <div class="admin-alert admin-alert-info">
                            <div class="admin-alert-content">
                                <i class="fas fa-info-circle admin-alert-icon"></i>
                                <div class="admin-alert-text">
                                    <h4>{{ trans('app.Envato API Configured') }}</h4>
                                    <p>{{ trans('app.Envato API is configured and ready to connect your account.') }}</p>
                                </div>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.profile.connect-envato') }}" class="mt-3">
                            @csrf
                            <button type="submit" class="admin-btn admin-btn-primary admin-btn-s w-100">
                                <i class="fas fa-link me-1"></i>
                                {{ trans('app.Connect Envato Account') }}
                            </button>
                        </form>
                    @else
                        <div class="admin-alert admin-alert-warning">
                            <div class="admin-alert-content">
                                <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
                                <div class="admin-alert-text">
                                    <h4>{{ trans('app.Envato API Not Configured') }}</h4>
                                    <p>{{ trans('app.Please configure Envato API settings first to connect your account.') }}</p>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('admin.settings.index') }}" class="admin-btn admin-btn-secondary admin-btn-s w-100">
                            <i class="fas fa-cog me-1"></i>
                            {{ trans('app.Configure Envato API') }}
                        </a>
                    @endif
                </div>
            </div>

            <!-- Account Statistics Card -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-chart-bar text-blue-500 me-2"></i>
                        {{ trans('app.Account Statistics') }}
                    </h3>
                </div>
                <div class="admin-card-content">
                    <div class="admin-stats-grid">
                        <div class="admin-stat-item">
                            <div class="admin-stat-icon">
                                <i class="fas fa-key text-blue-500"></i>
                            </div>
                            <div class="admin-stat-content">
                                <div class="admin-stat-value">{{ $user->licenses()->count() }}</div>
                                <div class="admin-stat-label">{{ trans('app.Total Licenses') }}</div>
                            </div>
                        </div>

                        <div class="admin-stat-item">
                            <div class="admin-stat-icon">
                                <i class="fas fa-check-circle text-green-500"></i>
                            </div>
                            <div class="admin-stat-content">
                                <div class="admin-stat-value">{{ $user->licenses()->where('status', 'active')->count() }}</div>
                                <div class="admin-stat-label">{{ trans('app.Active Licenses') }}</div>
                            </div>
                        </div>

                        <div class="admin-stat-item">
                            <div class="admin-stat-icon">
                                <i class="fas fa-ticket-alt text-orange-500"></i>
                            </div>
                            <div class="admin-stat-content">
                                <div class="admin-stat-value">{{ $user->tickets()->count() }}</div>
                                <div class="admin-stat-label">{{ trans('app.Support Tickets') }}</div>
                            </div>
                        </div>

                        <div class="admin-stat-item">
                            <div class="admin-stat-icon">
                                <i class="fas fa-calendar text-purple-500"></i>
                            </div>
                            <div class="admin-stat-content">
                                <div class="admin-stat-value">{{ $user->created_at->format('M d, Y') }}</div>
                                <div class="admin-stat-label">{{ trans('app.Member Since') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        {{ trans('app.Quick Actions') }}
                    </h3>
                </div>
                <div class="admin-card-content">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.dashboard') }}" class="admin-btn admin-btn-primary">
                            <i class="fas fa-tachometer-alt me-1"></i>
                            {{ trans('app.Dashboard') }}
                        </a>
                        <a href="{{ route('admin.settings.index') }}" class="admin-btn admin-btn-secondary">
                            <i class="fas fa-cog me-1"></i>
                            {{ trans('app.Settings') }}
                        </a>
                        <a href="{{ route('logout') }}" class="admin-btn admin-btn-danger logout-btn">
                            <i class="fas fa-sign-out-alt me-1"></i>
                            {{ trans('app.Logout') }}
                        </a>
                    </div>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
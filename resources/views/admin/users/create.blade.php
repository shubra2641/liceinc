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
                                <i class="fas fa-plus-circle text-primary me-2"></i>
                                {{ trans('app.Create User') }}
                            </h1>
                            <p class="text-muted mb-0">{{ trans('app.Create a new user account') }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ trans('app.Back to Users') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <form method="POST" action="{{ route('admin.users.store') }}" class="needs-validation" novalidate>
        @csrf

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- User Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user me-2"></i>
                            {{ trans('app.User Information') }}
                            <span class="badge bg-light text-primary ms-2">{{ trans('app.Required') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    {{ trans('app.Full Name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="{{ trans('app.Enter full name') }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope text-success me-1"></i>
                                    {{ trans('app.Email Address') }} <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" 
                                       placeholder="{{ trans('app.Enter email address') }}" required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock text-warning me-1"></i>
                                    {{ trans('app.Password') }} <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" 
                                       placeholder="{{ trans('app.Enter password') }}" required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Password must be at least 8 characters') }}
                                </div>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">
                                    <i class="fas fa-lock text-warning me-1"></i>
                                    {{ trans('app.Confirm Password') }} <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                       id="password_confirmation" name="password_confirmation" 
                                       placeholder="{{ trans('app.Confirm Password') }}" required>
                                @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-user-shield text-purple me-1"></i>
                                {{ trans('app.User Role') }} <span class="text-danger">*</span>
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded">
                                        <input class="form-check-input" type="radio" name="role" id="role_user" value="user" 
                                               {{ old('role', 'user') == 'user' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="role_user">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user text-primary me-2"></i>
                                                <div>
                                                    <strong>{{ trans('app.Regular User') }}</strong>
                                                    <p class="text-muted small mb-0">{{ trans('app.Can access basic features') }}</p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded">
                                        <input class="form-check-input" type="radio" name="role" id="role_admin" value="admin" 
                                               {{ old('role') == 'admin' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="role_admin">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-shield text-danger me-2"></i>
                                                <div>
                                                    <strong>{{ trans('app.Administrator') }}</strong>
                                                    <p class="text-muted small mb-0">{{ trans('app.Full system access') }}</p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('role')
                            <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="send_welcome_email" name="send_welcome_email" value="1"
                                   {{ old('send_welcome_email', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="send_welcome_email">
                                <i class="fas fa-envelope text-info me-1"></i>
                                {{ trans('app.Send Welcome Email') }}
                            </label>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.Send welcome email to the new user') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Client Information -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-address-card me-2"></i>
                            {{ trans('app.Client Information') }}
                            <span class="badge bg-light text-success ms-2">{{ trans('app.Optional') }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstname" class="form-label">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    {{ trans('app.First Name') }}
                                </label>
                                <input type="text" class="form-control @error('firstname') is-invalid @enderror" 
                                       id="firstname" name="firstname" value="{{ old('firstname') }}" 
                                       placeholder="{{ trans('app.Enter first name') }}">
                                @error('firstname')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="lastname" class="form-label">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    {{ trans('app.Last Name') }}
                                </label>
                                <input type="text" class="form-control @error('lastname') is-invalid @enderror" 
                                       id="lastname" name="lastname" value="{{ old('lastname') }}" 
                                       placeholder="{{ trans('app.Enter last name') }}">
                                @error('lastname')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="companyname" class="form-label">
                                    <i class="fas fa-building text-purple me-1"></i>
                                    {{ trans('app.Company Name') }}
                                </label>
                                <input type="text" class="form-control @error('companyname') is-invalid @enderror" 
                                       id="companyname" name="companyname" value="{{ old('companyname') }}" 
                                       placeholder="{{ trans('app.Enter company name') }}">
                                @error('companyname')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phonenumber" class="form-label">
                                    <i class="fas fa-phone text-success me-1"></i>
                                    {{ trans('app.Phone Number') }}
                                </label>
                                <input type="text" class="form-control @error('phonenumber') is-invalid @enderror" 
                                       id="phonenumber" name="phonenumber" value="{{ old('phonenumber') }}" 
                                       placeholder="{{ trans('app.Enter phone number') }}">
                                @error('phonenumber')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="address1" class="form-label">
                                    <i class="fas fa-map-marker-alt text-warning me-1"></i>
                                    {{ trans('app.Address Line 1') }}
                                </label>
                                <input type="text" class="form-control @error('address1') is-invalid @enderror" 
                                       id="address1" name="address1" value="{{ old('address1') }}" 
                                       placeholder="{{ trans('app.Enter address line 1') }}">
                                @error('address1')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="address2" class="form-label">
                                    <i class="fas fa-map-marker-alt text-warning me-1"></i>
                                    {{ trans('app.Address Line 2') }}
                                </label>
                                <input type="text" class="form-control @error('address2') is-invalid @enderror" 
                                       id="address2" name="address2" value="{{ old('address2') }}" 
                                       placeholder="{{ trans('app.Enter address line 2 (optional)') }}">
                                @error('address2')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">
                                    <i class="fas fa-city text-info me-1"></i>
                                    {{ trans('app.City') }}
                                </label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                       id="city" name="city" value="{{ old('city') }}" 
                                       placeholder="{{ trans('app.Enter city') }}">
                                @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">
                                    <i class="fas fa-map text-info me-1"></i>
                                    {{ trans('app.State/Province') }}
                                </label>
                                <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                       id="state" name="state" value="{{ old('state') }}" 
                                       placeholder="{{ trans('app.Enter state or province') }}">
                                @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="postcode" class="form-label">
                                    <i class="fas fa-mailbox text-danger me-1"></i>
                                    {{ trans('app.Postal Code') }}
                                </label>
                                <input type="text" class="form-control @error('postcode') is-invalid @enderror" 
                                       id="postcode" name="postcode" value="{{ old('postcode') }}" 
                                       placeholder="{{ trans('app.Enter postal code') }}">
                                @error('postcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="country" class="form-label">
                                    <i class="fas fa-globe text-danger me-1"></i>
                                    {{ trans('app.Country') }}
                                </label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                       id="country" name="country" value="{{ old('country') }}" 
                                       placeholder="{{ trans('app.Enter country') }}">
                                @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- User Preview -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-eye me-2"></i>
                            {{ trans('app.User Preview') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div id="user-preview" class="p-3 rounded border">
                                <i class="fas fa-user-circle fs-1 text-primary mb-2"></i>
                                <h5 id="preview-name">{{ trans('app.User Name') }}</h5>
                                <p id="preview-email" class="text-muted small mb-0">{{ trans('app.user@example.com') }}</p>
                                <span id="preview-role" class="badge bg-secondary mt-2">{{ trans('app.User') }}</span>
                            </div>
                            <p class="text-muted small mt-2">{{ trans('app.Live Preview') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            {{ trans('app.Quick Stats') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-primary">0</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Licenses') }}</p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-success">0</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Invoices') }}</p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-info">0</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Tickets') }}</p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-warning">0</h4>
                                    <p class="text-muted small mb-0">{{ trans('app.Orders') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog me-2"></i>
                            {{ trans('app.User Settings') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <i class="fas fa-toggle-on text-success me-1"></i>
                                {{ trans('app.Active User') }}
                            </label>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.User can login and access the system') }}
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="email_verified" name="email_verified" value="1"
                                   {{ old('email_verified', false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_verified">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                {{ trans('app.Email Verified') }}
                            </label>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ trans('app.User email is verified') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Tips -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-shield-alt me-2"></i>
                            {{ trans('app.Security Tips') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ trans('app.Use strong passwords') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ trans('app.Verify email addresses') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ trans('app.Assign appropriate roles') }}
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ trans('app.Review user permissions') }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>{{ trans('app.Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ trans('app.Create User') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
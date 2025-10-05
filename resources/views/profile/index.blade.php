@extends('layouts.user')

@section('title', trans('app.My Profile'))
@section('page-title', trans('app.My Profile'))
@section('page-subtitle', trans('app.Manage your account information and settings'))

@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-user-cog"></i>
                {{ trans('app.My Profile') }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.Manage your account information and settings') }}
            </p>
        </div>

        <div class="user-card-content">
                    @if(session('status'))
                        <div class="user-alert user-alert-success mb-4">
                            <div class="user-alert-content">
                                <i class="fas fa-check-circle user-alert-icon"></i>
                                <div class="user-alert-text">
                                    @if(session('status') == 'profile-updated')
                                        <h4>{{ trans('app.Profile Updated') }}</h4>
                                        <p>{{ trans('app.Your profile has been updated successfully.') }}</p>
                                    @elseif(session('status') == 'envato-unlinked')
                                        <h4>{{ trans('app.Envato Account Unlinked') }}</h4>
                                        <p>{{ trans('app.Your Envato account has been unlinked successfully.') }}</p>
                                    @else
                                        <h4>{{ trans('app.Success') }}</h4>
                                        <p>{{ session('status') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="user-alert user-alert-error mb-4">
                            <div class="user-alert-content">
                                <i class="fas fa-exclamation-triangle user-alert-icon"></i>
                                <div class="user-alert-text">
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

                    <!-- Profile Overview -->
                    <div class="profile-overview">
                        <div class="profile-avatar">
                            <div class="avatar-circle">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="profile-info">
                                <h3>{{ auth()->user()->name }}</h3>
                                <p>{{ auth()->user()->email }}</p>
                                <span class="member-since">{{ trans('app.Member since') }} {{ auth()->user()->created_at->format('M Y') }}</span>
                            </div>
                        </div>
                        
                        <div class="profile-stats">
                            <div class="stat-item">
                                <div class="stat-value">{{ auth()->user()->licenses()->count() }}</div>
                                <div class="stat-label">{{ trans('app.Licenses') }}</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ auth()->user()->tickets()->count() }}</div>
                                <div class="stat-label">{{ trans('app.Tickets') }}</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ auth()->user()->invoices()->count() }}</div>
                                <div class="stat-label">{{ trans('app.Invoices') }}</div>
                            </div>
                        </div>
                    </div>

            <!-- Main Content Grid -->
            <div class="user-profile-main-grid">
                <!-- Forms Section -->
                <div class="user-profile-forms-section">
                    <!-- Personal Information -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-user"></i>
                                {{ trans('app.Personal Information') }}
                            </div>
                            <p class="user-card-subtitle">{{ trans('app.Update your personal details') }}</p>
                        </div>
                        <div class="user-card-content">
                            <form action="{{ route('profile.update') }}" method="POST" class="user-profile-form">
                                @csrf
                                @method('PATCH')

                                <div class="user-form-grid">
                                    <div class="user-form-group">
                                        <label for="name" class="user-form-label">{{ trans('app.Full Name') }} *</label>
                                        <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" class="form-input form-input-error" required>
                                        @error('name')
                                            <p class="user-form-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="user-form-group">
                                        <label for="email" class="user-form-label">{{ trans('app.Email Address') }} *</label>
                                        <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" class="form-input form-input-error" required>
                                        @error('email')
                                            <p class="user-form-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="user-form-group">
                                        <label for="firstname" class="user-form-label">{{ trans('app.First Name') }}</label>
                                        <input type="text" id="firstname" name="firstname" value="{{ old('firstname', auth()->user()->firstname) }}" class="form-input form-input-error">
                                        @error('firstname')
                                            <p class="user-form-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="user-form-group">
                                        <label for="lastname" class="user-form-label">{{ trans('app.Last Name') }}</label>
                                        <input type="text" id="lastname" name="lastname" value="{{ old('lastname', auth()->user()->lastname) }}" class="form-input form-input-error">
                                        @error('lastname')
                                            <p class="user-form-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="user-form-group">
                                        <label for="companyname" class="user-form-label">{{ trans('app.Company Name') }}</label>
                                        <input type="text" id="companyname" name="companyname" value="{{ old('companyname', auth()->user()->companyname) }}" class="form-input form-input-error">
                                        @error('companyname')
                                            <p class="user-form-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="user-form-group">
                                        <label for="phonenumber" class="user-form-label">{{ trans('app.Phone Number') }}</label>
                                        <input type="text" id="phonenumber" name="phonenumber" value="{{ old('phonenumber', auth()->user()->phonenumber) }}" class="form-input form-input-error">
                                        @error('phonenumber')
                                            <p class="user-form-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="user-form-group">
                                        <label for="envato_username" class="user-form-label">{{ trans('app.Envato Username') }}</label>
                                        <input type="text" id="envato_username" name="envato_username" value="{{ old('envato_username', auth()->user()->envato_username) }}" class="form-input form-input-error" placeholder="Enter your Envato username">
                                        @error('envato_username')
                                            <p class="user-form-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="user-form-group">
                                        <label for="envato_id" class="user-form-label">{{ trans('app.Envato ID') }}</label>
                                        <input type="text" id="envato_id" name="envato_id" value="{{ old('envato_id', auth()->user()->envato_id) }}" class="form-input form-input-error" placeholder="Enter your Envato ID" readonly>
                                        <p class="user-form-help">{{ trans('app.This field is automatically set when you connect your Envato account') }}</p>
                                        @error('envato_id')
                                            <p class="user-form-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="user-form-actions">
                                    <button type="button" class="user-action-button user-action-button-outline">{{ trans('app.Cancel') }}</button>
                                    <button type="submit" class="user-action-button user-action-button-primary">
                                        <i class="fas fa-save"></i>
                                        {{ trans('app.Save Changes') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ trans('app.Contact Information') }}
                            </div>
                            <p class="user-card-subtitle">{{ trans('app.Update your contact details') }}</p>
                        </div>
                        <div class="user-card-content">
                            <form action="{{ route('profile.update') }}" method="POST" class="user-profile-form">
                                @csrf
                                @method('PATCH')

                                <div class="user-form-grid">
                                    <div class="user-form-group">
                                        <label for="address1" class="user-form-label">{{ trans('app.Address Line 1') }}</label>
                                        <input type="text" id="address1" name="address1" value="{{ old('address1', auth()->user()->address1) }}" class="form-input form-input-error">
                                        @error('address1')
                                            <p class="user-form-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="user-form-group">
                                        <label for="address2" class="user-form-label">{{ trans('app.Address Line 2') }}</label>
                                        <input type="text" id="address2" name="address2" value="{{ old('address2', auth()->user()->address2) }}" class="form-input form-input-error">
                                        @error('address2')
                                            <p class="user-form-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="user-form-group">
                                        <label for="city" class="user-form-label">{{ trans('app.City') }}</label>
                                        <input type="text" id="city" name="city" value="{{ old('city', auth()->user()->city) }}" class="form-input form-input-error">
                                        @error('city')
                                            <p class="user-form-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="user-form-group">
                                        <label for="state" class="user-form-label">{{ trans('app.State/Province') }}</label>
                                        <input type="text" id="state" name="state" value="{{ old('state', auth()->user()->state) }}" class="form-input form-input-error">
                                        @error('state')
                                            <p class="user-form-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="user-form-group">
                                        <label for="postcode" class="user-form-label">{{ trans('app.Postal Code') }}</label>
                                        <input type="text" id="postcode" name="postcode" value="{{ old('postcode', auth()->user()->postcode) }}" class="form-input form-input-error">
                                        @error('postcode')
                                            <p class="user-form-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="user-form-group">
                                        <label for="country" class="user-form-label">{{ trans('app.Country') }}</label>
                                        <input type="text" id="country" name="country" value="{{ old('country', auth()->user()->country) }}" class="form-input form-input-error">
                                        @error('country')
                                            <p class="user-form-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="user-form-actions">
                                    <button type="button" class="user-action-button user-action-button-outline">{{ trans('app.Cancel') }}</button>
                                    <button type="submit" class="user-action-button user-action-button-primary">
                                        <i class="fas fa-save"></i>
                                        {{ trans('app.Save Changes') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="user-profile-sidebar">
                    <!-- Envato Integration -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-link"></i>
                                {{ trans('app.Envato Integration') }}
                            </div>
                            <p class="user-card-subtitle">{{ trans('app.Manage your Envato account connection') }}</p>
                        </div>
                        <div class="user-card-content">
                            @if(auth()->user()->hasEnvatoAccount())
                                <div class="user-envato-connected">
                                    <div class="user-envato-status">
                                        <i class="fas fa-check-circle text-green-500"></i>
                                        <span>{{ trans('app.Connected to Envato') }}</span>
                                    </div>
                                    <p class="user-envato-username">{{ auth()->user()->envato_username }}</p>
                                    <button class="user-action-button user-action-button-outline" data-action="unlink-envato">
                                        <i class="fas fa-unlink"></i>
                                        {{ trans('app.Unlink Envato Account') }}
                                    </button>
                                    <noscript>
                                        <form method="POST" action="{{ route('profile.unlink-envato') }}" class="mt-2">
                                            @csrf
                                            <button type="submit" class="user-action-button user-action-button-outline" data-confirm="{{ trans('app.are_you_sure_unlink_envato') }}">
                                                <i class="fas fa-unlink"></i>
                                                {{ trans('app.Unlink Envato Account') }}
                                            </button>
                                        </form>
                                    </noscript>
                                </div>
                            @else
                                <div class="user-envato-connect">
                                    <div class="user-envato-icon">
                                        <i class="fas fa-link"></i>
                                    </div>
                                    <h3>{{ trans('app.Connect Your Envato Account') }}</h3>
                                    <p>{{ trans('app.Link Your Envato Account To Verify Purchases And Access Exclusive Features.') }}</p>
                                    <a href="{{ route('envato.link') }}" class="user-action-button user-action-button-primary">
                                        <i class="fas fa-link"></i>
                                        {{ trans('app.Connect Envato Account') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Account Statistics -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-chart-bar"></i>
                                {{ trans('app.Account Statistics') }}
                            </div>
                            <p class="user-card-subtitle">{{ trans('app.Your account overview') }}</p>
                        </div>
                        <div class="user-card-content">
                            <div class="user-stats-list">
                                <div class="user-stat-item">
                                    <div class="user-stat-icon blue">
                                        <i class="fas fa-key"></i>
                                    </div>
                                    <div class="user-stat-info">
                                        <div class="user-stat-number">{{ auth()->user()->licenses->count() }}</div>
                                        <div class="user-stat-label">{{ trans('app.Total Licenses') }}</div>
                                    </div>
                                </div>
                                <div class="user-stat-item">
                                    <div class="user-stat-icon green">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="user-stat-info">
                                        <div class="user-stat-number">{{ auth()->user()->licenses->where('status', 'active')->count() }}</div>
                                        <div class="user-stat-label">{{ trans('app.Active Licenses') }}</div>
                                    </div>
                                </div>
                                <div class="user-stat-item">
                                    <div class="user-stat-icon yellow">
                                        <i class="fas fa-ticket-alt"></i>
                                    </div>
                                    <div class="user-stat-info">
                                        <div class="user-stat-number">{{ auth()->user()->tickets->count() }}</div>
                                        <div class="user-stat-label">{{ trans('app.Support Tickets') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
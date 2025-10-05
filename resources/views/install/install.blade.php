@extends('install.layout', ['step' => 7])

@section('title', trans('install.install_title'))

@section('content')
<div class="install-card">
    <div class="install-card-header">
        <div class="install-card-icon">
            <i class="fas fa-cog"></i>
        </div>
        <h1 class="install-card-title">{{ trans('install.install_title') }}</h1>
        <p class="install-card-subtitle">{{ trans('install.install_subtitle') }}</p>
    </div>

    <div class="install-card-body">
        <div class="install-description">
            <p>{{ trans('install.install_description') }}</p>
        </div>

        <!-- Installation Progress Steps -->
        <div class="installation-steps">
            <div class="installation-step pending" id="step-env">
                <div class="step-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="step-content">
                    <div class="step-title">{{ trans('install.updating_configuration') }}</div>
                    <div class="step-description">{{ trans('install.updating_configuration_desc') }}</div>
                </div>
                <div class="step-status">
                    <i class="fas fa-clock"></i>
                </div>
            </div>

            <div class="installation-step pending" id="step-migrate">
                <div class="step-icon">
                    <i class="fas fa-database"></i>
                </div>
                <div class="step-content">
                    <div class="step-title">{{ trans('install.creating_database_tables') }}</div>
                    <div class="step-description">{{ trans('install.creating_database_tables_desc') }}</div>
                </div>
                <div class="step-status">
                    <i class="fas fa-clock"></i>
                </div>
            </div>

            <div class="installation-step pending" id="step-seed">
                <div class="step-icon">
                    <i class="fas fa-seedling"></i>
                </div>
                <div class="step-content">
                    <div class="step-title">{{ trans('install.seeding_database') }}</div>
                    <div class="step-description">{{ trans('install.seeding_database_desc') }}</div>
                </div>
                <div class="step-status">
                    <i class="fas fa-clock"></i>
                </div>
            </div>

            <div class="installation-step pending" id="step-roles">
                <div class="step-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="step-content">
                    <div class="step-title">{{ trans('install.setting_up_roles_permissions') }}</div>
                    <div class="step-description">{{ trans('install.setting_up_roles_permissions_desc') }}</div>
                </div>
                <div class="step-status">
                    <i class="fas fa-clock"></i>
                </div>
            </div>

            <div class="installation-step pending" id="step-admin">
                <div class="step-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="step-content">
                    <div class="step-title">{{ trans('install.creating_admin_account') }}</div>
                    <div class="step-description">{{ trans('install.creating_admin_account_desc') }}</div>
                </div>
                <div class="step-status">
                    <i class="fas fa-clock"></i>
                </div>
            </div>

            <div class="installation-step pending" id="step-settings">
                <div class="step-icon">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <div class="step-content">
                    <div class="step-title">{{ trans('install.configuring_system_settings') }}</div>
                    <div class="step-description">{{ trans('install.configuring_system_settings_desc') }}</div>
                </div>
                <div class="step-status">
                    <i class="fas fa-clock"></i>
                </div>
            </div>

            <div class="installation-step pending" id="step-storage">
                <div class="step-icon">
                    <i class="fas fa-link"></i>
                </div>
                <div class="step-content">
                    <div class="step-title">{{ trans('install.creating_storage_link') }}</div>
                    <div class="step-description">{{ trans('install.creating_storage_link_desc') }}</div>
                </div>
                <div class="step-status">
                    <i class="fas fa-clock"></i>
                </div>
            </div>

            <div class="installation-step pending" id="step-complete">
                <div class="step-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="step-content">
                    <div class="step-title">{{ trans('install.finalizing_installation') }}</div>
                    <div class="step-description">{{ trans('install.finalizing_installation_desc') }}</div>
                </div>
                <div class="step-status">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <form class="install-form" id="installation-form">
        @csrf
        <div class="install-actions">
            <a href="{{ route('install.settings') }}" class="install-btn install-btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>{{ trans('install.back') }}</span>
            </a>
            
            <button type="submit" id="start-installation-btn" class="install-btn install-btn-primary">
                <i class="fas fa-play"></i>
                <span>{{ trans('install.start_installation') }}</span>
            </button>
        </div>
    </form>
</div>
@endsection

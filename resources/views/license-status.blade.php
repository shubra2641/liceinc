@extends('layouts.user')

@section('title', trans('app.page_title'))
@section('page-title', trans('app.page_title'))
@section('page-subtitle', trans('app.page_subtitle'))

@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-shield-alt"></i>
                {{ trans('app.check_title') }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.check_description') }}
            </p>
        </div>

        <div class="user-card-content">
            <!-- Stats Cards -->
            <div class="user-stats-grid">
                <!-- License Check -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.license_check') }}</div>
                        <div class="user-stat-icon blue">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                    </div>
                    <div class="user-stat-value" id="licenseStatusValue">-</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.current_status') }}</p>
                </div>

                <!-- License Type -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.license_type') }}</div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-key"></i>
                        </div>
                    </div>
                    <div class="user-stat-value" id="licenseTypeValue">-</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.license_category') }}</p>
                </div>

                <!-- Days Remaining -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.days_remaining') }}</div>
                        <div class="user-stat-icon yellow">
                            <i class="fas fa-calendar"></i>
                        </div>
                    </div>
                    <div class="user-stat-value" id="daysRemainingValue">-</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.expiration_info') }}</p>
                </div>

                <!-- Domains Used -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.domains_used') }}</div>
                        <div class="user-stat-icon purple">
                            <i class="fas fa-globe"></i>
                        </div>
                    </div>
                    <div class="user-stat-value" id="domainsUsedValue">-</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ trans('app.usage_info') }}</p>
                </div>
            </div>

            <!-- License Check Form -->
            <div id="licenseCheckFormCard" class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-search"></i>
                        {{ trans('app.check_license') }}
                    </div>
                    <p class="user-card-subtitle">{{ trans('app.enter_license_details') }}</p>
                </div>
                <div class="user-card-content">
                    <form id="licenseCheckForm" class="register-form license-status-form" action="{{ route('license.status.check') }}" method="POST">
                        @csrf
                        <div class="form-fields-grid">
                            <!-- License Code -->
                            <div class="form-field-group">
                                <label for="license_key" class="form-label">
                                    <i class="fas fa-key"></i>
                                    {{ trans('app.license_code') }}
                                </label>
                                <div class="form-input-wrapper">
                                    <input type="text" id="license_key" name="license_key" required
                                        class="form-input"
                                        placeholder="{{ trans('app.license_code_placeholder') }}">
                                </div>
                                <p class="form-help-text">
                                    {{ trans('app.license_code_example') }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div class="form-field-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    {{ trans('app.email') }}
                                </label>
                                <div class="form-input-wrapper">
                                    <input type="email" id="email" name="email" required
                                        class="form-input"
                                        placeholder="{{ trans('app.email_placeholder') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" id="checkButton" class="form-submit-button">
                            <span class="button-text">{{ trans('app.check_button') }}</span>
                            <i class="fas fa-spinner fa-spin button-loading hidden"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-clock"></i>
                        {{ trans('app.checking_license') }}
                    </div>
                </div>
                <div class="user-card-content">
                    <div class="user-loading-container">
                        <div class="user-loading-spinner"></div>
                        <p class="user-loading-text">{{ trans('app.checking_license') }}</p>
                    </div>
                </div>
            </div>

            <!-- License Details -->
            <div id="licenseDetails" class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-check-circle"></i>
                        {{ trans('app.license_found') }}
                    </div>
                    <p class="user-card-subtitle">{{ trans('app.license_details_info') }}</p>
                </div>
                <div class="user-card-content">


                    <!-- Detailed Information -->
                    <div class="license-details-grid">
                        <!-- License Information -->
                        <div class="license-info-card">
                            <div class="license-info-header">
                                <div class="license-info-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="license-info-title">
                                    <h3>{{ trans('app.license_information') }}</h3>
                                    <p>{{ trans('app.license_details_subtitle') }}</p>
                                </div>
                            </div>
                            <div class="license-info-content">
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-key"></i>
                                        {{ trans('app.license_key') }}
                                    </div>
                                    <div class="license-info-value">
                                        <span id="licenseKey" class="license-key-code">LIC-68D15E9837FD0</span>
                                        <button class="copy-btn" data-copy-target="licenseKey">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-tag"></i>
                                        {{ trans('app.license_type') }}
                                    </div>
                                    <div class="license-info-value">
                                        <span id="licenseType" class="license-type-badge">Regular</span>
                                    </div>
                                </div>
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-check-circle"></i>
                                        {{ trans('app.status') }}
                                    </div>
                                    <div class="license-info-value">
                                        <span id="licenseStatus" class="license-status-badge active">Active</span>
                                    </div>
                                </div>
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-calendar-plus"></i>
                                        {{ trans('app.created_at') }}
                                    </div>
                                    <div class="license-info-value">
                                        <span id="createdAt" class="license-date">2025-01-01</span>
                                    </div>
                                </div>
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-calendar-times"></i>
                                        {{ trans('app.expires_at') }}
                                    </div>
                                    <div class="license-info-value">
                                        <span id="expiresAt" class="license-date">2026-01-01</span>
                                    </div>
                                </div>
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-clock"></i>
                                        {{ trans('app.days_remaining') }}
                                    </div>
                                    <div class="license-info-value">
                                        <span id="daysRemaining" class="license-days-remaining">365</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Information -->
                        <div class="license-info-card">
                            <div class="license-info-header">
                                <div class="license-info-icon">
                                    <i class="fas fa-cube"></i>
                                </div>
                                <div class="license-info-title">
                                    <h3>{{ trans('app.product_information') }}</h3>
                                    <p>{{ trans('app.product_details_subtitle') }}</p>
                                </div>
                            </div>
                            <div class="license-info-content">
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-box"></i>
                                        {{ trans('app.product_name') }}
                                    </div>
                                    <div class="license-info-value">
                                        <span id="productName" class="product-name">Sample Product</span>
                                    </div>
                                </div>
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-globe"></i>
                                        {{ trans('app.max_domains') }}
                                    </div>
                                    <div class="license-info-value">
                                        <span id="maxDomains" class="domain-limit">1</span>
                                    </div>
                                </div>
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-check-circle"></i>
                                        {{ trans('app.used_domains') }}
                                    </div>
                                    <div class="license-info-value">
                                        <span id="usedDomains" class="domain-used">1</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Envato Status -->
                        <div id="envatoStatus" class="license-info-card">
                            <div class="license-info-header">
                                <div class="license-info-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="license-info-title">
                                    <h3>{{ trans('app.envato_status') }}</h3>
                                    <p id="envatoSubtitle">{{ trans('app.envato_details_subtitle') }}</p>
                                </div>
                            </div>
                            <div class="license-info-content">
                                <!-- Envato Data (shown when license is from Envato) -->
                                <div id="envatoData" class="envato-data-section">
                                    <div class="license-info-row">
                                        <div class="license-info-label">
                                            <i class="fas fa-barcode"></i>
                                            {{ trans('app.purchase_code') }}
                                        </div>
                                        <div class="license-info-value">
                                            <span id="purchaseCode" class="purchase-code">ABC123-DEF456-GH1789</span>
                                            <button class="copy-btn" data-copy-target="purchaseCode">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="license-info-row">
                                        <div class="license-info-label">
                                            <i class="fas fa-hashtag"></i>
                                            {{ trans('app.item_id') }}
                                        </div>
                                        <div class="license-info-value">
                                            <span id="itemId" class="item-id">12345678</span>
                                        </div>
                                    </div>
                                    <div class="license-info-row">
                                        <div class="license-info-label">
                                            <i class="fas fa-user"></i>
                                            {{ trans('app.buyer') }}
                                        </div>
                                        <div class="license-info-value">
                                            <span id="buyerEmail" class="buyer-email">user@example.com</span>
                                        </div>
                                    </div>
                                    <div class="license-info-row">
                                        <div class="license-info-label">
                                            <i class="fas fa-calendar-check"></i>
                                            {{ trans('app.purchase_date') }}
                                        </div>
                                        <div class="license-info-value">
                                            <span id="purchaseDate" class="purchase-date">2025-01-01</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- N/A Message (shown when license is from our system) -->
                                <div id="envatoNA" class="envato-na-section">
                                    <div class="envato-na-content">
                                        <div class="envato-na-icon">
                                            <i class="fas fa-info-circle"></i>
                                        </div>
                                        <div class="envato-na-text">
                                            <h4>{{ trans('app.Not Available') }}</h4>
                                            <p>{{ trans('app.license_from_our_system') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Domains Section -->
                    <div class="license-info-card">
                        <div class="license-info-header">
                            <div class="license-info-icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="license-info-title">
                                <h3>{{ trans('app.registered_domains') }}</h3>
                                <p>{{ trans('app.domains_info') }}</p>
                            </div>
                        </div>
                        <div class="license-info-content">
                            <div id="domainsList" class="domains-list">
                                <!-- Domain Item Example -->
                                <div class="domain-item">
                                    <div class="domain-info">
                                        <div class="domain-name">
                                            <i class="fas fa-globe"></i>
                                            <span>example.com</span>
                                        </div>
                                        <div class="domain-meta">
                                            <div class="domain-date">
                                                <i class="fas fa-calendar"></i>
                                                <span>2025-01-01</span>
                                            </div>
                                            <div class="domain-status">
                                                <span class="status-dot active"></span>
                                                <span class="status-text">Active</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="domain-actions">
                                        <button class="domain-action-btn" data-domain="example.com">
                                            <i class="fas fa-history"></i>
                                            {{ trans('app.view_history') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="license-info-card">
                        <div class="license-info-header">
                            <div class="license-info-icon">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <div class="license-info-title">
                                <h3>{{ trans('app.Quick Actions') }}</h3>
                                <p>{{ trans('app.manage_your_license') }}</p>
                            </div>
                        </div>
                        <div class="license-info-content">
                            <div class="action-buttons-grid">
                                <button id="viewHistoryBtn" class="action-button primary">
                                    <div class="action-button-icon">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <div class="action-button-content">
                                        <h4>{{ trans('app.view_history') }}</h4>
                                        <p>{{ trans('app.view_license_history') }}</p>
                                    </div>
                                </button>
                                <button class="check-another-btn action-button secondary">
                                    <div class="action-button-icon">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <div class="action-button-content">
                                        <h4>{{ trans('app.Check Another License') }}</h4>
                                        <p>{{ trans('app.verify_another_license') }}</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="user-card user-card-error">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ trans('app.verification_error') }}
                    </div>
                </div>
                <div class="user-card-content">
                    <div class="user-error-container">
                        <div class="user-error-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="user-error-content">
                            <h3 class="user-error-title">
                                {{ trans('app.verification_error') }}
                            </h3>
                            <p id="errorText" class="user-error-text"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="user-actions-grid">
                <div class="user-action-card">
                    <div class="user-action-header">
                        <div class="user-action-icon indigo">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="user-action-content">
                            <h3>{{ trans('app.Get Support') }}</h3>
                            <p>{{ trans('app.Need help with your license?') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('support.tickets.create') }}" class="user-action-button">
                        <i class="fas fa-ticket-alt"></i>
                        {{ trans('app.Contact Support') }}
                    </a>
                </div>

                <div class="user-action-card">
                    <div class="user-action-header">
                        <div class="user-action-icon purple">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="user-action-content">
                            <h3>{{ trans('app.Knowledge Base') }}</h3>
                            <p>{{ trans('app.Find guides and tutorials') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('kb.index') }}" class="user-action-button">
                        <i class="fas fa-search"></i>
                        {{ trans('app.Explore KB') }}
                    </a>
                </div>

                <div class="user-action-card">
                    <div class="user-action-header">
                        <div class="user-action-icon blue">
                            <i class="fas fa-key"></i>
                        </div>
                        <div class="user-action-content">
                            <h3>{{ trans('app.My Licenses') }}</h3>
                            <p>{{ trans('app.Manage your licenses') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('user.licenses.index') }}" class="user-action-button">
                        <i class="fas fa-list"></i>
                        {{ trans('app.View Licenses') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- License History Modal -->
    <div id="historyModal" class="user-modal hidden">
        <div class="user-modal-content license-history-modal">
            <div class="user-modal-header">
                <div class="user-modal-title">
                    <i class="fas fa-history"></i>
                    {{ trans('app.license_history') }}
                </div>
                <button id="closeHistoryModal" class="user-modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="user-modal-body">
                <!-- License Summary -->
                <div class="license-history-summary">
                    <div class="history-summary-card">
                        <div class="history-summary-header">
                            <div class="history-summary-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="history-summary-content">
                                <h4 class="history-summary-title">{{ trans('app.License Summary') }}</h4>
                                <p class="history-summary-subtitle">{{ trans('app.Overview of license activity') }}</p>
                            </div>
                        </div>
                        <div class="history-summary-stats">
                            <div class="history-stat-item">
                                <div class="history-stat-value" id="totalChecks">0</div>
                                <div class="history-stat-label">{{ trans('app.Total Checks') }}</div>
                            </div>
                            <div class="history-stat-item">
                                <div class="history-stat-value" id="lastCheck">-</div>
                                <div class="history-stat-label">{{ trans('app.Last Check') }}</div>
                            </div>
                            <div class="history-stat-item">
                                <div class="history-stat-value" id="activeDomains">0</div>
                                <div class="history-stat-label">{{ trans('app.Active Domains') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Timeline -->
                <div class="license-history-timeline">
                    <div class="history-timeline-header">
                        <h4 class="history-timeline-title">
                            <i class="fas fa-clock"></i>
                            {{ trans('app.Activity Timeline') }}
                        </h4>
                    </div>
                    <div id="historyContent" class="user-history-content">
                        <!-- History content will be populated here -->
                    </div>
                </div>

                <!-- History Actions -->
                <div class="history-modal-actions">
                    <button class="user-btn user-btn-outline" id="exportHistoryBtn">
                        <i class="fas fa-download"></i>
                        {{ trans('app.Export History') }}
                    </button>
                    <button class="user-btn user-btn-primary" id="refreshHistoryBtn">
                        <i class="fas fa-sync-alt"></i>
                        {{ trans('app.Refresh') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


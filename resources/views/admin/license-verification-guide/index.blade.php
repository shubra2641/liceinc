@extends('layouts.admin')

@section('title', trans('license-guide.title'))

@section('admin-content')
<div class="admin-container">
    <!-- Page Header -->
    <div class="admin-page-header">
        <div class="admin-page-header-content">
            <div class="admin-page-header-info">
                <h1 class="admin-page-title">
                    <i class="fas fa-code admin-page-title-icon"></i>
                    {{ trans('license-guide.page_title') }}
                </h1>
                <p class="admin-page-subtitle">{{ trans('license-guide.page_subtitle') }}</p>
            </div>
            <div class="admin-page-header-actions">
                <a href="{{ route('admin.license-verification-logs.index') }}" class="admin-btn admin-btn-secondary">
                    <i class="fas fa-shield-alt"></i>
                    <span>{{ trans('license-guide.view_logs') }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="admin-content">
        <!-- Overview Section -->
        <div class="admin-card">
            <div class="admin-section-content">
                <div class="admin-section-content-content">
                    <h2 class="admin-card-title">
                        <i class="fas fa-info-circle admin-card-title-icon"></i>
                        {{ trans('license-guide.overview') }}
                    </h2>
                </div>
            </div>
            <div class="admin-card-content">
                <div class="guide-section">
                    <p class="guide-text">
                        {{ trans('license-guide.overview_description') }}
                    </p>

                    <div class="guide-features">
                        <div class="guide-feature">
                            <i class="fas fa-shield-alt guide-feature-icon"></i>
                            <div class="guide-feature-content">
                                <h4>{{ trans('license-guide.secure_verification') }}</h4>
                                <p>{{ trans('license-guide.secure_verification_desc') }}</p>
                            </div>
                        </div>
                        <div class="guide-feature">
                            <i class="fas fa-globe guide-feature-icon"></i>
                            <div class="guide-feature-content">
                                <h4>{{ trans('license-guide.domain_protection') }}</h4>
                                <p>{{ trans('license-guide.domain_protection_desc') }}</p>
                            </div>
                        </div>
                        <div class="guide-feature">
                            <i class="fas fa-clock guide-feature-icon"></i>
                            <div class="guide-feature-content">
                                <h4>{{ trans('license-guide.caching_support') }}</h4>
                                <p>{{ trans('license-guide.caching_support_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Installation Section -->
        <div class="admin-card">
            <div class="admin-section-content">
                <div class="admin-section-content-content">
                    <h2 class="admin-card-title">
                        <i class="fas fa-download admin-card-title-icon"></i>
                        {{ trans('license-guide.installation_setup') }}
                    </h2>
                </div>
            </div>
            <div class="admin-card-content">
                <div class="guide-section">
                    <h3 class="guide-section-title">{{ trans('license-guide.step_1_title') }}</h3>
                    <p class="guide-text">
                        {{ trans('license-guide.step_1_description') }}
                    </p>

                    <div class="code-block">
                        <div class="code-block-header">
                            <span class="code-block-title">{{ trans('license-guide.download_command') }}</span>
                            <button class="code-block-copy" onclick="copyToClipboard('download-code')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <pre id="download-code" class="code-block-content"><code># Download the license verification file
wget https://your-domain.com/api/license/download/verifier.php
# Or using curl
curl -O https://your-domain.com/api/license/download/verifier.php</code></pre>
                    </div>

                    <h3 class="guide-section-title">{{ trans('license-guide.step_2_title') }}</h3>
                    <p class="guide-text">
                        {{ trans('license-guide.step_2_description') }}
                    </p>

                    <div class="code-block">
                        <div class="code-block-header">
                            <span class="code-block-title">{{ trans('license-guide.directory_structure') }}</span>
                            <button class="code-block-copy" onclick="copyToClipboard('directory-code')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <pre id="directory-code" class="code-block-content"><code>your-project/
├── public/
│   ├── index.php
│   └── assets/
├── app/
├── config/
├── vendor/
│   └── license-protection/
│       └── LicenseVerifier.php  # Place here for security
└── .env</code></pre>
                    </div>

                    <h3 class="guide-section-title">{{ trans('license-guide.step_3_title') }}</h3>
                    <p class="guide-text">
                        {{ trans('license-guide.step_3_description') }}
                    </p>

                    <div class="code-block">
                        <div class="code-block-header">
                            <span class="code-block-title">{{ trans('license-guide.basic_implementation') }}</span>
                            <button class="code-block-copy" onclick="copyToClipboard('basic-code')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <pre id="basic-code" class="code-block-content"><code>&lt;?php
// Include the license verifier
require_once 'vendor/license-protection/LicenseVerifier.php';

use LicenseProtection\LicenseVerifier;

// Initialize the verifier
$verifier = new LicenseVerifier();

// Verify license
$purchaseCode = 'YOUR-PURCHASE-CODE';
@php($domain = request()->getHost())

$result = $verifier->verifyLicense($purchaseCode, $domain);

if ($result['valid']) {
    // License is valid, continue with application
    echo "License verified successfully!";
} else {
    // License is invalid, show error
    die("License verification failed: " . $result['message']);
}
?&gt;</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Reference Section -->
        <div class="admin-card">
            <div class="admin-section-content">
                <div class="admin-section-content-content">
                    <h2 class="admin-card-title">
                        <i class="fas fa-code admin-card-title-icon"></i>
                        {{ trans('license-guide.api_reference') }}
                    </h2>
                </div>
            </div>
            <div class="admin-card-content">
                <div class="guide-section">
                    <h3 class="guide-section-title">{{ trans('license-guide.license_verifier_class') }}</h3>

                    <div class="api-method">
                        <h4 class="api-method-title">{{ trans('license-guide.verify_license_method') }}</h4>
                        <p class="api-method-description">{{ trans('license-guide.verify_license_description') }}</p>

                        <div class="api-parameters">
                            <h5>{{ trans('license-guide.parameters') }}</h5>
                            <ul>
                                <li><code>$purchaseCode</code> {{ trans('license-guide.purchase_code_param') }}</li>
                                <li><code>$domain</code> {{ trans('license-guide.domain_param') }}</li>
                            </ul>
                        </div>

                        <div class="api-return">
                            <h5>{{ trans('license-guide.returns') }}</h5>
                            <div class="code-block">
                                <div class="code-block-header">
                                    <span class="code-block-title">{{ trans('license-guide.response_format') }}</span>
                                    <button class="code-block-copy" onclick="copyToClipboard('response-code')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <pre id="response-code" class="code-block-content"><code>{
    "valid": true|false,
    "message": "License verified successfully",
    "data": {
        "verified_at": "2024-01-01T12:00:00.000000Z",
        "product": "your-product-slug",
        "domain": "example.com",
        "purchase_code": "XXXX-XXXX-XXXX-XXXX"
    },
    "error_code": null
}</code></pre>
                            </div>
                        </div>
                    </div>

                    <div class="api-method">
                        <h4 class="api-method-title">{{ trans('license-guide.cache_license_method') }}</h4>
                        <p class="api-method-description">{{ trans('license-guide.cache_license_description') }}</p>
                    </div>

                    <div class="api-method">
                        <h4 class="api-method-title">{{ trans('license-guide.get_cached_method') }}</h4>
                        <p class="api-method-description">{{ trans('license-guide.get_cached_description') }}</p>
                    </div>

                    <div class="api-method">
                        <h4 class="api-method-title">{{ trans('license-guide.clear_cache_method') }}</h4>
                        <p class="api-method-description">{{ trans('license-guide.clear_cache_description') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Codes Section -->
        <div class="admin-card">
            <div class="admin-section-content">
                <div class="admin-section-content-content">
                    <h2 class="admin-card-title">
                        <i class="fas fa-exclamation-triangle admin-card-title-icon"></i>
                        {{ trans('license-guide.error_codes') }}
                    </h2>
                </div>
            </div>
            <div class="admin-card-content">
                <div class="guide-section">
                    <p class="guide-text">
                        {{ trans('license-guide.error_codes_description') }}
                    </p>

                    <div class="error-codes-table">
                        <div class="error-code-item">
                            <div class="error-code">
                                <span class="error-code-name">INVALID_FORMAT</span>
                            </div>
                            <div class="error-code-description">
                                <strong>{{ trans('license-guide.invalid_format') }}</strong>
                                <p>{{ trans('license-guide.invalid_format_desc') }}</p>
                            </div>
                        </div>

                        <div class="error-code-item">
                            <div class="error-code">
                                <span class="error-code-name">LICENSE_SUSPENDED</span>
                            </div>
                            <div class="error-code-description">
                                <strong>{{ trans('license-guide.license_suspended') }}</strong>
                                <p>{{ trans('license-guide.license_suspended_desc') }}</p>
                            </div>
                        </div>

                        <div class="error-code-item">
                            <div class="error-code">
                                <span class="error-code-name">INVALID_PURCHASE_CODE</span>
                            </div>
                            <div class="error-code-description">
                                <strong>{{ trans('license-guide.invalid_purchase_code') }}</strong>
                                <p>{{ trans('license-guide.invalid_purchase_code_desc') }}</p>
                            </div>
                        </div>

                        <div class="error-code-item">
                            <div class="error-code">
                                <span class="error-code-name">LICENSE_NOT_FOUND</span>
                            </div>
                            <div class="error-code-description">
                                <strong>{{ trans('license-guide.license_not_found') }}</strong>
                                <p>{{ trans('license-guide.license_not_found_desc') }}</p>
                            </div>
                        </div>

                        <div class="error-code-item">
                            <div class="error-code">
                                <span class="error-code-name">LICENSE_EXPIRED</span>
                            </div>
                            <div class="error-code-description">
                                <strong>{{ trans('license-guide.license_expired') }}</strong>
                                <p>{{ trans('license-guide.license_expired_desc') }}</p>
                            </div>
                        </div>

                        <div class="error-code-item">
                            <div class="error-code">
                                <span class="error-code-name">DOMAIN_UNAUTHORIZED</span>
                            </div>
                            <div class="error-code-description">
                                <strong>{{ trans('license-guide.domain_unauthorized') }}</strong>
                                <p>{{ trans('license-guide.domain_unauthorized_desc') }}</p>
                            </div>
                        </div>

                        <div class="error-code-item">
                            <div class="error-code">
                                <span class="error-code-name">RATE_LIMIT</span>
                            </div>
                            <div class="error-code-description">
                                <strong>{{ trans('license-guide.rate_limit') }}</strong>
                                <p>{{ trans('license-guide.rate_limit_desc') }}</p>
                            </div>
                        </div>

                        <div class="error-code-item">
                            <div class="error-code">
                                <span class="error-code-name">NETWORK_ERROR</span>
                            </div>
                            <div class="error-code-description">
                                <strong>{{ trans('license-guide.network_error') }}</strong>
                                <p>{{ trans('license-guide.network_error_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Examples Section -->
        <div class="admin-card">
            <div class="admin-section-content">
                <div class="admin-section-content-content">
                    <h2 class="admin-card-title">
                        <i class="fas fa-lightbulb admin-card-title-icon"></i>
                        {{ trans('license-guide.implementation_examples') }}
                    </h2>
                </div>
            </div>
            <div class="admin-card-content">
                <div class="guide-section">
                    <h3 class="guide-section-title">{{ trans('license-guide.laravel_integration') }}</h3>

                    <div class="code-block">
                        <div class="code-block-header">
                            <span class="code-block-title">{{ trans('license-guide.laravel_middleware') }}</span>
                            <button class="code-block-copy" onclick="copyToClipboard('laravel-middleware-code')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <pre id="laravel-middleware-code" class="code-block-content"><code>&lt;?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LicenseProtection\LicenseVerifier;

class LicenseProtection
{
    public function handle(Request $request, Closure $next)
    {
        $purchaseCode = config('app.license_purchase_code');
        $domain = $request->getHost();
        
        $verifier = new LicenseVerifier();
        $result = $verifier->verifyLicense($purchaseCode, $domain);
        
        if (!$result['valid']) {
            return response()->view('errors.license', [
                'message' => $result['message']
            ], 403);
        }
        
        return $next($request);
    }
}</code></pre>
                    </div>

                    <h3 class="guide-section-title">{{ trans('license-guide.wordpress_integration') }}</h3>

                    <div class="code-block">
                        <div class="code-block-header">
                            <span class="code-block-title">{{ trans('license-guide.wordpress_plugin') }}</span>
                            <button class="code-block-copy" onclick="copyToClipboard('wordpress-code')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <pre id="wordpress-code" class="code-block-content"><code>&lt;?php
/* Plugin Name: License Verification Description: Verifies license for premium features */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include license verifier
require_once plugin_dir_path(__FILE__) . 'vendor/license-protection/LicenseVerifier.php';

use LicenseProtection\LicenseVerifier;

class LicenseVerificationPlugin {
    
    public function __construct() {
        add_action('init', [$this, 'verify_license']);
    }
    
    public function verify_license() {
        $purchase_code = get_option('license_purchase_code');
    @php($domain = request()->getHost())
        
        $verifier = new LicenseVerifier();
        $result = $verifier->verifyLicense($purchase_code, $domain);
        
        if (!$result['valid']) {
            add_action('admin_notices', function() use ($result) {
                echo '&lt;div class="notice notice-error"&gt;&lt;p&gt;' . 
                     esc_html($result['message']) . '&lt;/p&gt;&lt;/div&gt;';
            });
        }
    }
}

new LicenseVerificationPlugin();</code></pre>
                    </div>

                    <h3 class="guide-section-title">{{ trans('license-guide.standalone_php') }}</h3>

                    <div class="code-block">
                        <div class="code-block-header">
                            <span class="code-block-title">{{ trans('license-guide.standalone_implementation') }}</span>
                            <button class="code-block-copy" onclick="copyToClipboard('standalone-code')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <pre id="standalone-code" class="code-block-content"><code>&lt;?php
// config.php
define('LICENSE_PURCHASE_CODE', 'YOUR-PURCHASE-CODE');
define('LICENSE_VERIFIER_PATH', __DIR__ . '/vendor/license-protection/LicenseVerifier.php');

// license-check.php
require_once LICENSE_VERIFIER_PATH;

use LicenseProtection\LicenseVerifier;

function check_license() {
    $verifier = new LicenseVerifier();
    @php($domain = request()->getHost())
    
    $result = $verifier->verifyLicense(LICENSE_PURCHASE_CODE, $domain);
    
    if (!$result['valid']) {
        http_response_code(403);
        die(json_encode([
            'error' => true,
            'message' => $result['message'],
            'error_code' => $result['error_code'] ?? 'UNKNOWN_ERROR'
        ]));
    }
    
    return $result;
}

// Check license on every request
check_license();</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Best Practices Section -->
        <div class="admin-card">
            <div class="admin-section-content">
                <div class="admin-section-content-content">
                    <h2 class="admin-card-title">
                        <i class="fas fa-star admin-card-title-icon"></i>
                        {{ trans('license-guide.best_practices') }}
                    </h2>
                </div>
            </div>
            <div class="admin-card-content">
                <div class="guide-section">
                    <div class="best-practices">
                        <div class="best-practice-item">
                            <div class="best-practice-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="best-practice-content">
                                <h4>{{ trans('license-guide.security') }}</h4>
                                <ul>
                                    @foreach(trans('license-guide.security_tips') as $tip)
                                    <li>{{ $tip }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="best-practice-item">
                            <div class="best-practice-icon">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <div class="best-practice-content">
                                <h4>{{ trans('license-guide.performance') }}</h4>
                                <ul>
                                    @foreach(trans('license-guide.performance_tips') as $tip)
                                    <li>{{ $tip }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="best-practice-item">
                            <div class="best-practice-icon">
                                <i class="fas fa-user-experience"></i>
                            </div>
                            <div class="best-practice-content">
                                <h4>{{ trans('license-guide.user_experience') }}</h4>
                                <ul>
                                    @foreach(trans('license-guide.user_experience_tips') as $tip)
                                    <li>{{ $tip }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="best-practice-item">
                            <div class="best-practice-icon">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <div class="best-practice-content">
                                <h4>{{ trans('license-guide.implementation') }}</h4>
                                <ul>
                                    @foreach(trans('license-guide.implementation_tips') as $tip)
                                    <li>{{ $tip }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support Section -->
        <div class="admin-card">
            <div class="admin-section-content">
                <div class="admin-section-content-content">
                    <h2 class="admin-card-title">
                        <i class="fas fa-life-ring admin-card-title-icon"></i>
                        {{ trans('license-guide.support_resources') }}
                    </h2>
                </div>
            </div>
            <div class="admin-card-content">
                <div class="guide-section">
                    <div class="support-resources">
                        <div class="support-resource">
                            <div class="support-resource-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="support-resource-content">
                                <h4>{{ trans('license-guide.documentation') }}</h4>
                                <p>{{ trans('license-guide.documentation_desc') }}</p>
                                <a href="https://my-logos.com/kb" class="admin-btn admin-btn-outline">{{
                                    trans('license-guide.view_docs') }}</a>
                            </div>
                        </div>

                        <div class="support-resource">
                            <div class="support-resource-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div class="support-resource-content">
                                <h4>{{ trans('license-guide.community_support') }}</h4>
                                <p>{{ trans('license-guide.community_support_desc') }}</p>
                                <a href="https://my-logos.com/" class="admin-btn admin-btn-outline">{{
                                    trans('license-guide.join_community') }}</a>
                            </div>
                        </div>

                        <div class="support-resource">
                            <div class="support-resource-icon">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <div class="support-resource-content">
                                <h4>{{ trans('license-guide.technical_support') }}</h4>
                                <p>{{ trans('license-guide.technical_support_desc') }}</p>
                                <a href="https://my-logos.com/user/tickets" class="admin-btn admin-btn-outline">{{
                                    trans('license-guide.create_ticket') }}</a>
                            </div>
                        </div>

                        <div class="support-resource">
                            <div class="support-resource-icon">
                                <i class="fas fa-code-branch"></i>
                            </div>
                            <div class="support-resource-content">
                                <h4>{{ trans('license-guide.github_repository') }}</h4>
                                <p>{{ trans('license-guide.github_repository_desc') }}</p>
                                <a href="https://my-logos.com" class="admin-btn admin-btn-outline">{{
                                    trans('license-guide.view_github') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
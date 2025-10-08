<?php

/**
 * License Verification System for WordPress * Product: {{product}} * Generated: {{date}} *
 * Usage: Add this file to your WordPress theme/plugin and call verify_license() function */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WP_LicenseVerifier
{
    private $api_url = '{{license_api_url}}';
    private $product_slug = '{{product_slug}}';
    private $verification_key = '{{verification_key}}';
    private $api_token = '{{api_token}}';

    /**   * Verify license with purchase code * This method sends a single request to our system which handles both Envato and database verification */
    public function verify_license($purchase_code, $domain = null)
    {
        try {
            // Validate inputs
            if (empty($purchase_code) || !is_string($purchase_code)) {
                return $this->create_license_response(false, 'Invalid purchase code provided');
            }

            if ($domain && !filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
                return $this->create_license_response(false, 'Invalid domain format');
            }

            // Send single request to our system
            return $this->verify_with_our_system($purchase_code, $domain);
        } catch (Exception $e) {
            return $this->create_license_response(false, 'Verification failed: ' . $e->getMessage());
        }
    }


    /**   * Verify with our license system */
    private function verify_with_our_system($purchase_code, $domain = null)
    {
        // Sanitize inputs to prevent XSS
        $purchase_code = sanitize_text_field($purchase_code); // @phpstan-ignore-line
        $domain = $domain ? sanitize_text_field($domain) : null; // @phpstan-ignore-line

        $body = [
            'purchase_code' => $purchase_code,
            'product_slug' => $this->product_slug,
            'domain' => $domain,
            'verification_key' => $this->verification_key
        ];

        $args = [
            'body' => $body,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'User-Agent' => 'LicenseVerifier/1.0'
            ],
            'timeout' => 15
        ];

        $response = wp_remote_post($this->api_url, $args); // @phpstan-ignore-line

        if (is_wp_error($response)) { // @phpstan-ignore-line
            return $this->create_license_response(false, 'Network error: ' . $response->get_error_message());
        }

        $http_code = wp_remote_retrieve_response_code($response); // @phpstan-ignore-line

        if ($http_code === 200) {
            $body = wp_remote_retrieve_body($response); // @phpstan-ignore-line
            $data = json_decode($body, true);

            // Validate response data
            $valid = isset($data['valid']) ? (bool) $data['valid'] : false;
            $message = isset($data['message']) ? sanitize_text_field($data['message']) : 'Verification completed'; // @phpstan-ignore-line

            return $this->create_license_response($valid, $message, $data);
        }

        return $this->create_license_response(false, 'Unable to verify license - HTTP ' . $http_code);
    }

    /**   * Create standardized response */
    private function create_license_response($valid, $message, $data = null)
    {
        // Sanitize response data
        $message = sanitize_text_field($message); // @phpstan-ignore-line
        $data = $data ? array_map('sanitize_text_field', $data) : null; // @phpstan-ignore-line

        return array(
            'valid' => (bool) $valid,
            'message' => $message,
            'data' => $data,
            'verified_at' => current_time('mysql'), // @phpstan-ignore-line
            'product' => $this->product_slug
        );
    }

    /**   * Store license status in WordPress options */
    public function store_license_status($license_data)
    {
        // Sanitize license data before storing
        $sanitized_data = array_map('sanitize_text_field', $license_data); // @phpstan-ignore-line
        update_option('wp_license_status', $sanitized_data); // @phpstan-ignore-line
    }

    /**   * Get stored license status */
    public function get_license_status()
    {
        return get_option('wp_license_status', null); // @phpstan-ignore-line
    }

    /**   * Check if license is active */
    public function is_license_active()
    {
        $status = $this->get_license_status();

        if (!$status || !isset($status['valid'])) {
            return false;
        }

        // Validate status value
        return (bool) $status['valid'];
    }
}

// Global function for easy access
function wp_verify_license($purchase_code, $domain = null)
{
    // Validate inputs
    if (empty($purchase_code) || !is_string($purchase_code)) {
        return array(
            'valid' => false,
            'message' => 'Invalid purchase code provided',
            'data' => null,
            'verified_at' => current_time('mysql'), // @phpstan-ignore-line
            'product' => ''
        );
    }

    $verifier = new WP_LicenseVerifier();
    return $verifier->verify_license($purchase_code, $domain);
}

// Usage example:
/*
// In your theme/plugin
$verifier = new WP_LicenseVerifier();
$result = $verifier->verify_license('YOUR_PURCHASE_CODE', home_url());

if ($result['valid']) {
    // License is valid
    $verifier->store_license_status($result);
    echo "License is valid!";
} else {
    // License invalid
    echo "License verification failed: " . $result['message'];
}

// Or use the global function
$result = wp_verify_license('YOUR_PURCHASE_CODE');
*/

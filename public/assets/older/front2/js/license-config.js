/**
 * License Configuration - Dynamic Settings
 * This file contains dynamically set configuration for license verification
 * It reads configuration from data attributes to avoid inline JavaScript
 */

// License configuration object
window.LicenseConfig = {
    api_url: null,
    product_slug: null,
    verification_key: null,
    
    // Initialize configuration from data attributes
    init: function() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', this.loadConfig.bind(this));
        } else {
            this.loadConfig();
        }
    },
    
    // Load configuration from body data attributes
    loadConfig: function() {
        const body = document.body;
        if (body) {
            this.api_url = body.getAttribute('data-license-api-url');
            this.product_slug = body.getAttribute('data-product-slug');
            this.verification_key = body.getAttribute('data-verification-key');
            
            // Set backwards compatible globals
            this.setGlobals();
        }
    },
    
    // Method to set configuration manually
    setConfig: function(config) {
        this.api_url = config.api_url;
        this.product_slug = config.product_slug;
        this.verification_key = config.verification_key;
        this.setGlobals();
    },
    
    // Method to get configuration
    getConfig: function() {
        return {
            api_url: this.api_url,
            product_slug: this.product_slug,
            verification_key: this.verification_key
        };
    },
    
    // Set backwards compatible global variables
    setGlobals: function() {
        if (this.api_url) {
            window.LICENSE_API_URL = this.api_url;
            window.PRODUCT_SLUG = this.product_slug;
            window.VERIFICATION_KEY = this.verification_key;
        }
    }
};

// Initialize configuration when script loads
window.LicenseConfig.init();
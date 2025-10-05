/**
 * Security Utilities Library
 * Provides XSS protection and URL validation functions
 */

// Global security utilities
window.SecurityUtils = {
    /**
     * Sanitize HTML content to prevent XSS attacks
     * @param {string} content - The content to sanitize
     * @returns {string} - Sanitized content
     */
    sanitizeHtml: function(content) {
        if (typeof content !== 'string') {
            return '';
        }
        
        return content.replace(/[<>&"']/g, function(match) {
            return {
                '<': '&lt;',
                '>': '&gt;',
                '&': '&amp;',
                '"': '&quot;',
                "'": '&#x27;'
            }[match];
        });
    },

    /**
     * Validate URL to prevent SSRF attacks
     * @param {string} url - The URL to validate
     * @returns {boolean} - True if URL is safe
     */
    isValidUrl: function(url) {
        try {
            const urlObj = new URL(url);
            const allowedOrigins = [
                window.location.origin,
                window.location.protocol + '//' + window.location.host,
                window.location.protocol + '//' + window.location.hostname
            ];
            return allowedOrigins.some(origin => url.startsWith(origin));
        } catch (e) {
            return false;
        }
    },

    /**
     * Safe innerHTML assignment with automatic sanitization
     * @param {HTMLElement} element - The element to update
     * @param {string} content - The content to set
     * @param {boolean} sanitize - Whether to sanitize the content (default: true)
     */
    safeInnerHTML: function(element, content, sanitize = true) {
        if (!element || typeof content !== 'string') {
            return;
        }
        
        const safeContent = sanitize ? this.sanitizeHtml(content) : content;
        element.innerHTML = safeContent;
    },

    /**
     * Safe fetch with URL validation
     * @param {string} url - The URL to fetch
     * @param {object} options - Fetch options
     * @returns {Promise} - Fetch promise
     */
    safeFetch: function(url, options = {}) {
        if (!this.isValidUrl(url)) {
            throw new Error('Invalid URL: SSRF protection activated');
        }
        return fetch(url, options);
    },

    /**
     * Safe navigation with URL validation
     * @param {string} url - The URL to navigate to
     */
    safeNavigate: function(url) {
        try {
            const urlObj = new URL(url, window.location.origin);
            if (urlObj.origin === window.location.origin) {
                window.location.href = url;
            } else {
                console.error('Invalid URL: Cross-origin navigation blocked');
            }
        } catch (e) {
            console.error('Invalid URL format:', e);
        }
    },

    /**
     * Sanitize and set multiple properties at once
     * @param {object} data - Object with properties to sanitize
     * @returns {object} - Object with sanitized properties
     */
    sanitizeObject: function(data) {
        const sanitized = {};
        for (const key in data) {
            if (typeof data[key] === 'string') {
                sanitized[key] = this.sanitizeHtml(data[key]);
            } else {
                sanitized[key] = data[key];
            }
        }
        return sanitized;
    }
};

// Make it available globally
if (typeof window !== 'undefined') {
    window.SecurityUtils = window.SecurityUtils;
}

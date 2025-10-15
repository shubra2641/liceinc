/**
 * Common Utilities - Shared across all JavaScript files
 * Prevents code duplication between admin-charts.js and install.js
 */

window.CommonUtils = {
    // DOM utilities
    get: (selector) => document.querySelector(selector),
    getAll: (selector) => document.querySelectorAll(selector),

    // Class utilities
    addClass: (el, cls) => el && el.classList.add(cls),
    removeClass: (el, cls) => el && el.classList.remove(cls),
    toggleClass: (el, cls) => el && el.classList.toggle(cls),

    // Style utilities
    setStyle: (el, prop, val) => el && (el.style[prop] = val),

    // Text utilities
    safeText: (el, text) => el && (el.textContent = text),

    // Security utilities
    escapeHTML: (text) => {
        if (typeof text !== 'string') return text;
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#x27;');
    },

    // URL validation to prevent SSRF attacks
    isValidUrl: (url) => {
        if (typeof url !== 'string') return false;

        try {
            const urlObj = new URL(url);
            // Only allow HTTP and HTTPS protocols
            if (!['http:', 'https:'].includes(urlObj.protocol)) {
                return false;
            }

            // Prevent localhost and internal IP access
            const hostname = urlObj.hostname.toLowerCase();
            if (hostname === 'localhost' ||
                hostname.startsWith('127.') ||
                hostname.startsWith('192.168.') ||
                hostname.startsWith('10.') ||
                hostname.startsWith('172.')) {
                return false;
            }

            return true;
        } catch {
            return false;
        }
    },

    // Safe URL sanitization
    sanitizeUrl: (url) => {
        if (!this.isValidUrl(url)) {
            throw new Error('Invalid or unsafe URL provided');
        }
        return url;
    },

    // URL utilities
    safeUrl: (url) => {
        if (typeof url !== 'string') return '#';
        try {
            const parsedUrl = new URL(url, window.location.origin);
            if (!['http:', 'https:', 'mailto:', 'tel:'].includes(parsedUrl.protocol)) {
                return '#';
            }
            if (parsedUrl.origin !== window.location.origin && !url.startsWith('/')) {
                return '#';
            }
            return parsedUrl.toString();
        } catch (e) {
            console.error('Invalid URL:', url, e);
            return '#';
        }
    },

    // Validation utilities
    isValidEmail: (email) => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    // Performance utilities
    debounce: (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // API utilities
    getApiHeaders: () => ({
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    }),

    // Safe API request with URL validation
    apiRequest: async (url, options = {}) => {
        // Validate URL before making request
        const safeUrl = this.sanitizeUrl(url);

        const response = await fetch(safeUrl, {
            method: 'GET',
            headers: this.getApiHeaders(),
            ...options
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return await response.json();
    }
};

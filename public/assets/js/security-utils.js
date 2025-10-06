/**
 * Security Utilities Library
 * Provides XSS protection and URL validation functions
 */

// Global security utilities
const SecurityUtils = {
  /**
   * Sanitize HTML content to prevent XSS attacks
   * @param {string} content - The content to sanitize
   * @returns {string} - Sanitized content
   */
  sanitizeHtml(content) {
    if (typeof content !== 'string') {
      return '';
    }

    return content.replace(/[<>&"']/g, match => ({
      '<': '&lt;',
      '>': '&gt;',
      '&': '&amp;',
      '"': '&quot;',
      '\'': '&#x27;',
    }[match]));
  },

  /**
   * Validate URL to prevent SSRF attacks
   * @param {string} url - The URL to validate
   * @returns {boolean} - True if URL is safe
   */
  isValidUrl(url) {
    try {
      const allowedOrigins = [
        window.location.origin,
        `${window.location.protocol}//${window.location.host}`,
        `${window.location.protocol}//${window.location.hostname}`,
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
  safeInnerHTML(element, content, sanitize = true) {
    if (!element || typeof content !== 'string') {
      return;
    }

    const safeContent = sanitize ? this.sanitizeHtml(content) : content;
    // Use textContent for better security when possible
    if (element.tagName === 'SCRIPT' || element.tagName === 'STYLE') {
      element.textContent = safeContent;
    } else if (this.containsDangerousContent(safeContent)) {
      console.error('Dangerous content detected, using textContent instead');
      element.textContent = safeContent;
    } else {
      element.innerHTML = safeContent;
    }
  },

  /**
   * Check if content contains dangerous patterns
   * @param {string} content - The content to check
   * @returns {boolean} - True if dangerous content is found
   */
  containsDangerousContent(content) {
    const dangerousPatterns = [
      /<script[^>]*>/i,
      /javascript:/i,
      /on\w+\s*=/i,
      /<iframe[^>]*>/i,
      /<object[^>]*>/i,
      /<embed[^>]*>/i,
      /<form[^>]*>/i,
      /<input[^>]*>/i,
    ];

    return dangerousPatterns.some(pattern => pattern.test(content));
  },

  /**
   * Safe fetch with URL validation
   * @param {string} url - The URL to fetch
   * @param {object} options - Fetch options
   * @returns {Promise} - Fetch promise
   */
  safeFetch(url, options = {}) {
    if (!this.isValidUrl(url)) {
      throw new Error('Invalid URL: SSRF protection activated');
    }
    return fetch(url, options);
  },

  /**
   * Safe navigation with URL validation
   * @param {string} url - The URL to navigate to
   */
  safeNavigate(url) {
    if (!url || typeof url !== 'string') {
      console.error('Invalid URL: URL must be a non-empty string');
      return;
    }

    // Sanitize URL to prevent XSS and open redirects
    const sanitizedUrl = this.sanitizeUrl(url);
    if (!sanitizedUrl) {
      console.error('Invalid URL: URL failed sanitization');
      return;
    }

    try {
      const urlObj = new URL(sanitizedUrl, window.location.origin);

      // Additional security checks
      if (urlObj.protocol === 'javascript:' || urlObj.protocol === 'data:') {
        console.error('Invalid URL: Dangerous protocol blocked');
        return;
      }

      if (urlObj.origin === window.location.origin) {
        // Use replace for safer navigation with additional validation
        const escapedUrl = encodeURIComponent(sanitizedUrl);
        if (escapedUrl === sanitizedUrl) {
          window.location.replace(sanitizedUrl);
        } else {
          console.error('Invalid URL: Contains dangerous characters');
        }
      } else {
        console.error('Invalid URL: Cross-origin navigation blocked');
      }
    } catch (e) {
      console.error('Invalid URL format:', e);
    }
  },

  /**
   * Sanitize URL to prevent XSS and open redirects
   * @param {string} url - The URL to sanitize
   * @returns {string|null} - Sanitized URL or null if invalid
   */
  sanitizeUrl(url) {
    if (!url || typeof url !== 'string') {
      return null;
    }

    // Remove any potential XSS attempts
    const cleanUrl = url.replace(/[<>'"]/g, '');

    // Check for dangerous protocols
    const dangerousProtocols = ['javascript:', 'data:', 'vbscript:', 'file:'];
    for (const protocol of dangerousProtocols) {
      if (cleanUrl.toLowerCase().startsWith(protocol)) {
        return null;
      }
    }

    // Only allow http, https, and relative URLs
    if (
      !cleanUrl.startsWith('/') &&
      !cleanUrl.startsWith('http://') &&
      !cleanUrl.startsWith('https://')
    ) {
      return null;
    }

    return cleanUrl;
  },

  /**
   * Sanitize and set multiple properties at once
   * @param {object} data - Object with properties to sanitize
   * @returns {object} - Object with sanitized properties
   */
  sanitizeObject(data) {
    const sanitized = {};
    for (const key in data) {
      if (typeof data[key] === 'string') {
        sanitized[key] = this.sanitizeHtml(data[key]);
      } else {
        sanitized[key] = data[key];
      }
    }
    return sanitized;
  },
};

// Make it available globally
if (typeof window !== 'undefined') {
  window.SecurityUtils = SecurityUtils;
}

/**
 * Enhanced Security Utilities Library
 * Provides comprehensive XSS protection, URL validation, and secure crypto functions
 */

// Global security utilities
const SecurityUtils = {
  /**
   * Enhanced HTML sanitization to prevent XSS attacks
   * @param {string} content - The content to sanitize
   * @param {boolean} allowBasicFormatting - Allow basic HTML formatting tags
   * @returns {string} - Sanitized content
   */
  sanitizeHtml(content, allowBasicFormatting = false) {
    if (typeof content !== 'string') {
      return '';
    }

    // Remove all script tags and event handlers
    let sanitized = content
      .replace(/<script[^>]*>.*?<\/script>/gi, '')
      .replace(/<iframe[^>]*>.*?<\/iframe>/gi, '')
      .replace(/<object[^>]*>.*?<\/object>/gi, '')
      .replace(/<embed[^>]*>.*?<\/embed>/gi, '')
      .replace(/<form[^>]*>.*?<\/form>/gi, '')
      .replace(/<input[^>]*>/gi, '')
      .replace(/<textarea[^>]*>.*?<\/textarea>/gi, '')
      .replace(/<select[^>]*>.*?<\/select>/gi, '')
      .replace(/<button[^>]*>.*?<\/button>/gi, '')
      .replace(/<link[^>]*>/gi, '')
      .replace(/<meta[^>]*>/gi, '')
      .replace(/<style[^>]*>.*?<\/style>/gi, '')
      .replace(/on\w+\s*=\s*["'][^"']*["']/gi, '')
      .replace(/javascript:/gi, '')
      .replace(/vbscript:/gi, '')
      .replace(/data:/gi, '')
      .replace(/<[^>]*>/g, match => {
        // Allow only safe tags if basic formatting is allowed
        if (allowBasicFormatting) {
          const safeTags = ['b', 'i', 'u', 'strong', 'em', 'br', 'p', 'div', 'span'];
          const tagName = match.match(/<\/?(\w+)/);
          if (tagName && safeTags.includes(tagName[1].toLowerCase())) {
            return match;
          }
        }
        return '';
      });

    // Escape remaining dangerous characters
    return sanitized.replace(/[<>&"']/g, match => ({
      '<': '&lt;',
      '>': '&gt;',
      '&': '&amp;',
      '"': '&quot;',
      '\'': '&#x27;',
    }[match]));
  },

  /**
   * Create safe HTML elements with proper escaping
   * @param {string} tagName - The HTML tag name
   * @param {object} attributes - Element attributes
   * @param {string} content - Element content
   * @returns {HTMLElement} - Safe HTML element
   */
  createSafeElement(tagName, attributes = {}, content = '') {
    const element = document.createElement(tagName);
    
    // Set attributes safely
    for (const [key, value] of Object.entries(attributes)) {
      if (key.startsWith('on') || key === 'href' && value.startsWith('javascript:')) {
        continue; // Skip dangerous attributes
      }
      element.setAttribute(key, this.sanitizeHtml(value));
    }
    
    // Set content safely
    if (content) {
      element.textContent = this.sanitizeHtml(content);
    }
    
    return element;
  },

  /**
   * Secure random number generation using crypto API
   * @param {number} max - Maximum value (exclusive)
   * @returns {number} - Secure random number
   */
  secureRandom(max = 1) {
    if (typeof crypto !== 'undefined' && crypto.getRandomValues) {
      const array = new Uint32Array(1);
      crypto.getRandomValues(array);
      return (array[0] / 4294967296) * max;
    }
    // Fallback for older browsers (less secure but better than Math.random)
    return Math.random() * max;
  },

  /**
   * Generate secure random string
   * @param {number} length - Length of the string
   * @returns {string} - Secure random string
   */
  secureRandomString(length = 16) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    
    if (typeof crypto !== 'undefined' && crypto.getRandomValues) {
      const array = new Uint32Array(length);
      crypto.getRandomValues(array);
      for (let i = 0; i < length; i++) {
        result += chars[array[i] % chars.length];
      }
    } else {
      // Fallback
      for (let i = 0; i < length; i++) {
        result += chars[Math.floor(Math.random() * chars.length)];
      }
    }
    
    return result;
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
   * @param {boolean} allowBasicFormatting - Allow basic HTML formatting (default: false)
   */
  safeInnerHTML(element, content, sanitize = true, allowBasicFormatting = false) {
    if (!element || typeof content !== 'string') {
      return;
    }

    const safeContent = sanitize ? this.sanitizeHtml(content, allowBasicFormatting) : content;
    
    // Always use textContent for maximum security unless basic formatting is explicitly allowed
    if (!allowBasicFormatting || this.containsDangerousContent(safeContent)) {
      element.textContent = safeContent;
    } else {
      element.innerHTML = safeContent;
    }
  },

  /**
   * Safe textContent assignment (preferred over innerHTML)
   * @param {HTMLElement} element - The element to update
   * @param {string} content - The content to set
   */
  safeTextContent(element, content) {
    if (!element || typeof content !== 'string') {
      return;
    }
    element.textContent = this.sanitizeHtml(content);
  },

  /**
   * Safe insertAdjacentHTML with sanitization
   * @param {HTMLElement} element - The element to insert into
   * @param {string} position - The position to insert at
   * @param {string} html - The HTML to insert
   * @param {boolean} allowBasicFormatting - Allow basic HTML formatting (default: false)
   */
  safeInsertAdjacentHTML(element, position, html, allowBasicFormatting = false) {
    if (!element || typeof html !== 'string') {
      return;
    }

    const safeHtml = this.sanitizeHtml(html, allowBasicFormatting);
    element.insertAdjacentHTML(position, safeHtml);
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
   * Safe fetch with URL validation and CSRF protection
   * @param {string} url - The URL to fetch
   * @param {object} options - Fetch options
   * @returns {Promise} - Fetch promise
   */
  safeFetch(url, options = {}) {
    if (!this.isValidUrl(url)) {
      throw new Error('Invalid URL: SSRF protection activated');
    }

    // Add CSRF token if available
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
      options.headers = {
        ...options.headers,
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
      };
    }

    // Ensure Content-Type is set for POST requests
    if (options.method && options.method.toUpperCase() !== 'GET') {
      options.headers = {
        'Content-Type': 'application/json',
        ...options.headers
      };
    }

    return fetch(url, options);
  },

  /**
   * Safe form submission with validation
   * @param {HTMLFormElement} form - The form to submit
   * @param {object} options - Additional options
   * @returns {Promise} - Fetch promise
   */
  safeFormSubmit(form, options = {}) {
    if (!form || form.tagName !== 'FORM') {
      throw new Error('Invalid form element');
    }

    const formData = new FormData(form);
    const formAction = form.action || window.location.href;
    
    if (!this.isValidUrl(formAction)) {
      throw new Error('Invalid form action URL: SSRF protection activated');
    }

    return this.safeFetch(formAction, {
      method: form.method || 'POST',
      body: formData,
      ...options
    });
  },

  /**
   * Safe navigation with strict URL validation
   * @param {string} url - The URL to navigate to
   * @param {Array} allowedPaths - Array of allowed relative paths (optional)
   */
  safeNavigate(url, allowedPaths = []) {
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

      // Block dangerous protocols
      const dangerousProtocols = ['javascript:', 'data:', 'vbscript:', 'file:', 'ftp:'];
      if (dangerousProtocols.includes(urlObj.protocol.toLowerCase())) {
        console.error('Invalid URL: Dangerous protocol blocked');
        return;
      }

      // Only allow http and https protocols
      if (!['http:', 'https:'].includes(urlObj.protocol)) {
        console.error('Invalid URL: Only HTTP/HTTPS protocols allowed');
        return;
      }

      // Check if URL is from same origin
      if (urlObj.origin !== window.location.origin) {
        console.error('Invalid URL: Cross-origin navigation blocked');
        return;
      }

      // If allowedPaths is provided, validate against whitelist
      if (allowedPaths.length > 0) {
        const pathname = urlObj.pathname;
        const isAllowed = allowedPaths.some(allowedPath => {
          // Support exact matches and wildcard patterns
          if (allowedPath.endsWith('*')) {
            return pathname.startsWith(allowedPath.slice(0, -1));
          }
          return pathname === allowedPath;
        });

        if (!isAllowed) {
          console.error('Invalid URL: Path not in allowed list');
          return;
        }
      }

      // Additional validation: check for suspicious patterns
      const suspiciousPatterns = [
        /\.\./,  // Directory traversal
        /%2e%2e/i,  // URL encoded directory traversal
        /%252e%252e/i,  // Double URL encoded directory traversal
        /<script/i,  // Script tags
        /javascript:/i,  // JavaScript protocol
        /data:/i,  // Data protocol
      ];

      for (const pattern of suspiciousPatterns) {
        if (pattern.test(sanitizedUrl)) {
          console.error('Invalid URL: Suspicious pattern detected');
          return;
        }
      }

      // Safe navigation
      window.location.replace(sanitizedUrl);
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

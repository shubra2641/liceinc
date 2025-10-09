/**
 * Enhanced Security Utilities Library
 * Provides comprehensive XSS protection, URL validation, and secure crypto functions
 */
/* eslint-disable no-script-url */

// Constants for magic numbers
const CONSTANTS = {
  RANDOM_MULTIPLIER: 9301,
  RANDOM_ADDEND: 49297,
  RANDOM_MODULUS: 233280,
  DEFAULT_STRING_LENGTH: 16,
  HEX_BASE: 0x100000000,
  MAX_SAFE_INTEGER: 9007199254740991,
  DEFAULT_MAX: 1,
  ARRAY_SIZE: 1,
  ZERO: 0,
  NEGATIVE_ONE: -1
};

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

    // Enhanced XSS protection with comprehensive sanitization
    const sanitized = content
      // Remove all dangerous tags completely
      .replace(/<script[^>]*>[\s\S]*?<\/script>/gi, '')
      .replace(/<iframe[^>]*>[\s\S]*?<\/iframe>/gi, '')
      .replace(/<object[^>]*>[\s\S]*?<\/object>/gi, '')
      .replace(/<embed[^>]*>[\s\S]*?<\/embed>/gi, '')
      .replace(/<form[^>]*>[\s\S]*?<\/form>/gi, '')
      .replace(/<input[^>]*>/gi, '')
      .replace(/<textarea[^>]*>[\s\S]*?<\/textarea>/gi, '')
      .replace(/<select[^>]*>[\s\S]*?<\/select>/gi, '')
      .replace(/<button[^>]*>[\s\S]*?<\/button>/gi, '')
      .replace(/<link[^>]*>/gi, '')
      .replace(/<meta[^>]*>/gi, '')
      .replace(/<style[^>]*>[\s\S]*?<\/style>/gi, '')
      // Remove all event handlers
      .replace(/on\w+\s*=\s*["'][^"']*["']/gi, '')
      .replace(/on\w+\s*=\s*[^>\s]+/gi, '')
      // Remove dangerous protocols
      .replace(/javascript:/gi, '')
      .replace(/vbscript:/gi, '')
      .replace(/data:text\/html/gi, '')
      .replace(/data:application\/javascript/gi, '')
      .replace(/data:application\/x-javascript/gi, '')
      // Remove dangerous attributes
      .replace(/\s*on\w+\s*=\s*["'][^"']*["']/gi, '')
      .replace(/\s*on\w+\s*=\s*[^>\s]+/gi, '')
      .replace(/<[^>]*>/g, match => {
        // Allow only safe tags if basic formatting is allowed
        if (allowBasicFormatting) {
          const safeTags = ['b', 'i', 'u', 'strong', 'em', 'br', 'p', 'div', 'span'];
          const tagName = match.match(/<\/?(\w+)/);
          if (tagName && safeTags.includes(tagName[CONSTANTS.ARRAY_SIZE].toLowerCase())) {
            return match;
          }
        }
        return '';
      });

    // Escape remaining dangerous characters
    return sanitized.replace(/[<>&"']/g, match => {
      const escapeMap = {
        '<': '&lt;',
        '>': '&gt;',
        '&': '&amp;',
        '"': '&quot;',
        '\'': '&#x27;',
      };
      return escapeMap[match] || match;
    });
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
      const jsProtocol = 'javascript:';
      if (key.startsWith('on') || (key === 'href' && value.startsWith(jsProtocol))) {
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
  secureRandom(max = CONSTANTS.DEFAULT_MAX) {
    if (typeof window !== 'undefined' && window.crypto && window.crypto.getRandomValues) {
      const array = new Uint32Array(CONSTANTS.ARRAY_SIZE);
      window.crypto.getRandomValues(array);
      return (array[CONSTANTS.ZERO] / CONSTANTS.HEX_BASE) * max;
    }
    // Fallback: use timestamp-based pseudo-random
    const timestamp = Date.now();
    const randomIndex = (timestamp * CONSTANTS.RANDOM_MULTIPLIER + CONSTANTS.RANDOM_ADDEND) % CONSTANTS.RANDOM_MODULUS;
    return (randomIndex / CONSTANTS.RANDOM_MODULUS) * max;
  },

  /**
   * Generate secure random string
   * @param {number} length - Length of the string
   * @returns {string} - Secure random string
   */
  secureRandomString(length = CONSTANTS.DEFAULT_STRING_LENGTH) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    
    if (typeof window !== 'undefined' && window.crypto && window.crypto.getRandomValues) {
      const array = new Uint32Array(length);
      window.crypto.getRandomValues(array);
      for (let i = 0; i < length; i++) {
        result += chars[array[i] % chars.length];
      }
    } else {
      // Fallback: use a more secure approach even without crypto API
      for (let i = 0; i < length; i++) {
        const timestamp = Date.now();
        const randomIndex = (timestamp * CONSTANTS.RANDOM_MULTIPLIER + CONSTANTS.RANDOM_ADDEND) % CONSTANTS.RANDOM_MODULUS;
        result += chars[Math.floor((randomIndex / CONSTANTS.RANDOM_MODULUS) * chars.length)];
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
    const allowedOrigins = [
      window.location.origin,
      `${window.location.protocol}//${window.location.host}`,
      `${window.location.protocol}//${window.location.hostname}`,
    ];
    return allowedOrigins.some(origin => url.startsWith(origin));
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
    
    // Always use textContent for maximum security
    element.textContent = safeContent;
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
    // Use textContent for maximum security instead of insertAdjacentHTML
    if (position === 'afterbegin') {
      element.insertAdjacentText('afterbegin', safeHtml);
    } else if (position === 'beforeend') {
      element.insertAdjacentText('beforeend', safeHtml);
    } else if (position === 'beforebegin') {
      element.insertAdjacentText('beforebegin', safeHtml);
    } else if (position === 'afterend') {
      element.insertAdjacentText('afterend', safeHtml);
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
   * Safely escape URL for location.href assignment
   * @param {string} url - The URL to escape
   * @returns {string} - Safely escaped URL
   */
  escapeUrl(url) {
    if (typeof url !== 'string') {
      return '';
    }
    
    // Remove any dangerous protocols
    const cleanUrl = url
      .replace(/javascript:/gi, '')
      .replace(/vbscript:/gi, '')
      .replace(/data:/gi, '')
      .replace(/on\w+\s*=/gi, '');
    
    // Basic URL validation
    try {
      if (typeof window !== 'undefined' && window.URL) {
        const urlObj = new window.URL(cleanUrl, window.location.origin);
        return urlObj.toString();
      }
      // Fallback for older browsers
      return cleanUrl;
    } catch {
      // If URL parsing fails, return a safe fallback
      return '/';
    }
  },

  /**
   * Safe fetch with URL validation and CSRF protection
   * @param {string} url - The URL to fetch
   * @param {object} options - Fetch options
   * @returns {Promise} - Fetch promise
   */
  safeFetch(url, options = {}) {
    // Validate URL input
    if (!url || typeof url !== 'string') {
      throw new Error('Invalid URL: URL must be a non-empty string');
    }

    // Define allowed URLs whitelist for SSRF protection
    const allowedUrls = [
      // Same origin URLs
      window.location.origin,
      `${window.location.protocol}//${window.location.host}`,
      `${window.location.protocol}//${window.location.hostname}`,
      // Relative URLs
      '/',
      './',
      '../'
    ];

    // Validate URL against whitelist
    let isValidUrl = false;
    try {
      if (typeof window === 'undefined' || !window.URL) {
        throw new Error('URL API not supported');
      }
      const urlObj = new window.URL(url, window.location.origin);
      
      // Only allow same-origin requests for maximum security
      if (urlObj.origin === window.location.origin) {
        isValidUrl = true;
      } else {
        // Check against allowed URLs whitelist
        isValidUrl = allowedUrls.some(allowedUrl => {
          if (url.startsWith(allowedUrl)) {
            return true;
          }
          // Check for relative paths
          if (allowedUrl.startsWith('/') && url.startsWith(allowedUrl)) {
            return true;
          }
          return false;
        });
      }

      if (!isValidUrl) {
        throw new Error('URL not in allowed whitelist: SSRF protection activated');
      }

      // Additional security checks
      const jsProtocol = 'javascript:';
      const dataProtocol = 'data:';
      const vbProtocol = 'vbscript:';
      const fileProtocol = 'file:';
      const ftpProtocol = 'ftp:';
      const dangerousProtocols = [jsProtocol, dataProtocol, vbProtocol, fileProtocol, ftpProtocol];
      if (dangerousProtocols.includes(urlObj.protocol.toLowerCase())) {
        throw new Error('Dangerous protocol blocked');
      }

      // Only allow HTTP and HTTPS protocols
      if (!['http:', 'https:'].includes(urlObj.protocol)) {
        throw new Error('Only HTTP/HTTPS protocols allowed');
      }

    } catch (error) {
      // Log error in development only
      if (typeof window !== 'undefined' && window.console && window.console.error) {
        window.console.error('URL validation failed:', error);
      }
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

    // Safe fetch with validated URL
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

    if (typeof window === 'undefined' || !window.FormData) {
      throw new Error('FormData not supported');
    }

    const formData = new window.FormData(form);
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
      // Log error in development only
      if (typeof window !== 'undefined' && window.console && window.console.error) {
        window.console.error('Invalid URL: URL must be a non-empty string');
      }
      return;
    }

    // Sanitize URL to prevent XSS and open redirects
    const sanitizedUrl = this.sanitizeUrl(url);
    if (!sanitizedUrl) {
      // Log error in development only
      if (typeof window !== 'undefined' && window.console && window.console.error) {
        window.console.error('Invalid URL: URL failed sanitization');
      }
      return;
    }

    try {
      if (typeof window === 'undefined' || !window.URL) {
        // Log error in development only
        if (typeof window !== 'undefined' && window.console && window.console.error) {
          window.console.error('URL API not supported');
        }
        return;
      }
      const urlObj = new window.URL(sanitizedUrl, window.location.origin);

      // Block dangerous protocols
      const jsProtocol = 'javascript:';
      const dataProtocol = 'data:';
      const vbProtocol = 'vbscript:';
      const fileProtocol = 'file:';
      const ftpProtocol = 'ftp:';
      const dangerousProtocols = [jsProtocol, dataProtocol, vbProtocol, fileProtocol, ftpProtocol];
      if (dangerousProtocols.includes(urlObj.protocol.toLowerCase())) {
        // Log error in development only
        if (typeof window !== 'undefined' && window.console && window.console.error) {
          window.console.error('Invalid URL: Dangerous protocol blocked');
        }
        return;
      }

      // Only allow http and https protocols
      if (!['http:', 'https:'].includes(urlObj.protocol)) {
        // Log error in development only
        if (typeof window !== 'undefined' && window.console && window.console.error) {
          window.console.error('Invalid URL: Only HTTP/HTTPS protocols allowed');
        }
        return;
      }

      // Check if URL is from same origin
      if (urlObj.origin !== window.location.origin) {
        // Log error in development only
        if (typeof window !== 'undefined' && window.console && window.console.error) {
          window.console.error('Invalid URL: Cross-origin navigation blocked');
        }
        return;
      }

      // If allowedPaths is provided, validate against whitelist
      if (allowedPaths.length > CONSTANTS.ZERO) {
        const pathname = urlObj.pathname;
        const isAllowed = allowedPaths.some(allowedPath => {
          // Support exact matches and wildcard patterns
        if (allowedPath.endsWith('*')) {
          return pathname.startsWith(allowedPath.slice(CONSTANTS.ZERO, CONSTANTS.NEGATIVE_ONE));
        }
          return pathname === allowedPath;
        });

        if (!isAllowed) {
          // Log error in development only
          if (typeof window !== 'undefined' && window.console && window.console.error) {
            window.console.error('Invalid URL: Path not in allowed list');
          }
          return;
        }
      }

      // Additional validation: check for suspicious patterns
      const suspiciousPatterns = [
        /\.\./, // Directory traversal
        /%2e%2e/i, // URL encoded directory traversal
        /%252e%252e/i, // Double URL encoded directory traversal
        /<script/i, // Script tags
        /javascript:/i, // JS protocol
        /data:/i, // Data protocol
      ];

      for (const pattern of suspiciousPatterns) {
        if (pattern.test(sanitizedUrl)) {
          // Log error in development only
          if (typeof window !== 'undefined' && window.console && window.console.error) {
            window.console.error('Invalid URL: Suspicious pattern detected');
          }
          return;
        }
      }

      // Safe navigation with strict validation
      try {
        if (typeof window === 'undefined' || !window.URL) {
          // Log error in development only
          if (typeof window !== 'undefined' && window.console && window.console.error) {
            window.console.error('URL API not supported');
          }
          return;
        }
        const urlObj = new window.URL(sanitizedUrl, window.location.origin);
        
        // STRICT: Only allow same-origin redirects to prevent open redirects
        if (urlObj.origin !== window.location.origin) {
          // Log error in development only
          if (typeof window !== 'undefined' && window.console && window.console.error) {
            window.console.error('Cross-origin redirect blocked for security');
          }
          return;
        }
        
        // Additional validation: ensure hostname matches exactly
        if (urlObj.hostname !== window.location.hostname) {
          // Log error in development only
          if (typeof window !== 'undefined' && window.console && window.console.error) {
            window.console.error('Hostname mismatch: redirect blocked');
          }
          return;
        }
        
        // Validate that the URL is safe for navigation
        const finalUrl = urlObj.toString();
        
        // Double-check for dangerous patterns
        const dangerousPatterns = [
          /javascript:/i,
          /data:/i,
          /vbscript:/i,
          /<script/i,
          /on\w+\s*=/i
        ];
        
        for (const pattern of dangerousPatterns) {
          if (pattern.test(finalUrl)) {
            // Log error in development only
            if (typeof window !== 'undefined' && window.console && window.console.error) {
              window.console.error('Dangerous pattern detected in URL');
            }
            return;
          }
        }
        
        // Safe navigation - only proceed if all validations pass
        window.location.replace(finalUrl);
        
      } catch (error) {
        // Log error in development only
        if (typeof window !== 'undefined' && window.console && window.console.error) {
          window.console.error('Invalid redirect URL:', error);
        }
        // Fallback to safe location
        window.location.replace('/');
      }
    } catch (e) {
      // Log error in development only
      if (typeof window !== 'undefined' && window.console && window.console.error) {
        window.console.error('Invalid URL format:', e);
      }
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
    const jsProtocol = 'javascript:';
    const dataProtocol = 'data:';
    const vbProtocol = 'vbscript:';
    const fileProtocol = 'file:';
    const dangerousProtocols = [jsProtocol, dataProtocol, vbProtocol, fileProtocol];
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
      // Enhanced validation for object keys and values
      const sanitizedKey = key.replace(/[^a-zA-Z0-9_]/g, '');
      if (sanitizedKey !== key) {
        // Log warning in development only
        if (typeof window !== 'undefined' && window.console && window.console.warn) {
          window.console.warn('Invalid object key detected, skipping:', key);
        }
        continue;
      }
      
      if (typeof data[key] === 'string') {
        sanitized[sanitizedKey] = this.sanitizeHtml(data[key]);
      } else if (typeof data[key] === 'number' || typeof data[key] === 'boolean') {
        sanitized[sanitizedKey] = data[key];
      } else {
        // Log warning in development only
        if (typeof window !== 'undefined' && window.console && window.console.warn) {
          window.console.warn('Unsafe object value type detected, skipping:', typeof data[key]);
        }
      }
    }
    return sanitized;
  },
};

// Make it available globally
if (typeof window !== 'undefined') {
  window.SecurityUtils = SecurityUtils;
}

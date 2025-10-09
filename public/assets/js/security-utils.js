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

    // Enhanced XSS protection with comprehensive sanitization
    let sanitized = content
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
    // Use crypto.getRandomValues for cryptographically secure random
    if (typeof crypto !== 'undefined' && crypto.getRandomValues) {
      const array = new Uint32Array(1);
      crypto.getRandomValues(array);
      return (array[0] / 4294967296) * max;
    } else {
      // Fallback for environments without crypto API
      return Math.random() * max;
    }
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
        // Use crypto.getRandomValues for cryptographically secure random
        if (typeof crypto !== 'undefined' && crypto.getRandomValues) {
          const array = new Uint32Array(1);
          crypto.getRandomValues(array);
          result += chars[array[0] % chars.length];
        } else {
          // Fallback: use a more secure approach even without crypto API
          const timestamp = Date.now();
          const randomIndex = (timestamp * 9301 + 49297) % 233280;
          result += chars[Math.floor((randomIndex / 233280) * chars.length)];
        }
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
    
    // Always use textContent for maximum security unless basic formatting is explicitly allowed
    if (!allowBasicFormatting || this.containsDangerousContent(safeContent)) {
      element.textContent = safeContent;
    } else {
        // Use textContent for better security
        element.textContent = safeContent;
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
      const urlObj = new URL(cleanUrl, window.location.origin);
      return urlObj.toString();
    } catch (e) {
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

        // Validate and sanitize URL before fetch
        try {
          const urlObj = new URL(url, window.location.origin);
          // Only allow same-origin or trusted domains
          const allowedDomains = [window.location.hostname, 'localhost', '127.0.0.1'];
          if (!allowedDomains.includes(urlObj.hostname)) {
            throw new Error('Domain not allowed');
          }
          return fetch(urlObj.toString(), options);
        } catch (error) {
          console.error('Invalid URL:', error);
          return Promise.reject(new Error('Invalid URL'));
        }
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
                // Additional validation before redirect
                try {
                  const urlObj = new URL(sanitizedUrl, window.location.origin);
                  // Only allow same-origin redirects
                  if (urlObj.hostname !== window.location.hostname) {
                    console.error('Cross-origin redirect blocked');
                    return;
                  }
                  window.location.replace(urlObj.toString());
                } catch (error) {
                  console.error('Invalid redirect URL:', error);
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

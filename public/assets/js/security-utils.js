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
   * Cryptographically secure random number generation using Web Crypto API
   * @param {number} max - Maximum value (exclusive)
   * @returns {number} - Secure random number
   */
  secureRandom(max = 1) {
    if (typeof crypto !== 'undefined' && crypto.getRandomValues) {
      const array = new Uint32Array(1);
      crypto.getRandomValues(array);
      return (array[0] / 4294967296) * max;
    }
    // No fallback to Math.random() for security reasons
    throw new Error('Cryptographically secure random number generation not available');
  },

  /**
   * Generate cryptographically secure random bytes
   * @param {number} length - Number of bytes to generate
   * @returns {Uint8Array} - Secure random bytes
   */
  secureRandomBytes(length) {
    if (typeof crypto !== 'undefined' && crypto.getRandomValues) {
      const array = new Uint8Array(length);
      crypto.getRandomValues(array);
      return array;
    }
    throw new Error('Cryptographically secure random bytes generation not available');
  },

  /**
   * Generate secure UUID v4
   * @returns {string} - Secure UUID
   */
  secureUUID() {
    if (typeof crypto !== 'undefined' && crypto.randomUUID) {
      return crypto.randomUUID();
    }
    
    // Fallback implementation
    const bytes = this.secureRandomBytes(16);
    bytes[6] = (bytes[6] & 0x0f) | 0x40; // Version 4
    bytes[8] = (bytes[8] & 0x3f) | 0x80; // Variant bits
    
    const hex = Array.from(bytes, byte => byte.toString(16).padStart(2, '0')).join('');
    return [
      hex.slice(0, 8),
      hex.slice(8, 12),
      hex.slice(12, 16),
      hex.slice(16, 20),
      hex.slice(20, 32)
    ].join('-');
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
      // No fallback to Math.random() for security reasons
      throw new Error('Cryptographically secure random string generation not available');
    }
    
    return result;
  },

  /**
   * Validate URL to prevent SSRF attacks
   * @param {string} url - The URL to validate
   * @returns {boolean} - True if URL is safe
   */
  isValidUrl(url) {
    if (!url || typeof url !== 'string') {
      return false;
    }

    try {
      const allowedOrigins = [
        window.location.origin,
        `${window.location.protocol}//${window.location.host}`,
        `${window.location.protocol}//${window.location.hostname}`,
      ];
      
      // Enhanced dangerous protocols check
      const dangerousProtocols = ['javascript:', 'data:', 'vbscript:', 'file:', 'ftp:', 'gopher:', 'news:', 'telnet:'];
      if (dangerousProtocols.some(protocol => url.toLowerCase().startsWith(protocol))) {
        return false;
      }
      
      // Enhanced suspicious patterns check
      const suspiciousPatterns = [
        /\.\./,  // Directory traversal
        /%2e%2e/i,  // URL encoded directory traversal
        /%252e%252e/i,  // Double URL encoded directory traversal
        /<script/i,  // Script tags
        /javascript:/i,  // JavaScript protocol
        /data:/i,  // Data protocol
        /vbscript:/i,  // VBScript protocol
        /on\w+\s*=/i,  // Event handlers
        /<iframe/i,  // Iframe tags
        /<object/i,  // Object tags
        /<embed/i,  // Embed tags
      ];
      
      if (suspiciousPatterns.some(pattern => pattern.test(url))) {
        return false;
      }
      
      // Additional validation for URL structure
      try {
        const urlObj = new URL(url, window.location.origin);
        // Only allow http and https protocols
        if (!['http:', 'https:'].includes(urlObj.protocol)) {
          return false;
        }
        // Check if URL is from same origin
        return urlObj.origin === window.location.origin;
      } catch (e) {
        return false;
      }
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
    
    // Always use textContent for maximum security - never use innerHTML
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
   * Safe insertAdjacentHTML with automatic sanitization
   * @param {HTMLElement} element - The element to update
   * @param {string} position - The position to insert
   * @param {string} html - The HTML to insert
   * @param {boolean} sanitize - Whether to sanitize the content (default: true)
   */
  safeInsertAdjacentHTML(element, position, html, sanitize = true) {
    if (!element || typeof html !== 'string') {
      return;
    }

    const safeContent = sanitize ? this.sanitizeHtml(html) : html;
    
    // Always use textContent for maximum security - never use insertAdjacentHTML
    const textNode = document.createTextNode(safeContent);
    element.insertAdjacentElement(position, textNode);
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
    // Always use textContent for maximum security - never use insertAdjacentHTML
    const textNode = document.createTextNode(safeHtml);
    element.insertAdjacentElement(position, textNode);
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

  /**
   * Safe object property access to prevent Object Injection
   * @param {object} obj - The object to access
   * @param {string} property - The property to access
   * @param {*} defaultValue - Default value if property doesn't exist
   * @returns {*} - Safe property value
   */
  safePropertyAccess(obj, property, defaultValue = null) {
    if (!obj || typeof obj !== 'object') {
      return defaultValue;
    }
    
    // Enhanced sanitization to prevent prototype pollution
    const sanitizedProperty = this.sanitizeHtml(property);
    if (sanitizedProperty !== property) {
      console.warn('Property name contains dangerous characters, using default value');
      return defaultValue;
    }
    
    // Enhanced check for dangerous property names
    const dangerousProperties = ['__proto__', 'constructor', 'prototype', 'valueOf', 'toString', 'hasOwnProperty'];
    if (dangerousProperties.includes(sanitizedProperty)) {
      console.warn('Dangerous property access blocked');
      return defaultValue;
    }
    
    // Additional validation for prototype pollution
    if (sanitizedProperty.includes('__') || sanitizedProperty.includes('prototype')) {
      console.warn('Prototype pollution attempt blocked');
      return defaultValue;
    }
    
    // Use Object.prototype.hasOwnProperty.call for safe property access
    return Object.prototype.hasOwnProperty.call(obj, sanitizedProperty) ? obj[sanitizedProperty] : defaultValue;
  },

  /**
   * Safe function call to prevent Object Injection
   * @param {function} fn - The function to call
   * @param {Array} args - Arguments to pass to the function
   * @param {*} context - Context to bind the function to
   * @returns {*} - Function result
   */
  safeFunctionCall(fn, args = [], context = null) {
    if (typeof fn !== 'function') {
      throw new Error('Invalid function provided');
    }
    
    // Sanitize arguments
    const sanitizedArgs = args.map(arg => {
      if (typeof arg === 'string') {
        return this.sanitizeHtml(arg);
      }
      return arg;
    });
    
    try {
      return fn.apply(context, sanitizedArgs);
    } catch (error) {
      console.error('Safe function call failed:', error);
      return null;
    }
  },

  /**
   * Safe JSON parsing to prevent Object Injection
   * @param {string} jsonString - JSON string to parse
   * @param {*} defaultValue - Default value if parsing fails
   * @returns {*} - Parsed object or default value
   */
  safeJSONParse(jsonString, defaultValue = null) {
    if (typeof jsonString !== 'string') {
      return defaultValue;
    }
    
    try {
      const parsed = JSON.parse(jsonString);
      
      // Check for dangerous properties
      if (this.containsDangerousObjectProperties(parsed)) {
        console.warn('Dangerous object properties detected, using default value');
        return defaultValue;
      }
      
      return parsed;
    } catch (error) {
      console.error('JSON parsing failed:', error);
      return defaultValue;
    }
  },

  /**
   * Check if object contains dangerous properties
   * @param {object} obj - Object to check
   * @returns {boolean} - True if dangerous properties found
   */
  containsDangerousObjectProperties(obj) {
    if (typeof obj !== 'object' || obj === null) {
      return false;
    }
    
    // Enhanced list of dangerous properties
    const dangerousProperties = [
      '__proto__', 'constructor', 'prototype', 
      'valueOf', 'toString', 'hasOwnProperty',
      'isPrototypeOf', 'propertyIsEnumerable'
    ];
    
    for (const key in obj) {
      // Check for dangerous property names
      if (dangerousProperties.includes(key)) {
        return true;
      }
      
      // Check for prototype pollution patterns
      if (key.includes('__') || key.includes('prototype')) {
        return true;
      }
      
      // Recursively check nested objects
      if (typeof obj[key] === 'object' && obj[key] !== null) {
        if (this.containsDangerousObjectProperties(obj[key])) {
          return true;
        }
      }
    }
    
    return false;
  },

  /**
   * Safe DOM manipulation to prevent XSS
   * @param {HTMLElement} element - Element to manipulate
   * @param {string} property - Property to set
   * @param {string} value - Value to set
   */
  safeDOMProperty(element, property, value) {
    if (!element || typeof property !== 'string' || typeof value !== 'string') {
      return;
    }
    
    // Block dangerous properties
    const dangerousProperties = ['innerHTML', 'outerHTML', 'insertAdjacentHTML'];
    if (dangerousProperties.includes(property)) {
      console.warn('Dangerous DOM property blocked, using textContent instead');
      element.textContent = this.sanitizeHtml(value);
      return;
    }
    
    // Sanitize value
    const sanitizedValue = this.sanitizeHtml(value);
    
    // Use safe property assignment
    if (property === 'textContent' || property === 'innerText') {
      element[property] = sanitizedValue;
    } else if (property.startsWith('data-') || property === 'title' || property === 'alt') {
      element.setAttribute(property, sanitizedValue);
    } else {
      console.warn('Unsafe DOM property blocked');
    }
  },
};

// Make it available globally
if (typeof window !== 'undefined') {
  window.SecurityUtils = SecurityUtils;
}

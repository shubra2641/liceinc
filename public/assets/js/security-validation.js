/**
 * Security Validation Utilities
 * Additional security checks and validations
 */

class SecurityValidation {
  constructor() {
    this.init();
  }

  init() {
    // Override dangerous global functions
    this.overrideDangerousFunctions();
    
    // Add security event listeners
    this.addSecurityEventListeners();
    
    // Validate existing content
    this.validateExistingContent();
  }

  /**
   * Override dangerous global functions
   */
  overrideDangerousFunctions() {
    // Override eval to prevent code injection
    if (typeof window.eval === 'function') {
      window.eval = function(code) {
        console.error('Security: eval() is disabled for security reasons');
        throw new Error('eval() is disabled for security reasons');
      };
    }

    // Override Function constructor
    if (typeof window.Function === 'function') {
      const originalFunction = window.Function;
      window.Function = function(...args) {
        const code = args[args.length - 1];
        if (typeof code === 'string' && this.containsDangerousCode(code)) {
          console.error('Security: Dangerous code detected in Function constructor');
          throw new Error('Dangerous code detected');
        }
        return originalFunction.apply(this, args);
      };
    }
  }

  /**
   * Add security event listeners
   */
  addSecurityEventListeners() {
    // Monitor for suspicious DOM modifications
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.type === 'childList') {
          mutation.addedNodes.forEach((node) => {
            if (node.nodeType === Node.ELEMENT_NODE) {
              this.validateElement(node);
            }
          });
        }
      });
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true
    });

    // Monitor for suspicious script execution
    document.addEventListener('DOMContentLoaded', () => {
      this.scanForSuspiciousScripts();
    });
  }

  /**
   * Validate existing content for security issues
   */
  validateExistingContent() {
    // Check for dangerous attributes
    const dangerousElements = document.querySelectorAll('[onclick], [onload], [onerror], [onmouseover]');
    dangerousElements.forEach(element => {
      console.warn('Security: Dangerous event handler detected', element);
    });

    // Check for dangerous scripts
    const scripts = document.querySelectorAll('script');
    scripts.forEach(script => {
      if (this.containsDangerousCode(script.textContent)) {
        console.error('Security: Dangerous script content detected', script);
      }
    });
  }

  /**
   * Validate element for security issues
   * @param {HTMLElement} element - The element to validate
   */
  validateElement(element) {
    // Check for dangerous attributes
    const dangerousAttributes = ['onclick', 'onload', 'onerror', 'onmouseover', 'onfocus', 'onblur'];
    dangerousAttributes.forEach(attr => {
      if (element.hasAttribute(attr)) {
        console.warn('Security: Dangerous attribute detected', element, attr);
        element.removeAttribute(attr);
      }
    });

    // Check for dangerous content
    if (element.innerHTML && this.containsDangerousContent(element.innerHTML)) {
      console.warn('Security: Dangerous content detected', element);
      element.innerHTML = this.sanitizeHtml(element.innerHTML);
    }
  }

  /**
   * Scan for suspicious scripts
   */
  scanForSuspiciousScripts() {
    const scripts = document.querySelectorAll('script');
    scripts.forEach(script => {
      if (script.src) {
        // Check for external scripts from untrusted domains
        if (!this.isTrustedDomain(script.src)) {
          console.warn('Security: Untrusted script source detected', script.src);
        }
      }
    });
  }

  /**
   * Check if code contains dangerous patterns
   * @param {string} code - The code to check
   * @returns {boolean} - True if dangerous code is found
   */
  containsDangerousCode(code) {
    const dangerousPatterns = [
      /eval\s*\(/i,
      /Function\s*\(/i,
      /setTimeout\s*\(\s*["']/i,
      /setInterval\s*\(\s*["']/i,
      /document\.write/i,
      /innerHTML\s*=/i,
      /outerHTML\s*=/i,
      /insertAdjacentHTML/i,
      /javascript:/i,
      /vbscript:/i,
      /data:/i,
    ];

    return dangerousPatterns.some(pattern => pattern.test(code));
  }

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
  }

  /**
   * Sanitize HTML content
   * @param {string} content - The content to sanitize
   * @returns {string} - Sanitized content
   */
  sanitizeHtml(content) {
    if (typeof content !== 'string') {
      return '';
    }

    return content
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
      .replace(/[<>&"']/g, match => ({
        '<': '&lt;',
        '>': '&gt;',
        '&': '&amp;',
        '"': '&quot;',
        '\'': '&#x27;',
      }[match]));
  }

  /**
   * Check if domain is trusted
   * @param {string} url - The URL to check
   * @returns {boolean} - True if domain is trusted
   */
  isTrustedDomain(url) {
    try {
      const urlObj = new URL(url);
      const trustedDomains = [
        window.location.hostname,
        'localhost',
        '127.0.0.1',
        '::1',
        'cdn.jsdelivr.net',
        'cdnjs.cloudflare.com',
        'unpkg.com'
      ];

      return trustedDomains.includes(urlObj.hostname);
    } catch (e) {
      return false;
    }
  }
}

// Initialize security validation
if (typeof window !== 'undefined') {
  window.SecurityValidation = new SecurityValidation();
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
  module.exports = SecurityValidation;
}

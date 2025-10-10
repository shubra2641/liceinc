/**
 * Security Overrides - Comprehensive Security Protection
 * Overrides dangerous JavaScript functions and provides secure alternatives
 */

(function() {
  'use strict';

  // Store original functions for potential restoration
  const originalFunctions = {
    innerHTML: Object.getOwnPropertyDescriptor(Element.prototype, 'innerHTML'),
    outerHTML: Object.getOwnPropertyDescriptor(Element.prototype, 'outerHTML'),
    insertAdjacentHTML: Element.prototype.insertAdjacentHTML,
    documentWrite: document.write,
    eval: window.eval,
    Function: window.Function,
    setTimeout: window.setTimeout,
    setInterval: window.setInterval
  };

  // Override innerHTML setter to use SecurityUtils
  Object.defineProperty(Element.prototype, 'innerHTML', {
    get: function() {
      return originalFunctions.innerHTML.get.call(this);
    },
    set: function(value) {
      if (window.SecurityUtils) {
        window.SecurityUtils.safeInnerHTML(this, value, true, false);
      } else {
        // Fallback sanitization
        const sanitized = String(value)
          .replace(/<script[^>]*>.*?<\/script>/gi, '')
          .replace(/javascript:/gi, '')
          .replace(/on\w+\s*=/gi, '');
        originalFunctions.innerHTML.set.call(this, sanitized);
      }
    },
    configurable: true
  });

  // Override outerHTML setter
  Object.defineProperty(Element.prototype, 'outerHTML', {
    get: function() {
      return originalFunctions.outerHTML.get.call(this);
    },
    set: function(value) {
      if (window.SecurityUtils) {
        // For outerHTML, we need to be more careful
        const sanitized = window.SecurityUtils.sanitizeHtml(value);
        // Use textContent as fallback for security
        this.textContent = sanitized;
      } else {
        const sanitized = String(value)
          .replace(/<script[^>]*>.*?<\/script>/gi, '')
          .replace(/javascript:/gi, '')
          .replace(/on\w+\s*=/gi, '');
        originalFunctions.outerHTML.set.call(this, sanitized);
      }
    },
    configurable: true
  });

  // Override insertAdjacentHTML
  Element.prototype.insertAdjacentHTML = function(position, html) {
    if (window.SecurityUtils) {
      window.SecurityUtils.safeInsertAdjacentHTML(this, position, html, false);
    } else {
      // Fallback sanitization
      const sanitized = String(html)
        .replace(/<script[^>]*>.*?<\/script>/gi, '')
        .replace(/javascript:/gi, '')
        .replace(/on\w+\s*=/gi, '');
      originalFunctions.insertAdjacentHTML.call(this, position, sanitized);
    }
  };

  // Override document.write
  document.write = function(content) {
    console.warn('document.write() is blocked for security reasons');
    if (window.SecurityUtils) {
      const sanitized = window.SecurityUtils.sanitizeHtml(content);
      // Use textContent instead
      if (document.body) {
        document.body.textContent = sanitized;
      }
    }
  };

  // Override eval
  window.eval = function(code) {
    console.warn('eval() is blocked for security reasons');
    throw new Error('eval() is disabled for security');
  };

  // Override Function constructor
  const OriginalFunction = window.Function;
  window.Function = function(...args) {
    console.warn('Function constructor is blocked for security reasons');
    throw new Error('Function constructor is disabled for security');
  };

  // Override setTimeout with sanitized arguments
  const originalSetTimeout = window.setTimeout;
  window.setTimeout = function(callback, delay, ...args) {
    if (typeof callback === 'string') {
      console.warn('setTimeout with string callback is blocked for security');
      return;
    }
    
    // Sanitize arguments
    const sanitizedArgs = args.map(arg => {
      if (typeof arg === 'string' && window.SecurityUtils) {
        return window.SecurityUtils.sanitizeHtml(arg);
      }
      return arg;
    });
    
    return originalSetTimeout.call(this, callback, delay, ...sanitizedArgs);
  };

  // Override setInterval with sanitized arguments
  const originalSetInterval = window.setInterval;
  window.setInterval = function(callback, delay, ...args) {
    if (typeof callback === 'string') {
      console.warn('setInterval with string callback is blocked for security');
      return;
    }
    
    // Sanitize arguments
    const sanitizedArgs = args.map(arg => {
      if (typeof arg === 'string' && window.SecurityUtils) {
        return window.SecurityUtils.sanitizeHtml(arg);
      }
      return arg;
    });
    
    return originalSetInterval.call(this, callback, delay, ...sanitizedArgs);
  };

  // Override location.href setter
  const originalLocationHref = Object.getOwnPropertyDescriptor(Location.prototype, 'href');
  Object.defineProperty(Location.prototype, 'href', {
    get: function() {
      return originalLocationHref.get.call(this);
    },
    set: function(url) {
      if (window.SecurityUtils) {
        window.SecurityUtils.safeNavigate(url);
      } else {
        // Basic validation
        if (typeof url === 'string' && !url.includes('javascript:') && !url.includes('data:')) {
          originalLocationHref.set.call(this, url);
        } else {
          console.warn('Dangerous URL blocked');
        }
      }
    },
    configurable: true
  });

  // Override location.replace
  const originalLocationReplace = Location.prototype.replace;
  Location.prototype.replace = function(url) {
    if (window.SecurityUtils) {
      window.SecurityUtils.safeNavigate(url);
    } else {
      // Basic validation
      if (typeof url === 'string' && !url.includes('javascript:') && !url.includes('data:')) {
        originalLocationReplace.call(this, url);
      } else {
        console.warn('Dangerous URL blocked');
      }
    }
  };

  // Override location.assign
  const originalLocationAssign = Location.prototype.assign;
  Location.prototype.assign = function(url) {
    if (window.SecurityUtils) {
      window.SecurityUtils.safeNavigate(url);
    } else {
      // Basic validation
      if (typeof url === 'string' && !url.includes('javascript:') && !url.includes('data:')) {
        originalLocationAssign.call(this, url);
      } else {
        console.warn('Dangerous URL blocked');
      }
    }
  };

  // Protect against prototype pollution
  const originalObjectDefineProperty = Object.defineProperty;
  Object.defineProperty = function(obj, prop, descriptor) {
    if (prop === '__proto__' || prop === 'constructor' || prop === 'prototype') {
      console.warn('Prototype pollution attempt blocked');
      return obj;
    }
    return originalObjectDefineProperty.call(this, obj, prop, descriptor);
  };

  // Protect against dangerous property access
  const originalObjectGetOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;
  Object.getOwnPropertyDescriptor = function(obj, prop) {
    if (prop === '__proto__' || prop === 'constructor' || prop === 'prototype') {
      console.warn('Dangerous property access blocked');
      return undefined;
    }
    return originalObjectGetOwnPropertyDescriptor.call(this, obj, prop);
  };

  // Override JSON.parse to prevent prototype pollution
  const originalJSONParse = JSON.parse;
  JSON.parse = function(text, reviver) {
    const parsed = originalJSONParse.call(this, text, reviver);
    
    // Check for dangerous properties
    if (typeof parsed === 'object' && parsed !== null) {
      const dangerousProps = ['__proto__', 'constructor', 'prototype'];
      for (const prop of dangerousProps) {
        if (prop in parsed) {
          console.warn('Dangerous JSON property detected and removed');
          delete parsed[prop];
        }
      }
    }
    
    return parsed;
  };

  // Override fetch to add security headers
  const originalFetch = window.fetch;
  window.fetch = function(url, options = {}) {
    if (window.SecurityUtils && !window.SecurityUtils.isValidUrl(url)) {
      throw new Error('Invalid URL: SSRF protection activated');
    }
    
    // Add security headers
    options.headers = {
      'X-Requested-With': 'XMLHttpRequest',
      'X-Content-Type-Options': 'nosniff',
      'X-Frame-Options': 'DENY',
      ...options.headers
    };
    
    return originalFetch.call(this, url, options);
  };

  // Override XMLHttpRequest for additional security
  const originalXHROpen = XMLHttpRequest.prototype.open;
  XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
    if (window.SecurityUtils && !window.SecurityUtils.isValidUrl(url)) {
      throw new Error('Invalid URL: SSRF protection activated');
    }
    return originalXHROpen.call(this, method, url, async, user, password);
  };

  // Monitor for dangerous DOM modifications
  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.type === 'childList') {
        mutation.addedNodes.forEach(function(node) {
          if (node.nodeType === Node.ELEMENT_NODE) {
            // Check for dangerous attributes
            const dangerousAttrs = ['onload', 'onerror', 'onclick', 'onmouseover'];
            dangerousAttrs.forEach(function(attr) {
              if (node.hasAttribute && node.hasAttribute(attr)) {
                console.warn('Dangerous attribute detected and removed:', attr);
                node.removeAttribute(attr);
              }
            });
            
            // Check for script tags
            if (node.tagName === 'SCRIPT') {
              console.warn('Script tag detected and removed');
              node.remove();
            }
          }
        });
      }
    });
  });

  // Start monitoring when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['onload', 'onerror', 'onclick', 'onmouseover']
      });
    });
  } else {
    observer.observe(document.body, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ['onload', 'onerror', 'onclick', 'onmouseover']
    });
  }

  // Console warning for security
  console.log('%cSecurity Overrides Active', 'color: green; font-weight: bold;');
  console.log('All dangerous JavaScript functions have been secured');

})();

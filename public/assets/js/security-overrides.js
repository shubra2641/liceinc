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

  // Override innerHTML setter to use SecurityUtils with enhanced protection
  Object.defineProperty(Element.prototype, 'innerHTML', {
    get: function() {
      return originalFunctions.innerHTML.get.call(this);
    },
    set: function(value) {
      if (window.SecurityUtils) {
        // Always sanitize and use textContent for maximum security
        window.SecurityUtils.safeInnerHTML(this, value, true, false);
      } else {
        // Enhanced fallback sanitization
        const sanitized = String(value)
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
        // Use textContent instead of innerHTML for maximum security
        this.textContent = sanitized;
      }
    },
    configurable: true
  });

  // Override outerHTML setter with enhanced security
  Object.defineProperty(Element.prototype, 'outerHTML', {
    get: function() {
      return originalFunctions.outerHTML.get.call(this);
    },
    set: function(value) {
      if (window.SecurityUtils) {
        // For outerHTML, we need to be more careful - always use textContent
        const sanitized = window.SecurityUtils.sanitizeHtml(value);
        this.textContent = sanitized;
      } else {
        // Enhanced fallback sanitization
        const sanitized = String(value)
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
        // Use textContent instead of outerHTML for maximum security
        this.textContent = sanitized;
      }
    },
    configurable: true
  });

  // Override insertAdjacentHTML with enhanced security
  Element.prototype.insertAdjacentHTML = function(position, html) {
    if (window.SecurityUtils) {
      window.SecurityUtils.safeInsertAdjacentHTML(this, position, html, false);
    } else {
      // Enhanced fallback sanitization
      const sanitized = String(html)
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
      // Use textContent instead of insertAdjacentHTML for maximum security
      const textNode = document.createTextNode(sanitized);
      this.insertAdjacentElement(position, textNode);
    }
  };

  // Override document.write with enhanced security
  document.write = function(content) {
    console.warn('document.write() is blocked for security reasons');
    if (window.SecurityUtils) {
      const sanitized = window.SecurityUtils.sanitizeHtml(content);
      // Use textContent instead for maximum security
      if (document.body) {
        document.body.textContent = sanitized;
      }
    } else {
      // Enhanced fallback sanitization
      const sanitized = String(content)
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

  // Override Function constructor with enhanced security
  const OriginalFunction = window.Function;
  window.Function = function(...args) {
    console.warn('Function constructor is blocked for security reasons');
    throw new Error('Function constructor is disabled for security');
  };

  // Override window.location to prevent dangerous redirects
  const originalLocation = window.location;
  Object.defineProperty(window, 'location', {
    get: function() {
      return originalLocation;
    },
    set: function(url) {
      if (window.SecurityUtils && window.SecurityUtils.isValidUrl(url)) {
        originalLocation.href = url;
      } else {
        console.warn('Dangerous URL blocked:', url);
        throw new Error('Invalid URL: Security protection activated');
      }
    },
    configurable: true
  });

  // Override window.open to prevent malicious popups
  const originalOpen = window.open;
  window.open = function(url, name, specs) {
    if (window.SecurityUtils && window.SecurityUtils.isValidUrl(url)) {
      return originalOpen.call(this, url, name, specs);
    } else {
      console.warn('Dangerous URL blocked in window.open:', url);
      return null;
    }
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

  // Enhanced protection against prototype pollution
  const originalObjectDefineProperty = Object.defineProperty;
  Object.defineProperty = function(obj, prop, descriptor) {
    // Enhanced check for dangerous property names
    const dangerousProps = [
      '__proto__', 'constructor', 'prototype',
      'valueOf', 'toString', 'hasOwnProperty',
      'isPrototypeOf', 'propertyIsEnumerable'
    ];
    
    if (dangerousProps.includes(prop) || prop.includes('__') || prop.includes('prototype')) {
      console.warn('Prototype pollution attempt blocked:', prop);
      return obj;
    }
    
    // Check descriptor for dangerous properties
    if (descriptor && typeof descriptor === 'object') {
      for (const key in descriptor) {
        if (dangerousProps.includes(key) || key.includes('__') || key.includes('prototype')) {
          console.warn('Dangerous descriptor property blocked:', key);
          return obj;
        }
      }
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
    
    // Enhanced check for dangerous properties
    if (typeof parsed === 'object' && parsed !== null) {
      const dangerousProps = [
        '__proto__', 'constructor', 'prototype',
        'valueOf', 'toString', 'hasOwnProperty',
        'isPrototypeOf', 'propertyIsEnumerable'
      ];
      
      for (const prop of dangerousProps) {
        if (prop in parsed) {
          console.warn('Dangerous JSON property detected and removed:', prop);
          delete parsed[prop];
        }
      }
      
      // Check for prototype pollution patterns
      for (const key in parsed) {
        if (key.includes('__') || key.includes('prototype')) {
          console.warn('Prototype pollution pattern detected and removed:', key);
          delete parsed[key];
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

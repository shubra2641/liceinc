/**
 * Common Utilities Library
 * Shared functions to reduce code duplication across the application
 */

// Global common utilities
const CommonUtils = {
  /**
   * Set button loading state with spinner
   * @param {HTMLElement} button - The button element
   * @param {string} loadingText - Text to show while loading
   * @param {string} originalText - Original text to restore
   */
  setButtonLoading(button, loadingText = 'Loading...', originalText = null) {
    if (!button) return;
    
    // Store original text if not provided
    if (!originalText) {
      originalText = button.textContent || button.innerHTML;
    }
    
    // Set loading state
    button.disabled = true;
    
    // Use SecurityUtils for safe HTML insertion
    if (window.SecurityUtils) {
      window.SecurityUtils.safeInnerHTML(button, `<i class="fas fa-spinner fa-spin me-2"></i>${loadingText}`, true, true);
    } else {
      button.textContent = loadingText;
    }
    
    // Return restore function
    return () => {
      button.disabled = false;
      if (window.SecurityUtils) {
        window.SecurityUtils.safeInnerHTML(button, originalText, true, true);
      } else {
        button.textContent = originalText;
      }
    };
  },

  /**
   * Show alert message with consistent styling
   * @param {string} type - Alert type (success, error, warning, info)
   * @param {string} message - Alert message
   * @param {number} duration - Auto-hide duration in ms (0 = no auto-hide)
   */
  showAlert(type, message, duration = 5000) {
    const alertContainer = document.getElementById('alert-container') || this.createAlertContainer();
    
    const alertId = `alert-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    const alertHtml = `
      <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
        <i class="fas fa-${this.getAlertIcon(type)} me-2"></i>
        <span class="alert-message">${this.escapeHtml(message)}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    `;
    
    // Use SecurityUtils for safe HTML insertion
    if (window.SecurityUtils) {
      window.SecurityUtils.safeInsertAdjacentHTML(alertContainer, 'beforeend', alertHtml, true);
    } else {
      alertContainer.insertAdjacentHTML('beforeend', alertHtml);
    }
    
    // Auto-hide if duration is specified
    if (duration > 0) {
      setTimeout(() => {
        const alertElement = document.getElementById(alertId);
        if (alertElement) {
          alertElement.remove();
        }
      }, duration);
    }
  },

  /**
   * Get icon class for alert type
   * @param {string} type - Alert type
   * @returns {string} - Icon class
   */
  getAlertIcon(type) {
    const icons = {
      success: 'check-circle',
      error: 'exclamation-triangle',
      warning: 'exclamation-circle',
      info: 'info-circle'
    };
    return icons[type] || 'info-circle';
  },

  /**
   * Create alert container if it doesn't exist
   * @returns {HTMLElement} - Alert container element
   */
  createAlertContainer() {
    let container = document.getElementById('alert-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'alert-container';
      container.className = 'position-fixed top-0 end-0 p-3';
      container.style.zIndex = '9999';
      document.body.appendChild(container);
    }
    return container;
  },

  /**
   * Escape HTML to prevent XSS
   * @param {string} text - Text to escape
   * @returns {string} - Escaped text
   */
  escapeHtml(text) {
    if (typeof text !== 'string') return '';
    
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  },

  /**
   * Make secure fetch request with common headers
   * @param {string} url - Request URL
   * @param {object} options - Fetch options
   * @returns {Promise} - Fetch promise
   */
  secureFetch(url, options = {}) {
    const defaultOptions = {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    };

    // Add CSRF token if available
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
      defaultOptions.headers['X-CSRF-TOKEN'] = csrfToken;
    }

    // Merge options
    const mergedOptions = {
      ...defaultOptions,
      ...options,
      headers: {
        ...defaultOptions.headers,
        ...options.headers
      }
    };

    // Use SecurityUtils if available
    if (window.SecurityUtils) {
      return window.SecurityUtils.safeFetch(url, mergedOptions);
    } else {
      return fetch(url, mergedOptions);
    }
  },

  /**
   * Handle fetch response with error handling
   * @param {Response} response - Fetch response
   * @returns {Promise} - Parsed response
   */
  handleFetchResponse(response) {
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
  },

  /**
   * Show loading spinner in element
   * @param {HTMLElement} element - Element to show spinner in
   * @param {string} text - Loading text
   */
  showLoading(element, text = 'Loading...') {
    if (!element) return;
    
    if (window.SecurityUtils) {
      window.SecurityUtils.safeInnerHTML(element, `<i class="fas fa-spinner fa-spin me-2"></i>${text}`, true, true);
    } else {
      element.textContent = text;
    }
  },

  /**
   * Hide loading spinner and restore content
   * @param {HTMLElement} element - Element to restore
   * @param {string} content - Content to restore
   */
  hideLoading(element, content = '') {
    if (!element) return;
    
    if (window.SecurityUtils) {
      window.SecurityUtils.safeInnerHTML(element, content, true, true);
    } else {
      element.textContent = content;
    }
  },

  /**
   * Validate required fields
   * @param {object} fields - Object with field names and values
   * @returns {object} - Validation result
   */
  validateRequiredFields(fields) {
    const missing = [];
    const values = {};
    
    for (const [name, value] of Object.entries(fields)) {
      if (!value || (typeof value === 'string' && value.trim() === '')) {
        missing.push(name);
      } else {
        values[name] = value;
      }
    }
    
    return {
      isValid: missing.length === 0,
      missing,
      values
    };
  },

  /**
   * Debounce function calls
   * @param {Function} func - Function to debounce
   * @param {number} wait - Wait time in ms
   * @returns {Function} - Debounced function
   */
  debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }
};

// Make it available globally
if (typeof window !== 'undefined') {
  window.CommonUtils = CommonUtils;
}

/**
 * Installation Wizard JavaScript - Simplified
 */

document.addEventListener('DOMContentLoaded', function() {
  // Utility functions
  const Utils = {
    get: (selector) => document.querySelector(selector),
    getAll: (selector) => document.querySelectorAll(selector),
    addClass: (el, cls) => el && el.classList.add(cls),
    removeClass: (el, cls) => el && el.classList.remove(cls),
    toggleClass: (el, cls) => el && el.classList.toggle(cls),
    setStyle: (el, prop, val) => el && (el.style[prop] = val),
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
    }
  };

  // Form validation
  function setupFormValidation() {
    const forms = Utils.getAll('.install-form');
    forms.forEach(form => {
      form.addEventListener('submit', handleFormSubmission);
      
      // Real-time validation
      const inputs = form.querySelectorAll('input, select, textarea');
      inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', Utils.debounce(validateField, 300));
      });
    });
  }

  function handleFormSubmission(event) {
    const form = event.target;
    if (!validateForm(form)) {
      event.preventDefault();
      showNotification('Please fix the errors before continuing.', 'error');
    }
  }

  function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
      if (!validateField({ target: input })) {
        isValid = false;
      }
    });
    
    return isValid;
  }

  function validateField(event) {
    const field = event.target;
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';

    // Required field validation
    if (field.hasAttribute('required') && !value) {
      isValid = false;
      errorMessage = 'This field is required';
    }

    // Email validation
    if (field.type === 'email' && value && !isValidEmail(value)) {
      isValid = false;
      errorMessage = 'Please enter a valid email address';
    }

    // Password validation
    if (field.type === 'password' && value && value.length < 8) {
      isValid = false;
      errorMessage = 'Password must be at least 8 characters long';
    }

    // Port validation
    if (field.name === 'port' && value && (isNaN(value) || value < 1 || value > 65535)) {
      isValid = false;
      errorMessage = 'Port must be between 1 and 65535';
    }

    updateFieldState(field, isValid, errorMessage);
    return isValid;
  }

  function updateFieldState(field, isValid, errorMessage) {
    const formGroup = field.closest('.form-group');
    const existingError = formGroup?.querySelector('.form-error');

    if (existingError) {
      existingError.remove();
    }

    Utils.removeClass(field, 'error success');
    if (isValid) {
      Utils.addClass(field, 'success');
    } else {
      Utils.addClass(field, 'error');
      if (errorMessage && formGroup) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        errorDiv.textContent = errorMessage;
        formGroup.appendChild(errorDiv);
      }
    }
  }

  // Database connection test
  async function testDatabaseConnection() {
    const form = Utils.get('#database-form');
    if (!form) return;

    const testButton = Utils.get('#test-connection-btn');
    setButtonLoading(testButton, true);
    hideConnectionResult();

    try {
      const formData = new FormData(form);
      const response = await fetch('./test-database', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      const result = await response.json();
      showConnectionResult(result.success, result.message);
    } catch (error) {
      showConnectionResult(false, `Connection test failed: ${error.message}`);
    } finally {
      setButtonLoading(testButton, false);
    }
  }

  function showConnectionResult(success, message) {
    const resultDiv = Utils.get('#connection-result');
    if (!resultDiv) return;

    Utils.removeClass(resultDiv, 'success error');
    Utils.addClass(resultDiv, success ? 'success' : 'error');
    resultDiv.textContent = message;
    Utils.setStyle(resultDiv, 'display', 'flex');
  }

  function hideConnectionResult() {
    const resultDiv = Utils.get('#connection-result');
    if (resultDiv) {
      Utils.setStyle(resultDiv, 'display', 'none');
    }
  }

  // Installation process
  async function startInstallation() {
    const button = Utils.get('#start-installation-btn');
    if (!button) return;

    setButtonLoading(button, true);

    try {
      const response = await fetch('./process', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': Utils.get('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({})
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const result = await response.json();
      if (result.success) {
        showNotification('Installation completed successfully! Redirecting...', 'success');
        setTimeout(() => {
          window.location.href = result.redirect || '/login?from_install=1';
        }, 1000);
      } else {
        showNotification(`Installation failed: ${result.message}`, 'error');
        setButtonLoading(button, false);
      }
    } catch (error) {
      showNotification(`Installation error: ${error.message}`, 'error');
      setButtonLoading(button, false);
    }
  }

  // Button loading state
  function setButtonLoading(button, loading) {
    if (!button) return;

    if (loading) {
      Utils.addClass(button, 'loading');
      button.disabled = true;
      const originalText = button.textContent;
      button.dataset.originalText = originalText;
      button.textContent = 'Testing...';
    } else {
      Utils.removeClass(button, 'loading');
      button.disabled = false;
      if (button.dataset.originalText) {
        button.textContent = button.dataset.originalText;
        delete button.dataset.originalText;
      }
    }
  }

  // Notifications
  function showNotification(message, type = 'info') {
    const existingNotifications = Utils.getAll('.install-notification');
    existingNotifications.forEach(notification => notification.remove());

    const notification = document.createElement('div');
    notification.className = `install-notification install-alert-${type}`;
    
    const icon = getIconForType(type);
    notification.innerHTML = `
      <i class="fas ${icon}"></i>
      <span>${message}</span>
      <button class="notification-close" onclick="this.parentElement.remove()">
        <i class="fas fa-times"></i>
      </button>
    `;

    const container = Utils.get('.install-container') || document.body;
    container.appendChild(notification);
  }

  function getIconForType(type) {
    const icons = {
      success: 'fa-check-circle',
      error: 'fa-times-circle',
      warning: 'fa-exclamation-triangle',
      info: 'fa-info-circle'
    };
    return icons[type] || icons.info;
  }

  // Language switcher
  function setupLanguageSwitcher() {
    const languageSelect = Utils.get('#language-select');
    if (languageSelect) {
      languageSelect.addEventListener('change', function() {
        updateUrlParam('lang', this.value);
      });
    }
  }

  function updateUrlParam(param, value) {
    const url = new URL(window.location);
    url.searchParams.set(param, value);
    window.history.replaceState({}, '', url);
    window.location.reload();
  }

  // Keyboard navigation
  function setupKeyboardNavigation() {
    document.addEventListener('keydown', event => {
      if (event.key === 'Enter' && event.target.matches('input, select, textarea')) {
        const form = event.target.closest('form');
        if (form && validateForm(form)) {
          const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
          if (submitButton) {
            submitButton.click();
          }
        }
      }

      if (event.key === 'Escape') {
        const notifications = Utils.getAll('.install-notification');
        notifications.forEach(notification => notification.remove());
      }
    });
  }

  // Auto-save functionality
  function setupAutoSave() {
    const forms = Utils.getAll('.install-form');
    forms.forEach(form => {
      const inputs = form.querySelectorAll('input, select, textarea');
      inputs.forEach(input => {
        input.addEventListener('input', Utils.debounce(() => {
          saveFormData(form);
        }, 1000));
      });
    });
  }

  function saveFormData(form) {
    const formData = new FormData(form);
    const data = {};
    for (const [key, value] of formData.entries()) {
      data[key] = value;
    }
    localStorage.setItem('install_form_data', JSON.stringify(data));
  }

  // Utility functions
  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  // Event listeners
  function setupEventListeners() {
    const testConnectionBtn = Utils.get('#test-connection-btn');
    if (testConnectionBtn) {
      testConnectionBtn.addEventListener('click', testDatabaseConnection);
    }

    const startInstallationBtn = Utils.get('#start-installation-btn');
    if (startInstallationBtn) {
      startInstallationBtn.addEventListener('click', startInstallation);
    }

    window.addEventListener('beforeunload', function(event) {
      const isInstalling = Utils.get('.installation-step.current');
      if (isInstalling) {
        event.preventDefault();
        event.returnValue = 'Installation is in progress. Are you sure you want to leave?';
      }
    });

    window.addEventListener('resize', Utils.debounce(function() {
      const isMobile = window.innerWidth < 768;
      Utils.toggleClass(document.body, 'mobile', isMobile);
    }, 250));
  }

  // Initialize everything
  function initializeInstallation() {
    setupFormValidation();
    setupLanguageSwitcher();
    setupKeyboardNavigation();
    setupAutoSave();
    setupEventListeners();
  }

  // Initialize
  initializeInstallation();

  // Expose functions globally
  window.InstallWizard = {
    testDatabaseConnection,
    startInstallation,
    showNotification,
    validateForm,
    validateField
  };
});
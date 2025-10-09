/**
 * License Verification JavaScript
 * Handles license verification form interactions
 */

document.addEventListener('DOMContentLoaded', () => {
  // Get form elements
  const form = document.getElementById('licenseForm');
  const verifyBtn = document.getElementById('verifyBtn');
  const continueBtn = document.getElementById('continueBtn');
  const successMessage = document.getElementById('licenseSuccess');
  const purchaseCodeInput = document.getElementById('purchase_code');

  if (!form || !verifyBtn || !purchaseCodeInput) {
    console.error('License verification form elements not found');
    return;
  }

  // Allow any format - no automatic formatting
  purchaseCodeInput.addEventListener('input', e => {
    // Just trim whitespace, no other formatting
    e.target.value = e.target.value.trim();
  });

  // Handle form submission with AJAX
  form.addEventListener('submit', e => {
    e.preventDefault();

    const purchaseCode = purchaseCodeInput.value.trim();
    if (!purchaseCode) {
      showError('Please enter a purchase code');
      return;
    }

    // Validate purchase code format
    if (!isValidPurchaseCodeFormat(purchaseCode)) {
      showError('Please enter a valid purchase code (5-100 characters).');
      return;
    }

    // Show loading state
    setLoadingState(true);

    // Hide previous messages
    hideMessages();

    // Send AJAX request
    sendVerificationRequest(purchaseCode);
  });

  // Auto-focus on purchase code input
  purchaseCodeInput.focus();

  /**
   * Validate purchase code format
   */
  function isValidPurchaseCodeFormat(code) {
    // Accept any format - just check basic requirements
    return code && code.trim().length >= 5 && code.trim().length <= 100;
  }

  /**
   * Send verification request to server
   */
  function sendVerificationRequest(purchaseCode) {
    const csrfToken = document
      .querySelector('meta[name="csrf-token"]')
      ?.getAttribute('content');

    if (!csrfToken) {
      showError(
        'Security token not found. Please refresh the page and try again.',
      );
      setLoadingState(false);
      return;
    }

    fetch(form.action, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: new URLSearchParams({
        purchase_code: purchaseCode,
      }),
    })
      .then(response => {
        if (
          response.headers.get('content-type')?.includes('application/json')
        ) {
          return response.json();
        }
        return response.text();
      })
      .then(data => {
        if (typeof data === 'object') {
          // JSON response
          handleJsonResponse(data);
        } else {
          // HTML response (fallback)
          handleHtmlResponse(data);
        }
        return undefined;
      })
      .catch(error => {
        console.error('License verification error:', error);
        showError('Network error. Please check your connection and try again.');
        setLoadingState(false);
      });
  }

  /**
   * Handle JSON response
   */
  function handleJsonResponse(data) {
    if (data.success) {
      showSuccess();
      showContinueButton();
      // Optionally redirect after a delay
      setTimeout(() => {
        if (data.redirect) {
          window.SecurityUtils.safeNavigate(data.redirect);
        }
      }, 2000);
    } else {
      showError(data.message || 'License verification failed');
      setLoadingState(false);
    }
  }

  /**
   * Handle HTML response (fallback)
   */
  function handleHtmlResponse(html) {
    const parser = new DOMParser();
        // Sanitize HTML before parsing
        const sanitizedHtml = html.replace(/<script[^>]*>[\s\S]*?<\/script>/gi, '')
          .replace(/<iframe[^>]*>[\s\S]*?<\/iframe>/gi, '')
          .replace(/on\w+\s*=\s*["'][^"']*["']/gi, '');
        const doc = parser.parseFromString(sanitizedHtml, 'text/html');

    // Check for errors
    const errors = doc.querySelectorAll(
      '.license-error, .form-error, .alert-danger',
    );
    if (errors.length > 0) {
      const errorMessage = errors[0].textContent.trim();
      showError(errorMessage);
      setLoadingState(false);
      return;
    }

    // Check for success
    if (
      html.includes('License verified successfully') ||
      html.includes('install/requirements')
    ) {
      showSuccess();
      showContinueButton();
    } else {
      showError('License verification failed. Please try again.');
      setLoadingState(false);
    }
  }

  /**
   * Show success message
   */
  function showSuccess() {
    if (successMessage) {
      successMessage.classList.add('show');
    }
    verifyBtn.style.display = 'none';
  }

  /**
   * Show continue button
   */
  function showContinueButton() {
    if (continueBtn) {
      continueBtn.classList.add('show');
    }
  }

  /**
   * Show error message
   */
  function showError(message) {
    // Sanitize and try to extract inner JSON "message" if the error string embeds a JSON payload
    if (typeof message === 'string') {
      // Pattern: Network error: ... {"valid":false,"message":"License is suspended","error_code":"LICENSE_SUSPENDED"}
      const jsonMatch = message.match(/(\{\s*"valid"[^}]+\})/);
      if (jsonMatch) {
        try {
          const parsed = JSON.parse(jsonMatch[1]);
          if (parsed && typeof parsed.message === 'string') {
            ({ message } = parsed);
          }
        } catch (e) {
          // Ignore JSON parse errors; fallback to original message
        }
      }
      // If message still starts with a technical prefix, trim it
      if (/^Network error:/i.test(message)) {
        // Remove leading technical phrase keeping the user-facing part after JSON if any
        const parts = message.split(':');
        if (parts.length > 2) {
          message = parts.slice(2).join(':').trim();
        }
      }
    }
    // Remove existing error messages
    const existingErrors = document.querySelectorAll('.license-error');
    existingErrors.forEach(error => error.remove());

    // Create new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'license-error';
    // Sanitize message to prevent XSS
    // Message will be sanitized by SecurityUtils
    // Use SecurityUtils for safe HTML insertion
    if (typeof SecurityUtils !== 'undefined') {
      SecurityUtils.safeInnerHTML(errorDiv, 
        `<i class="fas fa-exclamation-circle"></i> ${message}`, 
        true, 
        true
      );
    } else {
      // Fallback: use textContent for security
      errorDiv.textContent = message;
    }

    // Insert after the input field
    const inputGroup = purchaseCodeInput.closest('.license-form-group');
    if (inputGroup) {
      inputGroup.appendChild(errorDiv);
    }

    // Add error class to input
    purchaseCodeInput.classList.add('is-invalid');

    // Remove error class after user starts typing
    purchaseCodeInput.addEventListener(
      'input',
      () => {
        purchaseCodeInput.classList.remove('is-invalid');
      },
      { once: true },
    );
  }

  /**
   * Hide all messages
   */
  function hideMessages() {
    if (successMessage) {
      successMessage.classList.remove('show');
    }
    if (continueBtn) {
      continueBtn.classList.remove('show');
    }
    const existingErrors = document.querySelectorAll('.license-error');
    existingErrors.forEach(error => error.remove());
    purchaseCodeInput.classList.remove('is-invalid');
  }

  /**
   * Set loading state
   */
  function setLoadingState(loading) {
    if (loading) {
      verifyBtn.disabled = true;
      verifyBtn.classList.add('loading');
      // Use SecurityUtils for safe HTML insertion
      if (typeof SecurityUtils !== 'undefined' && SecurityUtils.safeInnerHTML) {
        SecurityUtils.safeInnerHTML(verifyBtn, '<i class="fas fa-spinner fa-spin"></i> <span>Verifying...</span>', true, true);
      } else {
        verifyBtn.textContent = 'Verifying...';
      }
    } else {
      verifyBtn.disabled = false;
      verifyBtn.classList.remove('loading');
      // Use SecurityUtils for safe HTML insertion
      if (typeof SecurityUtils !== 'undefined' && SecurityUtils.safeInnerHTML) {
        SecurityUtils.safeInnerHTML(verifyBtn, '<i class="fas fa-check"></i> <span>Verify License</span>', true, true);
      } else {
        verifyBtn.textContent = 'Verify License';
      }
    }
  }

  /**
   * Handle continue button click
   */
  if (continueBtn) {
    continueBtn.addEventListener('click', function(e) {
      // Add a small delay for better UX
      e.preventDefault();
      this.style.opacity = '0.7';
      setTimeout(() => {
                window.location.href = SecurityUtils.escapeUrl(this.href);
      }, 150);
    });
  }

  /**
   * Handle keyboard shortcuts
   */
  document.addEventListener('keydown', e => {
    // Enter key on input field
    if (e.key === 'Enter' && e.target === purchaseCodeInput) {
      e.preventDefault();
      form.dispatchEvent(new Event('submit'));
    }

    // Escape key to clear errors
    if (e.key === 'Escape') {
      hideMessages();
    }
  });

  /**
   * Handle input validation on blur
   */
  purchaseCodeInput.addEventListener('blur', function() {
    const value = this.value.trim();
    if (value && !isValidPurchaseCodeFormat(value)) {
      showError('Please enter a valid purchase code (5-100 characters).');
    }
  });

  /**
   * Handle input validation on focus
   */
  purchaseCodeInput.addEventListener('focus', function() {
    this.classList.remove('is-invalid');
    const existingErrors = document.querySelectorAll('.license-error');
    existingErrors.forEach(error => error.remove());
  });

  // Add accessibility improvements
  purchaseCodeInput.setAttribute('aria-describedby', 'purchase-code-hint');
  purchaseCodeInput.setAttribute('aria-invalid', 'false');

  // Update aria-invalid when validation state changes
  const observer = new MutationObserver(mutations => {
    mutations.forEach(mutation => {
      if (
        mutation.type === 'attributes' &&
        mutation.attributeName === 'class'
      ) {
        const hasError = purchaseCodeInput.classList.contains('is-invalid');
        purchaseCodeInput.setAttribute('aria-invalid', hasError.toString());
      }
    });
  });

  observer.observe(purchaseCodeInput, { attributes: true });
});

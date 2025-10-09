// Admin Actions JavaScript
// Utility function for showing notifications
/* eslint-disable no-unused-vars, promise/always-return, promise/catch-or-return, no-new, no-useless-escape, n/handle-callback-err */
const showNotification = (message, type = 'info') => {
  const notification = document.createElement('div');
  notification.className = `user-notification user-notification-${type} show`;
  // Use SecurityUtils for safe HTML insertion
  if (typeof SecurityUtils !== 'undefined') {
    SecurityUtils.safeInnerHTML(notification, `
        <div class="user-notification-content">
            <div class="user-notification-icon">
                <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : type === 'warning' ? 'exclamation' : 'info'}-circle"></i>
            </div>
            <div class="user-notification-message">${message}</div>
            <button class="user-notification-close" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `, true, true);
  } else {
    // Fallback: create elements safely
    const content = document.createElement('div');
    content.className = 'user-notification-content';
    
    const icon = document.createElement('div');
    icon.className = 'user-notification-icon';
    icon.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : type === 'warning' ? 'exclamation' : 'info'}-circle"></i>`;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = 'user-notification-message';
    messageDiv.textContent = message;
    
    const closeBtn = document.createElement('button');
    closeBtn.className = 'user-notification-close';
    closeBtn.innerHTML = '<i class="fas fa-times"></i>';
    closeBtn.onclick = () => notification.remove();
    
    content.appendChild(icon);
    content.appendChild(messageDiv);
    content.appendChild(closeBtn);
    notification.appendChild(content);
  }

  document.body.appendChild(notification);
  setTimeout(() => notification.remove(), 5000);
};

document.addEventListener('DOMContentLoaded', () => {
  // Handle maintenance page reload
  document.querySelectorAll('[data-action="reload"]').forEach(button => {
    button.addEventListener('click', () => {
      location.reload();
    });
  });

  // Handle product actions
  document.querySelectorAll('[data-action="purchase"]').forEach(button => {
    button.addEventListener('click', () => {
      purchaseProduct();
    });
  });

  document.querySelectorAll('[data-action="download"]').forEach(button => {
    button.addEventListener('click', () => {
      downloadProduct();
    });
  });

  document.querySelectorAll('[data-action="wishlist"]').forEach(button => {
    button.addEventListener('click', () => {
      addToWishlist();
    });
  });

  // Handle payment methods
  document.querySelectorAll('[data-payment]').forEach(button => {
    button.addEventListener('click', function() {
      const paymentMethod = this.getAttribute('data-payment');
      processPayment(paymentMethod);
    });
  });

  // Handle print action
  document.querySelectorAll('[data-action="print"]').forEach(button => {
    button.addEventListener('click', () => {
      window.print();
    });
  });

  // Handle copy to clipboard
  document.querySelectorAll('[data-copy-target]').forEach(button => {
    button.addEventListener('click', function() {
      const targetId = this.getAttribute('data-copy-target');
      copyToClipboard(targetId);
    });
  });

  // Handle generate preview
  document
    .querySelectorAll('[data-action="generate-preview"]')
    .forEach(button => {
      button.addEventListener('click', () => {
        generateLicenseKeyPreview();
      });
    });

  // Handle form confirmations
  document.querySelectorAll('[data-confirm]').forEach(form => {
    form.addEventListener('submit', function(e) {
      const confirmType = this.getAttribute('data-confirm');
      if (!confirmDelete(confirmType)) {
        e.preventDefault();
      }
    });
  });

  // Handle tab navigation
  document.querySelectorAll('[data-action="show-tab"]').forEach(button => {
    button.addEventListener('click', function() {
      const tabId = this.getAttribute('data-tab');
      showTab(tabId);
    });
  });

  // Handle tab navigation by data-tab attribute
  document.querySelectorAll('[data-tab]').forEach(button => {
    button.addEventListener('click', function() {
      const tabId = this.getAttribute('data-tab');
      showTab(tabId);
    });
  });
});

function purchaseProduct() {
  // Implementation for purchasing product
  showNotification(
    'Product purchase functionality will be implemented here',
    'info',
  );
}

function downloadProduct() {
  // Implementation for downloading product
  showNotification(
    'Product download functionality will be implemented here',
    'info',
  );
}

function addToWishlist() {
  // Implementation for adding to wishlist
  showNotification(
    'Add to wishlist functionality will be implemented here',
    'info',
  );
}

function processPayment(method) {
  // Implementation for processing payment
  showNotification(
    `Payment processing with ${method} will be implemented here`,
    'info',
  );
}

function copyToClipboard(elementId) {
  const element = document.getElementById(elementId);
  if (element) {
    navigator.clipboard
      .writeText(element.textContent)
      .then(() => {
        showNotification('Copied to clipboard successfully!', 'success');
      })
      .catch(err => {
        showNotification(`Could not copy text: ${err.message}`, 'error');
      });
  } else {
    showNotification('Element not found!', 'error');
  }
}

function generateLicenseKeyPreview() {
  // Implementation for generating license key preview
  showNotification(
    'License key preview generation will be implemented here',
    'info',
  );
}

function confirmDelete(type) {
  const messages = {
    'delete-license': 'Are you sure you want to delete this license?',
    'delete-product': 'Are you sure you want to delete this product?',
    'delete-category': 'Are you sure you want to delete this category?',
    'delete-category-articles':
      'Are you sure you want to delete this category and all its articles?',
    'delete-article': 'Are you sure you want to delete this article?',
    'delete-invoice': 'Are you sure you want to delete this invoice?',
    'delete-template': 'Are you sure you want to delete this template?',
    'delete-user': 'Are you sure you want to delete this user?',
  };

  return window.confirm && confirm(messages[type] || 'Are you sure?');
}

function showTab(tabId) {
  // Hide all tab panels
  document.querySelectorAll('.admin-tab-panel').forEach(panel => {
    panel.classList.add('admin-tab-panel-hidden');
    panel.setAttribute('aria-hidden', 'true');
  });

  // Remove active class from all tab buttons
  document.querySelectorAll('.admin-tab-btn').forEach(btn => {
    btn.classList.remove('admin-tab-btn-active');
    btn.setAttribute('aria-selected', 'false');
    btn.setAttribute('tabindex', '-1');
  });

  // Show the selected tab panel
  const targetPanel = document.getElementById(tabId);
  if (targetPanel) {
    targetPanel.classList.remove('admin-tab-panel-hidden');
    targetPanel.setAttribute('aria-hidden', 'false');
  }

  // Activate the clicked tab button
  const activeButton = document.querySelector(`[data-tab="${tabId}"]`);
  if (activeButton) {
    activeButton.classList.add('admin-tab-btn-active');
    activeButton.setAttribute('aria-selected', 'true');
    activeButton.setAttribute('tabindex', '0');
  }
}
// Layout JavaScript
document.addEventListener('DOMContentLoaded', () => {
  // Handle logout
  document.querySelectorAll('[data-action="logout"]').forEach(button => {
    button.addEventListener('click', e => {
      e.preventDefault();
      logout();
    });
  });

  // Handle clear cache
  document.querySelectorAll('[data-action="clear-cache"]').forEach(button => {
    button.addEventListener('click', e => {
      e.preventDefault();
      clearCache();
    });
  });
});

function logout() {
  document.getElementById('logout-form').submit();
}

function clearCache() {
  // Implementation for clearing cache
}
/**
 * Optimized Frontend Preloader System
 * High-performance preloader with fast hiding and smooth animations
 * Envato-compliant JavaScript with validation and best practices
 */

class FrontendPreloaderManager {
  constructor() {
    this.container = document.getElementById('preloader-container');
    this.settings = this.getSettings();
    this.isVisible = true;
    this.init();
  }

  getSettings() {
    if (!this.container) {
      return {};
    }

    return {
      enabled: this.container.dataset.enabled === '1',
      type: this.container.dataset.type || 'spinner',
      color: this.container.dataset.color || '#3b82f6',
      backgroundColor: this.container.dataset.bg || '#ffffff',
      duration: parseInt(this.container.dataset.duration) || 2000,
      text: this.container.dataset.text || 'Loading...',
    };
  }

  init() {
    if (!this.container || !this.settings.enabled) {
      return;
    }

    this.setupDynamicStyles();
    this.setupEventListeners();
    this.startPreloader();
  }

  setupDynamicStyles() {
    if (!this.settings.color || !this.settings.backgroundColor) {
      return;
    }

    const style = document.createElement('style');
    style.textContent = `
            :root {
                --preloader-color: ${this.settings.color};
                --preloader-bg: ${this.settings.backgroundColor};
                --preloader-text-color: ${this.settings.color};
            }
            
            @media (prefers-color-scheme: dark) {
                :root {
                    --preloader-bg-dark: ${this.settings.backgroundColor === '#ffffff' ? '#1f2937' : this.settings.backgroundColor};
                    --preloader-text-color-dark: ${this.settings.color === '#3b82f6' ? '#d1d5db' : this.settings.color};
                }
            }
        `;
    document.head.appendChild(style);
  }

  setupEventListeners() {
    // Hide preloader when DOM is ready (fastest)
    document.addEventListener('DOMContentLoaded', () => {
      setTimeout(() => {
        this.hidePreloader();
      }, 300); // Very short delay
    });

    // Hide preloader when page is fully loaded (fallback)
    window.addEventListener('load', () => {
      this.hidePreloader();
    });

    // Hide on first user interaction (click, keypress, scroll)
    const hideOnInteraction = () => {
      if (this.isVisible) {
        this.hidePreloader();
      }
    };

    document.addEventListener('click', hideOnInteraction, { once: true });
    document.addEventListener('keydown', hideOnInteraction, { once: true });
    document.addEventListener('scroll', hideOnInteraction, { once: true });

    // Fallback: Hide preloader after maximum duration
    if (this.settings.duration) {
      setTimeout(() => {
        this.hidePreloader();
      }, this.settings.duration);
    }

    // Additional fallback: Hide after 1 second maximum
    setTimeout(() => {
      this.hidePreloader();
    }, 1000);

    // Handle page visibility changes
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        this.pauseAnimations();
      } else {
        this.resumeAnimations();
      }
    });
  }

  startPreloader() {
    if (!this.container) {
      return;
    }

    // Add entrance animation
    this.container.style.opacity = '1';
    this.container.style.visibility = 'visible';

    // Start progress animation if it's a progress type
    if (this.settings.type === 'progress') {
      this.animateProgress();
    }

    // Add loading state to body
    document.body.classList.add('preloader-active');
  }

  hidePreloader() {
    if (!this.container || !this.isVisible) {
      return;
    }

    this.isVisible = false;

    // Force hide immediately with CSS
    this.container.style.opacity = '0';
    this.container.style.visibility = 'hidden';
    this.container.style.display = 'none';
    this.container.classList.add('hidden');

    // Remove loading state from body
    document.body.classList.remove('preloader-active');

    // Remove from DOM immediately
    if (this.container && this.container.parentNode) {
      this.container.parentNode.removeChild(this.container);
    }
  }

  animateProgress() {
    const progressBar = this.container.querySelector('.preloader-progress-bar');
    if (!progressBar) {
      return;
    }

    let progress = 0;
    const interval = setInterval(() => {
      // Use secure random for better security
      if (typeof SecurityUtils !== 'undefined' && SecurityUtils.secureRandom) {
        progress += SecurityUtils.secureRandom(20);
      } else {
        // Fallback: use crypto.getRandomValues if available, otherwise Math.random
        if (typeof crypto !== 'undefined' && crypto.getRandomValues) {
          const array = new Uint32Array(1);
          crypto.getRandomValues(array);
          progress += (array[0] / 4294967296) * 20;
        } else {
          progress += Math.random() * 20;
        }
      }
      if (progress >= 100) {
        progress = 100;
        clearInterval(interval);
      }
      progressBar.style.width = `${progress}%`;
    }, 50); // Faster updates
  }

  pauseAnimations() {
    const animations = this.container.querySelectorAll('[style*="animation"]');
    animations.forEach(el => {
      el.style.animationPlayState = 'paused';
    });
  }

  resumeAnimations() {
    const animations = this.container.querySelectorAll('[style*="animation"]');
    animations.forEach(el => {
      el.style.animationPlayState = 'running';
    });
  }

  // Public method to manually hide preloader
  static hide() {
    const preloader = document.getElementById('preloader-container');
    if (preloader) {
      preloader.style.opacity = '0';
      preloader.style.visibility = 'hidden';
      preloader.style.display = 'none';
      preloader.classList.add('hidden');
      document.body.classList.remove('preloader-active');

      // Remove immediately
      if (preloader.parentNode) {
        preloader.parentNode.removeChild(preloader);
      }
    }
  }

  // Public method to show preloader
  static show() {
    const preloader = document.getElementById('preloader-container');
    if (preloader) {
      preloader.classList.remove('hidden');
      preloader.style.opacity = '1';
      preloader.style.visibility = 'visible';
      document.body.classList.add('preloader-active');
    }
  }
}

// Initialize preloader when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  new FrontendPreloaderManager();
});

// Additional fallback: Force hide preloader after 800ms maximum
setTimeout(() => {
  const preloader = document.getElementById('preloader-container');
  if (preloader) {
    preloader.style.display = 'none';
    preloader.remove();
    document.body.classList.remove('preloader-active');
  }
}, 800);

// Export for global access
window.FrontendPreloaderManager = FrontendPreloaderManager;
/**
 * Product Show Page JavaScript
 * Envato-compliant external JavaScript file
 */

class ProductShowManager {
  constructor() {
    this.init();
  }

  init() {
    this.setupGalleryModal();
    this.setupPurchaseButtons();
    this.setupDownloadButtons();
    this.setupWishlistButtons();
  }

  setupGalleryModal() {
    const galleryModal = document.getElementById('galleryModal');
    const galleryModalImage = document.getElementById('galleryModalImage');

    if (galleryModal && galleryModalImage) {
      galleryModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const imageSrc = button.getAttribute('data-image');
        const imageAlt = button.getAttribute('alt');

        galleryModalImage.src = imageSrc;
        galleryModalImage.alt = imageAlt;
      });
    }
  }

  setupPurchaseButtons() {
    const purchaseButtons = document.querySelectorAll(
      '[data-action="purchase"]',
    );
    purchaseButtons.forEach(button => {
      button.addEventListener('click', e => {
        e.preventDefault();
        this.handlePurchase();
      });
    });
  }

  setupDownloadButtons() {
    const downloadButtons = document.querySelectorAll(
      '[data-action="download"]',
    );
    downloadButtons.forEach(button => {
      button.addEventListener('click', e => {
        e.preventDefault();
        this.handleDownload();
      });
    });
  }

  setupWishlistButtons() {
    const wishlistButtons = document.querySelectorAll(
      '[data-action="wishlist"]',
    );
    wishlistButtons.forEach(button => {
      button.addEventListener('click', e => {
        e.preventDefault();
        this.handleWishlist();
      });
    });
  }

  handlePurchase() {
    // Show notification
    this.showNotification('Purchase functionality will be implemented', 'info');
  }

  handleDownload() {
    // Show notification
    this.showNotification('Download functionality will be implemented', 'info');
  }

  handleWishlist() {
    // Show notification
    this.showNotification('Wishlist functionality will be implemented', 'info');
  }

  showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    // Sanitize message to prevent XSS
    window.SecurityUtils.safeInnerHTML(
      notification,
      `
            <div class="d-flex align-items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                <span>${message}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `,
    );

    // Insert at the top of the page
    const container =
      document.querySelector('.user-dashboard-container') || document.body;
    container.insertBefore(notification, container.firstChild);

    // Auto remove after 5 seconds
    setTimeout(() => {
      if (notification.parentElement) {
        notification.remove();
      }
    }, 5000);
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  new ProductShowManager();
});

// Export for global access
window.ProductShowManager = ProductShowManager;
/* ===== USER DASHBOARD OPTIMIZED JS ===== */
/* Optimized and compressed version - Removed duplicates and unused code */

(function() {
  'use strict';

  // ===== UTILITY FUNCTIONS =====
  const $ = (selector, context = document) => context.querySelector(selector);
  const $$ = (selector, context = document) =>
    context.querySelectorAll(selector);

  const setButtonLoading = (button, isLoading) => {
    const text = button.querySelector('.button-text, .user-btn-text');
    const spinner = button.querySelector('.button-loading, .user-btn-spinner');

    if (isLoading) {
      button.disabled = true;
      if (text) {
        text.style.opacity = '0';
      }
      if (spinner) {
        spinner.style.display = 'inline-block';
      }
    } else {
      button.disabled = false;
      if (text) {
        text.style.opacity = '1';
      }
      if (spinner) {
        spinner.style.display = 'none';
      }
    }
  };

  const validateInput = input => {
    const value = input.value.trim();
    const { type } = input;
    const required = input.hasAttribute('required');

    if (required && !value) {
      return 'This field is required';
    }

    if (
      type === 'email' &&
      value &&
      !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
    ) {
      return 'Please enter a valid email address';
    }

    if (type === 'password' && value && value.length < 6) {
      return 'Password must be at least 6 characters';
    }

    return null;
  };

  const showInputError = (input, message) => {
    const inputGroup = input.closest('.form-field-group, .form-group');
    if (!inputGroup) {
      return;
    }

    const existingError = inputGroup.querySelector(
      '.form-error, .user-form-error',
    );
    if (existingError) {
      existingError.remove();
    }

    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    // Sanitize message to prevent XSS
    window.SecurityUtils.safeInnerHTML(
      errorDiv,
      `<i class="fas fa-exclamation-circle"></i> ${message}`,
    );

    inputGroup.appendChild(errorDiv);
    input.classList.add('form-input-error');
  };

  const clearInputError = input => {
    const inputGroup = input.closest('.form-field-group, .form-group');
    if (!inputGroup) {
      return;
    }

    const existingError = inputGroup.querySelector(
      '.form-error, .user-form-error',
    );
    if (existingError) {
      existingError.remove();
    }

    input.classList.remove('form-input-error');
  };

  // ===== INITIALIZATION FUNCTIONS =====
  const initializeDashboard = () => {
    initializeTables();
    initializeForms();
    initializeTabs();
    initializeCopyButtons();
    initializeFilters();
    initializeMobileMenu();
    initializeProfileTabs();
    initializeLicenseStatus();
    initializeHashScrolling();
    initializeTableOfContents();
    initializeArticleFeatures();
  };

  const initializeAuth = () => {
    initializePasswordToggles();
    initializeFormValidation();
    initializeFormLoading();
    initializeFormAnimations();
  };

  const initializePasswordToggles = () => {
    const toggles = $$('[data-password-toggle]');
    toggles.forEach(toggle => {
      const input = $(toggle.dataset.passwordToggle);
      const showIcon = $(toggle.dataset.showIcon);
      const hideIcon = $(toggle.dataset.hideIcon);

      if (input && showIcon && hideIcon) {
        toggle.addEventListener('click', () =>
          togglePasswordVisibility(input, showIcon, hideIcon),
        );
      }
    });
  };

  const togglePasswordVisibility = (input, showIcon, hideIcon) => {
    if (input.type === 'password') {
      input.type = 'text';
      showIcon.style.display = 'none';
      hideIcon.style.display = 'inline';
    } else {
      input.type = 'password';
      showIcon.style.display = 'inline';
      hideIcon.style.display = 'none';
    }
  };

  const initializeFormValidation = () => {
    const forms = $$('.user-form, .register-form, .login-form');
    forms.forEach(form => {
      const inputs = $$('input, textarea, select', form);
      inputs.forEach(input => {
        input.addEventListener('blur', () => {
          const error = validateInput(input);
          if (error) {
            showInputError(input, error);
          } else {
            clearInputError(input);
          }
        });

        input.addEventListener('input', () => {
          if (input.classList.contains('form-input-error')) {
            const error = validateInput(input);
            if (!error) {
              clearInputError(input);
            }
          }
        });
      });

      form.addEventListener('submit', e => {
        const inputs = $$(
          'input[required], textarea[required], select[required]',
          form,
        );
        let isValid = true;

        inputs.forEach(input => {
          const error = validateInput(input);
          if (error) {
            showInputError(input, error);
            isValid = false;
          }
        });

        if (!isValid) {
          e.preventDefault();
        }
      });
    });
  };

  const initializeFormLoading = () => {
    const forms = $$('.user-form, .register-form, .login-form');
    forms.forEach(form => {
      form.addEventListener('submit', () => {
        const submitBtn = form.querySelector(
          'button[type="submit"], input[type="submit"]',
        );
        if (submitBtn) {
          setButtonLoading(submitBtn, true);
        }
      });
    });
  };

  const initializeFormAnimations = () => {
    const cards = $$('.user-card, .user-stat-card, .user-action-card');
    cards.forEach((card, index) => {
      card.style.animationDelay = `${index * 0.1}s`;
    });
  };

  const initializeTables = () => {
    const containers = $$('.table-container');
    containers.forEach(addScrollIndicator);
  };

  const addScrollIndicator = container => {
    const table = $('.user-table', container);
    if (!table) {
      return;
    }

    const checkScrollable = () => {
      const isScrollable = table.scrollWidth > container.clientWidth;
      container.classList.toggle('scrollable', isScrollable);
    };

    checkScrollable();
    window.addEventListener('resize', checkScrollable);
  };

  const initializeForms = () => {
    const forms = $$('form');
    forms.forEach(addFormValidation);
  };

  const addFormValidation = form => {
    const inputs = $$('input, textarea, select', form);
    inputs.forEach(input => {
      input.addEventListener('blur', () => {
        const error = validateInput(input);
        if (error) {
          showInputError(input, error);
        } else {
          clearInputError(input);
        }
      });
    });
  };

  const initializeTabs = () => {
    const tabButtons = $$('.tab-button');
    tabButtons.forEach(button => {
      button.addEventListener('click', () => {
        const tabId = button.dataset.tab;
        if (tabId) {
          showTab(tabId);
        }
      });
    });
  };

  const showTab = tabId => {
    const tabs = $$('.tab-content');
    const buttons = $$('.tab-button');

    tabs.forEach(tab => tab.classList.remove('active'));
    buttons.forEach(btn => btn.classList.remove('active'));

    const activeTab = $(`#${tabId}`);
    const activeButton = $(`[data-tab="${tabId}"]`);

    if (activeTab) {
      activeTab.classList.add('active');
    }
    if (activeButton) {
      activeButton.classList.add('active');
    }
  };

  const initializeCopyButtons = () => {
    const copyButtons = $$('.copy-btn, [data-copy]');
    copyButtons.forEach(button => {
      button.addEventListener('click', e => {
        e.preventDefault();
        const text = button.dataset.copy || button.textContent.trim();
        copyToClipboard(text, button);
      });
    });
  };

  const copyToClipboard = (text, button) => {
    if (navigator.clipboard) {
      navigator.clipboard
        .writeText(text)
        .then(() => {
          showCopySuccess(button);
        })
        .catch(() => {
          fallbackCopyTextToClipboard(text, button);
        });
    } else {
      fallbackCopyTextToClipboard(text, button);
    }
  };

  const fallbackCopyTextToClipboard = (text, button) => {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
      document.execCommand('copy');
      showCopySuccess(button);
    } catch (err) {
      showCopyError(button);
    }

    document.body.removeChild(textArea);
  };

  const showCopySuccess = button => {
    const originalText = button.innerHTML;
    // Use SecurityUtils for safe HTML insertion
    if (typeof SecurityUtils !== 'undefined') {
      SecurityUtils.safeInnerHTML(button, '<i class="fas fa-check"></i> Copied!', true, true);
    } else {
      button.textContent = 'Copied!';
    }
    button.style.background = '#10b981';

    setTimeout(() => {
      if (typeof SecurityUtils !== 'undefined') {
        SecurityUtils.safeInnerHTML(button, originalText, true, true);
      } else {
        button.textContent = originalText;
      }
      button.style.background = '';
    }, 2000);
  };

  const showCopyError = button => {
    const originalText = button.innerHTML;
    // Use SecurityUtils for safe HTML insertion
    if (typeof SecurityUtils !== 'undefined') {
      SecurityUtils.safeInnerHTML(button, '<i class="fas fa-times"></i> Failed', true, true);
    } else {
      button.textContent = 'Failed';
    }
    button.style.background = '#ef4444';

    setTimeout(() => {
      if (typeof SecurityUtils !== 'undefined') {
        SecurityUtils.safeInnerHTML(button, originalText, true, true);
      } else {
        button.textContent = originalText;
      }
      button.style.background = '';
    }, 2000);
  };

  const initializeFilters = () => {
    initializeLicenseFilters();
    initializeInvoiceFilters();
    initializeTicketFilters();
  };

  const initializeLicenseFilters = () => {
    const statusFilter = $('#licenseStatusFilter');
    const searchInput = $('#licenseSearchInput');

    if (statusFilter && searchInput) {
      const filterFunction = () =>
        filterLicenseTable(statusFilter, searchInput);
      statusFilter.addEventListener('change', filterFunction);
      searchInput.addEventListener('input', filterFunction);
    }
  };

  const filterLicenseTable = (statusFilter, searchInput) => {
    const table = $('.user-table');
    if (!table) {
      return;
    }

    const rows = $$('tbody tr', table);
    const statusValue = statusFilter.value.toLowerCase();
    const searchValue = searchInput.value.toLowerCase();

    rows.forEach(row => {
      const status =
        row.querySelector('.license-status-badge')?.textContent.toLowerCase() ||
        '';
      const text = row.textContent.toLowerCase();

      const statusMatch = !statusValue || status.includes(statusValue);
      const searchMatch = !searchValue || text.includes(searchValue);

      row.style.display = statusMatch && searchMatch ? '' : 'none';
    });
  };

  const initializeInvoiceFilters = () => {
    const statusFilter = $('#invoiceStatusFilter');
    const searchInput = $('#invoiceSearchInput');

    if (statusFilter && searchInput) {
      const filterFunction = () =>
        filterInvoiceTable(statusFilter, searchInput);
      statusFilter.addEventListener('change', filterFunction);
      searchInput.addEventListener('input', filterFunction);
    }
  };

  const filterInvoiceTable = (statusFilter, searchInput) => {
    const table = $('.user-table');
    if (!table) {
      return;
    }

    const rows = $$('tbody tr', table);
    const statusValue = statusFilter.value.toLowerCase();
    const searchValue = searchInput.value.toLowerCase();

    rows.forEach(row => {
      const status =
        row.querySelector('.invoice-status-badge')?.textContent.toLowerCase() ||
        '';
      const text = row.textContent.toLowerCase();

      const statusMatch = !statusValue || status.includes(statusValue);
      const searchMatch = !searchValue || text.includes(searchValue);

      row.style.display = statusMatch && searchMatch ? '' : 'none';
    });
  };

  const initializeTicketFilters = () => {
    const statusFilter = $('#ticketStatusFilter');
    const priorityFilter = $('#ticketPriorityFilter');
    const searchInput = $('#ticketSearchInput');

    if (statusFilter && priorityFilter && searchInput) {
      const filterFunction = () =>
        filterTicketTable(statusFilter, priorityFilter, searchInput);
      statusFilter.addEventListener('change', filterFunction);
      priorityFilter.addEventListener('change', filterFunction);
      searchInput.addEventListener('input', filterFunction);
    }
  };

  const filterTicketTable = (statusFilter, priorityFilter, searchInput) => {
    const table = $('.user-table');
    if (!table) {
      return;
    }

    const rows = $$('tbody tr', table);
    const statusValue = statusFilter.value.toLowerCase();
    const priorityValue = priorityFilter.value.toLowerCase();
    const searchValue = searchInput.value.toLowerCase();

    rows.forEach(row => {
      const status =
        row.querySelector('.ticket-status-badge')?.textContent.toLowerCase() ||
        '';
      const priority =
        row
          .querySelector('.ticket-priority-badge')
          ?.textContent.toLowerCase() || '';
      const text = row.textContent.toLowerCase();

      const statusMatch = !statusValue || status.includes(statusValue);
      const priorityMatch = !priorityValue || priority.includes(priorityValue);
      const searchMatch = !searchValue || text.includes(searchValue);

      row.style.display =
        statusMatch && priorityMatch && searchMatch ? '' : 'none';
    });
  };

  const initializeTicketForm = () => {
    const form = $('#ticketForm');
    if (!form) {
      return;
    }

    const submitBtn = $('button[type="submit"]', form);
    if (submitBtn) {
      form.addEventListener('submit', () => {
        setButtonLoading(submitBtn, true);
      });
    }
  };

  const initializeHashScrolling = () => {
    const { hash } = window.location;
    if (hash) {
      const element = $(hash);
      if (element) {
        setTimeout(() => element.scrollIntoView({ behavior: 'smooth' }), 100);
      }
    }
  };

  const generateTableOfContents = () => {
    const content = $('.article-content, .user-article-content');
    if (!content) {
      return;
    }

    const headings = $$('h1, h2, h3, h4, h5, h6', content);
    if (headings.length === 0) {
      return;
    }

    const toc = $('.article-toc-content, .user-toc');
    if (!toc) {
      return;
    }

    const tocList = document.createElement('ul');
    tocList.className = 'toc-list';

    headings.forEach((heading, index) => {
      const id = heading.id || `heading-${index}`;
      heading.id = id;

      const li = document.createElement('li');
      li.className = `toc-item ${heading.tagName.toLowerCase()}`;

      const a = document.createElement('a');
      a.href = `#${id}`;
      a.textContent = heading.textContent;
      a.addEventListener('click', e => {
        e.preventDefault();
        heading.scrollIntoView({ behavior: 'smooth' });
      });

      li.appendChild(a);
      tocList.appendChild(li);
    });

    toc.appendChild(tocList);
  };

  const addScrollSpy = () => {
    const tocItems = $$('.toc-item a');
    if (tocItems.length === 0) {
      return;
    }

    const removeActiveClass = () => {
      tocItems.forEach(item => item.classList.remove('active'));
    };

    const addActiveClass = () => {
      const scrollPos = window.scrollY + 100;
      const headings = $$('h1, h2, h3, h4, h5, h6');

      headings.forEach((heading, index) => {
        const nextHeading = headings[index + 1];
        const headingTop = heading.offsetTop;
        const headingBottom = nextHeading ?
          nextHeading.offsetTop :
          headingTop + heading.offsetHeight;

        if (scrollPos >= headingTop && scrollPos < headingBottom) {
          removeActiveClass();
          const tocItem = $(`.toc-item a[href="#${heading.id}"]`);
          if (tocItem) {
            tocItem.classList.add('active');
          }
        }
      });
    };

    const updateActiveSection = () => {
      requestAnimationFrame(addActiveClass);
    };

    window.addEventListener('scroll', updateActiveSection);
    updateActiveSection();
  };

  const handlePrintFunctionality = () => {
    const printBtn = $('.print-btn, [data-print]');
    if (printBtn) {
      printBtn.addEventListener('click', () => {
        window.print();
      });
    }
  };

  const handleShareFunctionality = () => {
    const shareBtn = $('.share-btn, [data-share]');
    if (shareBtn) {
      shareBtn.addEventListener('click', () => {
        if (navigator.share) {
          navigator.share({
            title: document.title,
            url: window.location.href,
          });
        } else {
          navigator.clipboard.writeText(window.location.href).then(() => {
            showNotification('Link copied to clipboard!', 'success');
          });
        }
      });
    }
  };

  const initializeTableOfContents = () => {
    generateTableOfContents();
    addScrollSpy();
  };

  const initializeArticleFeatures = () => {
    handlePrintFunctionality();
    handleShareFunctionality();
  };

  const initializeLicenseStatus = () => {
    const form = $('#licenseCheckForm');
    if (!form) {
      return;
    }

    const submitBtn = $('button[type="submit"]', form);
    const formCard = $('#licenseCheckFormCard');
    const loadingSpinner = $('#loadingSpinner');
    const licenseDetails = $('#licenseDetails');
    const errorMessage = $('#errorMessage');

    const showLicenseForm = () => {
      hideAllStates();
      if (formCard) {
        formCard.classList.remove('hidden');
      }
    };

    const showLoadingState = () => {
      hideAllStates();
      if (loadingSpinner) {
        loadingSpinner.classList.add('show');
      }
      if (submitBtn) {
        setButtonLoading(submitBtn, true);
      }
    };

    const showLicenseDetails = data => {
      hideAllStates();
      if (licenseDetails) {
        populateLicenseDetails(data);
        licenseDetails.classList.add('show');
      }
    };

    const showErrorMessage = message => {
      hideAllStates();
      if (errorMessage) {
        errorMessage.textContent = message;
        errorMessage.classList.add('show');
      }
    };

    const hideAllStates = () => {
      [formCard, loadingSpinner, licenseDetails, errorMessage].forEach(el => {
        if (el) {
          el.classList.add('hidden');
          el.classList.remove('show');
        }
      });
      if (submitBtn) {
        setButtonLoading(submitBtn, false);
      }
    };

    const populateLicenseDetails = data => {
      const elements = {
        licenseKey: data.license_key,
        licenseStatus: data.status,
        licenseType: data.type,
        productName: data.product_name,
        purchaseDate: data.purchase_date,
        expiryDate: data.expiry_date,
        domainLimit: data.domain_limit,
        purchaseCode: data.purchase_code,
        itemId: data.item_id,
        buyerEmail: data.buyer_email,
      };

      Object.entries(elements).forEach(([id, value]) => {
        const element = $(`#${id}`);
        if (element) {
          element.textContent = value;
        }
      });

      if (data.domains && data.domains.length > 0) {
        updateDomainsList(data.domains);
      }

      if (data.envato_data) {
        updateEnvatoStatus(data.envato_data);
      }
    };

    const updateStatCard = (elementId, value) => {
      const element = $(elementId);
      if (element) {
        element.textContent = value;
      }
    };

    const updateElement = (elementId, value) => {
      const element = $(elementId);
      if (element) {
        element.textContent = value;
      }
    };

    const updateDomainsList = domains => {
      const domainsList = $('.domains-list');
      if (!domainsList) {
        return;
      }

      // Sanitize domains to prevent XSS
      const sanitizedDomains = domains.map(domain => ({
        ...domain,
        domain: domain.domain.replace(/[<>&"']/g, match => ({
          '<': '&lt;',
          '>': '&gt;',
          '&': '&amp;',
          '"': '&quot;',
          '\'': '&#x27;',
        }[match])),
      }));
      // Use SecurityUtils for safe HTML insertion
      const domainsHtml = sanitizedDomains
        .map(
          domain => `
                <div class="domain-item">
                    <div class="domain-info">
                        <div class="domain-name">
                            <i class="fas fa-globe"></i>
                            ${domain.domain}
                        </div>
                        <div class="domain-meta">
                            <div class="domain-date">
                                <i class="fas fa-calendar"></i>
                                ${domain.registered_at}
                            </div>
                            <div class="domain-status">
                                <span class="status-dot ${domain.status}"></span>
                                <span class="status-text">${domain.status}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `,
        )
        .join('');
      
      if (typeof SecurityUtils !== 'undefined') {
        SecurityUtils.safeInnerHTML(domainsList, domainsHtml, true, true);
      } else {
        domainsList.textContent = ''; // Clear and add text content for security
        domainsList.textContent = 'Domains loaded';
      }
    };

    const updateEnvatoStatus = envatoData => {
      const envatoSection = $('.envato-data-section');
      const envatoNaSection = $('.envato-na-section');

      if (envatoData && Object.keys(envatoData).length > 0) {
        if (envatoSection) {
          envatoSection.classList.remove('hide');
          envatoSection.classList.add('show');
        }
        if (envatoNaSection) {
          envatoNaSection.classList.add('hide');
          envatoNaSection.classList.remove('show');
        }

        const elements = {
          envatoUsername: envatoData.username,
          envatoSales: envatoData.sales,
          envatoFollowers: envatoData.followers,
          envatoRating: envatoData.rating,
        };

        Object.entries(elements).forEach(([id, value]) => {
          const element = $(`#${id}`);
          if (element) {
            element.textContent = value;
          }
        });
      } else {
        if (envatoSection) {
          envatoSection.classList.add('hide');
          envatoSection.classList.remove('show');
        }
        if (envatoNaSection) {
          envatoNaSection.classList.remove('hide');
          envatoNaSection.classList.add('show');
        }
      }
    };

    const showHistoryModal = () => {
      const modal = $('.license-history-modal');
      if (modal) {
        modal.style.display = 'block';
        document.body.classList.add('modal-open');
        loadLicenseHistory();
      }
    };

    const hideHistoryModal = () => {
      const modal = $('.license-history-modal');
      if (modal) {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
      }
    };

    const loadLicenseHistory = () => {
      // Simulate loading license history
      const historyContent = $('.user-history-content');
      if (historyContent) {
        // Use SecurityUtils for safe HTML insertion
        if (typeof SecurityUtils !== 'undefined') {
          SecurityUtils.safeInnerHTML(historyContent, 
            '<div class="text-center p-4">Loading history...</div>', 
            true, 
            true
          );
        } else {
          historyContent.textContent = 'Loading history...';
        }

        // Simulate API call
        setTimeout(() => {
          const mockHistory = [
            {
              type: 'activation',
              title: 'License Activated',
              description: 'License was activated on example.com',
              date: '2024-01-15 10:30:00',
              ip: '192.168.1.1',
            },
            {
              type: 'deactivation',
              title: 'License Deactivated',
              description: 'License was deactivated from example.com',
              date: '2024-01-14 15:45:00',
              ip: '192.168.1.1',
            },
          ];

          // Sanitize mock history data to prevent XSS
          const sanitizedHistory = mockHistory.map(item => ({
            ...item,
            title: item.title.replace(/[<>&"']/g, match => ({
              '<': '&lt;',
              '>': '&gt;',
              '&': '&amp;',
              '"': '&quot;',
              '\'': '&#x27;',
            }[match])),
            description: item.description.replace(/[<>&"']/g, match => ({
              '<': '&lt;',
              '>': '&gt;',
              '&': '&amp;',
              '"': '&quot;',
              '\'': '&#x27;',
            }[match])),
            date: item.date.replace(/[<>&"']/g, match => ({
              '<': '&lt;',
              '>': '&gt;',
              '&': '&amp;',
              '"': '&quot;',
              '\'': '&#x27;',
            }[match])),
            ip: item.ip.replace(/[<>&"']/g, match => ({
              '<': '&lt;',
              '>': '&gt;',
              '&': '&amp;',
              '"': '&quot;',
              '\'': '&#x27;',
            }[match])),
          }));

          // Use SecurityUtils for safe HTML insertion
          const historyHtml = sanitizedHistory
            .map(
              item => `
                        <div class="history-item">
                            <div class="history-item-icon ${item.type}">
                                <i class="fas fa-${getHistoryIcon(item.type)}"></i>
                            </div>
                            <div class="history-item-content">
                                <div class="history-item-title">${item.title}</div>
                                <div class="history-item-description">${item.description}</div>
                                <div class="history-item-meta">
                                    <span class="history-item-time">${item.date}</span>
                                    <span class="history-item-ip">IP: ${item.ip}</span>
                                </div>
                            </div>
                        </div>
                    `,
            )
            .join('');
          
          if (typeof SecurityUtils !== 'undefined') {
            SecurityUtils.safeInnerHTML(historyContent, historyHtml, true, true);
          } else {
            historyContent.textContent = 'History loaded';
          }
        }, 1000);
      }
    };

    const populateHistorySummary = () => {
      const summaryStats = {
        totalActivations: '5',
        totalDeactivations: '2',
        activeDomains: '3',
      };

      Object.entries(summaryStats).forEach(([id, value]) => {
        const element = $(`#${id}`);
        if (element) {
          element.textContent = value;
        }
      });
    };

    const getHistoryIcon = type => {
      const icons = {
        activation: 'check-circle',
        deactivation: 'times-circle',
        suspension: 'exclamation-circle',
        renewal: 'refresh',
      };
      return icons[type] || 'info-circle';
    };

    const exportHistory = () => {
      const historyData = $('.user-history-content').textContent;
      const blob = new Blob([historyData], { type: 'text/plain' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'license-history.txt';
      a.click();
      URL.revokeObjectURL(url);
    };

    const copyToClipboard = elementId => {
      const element = $(elementId);
      if (!element) {
        return;
      }

      const text = element.textContent || element.value;
      if (navigator.clipboard) {
        navigator.clipboard
          .writeText(text)
          .then(() => {
            showCopySuccess(element);
          })
          .catch(() => {
            fallbackCopyTextToClipboard(text, element);
          });
      } else {
        fallbackCopyTextToClipboard(text, element);
      }
    };

    const fallbackCopyTextToClipboard = (text, element) => {
      const textArea = document.createElement('textarea');
      textArea.value = text;
      textArea.style.position = 'fixed';
      textArea.style.left = '-999999px';
      textArea.style.top = '-999999px';
      document.body.appendChild(textArea);
      textArea.focus();
      textArea.select();

      try {
        document.execCommand('copy');
        showCopySuccess(element);
      } catch (err) {
        showCopyError(element);
      }

      document.body.removeChild(textArea);
    };

    const showCopySuccess = element => {
      const originalText = element.textContent;
      element.textContent = 'Copied!';
      element.style.color = '#10b981';

      setTimeout(() => {
        element.textContent = originalText;
        element.style.color = '';
      }, 2000);
    };

    const showCopyError = element => {
      const originalText = element.textContent;
      element.textContent = 'Failed to copy';
      element.style.color = '#ef4444';

      setTimeout(() => {
        element.textContent = originalText;
        element.style.color = '';
      }, 2000);
    };

    const handleEnvatoStatus = licenseData => {
      if (licenseData.envato_data) {
        updateEnvatoStatus(licenseData.envato_data);
      } else {
        const envatoSection = $('.envato-data-section');
        const envatoNaSection = $('.envato-na-section');

        if (envatoSection) {
          envatoSection.classList.add('hide');
        }
        if (envatoNaSection) {
          envatoNaSection.classList.remove('hide');
        }
      }
    };

    const viewDomainHistory = domain => {
      showNotification(`Viewing history for ${domain}`, 'info');
    };

    const handleLicenseCheck = () => {
      if (!form) {
        return;
      }

      form.addEventListener('submit', async e => {
        e.preventDefault();
        showLoadingState();

        const formData = new FormData(form);
        const licenseKey = formData.get('license_key');

        if (!licenseKey) {
          showErrorMessage('Please enter a license key');
          return;
        }

        try {
          // Validate form action URL to prevent SSRF attacks
          const formAction = form.action;
          const allowedOrigins = [
            window.location.origin,
            `${window.location.protocol}//${window.location.host}`,
          ];

          // Check if the form action is from the same origin
          if (!allowedOrigins.some(origin => formAction.startsWith(origin))) {
            showErrorMessage('Invalid request URL');
            return;
          }

          // Use SecurityUtils for safe fetch
          const response = typeof SecurityUtils !== 'undefined' 
            ? await SecurityUtils.safeFetch(formAction, {
                method: 'POST',
                body: formData,
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                    .content,
                },
              })
            : await fetch(formAction, {
                method: 'POST',
                body: formData,
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                    .content,
                },
              });

          const data = await response.json();

          if (data.success) {
            showLicenseDetails(data.license);
            handleEnvatoStatus(data.license);
            populateHistorySummary();
          } else {
            showErrorMessage(data.message || 'License not found');
          }
        } catch (error) {
          showErrorMessage('An error occurred while checking the license');
        }
      });
    };

    const isValidEmail = email => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

    // Initialize license check functionality
    handleLicenseCheck();

    // Global functions for external access
    window.showLicenseHistory = showHistoryModal;
    window.hideLicenseHistory = hideHistoryModal;
    window.exportLicenseHistory = exportHistory;
    window.copyLicenseKey = () => copyToClipboard('#licenseKey');
    window.copyPurchaseCode = () => copyToClipboard('#purchaseCode');
    window.viewDomainHistory = viewDomainHistory;
  };

  const initializeMobileMenu = () => {
    const toggleBtn = $('[data-mobile-menu-toggle]');
    const menu = $('[data-mobile-menu]');
    const backdrop = $('.mobile-menu-backdrop');
    const closeBtn = $('.mobile-menu-close');

    if (!toggleBtn || !menu) {
      return;
    }

    const toggleMobileMenu = () => {
      const isOpen = menu.classList.contains('active');
      if (isOpen) {
        closeMobileMenu();
      } else {
        openMobileMenu();
      }
    };

    const openMobileMenu = () => {
      menu.classList.add('active');
      // Some CSS uses .show for visibility (legacy rules). Keep both in sync.
      menu.classList.add('show');
      // Remove any utility 'hidden' class so CSS display rules don't override visibility
      menu.classList.remove('hidden');
      // Mark the toggle button as active for styling consistency
      if (toggleBtn) {
        toggleBtn.classList.add('active');
      }
      if (backdrop) {
        backdrop.classList.add('active');
      }
      document.body.classList.add('mobile-menu-open');
      document.addEventListener('keydown', handleEscapeKey);
    };

    const closeMobileMenu = () => {
      menu.classList.remove('active');
      // Keep legacy .show class in sync when closing
      menu.classList.remove('show');
      // Re-apply the utility 'hidden' class to return to initial state
      menu.classList.add('hidden');
      if (toggleBtn) {
        toggleBtn.classList.remove('active');
      }
      if (backdrop) {
        backdrop.classList.remove('active');
      }
      document.body.classList.remove('mobile-menu-open');
      document.removeEventListener('keydown', handleEscapeKey);
    };

    const handleEscapeKey = e => {
      if (e.key === 'Escape') {
        closeMobileMenu();
      }
    };

    toggleBtn.addEventListener('click', toggleMobileMenu);
    if (closeBtn) {
      closeBtn.addEventListener('click', closeMobileMenu);
    }
    if (backdrop) {
      backdrop.addEventListener('click', closeMobileMenu);
    }

    // Close menu when clicking on nav links
    const navLinks = $$('.mobile-nav-link');
    navLinks.forEach(link => {
      link.addEventListener('click', closeMobileMenu);
    });
  };

  const initializeProfileTabs = () => {
    const tabButtons = $$('.tab-button');
    tabButtons.forEach(button => {
      button.addEventListener('click', () => {
        const tabId = button.dataset.tab;
        if (tabId) {
          showTab(tabId);
        }
      });
    });
  };

  // ===== INITIALIZATION =====
  document.addEventListener('DOMContentLoaded', () => {
    // Check if we're on dashboard or auth pages
    if ($('.user-dashboard-container')) {
      initializeDashboard();
    }

    if ($('.user-form, .register-form, .login-form')) {
      initializeAuth();
    }

    // Initialize ticket form if present
    initializeTicketForm();

    // Handle flash messages
    const flashSuccess = document.querySelector('meta[name="flash-success"]');
    const flashError = document.querySelector('meta[name="flash-error"]');
    const flashWarning = document.querySelector('meta[name="flash-warning"]');
    const flashInfo = document.querySelector('meta[name="flash-info"]');

    if (flashSuccess) {
      showNotification(flashSuccess.content, 'success');
    }
    if (flashError) {
      showNotification(flashError.content, 'error');
    }
    if (flashWarning) {
      showNotification(flashWarning.content, 'warning');
    }
    if (flashInfo) {
      showNotification(flashInfo.content, 'info');
    }
  });

  // ===== GLOBAL FUNCTIONS =====
  window.showNotification = showNotification;
  window.setButtonLoading = setButtonLoading;
  window.togglePasswordVisibility = togglePasswordVisibility;
  window.showTab = showTab;
  window.copyToClipboard = copyToClipboard;
})();
/**
 * User Tickets JavaScript
 * Handles ticket creation form functionality
 */

class UserTickets {
  constructor() {
    this.init();
  }

  init() {
    // Progressive enhancement - ensure form works without JavaScript
    if (typeof document === 'undefined') {
      return;
    }

    document.addEventListener('DOMContentLoaded', () => {
      this.setupTicketCreation();
    });
  }

  setupTicketCreation() {
    const categorySelect = document.getElementById('category_id');
    const purchaseCodeSection = document.getElementById(
      'purchase-code-section',
    );
    const productSlugSection = document.getElementById('product-slug-section');
    const purchaseCodeRequired = document.getElementById(
      'purchase-code-required',
    );
    const purchaseCodeInput = document.getElementById('purchase_code');
    const productSlugInput = document.getElementById('product_slug');
    const productNameDisplay = document.getElementById('product-name-display');
    const productNameSpan = document.getElementById('product-name');

    // Debug: Check if elements exist
    if (!productSlugInput) {
      return;
    }
    if (!productNameSpan) {
      return;
    }
    if (!productNameDisplay) {
      return;
    }

    // Handle category change
    if (categorySelect) {
      categorySelect.addEventListener('change', () => {
        const selectedOption =
          categorySelect.options[categorySelect.selectedIndex];
        const requiresPurchaseCode =
          selectedOption.dataset.requiresPurchaseCode === 'true';

        if (requiresPurchaseCode) {
          purchaseCodeSection.classList.remove('hidden');
          purchaseCodeRequired.style.display = 'inline';
          purchaseCodeInput.required = true;
          productSlugSection.classList.remove('hidden');
        } else {
          purchaseCodeSection.classList.add('hidden');
          purchaseCodeRequired.style.display = 'none';
          purchaseCodeInput.required = false;
          productSlugSection.classList.add('hidden');
          productNameDisplay.classList.add('hidden');
        }
      });
    }

    // Handle purchase code verification
    let verificationTimeout;
    if (purchaseCodeInput) {
      purchaseCodeInput.addEventListener('input', () => {
        clearTimeout(verificationTimeout);
        const purchaseCode = purchaseCodeInput.value.trim();

        // Reset visual feedback
        productSlugInput.style.borderColor = '';
        productSlugInput.placeholder = 'Product identifier from URL';

        if (purchaseCode.length < 10) {
          productNameDisplay.classList.add('hidden');
          productSlugInput.value = '';
          return;
        }

        verificationTimeout = setTimeout(() => {
          this.verifyPurchaseCode(purchaseCode);
        }, 1000);
      });
    }

    // Set browser info
    const browserInfo =
      `${navigator.userAgent
      } | ${
        navigator.language
      } | ${
        screen.width
      }x${
        screen.height}`;
    const browserInfoInput = document.getElementById('browser_info');
    if (browserInfoInput) {
      browserInfoInput.value = browserInfo;
    }

    // Initialize form state
    if (categorySelect && categorySelect.value) {
      categorySelect.dispatchEvent(new Event('change'));
    }
  }

  verifyPurchaseCode(purchaseCode) {
    // Validate purchase code format to prevent path traversal attacks
    if (!purchaseCode || typeof purchaseCode !== 'string') {
      return;
    }

    // Basic validation - purchase codes should be alphanumeric with some special chars
    const purchaseCodeRegex = /^[a-zA-Z0-9\-_\.]+$/;
    if (!purchaseCodeRegex.test(purchaseCode)) {
      console.error('Invalid purchase code format');
      return;
    }

    // Prevent path traversal attacks
    if (
      purchaseCode.includes('..') ||
      purchaseCode.includes('/') ||
      purchaseCode.includes('\\')
    ) {
      console.error('Invalid purchase code: path traversal detected');
      return;
    }

    const productSlugInput = document.getElementById('product_slug');
    const productNameSpan = document.getElementById('product-name');
    const productNameDisplay = document.getElementById('product-name-display');

    // Add visual feedback
    productSlugInput.style.borderColor = '#ffc107';
    productSlugInput.placeholder = 'Verifying purchase code...';

    window.SecurityUtils.safeFetch(
      `/verify-purchase-code/${encodeURIComponent(purchaseCode)}`,
    )
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        if (data.success && data.product) {
          // Set values
          productSlugInput.value = data.product.slug;
          productNameSpan.textContent = data.product.name;
          productNameDisplay.classList.remove('hidden');

          // Visual feedback
          productSlugInput.style.borderColor = '#28a745';
          productSlugInput.placeholder = 'Product slug filled automatically';

          // Force update the display
          productSlugInput.dispatchEvent(new Event('input'));
        } else {
          productSlugInput.value = '';
          productNameDisplay.classList.add('hidden');
          productSlugInput.style.borderColor = '#dc3545';
          productSlugInput.placeholder = 'Invalid purchase code';
        }
      })
      .catch(error => {
        productSlugInput.value = '';
        productNameDisplay.classList.add('hidden');
        productSlugInput.style.borderColor = '#dc3545';
        productSlugInput.placeholder = 'Error verifying purchase code';
      });
  }
}

// Initialize when DOM is ready
if (typeof document !== 'undefined') {
  new UserTickets();
}
/**
 * Maintenance Page JavaScript
 * Professional maintenance page functionality
 */

class MaintenanceManager {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
    this.initializeComponents();
  }

  bindEvents() {
    // Refresh page button
    document.addEventListener('click', e => {
      if (
        e.target.matches('[data-action="reload"]') ||
        e.target.closest('[data-action="reload"]')
      ) {
        this.handleRefresh();
      }
    });

    // Auto-refresh progress bar
    this.startProgressAnimation();
  }

  initializeComponents() {
    // Initialize maintenance components
    this.initProgressBar();
    this.initStatusUpdates();
  }

  /**
   * Handle page refresh
   */
  handleRefresh() {
    // Show loading state
    const refreshBtn = document.querySelector('[data-action="reload"]');
    if (refreshBtn) {
      const originalText = refreshBtn.innerHTML;
      // Use SecurityUtils for safe HTML insertion
      if (typeof SecurityUtils !== 'undefined') {
        SecurityUtils.safeInnerHTML(refreshBtn, 
          '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...', 
          true, 
          true
        );
      } else {
        refreshBtn.textContent = 'Refreshing...';
      }
      refreshBtn.disabled = true;

      // Reload page after short delay
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    }
  }

  /**
   * Initialize progress bar animation
   */
  initProgressBar() {
    const progressFill = document.querySelector('.progress-fill');
    if (progressFill) {
      // Animate progress bar on load
      setTimeout(() => {
        progressFill.style.width = '75%';
      }, 500);
    }
  }

  /**
   * Start progress animation
   */
  startProgressAnimation() {
    const progressFill = document.querySelector('.progress-fill');
    if (progressFill) {
      // Simulate progress updates
      let progress = 0;
      const interval = setInterval(() => {
        // Use secure random for better security
        if (typeof SecurityUtils !== 'undefined' && SecurityUtils.secureRandom) {
          progress += SecurityUtils.secureRandom(2);
        } else {
          progress += Math.random() * 2; // Fallback for older browsers
        }
        if (progress >= 75) {
          progress = 75;
          clearInterval(interval);
        }
        progressFill.style.width = `${progress}%`;
      }, 2000);
    }
  }

  /**
   * Initialize status updates
   */
  initStatusUpdates() {
    // Add real-time timestamp updates
    this.updateTimestamps();
    setInterval(() => {
      this.updateTimestamps();
    }, 60000); // Update every minute
  }

  /**
   * Update timestamps
   */
  updateTimestamps() {
    const lastUpdated = document.querySelector('.footer-text');
    if (lastUpdated && lastUpdated.textContent.includes('Last updated')) {
      const now = new Date();
      const timeString = now.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
      });
      // Use global security utility for safe HTML assignment
      window.SecurityUtils.safeInnerHTML(
        lastUpdated,
        `<i class="fas fa-clock me-2"></i>Last updated: ${timeString}`,
      );
    }
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  new MaintenanceManager();
});

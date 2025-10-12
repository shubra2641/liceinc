// ===== FRONTEND CONSOLIDATED JAVASCRIPT =====
// Optimized and refactored version with reduced complexity and duplication

'use strict';

// ===== GLOBAL UTILITIES =====
const FrontendUtils = {
  // Security utilities
  SecurityUtils: {
    safeInnerHTML(element, html, allowScripts = false, sanitize = true) {
      if (typeof window.SecurityUtils !== 'undefined') {
        return window.SecurityUtils.safeInnerHTML(element, html, allowScripts, sanitize);
      }
      // Fallback implementation
      element.innerHTML = html;
    },
    
    secureRandom(max = 1) {
      if (typeof window.SecurityUtils !== 'undefined' && window.SecurityUtils.secureRandom) {
        return window.SecurityUtils.secureRandom(max);
      }
      return Math.random() * max;
    }
  },

  // DOM utilities
  DOM: {
    $(selector, context = document) {
      return context.querySelector(selector);
    },
    
    $$(selector, context = document) {
      return context.querySelectorAll(selector);
    },
    
    addEventListeners(selector, event, handler) {
      this.$$(selector).forEach(element => {
        element.addEventListener(event, handler);
      });
    }
  },

  // Notification system
  Notification: {
    show(message, type = 'info') {
      const notification = document.createElement('div');
      notification.className = `user-notification user-notification-${type} show`;
      
      const iconMap = {
        success: 'check',
        error: 'times',
        warning: 'exclamation',
        info: 'info'
      };
      
      const icon = iconMap[type] || 'info';
      
      FrontendUtils.SecurityUtils.safeInnerHTML(notification, `
        <div class="user-notification-content">
          <div class="user-notification-icon">
            <i class="fas fa-${icon}-circle"></i>
          </div>
          <div class="user-notification-message">${message}</div>
          <button class="user-notification-close" onclick="this.parentElement.parentElement.remove()">
            <i class="fas fa-times"></i>
          </button>
        </div>
      `, true, true);

      document.body.appendChild(notification);
      setTimeout(() => notification.remove(), 5000);
    }
  }
};

// ===== EVENT HANDLERS =====
const EventHandlers = {
  // Product actions
  purchaseProduct() {
    FrontendUtils.Notification.show('Product purchase functionality will be implemented here', 'info');
  },

  downloadProduct() {
    FrontendUtils.Notification.show('Product download functionality will be implemented here', 'info');
  },

  addToWishlist() {
    FrontendUtils.Notification.show('Add to wishlist functionality will be implemented here', 'info');
  },

  processPayment(method) {
    FrontendUtils.Notification.show(`Payment processing with ${method} will be implemented here`, 'info');
  },

  copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
      navigator.clipboard
        .writeText(element.textContent)
        .then(() => {
          FrontendUtils.Notification.show('Copied to clipboard successfully!', 'success');
        })
        .catch(err => {
          FrontendUtils.Notification.show(`Could not copy text: ${err.message}`, 'error');
        });
    } else {
      FrontendUtils.Notification.show('Element not found!', 'error');
    }
  },

  generateLicenseKeyPreview() {
    FrontendUtils.Notification.show('License key preview generation will be implemented here', 'info');
  },

  confirmDelete(type) {
    const messages = {
      'delete-license': 'Are you sure you want to delete this license?',
      'delete-product': 'Are you sure you want to delete this product?',
      'delete-category': 'Are you sure you want to delete this category?',
      'delete-category-articles': 'Are you sure you want to delete this category and all its articles?',
      'delete-article': 'Are you sure you want to delete this article?',
      'delete-invoice': 'Are you sure you want to delete this invoice?',
      'delete-template': 'Are you sure you want to delete this template?',
      'delete-user': 'Are you sure you want to delete this user?',
      'delete-ticket': 'Are you sure you want to delete this ticket?',
      'delete-reply': 'Are you sure you want to delete this reply?'
    };
    
    return confirm(messages[type] || 'Are you sure you want to delete this item?');
  },

  showTab(tabId) {
    const targetPanel = document.getElementById(tabId);
    if (!targetPanel) return;

    // Hide all panels
    document.querySelectorAll('.admin-tab-panel').forEach(panel => {
      panel.classList.add('admin-tab-panel-hidden');
    });

    // Remove active class from all buttons
    document.querySelectorAll('.admin-tab-btn').forEach(btn => {
      btn.classList.remove('admin-tab-btn-active');
    });

    // Show target panel
    targetPanel.classList.remove('admin-tab-panel-hidden');

    // Add active class to clicked button
    const activeButton = document.querySelector(`[data-tab="${tabId}"]`);
    if (activeButton) {
      activeButton.classList.add('admin-tab-btn-active');
    }
  },

  logout() {
    if (confirm('Are you sure you want to logout?')) {
      window.location.href = '/logout';
    }
  },

  clearCache() {
    if (confirm('Are you sure you want to clear the cache?')) {
      window.location.href = '/admin/clear-cache';
    }
  }
};

// ===== PRELOADER MANAGER =====
class PreloaderManager {
  constructor() {
    this.container = FrontendUtils.DOM.$('.preloader-container');
    this.init();
  }

  init() {
    this.bindEvents();
  }

  bindEvents() {
    // Auto-hide after page load
    window.addEventListener('load', () => {
      this.hide();
    });

    // Hide on user interaction
    const hideOnInteraction = () => this.hide();
    document.addEventListener('click', hideOnInteraction, { once: true });
    document.addEventListener('keydown', hideOnInteraction, { once: true });
    document.addEventListener('scroll', hideOnInteraction, { once: true });
  }

  show() {
    if (this.container) {
      this.container.classList.remove('hidden');
      document.body.classList.add('preloader-active');
    }
  }

  hide() {
    if (this.container) {
      this.container.classList.add('hidden');
      document.body.classList.remove('preloader-active');
    }
  }
}

// ===== PRODUCT SHOW MANAGER =====
class ProductShowManager {
  constructor() {
    this.init();
  }

  init() {
    this.setupEventListeners();
  }

  setupEventListeners() {
    FrontendUtils.DOM.addEventListeners('[data-action="purchase"]', 'click', (e) => {
      e.preventDefault();
      this.handlePurchase();
    });

    FrontendUtils.DOM.addEventListeners('[data-action="download"]', 'click', (e) => {
      e.preventDefault();
      this.handleDownload();
    });

    FrontendUtils.DOM.addEventListeners('[data-action="wishlist"]', 'click', (e) => {
      e.preventDefault();
      this.handleWishlist();
    });
  }

  handlePurchase() {
    FrontendUtils.Notification.show('Purchase functionality will be implemented', 'info');
  }

  handleDownload() {
    FrontendUtils.Notification.show('Download functionality will be implemented', 'info');
  }

  handleWishlist() {
    FrontendUtils.Notification.show('Wishlist functionality will be implemented', 'info');
  }
}

// ===== USER TICKETS MANAGER =====
class UserTicketsManager {
  constructor() {
    this.init();
  }

  init() {
    this.setupEventListeners();
    this.initializeLicenseCheck();
  }

  setupEventListeners() {
    // Purchase code input
    const purchaseCodeInput = FrontendUtils.DOM.$('#purchase_code');
    if (purchaseCodeInput) {
      purchaseCodeInput.addEventListener('input', () => {
        this.handlePurchaseCodeInput(purchaseCodeInput);
      });
    }

    // Form submission
    const ticketForm = FrontendUtils.DOM.$('#ticket-form');
    if (ticketForm) {
      ticketForm.addEventListener('submit', (e) => {
        this.handleFormSubmission(e);
      });
    }
  }

  handlePurchaseCodeInput(input) {
    const code = input.value.trim();
    if (code.length >= 5) {
      this.verifyPurchaseCode(code);
    }
  }

  async verifyPurchaseCode(code) {
    try {
      const response = await fetch('/api/verify-purchase-code', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify({ code })
      });

      const data = await response.json();
      this.handleVerificationResponse(data);
    } catch (error) {
      this.handleVerificationError(error);
    }
  }

  handleVerificationResponse(data) {
    const productNameDisplay = FrontendUtils.DOM.$('#product-name-display');
    const productSlugInput = FrontendUtils.DOM.$('#product_slug');
    
    if (data.success && data.product) {
      if (productNameDisplay) {
        productNameDisplay.textContent = data.product.name;
        productNameDisplay.classList.remove('hidden');
      }
      
      if (productSlugInput) {
        productSlugInput.value = data.product.slug;
        productSlugInput.style.borderColor = '#28a745';
        productSlugInput.placeholder = 'Product slug filled automatically';
        productSlugInput.dispatchEvent(new Event('input'));
      }
    } else {
      this.handleVerificationError();
    }
  }

  handleVerificationError(error = null) {
    const productNameDisplay = FrontendUtils.DOM.$('#product-name-display');
    const productSlugInput = FrontendUtils.DOM.$('#product_slug');
    
    if (productNameDisplay) {
      productNameDisplay.classList.add('hidden');
    }
    
    if (productSlugInput) {
      productSlugInput.value = '';
      productSlugInput.style.borderColor = '#dc3545';
      productSlugInput.placeholder = error ? 'Error verifying purchase code' : 'Invalid purchase code';
    }
  }

  handleFormSubmission(e) {
    // Form validation and submission logic
    const formData = new FormData(e.target);
    // Additional form handling logic here
  }
}

// ===== MAINTENANCE MANAGER =====
class MaintenanceManager {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
    this.initializeComponents();
  }

  bindEvents() {
    FrontendUtils.DOM.addEventListeners('[data-action="reload"]', 'click', (e) => {
      this.handleRefresh(e.target);
    });

    this.startProgressAnimation();
  }

  initializeComponents() {
    this.initProgressBar();
    this.initStatusUpdates();
  }

  handleRefresh(button) {
    if (button) {
      const originalText = button.innerHTML;
      FrontendUtils.SecurityUtils.safeInnerHTML(button, 
        '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...', 
        true, 
        true
      );
      button.disabled = true;

      setTimeout(() => {
        window.location.reload();
      }, 1000);
    }
  }

  initProgressBar() {
    const progressFill = FrontendUtils.DOM.$('.progress-fill');
    if (progressFill) {
      setTimeout(() => {
        progressFill.style.width = '75%';
      }, 500);
    }
  }

  startProgressAnimation() {
    const progressFill = FrontendUtils.DOM.$('.progress-fill');
    if (progressFill) {
      let progress = 0;
      const interval = setInterval(() => {
        progress += FrontendUtils.SecurityUtils.secureRandom(2);
        if (progress >= 75) {
          progress = 75;
          clearInterval(interval);
        }
        progressFill.style.width = `${progress}%`;
      }, 2000);
    }
  }

  initStatusUpdates() {
    this.updateTimestamps();
    setInterval(() => {
      this.updateTimestamps();
    }, 60000);
  }

  updateTimestamps() {
    const lastUpdated = FrontendUtils.DOM.$('.footer-text');
    if (lastUpdated && lastUpdated.textContent.includes('Last updated')) {
      const now = new Date();
      const timeString = now.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
      });
      
      FrontendUtils.SecurityUtils.safeInnerHTML(
        lastUpdated,
        `<i class="fas fa-clock me-2"></i>Last updated: ${timeString}`
      );
    }
  }
}

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', () => {
  // Initialize all managers
  new PreloaderManager();
  new ProductShowManager();
  new UserTicketsManager();
  new MaintenanceManager();

  // Setup global event listeners
  FrontendUtils.DOM.addEventListeners('[data-action="reload"]', 'click', () => {
    location.reload();
  });

  FrontendUtils.DOM.addEventListeners('[data-action="purchase"]', 'click', EventHandlers.purchaseProduct);
  FrontendUtils.DOM.addEventListeners('[data-action="download"]', 'click', EventHandlers.downloadProduct);
  FrontendUtils.DOM.addEventListeners('[data-action="wishlist"]', 'click', EventHandlers.addToWishlist);
  FrontendUtils.DOM.addEventListeners('[data-payment]', 'click', function() {
    const paymentMethod = this.getAttribute('data-payment');
    EventHandlers.processPayment(paymentMethod);
  });
  FrontendUtils.DOM.addEventListeners('[data-action="print"]', 'click', () => {
    window.print();
  });
  FrontendUtils.DOM.addEventListeners('[data-copy-target]', 'click', function() {
    const targetId = this.getAttribute('data-copy-target');
    EventHandlers.copyToClipboard(targetId);
  });
  FrontendUtils.DOM.addEventListeners('[data-action="generate-preview"]', 'click', EventHandlers.generateLicenseKeyPreview);
  FrontendUtils.DOM.addEventListeners('[data-confirm]', 'submit', function(e) {
    const confirmType = this.getAttribute('data-confirm');
    if (!EventHandlers.confirmDelete(confirmType)) {
      e.preventDefault();
    }
  });
  FrontendUtils.DOM.addEventListeners('[data-action="show-tab"], [data-tab]', 'click', function() {
    const tabId = this.getAttribute('data-tab');
    EventHandlers.showTab(tabId);
  });

  // Flash message handling
  const flashSuccess = FrontendUtils.DOM.$('.flash-success');
  const flashError = FrontendUtils.DOM.$('.flash-error');
  const flashWarning = FrontendUtils.DOM.$('.flash-warning');
  const flashInfo = FrontendUtils.DOM.$('.flash-info');

  if (flashSuccess) {
    FrontendUtils.Notification.show(flashSuccess.content, 'success');
  }
  if (flashError) {
    FrontendUtils.Notification.show(flashError.content, 'error');
  }
  if (flashWarning) {
    FrontendUtils.Notification.show(flashWarning.content, 'warning');
  }
  if (flashInfo) {
    FrontendUtils.Notification.show(flashInfo.content, 'info');
  }
});

// ===== GLOBAL EXPORTS =====
window.FrontendUtils = FrontendUtils;
window.EventHandlers = EventHandlers;
window.showNotification = FrontendUtils.Notification.show;
/**
 * User Dashboard JavaScript - Optimized and Secure
 * Handles modal, dark mode, and responsive navigation with reduced complexity
 */

'use strict';

// ===== UTILITY FUNCTIONS =====
const Utils = {
  // Simple DOM helpers
  addClass(element, className) {
    if (element) element.classList.add(className);
  },

  removeClass(element, className) {
    if (element) element.classList.remove(className);
  },

  toggleClass(element, className) {
    if (element) element.classList.toggle(className);
  },

  setStyle(element, property, value) {
    if (element) element.style.setProperty(property, value);
  }
};

// ===== BASE MANAGER =====
class BaseManager {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
  }

  bindEvents() {
    document.addEventListener('click', (e) => {
      this.handleClick(e);
    });
  }
}

// ===== MODAL MANAGER =====
class ModalManager extends BaseManager {
  bindEvents() {
    super.bindEvents();
    
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        this.hideAll();
      }
    });
  }

  handleClick(e) {
    // Close modal on backdrop click
    if (e.target.classList.contains('user-modal-backdrop')) {
      this.hideAll();
      return;
    }

    // Close modal on close button click
    if (e.target.classList.contains('user-modal-close') || 
        e.target.closest('.user-modal-close')) {
      this.hideAll();
      return;
    }

    // Show modal on trigger click
    if (e.target.hasAttribute('data-modal-trigger')) {
      const modalId = e.target.getAttribute('data-modal-trigger');
      this.show(modalId);
    }
  }

  hideAll() {
    const modals = document.querySelectorAll('.license-history-modal, .user-modal-backdrop');
    modals.forEach(modal => {
      Utils.removeClass(modal, 'show');
      Utils.setStyle(modal, 'display', 'none');
    });
    Utils.setStyle(document.body, 'overflow', 'auto');
  }

  show(modalId) {
    const modal = document.getElementById(modalId);
    const backdrop = document.querySelector('.user-modal-backdrop');

    if (modal && backdrop) {
      Utils.addClass(modal, 'show');
      Utils.addClass(backdrop, 'show');
      Utils.setStyle(modal, 'display', 'block');
      Utils.setStyle(backdrop, 'display', 'block');
      Utils.setStyle(document.body, 'overflow', 'hidden');
    }
  }
}

// ===== THEME MANAGER =====
class ThemeManager {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
    this.initializeTheme();
  }

  bindEvents() {
    const themeToggleBtn = document.querySelector('[data-theme-toggle]');
    if (themeToggleBtn) {
      themeToggleBtn.addEventListener('click', () => this.toggle());
    }

    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)')
      .addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
          this.set(e.matches ? 'dark' : 'light');
        }
      });
  }

  getStoredTheme() {
    const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
    return localStorage.getItem('theme') || (prefersDarkMode ? 'dark' : 'light');
  }

  set(theme) {
    // Safe theme setting without direct DOM manipulation
    if (theme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.removeAttribute('data-theme');
      document.documentElement.classList.remove('dark');
    }

    localStorage.setItem('theme', theme);
  }

  toggle() {
    const currentTheme = this.getStoredTheme();
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    this.set(newTheme);
  }

  initializeTheme() {
    const theme = this.getStoredTheme();
    this.set(theme);
  }
}

// ===== MOBILE MENU MANAGER =====
class MobileMenuManager {
  constructor() {
    this.mobileMenuToggle = document.querySelector('[data-mobile-menu-toggle]');
    this.mobileMenu = document.querySelector('[data-mobile-menu]');
    this.mobileMenuClose = document.querySelector('.mobile-menu-close');
    this.init();
  }

  init() {
    this.bindEvents();
    this.handleResize();
    window.addEventListener('resize', () => this.handleResize());
  }

  bindEvents() {
    if (this.mobileMenuToggle) {
      this.mobileMenuToggle.addEventListener('click', () => this.toggle());
    }

    if (this.mobileMenuClose) {
      this.mobileMenuClose.addEventListener('click', () => this.close());
    }

    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('mobile-menu-backdrop')) {
        this.close();
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.mobileMenu && this.mobileMenu.classList.contains('show')) {
        this.close();
      }
    });
  }

  toggle() {
    if (this.mobileMenu) {
      Utils.toggleClass(this.mobileMenu, 'active');
      Utils.toggleClass(document.body, 'mobile-menu-open');
      this.toggleBackdrop();
    }
  }

  close() {
    if (this.mobileMenu) {
      Utils.removeClass(this.mobileMenu, 'active');
      Utils.removeClass(document.body, 'mobile-menu-open');
      this.hideBackdrop();
    }
  }

  toggleBackdrop() {
    let backdrop = document.querySelector('.mobile-menu-backdrop');
    if (!backdrop) {
      backdrop = document.createElement('div');
      backdrop.classList.add('mobile-menu-backdrop');
      document.body.appendChild(backdrop);
    }
    Utils.toggleClass(backdrop, 'active');
  }

  hideBackdrop() {
    const backdrop = document.querySelector('.mobile-menu-backdrop');
    if (backdrop) {
      Utils.removeClass(backdrop, 'active');
    }
  }

  handleResize() {
    const isMobile = window.innerWidth <= 768;
    const desktopNav = document.querySelector('.user-nav-links');

    if (isMobile) {
      if (desktopNav) {
        Utils.setStyle(desktopNav, 'display', 'none');
      }
      if (this.mobileMenuToggle) {
        Utils.setStyle(this.mobileMenuToggle, 'display', 'flex');
      }
    } else {
      if (desktopNav) {
        Utils.setStyle(desktopNav, 'display', 'flex');
      }
      if (this.mobileMenuToggle) {
        Utils.setStyle(this.mobileMenuToggle, 'display', 'none');
      }
      this.close();
    }
  }
}

// ===== DROPDOWN MANAGER =====
class DropdownManager extends BaseManager {
  bindEvents() {
    super.bindEvents();
    
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Tab') {
        this.handleTabNavigation(e);
      }
    });
  }

  handleClick(e) {
    const dropdownToggle = e.target.closest('.user-dropdown-toggle');
    const dropdown = e.target.closest('.user-dropdown');

    if (dropdownToggle) {
      e.preventDefault();
      const dropdownMenu = dropdown?.querySelector('.user-dropdown-menu');
      if (dropdownMenu) {
        this.closeOthers(dropdownMenu);
        Utils.toggleClass(dropdownMenu, 'open');
      }
    } else {
      this.closeAll();
    }
  }

  handleTabNavigation(e) {
    const focusedElement = document.activeElement;
    const dropdown = focusedElement?.closest('.user-dropdown');

    if (dropdown && !e.shiftKey) {
      const dropdownMenu = dropdown.querySelector('.user-dropdown-menu');
      if (dropdownMenu && !dropdownMenu.classList.contains('open')) {
        SecureUtils.safeAddClass(dropdownMenu, 'open');
      }
    }
  }

  closeOthers(currentMenu) {
    document.querySelectorAll('.user-dropdown-menu').forEach(menu => {
      if (menu !== currentMenu) {
        SecureUtils.safeRemoveClass(menu, 'open');
      }
    });
  }

  closeAll() {
    document.querySelectorAll('.user-dropdown-menu').forEach(menu => {
      SecureUtils.safeRemoveClass(menu, 'open');
    });
  }
}

// ===== FORM MANAGER =====
class FormManager {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
  }

  bindEvents() {
    document.querySelectorAll('form').forEach(form => {
      form.addEventListener('submit', () => this.handleSubmit(form));
    });

    document.addEventListener('click', (e) => {
      if (e.target.hasAttribute('data-action') && 
          e.target.getAttribute('data-action') === 'logout') {
        e.preventDefault();
        this.handleLogout();
      }
    });
  }

  handleSubmit(form) {
    const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
      SecureUtils.safeAddClass(submitBtn, 'loading');

      // Re-enable after 5 seconds as fallback
      setTimeout(() => {
        submitBtn.disabled = false;
        SecureUtils.safeRemoveClass(submitBtn, 'loading');
      }, 5000);
    }
  }

  handleLogout() {
    const logoutForm = document.getElementById('logout-form');
    if (logoutForm) {
      logoutForm.submit();
    }
  }
}

// ===== SCROLL MANAGER =====
class ScrollManager {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
  }

  bindEvents() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', (e) => this.handleAnchorClick(e));
    });
  }

  handleAnchorClick(e) {
    e.preventDefault();
    const target = document.querySelector(e.target.getAttribute('href'));
    if (target) {
      target.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
      });
    }
  }
}

// ===== NOTIFICATION MANAGER =====
class NotificationManager {
  constructor() {
    this.init();
  }

  init() {
    this.autoHideNotifications();
  }

  autoHideNotifications() {
    document.querySelectorAll('.user-notification').forEach(notification => {
      setTimeout(() => {
        SecureUtils.safeRemoveClass(notification, 'show');
        setTimeout(() => {
          notification.remove();
        }, 300);
      }, 5000);
    });
  }
}

// ===== LAZY LOAD MANAGER =====
class LazyLoadManager {
  constructor() {
    this.init();
  }

  init() {
    if ('IntersectionObserver' in window) {
      this.setupImageObserver();
    }
  }

  setupImageObserver() {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          SecureUtils.safeRemoveClass(img, 'lazy');
          observer.unobserve(img);
        }
      });
    });

    document.querySelectorAll('img[data-src]').forEach(img => {
      imageObserver.observe(img);
    });
  }
}

// ===== LICENSE STATUS MANAGER =====
class LicenseStatusManager {
  constructor() {
    this.bindEvents();
  }

  bindEvents() {
    document.addEventListener('click', (e) => {
      if (e.target.closest('.copy-btn')) {
        this.handleCopy(e);
      }
      if (e.target.closest('.check-another-btn')) {
        this.handleCheckAnother();
      }
    });
  }

  handleCopy(e) {
    e.preventDefault();
    const button = e.target.closest('.copy-btn');
    const targetId = button.getAttribute('data-copy-target');
    const targetElement = document.getElementById(targetId);
    
    if (targetElement) {
      navigator.clipboard.writeText(targetElement.textContent).then(() => {
        this.showCopySuccess(button);
      });
    }
  }

  showCopySuccess(button) {
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.classList.add('copied');
    
    setTimeout(() => {
      button.innerHTML = originalHTML;
      button.classList.remove('copied');
    }, 2000);
  }

  handleCheckAnother() {
    const form = document.getElementById('licenseCheckForm');
    if (form) form.reset();
  }
}

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', () => {
  // Initialize all managers
  new ModalManager();
  new ThemeManager();
  new MobileMenuManager();
  new DropdownManager();
  new FormManager();
  new ScrollManager();
  new NotificationManager();
  new LazyLoadManager();
  new LicenseStatusManager();

  console.log('User Dashboard JavaScript initialized successfully');
});
/**
 * User Dashboard JavaScript
 * Handles modal, dark mode, and responsive navigation
 */

// Constants for magic numbers
const CONSTANTS = {
  MOBILE_BREAKPOINT: 768,
  TOAST_TIMEOUT: 5000,
  ANIMATION_DURATION: 300
};

document.addEventListener('DOMContentLoaded', () => {
  // ========================================
  // Modal Management
  // ========================================

  // Hide all modals by default
  const hideAllModals = () => {
    const modals = document.querySelectorAll(
      '.license-history-modal, .user-modal-backdrop',
    );
    modals.forEach(modal => {
      modal.classList.remove('show');
      modal.style.display = 'none';
    });
  };

  // Show modal function
  const showModal = modalId => {
    const modal = document.getElementById(modalId);
    const backdrop = document.querySelector('.user-modal-backdrop');

    if (modal && backdrop) {
      modal.classList.add('show');
      backdrop.classList.add('show');
      modal.style.display = 'block';
      backdrop.style.display = 'block';
      document.body.style.overflow = 'hidden';
    }
  };

  // Hide modal function
  const hideModal = () => {
    hideAllModals();
    document.body.style.overflow = 'auto';
  };

  // Modal event listeners
  document.addEventListener('click', e => {
    // Close modal on backdrop click
    if (e.target.classList.contains('user-modal-backdrop')) {
      hideModal();
    }

    // Close modal on close button click
    if (
      e.target.classList.contains('user-modal-close') ||
      e.target.closest('.user-modal-close')
    ) {
      hideModal();
    }

    // Show modal on trigger click
    if (e.target.hasAttribute('data-modal-trigger')) {
      const modalId = e.target.getAttribute('data-modal-trigger');
      showModal(modalId);
    }
  });

  // Close modal on Escape key
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      hideModal();
    }
  });

  // ========================================
  // Dark Mode Management
  // ========================================

  // Check system dark mode preference
  const prefersDarkMode = window.matchMedia(
    '(prefers-color-scheme: dark)',
  ).matches;

  // Get stored theme preference
  const getStoredTheme = () => (
    localStorage.getItem('theme') || (prefersDarkMode ? 'dark' : 'light')
  );

  // Set theme
  const setTheme = theme => {
        const html = document.documentElement;
        // Use textContent instead of innerHTML for security

    if (theme === 'dark') {
      html.setAttribute('data-theme', 'dark');
      html.classList.add('dark');
    } else {
      html.removeAttribute('data-theme');
      html.classList.remove('dark');
    }

    localStorage.setItem('theme', theme);
  };

  // Initialize theme
  const initTheme = () => {
    const theme = getStoredTheme();
    setTheme(theme);
  };

  // Toggle theme
  const toggleTheme = () => {
    const currentTheme = getStoredTheme();
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    setTheme(newTheme);
  };

  // Theme toggle button
  const themeToggleBtn = document.querySelector('[data-theme-toggle]');
  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', toggleTheme);
  }

  // Initialize theme on load
  initTheme();

  // Listen for system theme changes
  window
    .matchMedia('(prefers-color-scheme: dark)')
    .addEventListener('change', e => {
      if (!localStorage.getItem('theme')) {
        setTheme(e.matches ? 'dark' : 'light');
      }
    });

  // ========================================
  // Mobile Menu Management
  // ========================================

  const mobileMenuToggle = document.querySelector('[data-mobile-menu-toggle]');
  const mobileMenu = document.querySelector('[data-mobile-menu]');
  const mobileMenuClose = document.querySelector('.mobile-menu-close');

  // Toggle mobile menu
  const toggleMobileMenu = () => {
    if (mobileMenu) {
      mobileMenu.classList.toggle('active');
      document.body.classList.toggle('mobile-menu-open');

      // Create backdrop if it doesn't exist
      let backdrop = document.querySelector('.mobile-menu-backdrop');
      if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.classList.add('mobile-menu-backdrop');
        document.body.appendChild(backdrop);
      }

      backdrop.classList.toggle('active');
    }
  };

  // Close mobile menu
  const closeMobileMenu = () => {
    if (mobileMenu) {
      mobileMenu.classList.remove('active');
      document.body.classList.remove('mobile-menu-open');

      const backdrop = document.querySelector('.mobile-menu-backdrop');
      if (backdrop) {
        backdrop.classList.remove('active');
      }
    }
  };

  // Mobile menu event listeners
  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener('click', toggleMobileMenu);
  }

  if (mobileMenuClose) {
    mobileMenuClose.addEventListener('click', closeMobileMenu);
  }

  // Close mobile menu on backdrop click
  document.addEventListener('click', e => {
    if (e.target.classList.contains('mobile-menu-backdrop')) {
      closeMobileMenu();
    }
  });

  // Close mobile menu on escape key
  document.addEventListener('keydown', e => {
    if (
      e.key === 'Escape' &&
      mobileMenu &&
      mobileMenu.classList.contains('show')
    ) {
      closeMobileMenu();
    }
  });

  // ========================================
  // Responsive Navigation
  // ========================================

  // Handle window resize
  const handleResize = () => {
    const isMobile = window.innerWidth <= CONSTANTS.MOBILE_BREAKPOINT;

    if (isMobile) {
      // Hide desktop navigation on mobile
      const desktopNav = document.querySelector('.user-nav-links');
      if (desktopNav) {
        desktopNav.style.display = 'none';
      }

      // Show mobile menu button
      if (mobileMenuToggle) {
        mobileMenuToggle.style.display = 'flex';
      }
    } else {
      // Show desktop navigation on desktop
      const desktopNav = document.querySelector('.user-nav-links');
      if (desktopNav) {
        desktopNav.style.display = 'flex';
      }

      // Hide mobile menu button
      if (mobileMenuToggle) {
        mobileMenuToggle.style.display = 'none';
      }

      // Close mobile menu if open
      closeMobileMenu();
    }
  };

  // Initialize responsive behavior
  handleResize();
  window.addEventListener('resize', handleResize);

  // ========================================
  // Dropdown Management
  // ========================================

  // Handle dropdown toggles
  document.addEventListener('click', e => {
    const dropdownToggle = e.target.closest('.user-dropdown-toggle');
    const dropdown = e.target.closest('.user-dropdown');

    if (dropdownToggle) {
      e.preventDefault();
      const dropdownMenu = dropdown.querySelector('.user-dropdown-menu');

      if (dropdownMenu) {
        // Close other dropdowns
        document.querySelectorAll('.user-dropdown-menu').forEach(menu => {
          if (menu !== dropdownMenu) {
            menu.classList.remove('open');
          }
        });

        // Toggle current dropdown
        dropdownMenu.classList.toggle('open');
      }
    } else {
      // Close all dropdowns when clicking outside
      document.querySelectorAll('.user-dropdown-menu').forEach(menu => {
        menu.classList.remove('open');
      });
    }
  });

  // ========================================
  // Logout Form Handling
  // ========================================

  // Handle logout button clicks
  document.addEventListener('click', e => {
    if (
      e.target.hasAttribute('data-action') &&
      e.target.getAttribute('data-action') === 'logout'
    ) {
      e.preventDefault();

      const logoutForm = document.getElementById('logout-form');
      if (logoutForm) {
        logoutForm.submit();
      }
    }
  });

  // ========================================
  // Smooth Scrolling
  // ========================================

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start',
        });
      }
    });
  });

  // ========================================
  // Form Enhancements
  // ========================================

  // Add loading states to forms
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', () => {
      const submitBtn = form.querySelector(
        'button[type="submit"], input[type="submit"]',
      );
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('loading');

        // Re-enable after 5 seconds as fallback
        setTimeout(() => {
          submitBtn.disabled = false;
          submitBtn.classList.remove('loading');
        }, CONSTANTS.TOAST_TIMEOUT);
      }
    });
  });

  // ========================================
  // Notification Management
  // ========================================

  // Auto-hide notifications after 5 seconds
  document.querySelectorAll('.user-notification').forEach(notification => {
    setTimeout(() => {
      notification.classList.remove('show');
      setTimeout(() => {
        notification.remove();
        }, CONSTANTS.ANIMATION_DURATION);
    }, CONSTANTS.TOAST_TIMEOUT);
  });

  // ========================================
  // Performance Optimizations
  // ========================================

  // Lazy load images
  if ('IntersectionObserver' in window) {
    const imageObserver = new window.IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          img.classList.remove('lazy');
          observer.unobserve(img);
        }
      });
    });

    document.querySelectorAll('img[data-src]').forEach(img => {
      imageObserver.observe(img);
    });
  }

  // ========================================
  // Accessibility Enhancements
  // ========================================

  // Add keyboard navigation for dropdowns
  document.addEventListener('keydown', e => {
    if (e.key === 'Tab') {
      const focusedElement = document.activeElement;
      const dropdown = focusedElement.closest('.user-dropdown');

      if (dropdown && e.shiftKey === false) {
        const dropdownMenu = dropdown.querySelector('.user-dropdown-menu');
        if (dropdownMenu && !dropdownMenu.classList.contains('open')) {
          dropdownMenu.classList.add('open');
        }
      }
    }
  });

  // ========================================
  // Initialize Everything
  // ========================================

  // Log in development only
  if (typeof window !== 'undefined' && window.console && window.console.log) {
      window.console.log('User Dashboard JavaScript initialized successfully');
  }
});

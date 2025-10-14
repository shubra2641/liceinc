/**
 * User Dashboard JavaScript - Simple and Clean
 */

document.addEventListener('DOMContentLoaded', function () {
  // ===== MODAL FUNCTIONS =====
  function initModals() {
    document.addEventListener('click', function (e) {
      // Close modal on backdrop click
      if (e.target.classList.contains('user-modal-backdrop')) {
        hideAllModals();
        return;
      }

      // Close modal on close button click
      if (e.target.classList.contains('user-modal-close') || e.target.closest('.user-modal-close')) {
        hideAllModals();
        return;
      }

      // Show modal on trigger click
      if (e.target.hasAttribute('data-modal-trigger')) {
        const modalId = e.target.getAttribute('data-modal-trigger');
        showModal(modalId);
      }
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        hideAllModals();
      }
    });
  }

  function hideAllModals() {
    const modals = document.querySelectorAll('.license-history-modal, .user-modal-backdrop');
    modals.forEach(modal => {
      modal.classList.remove('show');
      modal.style.display = 'none';
    });
    document.body.style.overflow = 'auto';
  }

  function showModal(modalId) {
    const modal = document.getElementById(modalId);
    const backdrop = document.querySelector('.user-modal-backdrop');

    if (modal && backdrop) {
      modal.classList.add('show');
      backdrop.classList.add('show');
      modal.style.display = 'block';
      backdrop.style.display = 'block';
      document.body.style.overflow = 'hidden';
    }
  }

  // ===== THEME FUNCTIONS =====
  function initTheme() {
    const themeToggleBtn = document.querySelector('[data-theme-toggle]');
    if (themeToggleBtn) {
      themeToggleBtn.addEventListener('click', toggleTheme);
    }

    // Initialize theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);
  }

  function toggleTheme() {
    const currentTheme = localStorage.getItem('theme') || 'light';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    setTheme(newTheme);
  }

  function setTheme(theme) {
    if (theme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.removeAttribute('data-theme');
      document.documentElement.classList.remove('dark');
    }
    localStorage.setItem('theme', theme);
  }

  // ===== MOBILE MENU FUNCTIONS =====
  function initMobileMenu() {
    const mobileMenuToggle = document.querySelector('[data-mobile-menu-toggle]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    const mobileMenuClose = document.querySelector('.mobile-menu-close');

    if (mobileMenuToggle) {
      mobileMenuToggle.addEventListener('click', toggleMobileMenu);
    }

    if (mobileMenuClose) {
      mobileMenuClose.addEventListener('click', closeMobileMenu);
    }

    // Close on backdrop click
    document.addEventListener('click', function (e) {
      if (e.target.classList.contains('mobile-menu-backdrop')) {
        closeMobileMenu();
      }
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && mobileMenu && mobileMenu.classList.contains('active')) {
        closeMobileMenu();
      }
    });

    // Handle resize
    window.addEventListener('resize', handleResize);
    handleResize();
  }

  function toggleMobileMenu() {
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    if (mobileMenu) {
      mobileMenu.classList.toggle('active');
      document.body.classList.toggle('mobile-menu-open');
      toggleBackdrop();
    }
  }

  function closeMobileMenu() {
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    if (mobileMenu) {
      mobileMenu.classList.remove('active');
      document.body.classList.remove('mobile-menu-open');
      hideBackdrop();
    }
  }

  function toggleBackdrop() {
    let backdrop = document.querySelector('.mobile-menu-backdrop');
    if (!backdrop) {
      backdrop = document.createElement('div');
      backdrop.classList.add('mobile-menu-backdrop');
      document.body.appendChild(backdrop);
    }
    backdrop.classList.toggle('active');
  }

  function hideBackdrop() {
    const backdrop = document.querySelector('.mobile-menu-backdrop');
    if (backdrop) {
      backdrop.classList.remove('active');
    }
  }

  function handleResize() {
    const isMobile = window.innerWidth <= 768;
    const desktopNav = document.querySelector('.user-nav-links');
    const mobileMenuToggle = document.querySelector('[data-mobile-menu-toggle]');

    if (isMobile) {
      if (desktopNav) desktopNav.style.display = 'none';
      if (mobileMenuToggle) mobileMenuToggle.style.display = 'flex';
    } else {
      if (desktopNav) desktopNav.style.display = 'flex';
      if (mobileMenuToggle) mobileMenuToggle.style.display = 'none';
      closeMobileMenu();
    }
  }

  // ===== DROPDOWN FUNCTIONS =====
  function initDropdowns() {
    document.addEventListener('click', function (e) {
      // Close dropdown on outside click
      if (!e.target.closest('.dropdown')) {
        closeAllDropdowns();
        return;
      }

      // Toggle dropdown on trigger click
      if (e.target.hasAttribute('data-dropdown-toggle')) {
        const dropdownId = e.target.getAttribute('data-dropdown-toggle');
        toggleDropdown(dropdownId);
      }
    });
  }

  function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    if (dropdown) {
      if (dropdown.classList.contains('active')) {
        closeAllDropdowns();
      } else {
        closeAllDropdowns();
        dropdown.classList.add('active');
      }
    }
  }

  function closeAllDropdowns() {
    const activeDropdowns = document.querySelectorAll('.dropdown.active');
    activeDropdowns.forEach(dropdown => {
      dropdown.classList.remove('active');
    });
  }

  // ===== COPY FUNCTION =====
  function initCopyButtons() {
    document.addEventListener('click', function (e) {
      if (e.target.closest('.copy-btn')) {
        e.preventDefault();
        const button = e.target.closest('.copy-btn');
        const targetId = button.getAttribute('data-copy-target');
        const targetElement = document.getElementById(targetId);

        if (targetElement) {
          navigator.clipboard.writeText(targetElement.textContent).then(() => {
            showCopySuccess(button);
          });
        }
      }
    });
  }

  function showCopySuccess(button) {
    const originalText = button.textContent;
    button.textContent = '✓';
    button.classList.add('copied');

    setTimeout(() => {
      button.textContent = originalText;
      button.classList.remove('copied');
    }, 2000);
  }

  // ===== LAZY LOAD FUNCTIONS =====
  function initLazyLoad() {
    if ('IntersectionObserver' in window) {
      const imageObserver = new IntersectionObserver((entries, observer) => {
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
  }

  // ===== SCROLL FUNCTIONS =====
  function initScroll() {
    window.addEventListener('scroll', function () {
      const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
      const header = document.querySelector('.user-header');

      if (header) {
        if (scrollTop > 100) {
          header.classList.add('scrolled');
        } else {
          header.classList.remove('scrolled');
        }
      }
    });
  }

  // ===== INITIALIZE ALL =====
  initModals();
  initTheme();
  initMobileMenu();
  initDropdowns();
  initCopyButtons();
  initLazyLoad();
  initScroll();

  console.log('User Dashboard JavaScript initialized');
});
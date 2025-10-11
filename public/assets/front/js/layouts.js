// Layout JavaScript
document.addEventListener('DOMContentLoaded', () => {
  // Initialize mobile menu
  initMobileMenu();

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

// Mobile Menu Functions
function initMobileMenu() {
  const mobileMenuToggle = document.querySelector('[data-mobile-menu-toggle]');
  const mobileMenu = document.querySelector('[data-mobile-menu]');
  const mobileMenuClose = document.querySelector('.mobile-menu-close');
  const mobileMenuBackdrop = document.querySelector('.mobile-menu-backdrop');

  if (!mobileMenuToggle || !mobileMenu) {
    return;
  }

  // Toggle mobile menu
  mobileMenuToggle.addEventListener('click', e => {
    e.preventDefault();
    toggleMobileMenu();
  });

  // Close mobile menu
  if (mobileMenuClose) {
    mobileMenuClose.addEventListener('click', e => {
      e.preventDefault();
      closeMobileMenu();
    });
  }

  // Close mobile menu when clicking backdrop
  if (mobileMenuBackdrop) {
    mobileMenuBackdrop.addEventListener('click', e => {
      e.preventDefault();
      closeMobileMenu();
    });
  }

  // Close mobile menu when clicking outside
  document.addEventListener('click', e => {
    if (
      mobileMenu.classList.contains('active') &&
      !mobileMenu.contains(e.target) &&
      !mobileMenuToggle.contains(e.target)
    ) {
      closeMobileMenu();
    }
  });

  // Close mobile menu on escape key
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
      closeMobileMenu();
    }
  });

  // Handle window resize
  window.addEventListener('resize', () => {
    if (window.innerWidth > 767) {
      closeMobileMenu();
    }
  });
}

function toggleMobileMenu() {
  const mobileMenu = document.querySelector('[data-mobile-menu]');

  if (!mobileMenu) {
    return;
  }

  if (mobileMenu.classList.contains('active')) {
    closeMobileMenu();
  } else {
    openMobileMenu();
  }
}

function openMobileMenu() {
  const mobileMenu = document.querySelector('[data-mobile-menu]');
  const mobileMenuBackdrop = document.querySelector('.mobile-menu-backdrop');

  if (!mobileMenu) {
    return;
  }

  // Add active class to menu
  mobileMenu.classList.add('active');

  // Add active class to backdrop
  if (mobileMenuBackdrop) {
    mobileMenuBackdrop.classList.add('active');
  }

  // Prevent body scroll
  document.body.style.overflow = 'hidden';

  // Focus first menu item for accessibility
  const firstMenuItem = mobileMenu.querySelector('.mobile-nav-link');
  if (firstMenuItem) {
    firstMenuItem.focus();
  }
}

function closeMobileMenu() {
  const mobileMenu = document.querySelector('[data-mobile-menu]');
  const mobileMenuBackdrop = document.querySelector('.mobile-menu-backdrop');

  if (!mobileMenu) {
    return;
  }

  // Remove active class from menu
  mobileMenu.classList.remove('active');

  // Remove active class from backdrop
  if (mobileMenuBackdrop) {
    mobileMenuBackdrop.classList.remove('active');
  }

  // Restore body scroll
  document.body.style.overflow = '';
}

function logout() {
  document.getElementById('logout-form').submit();
}

function clearCache() {
  // Implementation for clearing cache
}

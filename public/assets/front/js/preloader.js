/**
 * Optimized Frontend Preloader System
 * High-performance preloader with intelligent hiding based on page readiness
 * - Hides as soon as page content is ready, regardless of settings duration
 * - Uses settings duration only as maximum time, not minimum
 * - Prevents preloader from showing longer than necessary
 * Envato-compliant JavaScript with validation and best practices
 */
/* eslint-disable no-undef, radix */

class FrontendPreloaderManager {
  constructor() {
    this.container = document.getElementById('preloader-container');
    this.settings = this.getSettings();
    this.isVisible = true;
    this.pageReadyChecked = false;
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

  checkPageReadiness() {
    // Check if page is actually ready by looking at document state and rendered content
    if (
      document.readyState === 'complete' ||
      (document.readyState === 'interactive' &&
        document.body &&
        document.body.children.length > 0)
    ) {
      return true;
    }
    return false;
  }

  setupEventListeners() {
    let pageReadyHidden = false;

    // Hide preloader when DOM is ready (fastest)
    document.addEventListener('DOMContentLoaded', () => {
      // Use requestAnimationFrame to ensure page is visually ready
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          if (!pageReadyHidden && this.checkPageReadiness()) {
            pageReadyHidden = true;
            this.hidePreloader();
          }
        });
      });
    });

    // Hide preloader when page is fully loaded (fallback)
    window.addEventListener('load', () => {
      if (!pageReadyHidden) {
        pageReadyHidden = true;
        this.hidePreloader();
      }
    });

    // Hide on first user interaction (click, keypress, scroll)
    const hideOnInteraction = () => {
      if (this.isVisible && !pageReadyHidden) {
        pageReadyHidden = true;
        this.hidePreloader();
      }
    };

    document.addEventListener('click', hideOnInteraction, { once: true });
    document.addEventListener('keydown', hideOnInteraction, { once: true });
    document.addEventListener('scroll', hideOnInteraction, { once: true });

    // Settings-based timer: Only hide if page hasn't loaded yet
    // This acts as a maximum duration, not minimum
    if (this.settings.duration && this.settings.duration > 500) {
      setTimeout(() => {
        if (!pageReadyHidden) {
          // Page still not ready after settings duration - force hide
          this.hidePreloader();
        }
      }, this.settings.duration);
    }

    // Minimum display time (if user wants preloader to show for at least some time)
    const minDisplayTime = this.container.dataset.minDuration ?
      parseInt(this.container.dataset.minDuration, 10) :
      0;

    if (minDisplayTime > 0) {
      setTimeout(() => {
        // Allow hiding after minimum time has passed
        if (this.checkPageReadiness() && !pageReadyHidden) {
          pageReadyHidden = true;
          this.hidePreloader();
        }
      }, minDisplayTime);
    }

    // Safety fallback: Force hide after maximum duration (prevent stuck preloader)
    setTimeout(
      () => {
        this.hidePreloader();
      },
      Math.max(this.settings.duration || 2000, 3000),
    ); // Max 3 seconds total

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
            // Use crypto.getRandomValues for better security even in UI animations
            if (typeof crypto !== 'undefined' && crypto.getRandomValues) {
              const array = new Uint32Array(1);
              crypto.getRandomValues(array);
              progress += (array[0] / 4294967296) * 20;
            } else {
              progress += Math.random() * 20;
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
  new FrontendPreloaderManager(); // eslint-disable-line no-new
});

// Emergency fallback: Force hide preloader if nothing else worked
setTimeout(() => {
  const preloader = document.getElementById('preloader-container');
  if (preloader && preloader.style.display !== 'none') {
    preloader.style.display = 'none';
    preloader.remove();
    document.body.classList.remove('preloader-active');
  }
}, 5000); // Emergency only - 5 seconds max

// Export for global access
window.FrontendPreloaderManager = FrontendPreloaderManager;

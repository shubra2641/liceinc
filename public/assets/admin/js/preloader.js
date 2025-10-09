/**
 * Professional Admin Preloader System
 * High-quality, customizable preloader with intelligent timing
 * - Hides immediately when page is ready, not after fixed duration
 * - Settings duration acts as maximum limit, not minimum requirement
 * - Optimized for admin interface responsiveness
 * Envato-compliant JavaScript with validation and best practices
 */

class PreloaderManager {
  constructor() {
    this.container = document.getElementById('preloader-container');
    this.settings = window.preloaderSettings || {};
    this.isVisible = true;
    this.pageReadyChecked = false;
    this.init();
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

    // Hide preloader when DOM is ready (faster than window.load)
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

    // Hide on first user interaction for admin
    const hideOnInteraction = () => {
      if (this.isVisible && !pageReadyHidden) {
        pageReadyHidden = true;
        this.hidePreloader();
      }
    };

    document.addEventListener('click', hideOnInteraction, { once: true });
    document.addEventListener('keydown', hideOnInteraction, { once: true });

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

    // Minimum display time for admin (shorter for better UX)
    const minDisplayTime = this.container ?
      parseInt(this.container.dataset.minDuration || '0', 10) :
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

    // Safety fallback: Force hide after maximum duration
    setTimeout(
      () => {
        this.hidePreloader();
      },
      Math.max(this.settings.duration || 2000, 2500),
    ); // Max 2.5 seconds for admin

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
            // Use crypto.getRandomValues for better security
            if (typeof crypto !== 'undefined' && crypto.getRandomValues) {
              const array = new Uint32Array(1);
              crypto.getRandomValues(array);
              progress += (array[0] / 0x100000000) * 15;
            } else {
              // Fallback: use timestamp-based pseudo-random
              const timestamp = Date.now();
              const randomIndex = (timestamp * 9301 + 49297) % 233280;
              progress += (randomIndex / 233280) * 15;
            }
      if (progress >= 100) {
        progress = 100;
        clearInterval(interval);
      }
      progressBar.style.width = `${progress}%`;
    }, 100);
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
    }
  }
}

// Initialize preloader when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  // eslint-disable-next-line no-new
  new PreloaderManager();
});

// Emergency fallback: Force hide preloader if nothing else worked
setTimeout(() => {
  const preloader = document.getElementById('preloader-container');
  if (preloader && preloader.style.display !== 'none') {
    preloader.style.display = 'none';
    preloader.remove();
  }
}, 4000); // Emergency only - 4 seconds max

// Export for global access
window.PreloaderManager = PreloaderManager;

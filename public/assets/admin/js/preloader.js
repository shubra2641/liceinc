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
    this.settings = this.getSettings();
    this.isVisible = true;
    this.pageReadyChecked = false;
    this.init();
  }

  getSettings() {
    // Get settings from window object first, then fallback to data attributes
    const windowSettings = window.preloaderSettings || {};
    const dataSettings = this.container ? {
      enabled: this.container.dataset.enabled === '1',
      type: this.container.dataset.type,
      color: this.container.dataset.color,
      backgroundColor: this.container.dataset.bg,
      duration: parseInt(this.container.dataset.duration, 10) || 2000,
      minDuration: parseInt(this.container.dataset.minDuration, 10) || 0,
      text: this.container.dataset.text,
      logo: this.container.dataset.logo,
      logoText: this.container.dataset.logoText,
      logoShowText: this.container.dataset.logoShowText === '1'
    } : {};

    // Default settings for enhanced functionality
    const defaultSettings = {
      enabled: true,
      type: 'progress',
      color: '#3b82f6',
      backgroundColor: '#ffffff',
      duration: 2000,
      minDuration: 0,
      text: 'Loading...',
      logo: null,
      logoText: '',
      logoShowText: false,
      showProgress: true,
      progressText: true,
      fadeOutDuration: 500,
      animationSpeed: 'normal', // slow, normal, fast
      responsive: true,
      accessibility: true,
      keyboardNavigation: true,
      reducedMotion: false
    };

    // Merge settings with precedence: window > data > defaults
    return { ...defaultSettings, ...dataSettings, ...windowSettings };
  }

  init() {
    if (!this.container || !this.settings.enabled) {
      return;
    }

    // Debug: Log settings to console for troubleshooting
    console.log('Preloader Settings:', this.settings);

    this.setupDynamicStyles();
    this.setupEventListeners();
    this.startPreloader();
  }

  setupDynamicStyles() {
    if (!this.settings.color || !this.settings.backgroundColor) {
      return;
    }

    // Check for reduced motion preference
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    this.settings.reducedMotion = prefersReducedMotion;

    const style = document.createElement('style');
    style.id = 'preloader-dynamic-styles';

    // Animation speed mapping
    const animationSpeeds = {
      slow: '2s',
      normal: '1s',
      fast: '0.5s'
    };

    const animationDuration = animationSpeeds[this.settings.animationSpeed] || '1s';

    style.textContent = `
            :root {
                --preloader-color: ${this.settings.color};
                --preloader-bg: ${this.settings.backgroundColor};
                --preloader-text-color: ${this.settings.color};
                --preloader-fade-duration: ${this.settings.fadeOutDuration}ms;
                --preloader-animation-speed: ${animationDuration};
            }
            
            @media (prefers-color-scheme: dark) {
                :root {
                    --preloader-bg-dark: ${this.settings.backgroundColor === '#ffffff' ? '#1f2937' : this.settings.backgroundColor};
                    --preloader-text-color-dark: ${this.settings.color === '#3b82f6' ? '#d1d5db' : this.settings.color};
                }
            }

            ${prefersReducedMotion ? `
            .preloader-spinner,
            .preloader-dot,
            .preloader-bar,
            .preloader-pulse {
                animation: none !important;
            }
            .preloader-spinner {
                border-left-color: var(--preloader-color);
            }
            .preloader-dot,
            .preloader-bar,
            .preloader-pulse {
                opacity: 0.8;
            }
            ` : ''}

            .preloader-container {
                transition: opacity var(--preloader-fade-duration) ease-out, 
                           visibility var(--preloader-fade-duration) ease-out;
            }

            .preloader-spinner {
                animation-duration: var(--preloader-animation-speed);
            }

            .preloader-dot {
                animation-duration: var(--preloader-animation-speed);
            }

            .preloader-bar {
                animation-duration: var(--preloader-animation-speed);
            }

            .preloader-pulse {
                animation-duration: var(--preloader-animation-speed);
            }
        `;
    document.head.appendChild(style);
  }

  updatePreloaderContent() {
    if (!this.container) return;

    // Update logo
    const logoSection = this.container.querySelector('.preloader-logo');
    if (logoSection) {
      if (this.settings.logo) {
        const logoImg = logoSection.querySelector('.preloader-logo-img');
        if (logoImg) {
          logoImg.src = this.settings.logo;
          logoImg.alt = this.settings.logoText || 'Logo';
        }
      } else if (this.settings.logoShowText && this.settings.logoText) {
        const logoText = logoSection.querySelector('.preloader-logo-text');
        if (logoText) {
          logoText.textContent = this.settings.logoText;
        }
      }
    }

    // Update text
    const textElement = this.container.querySelector('.preloader-text');
    if (textElement && this.settings.text) {
      textElement.textContent = this.settings.text;
    }

    // Apply custom styles based on settings
    if (this.settings.color) {
      this.container.style.setProperty('--preloader-color', this.settings.color);
    }
    if (this.settings.backgroundColor) {
      this.container.style.setProperty('--preloader-bg', this.settings.backgroundColor);
    }
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

    // Record start time
    this.startTime = Date.now();

    // Update content based on settings
    this.updatePreloaderContent();

    // Add entrance animation
    this.container.style.opacity = '1';
    this.container.style.visibility = 'visible';

    // Add accessibility attributes
    if (this.settings.accessibility) {
      this.container.setAttribute('role', 'status');
      this.container.setAttribute('aria-live', 'polite');
      this.container.setAttribute('aria-label', 'Loading content');
    }

    // Start progress animation if it's a progress type
    if (this.settings.type === 'progress') {
      this.animateProgress();
    }

    // Dispatch custom event
    const event = new CustomEvent('preloader:shown', {
      detail: {
        settings: this.settings,
        type: this.settings.type
      }
    });
    document.dispatchEvent(event);
  }

  hidePreloader() {
    if (!this.container || !this.isVisible) {
      return;
    }

    this.isVisible = false;

    // Add fade out animation
    this.container.style.transition = `opacity ${this.settings.fadeOutDuration}ms ease-out, visibility ${this.settings.fadeOutDuration}ms ease-out`;
    this.container.style.opacity = '0';
    this.container.style.visibility = 'hidden';

    // Remove from DOM after animation
    setTimeout(() => {
      if (this.container && this.container.parentNode) {
        this.container.style.display = 'none';
        this.container.classList.add('hidden');
        this.container.parentNode.removeChild(this.container);
      }
    }, this.settings.fadeOutDuration);

    // Dispatch custom event
    const event = new CustomEvent('preloader:hidden', {
      detail: {
        settings: this.settings,
        duration: Date.now() - this.startTime
      }
    });
    document.dispatchEvent(event);
  }

  animateProgress() {
    const progressBar = this.container.querySelector('.preloader-progress-bar');
    if (!progressBar) {
      return;
    }

    let progress = 0;
    const interval = setInterval(() => {
      // Use crypto.getRandomValues for cryptographically secure random numbers
      const array = new Uint32Array(1);
      crypto.getRandomValues(array);
      const randomValue = array[0] / (0xffffffff + 1);
      progress += randomValue * 15;
      if (progress >= 100) {
        progress = 100;
        clearInterval(interval);
      }
      progressBar.style.width = `${progress}%`;

      // Update progress text if enabled
      if (this.settings.progressText) {
        const progressText = this.container.querySelector('.preloader-progress-text');
        if (progressText) {
          progressText.textContent = `${Math.round(progress)}%`;
        }
      }
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

// Global preloader control methods
window.PreloaderControl = {
  hide: () => PreloaderManager.hide(),
  show: () => PreloaderManager.show(),
  isVisible: () => {
    const preloader = document.getElementById('preloader-container');
    return preloader && preloader.style.display !== 'none';
  },
  updateSettings: (newSettings) => {
    if (window.preloaderManager) {
      window.preloaderManager.settings = { ...window.preloaderManager.settings, ...newSettings };
      window.preloaderManager.setupDynamicStyles();
    }
  },
  getSettings: () => {
    return window.preloaderManager ? window.preloaderManager.settings : null;
  }
};

// Event listeners for preloader events
document.addEventListener('preloader:shown', (e) => {
  console.log('Preloader shown:', e.detail);
});

document.addEventListener('preloader:hidden', (e) => {
  console.log('Preloader hidden:', e.detail);
});

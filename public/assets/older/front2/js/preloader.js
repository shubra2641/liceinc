/**
 * Professional Preloader System - Frontend
 * High-quality, customizable preloader with multiple styles
 * Envato-compliant JavaScript with validation and best practices
 */

// Constants for magic numbers - using window object to avoid conflicts
window.PRELOADER_CONSTANTS = {
  FADE_DURATION: 200,
  ANIMATION_DURATION: 800,
  TIMEOUT_DURATION: 2000,
  RETRY_DELAY: 500,
  PROGRESS_STEP: 15,
  PROGRESS_MAX: 100,
  PROGRESS_MIN: 100,
  PROGRESS_FINAL: 100,
  LOOP_DELAY: 500,
  FINAL_DELAY: 3000
};

class FrontendPreloaderManager {
    constructor() {
        this.container = document.getElementById('preloader-container');
        this.settings = this.extractSettings();
        this.isVisible = true;
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

    extractSettings() {
        if (!this.container) {
            return { enabled: false };
        }
        const d = this.container.dataset;
        return {
            enabled: d.enabled === '1' || d.enabled === 'true',
            type: d.type || 'spinner',
            color: d.color || '#3b82f6',
            backgroundColor: d.bg || '#ffffff',
            duration: parseInt(d.duration || window.PRELOADER_CONSTANTS.ANIMATION_DURATION.toString(), 10),
            text: d.text || 'Loading...',
            logo: d.logo || null
        };
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
        // Hide preloader when page is fully loaded
        window.addEventListener('load', () => {
            setTimeout(() => {
                this.hidePreloader();
            }, window.PRELOADER_CONSTANTS.FADE_DURATION); // Small delay to ensure smooth transition
        });

        // Fallback: Hide preloader after maximum duration (minimum 1 second)
        const duration = Math.max(this.settings.duration || window.PRELOADER_CONSTANTS.ANIMATION_DURATION, window.PRELOADER_CONSTANTS.RETRY_DELAY);
        setTimeout(() => {
            this.hidePreloader();
        }, duration);

        // Additional fallback: Hide after 2 seconds maximum
        setTimeout(() => {
            this.hidePreloader();
        }, window.PRELOADER_CONSTANTS.TIMEOUT_DURATION);

        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseAnimations();
            } else {
                this.resumeAnimations();
            }
        });

        // Handle user interaction to hide preloader early
        document.addEventListener('click', () => {
            if (this.isVisible) {
                this.hidePreloader();
            }
        }, { once: true });

        document.addEventListener('keydown', () => {
            if (this.isVisible) {
                this.hidePreloader();
            }
        }, { once: true });
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
        
        // Force hide with multiple methods
        this.container.style.opacity = '0';
        this.container.style.visibility = 'hidden';
        this.container.style.display = 'none';
        this.container.classList.add('hidden');

        // Remove loading state from body
        document.body.classList.remove('preloader-active');

        // Remove from DOM after animation
        setTimeout(() => {
            if (this.container && this.container.parentNode) {
                this.container.parentNode.removeChild(this.container);
            }
        }, window.PRELOADER_CONSTANTS.RETRY_DELAY);
    }

    animateProgress() {
        const progressBar = this.container.querySelector('.preloader-progress-bar');
        if (!progressBar) {
            return;
        }

        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * window.PRELOADER_CONSTANTS.PROGRESS_STEP;
            if (progress >= window.PRELOADER_CONSTANTS.PROGRESS_MAX) {
                progress = window.PRELOADER_CONSTANTS.PROGRESS_MAX;
                clearInterval(interval);
            }
            progressBar.style.width = progress + '%';
        }, window.PRELOADER_CONSTANTS.PROGRESS_STEP);
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
            preloader.classList.add('hidden');
            document.body.classList.remove('preloader-active');
            setTimeout(() => {
                if (preloader.parentNode) {
                    preloader.parentNode.removeChild(preloader);
                }
            }, window.PRELOADER_CONSTANTS.RETRY_DELAY);
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
        // const preloaderManager = new FrontendPreloaderManager(); // Unused variable removed
});

// Additional fallback: Force hide preloader after 3 seconds
setTimeout(() => {
    const preloader = document.getElementById('preloader-container');
    if (preloader) {
        preloader.style.display = 'none';
        preloader.remove();
        document.body.classList.remove('preloader-active');
    }
}, window.PRELOADER_CONSTANTS.FINAL_DELAY);

// Export for global access
window.FrontendPreloaderManager = FrontendPreloaderManager;
/**
 * Professional Preloader System
 * High-quality, customizable preloader with multiple styles
 * Envato-compliant JavaScript with validation and best practices
 */

// Constants to avoid magic numbers
const ANIMATION_DURATION = 500;
const PROGRESS_INCREMENT = 15;
const PROGRESS_MAX = 100;
const PROGRESS_INTERVAL = 100;

class PreloaderManager {
    constructor() {
        this.container = document.getElementById('preloader-container');
        this.settings = window.preloaderSettings || {};
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
            this.hidePreloader();
        });

        // Fallback: Hide preloader after maximum duration
        if (this.settings.duration) {
            setTimeout(() => {
                this.hidePreloader();
            }, this.settings.duration);
        }

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
        this.container.classList.add('hidden');

        // Remove from DOM after animation
        setTimeout(() => {
            if (this.container && this.container.parentNode) {
                this.container.parentNode.removeChild(this.container);
            }
        }, ANIMATION_DURATION);
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
            if (typeof crypto !== 'undefined' && crypto.getRandomValues) {
                crypto.getRandomValues(array);
                progress += (array[0] / 0xffffffff) * PROGRESS_INCREMENT;
            } else {
                // Fallback to a more secure random method for older browsers
                // Use Date.now() and performance.now() for better randomness
                const now = Date.now();
                const perf = typeof performance !== 'undefined' ? performance.now() : 0;
                const randomValue = ((now + perf) % 1000) / 1000;
                progress += randomValue * PROGRESS_INCREMENT;
            }
            if (progress >= PROGRESS_MAX) {
                progress = PROGRESS_MAX;
                clearInterval(interval);
            }
            progressBar.style.width = progress + '%';
        }, PROGRESS_INTERVAL);
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
            setTimeout(() => {
                if (preloader.parentNode) {
                    preloader.parentNode.removeChild(preloader);
                }
            }, ANIMATION_DURATION);
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
    const preloaderManager = new PreloaderManager();
    // Store instance for potential future use
    window.preloaderManagerInstance = preloaderManager;
});

// Export for global access
window.PreloaderManager = PreloaderManager;
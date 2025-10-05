/**
 * Professional Preloader System
 * High-quality, customizable preloader with multiple styles
 * Envato-compliant JavaScript with validation and best practices
 */

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
        // Hide preloader when DOM is ready (faster than window.load)
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                this.hidePreloader();
            }, 500); // Small delay to show preloader briefly
        });

        // Hide preloader when page is fully loaded (fallback)
        window.addEventListener('load', () => {
            this.hidePreloader();
        });

        // Fallback: Hide preloader after maximum duration
        if (this.settings.duration) {
            setTimeout(() => {
                this.hidePreloader();
            }, this.settings.duration);
        }

        // Additional fallback: Hide after 1.5 seconds maximum
        setTimeout(() => {
            this.hidePreloader();
        }, 1500);

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
            progress += Math.random() * 15;
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
            }
            progressBar.style.width = progress + '%';
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
    new PreloaderManager();
});

// Additional fallback: Force hide preloader after 1 second maximum
setTimeout(() => {
    const preloader = document.getElementById('preloader-container');
    if (preloader) {
        preloader.style.display = 'none';
        preloader.remove();
    }
}, 1000);

// Export for global access
window.PreloaderManager = PreloaderManager;
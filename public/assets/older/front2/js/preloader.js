/**
 * Professional Preloader System - Frontend
 * High-quality, customizable preloader with multiple styles
 * Envato-compliant JavaScript with validation and best practices
 */

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
            duration: parseInt(d.duration || '800', 10),
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
            }, 200); // Small delay to ensure smooth transition
        });

        // Fallback: Hide preloader after maximum duration (minimum 1 second)
        const duration = Math.max(this.settings.duration || 800, 500);
        setTimeout(() => {
            this.hidePreloader();
        }, duration);

        // Additional fallback: Hide after 2 seconds maximum
        setTimeout(() => {
            this.hidePreloader();
        }, 2000);

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
        }, 500);
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
            preloader.classList.add('hidden');
            document.body.classList.remove('preloader-active');
            setTimeout(() => {
                if (preloader.parentNode) {
                    preloader.parentNode.removeChild(preloader);
                }
            }, 500);
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
    new FrontendPreloaderManager();
});

// Additional fallback: Force hide preloader after 3 seconds
setTimeout(() => {
    const preloader = document.getElementById('preloader-container');
    if (preloader) {
        preloader.style.display = 'none';
        preloader.remove();
        document.body.classList.remove('preloader-active');
    }
}, 3000);

// Export for global access
window.FrontendPreloaderManager = FrontendPreloaderManager;
/**
 * Product Show Page JavaScript
 * Envato-compliant external JavaScript file
 */

class ProductShowManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupGalleryModal();
        this.setupPurchaseButtons();
        this.setupDownloadButtons();
        this.setupWishlistButtons();
    }

    setupGalleryModal() {
        const galleryModal = document.getElementById('galleryModal');
        const galleryModalImage = document.getElementById('galleryModalImage');
        
        if (galleryModal && galleryModalImage) {
            galleryModal.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                const imageSrc = button.getAttribute('data-image');
                const imageAlt = button.getAttribute('alt');
                
                galleryModalImage.src = imageSrc;
                galleryModalImage.alt = imageAlt;
            });
        }
    }

    setupPurchaseButtons() {
        const purchaseButtons = document.querySelectorAll('[data-action="purchase"]');
        purchaseButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.handlePurchase();
            });
        });
    }

    setupDownloadButtons() {
        const downloadButtons = document.querySelectorAll('[data-action="download"]');
        downloadButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleDownload();
            });
        });
    }

    setupWishlistButtons() {
        const wishlistButtons = document.querySelectorAll('[data-action="wishlist"]');
        wishlistButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleWishlist();
            });
        });
    }

    handlePurchase() {
        // Show notification
        this.showNotification('Purchase functionality will be implemented', 'info');
    }

    handleDownload() {
        // Show notification
        this.showNotification('Download functionality will be implemented', 'info');
    }

    handleWishlist() {
        // Show notification
        this.showNotification('Wishlist functionality will be implemented', 'info');
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        
        // Create DOM elements safely to prevent XSS
        const contentDiv = document.createElement('div');
        contentDiv.className = 'd-flex align-items-center';
        
        const icon = document.createElement('i');
        const iconClass = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle';
        icon.className = `fas fa-${iconClass} me-2`;
        
        const messageSpan = document.createElement('span');
        messageSpan.textContent = message; // Safe text content
        
        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'btn-close';
        closeButton.setAttribute('data-bs-dismiss', 'alert');
        closeButton.setAttribute('aria-label', 'Close');
        
        contentDiv.appendChild(icon);
        contentDiv.appendChild(messageSpan);
        
        notification.appendChild(contentDiv);
        notification.appendChild(closeButton);
        
        // Insert at the top of the page
        const container = document.querySelector('.user-dashboard-container') || document.body;
        container.insertBefore(notification, container.firstChild);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ProductShowManager();
});

// Export for global access
window.ProductShowManager = ProductShowManager;

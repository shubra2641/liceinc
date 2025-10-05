/**
 * User Tickets JavaScript
 * Handles ticket creation form functionality
 */

class UserTickets {
    constructor() {
        this.init();
    }

    init() {
        // Progressive enhancement - ensure form works without JavaScript
        if (typeof document === 'undefined') return;
        
        document.addEventListener('DOMContentLoaded', () => {
            this.setupTicketCreation();
        });
    }

    setupTicketCreation() {
        const categorySelect = document.getElementById('category_id');
        const purchaseCodeSection = document.getElementById('purchase-code-section');
        const productSlugSection = document.getElementById('product-slug-section');
        const purchaseCodeRequired = document.getElementById('purchase-code-required');
        const purchaseCodeInput = document.getElementById('purchase_code');
        const productSlugInput = document.getElementById('product_slug');
        const productNameDisplay = document.getElementById('product-name-display');
        const productNameSpan = document.getElementById('product-name');
        
        // Debug: Check if elements exist
        if (!productSlugInput) {
            return;
        }
        if (!productNameSpan) {
            return;
        }
        if (!productNameDisplay) {
            return;
        }

        // Handle category change
        if (categorySelect) {
            categorySelect.addEventListener('change', () => {
                const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                const requiresPurchaseCode = selectedOption.dataset.requiresPurchaseCode === 'true';
                
                if (requiresPurchaseCode) {
                    purchaseCodeSection.classList.remove('hidden');
                    purchaseCodeRequired.style.display = 'inline';
                    purchaseCodeInput.required = true;
                    productSlugSection.classList.remove('hidden');
                } else {
                    purchaseCodeSection.classList.add('hidden');
                    purchaseCodeRequired.style.display = 'none';
                    purchaseCodeInput.required = false;
                    productSlugSection.classList.add('hidden');
                    productNameDisplay.classList.add('hidden');
                }
            });
        }

        // Handle purchase code verification
        let verificationTimeout;
        if (purchaseCodeInput) {
            purchaseCodeInput.addEventListener('input', () => {
                clearTimeout(verificationTimeout);
                const purchaseCode = purchaseCodeInput.value.trim();
                
                // Reset visual feedback
                productSlugInput.style.borderColor = '';
                productSlugInput.placeholder = 'Product identifier from URL';
                
                if (purchaseCode.length < 10) {
                    productNameDisplay.classList.add('hidden');
                    productSlugInput.value = '';
                    return;
                }

                verificationTimeout = setTimeout(() => {
                    this.verifyPurchaseCode(purchaseCode);
                }, 1000);
            });
        }

        // Set browser info
        const browserInfo = navigator.userAgent + ' | ' + navigator.language + ' | ' + screen.width + 'x' + screen.height;
        const browserInfoInput = document.getElementById('browser_info');
        if (browserInfoInput) {
            browserInfoInput.value = browserInfo;
        }

        // Initialize form state
        if (categorySelect && categorySelect.value) {
            categorySelect.dispatchEvent(new Event('change'));
        }
    }

    verifyPurchaseCode(purchaseCode) {
        const productSlugInput = document.getElementById('product_slug');
        const productNameSpan = document.getElementById('product-name');
        const productNameDisplay = document.getElementById('product-name-display');
        
        // Add visual feedback
        productSlugInput.style.borderColor = '#ffc107';
        productSlugInput.placeholder = 'Verifying purchase code...';
        
        fetch(`/verify-purchase-code/${encodeURIComponent(purchaseCode)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.product) {
                    // Set values
                    productSlugInput.value = data.product.slug;
                    productNameSpan.textContent = data.product.name;
                    productNameDisplay.classList.remove('hidden');
                    
                    // Visual feedback
                    productSlugInput.style.borderColor = '#28a745';
                    productSlugInput.placeholder = 'Product slug filled automatically';
                    
                    // Force update the display
                    productSlugInput.dispatchEvent(new Event('input'));
                } else {
                    productSlugInput.value = '';
                    productNameDisplay.classList.add('hidden');
                    productSlugInput.style.borderColor = '#dc3545';
                    productSlugInput.placeholder = 'Invalid purchase code';
                }
            })
            .catch(error => {
                productSlugInput.value = '';
                productNameDisplay.classList.add('hidden');
                productSlugInput.style.borderColor = '#dc3545';
                productSlugInput.placeholder = 'Error verifying purchase code';
            });
    }
}

// Initialize when DOM is ready
if (typeof document !== 'undefined') {
    new UserTickets();
}

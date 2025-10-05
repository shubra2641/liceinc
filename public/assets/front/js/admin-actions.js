// Admin Actions JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Handle maintenance page reload
    document.querySelectorAll('[data-action="reload"]').forEach(button => {
        button.addEventListener('click', function() {
            location.reload();
        });
    });

    // Handle product actions
    document.querySelectorAll('[data-action="purchase"]').forEach(button => {
        button.addEventListener('click', function() {
            purchaseProduct();
        });
    });

    document.querySelectorAll('[data-action="download"]').forEach(button => {
        button.addEventListener('click', function() {
            downloadProduct();
        });
    });

    document.querySelectorAll('[data-action="wishlist"]').forEach(button => {
        button.addEventListener('click', function() {
            addToWishlist();
        });
    });

    // Handle payment methods
    document.querySelectorAll('[data-payment]').forEach(button => {
        button.addEventListener('click', function() {
            const paymentMethod = this.getAttribute('data-payment');
            processPayment(paymentMethod);
        });
    });

    // Handle print action
    document.querySelectorAll('[data-action="print"]').forEach(button => {
        button.addEventListener('click', function() {
            window.print();
        });
    });

    // Handle copy to clipboard
    document.querySelectorAll('[data-copy-target]').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-copy-target');
            copyToClipboard(targetId);
        });
    });

    // Handle generate preview
    document.querySelectorAll('[data-action="generate-preview"]').forEach(button => {
        button.addEventListener('click', function() {
            generateLicenseKeyPreview();
        });
    });

    // Handle form confirmations
    document.querySelectorAll('[data-confirm]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const confirmType = this.getAttribute('data-confirm');
            if (!confirmDelete(confirmType)) {
                e.preventDefault();
            }
        });
    });

    // Handle tab navigation
    document.querySelectorAll('[data-action="show-tab"]').forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            showTab(tabId);
        });
    });

    // Handle tab navigation by data-tab attribute
    document.querySelectorAll('[data-tab]').forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            showTab(tabId);
        });
    });
});

function purchaseProduct() {
    // Implementation for purchasing product
    showNotification('Product purchase functionality will be implemented here', 'info');
}

function downloadProduct() {
    // Implementation for downloading product
    showNotification('Product download functionality will be implemented here', 'info');
}

function addToWishlist() {
    // Implementation for adding to wishlist
    showNotification('Add to wishlist functionality will be implemented here', 'info');
}

function processPayment(method) {
    // Implementation for processing payment
    showNotification(`Payment processing with ${method} will be implemented here`, 'info');
}

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        navigator.clipboard.writeText(element.textContent).then(function() {
            showNotification('Copied to clipboard successfully!', 'success');
        }).catch(function(err) {
            showNotification('Could not copy text: ' + err.message, 'error');
        });
    } else {
        showNotification('Element not found!', 'error');
    }
}

function generateLicenseKeyPreview() {
    // Implementation for generating license key preview
    showNotification('License key preview generation will be implemented here', 'info');
}

function confirmDelete(type) {
    const messages = {
        'delete-license': 'Are you sure you want to delete this license?',
        'delete-product': 'Are you sure you want to delete this product?',
        'delete-category': 'Are you sure you want to delete this category?',
        'delete-category-articles': 'Are you sure you want to delete this category and all its articles?',
        'delete-article': 'Are you sure you want to delete this article?',
        'delete-invoice': 'Are you sure you want to delete this invoice?',
        'delete-template': 'Are you sure you want to delete this template?',
        'delete-user': 'Are you sure you want to delete this user?'
    };
    
    return window.confirm && confirm(messages[type] || 'Are you sure?');
}

function showTab(tabId) {
    // Hide all tab panels
    document.querySelectorAll('.admin-tab-panel').forEach(panel => {
        panel.classList.add('admin-tab-panel-hidden');
        panel.setAttribute('aria-hidden', 'true');
    });

    // Remove active class from all tab buttons
    document.querySelectorAll('.admin-tab-btn').forEach(btn => {
        btn.classList.remove('admin-tab-btn-active');
        btn.setAttribute('aria-selected', 'false');
        btn.setAttribute('tabindex', '-1');
    });

    // Show the selected tab panel
    const targetPanel = document.getElementById(tabId);
    if (targetPanel) {
        targetPanel.classList.remove('admin-tab-panel-hidden');
        targetPanel.setAttribute('aria-hidden', 'false');
    }

    // Activate the clicked tab button
    const activeButton = document.querySelector(`[data-tab="${tabId}"]`);
    if (activeButton) {
        activeButton.classList.add('admin-tab-btn-active');
        activeButton.setAttribute('aria-selected', 'true');
        activeButton.setAttribute('tabindex', '0');
    }
}

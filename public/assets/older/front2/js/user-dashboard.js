/**
 * User Dashboard JavaScript
 * Progressive Enhancement for User Dashboard Components
 */

// Toast Notification System
class ToastManager {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Create toast container if it doesn't exist
        if (!document.getElementById('toast-container')) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('toast-container');
        }
    }

    show(message, type = 'info', title = null, duration = 5000) {
        const toast = this.createToast(message, type, title);
        this.container.appendChild(toast);

        // Trigger animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                this.hide(toast);
            }, duration);
        }

        return toast;
    }

    createToast(message, type, title) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };

        const titles = {
            success: title || 'Success',
            error: title || 'Error',
            warning: title || 'Warning',
            info: title || 'Information'
        };

        toast.innerHTML = `
            <div class="toast-header">
                <i class="${icons[type]} toast-icon"></i>
                <h6 class="toast-title">${titles[type]}</h6>
                <button type="button" class="toast-close" onclick="window.toastManager.hide(this.closest('.toast'))">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;

        return toast;
    }

    hide(toast) {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    success(message, title = null, duration = 5000) {
        return this.show(message, 'success', title, duration);
    }

    error(message, title = null, duration = 7000) {
        return this.show(message, 'error', title, duration);
    }

    warning(message, title = null, duration = 6000) {
        return this.show(message, 'warning', title, duration);
    }

    info(message, title = null, duration = 5000) {
        return this.show(message, 'info', title, duration);
    }
}

// Initialize toast manager
const toastManager = new ToastManager();

// Make toastManager globally available
window.toastManager = toastManager;

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        initializeDashboard();
        initializeToastMessages();
    });

    function initializeToastMessages() {
        // Add a small delay to ensure toastManager is ready
        setTimeout(() => {
            // Check for session flash messages
            const successMessage = document.querySelector('[data-flash-success]');
            const errorMessage = document.querySelector('[data-flash-error]');
            const warningMessage = document.querySelector('[data-flash-warning]');
            const infoMessage = document.querySelector('[data-flash-info]');

            if (successMessage && window.toastManager) {
                window.toastManager.success(successMessage.textContent);
            }
            if (errorMessage && window.toastManager) {
                window.toastManager.error(errorMessage.textContent);
            }
            if (warningMessage && window.toastManager) {
                window.toastManager.warning(warningMessage.textContent);
            }
            if (infoMessage && window.toastManager) {
                window.toastManager.info(infoMessage.textContent);
            }
        }, 200);
    }

function initializeDashboard() {
    initializeTables();
    initializeForms();
    initializeTabs();
    initializeCopyButtons();
    initializeFilters();
    initializeTicketForm();
    initializeTableOfContents();
    initializeArticleFeatures();
    initializeLicenseStatus();
    initializeMobileMenu();
    initializeProfileTabs();
    
    // Initialize Auth pages if present
    if (document.querySelector('.user-dashboard-container .user-form')) {
        initializeAuth();
    }
    
    // Initialize License Status page if present
    if (document.querySelector('#licenseCheckForm')) {
    }
}

// Auth Pages JavaScript
function initializeAuth() {
    initializePasswordToggles();
    initializeFormValidation();
    initializeFormLoading();
    initializeFormAnimations();
}

// Password Toggle Functionality
function initializePasswordToggles() {
    const loginToggle = document.getElementById('toggle-password');
    const loginPassword = document.getElementById('login-password');
    const loginShow = document.getElementById('password-show');
    const loginHide = document.getElementById('password-hide');

    if (loginToggle && loginPassword) {
        loginToggle.addEventListener('click', function() {
            togglePasswordVisibility(loginPassword, loginShow, loginHide);
        });
    }
}

function togglePasswordVisibility(input, showIcon, hideIcon) {
    if (input.type === 'password') {
        input.type = 'text';
        showIcon.classList.add('hidden');
        hideIcon.classList.remove('hidden');
    } else {
        input.type = 'password';
        showIcon.classList.remove('hidden');
        hideIcon.classList.add('hidden');
    }
}

// Form Validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('.user-form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('.user-input');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateInput(this);
            });
            
            input.addEventListener('input', function() {
                clearInputError(this);
            });
        });
        
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

function validateInput(input) {
    const value = input.value.trim();
    const type = input.type;
    const name = input.name;
    let isValid = true;
    let errorMessage = '';

    if (input.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'This field is required.';
    }

    if (type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address.';
        }
    }

    if (name === 'password' && value) {
        if (value.length < 8) {
            isValid = false;
            errorMessage = 'Password must be at least 8 characters long.';
        }
    }

    if (isValid) {
        clearInputError(input);
    } else {
        showInputError(input, errorMessage);
    }

    return isValid;
}

function validateForm(form) {
    const inputs = form.querySelectorAll('.user-input[required]');
    let isFormValid = true;

    inputs.forEach(input => {
        if (!validateInput(input)) {
            isFormValid = false;
        }
    });

    return isFormValid;
}

function showInputError(input, message) {
    const inputGroup = input.closest('.user-input-group');
    const existingError = inputGroup.parentNode.querySelector('.user-form-error');
    
    if (existingError) {
        existingError.remove();
    }

    const errorDiv = document.createElement('div');
    errorDiv.className = 'user-form-error';
    errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
    
    inputGroup.parentNode.appendChild(errorDiv);
    inputGroup.classList.add('user-error');
}

function clearInputError(input) {
    const inputGroup = input.closest('.user-input-group');
    const existingError = inputGroup.parentNode.querySelector('.user-form-error');
    
    if (existingError) {
        existingError.remove();
    }
    
    inputGroup.classList.remove('user-error');
}

// Form Loading States
function initializeFormLoading() {
    // Handle user forms
    const userForms = document.querySelectorAll('.user-form');
    userForms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('.user-btn[type="submit"]');
            if (submitBtn) {
                setButtonLoading(submitBtn, true);
            }
        });
    });
    
    // Handle auth forms (register, login, etc.)
    const authForms = document.querySelectorAll('.register-form');
    authForms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('.form-submit-button');
            if (submitBtn) {
                setAuthButtonLoading(submitBtn, true);
            }
        });
    });
}

function setButtonLoading(button, isLoading) {
    const text = button.querySelector('.user-btn-text');
    const spinner = button.querySelector('.user-btn-spinner');
    
    if (isLoading) {
        button.classList.add('loading');
        button.disabled = true;
        if (text) text.style.opacity = '0';
        if (spinner) spinner.style.opacity = '1';
    } else {
        button.classList.remove('loading');
        button.disabled = false;
        if (text) text.style.opacity = '1';
        if (spinner) spinner.style.opacity = '0';
    }
}

function setAuthButtonLoading(button, isLoading) {
    const text = button.querySelector('.button-text');
    const spinner = button.querySelector('.button-loading');
    
    if (isLoading) {
        button.classList.add('form-loading');
        button.disabled = true;
        if (text) text.style.opacity = '0';
        if (spinner) {
            spinner.classList.remove('hidden');
            spinner.style.opacity = '1';
        }
    } else {
        button.classList.remove('form-loading');
        button.disabled = false;
        if (text) text.style.opacity = '1';
        if (spinner) {
            spinner.classList.add('hidden');
            spinner.style.opacity = '0';
        }
    }
}

// Form Animations
function initializeFormAnimations() {
    const userCards = document.querySelectorAll('.user-card');
    userCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.classList.add('user-fade-in');
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    const formGroups = document.querySelectorAll('.user-form-group');
    formGroups.forEach((group, index) => {
        group.style.opacity = '0';
        group.style.transform = 'translateY(10px)';
        
        setTimeout(() => {
            group.classList.add('user-slide-up');
            group.style.opacity = '1';
            group.style.transform = 'translateY(0)';
        }, 200 + (index * 50));
    });
}

    function initializeTables() {
        const tableContainers = document.querySelectorAll('.table-container');
        tableContainers.forEach(container => {
            addScrollIndicator(container);
        });
    }

    function addScrollIndicator(container) {
        const table = container.querySelector('.user-table');
        if (!table) return;

        function checkScrollable() {
            const isScrollable = table.scrollWidth > container.clientWidth;
            container.classList.toggle('scrollable', isScrollable);
        }

        checkScrollable();
        window.addEventListener('resize', checkScrollable);
    }

    function initializeForms() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            addFormValidation(form);
        });
    }

    function addFormValidation(form) {
        const requiredFields = form.querySelectorAll('[required]');
        
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill in all required fields', 'error');
            }
        });

        requiredFields.forEach(field => {
            field.addEventListener('input', function() {
                this.classList.remove('error');
            });
        });
    }

    function initializeTabs() {
        const tabContainers = document.querySelectorAll('.profile-tabs');
        
        tabContainers.forEach(container => {
            const tabButtons = container.querySelectorAll('.tab-button');
            const tabContents = container.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetTab = this.getAttribute('data-tab');
                    
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    this.classList.add('active');
                    const targetContent = document.getElementById(targetTab);
                    if (targetContent) {
                        targetContent.classList.add('active');
                    }
                });
                });
            });
        }

    function initializeCopyButtons() {
        const copyButtons = document.querySelectorAll('.copy-key-btn');
        
        copyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const key = this.getAttribute('data-key');
                if (!key) return;
                
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(key).then(() => {
                        showCopySuccess(this);
                    }).catch(() => {
                        fallbackCopy(key, this);
                    });
                } else {
                    fallbackCopy(key, this);
                }
            });
        });
    }

    function fallbackCopy(text, button) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showCopySuccess(button);
        } catch (err) {
            showNotification('Failed to copy to clipboard', 'error');
        }
        
        document.body.removeChild(textArea);
    }

    function showCopySuccess(button) {
        const originalIcon = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.style.color = '#10b981';
        
        setTimeout(() => {
            button.innerHTML = originalIcon;
            button.style.color = '';
        }, 2000);
    }

    function initializeFilters() {
        initializeLicenseFilters();
        initializeInvoiceFilters();
        initializeTicketFilters();
    }

    function initializeLicenseFilters() {
        const statusFilter = document.getElementById('status-filter');
        const searchInput = document.getElementById('search-input');
        
        if (statusFilter && searchInput) {
            const filterFunction = () => filterLicenseTable(statusFilter, searchInput);
            statusFilter.addEventListener('change', filterFunction);
            searchInput.addEventListener('input', filterFunction);
        }
    }

    function filterLicenseTable(statusFilter, searchInput) {
        const statusValue = statusFilter.value.toLowerCase();
        const searchValue = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('.user-table tbody tr');
        
        rows.forEach(row => {
            const status = row.querySelector('.license-status-badge')?.textContent.toLowerCase() || '';
            const productName = row.querySelector('.license-name')?.textContent.toLowerCase() || '';
            
            const statusMatch = !statusValue || status.includes(statusValue);
            const searchMatch = !searchValue || productName.includes(searchValue);
            
            row.style.display = (statusMatch && searchMatch) ? '' : 'none';
        });
    }

    function initializeInvoiceFilters() {
        const statusFilter = document.getElementById('status-filter');
        const searchInput = document.getElementById('search-input');
        
        if (statusFilter && searchInput) {
            const filterFunction = () => filterInvoiceTable(statusFilter, searchInput);
            statusFilter.addEventListener('change', filterFunction);
            searchInput.addEventListener('input', filterFunction);
        }
    }

    function filterInvoiceTable(statusFilter, searchInput) {
        const statusValue = statusFilter.value.toLowerCase();
        const searchValue = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('.user-table tbody tr');
        
        rows.forEach(row => {
            const status = row.querySelector('.invoice-status-badge')?.textContent.toLowerCase() || '';
            const invoiceNumber = row.querySelector('.invoice-number')?.textContent.toLowerCase() || '';
            
            const statusMatch = !statusValue || status.includes(statusValue);
            const searchMatch = !searchValue || invoiceNumber.includes(searchValue);
            
            row.style.display = (statusMatch && searchMatch) ? '' : 'none';
        });
    }

    function initializeTicketFilters() {
        const statusFilter = document.getElementById('status-filter');
        const priorityFilter = document.getElementById('priority-filter');
        const searchInput = document.getElementById('search-input');
        
        if (statusFilter && priorityFilter && searchInput) {
            const filterFunction = () => filterTicketTable(statusFilter, priorityFilter, searchInput);
            statusFilter.addEventListener('change', filterFunction);
            priorityFilter.addEventListener('change', filterFunction);
            searchInput.addEventListener('input', filterFunction);
        }
    }

    function filterTicketTable(statusFilter, priorityFilter, searchInput) {
        const statusValue = statusFilter.value.toLowerCase();
        const priorityValue = priorityFilter.value.toLowerCase();
        const searchValue = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('.user-table tbody tr');
        
        rows.forEach(row => {
            const status = row.querySelector('.ticket-status-badge')?.textContent.toLowerCase() || '';
            const priority = row.querySelector('.ticket-priority-badge')?.textContent.toLowerCase() || '';
            const subject = row.querySelector('.ticket-subject')?.textContent.toLowerCase() || '';
            
            const statusMatch = !statusValue || status.includes(statusValue);
            const priorityMatch = !priorityValue || priority.includes(priorityValue);
            const searchMatch = !searchValue || subject.includes(searchValue);
            
            row.style.display = (statusMatch && priorityMatch && searchMatch) ? '' : 'none';
        });
    }

    function initializeTicketForm() {
        const categorySelect = document.getElementById('category_id');
        const purchaseCodeSection = document.getElementById('purchase-code-section');
        const productSlugSection = document.getElementById('product-slug-section');
        const productVersionField = document.querySelector('input[name="product_version"]');
        const purchaseCodeRequired = document.getElementById('purchase-code-required');
        const purchaseCodeInput = document.getElementById('purchase_code');
        const productSlugInput = document.getElementById('product_slug');
        const productNameSpan = document.getElementById('product-name');

        if (!categorySelect) return;

        // Function to toggle purchase code related fields
        function togglePurchaseCodeFields(requiresPurchaseCode) {
            if (requiresPurchaseCode) {
                // Show all purchase code related fields
                if (purchaseCodeSection) purchaseCodeSection.style.display = 'block';
                if (productSlugSection) productSlugSection.style.display = 'block';
                if (productVersionField) productVersionField.closest('.form-group').style.display = 'block';
                
                // Make purchase code required
                if (purchaseCodeInput) {
                    purchaseCodeInput.required = true;
                    purchaseCodeInput.classList.add('required');
                }
                if (purchaseCodeRequired) purchaseCodeRequired.style.display = 'inline';
            } else {
                // Hide purchase code related fields
                if (purchaseCodeSection) purchaseCodeSection.style.display = 'none';
                if (productSlugSection) productSlugSection.style.display = 'none';
                if (productVersionField) productVersionField.closest('.form-group').style.display = 'none';
                
                // Make purchase code not required
                if (purchaseCodeInput) {
                    purchaseCodeInput.required = false;
                    purchaseCodeInput.classList.remove('required');
                    purchaseCodeInput.value = '';
                }
                if (purchaseCodeRequired) purchaseCodeRequired.style.display = 'none';
                
                // Clear fields
                if (productSlugInput) productSlugInput.value = '';
                if (productNameSpan) productNameSpan.textContent = '';
            }
        }

        // Handle category change
        categorySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const requiresPurchaseCode = selectedOption.getAttribute('data-requires-purchase-code') === 'true';
            togglePurchaseCodeFields(requiresPurchaseCode);
        });

        // Handle purchase code input for auto-filling product info
        if (purchaseCodeInput) {
            let purchaseCodeTimeout;
            purchaseCodeInput.addEventListener('input', function() {
                const purchaseCode = this.value.trim();
                
                // Clear previous timeout
                clearTimeout(purchaseCodeTimeout);
                
                if (purchaseCode.length > 0) {
                    // Add loading state
                    this.classList.add('loading');
                    
                    // Debounce the API call
                    purchaseCodeTimeout = setTimeout(() => {
                        // Make AJAX request to get product info
                        fetch(`/api/verify-purchase-code/${encodeURIComponent(purchaseCode)}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    if (productSlugInput) productSlugInput.value = data.product.slug;
                                    if (productNameSpan) productNameSpan.textContent = data.product.name;
                                    if (productVersionField) productVersionField.value = data.product.version || '';
                                    
                                    // Add success styling
                                    this.classList.remove('error');
                                    this.classList.add('success');
                                } else {
                                    if (productSlugInput) productSlugInput.value = '';
                                    if (productNameSpan) productNameSpan.textContent = '';
                                    if (productVersionField) productVersionField.value = '';
                                    
                                    // Add error styling
                                    this.classList.remove('success');
                                    this.classList.add('error');
                                }
                            })
                            .catch(error => {
                                // Error verifying purchase code handled gracefully
                                if (productSlugInput) productSlugInput.value = '';
                                if (productNameSpan) productNameSpan.textContent = '';
                                if (productVersionField) productVersionField.value = '';
                                
                                // Add error styling
                                this.classList.remove('success');
                                this.classList.add('error');
                            })
                            .finally(() => {
                                // Remove loading state
                                this.classList.remove('loading');
                            });
                    }, 500); // Wait 500ms after user stops typing
                } else {
                    if (productSlugInput) productSlugInput.value = '';
                    if (productNameSpan) productNameSpan.textContent = '';
                    if (productVersionField) productVersionField.value = '';
                    
                    // Remove all styling
                    this.classList.remove('loading', 'success', 'error');
                }
            });
        }

        // Initialize form state based on selected category
        if (categorySelect.value) {
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            const requiresPurchaseCode = selectedOption.getAttribute('data-requires-purchase-code') === 'true';
            togglePurchaseCodeFields(requiresPurchaseCode);
        } else {
            // Hide fields by default
            togglePurchaseCodeFields(false);
        }
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '12px 20px',
            borderRadius: '8px',
            color: 'white',
            fontWeight: '500',
            zIndex: '10000',
            maxWidth: '300px',
            wordWrap: 'break-word',
            boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
            transform: 'translateX(100%)',
            transition: 'transform 0.3s ease-in-out'
        });
        
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
        notification.style.backgroundColor = colors[type] || colors.info;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    function initializeHashScrolling() {
        if (window.location.hash) {
            const targetElement = document.querySelector(window.location.hash);
            if (targetElement) {
                setTimeout(() => {
                    targetElement.scrollIntoView({ behavior: 'smooth' });
                }, 100);
            }
        }
    }

    initializeHashScrolling();

    // Global functions for product actions
    window.processPayment = function(method) {
        showNotification(`Payment processing for ${method} would be implemented here`, 'info');
    };

    window.purchaseProduct = function() {
        showNotification('Purchase functionality will be implemented', 'info');
    };

    window.downloadProduct = function() {
        showNotification('Download functionality will be implemented', 'info');
    };

    window.addToWishlist = function() {
        showNotification('Wishlist functionality will be implemented', 'info');
    };

    window.enableTwoFactor = function() {
        showNotification('Two-factor authentication setup would be implemented here', 'info');
    };

    // --- Article Page Functionality (Used in article.blade.php) ---
    function generateTableOfContents() {
        const tocContainer = document.getElementById('toc-list');
        if (!tocContainer) return;
        
        const content = document.querySelector('.article-content');
        if (!content) return;
        
        const headings = content.querySelectorAll('h1, h2, h3, h4, h5, h6');
        if (headings.length === 0) {
            tocContainer.innerHTML = '<div class="toc-empty"><i class="fas fa-info-circle"></i><span>No headings found in this article</span></div>';
            return;
        }
        
        const tocList = document.createElement('div');
        tocList.className = 'toc-list';
        
        headings.forEach((heading, index) => {
            // Create ID for heading if it doesn't exist
            if (!heading.id) {
                heading.id = 'heading-' + index;
            }
            
            // Create TOC item
            const tocLink = document.createElement('a');
            tocLink.href = '#' + heading.id;
            tocLink.textContent = heading.textContent;
            tocLink.className = 'toc-item ' + heading.tagName.toLowerCase();
            
            // Add click handler for smooth scrolling
            tocLink.addEventListener('click', function(e) {
                e.preventDefault();
                heading.scrollIntoView({ behavior: 'smooth' });
            });
            
            tocList.appendChild(tocLink);
        });
        
        tocContainer.innerHTML = '';
        tocContainer.appendChild(tocList);
        
        // Add scroll spy functionality
        addScrollSpy();
    }

    function addScrollSpy() {
        const tocLinks = document.querySelectorAll('.toc-item');
        const headings = document.querySelectorAll('.article-content h1, .article-content h2, .article-content h3, .article-content h4, .article-content h5, .article-content h6');
        
        if (tocLinks.length === 0 || headings.length === 0) return;
        
        // Remove active class from all links
        function removeActiveClass() {
            tocLinks.forEach(link => link.classList.remove('active'));
        }
        
        // Add active class to current section
        function addActiveClass() {
            const scrollPosition = window.scrollY + 100;
            
            headings.forEach((heading, index) => {
                const headingTop = heading.offsetTop;
                const headingBottom = headingTop + heading.offsetHeight;
                
                if (scrollPosition >= headingTop && scrollPosition < headingBottom) {
                    removeActiveClass();
                    tocLinks[index].classList.add('active');
                }
            });
        }
        
        // Throttle scroll events
        let ticking = false;
        function updateActiveSection() {
            if (!ticking) {
                requestAnimationFrame(() => {
                    addActiveClass();
                    ticking = false;
                });
                ticking = true;
            }
        }
        
        window.addEventListener('scroll', updateActiveSection);
        addActiveClass(); // Set initial active section
    }

    function handlePrintFunctionality() {
        const printBtn = document.querySelector('[data-action="print"]');
        if (!printBtn) return;
        
        printBtn.addEventListener('click', function() {
            // Create print window
            const printWindow = window.open('', '_blank');
            const articleContent = document.querySelector('.user-article-content').innerHTML;
            const articleTitle = document.querySelector('.user-card-title').textContent;
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>${articleTitle}</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 40px; }
                        h1, h2, h3, h4, h5, h6 { color: #333; margin-top: 2rem; }
                        p { margin-bottom: 1rem; }
                        code { background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
                        pre { background: #f4f4f4; padding: 1rem; border-radius: 5px; overflow-x: auto; }
                        blockquote { border-left: 4px solid #007cba; padding-left: 1rem; margin: 1rem 0; }
                        @media print { body { margin: 20px; } }
                    </style>
                </head>
                <body>
                    <h1>${articleTitle}</h1>
                    ${articleContent}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.print();
        });
    }

    function handleShareFunctionality() {
        const shareBtn = document.querySelector('[data-action="share"]');
        if (!shareBtn) return;
        
        shareBtn.addEventListener('click', function() {
            const title = shareBtn.getAttribute('data-title');
            const url = window.location.href;
            
            if (navigator.share) {
                // Use native share API if available
                navigator.share({
                    title: title,
                    url: url
                });
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(url).then(() => {
                    showNotification('Link copied to clipboard!', 'success');
                }).catch(() => {
                    // Fallback: show URL in prompt
                    prompt('Copy this link:', url);
                });
            }
        });
    }

    function initializeTableOfContents() {
        // Generate Table of Contents for article pages
        generateTableOfContents();
    }

    function initializeArticleFeatures() {
        // Handle print functionality
        handlePrintFunctionality();
        
        // Handle share functionality
        handleShareFunctionality();
    }

    function initializeLicenseStatus() {
        const licenseForm = document.getElementById('licenseCheckForm');
        if (!licenseForm) {
            return;
        }
        
        

        const licenseFormCard = document.getElementById('licenseCheckFormCard');
        const checkButton = document.getElementById('checkButton');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const licenseDetails = document.getElementById('licenseDetails');
        const errorMessage = document.getElementById('errorMessage');
        const viewHistoryBtn = document.getElementById('viewHistoryBtn');
        const historyModal = document.getElementById('historyModal');
        const closeHistoryModal = document.getElementById('closeHistoryModal');
        
        // Debug: Check if all elements exist

        // Initialize state - show only the form and hide all other states
        
        
        // Force hide all dynamic sections immediately
        if (loadingSpinner) {
            loadingSpinner.style.display = 'none';
            loadingSpinner.classList.remove('show');
        }
        if (licenseDetails) {
            licenseDetails.style.display = 'none';
            licenseDetails.classList.remove('show');
        }
        if (errorMessage) {
            errorMessage.style.display = 'none';
            errorMessage.classList.remove('show');
        }
        
        hideAllStates();
        showLicenseForm();
        
        // Ensure form is visible
        if (licenseFormCard) {
            licenseFormCard.style.display = 'block';
            licenseFormCard.classList.remove('hidden');
            
        } else {
            // License form card not found
        }
        
        // Debug: Check current state of all elements
        
        // Force show form if it's hidden
        if (licenseFormCard && licenseFormCard.classList.contains('hidden')) {
            
            licenseFormCard.classList.remove('hidden');
            licenseFormCard.style.display = 'block';
        }
        
        // Final check - ensure form is visible and sections are hidden
        setTimeout(() => {
            if (licenseFormCard && licenseFormCard.classList.contains('hidden')) {
                
                licenseFormCard.classList.remove('hidden');
                licenseFormCard.style.display = 'block';
            }
            
            // Force hide sections again
            if (loadingSpinner) {
                loadingSpinner.style.display = 'none';
                loadingSpinner.classList.remove('show');
            }
            if (licenseDetails) {
                licenseDetails.style.display = 'none';
                licenseDetails.classList.remove('show');
            }
            if (errorMessage) {
                errorMessage.style.display = 'none';
                errorMessage.classList.remove('show');
            }
        }, 100);

        // Handle form submission
        licenseForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            handleLicenseCheck();
        });
        
        // Add click event to button as backup
        if (checkButton) {
            checkButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                handleLicenseCheck();
            });
        }

        // Handle history modal
        if (viewHistoryBtn) {
            viewHistoryBtn.addEventListener('click', function() {
                showHistoryModal();
            });
        }

        if (closeHistoryModal) {
            closeHistoryModal.addEventListener('click', function() {
                hideHistoryModal();
            });
        }

        // Handle history modal actions
        const exportHistoryBtn = document.getElementById('exportHistoryBtn');
        const refreshHistoryBtn = document.getElementById('refreshHistoryBtn');

        if (exportHistoryBtn) {
            exportHistoryBtn.addEventListener('click', function() {
                exportHistory();
            });
        }

        if (refreshHistoryBtn) {
            refreshHistoryBtn.addEventListener('click', function() {
                loadLicenseHistory();
                populateHistorySummary();
            });
        }

        // Close modal when clicking outside
        if (historyModal) {
            historyModal.addEventListener('click', function(e) {
                if (e.target === historyModal) {
                    hideHistoryModal();
                }
            });
        }

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !historyModal.classList.contains('hidden')) {
                hideHistoryModal();
            }
        });

        function showLicenseForm() {
            
            hideAllStates();
            // Form card should always be visible, just ensure it's not hidden
            if (licenseFormCard) {
                licenseFormCard.classList.remove('hidden');
                
            }
        }

        function showLoadingState() {
            hideAllStates();
            // Hide form during loading
            if (licenseFormCard) licenseFormCard.classList.add('hidden');
            if (loadingSpinner) {
                loadingSpinner.classList.remove('hidden');
                loadingSpinner.classList.add('show');
            }
        }

        function showLicenseDetails(data) {
            hideAllStates();
            // Hide form when showing results
            if (licenseFormCard) licenseFormCard.classList.add('hidden');
            if (licenseDetails) {
                populateLicenseDetails(data);
                
                // Handle Envato status based on license source
                handleEnvatoStatus(data);
                
                // Add check another license button if it doesn't exist
                let checkAnotherBtn = licenseDetails.querySelector('.check-another-btn');
                if (!checkAnotherBtn) {
                    checkAnotherBtn = document.createElement('button');
                    checkAnotherBtn.className = 'user-btn user-btn-outline check-another-btn';
                    checkAnotherBtn.innerHTML = '<i class="fas fa-search"></i> Check Another License';
                    checkAnotherBtn.style.marginTop = 'var(--spacing-lg)';
                    
                    checkAnotherBtn.addEventListener('click', function() {
                        // Clear form
                        document.getElementById('license_code').value = '';
                        document.getElementById('email').value = '';
                        showLicenseForm();
                    });
                    
                    const formActions = licenseDetails.querySelector('.user-form-actions');
                    if (formActions) {
                        formActions.appendChild(checkAnotherBtn);
                    }
                }
                
                licenseDetails.classList.remove('hidden');
                licenseDetails.classList.add('show');
            }
        }

        function showErrorMessage(message) {
            hideAllStates();
            // Hide form when showing error
            if (licenseFormCard) licenseFormCard.classList.add('hidden');
            if (errorMessage) {
                const errorText = document.getElementById('errorText');
                if (errorText) {
                    errorText.textContent = message;
                }
                
                // Add try again button if it doesn't exist
                let tryAgainBtn = errorMessage.querySelector('.try-again-btn');
                if (!tryAgainBtn) {
                    tryAgainBtn = document.createElement('button');
                    tryAgainBtn.className = 'user-btn user-btn-primary try-again-btn';
                    tryAgainBtn.innerHTML = '<i class="fas fa-redo"></i> Try Again';
                    tryAgainBtn.style.marginTop = 'var(--spacing-lg)';
                    
                    tryAgainBtn.addEventListener('click', function() {
                        showLicenseForm();
                    });
                    
                    const errorContent = errorMessage.querySelector('.user-error-content');
                    if (errorContent) {
                        errorContent.appendChild(tryAgainBtn);
                    }
                }
                
                errorMessage.classList.remove('hidden');
                errorMessage.classList.add('show');
            }
        }

        function hideAllStates() {
            // Don't hide the form card, only hide the result states
            
            if (loadingSpinner) {
                loadingSpinner.classList.remove('show');
                loadingSpinner.classList.add('hidden');
                
            }
            if (licenseDetails) {
                licenseDetails.classList.remove('show');
                licenseDetails.classList.add('hidden');
                
            }
            if (errorMessage) {
                errorMessage.classList.remove('show');
                errorMessage.classList.add('hidden');
                
            }
        }

        function populateLicenseDetails(data) {
            // Update stats cards
            updateStatCard('licenseStatusValue', data.status || '-');
            updateStatCard('licenseTypeValue', data.license_type || '-');
            updateStatCard('daysRemainingValue', data.days_remaining || '-');
            updateStatCard('domainsUsedValue', data.used_domains || '-');

            // Update license information
            updateElement('licenseKey', data.license_key || '-');
            updateElement('licenseType', data.license_type || '-');
            updateElement('licenseStatus', data.status || '-');
            updateElement('createdAt', data.created_at || '-');
            updateElement('expiresAt', data.expires_at || '-');
            updateElement('daysRemaining', data.days_remaining || '-');

            // Update product information
            updateElement('productName', data.product_name || '-');
            updateElement('maxDomains', data.max_domains || '-');
            updateElement('usedDomains', data.used_domains || '-');

            // Update domains list
            updateDomainsList(data.domains || []);

            // Update envato status if available
            if (data.envato_status) {
                updateEnvatoStatus(data.envato_status);
            }
        }

        function updateStatCard(elementId, value) {
            const element = document.getElementById(elementId);
            if (element) {
                element.textContent = value;
            }
        }

        function updateElement(elementId, value) {
            const element = document.getElementById(elementId);
            if (element) {
                element.textContent = value;
            }
        }

        function updateDomainsList(domains) {
            const domainsList = document.getElementById('domainsList');
            if (!domainsList) return;

            if (domains.length === 0) {
                domainsList.innerHTML = '<p class="text-gray-500">No domains registered</p>';
                return;
            }

            domainsList.innerHTML = domains.map(domain => `
                <div class="user-domain-item">
                    <div class="user-domain-info">
                        <div class="user-domain-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <div class="user-domain-details">
                            <div class="user-domain-url">${domain.url || '-'}</div>
                            <div class="user-domain-date">${domain.registered_at || '-'}</div>
                        </div>
                    </div>
                    <div class="user-domain-status ${domain.status || 'inactive'}">
                        <i class="fas fa-circle"></i>
                        ${domain.status || 'Inactive'}
                    </div>
                </div>
            `).join('');
        }

        function updateEnvatoStatus(envatoData) {
            const envatoStatus = document.getElementById('envatoStatus');
            if (!envatoStatus) return;

            envatoStatus.classList.remove('hidden');
            const envatoContent = envatoStatus.querySelector('.user-info-content');
            if (envatoContent) {
                envatoContent.innerHTML = `
                    <div class="user-info-item">
                        <span class="user-info-label">Purchase Code:</span>
                        <span class="user-info-value user-code">${envatoData.purchase_code || '-'}</span>
                    </div>
                    <div class="user-info-item">
                        <span class="user-info-label">Item ID:</span>
                        <span class="user-info-value">${envatoData.item_id || '-'}</span>
                    </div>
                    <div class="user-info-item">
                        <span class="user-info-label">Buyer:</span>
                        <span class="user-info-value">${envatoData.buyer || '-'}</span>
                    </div>
                    <div class="user-info-item">
                        <span class="user-info-label">Purchase Date:</span>
                        <span class="user-info-value">${envatoData.purchase_date || '-'}</span>
                    </div>
                `;
            }
        }

        function showHistoryModal() {
            if (historyModal) {
                // Create backdrop
                const backdrop = document.createElement('div');
                backdrop.className = 'user-modal-backdrop';
                backdrop.id = 'historyModalBackdrop';
                document.body.appendChild(backdrop);
                
                // Show modal
                historyModal.classList.remove('hidden');
                loadLicenseHistory();
                populateHistorySummary();
                
                // Add click outside to close
                backdrop.addEventListener('click', hideHistoryModal);
                
                // Prevent body scroll
                document.body.style.overflow = 'hidden';
            }
        }

        function hideHistoryModal() {
            if (historyModal) {
                historyModal.classList.add('hidden');
                
                // Remove backdrop
                const backdrop = document.getElementById('historyModalBackdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                
                // Restore body scroll
                document.body.style.overflow = '';
            }
        }

        function loadLicenseHistory() {
            const historyContent = document.getElementById('historyContent');
            if (!historyContent) return;

            // Show loading state
            historyContent.innerHTML = '<div class="user-loading-container"><div class="user-loading-spinner"></div><p class="user-loading-text">Loading history...</p></div>';

            // Simulate API call - replace with actual API call
            setTimeout(() => {
                const mockHistory = [
                    {
                        action: 'License Checked',
                        description: 'License verification completed successfully',
                        date: '2025-01-15 10:30:00',
                        ip: '192.168.1.1',
                        type: 'success'
                    },
                    {
                        action: 'Domain Registered',
                        description: 'New domain example.com registered',
                        date: '2025-01-14 15:45:00',
                        ip: '192.168.1.1',
                        type: 'info'
                    },
                    {
                        action: 'License Activated',
                        description: 'License activated for first time',
                        date: '2025-01-13 09:20:00',
                        ip: '192.168.1.1',
                        type: 'success'
                    },
                    {
                        action: 'Domain Limit Warning',
                        description: 'Approaching maximum domain limit',
                        date: '2025-01-12 14:15:00',
                        ip: '192.168.1.1',
                        type: 'warning'
                    },
                    {
                        action: 'License Expired',
                        description: 'License has expired, renewal required',
                        date: '2025-01-11 08:00:00',
                        ip: '192.168.1.1',
                        type: 'error'
                    }
                ];

                historyContent.innerHTML = mockHistory.map(item => `
                    <div class="history-item">
                        <div class="history-item-icon ${item.type}">
                            <i class="fas fa-${getHistoryIcon(item.type)}"></i>
                        </div>
                        <div class="history-item-content">
                            <div class="history-item-title">${item.action}</div>
                            <div class="history-item-description">${item.description}</div>
                            <div class="history-item-meta">
                                <span class="history-item-time">
                                    <i class="fas fa-clock"></i>
                                    ${item.date}
                                </span>
                                <span class="history-item-ip">
                                    <i class="fas fa-globe"></i>
                                    ${item.ip}
                                </span>
                            </div>
                        </div>
                    </div>
                `).join('');
            }, 1000);
        }

        function populateHistorySummary() {
            // Update summary statistics
            const totalChecks = document.getElementById('totalChecks');
            const lastCheck = document.getElementById('lastCheck');
            const activeDomains = document.getElementById('activeDomains');

            if (totalChecks) totalChecks.textContent = '15';
            if (lastCheck) lastCheck.textContent = '2 hours ago';
            if (activeDomains) activeDomains.textContent = '3';
        }

        function getHistoryIcon(type) {
            switch (type) {
                case 'success': return 'check';
                case 'warning': return 'exclamation-triangle';
                case 'error': return 'times';
                case 'info': return 'info-circle';
                default: return 'circle';
            }
        }

        function exportHistory() {
            // Create CSV content
            const csvContent = "Action,Description,Date,IP\n" +
                "License Checked,License verification completed successfully,2025-01-15 10:30:00,192.168.1.1\n" +
                "Domain Registered,New domain example.com registered,2025-01-14 15:45:00,192.168.1.1\n" +
                "License Activated,License activated for first time,2025-01-13 09:20:00,192.168.1.1\n" +
                "Domain Limit Warning,Approaching maximum domain limit,2025-01-12 14:15:00,192.168.1.1\n" +
                "License Expired,License has expired renewal required,2025-01-11 08:00:00,192.168.1.1";

            // Create and download file
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'license-history.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }

        // Copy to clipboard function
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            if (!element) return;

            const text = element.textContent || element.innerText;
            
            if (navigator.clipboard && window.isSecureContext) {
                // Use modern clipboard API
                navigator.clipboard.writeText(text).then(() => {
                    showCopySuccess(element);
                }).catch(() => {
                    fallbackCopyTextToClipboard(text, element);
                });
            } else {
                // Fallback for older browsers
                fallbackCopyTextToClipboard(text, element);
            }
        }

        function fallbackCopyTextToClipboard(text, element) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";
            textArea.style.opacity = "0";
            
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showCopySuccess(element);
                } else {
                    showCopyError(element);
                }
            } catch (err) {
                showCopyError(element);
            }
            
            document.body.removeChild(textArea);
        }

        function showCopySuccess(element) {
            const button = element.nextElementSibling;
            if (button && button.classList.contains('copy-btn')) {
                const originalIcon = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i>';
                button.style.background = '#10b981';
                
                setTimeout(() => {
                    button.innerHTML = originalIcon;
                    button.style.background = '';
                }, 2000);
            }
        }

        function showCopyError(element) {
            const button = element.nextElementSibling;
            if (button && button.classList.contains('copy-btn')) {
                const originalIcon = button.innerHTML;
                button.innerHTML = '<i class="fas fa-times"></i>';
                button.style.background = '#ef4444';
                
                setTimeout(() => {
                    button.innerHTML = originalIcon;
                    button.style.background = '';
                }, 2000);
            }
        }

        // Handle Envato status display based on license source
        function handleEnvatoStatus(licenseData) {
            const envatoData = document.getElementById('envatoData');
            const envatoNA = document.getElementById('envatoNA');
            const envatoSubtitle = document.getElementById('envatoSubtitle');
            
            if (!envatoData || !envatoNA || !envatoSubtitle) return;
            
            // Check if license is from Envato or our system
            // You can modify this logic based on your actual data structure
            const isFromEnvato = licenseData && (
                licenseData.purchase_code || 
                licenseData.item_id || 
                licenseData.buyer_email ||
                licenseData.envato_source === true
            );
            
            if (isFromEnvato) {
                // Show Envato data
                envatoData.classList.remove('hide');
                envatoNA.classList.remove('show');
                envatoSubtitle.textContent = 'Envato purchase information';
                
                // Populate Envato data if available
                if (licenseData.purchase_code) {
                    const purchaseCodeEl = document.getElementById('purchaseCode');
                    if (purchaseCodeEl) purchaseCodeEl.textContent = licenseData.purchase_code;
                }
                if (licenseData.item_id) {
                    const itemIdEl = document.getElementById('itemId');
                    if (itemIdEl) itemIdEl.textContent = licenseData.item_id;
                }
                if (licenseData.buyer_email) {
                    const buyerEmailEl = document.getElementById('buyerEmail');
                    if (buyerEmailEl) buyerEmailEl.textContent = licenseData.buyer_email;
                }
                if (licenseData.purchase_date) {
                    const purchaseDateEl = document.getElementById('purchaseDate');
                    if (purchaseDateEl) purchaseDateEl.textContent = licenseData.purchase_date;
                }
            } else {
                // Show N/A message
                envatoData.classList.add('hide');
                envatoNA.classList.add('show');
                envatoSubtitle.textContent = 'Not available for internal licenses';
            }
        }

        // View domain history function
        function viewDomainHistory(domain) {
            
            // Show the license history modal
            showHistoryModal();
            
            // You can customize the history content based on the domain
            // For now, it will show the general license history
        }

        function handleLicenseCheck() {
            
            const licenseCode = document.getElementById('license_code').value.trim();
            const email = document.getElementById('email').value.trim();

            

            // Set button loading state
            if (checkButton) {
                setAuthButtonLoading(checkButton, true);
            }

            // Clear any previous error messages
            hideAllStates();

            // Validation
            if (!licenseCode || !email) {
                if (checkButton) setAuthButtonLoading(checkButton, false);
                showErrorMessage('Please fill in all required fields.');
                return;
            }

            if (!isValidEmail(email)) {
                if (checkButton) setAuthButtonLoading(checkButton, false);
                showErrorMessage('Please enter a valid email address.');
                return;
            }

            if (licenseCode.length < 10) {
                if (checkButton) setAuthButtonLoading(checkButton, false);
                showErrorMessage('License code must be at least 10 characters long.');
                return;
            }

            showLoadingState();

            // Simulate API call - replace with actual API call
            setTimeout(() => {
                // Mock response - replace with actual API response
                const mockResponse = {
                    success: Math.random() > 0.3, // 70% success rate for demo
                    data: {
                        license_key: licenseCode,
                        license_type: 'Regular',
                        status: 'Active',
                        created_at: '2025-01-01',
                        expires_at: '2026-01-01',
                        days_remaining: '365',
                        product_name: 'Sample Product',
                        max_domains: '1',
                        used_domains: '1',
                        domains: [
                            {
                                url: 'example.com',
                                registered_at: '2025-01-01',
                                status: 'active'
                            }
                        ],
                        envato_status: {
                            purchase_code: 'ABC123-DEF456-GHI789',
                            item_id: '12345678',
                            buyer: email,
                            purchase_date: '2025-01-01'
                        }
                    },
                    error: 'License not found or invalid email.'
                };

                if (mockResponse.success) {
                    showLicenseDetails(mockResponse.data);
                } else {
                    showErrorMessage(mockResponse.error);
                }
                
                // Reset button loading state
                if (checkButton) {
                    setAuthButtonLoading(checkButton, false);
                }
            }, 2000);
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    }

// Mobile Menu JavaScript
function initializeMobileMenu() {
    const mobileMenuToggle = document.querySelector('[data-mobile-menu-toggle]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    const mobileMenuBackdrop = document.querySelector('.mobile-menu-backdrop');
    const mobileMenuClose = document.querySelector('.mobile-menu-close');
    const body = document.body;

    if (!mobileMenuToggle || !mobileMenu) {
        return;
    }

    // Create backdrop if it doesn't exist
    if (!mobileMenuBackdrop) {
        const backdrop = document.createElement('div');
        backdrop.className = 'mobile-menu-backdrop';
        document.body.appendChild(backdrop);
    }

    // Create close button if it doesn't exist
    if (!mobileMenuClose) {
        const closeBtn = document.createElement('button');
        closeBtn.className = 'mobile-menu-close';
        closeBtn.innerHTML = '<i class="fas fa-times"></i>';
        closeBtn.setAttribute('aria-label', 'Close menu');
        
        // Add header if it doesn't exist
        const header = mobileMenu.querySelector('.mobile-menu-header');
        if (!header) {
            const menuHeader = document.createElement('div');
            menuHeader.className = 'mobile-menu-header';
            menuHeader.innerHTML = `
                <div class="mobile-menu-title">Menu</div>
            `;
            menuHeader.appendChild(closeBtn);
            mobileMenu.insertBefore(menuHeader, mobileMenu.firstChild);
        } else {
            header.appendChild(closeBtn);
        }
    }

    // Toggle mobile menu
    function toggleMobileMenu() {
        const isOpen = mobileMenu.classList.contains('active');
        
        if (isOpen) {
            closeMobileMenu();
        } else {
            openMobileMenu();
        }
    }

    // Open mobile menu
    function openMobileMenu() {
        mobileMenu.classList.add('active');
        // keep legacy 'show' in sync with CSS variants
        mobileMenu.classList.add('show');
        // Remove hidden utility in case CSS hides the element
        mobileMenu.classList.remove('hidden');
        // Mark toggle as active for visual state
        mobileMenuToggle.classList.add('active');
        
        const backdrop = document.querySelector('.mobile-menu-backdrop');
        if (backdrop) {
            backdrop.classList.add('active');
        }
        
        body.style.overflow = 'hidden';
        
        // Add escape key listener
        document.addEventListener('keydown', handleEscapeKey);
    }

    // Close mobile menu
    function closeMobileMenu() {
        mobileMenu.classList.remove('active');
        mobileMenu.classList.remove('show');
        // Restore hidden utility class
        mobileMenu.classList.add('hidden');
        mobileMenuToggle.classList.remove('active');
        
        const backdrop = document.querySelector('.mobile-menu-backdrop');
        if (backdrop) {
            backdrop.classList.remove('active');
        }
        
        body.style.overflow = '';
        
        // Remove escape key listener
        document.removeEventListener('keydown', handleEscapeKey);
    }

    // Handle escape key
    function handleEscapeKey(event) {
        if (event.key === 'Escape') {
            closeMobileMenu();
        }
    }

    // Event listeners
    mobileMenuToggle.addEventListener('click', toggleMobileMenu);
    
    // Close button
    const closeBtn = document.querySelector('.mobile-menu-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeMobileMenu);
    }
    
    // Backdrop click
    const backdrop = document.querySelector('.mobile-menu-backdrop');
    if (backdrop) {
        backdrop.addEventListener('click', closeMobileMenu);
    }
    
    // Close menu when clicking on nav links
    const navLinks = mobileMenu.querySelectorAll('.mobile-nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            setTimeout(closeMobileMenu, 150); // Small delay for better UX
        });
    });

    // Handle window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            closeMobileMenu();
        }
    });

    // Prevent body scroll when menu is open
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const isOpen = mobileMenu.classList.contains('active');
                if (isOpen) {
                    body.style.overflow = 'hidden';
                } else {
                    body.style.overflow = '';
                }
            }
        });
    });

    observer.observe(mobileMenu, { attributes: true });
}

// Profile Tabs JavaScript
function initializeProfileTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    if (tabButtons.length === 0 || tabContents.length === 0) {
        return;
    }

    // Function to show specific tab
    function showTab(tabId) {
        // Hide all tab contents
        tabContents.forEach(content => {
            content.classList.remove('active');
        });

        // Remove active class from all buttons
        tabButtons.forEach(button => {
            button.classList.remove('active');
        });

        // Show selected tab content
        const selectedContent = document.getElementById(tabId);
        if (selectedContent) {
            selectedContent.classList.add('active');
        }

        // Add active class to clicked button
        const selectedButton = document.querySelector(`[data-tab="${tabId}"]`);
        if (selectedButton) {
            selectedButton.classList.add('active');
        }
    }

    // Add click event listeners to tab buttons
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            if (tabId) {
                showTab(tabId);
            }
        });
    });

    // Initialize with first tab if no active tab
    const activeTab = document.querySelector('.tab-content.active');
    if (!activeTab && tabContents.length > 0) {
        const firstTabId = tabContents[0].getAttribute('id');
        if (firstTabId) {
            showTab(firstTabId);
        }
    }
}

// ===========================================
// LICENSE CREATE PAGE FUNCTIONALITY
// ===========================================

function initializeLicenseCreate() {
    if (!document.querySelector('.products-form')) return;
    
    setupLicenseCreateEventListeners();
    generateInitialPreview();
}

function setupLicenseCreateEventListeners() {
    // Generate preview button
    const generatePreviewBtn = document.querySelector('[data-action="generate-preview"]');
    if (generatePreviewBtn) {
        generatePreviewBtn.addEventListener('click', () => {
            generateLicenseKeyPreview();
        });
    }

    // Form field change listeners for live preview
    const userSelect = document.getElementById('user_id');
    const productSelect = document.getElementById('product_id');
    const statusSelect = document.getElementById('status');
    const maxDomainsInput = document.getElementById('max_domains');

    if (userSelect) {
        userSelect.addEventListener('change', () => {
            updateLicensePreview();
        });
    }

    if (productSelect) {
        productSelect.addEventListener('change', () => {
            updateLicensePreview();
        });
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', () => {
            updateLicensePreview();
        });
    }

    if (maxDomainsInput) {
        maxDomainsInput.addEventListener('input', () => {
            updateLicensePreview();
        });
    }
}

function generateLicenseKeyPreview() {
    // Generate a preview license key (same format as the actual generation)
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let key = '';
    for (let i = 0; i < 32; i++) {
        key += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    // Format like XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX
    key = key.substr(0, 8) + '-' + key.substr(8, 8) + '-' + key.substr(16, 8) + '-' + key.substr(24, 8);

    // Update preview elements
    const previewKeyElement = document.getElementById('preview-license-key');
    const displayElement = document.getElementById('license_key_display');
    const hiddenElement = document.getElementById('license_key_hidden');

    if (previewKeyElement) {
        previewKeyElement.textContent = key;
    }
    if (displayElement) {
        displayElement.value = key;
    }
    if (hiddenElement) {
        hiddenElement.value = key;
    }
}

function generateInitialPreview() {
    // Generate initial preview on page load
    generateLicenseKeyPreview();
    updateLicensePreview();
}

function updateLicensePreview() {
    // Update live preview based on form values
    const userSelect = document.getElementById('user_id');
    const productSelect = document.getElementById('product_id');
    const statusSelect = document.getElementById('status');
    const maxDomainsInput = document.getElementById('max_domains');

    // Update product name
    const previewProduct = document.getElementById('preview-product');
    if (previewProduct && productSelect) {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        previewProduct.textContent = selectedOption ? selectedOption.text : 'Product Name';
    }

    // Update user name
    const previewUser = document.getElementById('preview-user');
    if (previewUser && userSelect) {
        const selectedOption = userSelect.options[userSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const userText = selectedOption.text.split(' (')[0]; // Remove email part
            previewUser.textContent = userText;
        } else {
            previewUser.textContent = 'User Name';
        }
    }

    // Update status
    const previewStatus = document.getElementById('preview-status');
    if (previewStatus && statusSelect) {
        const selectedOption = statusSelect.options[statusSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            previewStatus.textContent = selectedOption.text;
            
            // Update status badge color
            previewStatus.className = 'badge mt-2';
            switch (selectedOption.value) {
                case 'active':
                    previewStatus.classList.add('bg-success');
                    break;
                case 'inactive':
                    previewStatus.classList.add('bg-secondary');
                    break;
                case 'suspended':
                    previewStatus.classList.add('bg-warning');
                    break;
                case 'expired':
                    previewStatus.classList.add('bg-danger');
                    break;
                default:
                    previewStatus.classList.add('bg-success');
            }
        } else {
            previewStatus.textContent = 'Active';
            previewStatus.className = 'badge bg-success mt-2';
        }
    }

    // Update max domains
    const previewDomains = document.getElementById('preview-domains');
    if (previewDomains && maxDomainsInput) {
        previewDomains.textContent = maxDomainsInput.value || '1';
    }
}

// ===========================================
// LICENSE VERIFIER WIDGET FUNCTIONALITY
// ===========================================

function initializeLicenseVerifier() {
    if (!document.getElementById('licenseForm')) return;
    
    setupLicenseVerifierEventListeners();
}

function setupLicenseVerifierEventListeners() {
    const licenseForm = document.getElementById('licenseForm');
    if (licenseForm) {
        licenseForm.addEventListener('submit', (e) => {
            e.preventDefault();
            handleLicenseFormSubmit();
        });
    }
}

async function handleLicenseFormSubmit() {
    const purchaseCode = document.getElementById('purchaseCode').value;
    const domain = document.getElementById('domain').value;

    // Show loading
    showLicenseLoading(true);

    try {
        const result = await verifyLicense(purchaseCode, domain);
        showLicenseResult(result);
    } catch (error) {
        showLicenseResult({
            valid: false,
            message: 'Verification failed: ' + error.message
        });
    }

    // Hide loading
    showLicenseLoading(false);
}

async function verifyLicense(purchaseCode, domain) {
    // First try Envato verification
    try {
        const envatoResult = await verifyWithEnvato(purchaseCode);
        if (envatoResult.valid) {
            return {
                valid: true,
                message: 'License verified via Envato',
                data: envatoResult.data
            };
        }
    } catch (e) {
        // Envato verification failed, trying our system
    }

    // Fallback to our system
    return await verifyWithOurSystem(purchaseCode, domain);
}

async function verifyWithEnvato(purchaseCode) {
    const response = await fetch(`https://api.envato.com/v3/market/author/sale?code=${encodeURIComponent(purchaseCode)}`, {
        headers: {
            'Authorization': 'Bearer YOUR_ENVATO_TOKEN',
            'User-Agent': 'LicenseVerifier/1.0'
        }
    });

    if (response.ok) {
        const data = await response.json();
        return { valid: true, data: data };
    }

    throw new Error('Envato verification failed');
}

async function verifyWithOurSystem(purchaseCode, domain) {
    const apiUrl = window.LICENSE_API_URL || '';
    const productSlug = window.PRODUCT_SLUG || '';
    const verificationKey = window.VERIFICATION_KEY || '';
    
    const response = await fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'User-Agent': 'LicenseVerifier/1.0'
        },
        body: new URLSearchParams({
            purchase_code: purchaseCode,
            product_slug: productSlug,
            domain: domain,
            verification_key: verificationKey
        })
    });

    if (response.ok) {
        const data = await response.json();
        return data;
    }

    throw new Error('Network error');
}

function showLicenseLoading(show) {
    const btnText = document.getElementById('btnText');
    const loading = document.getElementById('loading');
    const btn = document.getElementById('verifyBtn');

    if (btnText && loading && btn) {
        if (show) {
            btnText.style.display = 'none';
            loading.style.display = 'inline-block';
            btn.disabled = true;
        } else {
            btnText.style.display = 'inline';
            loading.style.display = 'none';
            btn.disabled = false;
        }
    }
}

function showLicenseResult(result) {
    const resultDiv = document.getElementById('result');
    if (resultDiv) {
        resultDiv.className = 'result ' + (result.valid ? 'success' : 'error');
        resultDiv.textContent = result.message;
        resultDiv.style.display = 'block';
    }
}

// ===========================================
// PRODUCT KB MANAGER FUNCTIONALITY
// ===========================================

function initializeProductKBManager() {
    if (!document.querySelector('.product-kb-manager')) return;
    
    setupProductKBEventListeners();
    initializeProductKBState();
}

function setupProductKBEventListeners() {
    const kbAccessRequired = document.getElementById('kb_access_required');
    const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
    const articleCheckboxes = document.querySelectorAll('.article-checkbox');
    const categorySearch = document.getElementById('category-search');
    const articleSearch = document.getElementById('article-search');

    // KB Access Required toggle
    if (kbAccessRequired) {
        kbAccessRequired.addEventListener('change', () => {
            toggleKBSections();
        });
    }

    // Category checkboxes
    categoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            updateSelectedSummary();
        });
    });

    // Article checkboxes
    articleCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            updateSelectedSummary();
        });
    });

    // Search functionality
    setupKBSearch(categorySearch, categoryCheckboxes, document.querySelector('.kb-categories-list'));
    setupKBSearch(articleSearch, articleCheckboxes, document.querySelector('.kb-articles-list'));

    // Select all functionality
    setupKBSelectAll(
        document.getElementById('select-all-categories'),
        categoryCheckboxes,
        document.getElementById('clear-categories')
    );

    setupKBSelectAll(
        document.getElementById('select-all-articles'),
        articleCheckboxes,
        document.getElementById('clear-articles')
    );
}

function initializeProductKBState() {
    toggleKBSections();
}

function toggleKBSections() {
    const kbAccessRequired = document.getElementById('kb_access_required');
    const kbAccessMessageSection = document.getElementById('kb-access-message-section');
    const kbCategoriesSection = document.getElementById('kb-categories-section');
    const kbArticlesSection = document.getElementById('kb-articles-section');
    
    const isRequired = kbAccessRequired ? kbAccessRequired.checked : false;
    
    if (kbAccessMessageSection) {
        kbAccessMessageSection.style.display = isRequired ? 'block' : 'none';
    }
    if (kbCategoriesSection) {
        kbCategoriesSection.style.display = isRequired ? 'block' : 'none';
    }
    if (kbArticlesSection) {
        kbArticlesSection.style.display = isRequired ? 'block' : 'none';
    }
    
    updateSelectedSummary();
}

function updateSelectedSummary() {
    const selectedCategories = document.querySelectorAll('.category-checkbox:checked').length;
    const selectedArticles = document.querySelectorAll('.article-checkbox:checked').length;
    const selectedSummary = document.getElementById('selected-summary');
    
    const selectedCategoriesCount = document.getElementById('selected-categories-count');
    const selectedArticlesCount = document.getElementById('selected-articles-count');
    
    if (selectedCategoriesCount) {
        selectedCategoriesCount.textContent = selectedCategories;
    }
    if (selectedArticlesCount) {
        selectedArticlesCount.textContent = selectedArticles;
    }
    
    const hasSelection = selectedCategories > 0 || selectedArticles > 0;
    if (selectedSummary) {
        selectedSummary.style.display = hasSelection ? 'block' : 'none';
    }
}

function setupKBSearch(input, checkboxes, container) {
    if (!input || !checkboxes || !container) return;

    input.addEventListener('input', () => {
        const searchTerm = input.value.toLowerCase();
        checkboxes.forEach(checkbox => {
            const label = checkbox.closest('label');
            if (label) {
                const text = label.textContent.toLowerCase();
                label.style.display = text.includes(searchTerm) ? 'flex' : 'none';
            }
        });
    });
}

function setupKBSelectAll(selectAllButton, checkboxes, clearButton) {
    if (!selectAllButton || !checkboxes || !clearButton) return;

    selectAllButton.addEventListener('click', () => {
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        updateSelectedSummary();
    });

    clearButton.addEventListener('click', () => {
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        updateSelectedSummary();
    });
}

// ===========================================
// USER LAYOUT FUNCTIONALITY
// ===========================================

function initializeUserLayout() {
    setupUserLayoutEventListeners();
    initializeUserMobileMenu();
    initializeUserDropdowns();
}

function setupUserLayoutEventListeners() {
    // Logout functionality
    setupUserLogoutHandlers();
    
    // Mobile menu functionality
    setupUserMobileMenuHandlers();
    
    // Dropdown functionality
    setupUserDropdownHandlers();
}

function setupUserLogoutHandlers() {
    const logoutElements = document.querySelectorAll('[data-action="logout"]');
    logoutElements.forEach(element => {
        element.addEventListener('click', (e) => {
            e.preventDefault();
            handleUserLogout();
        });
    });
}

function setupUserMobileMenuHandlers() {
    const mobileMenuToggle = document.querySelector('[data-mobile-menu-toggle]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    const mobileMenuClose = document.querySelector('.mobile-menu-close');
    const mobileMenuBackdrop = document.querySelector('.mobile-menu-backdrop');

    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', () => {
            toggleUserMobileMenu();
        });
    }

    if (mobileMenuClose) {
        mobileMenuClose.addEventListener('click', () => {
            closeUserMobileMenu();
        });
    }

    if (mobileMenuBackdrop) {
        mobileMenuBackdrop.addEventListener('click', () => {
            closeUserMobileMenu();
        });
    }

    // Close mobile menu when clicking on nav links
    const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
    mobileNavLinks.forEach(link => {
        link.addEventListener('click', () => {
            closeUserMobileMenu();
        });
    });
}

function setupUserDropdownHandlers() {
    const dropdownToggles = document.querySelectorAll('.user-dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            toggleUserDropdown(toggle);
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.user-dropdown')) {
            closeAllUserDropdowns();
        }
    });

    // Close dropdowns on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAllUserDropdowns();
        }
    });
}

function handleUserLogout() {
    const confirmMessage = document.querySelector('meta[name="logout-confirm"]')?.getAttribute('content') || 
                          'Are you sure you want to logout?';
    
    if (window.confirm && confirm(confirmMessage)) {
        const logoutForm = document.getElementById('logout-form');
        if (logoutForm) {
            logoutForm.submit();
        }
    }
}

function toggleUserMobileMenu() {
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    const mobileMenuBackdrop = document.querySelector('.mobile-menu-backdrop');
    
    if (mobileMenu && mobileMenuBackdrop) {
        const isOpen = !mobileMenu.classList.contains('hidden');
        
        if (isOpen) {
            closeUserMobileMenu();
        } else {
            openUserMobileMenu();
        }
    }
}

function openUserMobileMenu() {
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    const mobileMenuBackdrop = document.querySelector('.mobile-menu-backdrop');
    
    if (mobileMenu && mobileMenuBackdrop) {
        mobileMenu.classList.remove('hidden');
        mobileMenuBackdrop.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeUserMobileMenu() {
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    const mobileMenuBackdrop = document.querySelector('.mobile-menu-backdrop');
    
    if (mobileMenu && mobileMenuBackdrop) {
        mobileMenu.classList.add('hidden');
        mobileMenuBackdrop.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function toggleUserDropdown(toggle) {
    const dropdown = toggle.closest('.user-dropdown');
    const menu = dropdown?.querySelector('.user-dropdown-menu');
    
    if (menu) {
        const isOpen = menu.classList.contains('show');
        
        // Close all other dropdowns
        closeAllUserDropdowns();
        
        if (!isOpen) {
            menu.classList.add('show');
            toggle.setAttribute('aria-expanded', 'true');
        }
    }
}

function closeAllUserDropdowns() {
    const dropdownMenus = document.querySelectorAll('.user-dropdown-menu');
    const dropdownToggles = document.querySelectorAll('.user-dropdown-toggle');
    
    dropdownMenus.forEach(menu => {
        menu.classList.remove('show');
    });
    
    dropdownToggles.forEach(toggle => {
        toggle.setAttribute('aria-expanded', 'false');
    });
}

function initializeUserMobileMenu() {
    // Ensure mobile menu is closed on page load
    closeUserMobileMenu();
}

function initializeUserDropdowns() {
    // Ensure all dropdowns are closed on page load
    closeAllUserDropdowns();
}

// ===========================================
// INITIALIZE ALL NEW FUNCTIONALITY
// ===========================================

function initializeDashboard() {
    initializeTables();
    initializeForms();
    initializeTabs();
    initializeCopyButtons();
    initializeFilters();
    initializeTicketForm();
    initializeTableOfContents();
    initializeArticleFeatures();
    initializeLicenseStatus();
    initializeMobileMenu();
    initializeProfileTabs();
    
    // Initialize new functionality
    initializeLicenseCreate();
    initializeLicenseVerifier();
    initializeProductKBManager();
    initializeUserLayout();
    
    // Initialize Auth pages if present
    if (document.querySelector('.user-dashboard-container .user-form')) {
        initializeAuth();
    }
    
    // Initialize License Status page if present
    if (document.querySelector('#licenseCheckForm')) {
    }
    
    // Initialize Search enhancements if present
    if (document.querySelector('.kb-search-form')) {
        initializeSearchEnhancements();
    }
}

// Search Enhancements
function initializeSearchEnhancements() {
    const searchForm = document.querySelector('.kb-search-form');
    const searchInput = document.querySelector('.kb-search-input');
    const searchButton = document.querySelector('.kb-search-button');
    
    if (!searchForm || !searchInput) return;
    
    // Auto-focus search input
    if (searchInput && !searchInput.value) {
        searchInput.focus();
    }
    
    // Search form submission with loading state
    searchForm.addEventListener('submit', function(e) {
        if (searchButton) {
            searchButton.disabled = true;
            searchButton.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> <span class="button-text">' + (searchButton.querySelector('.button-text')?.textContent || 'Searching') + '...</span>';
        }
    });
    
    // Search input enhancements
    searchInput.addEventListener('input', function() {
        const value = this.value.trim();
        if (searchButton) {
            searchButton.disabled = value.length === 0;
        }
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
        
        // Escape to clear search
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.value = '';
            searchInput.blur();
        }
    });
    
    // Search suggestions (if implemented)
    initializeSearchSuggestions();
    
    // Search result animations
    initializeSearchResultAnimations();
}

// Search Suggestions
function initializeSearchSuggestions() {
    const searchInput = document.querySelector('.kb-search-input');
    if (!searchInput) return;
    
    // Add search suggestions container
    const suggestionsContainer = document.createElement('div');
    suggestionsContainer.className = 'kb-search-suggestions-container';
    suggestionsContainer.style.display = 'none';
    
    const searchWrapper = document.querySelector('.kb-search-wrapper');
    if (searchWrapper) {
        searchWrapper.appendChild(suggestionsContainer);
    }
    
    // Handle input with debouncing
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            suggestionsContainer.style.display = 'none';
            return;
        }
        
        searchTimeout = setTimeout(() => {
            // Here you could implement AJAX search suggestions
            // For now, we'll just show/hide based on input length
            if (query.length >= 2) {
                suggestionsContainer.style.display = 'block';
            }
        }, 300);
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchWrapper.contains(e.target)) {
            suggestionsContainer.style.display = 'none';
        }
    });
}

// Search Result Animations
function initializeSearchResultAnimations() {
    const searchResults = document.querySelectorAll('.user-kb-search-result');
    
    if (searchResults.length === 0) return;
    
    // Intersection Observer for scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    // Initialize animations
    searchResults.forEach((result, index) => {
        result.style.opacity = '0';
        result.style.transform = 'translateY(20px)';
        result.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(result);
    });
}

})();

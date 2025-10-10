/**
 * License Verification JavaScript
 * Handles license verification form interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const form = document.getElementById('licenseForm');
    const verifyBtn = document.getElementById('verifyBtn');
    const continueBtn = document.getElementById('continueBtn');
    const successMessage = document.getElementById('licenseSuccess');
    const purchaseCodeInput = document.getElementById('purchase_code');

    if (!form || !verifyBtn || !purchaseCodeInput) {
        console.error('License verification form elements not found');
        return;
    }

    // Allow any format - no automatic formatting
    purchaseCodeInput.addEventListener('input', function(e) {
        // Just trim whitespace, no other formatting
        e.target.value = e.target.value.trim();
    });

    // Handle form submission with AJAX
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const purchaseCode = purchaseCodeInput.value.trim();
        if (!purchaseCode) {
            showError('Please enter a purchase code');
            return;
        }

        // Validate purchase code format
        if (!isValidPurchaseCodeFormat(purchaseCode)) {
            showError('Please enter a valid purchase code (5-100 characters).');
            return;
        }

        // Show loading state
        setLoadingState(true);
        
        // Hide previous messages
        hideMessages();

        // Send AJAX request
        sendVerificationRequest(purchaseCode);
    });

    // Auto-focus on purchase code input
    purchaseCodeInput.focus();

    /**
     * Validate purchase code format
     */
    function isValidPurchaseCodeFormat(code) {
        // Accept any format - just check basic requirements
        return code && code.trim().length >= 5 && code.trim().length <= 100;
    }

    /**
     * Send verification request to server
     */
    function sendVerificationRequest(purchaseCode) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            showError('Security token not found. Please refresh the page and try again.');
            setLoadingState(false);
            return;
        }

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                purchase_code: purchaseCode
            })
        })
        .then(response => {
            if (response.headers.get('content-type')?.includes('application/json')) {
                return response.json();
            } else {
                return response.text();
            }
        })
        .then(data => {
            if (typeof data === 'object') {
                // JSON response
                handleJsonResponse(data);
            } else {
                // HTML response (fallback)
                handleHtmlResponse(data);
            }
        })
        .catch(error => {
            console.error('License verification error:', error);
            showError('Network error. Please check your connection and try again.');
            setLoadingState(false);
        });
    }

    /**
     * Handle JSON response
     */
    function handleJsonResponse(data) {
        if (data.success) {
            showSuccess();
            showContinueButton();
            // Optionally redirect after a delay with URL validation
            setTimeout(() => {
                if (data.redirect) {
                    // Validate redirect URL to prevent open redirect attacks
                    try {
                        const redirectUrl = new URL(data.redirect, window.location.origin);
                        // Only allow same-origin redirects
                        if (redirectUrl.origin === window.location.origin) {
                            // Additional security: Check for dangerous protocols
                            if (redirectUrl.protocol !== 'http:' && redirectUrl.protocol !== 'https:') {
                                console.warn('Invalid redirect protocol blocked:', redirectUrl.protocol);
                                return;
                            }
                            
                            // Additional security: Check for localhost/private IPs
                            if (redirectUrl.hostname === 'localhost' || redirectUrl.hostname === '127.0.0.1' || 
                                redirectUrl.hostname.startsWith('192.168.') || redirectUrl.hostname.startsWith('10.') || 
                                redirectUrl.hostname.startsWith('172.')) {
                                console.warn('Private network redirect blocked:', redirectUrl.hostname);
                                return;
                            }
                            
                            window.location.href = redirectUrl.toString();
                        } else {
                            console.warn('Invalid redirect URL blocked:', data.redirect);
                        }
                    } catch (e) {
                        console.warn('Invalid redirect URL format:', data.redirect);
                    }
                }
            }, 2000);
        } else {
            showError(data.message || 'License verification failed');
            setLoadingState(false);
        }
    }

    /**
     * Handle HTML response (fallback)
     */
    function handleHtmlResponse(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Check for errors
        const errors = doc.querySelectorAll('.license-error, .form-error, .alert-danger');
        if (errors.length > 0) {
            const errorMessage = errors[0].textContent.trim();
            showError(errorMessage);
            setLoadingState(false);
            return;
        }

        // Check for success
        if (html.includes('License verified successfully') || html.includes('install/requirements')) {
            showSuccess();
            showContinueButton();
        } else {
            showError('License verification failed. Please try again.');
            setLoadingState(false);
        }
    }

    /**
     * Show success message
     */
    function showSuccess() {
        if (successMessage) {
            successMessage.classList.add('show');
        }
        verifyBtn.style.display = 'none';
    }

    /**
     * Show continue button
     */
    function showContinueButton() {
        if (continueBtn) {
            continueBtn.classList.add('show');
        }
    }

    /**
     * Show error message
     */
    function showError(message) {
        // Sanitize and try to extract inner JSON "message" if the error string embeds a JSON payload
        if (typeof message === 'string') {
            // Pattern: Network error: ... {"valid":false,"message":"License is suspended","error_code":"LICENSE_SUSPENDED"}
            const jsonMatch = message.match(/(\{\s*"valid"[^}]+\})/);
            if (jsonMatch) {
                try {
                    const parsed = JSON.parse(jsonMatch[1]);
                    if (parsed && typeof parsed.message === 'string') {
                        message = parsed.message;
                    }
                } catch (e) {
                    // Ignore JSON parse errors; fallback to original message
                }
            }
            // If message still starts with a technical prefix, trim it
            if (/^Network error:/i.test(message)) {
                // Remove leading technical phrase keeping the user-facing part after JSON if any
                const parts = message.split(':');
                if (parts.length > 2) {
                    message = parts.slice(2).join(':').trim();
                }
            }
        }
        // Remove existing error messages
        const existingErrors = document.querySelectorAll('.license-error');
        existingErrors.forEach(error => error.remove());

        // Create new error message safely to prevent XSS
        const errorDiv = document.createElement('div');
        errorDiv.className = 'license-error';
        
        const icon = document.createElement('i');
        icon.className = 'fas fa-exclamation-circle';
        const messageSpan = document.createElement('span');
        messageSpan.textContent = message; // Safe text content
        
        errorDiv.appendChild(icon);
        errorDiv.appendChild(document.createTextNode(' '));
        errorDiv.appendChild(messageSpan);
        
        // Insert after the input field
        const inputGroup = purchaseCodeInput.closest('.license-form-group');
        if (inputGroup) {
            inputGroup.appendChild(errorDiv);
        }

        // Add error class to input
        purchaseCodeInput.classList.add('is-invalid');
        
        // Remove error class after user starts typing
        purchaseCodeInput.addEventListener('input', function() {
            purchaseCodeInput.classList.remove('is-invalid');
        }, { once: true });
    }

    /**
     * Hide all messages
     */
    function hideMessages() {
        if (successMessage) {
            successMessage.classList.remove('show');
        }
        if (continueBtn) {
            continueBtn.classList.remove('show');
        }
        const existingErrors = document.querySelectorAll('.license-error');
        existingErrors.forEach(error => error.remove());
        purchaseCodeInput.classList.remove('is-invalid');
    }

    /**
     * Set loading state
     */
    function setLoadingState(loading) {
        if (loading) {
            verifyBtn.disabled = true;
            verifyBtn.classList.add('loading');
            verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Verifying...</span>';
        } else {
            verifyBtn.disabled = false;
            verifyBtn.classList.remove('loading');
            verifyBtn.innerHTML = '<i class="fas fa-check"></i> <span>Verify License</span>';
        }
    }

    /**
     * Handle continue button click
     */
    if (continueBtn) {
        continueBtn.addEventListener('click', function(e) {
            // Add a small delay for better UX
            e.preventDefault();
            this.style.opacity = '0.7';
            
            // Use requestAnimationFrame for better performance and security
            let frameCount = 0;
            const maxFrames = 9; // ~150ms at 60fps
            const animate = () => {
                frameCount++;
                if (frameCount >= maxFrames) {
                    window.location.href = this.href;
                } else {
                    requestAnimationFrame(animate);
                }
            };
            requestAnimationFrame(animate);
        });
    }

    /**
     * Handle keyboard shortcuts
     */
    document.addEventListener('keydown', function(e) {
        // Enter key on input field
        if (e.key === 'Enter' && e.target === purchaseCodeInput) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
        
        // Escape key to clear errors
        if (e.key === 'Escape') {
            hideMessages();
        }
    });

    /**
     * Handle input validation on blur
     */
    purchaseCodeInput.addEventListener('blur', function() {
        const value = this.value.trim();
        if (value && !isValidPurchaseCodeFormat(value)) {
            showError('Please enter a valid purchase code (5-100 characters).');
        }
    });

    /**
     * Handle input validation on focus
     */
    purchaseCodeInput.addEventListener('focus', function() {
        this.classList.remove('is-invalid');
        const existingErrors = document.querySelectorAll('.license-error');
        existingErrors.forEach(error => error.remove());
    });

    // Add accessibility improvements
    purchaseCodeInput.setAttribute('aria-describedby', 'purchase-code-hint');
    purchaseCodeInput.setAttribute('aria-invalid', 'false');
    
    // Update aria-invalid when validation state changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const hasError = purchaseCodeInput.classList.contains('is-invalid');
                purchaseCodeInput.setAttribute('aria-invalid', hasError.toString());
            }
        });
    });
    
    observer.observe(purchaseCodeInput, { attributes: true });
});

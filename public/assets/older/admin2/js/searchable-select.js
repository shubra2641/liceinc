/**
 * Searchable Select Functionality
 * Adds search capability to user select dropdowns
 */

class SearchableSelect {
    constructor() {
        this.init();
    }

    init() {
        // Initialize Select2 on user select elements
        this.initializeSelect2();
        
        // Handle dynamic license loading for tickets
        this.handleLicenseLoading();
        
        // Handle invoice form interactions
        this.handleInvoiceForm();
    }

    getTranslations() {
        // Get translations from meta tags or global variables
        return {
            searchPlaceholder: window.translations?.searchPlaceholder || 'Search for user by name or email...',
            noResults: window.translations?.noResults || 'No results found',
            searching: window.translations?.searching || 'Searching...',
            inputTooShort: window.translations?.inputTooShort || 'Please enter at least one character',
            selectLicense: window.translations?.selectLicense || 'Select License',
            customInvoice: window.translations?.customInvoice || 'Custom Invoice (No License)',
            noLicensesFound: window.translations?.noLicensesFound || 'No licenses found for this user',
            licenseKey: window.translations?.licenseKey || 'License Key',
            expiresAt: window.translations?.expiresAt || 'Expires At',
            unknownProduct: window.translations?.unknownProduct || 'Unknown Product',
            notSpecified: window.translations?.notSpecified || 'Not Specified',
            active: window.translations?.active || 'Active',
            inactive: window.translations?.inactive || 'Inactive',
            suspended: window.translations?.suspended || 'Suspended',
            expired: window.translations?.expired || 'Expired'
        };
    }

    initializeSelect2() {
        // Get translations from meta tags or global variables
        const translations = this.getTranslations();
        
        // Initialize Select2 on all user select elements
        $('select[name="user_id"]').select2({
            placeholder: translations.searchPlaceholder,
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return translations.noResults;
                },
                searching: function() {
                    return translations.searching;
                },
                inputTooShort: function() {
                    return translations.inputTooShort;
                }
            },
            templateResult: this.formatUserResult.bind(this),
            templateSelection: this.formatUserSelection.bind(this),
            escapeMarkup: function(markup) {
                return markup;
            }
        });

        // Handle select change events
        $('select[name="user_id"]').on('select2:select', (e) => {
            this.handleUserSelection(e);
        });
    }

    formatUserResult(user) {
        if (user.loading) {
            return user.text;
        }

        if (!user.id) {
            return user.text;
        }

        // Get data from option element
        const $option = $(user.element);
        const userName = $option.data('name') || $option.text().split(' (')[0];
        const userEmail = $option.data('email') || $option.text().split(' (')[1]?.replace(')', '') || '';

        const $container = $(
            '<div class="select2-result-user">' +
                '<div class="user-info">' +
                    '<div class="user-name">' + userName + '</div>' +
                    '<div class="user-email text-muted small">' + userEmail + '</div>' +
                '</div>' +
            '</div>'
        );

        return $container;
    }

    formatUserSelection(user) {
        if (!user.id) {
            return user.text;
        }

        // Get data from option element
        const $option = $(user.element);
        const userName = $option.data('name') || $option.text().split(' (')[0];
        const userEmail = $option.data('email') || $option.text().split(' (')[1]?.replace(')', '') || '';

        return userName + ' (' + userEmail + ')';
    }

    handleUserSelection(e) {
        const userId = e.params.data.id;
        const $option = $(e.params.data.element);
        
        // Get user data from option attributes
        const userData = {
            id: userId,
            name: $option.data('name') || $option.text().split(' (')[0],
            email: $option.data('email') || $option.text().split(' (')[1]?.replace(')', '') || '',
            licenses: $option.data('licenses') || []
        };
        
        // Update license information for tickets page
        if (window.location.pathname.includes('tickets/create')) {
            this.updateLicenseInfo(userId, userData);
        }
        
        // Update license dropdown for invoices page
        if (window.location.pathname.includes('invoices/create')) {
            this.updateLicenseDropdown(userId);
        }
        
        // Update preview sections
        this.updatePreviews(userData);
    }

    updateLicenseInfo(userId, userData) {
        const licenseInfo = document.getElementById('license-info');
        const licenseDetails = document.getElementById('license-details');
        
        if (!licenseInfo || !licenseDetails) return;

        const translations = this.getTranslations();

        // Show license info section
        licenseInfo.classList.remove('hidden-field');
        
        // Parse licenses data if it's a string
        let licenses = userData.licenses;
        if (typeof licenses === 'string') {
            try {
                licenses = JSON.parse(licenses);
            } catch (e) {
                licenses = [];
            }
        }
        
        if (licenses && licenses.length > 0) {
            let licenseHtml = '<div class="row">';
            
            licenses.forEach(license => {
                const statusClass = this.getStatusClass(license.status);
                const expiresAt = license.expires_at ? new Date(license.expires_at).toLocaleDateString() : translations.notSpecified;
                const productName = license.product ? license.product.name : (license.product_name || translations.unknownProduct);
                const licenseKey = license.license_key ? license.license_key.substring(0, 20) + '...' : translations.notSpecified;
                
                licenseHtml += `
                    <div class="col-md-6 mb-2">
                        <div class="card border">
                            <div class="card-body p-2">
                                <h6 class="card-title mb-1">${productName}</h6>
                                <p class="card-text small mb-1">
                                    <strong>${translations.licenseKey}:</strong> ${licenseKey}
                                </p>
                                <p class="card-text small mb-1">
                                    <strong>${translations.expiresAt}:</strong> ${expiresAt}
                                </p>
                                <span class="badge ${statusClass}">${this.getStatusText(license.status)}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            licenseHtml += '</div>';
            licenseDetails.innerHTML = licenseHtml;
        } else {
            licenseDetails.innerHTML = `<p class="text-muted text-center">${translations.noLicensesFound}</p>`;
        }
    }

    updateLicenseDropdown(userId) {
        const licenseSelect = document.getElementById('license_id');
        if (!licenseSelect) return;

        const translations = this.getTranslations();

        // Clear existing options except the first one
        licenseSelect.innerHTML = `<option value="">${translations.selectLicense}</option><option value="custom">${translations.customInvoice}</option>`;

        // Fetch user licenses via AJAX
        fetch(`/api/admin/user-licenses/${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.licenses) {
                    data.licenses.forEach(license => {
                        const option = document.createElement('option');
                        option.value = license.id;
                        option.textContent = `${license.product_name} - ${license.license_key.substring(0, 20)}...`;
                        option.dataset.amount = license.product_price || 0;
                        licenseSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching licenses:', error);
            });
    }

    updatePreviews(userData) {
        // Update ticket preview
        const previewSubject = document.getElementById('preview-subject');
        if (previewSubject && userData.name) {
            previewSubject.textContent = userData.name;
        }

        // Update license preview
        const previewUser = document.getElementById('preview-user');
        if (previewUser && userData.name) {
            previewUser.textContent = userData.name;
        }

        // Update invoice preview
        const previewCustomer = document.getElementById('preview-customer');
        if (previewCustomer && userData.name) {
            previewCustomer.textContent = userData.name;
        }
    }

    handleLicenseLoading() {
        // This method handles license loading for tickets page
        const userSelect = document.querySelector('select[name="user_id"]');
        if (userSelect && userSelect.dataset.action === 'update-user-licenses') {
            // The license loading is handled by the select2 change event
        }
    }

    handleInvoiceForm() {
        // Handle invoice form specific interactions
        const statusSelect = document.getElementById('status');
        const paidAtGroup = document.getElementById('paid_at_group');
        
        if (statusSelect && paidAtGroup) {
            statusSelect.addEventListener('change', (e) => {
                if (e.target.value === 'paid') {
                    paidAtGroup.classList.remove('hidden-field');
                } else {
                    paidAtGroup.classList.add('hidden-field');
                }
            });
        }

        // Handle custom invoice fields
        const typeSelect = document.getElementById('type');
        const customFields = document.getElementById('custom_invoice_fields');
        
        if (typeSelect && customFields) {
            typeSelect.addEventListener('change', (e) => {
                if (e.target.value === 'custom') {
                    customFields.classList.remove('hidden-field');
                } else {
                    customFields.classList.add('hidden-field');
                }
            });
        }
    }

    getStatusClass(status) {
        const statusClasses = {
            'active': 'bg-success',
            'inactive': 'bg-secondary',
            'suspended': 'bg-warning',
            'expired': 'bg-danger'
        };
        return statusClasses[status] || 'bg-secondary';
    }

    getStatusText(status) {
        const translations = this.getTranslations();
        const statusTexts = {
            'active': translations.active,
            'inactive': translations.inactive,
            'suspended': translations.suspended,
            'expired': translations.expired
        };
        return statusTexts[status] || status;
    }
}

// Initialize when document is ready
$(document).ready(function() {
    // Wait a bit for all elements to be loaded
    setTimeout(function() {
        new SearchableSelect();
    }, 100);
});

// Add CSS for better styling
const style = document.createElement('style');
style.textContent = `
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #007bff;
        color: white;
    }
    
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    
    .select2-dropdown {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }
    
    .select2-result-user .user-info {
        padding: 5px 0;
    }
    
    .select2-result-user .user-name {
        font-weight: 500;
        color: #333;
    }
    
    .select2-result-user .user-email {
        font-size: 0.85em;
        margin-top: 2px;
    }
    
    .hidden-field {
        display: none !important;
    }
`;
document.head.appendChild(style);
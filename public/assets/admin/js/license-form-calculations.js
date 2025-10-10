/**
 * License Form Calculations
 * 
 * This script handles automatic calculations for license forms
 * including license type inheritance, domain count, and expiry dates.
 */

// Constants to avoid magic numbers
const HOURS_PER_DAY = 24;
const MINUTES_PER_HOUR = 60;
const SECONDS_PER_MINUTE = 60;
const MILLISECONDS_PER_SECOND = 1000;
const MILLISECONDS_PER_DAY = HOURS_PER_DAY * MINUTES_PER_HOUR * SECONDS_PER_MINUTE * MILLISECONDS_PER_SECOND;

// License type domain limits
const LICENSE_DOMAIN_LIMITS = {
    single: 1,
    multi: 5,
    developer: 10,
    extended: 20,
    default: 1
};

// Array index constants
const ARRAY_INDEX_OFFSET = 0;

document.addEventListener('DOMContentLoaded', function() {
    
    // Check if we're on a license form page
    const productSelect = document.getElementById('product_id');
    const licenseTypeSelect = document.getElementById('license_type');
    const maxDomainsInput = document.getElementById('max_domains');
    const licenseExpiresAtInput = document.getElementById('license_expires_at');
    const supportExpiresAtInput = document.getElementById('support_expires_at');
    
    // Debug logging (removed for production)
    // console.log('Elements found:', {
    //     productSelect: Boolean(productSelect),
    //     licenseTypeSelect: Boolean(licenseTypeSelect),
    //     maxDomainsInput: Boolean(maxDomainsInput),
    //     licenseExpiresAtInput: Boolean(licenseExpiresAtInput),
    //     supportExpiresAtInput: Boolean(supportExpiresAtInput)
    // });
    
    // If elements don't exist, exit early
    if (!productSelect || !licenseTypeSelect || !maxDomainsInput) {
        // console.log('Required elements not found, exiting');
        return;
    }
    
    // Product data cache
    const productData = {};
    
    /**
     * Load product data when product is selected
     */
    function loadProductData(productId) {
        if (!productId) {
            clearCalculations();
            return;
        }
        
        // If we already have the data, use it
        if (productData[productId]) {
            applyProductData(productData[productId]);
            return;
        }
        
        // Fetch product data via AJAX
        fetch(`/admin/products/${productId}/data`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            productData[productId] = data;
            applyProductData(data);
        })
        .catch(() => {
            // console.error('Error loading product data:', error);
            // Handle error silently or show user-friendly message
        });
    }
    
    /**
     * Apply product data to form fields
     */
    function applyProductData(data) {
        // console.log('Applying product data:', data);
        
        // Set license type
        if (data.license_type) {
            licenseTypeSelect.value = data.license_type;
            // console.log('Set license type to:', data.license_type);
        }
        
        // Calculate max domains based on license type
        calculateMaxDomains(data.license_type);
        
        // Calculate expiry dates
        calculateExpiryDates(data);
    }
    
    /**
     * Calculate max domains based on license type
     */
    function calculateMaxDomains(licenseType) {
        const maxDomains = LICENSE_DOMAIN_LIMITS[licenseType] || LICENSE_DOMAIN_LIMITS.default;
        maxDomainsInput.value = maxDomains;
    }
    
    /**
     * Calculate expiry dates based on product data
     */
    function calculateExpiryDates(data) {
        const today = new Date();
        
        // Calculate license expiry date
        if (data.duration_days && licenseExpiresAtInput) {
            const licenseExpiry = new Date(today.getTime() + (data.duration_days * MILLISECONDS_PER_DAY));
            licenseExpiresAtInput.value = licenseExpiry.toISOString().split('T')[ARRAY_INDEX_OFFSET];
        }
        
        // Calculate support expiry date
        if (data.support_days && supportExpiresAtInput) {
            const supportExpiry = new Date(today.getTime() + (data.support_days * MILLISECONDS_PER_DAY));
            supportExpiresAtInput.value = supportExpiry.toISOString().split('T')[ARRAY_INDEX_OFFSET];
        }
    }
    
    /**
     * Clear all calculated fields
     */
    function clearCalculations() {
        licenseTypeSelect.value = '';
        maxDomainsInput.value = LICENSE_DOMAIN_LIMITS.default;
        if (licenseExpiresAtInput) licenseExpiresAtInput.value = '';
        if (supportExpiresAtInput) supportExpiresAtInput.value = '';
    }
    
    /**
     * Update preview when form changes
     */
    function updatePreview() {
        const productSelect = document.getElementById('product_id');
        const userSelect = document.getElementById('user_id');
        const statusSelect = document.getElementById('status');
        
        if (productSelect && productSelect.value) {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const productName = selectedOption.text;
            document.getElementById('preview-product').textContent = productName;
        }
        
        if (userSelect && userSelect.value) {
            const selectedOption = userSelect.options[userSelect.selectedIndex];
            const userName = selectedOption.text.split(' (')[ARRAY_INDEX_OFFSET];
            document.getElementById('preview-user').textContent = userName;
        }
        
        if (statusSelect && statusSelect.value) {
            const statusText = statusSelect.options[statusSelect.selectedIndex].text;
            const statusBadge = document.getElementById('preview-status');
            statusBadge.textContent = statusText;
            
            // Update badge color based on status
            statusBadge.className = 'badge mt-2';
            switch (statusSelect.value) {
                case 'active':
                    statusBadge.classList.add('bg-success');
                    break;
                case 'inactive':
                    statusBadge.classList.add('bg-secondary');
                    break;
                case 'suspended':
                    statusBadge.classList.add('bg-warning');
                    break;
                case 'expired':
                    statusBadge.classList.add('bg-danger');
                    break;
                default:
                    statusBadge.classList.add('bg-primary');
            }
        }
        
        // Update domains preview
        if (maxDomainsInput) {
            document.getElementById('preview-domains').textContent = maxDomainsInput.value;
        }
    }
    
    // Event listeners
    productSelect.addEventListener('change', function() {
        // console.log('Product changed to:', this.value);
        loadProductData(this.value);
        updatePreview();
    });
    
    licenseTypeSelect.addEventListener('change', function() {
        calculateMaxDomains(this.value);
        updatePreview();
    });
    
    // Add event listeners for preview updates
    const userSelect = document.getElementById('user_id');
    const statusSelect = document.getElementById('status');
    
    if (userSelect) {
        userSelect.addEventListener('change', updatePreview);
    }
    
    if (statusSelect) {
        statusSelect.addEventListener('change', updatePreview);
    }
    
    // Initial preview update
    updatePreview();
});

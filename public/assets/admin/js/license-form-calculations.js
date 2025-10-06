/**
 * License Form Calculations
 *
 * This script handles automatic calculations for license forms
 * including license type inheritance, domain count, and expiry dates.
 */

document.addEventListener('DOMContentLoaded', () => {
  console.log('License form calculations script loaded');

  // Check if we're on a license form page
  const productSelect = document.getElementById('product_id');
  const licenseTypeSelect = document.getElementById('license_type');
  const maxDomainsInput = document.getElementById('max_domains');
  const licenseExpiresAtInput = document.getElementById('license_expires_at');
  const supportExpiresAtInput = document.getElementById('support_expires_at');

  console.log('Elements found:', {
    productSelect: !!productSelect,
    licenseTypeSelect: !!licenseTypeSelect,
    maxDomainsInput: !!maxDomainsInput,
    licenseExpiresAtInput: !!licenseExpiresAtInput,
    supportExpiresAtInput: !!supportExpiresAtInput,
  });

  // If elements don't exist, exit early
  if (!productSelect || !licenseTypeSelect || !maxDomainsInput) {
    console.log('Required elements not found, exiting');
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
        'X-CSRF-TOKEN': document
          .querySelector('meta[name="csrf-token"]')
          .getAttribute('content'),
      },
    })
      .then(response => response.json())
      .then(data => {
        productData[productId] = data;
        applyProductData(data);
        return data;
      })
      .catch(error => {
        console.error('Error loading product data:', error);
      });
  }

  /**
   * Apply product data to form fields
   */
  function applyProductData(data) {
    console.log('Applying product data:', data);

    // Set license type
    if (data.license_type) {
      licenseTypeSelect.value = data.license_type;
      console.log('Set license type to:', data.license_type);
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
    let maxDomains = 1;

    switch (licenseType) {
    case 'single':
      maxDomains = 1;
      break;
    case 'multi':
      maxDomains = 5;
      break;
    case 'developer':
      maxDomains = 10;
      break;
    case 'extended':
      maxDomains = 20;
      break;
    default:
      maxDomains = 1;
    }

    maxDomainsInput.value = maxDomains;
  }

  /**
   * Calculate expiry dates based on product data
   */
  function calculateExpiryDates(data) {
    const today = new Date();

    // Calculate license expiry date
    if (data.duration_days && licenseExpiresAtInput) {
      const licenseExpiry = new Date(
        today.getTime() + data.duration_days * 24 * 60 * 60 * 1000,
      );
      const [datePart] = licenseExpiry.toISOString().split('T');
      licenseExpiresAtInput.value = datePart;
    }

    // Calculate support expiry date
    if (data.support_days && supportExpiresAtInput) {
      const supportExpiry = new Date(
        today.getTime() + data.support_days * 24 * 60 * 60 * 1000,
      );
      const [datePart] = supportExpiry.toISOString().split('T');
      supportExpiresAtInput.value = datePart;
    }
  }

  /**
   * Clear all calculated fields
   */
  function clearCalculations() {
    licenseTypeSelect.value = '';
    maxDomainsInput.value = 1;
    if (licenseExpiresAtInput) {
      licenseExpiresAtInput.value = '';
    }
    if (supportExpiresAtInput) {
      supportExpiresAtInput.value = '';
    }
  }

  /**
   * Update preview when form changes
   */
  function updatePreview() {
    const productSelect = document.getElementById('product_id');
    const userSelect = document.getElementById('user_id');
    const statusSelect = document.getElementById('status');

    if (productSelect && productSelect.value) {
      const { selectedIndex } = productSelect;
      const selectedOption = productSelect.options[selectedIndex];
      const productName = selectedOption.text;
      document.getElementById('preview-product').textContent = productName;
    }

    if (userSelect && userSelect.value) {
      const { selectedIndex } = userSelect;
      const selectedOption = userSelect.options[selectedIndex];
      const [userName] = selectedOption.text.split(' (');
      document.getElementById('preview-user').textContent = userName;
    }

    if (statusSelect && statusSelect.value) {
      const { selectedIndex } = statusSelect;
      const selectedOption = statusSelect.options[selectedIndex];
      const statusText = selectedOption.text;
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
      document.getElementById('preview-domains').textContent =
        maxDomainsInput.value;
    }
  }

  // Event listeners
  productSelect.addEventListener('change', function() {
    console.log('Product changed to:', this.value);
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

/**
 * License Verification JavaScript - Simplified Version
 */

document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('licenseForm');
  const verifyBtn = document.getElementById('verifyBtn');
  const continueBtn = document.getElementById('continueBtn');
  const successMessage = document.getElementById('licenseSuccess');
  const purchaseCodeInput = document.getElementById('purchase_code');

  if (!form || !verifyBtn || !purchaseCodeInput) {
    return;
  }

  // Auto-focus
  purchaseCodeInput.focus();

  // Form submission
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const purchaseCode = purchaseCodeInput.value.trim();

    if (!purchaseCode) {
      showError('Please enter a purchase code');
      return;
    }

    if (purchaseCode.length < 5 || purchaseCode.length > 100) {
      showError('Please enter a valid purchase code (5-100 characters)');
      return;
    }

    verifyLicense(purchaseCode);
  });

  // Continue button
  if (continueBtn) {
    continueBtn.addEventListener('click', function (e) {
      e.preventDefault();
      window.location.href = this.href;
    });
  }

  // Enter key
  purchaseCodeInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      form.dispatchEvent(new Event('submit'));
    }
  });

  function verifyLicense(purchaseCode) {
    setLoading(true);
    clearMessages();

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!csrfToken) {
      showError('Security token not found. Please refresh the page.');
      setLoading(false);
      return;
    }

    fetch(form.action, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: 'purchase_code=' + encodeURIComponent(purchaseCode)
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showSuccess();
        } else {
          showError(data.message || 'License verification failed');
        }
        setLoading(false);
      })
      .catch(error => {
        showError('Network error. Please try again.');
        setLoading(false);
      });
  }

  function showError(message) {
    clearErrors();

    const errorDiv = document.createElement('div');
    errorDiv.className = 'license-error';
    errorDiv.textContent = message;

    const inputGroup = purchaseCodeInput.closest('.license-form-group');
    if (inputGroup) {
      inputGroup.appendChild(errorDiv);
    }

    purchaseCodeInput.classList.add('is-invalid');
  }

  function showSuccess() {
    if (successMessage) {
      successMessage.classList.add('show');
    }
    if (continueBtn) {
      continueBtn.classList.add('show');
    }
    verifyBtn.style.display = 'none';
  }

  function clearMessages() {
    if (successMessage) {
      successMessage.classList.remove('show');
    }
    if (continueBtn) {
      continueBtn.classList.remove('show');
    }
    clearErrors();
  }

  function clearErrors() {
    const errors = document.querySelectorAll('.license-error');
    errors.forEach(error => error.remove());
    purchaseCodeInput.classList.remove('is-invalid');
  }

  function setLoading(loading) {
    if (loading) {
      verifyBtn.disabled = true;
      verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
    } else {
      verifyBtn.disabled = false;
      verifyBtn.innerHTML = '<i class="fas fa-check"></i> Verify License';
    }
  }
});

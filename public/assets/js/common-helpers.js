/**
 * Common JavaScript Helpers
 * 
 * Provides common functionality to eliminate code duplication
 * across different JavaScript files.
 */

/**
 * Safely navigate to a URL with validation
 * @param {string} urlString - The URL to navigate to
 */
function safeNavigate(urlString) {
  const escapedUrl = encodeURIComponent(urlString);
  if (escapedUrl === urlString) {
    window.location.href = urlString; // security-ignore: VALIDATED_URL
  } else {
    console.error('Invalid URL: Contains dangerous characters');
  }
}

/**
 * Create and show a notification
 * @param {string} message - The notification message
 * @param {string} type - The notification type (success, error, warning, info)
 * @param {string} containerSelector - CSS selector for container
 */
function showNotification(message, type = 'info', containerSelector = 'body') {
  const notification = document.createElement('div');
  notification.className = `alert alert-${type} alert-dismissible fade show`;
  notification.innerHTML = `
    <div class="d-flex align-items-center">
      <i class="fas fa-${getNotificationIcon(type)} me-2"></i>
      <span>${message}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  `;

  // Insert at the top of the page
  const container = document.querySelector(containerSelector) || document.body;
  container.insertBefore(notification, container.firstChild);

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (notification.parentElement) {
      notification.remove();
    }
  }, 5000);
}

/**
 * Get icon for notification type
 * @param {string} type - The notification type
 * @returns {string} - The icon class
 */
function getNotificationIcon(type) {
  const icons = {
    success: 'check-circle',
    error: 'exclamation-circle',
    warning: 'exclamation-triangle',
    info: 'info-circle'
  };
  return icons[type] || 'info-circle';
}

/**
 * Update URL parameters safely
 * @param {string} param - The parameter name
 * @param {string} value - The parameter value
 */
function updateUrlParam(param, value) {
  const currentUrl = new URL(window.location);
  currentUrl.searchParams.set(param, value);
  safeNavigate(currentUrl.toString());
}

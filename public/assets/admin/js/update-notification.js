/**
 * Update Notification JavaScript
 * Handles update notification display and dismissal
 */

class UpdateNotification {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
    this.checkForUpdates();
  }

  bindEvents() {
    // Bind global functions
    window.dismissUpdateNotification = () => this.dismiss();
    window.dismissUpdateNotificationPermanently = () =>
      this.dismissPermanently();
  }

  checkForUpdates() {
    // Check if update notification should be shown
    const dismissed = localStorage.getItem('update-notification-dismissed');
    const dismissedPermanently = localStorage.getItem(
      'update-notification-dismissed-permanently',
    );

    if (!dismissedPermanently && !dismissed) {
      this.show();
    }
  }

  show() {
    const notification = document.getElementById('update-notification');
    if (notification) {
      notification.style.display = 'block';
      notification.classList.add('show');
    }
  }

  dismiss() {
    const notification = document.getElementById('update-notification');
    if (notification) {
      notification.style.display = 'none';
      localStorage.setItem('update-notification-dismissed', 'true');
    }
  }

  dismissPermanently() {
    const notification = document.getElementById('update-notification');
    if (notification) {
      notification.style.display = 'none';
      localStorage.setItem('update-notification-dismissed-permanently', 'true');
    }
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  // eslint-disable-next-line no-new
  new UpdateNotification();
});

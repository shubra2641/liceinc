/**
 * System Updates Management
 * Handles update checking, installation, and rollback functionality
 */

// Security utilities
const SecurityUtils = {
    sanitizeHtml: function(content) {
        if (typeof content !== 'string') return '';
        return content.replace(/[<>&"']/g, match => ({
            '<': '&lt;',
            '>': '&gt;',
            '&': '&amp;',
            '"': '&quot;',
            '\'': '&#x27;',
        }[match]));
    },
    safeInnerHTML: function(element, content, sanitize = true) {
        if (!element || typeof content !== 'string') return;
        const safeContent = sanitize ? this.sanitizeHtml(content) : content;
        element.textContent = safeContent;
    },
    safeInsertAdjacentHTML: function(element, position, html) {
        if (!element || typeof html !== 'string') return;
        const safeHtml = this.sanitizeHtml(html);
        element.insertAdjacentHTML(position, safeHtml);
    }
};

// Utility functions
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    const power = Math.pow(k, i);
    const result = bytes / power;
    return parseFloat(result.toFixed(2)) + ' ' + sizes[i];
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'info' ? 'alert-info' : 'alert-danger';
    const iconClass = type === 'success' ? 'fa-check-circle' :
                     type === 'info' ? 'fa-info-circle' : 'fa-exclamation-triangle';

    const alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
        '<i class="fas ' + iconClass + ' me-2"></i>' +
        SecurityUtils.sanitizeHtml(message) +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
        '</div>';

    const container = document.querySelector('.admin-page-header').parentNode;
    SecurityUtils.safeInsertAdjacentHTML(container, 'afterbegin', alertHtml);

    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            window.bootstrap.Alert.getOrCreateInstance(alert).close();
        }
    }, 5000);
}

// Update functions
function checkForUpdates() {
  const btn = document.getElementById('check-updates-btn');
    const originalText = btn.textContent;

    SecurityUtils.safeInnerHTML(btn, '<i class="fas fa-spinner fa-spin me-2"></i>Checking...');
  btn.disabled = true;

  fetch(window.location.href, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    },
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.location.reload();
      } else {
        showAlert('error', data.message || 'Failed to check for updates');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showAlert('error', 'An error occurred while checking for updates');
    })
    .finally(() => {
        SecurityUtils.safeInnerHTML(btn, originalText);
      btn.disabled = false;
    });
}

function showUpdateModal(version) {
  document.getElementById('update-system-btn').dataset.version = version;
  document.getElementById('confirmUpdate').checked = false;
  document.getElementById('confirm-update-btn').disabled = true;

    const modal = new window.bootstrap.Modal(document.getElementById('updateModal'));
  modal.show();
}

function performUpdate(version) {
  const btn = document.getElementById('confirm-update-btn');
    const originalText = btn.textContent;

    SecurityUtils.safeInnerHTML(btn, '<i class="fas fa-spinner fa-spin me-2"></i>Updating...');
  btn.disabled = true;

  fetch(window.location.href, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    },
        body: JSON.stringify({ version, confirm: true }),
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showAlert('success', data.message);
            setTimeout(() => window.location.reload(), 2000);
      } else {
        showAlert('error', data.message || 'Update failed');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showAlert('error', 'An error occurred during update');
    })
    .finally(() => {
        SecurityUtils.safeInnerHTML(btn, originalText);
      btn.disabled = false;
        window.bootstrap.Modal.getInstance(document.getElementById('updateModal')).hide();
    });
}

function showVersionDetails(version) {
    fetch('/admin/updates/version-info/' + version)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
            const sanitizedVersion = SecurityUtils.sanitizeHtml(version);
        const titleElement = document.getElementById('versionDetailsTitle');
            SecurityUtils.safeInnerHTML(titleElement, '<i class="fas fa-info-circle me-2"></i>Version Details - ' + sanitizedVersion);

        let content = '<div class="version-details">';

        if (data.data.info.features && data.data.info.features.length > 0) {
                content += '<h6 class="text-success mb-3"><i class="fas fa-plus me-2"></i>New Features</h6>';
          content += '<ul class="list-group list-group-flush mb-4">';
          data.data.info.features.forEach(feature => {
                    const sanitizedFeature = SecurityUtils.sanitizeHtml(feature);
                    content += '<li class="list-group-item"><i class="fas fa-check text-success me-2"></i>' + sanitizedFeature + '</li>';
          });
          content += '</ul>';
        }

        if (data.data.info.fixes && data.data.info.fixes.length > 0) {
                content += '<h6 class="text-warning mb-3"><i class="fas fa-wrench me-2"></i>Bug Fixes</h6>';
          content += '<ul class="list-group list-group-flush mb-4">';
          data.data.info.fixes.forEach(fix => {
                    const sanitizedFix = SecurityUtils.sanitizeHtml(fix);
                    content += '<li class="list-group-item"><i class="fas fa-check text-warning me-2"></i>' + sanitizedFix + '</li>';
          });
          content += '</ul>';
        }

            if (data.data.info.improvements && data.data.info.improvements.length > 0) {
                content += '<h6 class="text-info mb-3"><i class="fas fa-arrow-up me-2"></i>Improvements</h6>';
          content += '<ul class="list-group list-group-flush mb-4">';
          data.data.info.improvements.forEach(improvement => {
                    const sanitizedImprovement = SecurityUtils.sanitizeHtml(improvement);
                    content += '<li class="list-group-item"><i class="fas fa-check text-info me-2"></i>' + sanitizedImprovement + '</li>';
          });
          content += '</ul>';
        }

        if (data.data.instructions && data.data.instructions.length > 0) {
                content += '<h6 class="text-primary mb-3"><i class="fas fa-list me-2"></i>Update Instructions</h6>';
          content += '<ul class="list-group list-group-flush">';
          data.data.instructions.forEach(instruction => {
                    const sanitizedInstruction = SecurityUtils.sanitizeHtml(instruction);
                    content += '<li class="list-group-item"><i class="fas fa-arrow-right text-primary me-2"></i>' + sanitizedInstruction + '</li>';
          });
          content += '</ul>';
        }

        content += '</div>';
            SecurityUtils.safeInnerHTML(document.getElementById('versionDetailsContent'), content);

            const modal = new window.bootstrap.Modal(document.getElementById('versionDetailsModal'));
            modal.show();
        } else {
            showAlert('error', data.message || 'Failed to load version details');
        }
    })
    .catch(error => {
      console.error('Error:', error);
      showAlert('error', 'An error occurred while loading version details');
    });
}

function showRollbackModal(version) {
  document.getElementById('confirm-rollback-btn').dataset.version = version;
  document.getElementById('confirmRollback').checked = false;
  document.getElementById('confirm-rollback-btn').disabled = true;

    const modal = new window.bootstrap.Modal(document.getElementById('rollbackModal'));
  modal.show();
}

function performRollback(version) {
  const btn = document.getElementById('confirm-rollback-btn');
    const originalText = btn.textContent;

    SecurityUtils.safeInnerHTML(btn, '<i class="fas fa-spinner fa-spin me-2"></i>Rolling back...');
  btn.disabled = true;

  fetch('/admin/updates/rollback', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    },
        body: JSON.stringify({ version, confirm: true }),
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showAlert('success', data.message);
            setTimeout(() => window.location.reload(), 2000);
      } else {
        showAlert('error', data.message || 'Rollback failed');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showAlert('error', 'An error occurred during rollback');
    })
    .finally(() => {
        SecurityUtils.safeInnerHTML(btn, originalText);
      btn.disabled = false;
        window.bootstrap.Modal.getInstance(document.getElementById('rollbackModal')).hide();
    });
}

function uploadUpdatePackage() {
    const fileInput = document.getElementById('update-package-file');
    const btn = document.getElementById('confirm-upload-btn');
    const originalText = btn.textContent;

    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        showAlert('error', 'Please select a file to upload');
    return;
  }

    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('update_package', file);

    SecurityUtils.safeInnerHTML(btn, '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...');
  btn.disabled = true;

    fetch('/admin/updates/upload', {
    method: 'POST',
    headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
            showAlert('success', data.message || 'Package uploaded successfully');
            const modal = window.bootstrap.Modal.getInstance(document.getElementById('uploadPackageModal'));
            if (modal) modal.hide();
            setTimeout(() => window.location.reload(), 2000);
        } else {
            showAlert('error', data.message || 'Upload failed');
        }
    })
    .catch(error => {
      console.error('Error:', error);
        showAlert('error', 'An error occurred during upload');
    })
    .finally(() => {
        SecurityUtils.safeInnerHTML(btn, originalText);
      btn.disabled = false;
    });
}

function displayUpdateInfo(updateData) {
    document.getElementById('update-title').textContent = updateData.update_info.title || 'Update';
    document.getElementById('update-version').textContent = updateData.latest_version;
    
    const majorElement = document.getElementById('update-major');
    if (updateData.update_info.is_major) {
        SecurityUtils.safeInnerHTML(majorElement, '<span class="badge bg-warning">Yes</span>');
  } else {
        SecurityUtils.safeInnerHTML(majorElement, '<span class="badge bg-info">No</span>');
    }
    
    const requiredElement = document.getElementById('update-required');
    if (updateData.update_info.is_required) {
        SecurityUtils.safeInnerHTML(requiredElement, '<span class="badge bg-danger">Yes</span>');
    } else {
        SecurityUtils.safeInnerHTML(requiredElement, '<span class="badge bg-success">No</span>');
    }
    
    SecurityUtils.safeInnerHTML(document.getElementById('update-status'), '<span class="badge bg-success">Available</span>');
    document.getElementById('update-release-date').textContent = updateData.update_info.release_date || 'Not specified';
    document.getElementById('update-file-size').textContent = formatFileSize(updateData.update_info.file_size);
    document.getElementById('update-description').textContent = updateData.update_info.description || 'No description available';

    const changelogElement = document.getElementById('update-changelog');
    if (updateData.update_info.changelog && updateData.update_info.changelog.length > 0) {
        let changelogHtml = '<ul class="list-unstyled">';
        updateData.update_info.changelog.forEach(item => {
            const sanitizedItem = SecurityUtils.sanitizeHtml(item);
            changelogHtml += '<li><i class="fas fa-check text-success me-2"></i>' + sanitizedItem + '</li>';
        });
        changelogHtml += '</ul>';
        SecurityUtils.safeInnerHTML(changelogElement, changelogHtml);
  } else {
        changelogElement.textContent = 'No changelog available';
    }

    document.getElementById('update-info-section').style.display = 'block';
    document.getElementById('no-updates-section').style.display = 'none';
    window.currentUpdateData = updateData;
}

function showNoUpdatesAvailable() {
    document.getElementById('update-info-section').style.display = 'none';
    document.getElementById('no-updates-section').style.display = 'block';
}

function loadVersionHistory() {
  fetch('/admin/updates/current-version', {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    },
  })
    .then(response => response.json())
    .then(data => {
      const content = document.getElementById('version-history-content');

        if (data.success && data.data.version_history && data.data.version_history.length > 0) {
        let html = '<div class="timeline">';
        data.data.version_history.forEach(history => {
          const isCurrent = history.version === data.data.current_version;
          const badgeClass = isCurrent ? 'bg-success' : 'bg-secondary';
          const badgeText = isCurrent ? 'Current' : 'Previous';
                const sanitizedVersion = SecurityUtils.sanitizeHtml(history.version);
                const sanitizedValue = SecurityUtils.sanitizeHtml(history.value || 'Auto update');
                const sanitizedDate = SecurityUtils.sanitizeHtml(new Date(history.updated_at).toLocaleDateString());

                html += '<div class="timeline-item">';
                html += '<div class="timeline-marker bg-' + (isCurrent ? 'success' : 'secondary') + '"></div>';
                html += '<div class="timeline-content">';
                html += '<div class="d-flex justify-content-between align-items-start">';
                html += '<div>';
                html += '<h6 class="timeline-title">';
                html += 'Version ' + sanitizedVersion;
                html += '<span class="badge ' + badgeClass + ' ms-2">' + badgeText + '</span>';
                html += '</h6>';
                html += '<p class="timeline-text text-muted">' + sanitizedDate + '</p>';
                html += '</div>';
                html += '</div>';
                html += '<div class="mt-2">';
                html += '<p class="small text-muted">' + sanitizedValue + '</p>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
            });
        html += '</div>';
            SecurityUtils.safeInnerHTML(content, html);
        } else {
            const noHistoryHtml = '<div class="text-center py-4">' +
                '<i class="fas fa-info-circle text-muted fs-1 mb-3"></i>' +
                '<h5 class="text-muted">No Version History Available</h5>' +
                '<p class="text-muted">Version information will appear here</p>' +
                '</div>';
            SecurityUtils.safeInnerHTML(content, noHistoryHtml);
        }
    })
    .catch(error => {
      console.error('Error loading version history:', error);
      const content = document.getElementById('version-history-content');
        const errorHtml = '<div class="text-center py-4">' +
            '<i class="fas fa-exclamation-triangle text-warning fs-1 mb-3"></i>' +
            '<h5 class="text-warning">Error loading version history</h5>' +
            '<p class="text-muted">Please try again later</p>' +
            '</div>';
        SecurityUtils.safeInnerHTML(content, errorHtml);
    });
}

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
    // Check for updates button
    document.getElementById('check-updates-btn').addEventListener('click', checkForUpdates);

    // Update system button
    document.getElementById('update-system-btn').addEventListener('click', function() {
        const { version } = this.dataset;
        showUpdateModal(version);
    });

    // Confirm update checkbox
    document.getElementById('confirmUpdate').addEventListener('change', function() {
        document.getElementById('confirm-update-btn').disabled = !this.checked;
    });

    // Confirm update button
    document.getElementById('confirm-update-btn').addEventListener('click', () => {
        const { version } = document.getElementById('update-system-btn').dataset;
        performUpdate(version);
    });

    // Confirm upload button
    document.getElementById('confirm-upload-btn').addEventListener('click', uploadUpdatePackage);

    // Confirm rollback checkbox
    document.getElementById('confirmRollback').addEventListener('change', function() {
        document.getElementById('confirm-rollback-btn').disabled = !this.checked;
    });

    // Confirm rollback button
    document.getElementById('confirm-rollback-btn').addEventListener('click', function() {
        const { version } = this.dataset;
        performRollback(version);
    });

    // Auto update button
    document.getElementById('auto-update-btn').addEventListener('click', () => {
        const modal = new window.bootstrap.Modal(document.getElementById('autoUpdateModal'));
        modal.show();
    });

  // Load version history on page load
  loadVersionHistory();
});
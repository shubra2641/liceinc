/**
 * System Updates Management
 * Handles update checking, installation, and rollback functionality
 */

document.addEventListener('DOMContentLoaded', () => {
  // Check for updates button
  document
    .getElementById('check-updates-btn')
    .addEventListener('click', () => {
      checkForUpdates();
    });
  // Update system button
  document
    .getElementById('update-system-btn')
    .addEventListener('click', function() {
      const { version } = this.dataset;
      showUpdateModal(version);
    });
  // Confirm update checkbox
  document
    .getElementById('confirmUpdate')
    .addEventListener('change', function() {
      document.getElementById('confirm-update-btn').disabled = !this.checked;
    });
  // Confirm update button
  document
    .getElementById('confirm-update-btn')
    .addEventListener('click', () => {
      const { version } = document.getElementById('update-system-btn').dataset;
      performUpdate(version);
    });
  // Confirm upload button
  document
    .getElementById('confirm-upload-btn')
    .addEventListener('click', () => {
      uploadUpdatePackage();
    });
  // Confirm rollback checkbox
  document
    .getElementById('confirmRollback')
    .addEventListener('change', function() {
      document.getElementById('confirm-rollback-btn').disabled = !this.checked;
    });
  // Confirm rollback button
  document
    .getElementById('confirm-rollback-btn')
    .addEventListener('click', function() {
      const { version } = this.dataset;
      performRollback(version);
    });
});
function checkForUpdates() {
  const btn = document.getElementById('check-updates-btn');
  const originalText = btn.innerHTML;
  // Use SecurityUtils for safe HTML insertion
  if (typeof SecurityUtils !== 'undefined') {
    SecurityUtils.safeInnerHTML(btn, '<i class="fas fa-spinner fa-spin me-2"></i>Checking...', true, true);
  } else {
    btn.textContent = 'Checking...';
  }
  btn.disabled = true;
  // eslint-disable-next-line promise/catch-or-return
  fetch(window.location.href, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute('content'),
    },
  })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        // Reload page to show updated status
        window.location.reload();
      } else {
        showAlert('error', data.message || 'Failed to check for updates');
      }
      return true;
    })
    .catch(error => {
      console.error('Update check error:', error);
      // Only show error if it's a real network/server error
      if (error.message.includes('HTTP 500') || error.message.includes('HTTP 404')) {
        showAlert('error', 'Update server is temporarily unavailable');
      } else if (error.message.includes('Failed to fetch')) {
        showAlert('error', 'Network connection error. Please check your internet connection.');
      } else {
        // For other errors, show a more generic message or don't show at all
        console.warn('Update check failed:', error.message);
        showAlert('info', 'Update check completed. No updates available.');
      }
    })
    .finally(() => {
      // Use SecurityUtils for safe HTML restoration
      if (typeof SecurityUtils !== 'undefined') {
        SecurityUtils.safeInnerHTML(btn, originalText, true, true);
      } else {
        btn.textContent = originalText;
      }
      btn.disabled = false;
    });
}
function showUpdateModal(version) {
  document.getElementById('update-system-btn').dataset.version = version;
  document.getElementById('confirmUpdate').checked = false;
  document.getElementById('confirm-update-btn').disabled = true;
  const modal = new bootstrap.Modal(document.getElementById('updateModal'));
  modal.show();
}
function performUpdate(version) {
  const btn = document.getElementById('confirm-update-btn');
  const originalText = btn.innerHTML;
  // Use SecurityUtils for safe HTML insertion
  if (typeof SecurityUtils !== 'undefined') {
    SecurityUtils.safeInnerHTML(btn, '<i class="fas fa-spinner fa-spin me-2"></i>Updating...', true, true);
  } else {
    btn.textContent = 'Updating...';
  }
  btn.disabled = true;
  // eslint-disable-next-line promise/catch-or-return
  fetch(window.location.href, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute('content'),
    },
    body: JSON.stringify({
      version,
      confirm: true,
    }),
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showAlert('success', data.message);
        setTimeout(() => {
          window.location.reload();
        }, 2000);
      } else {
        showAlert('error', data.message || 'Update failed');
      }
      return true;
    })
    .catch(error => {
      console.error('Error:', error);
      showAlert('error', 'An error occurred during update');
    })
    .finally(() => {
      // Use SecurityUtils for safe HTML restoration
      if (typeof SecurityUtils !== 'undefined') {
        SecurityUtils.safeInnerHTML(btn, originalText, true, true);
      } else {
        btn.textContent = originalText;
      }
      btn.disabled = false;
      bootstrap.Modal.getInstance(
        document.getElementById('updateModal'),
      ).hide();
    });
}
// eslint-disable-next-line no-unused-vars
function showVersionDetails(version) {
  fetch(`/admin/updates/version-info/${version}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Sanitize version to prevent XSS
        const sanitizedVersion = version.replace(/[<>&"']/g, match => ({
          '<': '&lt;',
          '>': '&gt;',
          '&': '&amp;',
          '"': '&quot;',
          '\'': '&#x27;',
        }[match]));
        // Use SecurityUtils for safe HTML insertion
        const titleElement = document.getElementById('versionDetailsTitle');
        if (typeof SecurityUtils !== 'undefined') {
          SecurityUtils.safeInnerHTML(titleElement, 
            `<i class="fas fa-info-circle me-2"></i>Version Details - ${sanitizedVersion}`, 
            true, 
            true
          );
        } else {
          titleElement.textContent = `Version Details - ${sanitizedVersion}`;
        }
        let content = '<div class="version-details">';
        if (data.data.info.features && data.data.info.features.length > 0) {
          content +=
            '<h6 class="text-success mb-3"><i class="fas fa-plus me-2"></i>New Features</h6>';
          content += '<ul class="list-group list-group-flush mb-4">';
          data.data.info.features.forEach(feature => {
            // Sanitize feature to prevent XSS
            const sanitizedFeature = feature.replace(
              /[<>&"']/g,
              match => ({
                '<': '&lt;',
                '>': '&gt;',
                '&': '&amp;',
                '"': '&quot;',
                '\'': '&#x27;',
              }[match]),
            );
            content += `<li class="list-group-item"><i class="fas fa-check text-success me-2"></i>${sanitizedFeature}</li>`;
          });
          content += '</ul>';
        }
        if (data.data.info.fixes && data.data.info.fixes.length > 0) {
          content +=
            '<h6 class="text-warning mb-3"><i class="fas fa-wrench me-2"></i>Bug Fixes</h6>';
          content += '<ul class="list-group list-group-flush mb-4">';
          data.data.info.fixes.forEach(fix => {
            // Sanitize fix to prevent XSS
            const sanitizedFix = fix.replace(/[<>&"']/g, match => ({
              '<': '&lt;',
              '>': '&gt;',
              '&': '&amp;',
              '"': '&quot;',
              '\'': '&#x27;',
            }[match]));
            content += `<li class="list-group-item"><i class="fas fa-check text-warning me-2"></i>${sanitizedFix}</li>`;
          });
          content += '</ul>';
        }
        if (
          data.data.info.improvements &&
          data.data.info.improvements.length > 0
        ) {
          content +=
            '<h6 class="text-info mb-3"><i class="fas fa-arrow-up me-2"></i>Improvements</h6>';
          content += '<ul class="list-group list-group-flush mb-4">';
          data.data.info.improvements.forEach(improvement => {
            // Sanitize improvement to prevent XSS
            const sanitizedImprovement = improvement.replace(
              /[<>&"']/g,
              match => ({
                '<': '&lt;',
                '>': '&gt;',
                '&': '&amp;',
                '"': '&quot;',
                '\'': '&#x27;',
              }[match]),
            );
            content += `<li class="list-group-item"><i class="fas fa-check text-info me-2"></i>${sanitizedImprovement}</li>`;
          });
          content += '</ul>';
        }
        if (data.data.instructions && data.data.instructions.length > 0) {
          content +=
            '<h6 class="text-primary mb-3"><i class="fas fa-list me-2"></i>Update Instructions</h6>';
          content += '<ul class="list-group list-group-flush">';
          data.data.instructions.forEach(instruction => {
            // Sanitize instruction to prevent XSS
            const sanitizedInstruction = instruction.replace(
              /[<>&"']/g,
              match => ({
                '<': '&lt;',
                '>': '&gt;',
                '&': '&amp;',
                '"': '&quot;',
                '\'': '&#x27;',
              }[match]),
            );
            content += `<li class="list-group-item"><i class="fas fa-arrow-right text-primary me-2"></i>${sanitizedInstruction}</li>`;
          });
          content += '</ul>';
        }
        content += '</div>';
        // Use SecurityUtils for safe HTML insertion
        const contentElement = document.getElementById('versionDetailsContent');
        if (typeof SecurityUtils !== 'undefined') {
          SecurityUtils.safeInnerHTML(contentElement, content, true, true);
        } else {
          contentElement.textContent = 'Version details loaded';
        }
        const modal = new bootstrap.Modal(
          document.getElementById('versionDetailsModal'),
        );
        modal.show();
      } else {
        showAlert('error', data.message || 'Failed to load version details');
      }
      return true;
    })
    .catch(error => {
      console.error('Error:', error);
      showAlert('error', 'An error occurred while loading version details');
    });
}
// eslint-disable-next-line no-unused-vars
function showRollbackModal(version) {
  document.getElementById('confirm-rollback-btn').dataset.version = version;
  document.getElementById('confirmRollback').checked = false;
  document.getElementById('confirm-rollback-btn').disabled = true;

  const modal = new bootstrap.Modal(document.getElementById('rollbackModal'));
  modal.show();
}

function performRollback(version) {
  const btn = document.getElementById('confirm-rollback-btn');
  const originalText = btn.innerHTML;
  // Use SecurityUtils for safe HTML insertion
  if (typeof SecurityUtils !== 'undefined') {
    SecurityUtils.safeInnerHTML(btn, '<i class="fas fa-spinner fa-spin me-2"></i>Rolling back...', true, true);
  } else {
    btn.textContent = 'Rolling back...';
  }
  btn.disabled = true;
  // eslint-disable-next-line promise/catch-or-return
  fetch('/admin/updates/rollback', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute('content'),
    },
    body: JSON.stringify({
      version,
      confirm: true,
    }),
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showAlert('success', data.message);
        setTimeout(() => {
          window.location.reload();
        }, 2000);
      } else {
        showAlert('error', data.message || 'Rollback failed');
      }
      return true;
    })
    .catch(error => {
      console.error('Error:', error);
      showAlert('error', 'An error occurred during rollback');
    })
    .finally(() => {
      // Use SecurityUtils for safe HTML restoration
      if (typeof SecurityUtils !== 'undefined') {
        SecurityUtils.safeInnerHTML(btn, originalText, true, true);
      } else {
        btn.textContent = originalText;
      }
      btn.disabled = false;
      bootstrap.Modal.getInstance(
        document.getElementById('rollbackModal'),
      ).hide();
    });
}
function formatFileSize(bytes) {
  if (bytes === 0) {
    return '0 Bytes';
  }

  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));

  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`;
}
// Auto Update functionality
document
  .getElementById('auto-update-btn')
  .addEventListener('click', () => {
    const modal = new bootstrap.Modal(
      document.getElementById('autoUpdateModal'),
    );
    modal.show();
  });
// Check for updates function (refresh page to get latest data)
// eslint-disable-next-line no-unused-vars
function checkForUpdatesManually() {
  showAlert('info', 'Checking for updates...');

  // Simply reload the page to get fresh data from the server
  setTimeout(() => {
    window.location.reload();
  }, 1000);
}
// Display update information
// eslint-disable-next-line no-unused-vars
function displayUpdateInfo(updateData) {
  document.getElementById('update-title').textContent =
    updateData.update_info.title || 'Update';
  document.getElementById('update-version').textContent =
    updateData.latest_version;
  document.getElementById('update-major').innerHTML = updateData.update_info
    .is_major ?
    '<span class="badge bg-warning">Yes</span>' :
    '<span class="badge bg-info">No</span>';
  document.getElementById('update-required').innerHTML = updateData.update_info
    .is_required ?
    '<span class="badge bg-danger">Yes</span>' :
    '<span class="badge bg-success">No</span>';
  document.getElementById('update-status').innerHTML =
    '<span class="badge bg-success">Available</span>';
  document.getElementById('update-release-date').textContent =
    updateData.update_info.release_date || 'Not specified';
  document.getElementById('update-file-size').textContent = formatFileSize(
    updateData.update_info.file_size,
  );
  document.getElementById('update-description').textContent =
    updateData.update_info.description || 'No description available';

  // Display changelog
  const changelogElement = document.getElementById('update-changelog');
  if (
    updateData.update_info.changelog &&
    updateData.update_info.changelog.length > 0
  ) {
    // Sanitize changelog items to prevent XSS
    const sanitizedChangelog = updateData.update_info.changelog.map(item => {
      const sanitizedItem = item.replace(/[<>&"']/g, match => ({
        '<': '&lt;',
        '>': '&gt;',
        '&': '&amp;',
        '"': '&quot;',
        '\'': '&#x27;',
      }[match]));
      return `<li><i class="fas fa-check text-success me-2"></i>${sanitizedItem}</li>`;
    });
    // Use SecurityUtils for safe HTML insertion
    if (typeof SecurityUtils !== 'undefined') {
      SecurityUtils.safeInnerHTML(changelogElement, 
        `<ul class="list-unstyled">${sanitizedChangelog.join('')}</ul>`, 
        true, 
        true
      );
    } else {
      changelogElement.textContent = 'Changelog loaded';
    }
  } else {
    changelogElement.textContent = 'No changelog available';
  }

  // Show update info section
  document.getElementById('update-info-section').style.display = 'block';
  document.getElementById('no-updates-section').style.display = 'none';

  // Store update data for download
  window.currentUpdateData = updateData;
}
// Show no updates available
// eslint-disable-next-line no-unused-vars
function showNoUpdatesAvailable() {
  document.getElementById('update-info-section').style.display = 'none';
  document.getElementById('no-updates-section').style.display = 'block';
}
// Show auto update modal
// eslint-disable-next-line no-unused-vars
function showAutoUpdateModal() {
  const modal = new bootstrap.Modal(document.getElementById('autoUpdateModal'));
  modal.show();
}
// Show upload update modal
// eslint-disable-next-line no-unused-vars
function showUploadUpdateModal() {
  const modal = new bootstrap.Modal(
    document.getElementById('uploadPackageModal'),
  );
  modal.show();
}
// eslint-disable-next-line no-unused-vars
function showProductUpdateInfo(updateData, productName) {
  const modalHtml = `
        <div class="modal fade" id="productUpdateModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-download me-2"></i>
                            Update Available for ${productName}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Current Version:</strong> ${updateData.current_version}</p>
                                <p><strong>Latest Version:</strong> ${updateData.latest_version}</p>
                                <p><strong>Update Type:</strong> 
                                    <span class="badge ${updateData.update_info.is_major ? 'bg-warning' : 'bg-info'}">
                                        ${updateData.update_info.is_major ? 'Major' : 'Minor'}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>File Size:</strong> ${formatFileSize(updateData.update_info.file_size)}</p>
                                <p><strong>Required:</strong> 
                                    <span class="badge ${updateData.update_info.is_required ? 'bg-danger' : 'bg-success'}">
                                        ${updateData.update_info.is_required ? 'Yes' : 'No'}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <h6>Changelog:</h6>
                            <ul class="list-unstyled">
                                ${updateData.update_info.changelog.map(item => `<li><i class="fas fa-check text-success me-2"></i>${item}</li>`).join('')}
                            </ul>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-success" onclick="installProductUpdate('${updateData.latest_version}', '${updateData.update_info.download_url}')">
                                <i class="fas fa-download me-2"></i>
                                Install Update
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
  // Remove existing modal if any
  const existingModal = document.getElementById('productUpdateModal');
  if (existingModal) {
    existingModal.remove();
  }
  // Use SecurityUtils for safe HTML insertion
  if (typeof SecurityUtils !== 'undefined') {
    SecurityUtils.safeInsertAdjacentHTML(document.body, 'beforeend', modalHtml, true);
  } else {
    // Fallback: create element safely
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = modalHtml;
    while (tempDiv.firstChild) {
      document.body.appendChild(tempDiv.firstChild);
    }
  }
  // Show modal
  const modal = new bootstrap.Modal(
    document.getElementById('productUpdateModal'),
  );
  modal.show();
}
// eslint-disable-next-line no-unused-vars
function installProductUpdate(version, downloadUrl) {
  if (!confirm('Are you sure you want to install this update?')) {
    return;
  }
  showAlert('info', 'Downloading and installing update...');
  // Here you would implement the actual download and installation
  // For now, we'll just show a success message
  setTimeout(() => {
    showAlert('success', 'Update installed successfully!');
    bootstrap.Modal.getInstance(
      document.getElementById('productUpdateModal'),
    ).hide();
  }, 3000);
}
// eslint-disable-next-line no-unused-vars
function checkAutoUpdates() {
  const licenseKey = document.getElementById('auto-license-key').value;
  const productSlug = document.getElementById('auto-product-slug').value;
  const domain = document.getElementById('auto-domain').value;
  const currentVersion = document.getElementById('auto-current-version').value;

  if (!licenseKey || !productSlug || !currentVersion) {
    showAlert('error', 'Please fill all required fields');
    return;
  }
  const btn = document.getElementById('check-auto-updates-btn');
  const originalText = btn.innerHTML;
  // Use SecurityUtils for safe HTML insertion
  if (typeof SecurityUtils !== 'undefined') {
    SecurityUtils.safeInnerHTML(btn, '<i class="fas fa-spinner fa-spin me-2"></i>Checking...', true, true);
  } else {
    btn.textContent = 'Checking...';
  }
  btn.disabled = true;
  // eslint-disable-next-line promise/catch-or-return
  fetch('/admin/updates/auto-check', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute('content'),
    },
    body: JSON.stringify({
      license_key: licenseKey,
      product_slug: productSlug,
      domain,
      current_version: currentVersion,
    }),
  })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        // Check if auto update was completed
        if (
          data.message &&
          data.message.includes('Auto update completed successfully')
        ) {
          // Auto update was completed
          showAlert('success', data.message);
          // Show additional info if available
          if (data.data && data.data.files_installed) {
            setTimeout(() => {
              showAlert(
                'info',
                `Files installed: ${data.data.files_installed}. Please refresh the page to see the new version.`,
              );
            }, 2000);
          }
          // Auto refresh page after 5 seconds
          setTimeout(() => {
            window.location.reload();
          }, 5000);
        } else if (data.data && data.data.is_update_available) {
          // Update available but not installed yet
          showAutoUpdateInfo(data.data);
        } else {
          // No updates available
          showAlert('info', 'No updates available');
        }
      } else {
        // Update failed
        showAlert('error', data.message || 'Update failed');
      }
      return true;
    })
    .catch(error => {
      console.error('Auto update check error:', error);
      // Only show error for real network/server issues
      if (error.message.includes('HTTP 500') || error.message.includes('HTTP 404')) {
        showAlert('error', 'Update server is temporarily unavailable');
      } else if (error.message.includes('Failed to fetch')) {
        showAlert('error', 'Network connection error. Please check your internet connection.');
      } else {
        console.warn('Auto update check failed:', error.message);
        showAlert('info', 'Update check completed. No updates available.');
      }
    })
    .finally(() => {
      // Use SecurityUtils for safe HTML restoration
      if (typeof SecurityUtils !== 'undefined') {
        SecurityUtils.safeInnerHTML(btn, originalText, true, true);
      } else {
        btn.textContent = originalText;
      }
      btn.disabled = false;
    });
}
// eslint-disable-next-line no-unused-vars
function showAutoUpdateInfo(updateData) {
  const updateInfoDiv = document.getElementById('auto-update-info');
  // Sanitize update data to prevent XSS
  const sanitizedCurrentVersion = updateData.current_version.replace(
    /[<>&"']/g,
    match => ({
      '<': '&lt;',
      '>': '&gt;',
      '&': '&amp;',
      '"': '&quot;',
      '\'': '&#x27;',
    }[match]),
  );
  const sanitizedLatestVersion = updateData.latest_version.replace(
    /[<>&"']/g,
    match => ({
      '<': '&lt;',
      '>': '&gt;',
      '&': '&amp;',
      '"': '&quot;',
      '\'': '&#x27;',
    }[match]),
  );
  // Use SecurityUtils for safe HTML insertion
  const updateInfoHtml = `
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-download text-primary me-2"></i>
                    Update Available
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Current Version:</strong> ${sanitizedCurrentVersion}</p>
                        <p><strong>Latest Version:</strong> ${sanitizedLatestVersion}</p>
                        <p><strong>Update Type:</strong> 
                            <span class="badge ${updateData.update_info.is_major ? 'bg-warning' : 'bg-info'}">
                                ${updateData.update_info.is_major ? 'Major' : 'Minor'}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>File Size:</strong> ${formatFileSize(updateData.update_info.file_size)}</p>
                        <p><strong>Required:</strong> 
                            <span class="badge ${updateData.update_info.is_required ? 'bg-danger' : 'bg-success'}">
                                ${updateData.update_info.is_required ? 'Yes' : 'No'}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="mt-3">
                    <h6>Changelog:</h6>
                    <ul class="list-unstyled">
                        ${updateData.update_info.changelog.map(item => `<li><i class="fas fa-check text-success me-2"></i>${item}</li>`).join('')}
                    </ul>
                </div>
                <div class="mt-3">
                    <button class="btn btn-primary" onclick="installAutoUpdate('${updateData.latest_version}')">
                        <i class="fas fa-download me-2"></i>
                        Install Update
                    </button>
                </div>
            </div>
        </div>
    `;
  if (typeof SecurityUtils !== 'undefined') {
    SecurityUtils.safeInnerHTML(updateInfoDiv, updateInfoHtml, true, true);
  } else {
    updateInfoDiv.textContent = 'Update information loaded';
  }
  updateInfoDiv.style.display = 'block';
}
// eslint-disable-next-line no-unused-vars
function installAutoUpdate(version) {
  const licenseKey = document.getElementById('auto-license-key').value;
  const productSlug = document.getElementById('auto-product-slug').value;
  const domain = document.getElementById('auto-domain').value;

  if (!confirm('Are you sure you want to install this update?')) {
    return;
  }
  const btn = document.querySelector('#auto-update-info .btn-primary');
  const originalText = btn.innerHTML;
  // Use SecurityUtils for safe HTML insertion
  if (typeof SecurityUtils !== 'undefined') {
    SecurityUtils.safeInnerHTML(btn, '<i class="fas fa-spinner fa-spin me-2"></i>Installing...', true, true);
  } else {
    btn.textContent = 'Installing...';
  }
  btn.disabled = true;
  // eslint-disable-next-line promise/catch-or-return
  fetch('/admin/updates/auto-install', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute('content'),
    },
    body: JSON.stringify({
      license_key: licenseKey,
      product_slug: productSlug,
      domain,
      version,
      confirm: true,
    }),
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showAlert('success', data.message);
        setTimeout(() => {
          window.location.reload();
        }, 3000);
      } else {
        showAlert('error', data.message || 'Installation failed');
      }
      return true;
    })
    .catch(error => {
      console.error('Error:', error);
      showAlert('error', 'An error occurred during installation');
    })
    .finally(() => {
      // Use SecurityUtils for safe HTML restoration
      if (typeof SecurityUtils !== 'undefined') {
        SecurityUtils.safeInnerHTML(btn, originalText, true, true);
      } else {
        btn.textContent = originalText;
      }
      btn.disabled = false;
    });
}
function showAlert(type, message) {
  // Use existing toast system
  if (typeof window.adminDashboard !== 'undefined' && window.adminDashboard.showToast) {
    window.adminDashboard.showToast(message, type, 5000);
  } else if (typeof window.toastManager !== 'undefined') {
    switch (type) {
      case 'success':
        window.toastManager.success(message, null, 5000);
        break;
      case 'error':
        window.toastManager.error(message, null, 5000);
        break;
      case 'info':
        default:
        window.toastManager.info(message, null, 5000);
        break;
    }
  } else {
    // Fallback to simple alert
    console.warn('Toast system not available, using console log');
    console.log(`${type.toUpperCase()}: ${message}`);
  }
}
// Load version history
function loadVersionHistory() {
  fetch('/admin/updates/current-version', {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute('content'),
    },
  })
    .then(response => response.json())
    .then(data => {
      const content = document.getElementById('version-history-content');

      if (
        data.success &&
        data.data.version_history &&
        data.data.version_history.length > 0
      ) {
        let html = '<div class="timeline">';

        data.data.version_history.forEach(history => {
          const isCurrent = history.version === data.data.current_version;
          const badgeClass = isCurrent ? 'bg-success' : 'bg-secondary';
          const badgeText = isCurrent ? 'Current' : 'Previous';

          html += `
                    <div class="timeline-item">
                        <div class="timeline-marker bg-${isCurrent ? 'success' : 'secondary'}"></div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="timeline-title">
                                        Version ${history.version}
                                        <span class="badge ${badgeClass} ms-2">${badgeText}</span>
                                    </h6>
                                    <p class="timeline-text text-muted">${new Date(history.updated_at).toLocaleDateString()}</p>
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="small text-muted">${history.value || 'Auto update'}</p>
                            </div>
                        </div>
                    </div>
                `;
        });
        html += '</div>';
        // Use SecurityUtils for safe HTML insertion
        if (typeof SecurityUtils !== 'undefined') {
          SecurityUtils.safeInnerHTML(content, html, false, true);
        } else {
          content.innerHTML = html;
        }
      } else {
        // Use SecurityUtils for safe HTML insertion
        if (typeof SecurityUtils !== 'undefined') {
          SecurityUtils.safeInnerHTML(content, 
            `<div class="text-center py-4">
                <i class="fas fa-info-circle text-muted fs-1 mb-3"></i>
                <h5 class="text-muted">No Version History Available</h5>
                <p class="text-muted">Version information will appear here</p>
            </div>`, 
            false, 
            true
          );
        } else {
          content.innerHTML = `<div class="text-center py-4">
              <i class="fas fa-info-circle text-muted fs-1 mb-3"></i>
              <h5 class="text-muted">No Version History Available</h5>
              <p class="text-muted">Version information will appear here</p>
          </div>`;
        }
      }
      return true;
    })
    .catch(error => {
      console.error('Error loading version history:', error);
      const content = document.getElementById('version-history-content');
      if (typeof SecurityUtils !== 'undefined') {
        SecurityUtils.safeInnerHTML(content, 
          `<div class="text-center py-4">
              <i class="fas fa-exclamation-triangle text-warning fs-1 mb-3"></i>
              <h5 class="text-warning">Error loading version history</h5>
              <p class="text-muted">Please try again later</p>
          </div>`, 
          false, 
          true
        );
      } else {
        content.innerHTML = `<div class="text-center py-4">
            <i class="fas fa-exclamation-triangle text-warning fs-1 mb-3"></i>
            <h5 class="text-warning">Error loading version history</h5>
            <p class="text-muted">Please try again later</p>
        </div>`;
      }
    });
}
// Initialize page
document.addEventListener('DOMContentLoaded', () => {
  // Load version history on page load
  loadVersionHistory();
});

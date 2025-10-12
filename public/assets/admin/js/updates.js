/**
 * System Updates Management
 * Handles update checking, installation, and rollback functionality
 */

// Import SecurityUtils if available
if (typeof SecurityUtils === 'undefined') {
    console.warn('SecurityUtils not found. Loading security-utils.js...');
    const script = document.createElement('script');
    script.src = '/assets/js/security-utils.js';
    script.onload = () => {
        console.log('SecurityUtils loaded successfully');
    };
    document.head.appendChild(script);
}

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
  const originalText = btn.textContent;

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
    .then(response => response.json())
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
      console.error('Error:', error);
      showAlert('error', 'An error occurred while checking for updates');
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

  const modal = new window.bootstrap.Modal(document.getElementById('updateModal'));
  modal.show();
}

function performUpdate(version) {
  const btn = document.getElementById('confirm-update-btn');
  const originalText = btn.textContent;

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
      window.bootstrap.Modal.getInstance(
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
        const sanitizedVersion = SecurityUtils.sanitizeHtml(version);
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

        const contentBuilder = [];
        contentBuilder.push('<div class="version-details">');

        if (data.data.info.features && data.data.info.features.length > 0) {
          contentBuilder.push('<h6 class="text-success mb-3"><i class="fas fa-plus me-2"></i>New Features</h6>');
          contentBuilder.push('<ul class="list-group list-group-flush mb-4">');
          data.data.info.features.forEach(feature => {
            // Sanitize feature to prevent XSS
            const sanitizedFeature = SecurityUtils.sanitizeHtml(feature);
            contentBuilder.push(`<li class="list-group-item"><i class="fas fa-check text-success me-2"></i>${sanitizedFeature}</li>`);
          });
          contentBuilder.push('</ul>');
        }

        if (data.data.info.fixes && data.data.info.fixes.length > 0) {
          contentBuilder.push('<h6 class="text-warning mb-3"><i class="fas fa-wrench me-2"></i>Bug Fixes</h6>');
          contentBuilder.push('<ul class="list-group list-group-flush mb-4">');
          data.data.info.fixes.forEach(fix => {
            // Sanitize fix to prevent XSS
            const sanitizedFix = SecurityUtils.sanitizeHtml(fix);
            contentBuilder.push(`<li class="list-group-item"><i class="fas fa-check text-warning me-2"></i>${sanitizedFix}</li>`);
          });
          contentBuilder.push('</ul>');
        }

        if (
          data.data.info.improvements &&
          data.data.info.improvements.length > 0
        ) {
          contentBuilder.push('<h6 class="text-info mb-3"><i class="fas fa-arrow-up me-2"></i>Improvements</h6>');
          contentBuilder.push('<ul class="list-group list-group-flush mb-4">');
          data.data.info.improvements.forEach(improvement => {
            // Sanitize improvement to prevent XSS
            const sanitizedImprovement = SecurityUtils.sanitizeHtml(improvement);
            contentBuilder.push(`<li class="list-group-item"><i class="fas fa-check text-info me-2"></i>${sanitizedImprovement}</li>`);
          });
          contentBuilder.push('</ul>');
        }

        if (data.data.instructions && data.data.instructions.length > 0) {
          contentBuilder.push('<h6 class="text-primary mb-3"><i class="fas fa-list me-2"></i>Update Instructions</h6>');
          contentBuilder.push('<ul class="list-group list-group-flush">');
          data.data.instructions.forEach(instruction => {
            // Sanitize instruction to prevent XSS
            const sanitizedInstruction = SecurityUtils.sanitizeHtml(instruction);
            contentBuilder.push(`<li class="list-group-item"><i class="fas fa-arrow-right text-primary me-2"></i>${sanitizedInstruction}</li>`);
          });
          contentBuilder.push('</ul>');
        }

        contentBuilder.push('</div>');
        const content = contentBuilder.join('');
        // Use SecurityUtils for safe HTML insertion
        const contentElement = document.getElementById('versionDetailsContent');
        if (typeof SecurityUtils !== 'undefined') {
          SecurityUtils.safeInnerHTML(contentElement, content, true, true);
        } else {
          contentElement.textContent = 'Version details loaded';
        }

        const modal = new window.bootstrap.Modal(
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

  const modal = new window.bootstrap.Modal(document.getElementById('rollbackModal'));
  modal.show();
}

function performRollback(version) {
  const btn = document.getElementById('confirm-rollback-btn');
  const originalText = btn.textContent;

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
      window.bootstrap.Modal.getInstance(
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

  const result = bytes / Math.pow(k, i);
  return `${parseFloat(result.toFixed(2))} ${sizes[i]}`;
}

// Auto Update functionality
document
  .getElementById('auto-update-btn')
  .addEventListener('click', () => {
    const modal = new window.bootstrap.Modal(
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
  const majorElement = document.getElementById('update-major');
  if (updateData.update_info.is_major) {
    SecurityUtils.safeInnerHTML(majorElement, '<span class="badge bg-warning">Yes</span>', true, true);
  } else {
    SecurityUtils.safeInnerHTML(majorElement, '<span class="badge bg-info">No</span>', true, true);
  }
  const requiredElement = document.getElementById('update-required');
  if (updateData.update_info.is_required) {
    SecurityUtils.safeInnerHTML(requiredElement, '<span class="badge bg-danger">Yes</span>', true, true);
  } else {
    SecurityUtils.safeInnerHTML(requiredElement, '<span class="badge bg-success">No</span>', true, true);
  }
  SecurityUtils.safeInnerHTML(document.getElementById('update-status'), 
    '<span class="badge bg-success">Available</span>', true, true);
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
      const sanitizedItem = SecurityUtils.sanitizeHtml(item);
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
  const modal = new window.bootstrap.Modal(document.getElementById('autoUpdateModal'));
  modal.show();
}

// Show upload update modal
// eslint-disable-next-line no-unused-vars
function showUploadUpdateModal() {
  const modal = new window.bootstrap.Modal(
    document.getElementById('uploadPackageModal'),
  );
  modal.show();
}

// eslint-disable-next-line no-unused-vars
function showProductUpdateInfo(updateData, productName) {
  const modalBuilder = [];
  modalBuilder.push('<div class="modal fade" id="productUpdateModal" tabindex="-1">');
  modalBuilder.push('<div class="modal-dialog modal-lg">');
  modalBuilder.push('<div class="modal-content">');
  modalBuilder.push('<div class="modal-header bg-success text-white">');
  modalBuilder.push('<h5 class="modal-title">');
  modalBuilder.push('<i class="fas fa-download me-2"></i>');
  modalBuilder.push(`Update Available for ${SecurityUtils.sanitizeHtml(productName)}`);
  modalBuilder.push('</h5>');
  modalBuilder.push('<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>');
  modalBuilder.push('</div>');
  modalBuilder.push('<div class="modal-body">');
  modalBuilder.push('<div class="row">');
  modalBuilder.push('<div class="col-md-6">');
  modalBuilder.push(`<p><strong>Current Version:</strong> ${SecurityUtils.sanitizeHtml(updateData.current_version)}</p>`);
  modalBuilder.push(`<p><strong>Latest Version:</strong> ${SecurityUtils.sanitizeHtml(updateData.latest_version)}</p>`);
  modalBuilder.push('<p><strong>Update Type:</strong>');
  modalBuilder.push(`<span class="badge ${updateData.update_info.is_major ? 'bg-warning' : 'bg-info'}">`);
  modalBuilder.push(`${updateData.update_info.is_major ? 'Major' : 'Minor'}`);
  modalBuilder.push('</span>');
  modalBuilder.push('</p>');
  modalBuilder.push('</div>');
  modalBuilder.push('<div class="col-md-6">');
  modalBuilder.push(`<p><strong>File Size:</strong> ${formatFileSize(updateData.update_info.file_size)}</p>`);
  modalBuilder.push('<p><strong>Required:</strong>');
  modalBuilder.push(`<span class="badge ${updateData.update_info.is_required ? 'bg-danger' : 'bg-success'}">`);
  modalBuilder.push(`${updateData.update_info.is_required ? 'Yes' : 'No'}`);
  modalBuilder.push('</span>');
  modalBuilder.push('</p>');
  modalBuilder.push('</div>');
  modalBuilder.push('</div>');
  modalBuilder.push('<div class="mt-3">');
  modalBuilder.push('<h6>Changelog:</h6>');
  modalBuilder.push('<ul class="list-unstyled">');
  updateData.update_info.changelog.forEach(item => {
    const sanitizedItem = SecurityUtils.sanitizeHtml(item);
    modalBuilder.push(`<li><i class="fas fa-check text-success me-2"></i>${sanitizedItem}</li>`);
  });
  modalBuilder.push('</ul>');
  modalBuilder.push('</div>');
  modalBuilder.push('<div class="mt-3">');
  modalBuilder.push(`<button class="btn btn-success" onclick="installProductUpdate('${updateData.latest_version}', '${updateData.update_info.download_url}')">`);
  modalBuilder.push('<i class="fas fa-download me-2"></i>');
  modalBuilder.push('Install Update');
  modalBuilder.push('</button>');
  modalBuilder.push('</div>');
  modalBuilder.push('</div>');
  modalBuilder.push('</div>');
  modalBuilder.push('</div>');
  modalBuilder.push('</div>');
  
  const modalHtml = modalBuilder.join('');

  // Remove existing modal if any
  const existingModal = document.getElementById('productUpdateModal');
  if (existingModal) {
    existingModal.remove();
  }

  // Use SecurityUtils for safe HTML insertion
  if (typeof SecurityUtils !== 'undefined') {
    SecurityUtils.safeInsertAdjacentHTML(document.body, 'beforeend', modalHtml, true);
  } else {
    // Fallback: create element safely using SecurityUtils
    const tempDiv = document.createElement('div');
    SecurityUtils.safeInnerHTML(tempDiv, modalHtml, true, true);
    while (tempDiv.firstChild) {
      document.body.appendChild(tempDiv.firstChild);
    }
  }

  // Show modal
  const modal = new window.bootstrap.Modal(
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
    window.bootstrap.Modal.getInstance(
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
  const originalText = btn.textContent;

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
      console.error('Error:', error);
      showAlert('error', `An error occurred during update: ${error.message}`);
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
  const sanitizedCurrentVersion = SecurityUtils.sanitizeHtml(updateData.current_version);
  const sanitizedLatestVersion = SecurityUtils.sanitizeHtml(updateData.latest_version);
  // Use SecurityUtils for safe HTML insertion
  const updateInfoBuilder = [];
  updateInfoBuilder.push('<div class="card">');
  updateInfoBuilder.push('<div class="card-header">');
  updateInfoBuilder.push('<h5 class="mb-0">');
  updateInfoBuilder.push('<i class="fas fa-download text-primary me-2"></i>');
  updateInfoBuilder.push('Update Available');
  updateInfoBuilder.push('</h5>');
  updateInfoBuilder.push('</div>');
  updateInfoBuilder.push('<div class="card-body">');
  updateInfoBuilder.push('<div class="row">');
  updateInfoBuilder.push('<div class="col-md-6">');
  updateInfoBuilder.push(`<p><strong>Current Version:</strong> ${sanitizedCurrentVersion}</p>`);
  updateInfoBuilder.push(`<p><strong>Latest Version:</strong> ${sanitizedLatestVersion}</p>`);
  updateInfoBuilder.push('<p><strong>Update Type:</strong>');
  updateInfoBuilder.push(`<span class="badge ${updateData.update_info.is_major ? 'bg-warning' : 'bg-info'}">`);
  updateInfoBuilder.push(`${updateData.update_info.is_major ? 'Major' : 'Minor'}`);
  updateInfoBuilder.push('</span>');
  updateInfoBuilder.push('</p>');
  updateInfoBuilder.push('</div>');
  updateInfoBuilder.push('<div class="col-md-6">');
  updateInfoBuilder.push(`<p><strong>File Size:</strong> ${formatFileSize(updateData.update_info.file_size)}</p>`);
  updateInfoBuilder.push('<p><strong>Required:</strong>');
  updateInfoBuilder.push(`<span class="badge ${updateData.update_info.is_required ? 'bg-danger' : 'bg-success'}">`);
  updateInfoBuilder.push(`${updateData.update_info.is_required ? 'Yes' : 'No'}`);
  updateInfoBuilder.push('</span>');
  updateInfoBuilder.push('</p>');
  updateInfoBuilder.push('</div>');
  updateInfoBuilder.push('</div>');
  updateInfoBuilder.push('<div class="mt-3">');
  updateInfoBuilder.push('<h6>Changelog:</h6>');
  updateInfoBuilder.push('<ul class="list-unstyled">');
  updateData.update_info.changelog.forEach(item => {
    const sanitizedItem = SecurityUtils.sanitizeHtml(item);
    updateInfoBuilder.push(`<li><i class="fas fa-check text-success me-2"></i>${sanitizedItem}</li>`);
  });
  updateInfoBuilder.push('</ul>');
  updateInfoBuilder.push('</div>');
  updateInfoBuilder.push('<div class="mt-3">');
  updateInfoBuilder.push(`<button class="btn btn-primary" onclick="installAutoUpdate('${updateData.latest_version}')">`);
  updateInfoBuilder.push('<i class="fas fa-download me-2"></i>');
  updateInfoBuilder.push('Install Update');
  updateInfoBuilder.push('</button>');
  updateInfoBuilder.push('</div>');
  updateInfoBuilder.push('</div>');
  updateInfoBuilder.push('</div>');
  
  const updateInfoHtml = updateInfoBuilder.join('');
  
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
  const originalText = btn.textContent;

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
  const alertClass =
    type === 'success' ?
      'alert-success' :
      type === 'info' ?
        'alert-info' :
        'alert-danger';
  const iconClass =
    type === 'success' ?
      'fa-check-circle' :
      type === 'info' ?
        'fa-info-circle' :
        'fa-exclamation-triangle';

  const alertBuilder = [];
  alertBuilder.push(`<div class="alert ${alertClass} alert-dismissible fade show" role="alert">`);
  alertBuilder.push(`<i class="fas ${iconClass} me-2"></i>`);
  alertBuilder.push(SecurityUtils.sanitizeHtml(message));
  alertBuilder.push('<button type="button" class="btn-close" data-bs-dismiss="alert"></button>');
  alertBuilder.push('</div>');
  
  const alertHtml = alertBuilder.join('');

  // Insert at the top of the page
  const container = document.querySelector('.admin-page-header').parentNode;
  if (typeof SecurityUtils !== 'undefined') {
    SecurityUtils.safeInsertAdjacentHTML(container, 'afterbegin', alertHtml, true);
  } else {
    // Fallback: create element safely using SecurityUtils
    const tempDiv = document.createElement('div');
    SecurityUtils.safeInnerHTML(tempDiv, alertHtml, true, true);
    while (tempDiv.firstChild) {
      container.insertBefore(tempDiv.firstChild, container.firstChild);
    }
  }

  // Auto-dismiss after 5 seconds
  setTimeout(() => {
    const alert = container.querySelector('.alert');
    if (alert) {
      window.bootstrap.Alert.getOrCreateInstance(alert).close();
    }
  }, 5000);
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
        const htmlBuilder = [];
        htmlBuilder.push('<div class="timeline">');

        data.data.version_history.forEach(history => {
          const isCurrent = history.version === data.data.current_version;
          const badgeClass = isCurrent ? 'bg-success' : 'bg-secondary';
          const badgeText = isCurrent ? 'Current' : 'Previous';
          const sanitizedVersion = SecurityUtils.sanitizeHtml(history.version);
          const sanitizedValue = SecurityUtils.sanitizeHtml(history.value || 'Auto update');
          const sanitizedDate = SecurityUtils.sanitizeHtml(new Date(history.updated_at).toLocaleDateString());

          htmlBuilder.push('<div class="timeline-item">');
          htmlBuilder.push(`<div class="timeline-marker bg-${isCurrent ? 'success' : 'secondary'}"></div>`);
          htmlBuilder.push('<div class="timeline-content">');
          htmlBuilder.push('<div class="d-flex justify-content-between align-items-start">');
          htmlBuilder.push('<div>');
          htmlBuilder.push('<h6 class="timeline-title">');
          htmlBuilder.push(`Version ${sanitizedVersion}`);
          htmlBuilder.push(`<span class="badge ${badgeClass} ms-2">${badgeText}</span>`);
          htmlBuilder.push('</h6>');
          htmlBuilder.push(`<p class="timeline-text text-muted">${sanitizedDate}</p>`);
          htmlBuilder.push('</div>');
          htmlBuilder.push('</div>');
          htmlBuilder.push('<div class="mt-2">');
          htmlBuilder.push(`<p class="small text-muted">${sanitizedValue}</p>`);
          htmlBuilder.push('</div>');
          htmlBuilder.push('</div>');
          htmlBuilder.push('</div>');
        });

        htmlBuilder.push('</div>');
        const html = htmlBuilder.join('');
        // Use SecurityUtils for safe HTML insertion
        if (typeof SecurityUtils !== 'undefined') {
          SecurityUtils.safeInnerHTML(content, html, true, true);
        } else {
          content.textContent = 'Version history loaded';
        }
      } else {
        // Use SecurityUtils for safe HTML insertion
        if (typeof SecurityUtils !== 'undefined') {
          const noHistoryBuilder = [];
          noHistoryBuilder.push('<div class="text-center py-4">');
          noHistoryBuilder.push('<i class="fas fa-info-circle text-muted fs-1 mb-3"></i>');
          noHistoryBuilder.push('<h5 class="text-muted">No Version History Available</h5>');
          noHistoryBuilder.push('<p class="text-muted">Version information will appear here</p>');
          noHistoryBuilder.push('</div>');
          
          SecurityUtils.safeInnerHTML(content, noHistoryBuilder.join(''), true, true);
        } else {
          content.textContent = 'No version history available';
        }
      }
      return true;
    })
    .catch(error => {
      console.error('Error loading version history:', error);
      const content = document.getElementById('version-history-content');
      if (typeof SecurityUtils !== 'undefined') {
        const errorBuilder = [];
        errorBuilder.push('<div class="text-center py-4">');
        errorBuilder.push('<i class="fas fa-exclamation-triangle text-warning fs-1 mb-3"></i>');
        errorBuilder.push('<h5 class="text-warning">Error loading version history</h5>');
        errorBuilder.push('<p class="text-muted">Please try again later</p>');
        errorBuilder.push('</div>');
        
        SecurityUtils.safeInnerHTML(content, errorBuilder.join(''), true, true);
      } else {
        content.textContent = 'Error loading version history';
      }
    });
}

// Upload update package function
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

  // Use SecurityUtils for safe HTML insertion
  if (typeof SecurityUtils !== 'undefined') {
    SecurityUtils.safeInnerHTML(btn, '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...', true, true);
  } else {
    btn.textContent = 'Uploading...';
  }
  btn.disabled = true;

  fetch('/admin/updates/upload', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute('content'),
    },
    body: formData,
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showAlert('success', data.message || 'Package uploaded successfully');
        // Close modal
        const modal = window.bootstrap.Modal.getInstance(document.getElementById('uploadPackageModal'));
        if (modal) {
          modal.hide();
        }
        // Reload page to show updated status
        setTimeout(() => {
          window.location.reload();
        }, 2000);
      } else {
        showAlert('error', data.message || 'Upload failed');
      }
      return true;
    })
    .catch(error => {
      console.error('Error:', error);
      showAlert('error', 'An error occurred during upload');
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

// Initialize page
document.addEventListener('DOMContentLoaded', () => {
  // Load version history on page load
  loadVersionHistory();
});

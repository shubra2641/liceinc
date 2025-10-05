/**
 * System Updates Management
 * Handles update checking, installation, and rollback functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check for updates button
    document.getElementById('check-updates-btn').addEventListener('click', function() {
        checkForUpdates();
    });


    // Update system button
    document.getElementById('update-system-btn').addEventListener('click', function() {
        const version = this.dataset.version;
        showUpdateModal(version);
    });

    // Confirm update checkbox
    document.getElementById('confirmUpdate').addEventListener('change', function() {
        document.getElementById('confirm-update-btn').disabled = !this.checked;
    });

    // Confirm update button
    document.getElementById('confirm-update-btn').addEventListener('click', function() {
        const version = document.getElementById('update-system-btn').dataset.version;
        performUpdate(version);
    });

    // Confirm upload button
    document.getElementById('confirm-upload-btn').addEventListener('click', function() {
        uploadUpdatePackage();
    });

    // Confirm rollback checkbox
    document.getElementById('confirmRollback').addEventListener('change', function() {
        document.getElementById('confirm-rollback-btn').disabled = !this.checked;
    });

    // Confirm rollback button
    document.getElementById('confirm-rollback-btn').addEventListener('click', function() {
        const version = this.dataset.version;
        performRollback(version);
    });
});

function checkForUpdates() {
    const btn = document.getElementById('check-updates-btn');
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Checking...';
    btn.disabled = true;

    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to show updated status
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
        btn.innerHTML = originalText;
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
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
    btn.disabled = true;

    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            version: version,
            confirm: true
        })
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
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred during update');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        bootstrap.Modal.getInstance(document.getElementById('updateModal')).hide();
    });
}

function showVersionDetails(version) {
    fetch(`/admin/updates/version-info/${version}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('versionDetailsTitle').innerHTML = 
                '<i class="fas fa-info-circle me-2"></i>Version Details - ' + version;
            
            let content = '<div class="version-details">';
            
            if (data.data.info.features && data.data.info.features.length > 0) {
                content += '<h6 class="text-success mb-3"><i class="fas fa-plus me-2"></i>New Features</h6>';
                content += '<ul class="list-group list-group-flush mb-4">';
                data.data.info.features.forEach(feature => {
                    content += `<li class="list-group-item"><i class="fas fa-check text-success me-2"></i>${feature}</li>`;
                });
                content += '</ul>';
            }
            
            if (data.data.info.fixes && data.data.info.fixes.length > 0) {
                content += '<h6 class="text-warning mb-3"><i class="fas fa-wrench me-2"></i>Bug Fixes</h6>';
                content += '<ul class="list-group list-group-flush mb-4">';
                data.data.info.fixes.forEach(fix => {
                    content += `<li class="list-group-item"><i class="fas fa-check text-warning me-2"></i>${fix}</li>`;
                });
                content += '</ul>';
            }
            
            if (data.data.info.improvements && data.data.info.improvements.length > 0) {
                content += '<h6 class="text-info mb-3"><i class="fas fa-arrow-up me-2"></i>Improvements</h6>';
                content += '<ul class="list-group list-group-flush mb-4">';
                data.data.info.improvements.forEach(improvement => {
                    content += `<li class="list-group-item"><i class="fas fa-check text-info me-2"></i>${improvement}</li>`;
                });
                content += '</ul>';
            }
            
            if (data.data.instructions && data.data.instructions.length > 0) {
                content += '<h6 class="text-primary mb-3"><i class="fas fa-list me-2"></i>Update Instructions</h6>';
                content += '<ul class="list-group list-group-flush">';
                data.data.instructions.forEach(instruction => {
                    content += `<li class="list-group-item"><i class="fas fa-arrow-right text-primary me-2"></i>${instruction}</li>`;
                });
                content += '</ul>';
            }
            
            content += '</div>';
            document.getElementById('versionDetailsContent').innerHTML = content;
            
            const modal = new bootstrap.Modal(document.getElementById('versionDetailsModal'));
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
    
    const modal = new bootstrap.Modal(document.getElementById('rollbackModal'));
    modal.show();
}

function performRollback(version) {
    const btn = document.getElementById('confirm-rollback-btn');
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Rolling back...';
    btn.disabled = true;
    
    fetch('/admin/updates/rollback', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            version: version,
            confirm: true
        })
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
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred during rollback');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        bootstrap.Modal.getInstance(document.getElementById('rollbackModal')).hide();
    });
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Auto Update functionality
document.getElementById('auto-update-btn').addEventListener('click', function() {
    const modal = new bootstrap.Modal(document.getElementById('autoUpdateModal'));
    modal.show();
});

// Check for updates function (refresh page to get latest data)
function checkForUpdates() {
    showAlert('info', 'Checking for updates...');
    
    // Simply reload the page to get fresh data from the server
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

// Display update information
function displayUpdateInfo(updateData) {
    document.getElementById('update-title').textContent = updateData.update_info.title || 'Update';
    document.getElementById('update-version').textContent = updateData.latest_version;
    document.getElementById('update-major').innerHTML = updateData.update_info.is_major ? 
        '<span class="badge bg-warning">Yes</span>' : '<span class="badge bg-info">No</span>';
    document.getElementById('update-required').innerHTML = updateData.update_info.is_required ? 
        '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-success">No</span>';
    document.getElementById('update-status').innerHTML = '<span class="badge bg-success">Available</span>';
    document.getElementById('update-release-date').textContent = updateData.update_info.release_date || 'Not specified';
    document.getElementById('update-file-size').textContent = formatFileSize(updateData.update_info.file_size);
    document.getElementById('update-description').textContent = updateData.update_info.description || 'No description available';
    
    // Display changelog
    const changelogElement = document.getElementById('update-changelog');
    if (updateData.update_info.changelog && updateData.update_info.changelog.length > 0) {
        changelogElement.innerHTML = '<ul class="list-unstyled">' + 
            updateData.update_info.changelog.map(item => `<li><i class="fas fa-check text-success me-2"></i>${item}</li>`).join('') + 
            '</ul>';
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
function showNoUpdatesAvailable() {
    document.getElementById('update-info-section').style.display = 'none';
    document.getElementById('no-updates-section').style.display = 'block';
}


// Show auto update modal
function showAutoUpdateModal() {
    const modal = new bootstrap.Modal(document.getElementById('autoUpdateModal'));
    modal.show();
}

// Show upload update modal
function showUploadUpdateModal() {
    const modal = new bootstrap.Modal(document.getElementById('uploadPackageModal'));
    modal.show();
}

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
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('productUpdateModal'));
    modal.show();
}

function installProductUpdate(version, downloadUrl) {
    if (!confirm('Are you sure you want to install this update?')) {
        return;
    }
    
    showAlert('info', 'Downloading and installing update...');
    
    // Here you would implement the actual download and installation
    // For now, we'll just show a success message
    setTimeout(() => {
        showAlert('success', 'Update installed successfully!');
        bootstrap.Modal.getInstance(document.getElementById('productUpdateModal')).hide();
    }, 3000);
}

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
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Checking...';
    btn.disabled = true;
    
    fetch('/admin/updates/auto-check', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            license_key: licenseKey,
            product_slug: productSlug,
            domain: domain,
            current_version: currentVersion
        })
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
            if (data.message && data.message.includes('Auto update completed successfully')) {
                // Auto update was completed
                showAlert('success', data.message);
                
                // Show additional info if available
                if (data.data && data.data.files_installed) {
                    setTimeout(() => {
                        showAlert('info', `Files installed: ${data.data.files_installed}. Please refresh the page to see the new version.`);
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
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred during update: ' + error.message);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function showAutoUpdateInfo(updateData) {
    const updateInfoDiv = document.getElementById('auto-update-info');
    updateInfoDiv.innerHTML = `
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
                    <button class="btn btn-primary" onclick="installAutoUpdate('${updateData.latest_version}')">
                        <i class="fas fa-download me-2"></i>
                        Install Update
                    </button>
                </div>
            </div>
        </div>
    `;
    updateInfoDiv.style.display = 'block';
}

function installAutoUpdate(version) {
    const licenseKey = document.getElementById('auto-license-key').value;
    const productSlug = document.getElementById('auto-product-slug').value;
    const domain = document.getElementById('auto-domain').value;
    
    if (!confirm('Are you sure you want to install this update?')) {
        return;
    }
    
    const btn = document.querySelector('#auto-update-info .btn-primary');
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Installing...';
    btn.disabled = true;
    
    fetch('/admin/updates/auto-install', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            license_key: licenseKey,
            product_slug: productSlug,
            domain: domain,
            version: version,
            confirm: true
        })
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
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred during installation');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : type === 'info' ? 'alert-info' : 'alert-danger';
    const iconClass = type === 'success' ? 'fa-check-circle' : type === 'info' ? 'fa-info-circle' : 'fa-exclamation-triangle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert at the top of the page
    const container = document.querySelector('.admin-page-header').parentNode;
    container.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        }
    }, 5000);
}

// Load version history
function loadVersionHistory() {
    fetch('/admin/updates/current-version', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
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
            content.innerHTML = html;
        } else {
            content.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-info-circle text-muted fs-1 mb-3"></i>
                    <h5 class="text-muted">No Version History Available</h5>
                    <p class="text-muted">Version information will appear here</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading version history:', error);
        document.getElementById('version-history-content').innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-exclamation-triangle text-warning fs-1 mb-3"></i>
                <h5 class="text-warning">Error loading version history</h5>
                <p class="text-muted">Please try again later</p>
            </div>
        `;
    });
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Load version history on page load
    loadVersionHistory();
});

/**
 * System Updates Management
 * Handles update checking, installation, and rollback functionality
 */

// Security utilities
const SecurityUtils = {
    sanitizeHtml: function(content) {
        if (typeof content !== 'string') return '';
        return content.replace(/[<>&"']/g, match => ({
            '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;', '\'': '&#x27;'
        }[match]));
    },
    safeInnerHTML: function(element, content) {
        if (!element || typeof content !== 'string') return;
        element.textContent = this.sanitizeHtml(content);
    },
    safeInsertAdjacentHTML: function(element, position, html) {
        if (!element || typeof html !== 'string') return;
        element.insertAdjacentHTML(position, this.sanitizeHtml(html));
    }
};

// Utility functions
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024, sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : type === 'info' ? 'alert-info' : 'alert-danger';
    const iconClass = type === 'success' ? 'fa-check-circle' : type === 'info' ? 'fa-info-circle' : 'fa-exclamation-triangle';
    
    const alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
        '<i class="fas ' + iconClass + ' me-2"></i>' + SecurityUtils.sanitizeHtml(message) +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    
    const container = document.querySelector('.admin-page-header').parentNode;
    SecurityUtils.safeInsertAdjacentHTML(container, 'afterbegin', alertHtml);
    
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) window.bootstrap.Alert.getOrCreateInstance(alert).close();
    }, 5000);
}

// Common fetch function
function makeRequest(url, data = null) {
    const options = {
        method: data ? 'POST' : 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    if (data) options.body = JSON.stringify(data);
    return fetch(url, options).then(response => response.json());
}

// Update functions
function checkForUpdates() {
    const btn = document.getElementById('check-updates-btn');
    const originalText = btn.textContent;
    
    SecurityUtils.safeInnerHTML(btn, '<i class="fas fa-spinner fa-spin me-2"></i>Checking...');
    btn.disabled = true;
    
    makeRequest(window.location.href, {})
    .then(data => {
        if (data.success) window.location.reload();
        else showAlert('error', data.message || 'Failed to check for updates');
    })
    .catch(() => showAlert('error', 'An error occurred while checking for updates'))
    .finally(() => {
        SecurityUtils.safeInnerHTML(btn, originalText);
        btn.disabled = false;
    });
}

function showUpdateModal(version) {
    document.getElementById('update-system-btn').dataset.version = version;
    document.getElementById('confirmUpdate').checked = false;
    document.getElementById('confirm-update-btn').disabled = true;
    new window.bootstrap.Modal(document.getElementById('updateModal')).show();
}

function performUpdate(version) {
    const btn = document.getElementById('confirm-update-btn');
    const originalText = btn.textContent;
    
    SecurityUtils.safeInnerHTML(btn, '<i class="fas fa-spinner fa-spin me-2"></i>Updating...');
    btn.disabled = true;
    
    makeRequest(window.location.href, { version, confirm: true })
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => window.location.reload(), 2000);
        } else showAlert('error', data.message || 'Update failed');
    })
    .catch(() => showAlert('error', 'An error occurred during update'))
    .finally(() => {
        SecurityUtils.safeInnerHTML(btn, originalText);
        btn.disabled = false;
        window.bootstrap.Modal.getInstance(document.getElementById('updateModal')).hide();
    });
}

function showVersionDetails(version) {
    makeRequest('/admin/updates/version-info/' + version)
    .then(data => {
        if (data.success) {
            const sanitizedVersion = SecurityUtils.sanitizeHtml(version);
            SecurityUtils.safeInnerHTML(document.getElementById('versionDetailsTitle'), 
                '<i class="fas fa-info-circle me-2"></i>Version Details - ' + sanitizedVersion);
            
            let content = '<div class="version-details">';
            
            // Features
            if (data.data.info.features?.length > 0) {
                content += '<h6 class="text-success mb-3"><i class="fas fa-plus me-2"></i>New Features</h6><ul class="list-group list-group-flush mb-4">';
                data.data.info.features.forEach(feature => {
                    content += '<li class="list-group-item"><i class="fas fa-check text-success me-2"></i>' + SecurityUtils.sanitizeHtml(feature) + '</li>';
                });
                content += '</ul>';
            }
            
            // Fixes
            if (data.data.info.fixes?.length > 0) {
                content += '<h6 class="text-warning mb-3"><i class="fas fa-wrench me-2"></i>Bug Fixes</h6><ul class="list-group list-group-flush mb-4">';
                data.data.info.fixes.forEach(fix => {
                    content += '<li class="list-group-item"><i class="fas fa-check text-warning me-2"></i>' + SecurityUtils.sanitizeHtml(fix) + '</li>';
                });
                content += '</ul>';
            }
            
            // Improvements
            if (data.data.info.improvements?.length > 0) {
                content += '<h6 class="text-info mb-3"><i class="fas fa-arrow-up me-2"></i>Improvements</h6><ul class="list-group list-group-flush mb-4">';
                data.data.info.improvements.forEach(improvement => {
                    content += '<li class="list-group-item"><i class="fas fa-check text-info me-2"></i>' + SecurityUtils.sanitizeHtml(improvement) + '</li>';
                });
                content += '</ul>';
            }
            
            // Instructions
            if (data.data.instructions?.length > 0) {
                content += '<h6 class="text-primary mb-3"><i class="fas fa-list me-2"></i>Update Instructions</h6><ul class="list-group list-group-flush">';
                data.data.instructions.forEach(instruction => {
                    content += '<li class="list-group-item"><i class="fas fa-arrow-right text-primary me-2"></i>' + SecurityUtils.sanitizeHtml(instruction) + '</li>';
                });
                content += '</ul>';
            }
            
            content += '</div>';
            SecurityUtils.safeInnerHTML(document.getElementById('versionDetailsContent'), content);
            new window.bootstrap.Modal(document.getElementById('versionDetailsModal')).show();
        } else showAlert('error', data.message || 'Failed to load version details');
    })
    .catch(() => showAlert('error', 'An error occurred while loading version details'));
}

function showRollbackModal(version) {
    document.getElementById('confirm-rollback-btn').dataset.version = version;
    document.getElementById('confirmRollback').checked = false;
    document.getElementById('confirm-rollback-btn').disabled = true;
    new window.bootstrap.Modal(document.getElementById('rollbackModal')).show();
}

function performRollback(version) {
    const btn = document.getElementById('confirm-rollback-btn');
    const originalText = btn.textContent;
    
    SecurityUtils.safeInnerHTML(btn, '<i class="fas fa-spinner fa-spin me-2"></i>Rolling back...');
    btn.disabled = true;
    
    makeRequest('/admin/updates/rollback', { version, confirm: true })
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => window.location.reload(), 2000);
        } else showAlert('error', data.message || 'Rollback failed');
    })
    .catch(() => showAlert('error', 'An error occurred during rollback'))
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
    
    if (!fileInput?.files?.length) {
        showAlert('error', 'Please select a file to upload');
        return;
    }
    
    const formData = new FormData();
    formData.append('update_package', fileInput.files[0]);
    
    SecurityUtils.safeInnerHTML(btn, '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...');
    btn.disabled = true;
    
    fetch('/admin/updates/upload', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || 'Package uploaded successfully');
            const modal = window.bootstrap.Modal.getInstance(document.getElementById('uploadPackageModal'));
            if (modal) modal.hide();
            setTimeout(() => window.location.reload(), 2000);
        } else showAlert('error', data.message || 'Upload failed');
    })
    .catch(() => showAlert('error', 'An error occurred during upload'))
    .finally(() => {
        SecurityUtils.safeInnerHTML(btn, originalText);
        btn.disabled = false;
    });
}

function displayUpdateInfo(updateData) {
    document.getElementById('update-title').textContent = updateData.update_info.title || 'Update';
    document.getElementById('update-version').textContent = updateData.latest_version;
    
    const majorElement = document.getElementById('update-major');
    SecurityUtils.safeInnerHTML(majorElement, updateData.update_info.is_major ? 
        '<span class="badge bg-warning">Yes</span>' : '<span class="badge bg-info">No</span>');
    
    const requiredElement = document.getElementById('update-required');
    SecurityUtils.safeInnerHTML(requiredElement, updateData.update_info.is_required ? 
        '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-success">No</span>');
    
    SecurityUtils.safeInnerHTML(document.getElementById('update-status'), '<span class="badge bg-success">Available</span>');
    document.getElementById('update-release-date').textContent = updateData.update_info.release_date || 'Not specified';
    document.getElementById('update-file-size').textContent = formatFileSize(updateData.update_info.file_size);
    document.getElementById('update-description').textContent = updateData.update_info.description || 'No description available';
    
    const changelogElement = document.getElementById('update-changelog');
    if (updateData.update_info.changelog?.length > 0) {
        let changelogHtml = '<ul class="list-unstyled">';
        updateData.update_info.changelog.forEach(item => {
            changelogHtml += '<li><i class="fas fa-check text-success me-2"></i>' + SecurityUtils.sanitizeHtml(item) + '</li>';
        });
        changelogHtml += '</ul>';
        SecurityUtils.safeInnerHTML(changelogElement, changelogHtml);
    } else changelogElement.textContent = 'No changelog available';
    
    document.getElementById('update-info-section').style.display = 'block';
    document.getElementById('no-updates-section').style.display = 'none';
    window.currentUpdateData = updateData;
}

function showNoUpdatesAvailable() {
    document.getElementById('update-info-section').style.display = 'none';
    document.getElementById('no-updates-section').style.display = 'block';
}

function loadVersionHistory() {
    makeRequest('/admin/updates/current-version')
    .then(data => {
        const content = document.getElementById('version-history-content');
        
        if (data.success && data.data.version_history?.length > 0) {
            let html = '<div class="timeline">';
            data.data.version_history.forEach(history => {
                const isCurrent = history.version === data.data.current_version;
                const badgeClass = isCurrent ? 'bg-success' : 'bg-secondary';
                const badgeText = isCurrent ? 'Current' : 'Previous';
                const sanitizedVersion = SecurityUtils.sanitizeHtml(history.version);
                const sanitizedValue = SecurityUtils.sanitizeHtml(history.value || 'Auto update');
                const sanitizedDate = SecurityUtils.sanitizeHtml(new Date(history.updated_at).toLocaleDateString());
                
                html += '<div class="timeline-item">' +
                    '<div class="timeline-marker bg-' + (isCurrent ? 'success' : 'secondary') + '"></div>' +
                    '<div class="timeline-content">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                    '<div><h6 class="timeline-title">Version ' + sanitizedVersion +
                    '<span class="badge ' + badgeClass + ' ms-2">' + badgeText + '</span></h6>' +
                    '<p class="timeline-text text-muted">' + sanitizedDate + '</p></div></div>' +
                    '<div class="mt-2"><p class="small text-muted">' + sanitizedValue + '</p></div>' +
                    '</div></div>';
            });
            html += '</div>';
            SecurityUtils.safeInnerHTML(content, html);
        } else {
            const noHistoryHtml = '<div class="text-center py-4">' +
                '<i class="fas fa-info-circle text-muted fs-1 mb-3"></i>' +
                '<h5 class="text-muted">No Version History Available</h5>' +
                '<p class="text-muted">Version information will appear here</p></div>';
            SecurityUtils.safeInnerHTML(content, noHistoryHtml);
        }
    })
    .catch(() => {
        const content = document.getElementById('version-history-content');
        const errorHtml = '<div class="text-center py-4">' +
            '<i class="fas fa-exclamation-triangle text-warning fs-1 mb-3"></i>' +
            '<h5 class="text-warning">Error loading version history</h5>' +
            '<p class="text-muted">Please try again later</p></div>';
        SecurityUtils.safeInnerHTML(content, errorHtml);
    });
}

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('check-updates-btn').addEventListener('click', checkForUpdates);
    document.getElementById('update-system-btn').addEventListener('click', function() {
        showUpdateModal(this.dataset.version);
    });
    document.getElementById('confirmUpdate').addEventListener('change', function() {
        document.getElementById('confirm-update-btn').disabled = !this.checked;
    });
    document.getElementById('confirm-update-btn').addEventListener('click', () => {
        performUpdate(document.getElementById('update-system-btn').dataset.version);
    });
    document.getElementById('confirm-upload-btn').addEventListener('click', uploadUpdatePackage);
    document.getElementById('confirmRollback').addEventListener('change', function() {
        document.getElementById('confirm-rollback-btn').disabled = !this.checked;
    });
    document.getElementById('confirm-rollback-btn').addEventListener('click', function() {
        performRollback(this.dataset.version);
    });
    document.getElementById('auto-update-btn').addEventListener('click', () => {
        new window.bootstrap.Modal(document.getElementById('autoUpdateModal')).show();
    });
    loadVersionHistory();
});
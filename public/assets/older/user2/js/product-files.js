/**
 * Product Files JavaScript
 * Handles file upload, download, and UI interactions
 */

// Constants for magic numbers - using window object to avoid conflicts
window.PRODUCT_FILES_CONSTANTS = {
  ZERO: 0,
  ONE: 1,
  TWO: 2,
  EIGHT: 8,
  TEN: 10,
  FIFTY: 50,
  HUNDRED: 100,
  FIVE_HUNDRED: 500,
  THOUSAND: 1000,
  FIFTEEN_HUNDRED: 1500,
  TWO_THOUSAND: 2000,
  FIVE_THOUSAND: 5000,
  MAX_FILE_SIZE: 1024,
  RADIX_BASE: 10
};

$(document).ready(function() {
    // Initialize product files functionality
    initProductFiles();
});

function initProductFiles() {
    // File upload functionality
    initFileUpload();
    
    // Download functionality
    initDownloadButtons();
    
    // File management
    initFileManagement();
    
    // Statistics
    initStatistics();
}

/**
 * Initialize file upload functionality
 */
function initFileUpload() {
    const fileInput = $('#file');
    const uploadForm = $('#fileUploadForm');
    const uploadArea = $('.file-upload-area');
    
    // Drag and drop functionality
    if (uploadArea.length) {
        uploadArea.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        });
        
        uploadArea.on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
        });
        
        uploadArea.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > window.PRODUCT_FILES_CONSTANTS.ZERO) {
                fileInput[window.PRODUCT_FILES_CONSTANTS.ZERO].files = files;
                handleFileSelection(files[window.PRODUCT_FILES_CONSTANTS.ZERO]);
            }
        });
        
        uploadArea.on('click', function() {
            fileInput.click();
        });
    }
    
    // File input change
    fileInput.on('change', function() {
        const file = this.files[window.PRODUCT_FILES_CONSTANTS.ZERO];
        if (file) {
            handleFileSelection(file);
        }
    });
    
    // Form submission
    uploadForm.on('submit', function(e) {
        e.preventDefault();
        handleFileUpload();
    });
}

/**
 * Handle file selection
 */
function handleFileSelection(file) {
        const maxSize = window.PRODUCT_FILES_CONSTANTS.HUNDRED * window.PRODUCT_FILES_CONSTANTS.MAX_FILE_SIZE * window.PRODUCT_FILES_CONSTANTS.MAX_FILE_SIZE; // 100MB
    const allowedTypes = [
        'application/zip',
        'application/x-zip-compressed',
        'application/x-rar-compressed',
        'application/pdf',
        'text/plain',
        'application/json',
        'application/xml',
        'text/xml',
        'application/javascript',
        'text/css',
        'text/html',
        'application/php',
        'application/x-php',
        'text/php',
        'application/sql',
        'text/sql',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/svg+xml'
    ];
    
    // Validate file size
    if (file.size > maxSize) {
        showAlert('error', 'File size cannot exceed 100MB');
        return;
    }
    
    // Validate file type
    if (!allowedTypes.includes(file.type)) {
        showAlert('error', 'File type not allowed');
        return;
    }
    
    // Show file info
    showFileInfo(file);
}

/**
 * Show file information
 */
function showFileInfo(file) {
    const fileInfo = `
        <div class="file-info mt-2">
            <small class="text-muted">
                <i class="fas fa-file mr-1"></i>
                ${file.name} (${formatFileSize(file.size)})
            </small>
        </div>
    `;
    
    $('.file-upload-area').after(fileInfo);
}

/**
 * Handle file upload
 */
function handleFileUpload() {
    const form = $('#fileUploadForm');
    const formData = new window.FormData(form[window.PRODUCT_FILES_CONSTANTS.ZERO]);
    const submitBtn = form.find('button[type="submit"]');
    const originalText = submitBtn.html();
    
    // Show loading state
    submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>' + getTranslation('Uploading...')).prop('disabled', true);
    
    // Show progress bar
    showProgressBar();
    
    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function(evt) {
                if (evt.lengthComputable) {
                    const percentComplete = evt.loaded / evt.total * window.PRODUCT_FILES_CONSTANTS.HUNDRED;
                    updateProgressBar(percentComplete);
                }
            }, false);
            return xhr;
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                setTimeout(() => {
                    location.reload();
                }, window.PRODUCT_FILES_CONSTANTS.FIFTEEN_HUNDRED);
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                Object.keys(errors).forEach(function(key) {
                    showAlert('error', errors[key][window.PRODUCT_FILES_CONSTANTS.ZERO]);
                });
            } else {
                showAlert('error', 'File upload failed');
            }
        },
        complete: function() {
            submitBtn.html(originalText).prop('disabled', false);
            hideProgressBar();
        }
    });
}

/**
 * Initialize download buttons
 */
function initDownloadButtons() {
    // Add loading state to download buttons
    $('a[href*="download"]').on('click', function() {
        const btn = $(this);
        const originalText = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>' + getTranslation('Downloading...')).prop('disabled', true);
        
        // Re-enable button after 5 seconds (in case download doesn't start)
        setTimeout(function() {
            btn.html(originalText).prop('disabled', false);
        }, window.PRODUCT_FILES_CONSTANTS.FIVE_THOUSAND);
    });
    
    // Show download confirmation for large files
    $('a[href*="download"]').on('click', function(e) {
        const fileSize = $(this).closest('.card').find('.font-weight-bold').first().text();
        const sizeInMB = parseFloat(fileSize);
        
        if (sizeInMB > window.PRODUCT_FILES_CONSTANTS.FIFTY) { // Files larger than 50MB
            if (!confirm('This file is large (' + fileSize + '). Do you want to continue?')) {
                e.preventDefault();
            }
        }
    });
}

/**
 * Initialize file management
 */
function initFileManagement() {
    // Edit file functionality
    $('.edit-file-btn').on('click', function() {
        const fileId = $(this).data('file-id');
        const description = $(this).data('description');
        const isActive = $(this).data('is-active');
        
        $('#editFileForm').data('file-id', fileId);
        $('#edit_description').val(description);
        $('#edit_is_active').prop('checked', isActive === window.PRODUCT_FILES_CONSTANTS.ONE);
        
        $('#editFileModal').modal('show');
    });
    
    // Edit form submission
    $('#editFileForm').on('submit', function(e) {
        e.preventDefault();
        handleFileEdit();
    });
    
    // Delete file functionality
    $('.delete-file-btn').on('click', function() {
        const fileId = $(this).data('file-id');
        const filename = $(this).data('filename');
        
        if (confirm(getTranslation('Are you sure you want to delete the file') + ' "' + filename + '"?\n\n' + getTranslation('This action cannot be undone.'))) {
            handleFileDelete(fileId);
        }
    });
}

/**
 * Handle file edit
 */
function handleFileEdit() {
    const form = $('#editFileForm');
    const fileId = form.data('file-id');
    const formData = form.serialize();
    
    $.ajax({
        url: '/admin/product-files/' + fileId,
        type: 'PUT',
        data: formData,
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                $('#editFileModal').modal('hide');
                setTimeout(() => {
                    location.reload();
                }, window.PRODUCT_FILES_CONSTANTS.FIFTEEN_HUNDRED);
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                Object.keys(errors).forEach(function(key) {
                    showAlert('error', errors[key][window.PRODUCT_FILES_CONSTANTS.ZERO]);
                });
            } else {
                showAlert('error', 'File update failed');
            }
        }
    });
}

/**
 * Handle file delete
 */
function handleFileDelete(fileId) {
    $.ajax({
        url: '/admin/product-files/' + fileId,
        type: 'DELETE',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                $('tr[data-file-id="' + fileId + '"]').fadeOut(window.PRODUCT_FILES_CONSTANTS.FIVE_HUNDRED, function() {
                    $(this).remove();
                });
            } else {
                showAlert('error', response.message);
            }
        },
        error: function() {
            showAlert('error', 'File deletion failed');
        }
    });
}

/**
 * Initialize statistics
 */
function initStatistics() {
    // Animate statistics on page load
    $('.stat-item h4').each(function() {
        const $this = $(this);
        const countTo = parseInt($this.text(), window.PRODUCT_FILES_CONSTANTS.RADIX_BASE);
        
        $({ countNum: 0 }).animate({
            countNum: countTo
        }, {
            duration: 2000,
            easing: 'swing',
            step: function() {
                $this.text(Math.floor(this.countNum));
            },
            complete: function() {
                $this.text(this.countNum);
            }
        });
    });
}

/**
 * Show progress bar
 */
function showProgressBar() {
    const progressBar = `
        <div class="upload-progress">
            <div class="upload-progress-bar" style="width: 0%"></div>
        </div>
    `;
    
    $('#fileUploadForm').after(progressBar);
}

/**
 * Update progress bar
 */
function updateProgressBar(percent) {
    $('.upload-progress-bar').css('width', percent + '%');
}

/**
 * Hide progress bar
 */
function hideProgressBar() {
    $('.upload-progress').fadeOut(window.PRODUCT_FILES_CONSTANTS.FIVE_HUNDRED, function() {
        $(this).remove();
    });
}

/**
 * Show alert message
 */
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${icon} mr-2"></i>
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    $('.card-body').prepend(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        $('.alert').fadeOut(window.PRODUCT_FILES_CONSTANTS.FIVE_HUNDRED, function() {
            $(this).remove();
        });
    }, window.PRODUCT_FILES_CONSTANTS.FIVE_THOUSAND);
}

/**
 * Format file size
 */
function formatFileSize(bytes) {
    if (bytes === window.PRODUCT_FILES_CONSTANTS.ZERO) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(window.PRODUCT_FILES_CONSTANTS.TWO)) + ' ' + sizes[i];
}

/**
 * Get translation (placeholder function)
 */
function getTranslation(key) {
    // This would typically use Laravel's translation system
    // For now, return the key as fallback
    const translations = {
        'Uploading...': 'جاري الرفع...',
        'Downloading...': 'جاري التحميل...',
        'Are you sure you want to delete the file': 'هل أنت متأكد من حذف الملف',
        'This action cannot be undone.': 'هذا الإجراء لا يمكن التراجع عنه.'
    };
    
    return translations[key] || key;
}

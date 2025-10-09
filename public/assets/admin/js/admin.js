/**
 * Admin Dashboard JavaScript
 * Unified JavaScript for all admin pages
 */

// Constants for magic numbers - using window object to avoid conflicts
window.ADMIN_CONSTANTS = {
  NOTIFICATION_TIMEOUT: 4000,
  TOAST_TIMEOUT: 5000,
  DEBOUNCE_DELAY: 100,
  ANIMATION_DURATION: 300,
  SUCCESS_DELAY: 5000,
  ERROR_DELAY: 7000,
  WARNING_DELAY: 6000,
  INFO_DELAY: 5000,
  RETRY_DELAY: 200,
  FADE_DURATION: 200,
  SCROLL_OFFSET: 200,
  ZERO: 0,
  ONE: 1,
  TWO: 2,
  THREE: 3,
  SIX: 6,
  TEN: 10,
  FIFTY: 50,
  HUNDRED: 100,
  FIVE_HUNDRED: 500,
  THOUSAND: 1000,
  TWO_THOUSAND: 2000,
  THREE_THOUSAND: 3000,
  FOUR_THOUSAND: 4000,
  NEGATIVE_ONE: -1
};

// Global notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show`;
    
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 300px;
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 4 seconds
    setTimeout(function() {
        if (notification.parentNode) {
            notification.remove();
        }
    }, window.ADMIN_CONSTANTS.NOTIFICATION_TIMEOUT);
}

// Toast Notification System
class ToastManager {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Create toast container if it doesn't exist
        if (!document.getElementById('toast-container')) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('toast-container');
        }
    }

    show(message, type = 'info', title = null, duration = window.ADMIN_CONSTANTS.TOAST_TIMEOUT) {
        const toast = this.createToast(message, type, title);
        this.container.appendChild(toast);

        // Trigger animation
        setTimeout(() => {
            toast.classList.add('show');
        }, window.ADMIN_CONSTANTS.DEBOUNCE_DELAY);

        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                this.hide(toast);
            }, duration);
        }

        return toast;
    }

    createToast(message, type, title) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };

        const titles = {
            success: title || 'Success',
            error: title || 'Error',
            warning: title || 'Warning',
            info: title || 'Information'
        };

        toast.innerHTML = `
            <div class="toast-header">
                <i class="${icons[type]} toast-icon"></i>
                <h6 class="toast-title">${titles[type]}</h6>
                <button type="button" class="toast-close" onclick="window.toastManager.hide(this.closest('.toast'))">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;

        return toast;
    }

    hide(toast) {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, window.ADMIN_CONSTANTS.ANIMATION_DURATION);
    }

    success(message, title = null, duration = window.ADMIN_CONSTANTS.SUCCESS_DELAY) {
        return this.show(message, 'success', title, duration);
    }

    error(message, title = null, duration = window.ADMIN_CONSTANTS.ERROR_DELAY) {
        return this.show(message, 'error', title, duration);
    }

    warning(message, title = null, duration = window.ADMIN_CONSTANTS.WARNING_DELAY) {
        return this.show(message, 'warning', title, duration);
    }

    info(message, title = null, duration = window.ADMIN_CONSTANTS.INFO_DELAY) {
        return this.show(message, 'info', title, duration);
    }
}

// Initialize toast manager
const toastManager = new ToastManager();

// Make toastManager globally available
window.toastManager = toastManager;

// Session flash messages handler
document.addEventListener('DOMContentLoaded', function() {
    // Add a small delay to ensure toastManager is ready
    setTimeout(() => {
        // Check for session flash messages
        const successMessage = document.querySelector('[data-flash-success]');
        const errorMessage = document.querySelector('[data-flash-error]');
        const warningMessage = document.querySelector('[data-flash-warning]');
        const infoMessage = document.querySelector('[data-flash-info]');

        if (successMessage && window.toastManager) {
            window.toastManager.success(successMessage.textContent);
        }
        if (errorMessage && window.toastManager) {
            window.toastManager.error(errorMessage.textContent);
        }
        if (warningMessage && window.toastManager) {
            window.toastManager.warning(warningMessage.textContent);
        }
        if (infoMessage && window.toastManager) {
            window.toastManager.info(infoMessage.textContent);
        }
        }, window.ADMIN_CONSTANTS.FADE_DURATION);
});

class AdminDashboard {
    constructor() {
        this.selectedFile = null;
        this.init();
    }

    init() {
        this.initSummernote();
        this.initFormValidation();
        this.initAutoGeneration();
        this.initFileUploads();
        this.initTabs();
    }

    // Toast notification method for compatibility with existing code
    showToast(message, type = 'info', duration = window.ADMIN_CONSTANTS.TOAST_TIMEOUT) {
        if (typeof window.toastManager !== 'undefined') {
            switch(type) {
                case 'success':
                    return window.toastManager.success(message, null, duration);
                case 'error':
                    return window.toastManager.error(message, null, duration);
                case 'warning':
                    return window.toastManager.warning(message, null, duration);
                case 'info':
                default:
                    return window.toastManager.info(message, null, duration);
            }
        }
        return null;
    }

    // Initialize Tabs
    initTabs() {
        const tabButtons = document.querySelectorAll('[data-action="show-tab"]');
        const tabPanels = document.querySelectorAll('.admin-tab-panel');

        tabButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                
                const targetTab = button.getAttribute('data-tab');
                
                // Remove active class from all buttons
                tabButtons.forEach(btn => {
                    btn.classList.remove('admin-tab-btn-active');
                    btn.setAttribute('aria-selected', 'false');
                    btn.setAttribute('tabindex', '-1');
                });
                
                // Add active class to clicked button
                button.classList.add('admin-tab-btn-active');
                button.setAttribute('aria-selected', 'true');
                button.setAttribute('tabindex', '0');
                
                // Hide all tab panels
                tabPanels.forEach(panel => {
                    panel.classList.add('admin-tab-panel-hidden');
                    panel.setAttribute('aria-hidden', 'true');
                });
                
                // Show target tab panel
                const targetPanel = document.getElementById(targetTab);
                if (targetPanel) {
                    targetPanel.classList.remove('admin-tab-panel-hidden');
                    targetPanel.setAttribute('aria-hidden', 'false');
                }
            });
        });
    }

    initFileUploads() {
        this.initButtons();
        this.initModals();
        this.initTables();
        this.initIconPreview();
        // Initialize category preview
        this.initCategoryPreview();
        
        // Initialize article preview
        this.initArticlePreview();
        
        // Initialize serial fields
        this.initSerialFields();
        
        // Initialize confirmation dialogs
        this.initConfirmations();
        
        // Initialize user preview
        this.initUserPreview();
        
        // Initialize license preview
        this.initLicensePreview();
        
        // Initialize invoice preview
        this.initInvoicePreview();
        
        // Initialize email template preview
        this.initEmailTemplatePreview();
        
        // Initialize email template variables
        this.initEmailTemplateVariables();
        
        // Initialize email template test functions
        this.initEmailTemplateTest();
        
        // Initialize email template show functions
        this.initEmailTemplateShow();
        
        // Initialize email template index functions
        this.initEmailTemplateIndex();
        
        // Initialize invoice functions
        this.initInvoiceFunctions();
        
        // Initialize KB functions
        this.initKBFunctions();
        
        // Initialize all other page functions
        this.initAllPageFunctions();
        
        // Initialize programming languages functions
        this.initProgrammingLanguagesFunctions();
        
        // Initialize programming languages edit functions
        if (document.querySelector('.admin-programming-languages-edit')) {
            initProgrammingLanguagesEditFunctions();
        }
        
        // Initialize settings tabs (handled by the class method to avoid duplicate/global init)
        // if an .admin-settings-page exists, the class method initSettingsTabs will initialize tabs
        
        
        // Initialize programming languages create functions
        if (document.querySelector('.admin-programming-languages-create')) {
            initProgrammingLanguagesCreateFunctions();
        }
        
        // Initialize programming languages show tabs
        if (document.querySelector('.admin-programming-languages-show')) {
            initProgrammingLanguagesShowTabs();
        }
        
        // Initialize programming languages index tabs
        if (document.querySelector('.admin-programming-languages-index')) {
            initProgrammingLanguagesIndexTabs();
        }
        
        // Initialize reports functions
        if (document.querySelector('.admin-reports-page')) {
            initReportsFunctions();
        }
        
        // Initialize profile functions
        this.initProfileFunctions();
        
        // Initialize settings functions
        const settingsPage = document.querySelector('.admin-settings-page');
        const testApiBtn = document.getElementById('test-api-btn');
        // Log in development only
        if (typeof window !== 'undefined' && window.console && window.console.log) {
            window.console.log('Settings page check:', settingsPage);
            window.console.log('Test API button check:', testApiBtn);
        }
        
        if (settingsPage || testApiBtn) {
            // Log in development only
            if (typeof window !== 'undefined' && window.console && window.console.log) {
                window.console.log('Settings page detected, initializing settings functions');
            }
            this.initSettingsFunctions();
        } else {
            // Log in development only
            if (typeof window !== 'undefined' && window.console && window.console.log) {
                window.console.log('Settings page not detected');
            }
        }
        
        // Settings functions are initialized conditionally above
    }

    // Initialize Summernote Editor
    initSummernote() {
        // Check if Summernote is available
        if (typeof $.fn.summernote === 'undefined') {
            // Summernote is not loaded
            // Try again after a delay
            setTimeout(() => {
                this.initSummernote();
            }, window.ADMIN_CONSTANTS.FIVE_HUNDRED);
            return;
        }
        
        // Wait for DOM to be ready
        $(document).ready(() => {
            // Initialize all textareas with data-summernote attribute
            const textareas = $('textarea[data-summernote="true"]');
            
            if (textareas.length === 0) {
                
                return;
            }
            
            textareas.each((index, element) => {
                const $this = $(element);
                const toolbar = $this.data('toolbar') || 'standard';
                const placeholder = $this.data('placeholder') || '';
                
                try {
                    $this.summernote({
                        height: 200,
                        toolbar: this.getToolbarConfig(toolbar),
                        placeholder: placeholder,
                        focus: false
                    });
                } catch {
                    // Error initializing Summernote
                }
            });
        });
    }

    // Get toolbar configuration based on type
    getToolbarConfig(type) {
        const configs = {
            basic: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview']]
            ],
            standard: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video', 'hr']],
                ['view', ['fullscreen', 'codeview']],
                ['help', ['help']]
            ],
            minimal: [
                ['style', ['bold', 'italic', 'underline']],
                ['para', ['ul', 'ol']],
                ['insert', ['link']]
            ]
        };
        
        return configs[type] || configs.standard;
    }

    // Initialize form validation
    initFormValidation() {
        // Bootstrap form validation
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                // Add validation class for styling
                form.classList.add('was-validated');

                // If invalid, prevent submission
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    return;
                }

                // Proceeding with a real submission: disable all submit buttons in this form and show loading
                const submitButtons = form.querySelectorAll('button[type="submit"]');
                submitButtons.forEach(button => {
                    button.disabled = true;
                    button.classList.add('loading');
                });
                // Allow form to submit normally
            });
        });

        // Real-time validation
        const inputs = document.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });
        });
    }

    // Validate individual field
    validateField(field) {
        const isValid = field.checkValidity();
        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
        }
    }

    // Initialize auto-generation features
    initAutoGeneration() {
        // Auto-generate slug from name
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        
        if (nameInput && slugInput) {
            nameInput.addEventListener('input', () => {
                if (!slugInput.value) {
                    slugInput.value = this.generateSlug(nameInput.value);
                }
            });
        }

        // Auto-generate meta title from name
        const metaTitleInput = document.getElementById('meta_title');
        if (nameInput && metaTitleInput) {
            nameInput.addEventListener('input', () => {
                if (!metaTitleInput.value) {
                    metaTitleInput.value = nameInput.value;
                }
            });
        }
    }

    // Generate URL-friendly slug
    generateSlug(text) {
        return text
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            // trim leading/trailing hyphens (String.trim doesn't accept arguments)
            .replace(/^[-]+|[-]+$/g, '');
    }

    // Initialize file uploads
    initFileUploads() {
        // Image preview
        const imageInput = document.getElementById('image');
        if (imageInput) {
            imageInput.addEventListener('change', (e) => {
                this.handleImagePreview(e, 'image-preview');
            });
        }

        // Gallery images preview
        const galleryInput = document.getElementById('gallery_images');
        if (galleryInput) {
            galleryInput.addEventListener('change', (e) => {
                this.handleGalleryPreview(e, 'gallery-preview');
            });
        }

        // Drag and drop
        const fileAreas = document.querySelectorAll('.file-upload-area');
        fileAreas.forEach(area => {
            area.addEventListener('dragover', (e) => {
                e.preventDefault();
                area.classList.add('dragover');
            });

            area.addEventListener('dragleave', () => {
                area.classList.remove('dragover');
            });

            area.addEventListener('drop', (e) => {
                e.preventDefault();
                area.classList.remove('dragover');
                const files = e.dataTransfer.files;
                this.handleFileDrop(files, area);
            });
        });
    }

    // Handle image preview
    handleImagePreview(event, previewId) {
        const file = event.target.files[0];
        if (file) {
            const reader = new window.FileReader();
            reader.onload = (e) => {
                let preview = document.getElementById(previewId);
                if (!preview) {
                    preview = document.createElement('div');
                    preview.id = previewId;
                    preview.className = 'image-preview mt-2';
                    event.target.parentNode.appendChild(preview);
                }
                preview.innerHTML = `<img src="${e.target.result}" class="img-thumbnail image-preview">`;
            };
            reader.readAsDataURL(file);
        }
    }

    // Handle gallery preview
    handleGalleryPreview(event, previewId) {
        const files = event.target.files;
        if (files.length > 0) {
            let preview = document.getElementById(previewId);
            if (!preview) {
                preview = document.createElement('div');
                preview.id = previewId;
                preview.className = 'gallery-preview mt-2';
                event.target.parentNode.appendChild(preview);
            }
            
            preview.innerHTML = '';
            Array.from(files).forEach((file) => {
                const reader = new window.FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'img-thumbnail me-2 mb-2';
                    img.style.maxWidth = '100px';
                    img.style.maxHeight = '100px';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }
    }

    // Handle file drop
    handleFileDrop(files, area) {
        const input = area.querySelector('input[type="file"]');
        if (input) {
            try {
                // Use DataTransfer to build a FileList that can be assigned to input.files
                const dataTransfer = new window.DataTransfer();
                Array.from(files).forEach(file => dataTransfer.items.add(file));
                input.files = dataTransfer.files;
            } catch {
                // Fallback: some environments may not support DataTransfer constructor
                try {
                    input.files = files;
                } catch {
                    // Cannot programmatically set files; instead call any handler with the FileList
                    const evt = new window.CustomEvent('filesDropped', { detail: { files } });
                    input.dispatchEvent(evt);
                }
            }

            // Notify change handlers
            input.dispatchEvent(new window.Event('change'));
        }
    }

    // Initialize buttons
    initButtons() {
        // Envato data fetching
        const fetchEnvatoBtn = document.getElementById('fetch-envato-data');
        if (fetchEnvatoBtn) {
            fetchEnvatoBtn.addEventListener('click', () => {
                this.handleEnvatoFetch();
            });
        }

        // Previously we set loading on button click which could prevent form submission
        // We'll prefer disabling buttons on actual form submit (handled in initFormValidation) so here we keep only non-form buttons handling
        const nonFormButtons = document.querySelectorAll('button[type="submit"][form=""]');
        nonFormButtons.forEach(button => {
            button.addEventListener('click', () => {
                this.setButtonLoading(button, true);
            });
        });
    }

    // Handle Envato data fetching
    handleEnvatoFetch() {
        const envatoItemIdInput = document.getElementById('envato_item_id');
        const envatoLoading = document.getElementById('envato-loading');
        const envatoError = document.getElementById('envato-error');
        const fetchBtn = document.getElementById('fetch-envato-data');
        
        if (!envatoItemIdInput) return;
        
        const itemId = envatoItemIdInput.value.trim();
        if (!itemId) {
            this.showAlert('Please enter an Envato Item ID first', 'warning');
            return;
        }

        // Show loading state
        if (envatoLoading) envatoLoading.classList.remove('hidden');
        if (envatoError) envatoError.classList.add('hidden');
        if (fetchBtn) {
            fetchBtn.disabled = true;
            fetchBtn.classList.add('loading');
        }

        // Simulate API call (replace with actual implementation)
        setTimeout(() => {
            if (envatoLoading) envatoLoading.classList.add('hidden');
            if (fetchBtn) {
                fetchBtn.disabled = false;
                fetchBtn.classList.remove('loading');
            }
            // Add actual Envato API integration here
            
        }, window.ADMIN_CONSTANTS.TWO_THOUSAND);
    }

    // Set button loading state
    setButtonLoading(button, loading) {
        if (loading) {
            button.disabled = true;
            button.classList.add('loading');
        } else {
            button.disabled = false;
            button.classList.remove('loading');
        }
    }

    // Initialize modals
    initModals() {
        // Auto-focus first input in modals
        $('.modal').on('shown.bs.modal', function() {
            $(this).find('input, textarea, select').first().focus();
        });
    }

    // Bridge to global confirmation initializer (keeps compatibility)
    initConfirmations() {
        if (typeof initConfirmationsGlobal === 'function') {
            initConfirmationsGlobal();
        } else {
            // Fallback inline implementation
            document.addEventListener('click', (e) => {
                const button = e.target.closest('[data-confirm]');
                if (button) {
                    e.preventDefault();
                    const message = button.getAttribute('data-confirm');
                    if (window.confirm && confirm(message)) {
                        if (button.type === 'submit' && button.form) {
                            button.form.submit();
                        } else if (button.href) {
                            window.location.href = button.href;
                        }
                    }
                }
            });
        }
    }

    // Initialize tables
    initTables() {
        // Initialize DataTables if available
        if (typeof $.fn.DataTable !== 'undefined') {
            $('.admin-table').DataTable({
                responsive: true,
                pageLength: 25,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json'
                }
            });
        }
    }

    // Initialize icon preview for category forms
    initIconPreview() {
        const iconInput = document.getElementById('icon');
        const iconPreview = document.getElementById('icon-preview');
        
        if (iconInput && iconPreview) {
            iconInput.addEventListener('input', function() {
                const iconClass = this.value || 'fas fa-ticket-alt';
                iconPreview.innerHTML = `<i class="${iconClass}"></i>`;
            });
        }
    }

    initCategoryPreview() {
        const nameInput = document.getElementById('name');
        const descriptionInput = document.getElementById('description');
        const colorInput = document.getElementById('color');
        const colorTextInput = document.getElementById('color-text');
        // const previewDiv = document.getElementById('category-preview'); // Unused variable removed
        const previewName = document.getElementById('preview-name');
        const previewDescription = document.getElementById('preview-description');

        if (nameInput && previewName) {
            nameInput.addEventListener('input', function() {
                previewName.textContent = this.value || '{{ trans("app.Category Name") }}';
            });
        }

        if (descriptionInput && previewDescription) {
            descriptionInput.addEventListener('input', function() {
                previewDescription.textContent = this.value || '{{ trans("app.Category Description") }}';
            });
        }

        if (colorInput && colorTextInput) {
            colorInput.addEventListener('input', function() {
                const color = this.value;
                colorTextInput.value = color;
                // previewDiv.style.backgroundColor = color; // previewDiv removed
            });

            colorTextInput.addEventListener('input', function() {
                const color = this.value;
                if (/^#[0-9A-F]{6}$/i.test(color)) {
                    colorInput.value = color;
                    // previewDiv.style.backgroundColor = color; // previewDiv removed
                }
            });
        }
    }

    initArticlePreview() {
        const titleInput = document.getElementById('title');
        const excerptInput = document.getElementById('excerpt');
        // const previewDiv = document.getElementById('article-preview'); // Unused variable removed
        const previewTitle = document.getElementById('preview-title');
        const previewExcerpt = document.getElementById('preview-excerpt');

        if (titleInput && previewTitle) {
            titleInput.addEventListener('input', function() {
                previewTitle.textContent = this.value || '{{ trans("app.Article Title") }}';
            });
        }

        if (excerptInput && previewExcerpt) {
            excerptInput.addEventListener('input', function() {
                previewExcerpt.textContent = this.value || '{{ trans("app.Article Excerpt") }}';
            });
        }
    }

    initSerialFields() {
        const requiresSerialCheckbox = document.getElementById('requires_serial');
        const serialFields = document.getElementById('serial-fields');

        if (requiresSerialCheckbox && serialFields) {
            requiresSerialCheckbox.addEventListener('change', function() {
                serialFields.style.display = this.checked ? 'block' : 'none';
            });
        }
    }

    initUserPreview() {
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const roleInputs = document.querySelectorAll('input[name="role"]');
        // const previewDiv = document.getElementById('user-preview'); // Unused variable removed
        const previewName = document.getElementById('preview-name');
        const previewEmail = document.getElementById('preview-email');
        const previewRole = document.getElementById('preview-role');

        if (nameInput && previewName) {
            nameInput.addEventListener('input', function() {
                previewName.textContent = this.value || '{{ trans("app.User Name") }}';
            });
        }

        if (emailInput && previewEmail) {
            emailInput.addEventListener('input', function() {
                previewEmail.textContent = this.value || '{{ trans("app.user@example.com") }}';
            });
        }

        if (roleInputs.length > 0 && previewRole) {
            roleInputs.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        const roleText = this.value === 'admin' ? '{{ trans("app.Administrator") }}' : '{{ trans("app.User") }}';
                        const roleClass = this.value === 'admin' ? 'danger' : 'secondary';
                        previewRole.textContent = roleText;
                        previewRole.className = `badge bg-${roleClass} mt-2`;
                    }
                });
            });
        }
    }

    initLicensePreview() {
        const productSelect = document.getElementById('product_id');
        const userSelect = document.getElementById('user_id');
        const statusSelect = document.getElementById('status');
        const maxDomainsInput = document.getElementById('max_domains');
        // const previewDiv = document.getElementById('license-preview'); // Unused variable removed
        const previewProduct = document.getElementById('preview-product');
        const previewUser = document.getElementById('preview-user');
        const previewStatus = document.getElementById('preview-status');
        const previewDomains = document.getElementById('preview-domains');

        if (productSelect && previewProduct) {
            productSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                previewProduct.textContent = selectedOption.text || '{{ trans("app.Product Name") }}';
            });
        }

        if (userSelect && previewUser) {
            userSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                previewUser.textContent = selectedOption.text || '{{ trans("app.User Name") }}';
            });
        }

        if (statusSelect && previewStatus) {
            statusSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const statusValue = selectedOption.value;
                const statusText = selectedOption.text;
                const statusClass = statusValue === 'active' ? 'success' : 'danger';
                previewStatus.textContent = statusText;
                previewStatus.className = `badge bg-${statusClass} mt-2`;
            });
        }

        if (maxDomainsInput && previewDomains) {
            maxDomainsInput.addEventListener('input', function() {
                previewDomains.textContent = this.value || '1';
            });
        }
    }

    initInvoicePreview() {
        const userSelect = document.getElementById('user_id');
        const amountInput = document.getElementById('amount');
        const currencySelect = document.getElementById('currency');
        const statusSelect = document.getElementById('status');
        const dueDateInput = document.getElementById('due_date');
        // const previewDiv = document.getElementById('invoice-preview'); // Unused variable removed
        const previewCustomer = document.getElementById('preview-customer');
        const previewAmount = document.getElementById('preview-amount');
        const previewStatus = document.getElementById('preview-status');
        const previewDueDate = document.getElementById('preview-due-date');

        if (userSelect && previewCustomer) {
            userSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                previewCustomer.textContent = selectedOption.text.split(' (')[0] || '{{ trans("app.Customer Name") }}';
            });
        }

        if (amountInput && currencySelect && previewAmount) {
            function updateAmount() {
                if (amountInput.value && currencySelect.value) {
                    previewAmount.textContent = `${amountInput.value} ${currencySelect.value}`;
                } else {
                    previewAmount.textContent = '$0.00 USD';
                }
            }
            
            amountInput.addEventListener('input', updateAmount);
            currencySelect.addEventListener('change', updateAmount);
        }

        if (statusSelect && previewStatus) {
            statusSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const statusValue = selectedOption.value;
                const statusText = selectedOption.text;
                const statusClass = statusValue === 'paid' ? 'success' : 
                                  statusValue === 'overdue' ? 'danger' : 'warning';
                previewStatus.textContent = statusText;
                previewStatus.className = `badge bg-${statusClass} mt-2`;
            });
        }

        if (dueDateInput && previewDueDate) {
            dueDateInput.addEventListener('change', function() {
                if (this.value) {
                    const date = new Date(this.value);
                    previewDueDate.textContent = date.toLocaleDateString('en-US', { 
                        year: 'numeric', month: 'short', day: 'numeric' 
                    });
                } else {
                    previewDueDate.textContent = '{{ trans("app.No Due Date") }}';
                }
            });
        }
    }

    initEmailTemplatePreview() {
        const subjectInput = document.getElementById('subject');
        const bodyTextarea = document.getElementById('body');
        const nameInput = document.getElementById('name');
        const previewSubject = document.getElementById('preview-subject');
        const previewContent = document.getElementById('preview-content');
        const previewTemplateId = document.getElementById('preview-template-id');

        // Auto-generate template name from subject
        if (subjectInput && nameInput) {
            subjectInput.addEventListener('input', function() {
                if (!nameInput.value) {
                    const value = this.value
                        .toLowerCase()
                        .replace(/[^a-z0-9\s]/g, '')
                        .replace(/\s+/g, '_')
                        .substring(0, 50);
                    nameInput.value = value;
                }
            });
        }

        // Update preview
        function updatePreview() {
            if (previewSubject && subjectInput) {
                previewSubject.textContent = subjectInput.value || '{{ trans("app.Email Subject") }}';
            }
            
            if (previewContent && bodyTextarea) {
                const content = bodyTextarea.value || '{{ trans("app.Email content will appear here") }}';
                previewContent.innerHTML = content;
            }

            if (previewTemplateId && nameInput) {
                previewTemplateId.textContent = nameInput.value || '{{ trans("app.Auto Generated") }}';
            }
        }

        // Event listeners for preview
        if (subjectInput) subjectInput.addEventListener('input', updatePreview);
        if (bodyTextarea) bodyTextarea.addEventListener('input', updatePreview);
        if (nameInput) nameInput.addEventListener('input', updatePreview);

        // Variable copy functionality
        const variableItems = document.querySelectorAll('.variable-item[data-variable]');
        variableItems.forEach(item => {
            item.addEventListener('click', function() {
                const variable = this.getAttribute('data-variable');
                copyToClipboard(variable);
            });
        });

        // Copy to clipboard functionality
        function copyToClipboard(text) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    showNotification('{{ trans("app.Variable copied to clipboard!") }}', 'success');
                }).catch(function() {
                    fallbackCopyToClipboard(text);
                });
            } else {
                fallbackCopyToClipboard(text);
            }
        }

        // Fallback copy method
        function fallbackCopyToClipboard(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                showNotification('{{ trans("app.Variable copied to clipboard!") }}', 'success');
            } catch {
                showNotification('{{ trans("app.Failed to copy variable") }}', 'error');
            }
            
            document.body.removeChild(textArea);
        }


        // Initialize preview
        updatePreview();
    }

    // Show alert message
    showAlert(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Insert at the top of the page
        const container = document.querySelector('.container-fluid');
        if (container) {
            container.insertAdjacentHTML('afterbegin', alertHtml);
        }
    }

    // Utility methods
    static showLoading(element) {
        element.classList.add('loading');
    }

    static hideLoading(element) {
        element.classList.remove('loading');
    }

    static showElement(element) {
        element.classList.remove('hidden');
    }

    static hideElement(element) {
        element.classList.add('hidden');
    }

    // Initialize Email Template Variables
    initEmailTemplateVariables() {
        const variableItems = document.querySelectorAll('.variable-item[data-variable]');
        variableItems.forEach(item => {
            item.addEventListener('click', (event) => {
                const variable = event.currentTarget.getAttribute('data-variable');
                this.copyToClipboard(variable);
            });
        });
    }

    // Copy to clipboard functionality
    copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                this.showNotification('Variable copied to clipboard!', 'success');
            }).catch(() => {
                this.fallbackCopyToClipboard(text);
            });
        } else {
            this.fallbackCopyToClipboard(text);
        }
    }

    // Fallback copy method
    fallbackCopyToClipboard(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            this.showNotification('Variable copied to clipboard!', 'success');
        } catch {
            this.showNotification('Failed to copy variable', 'error');
        }
        
        document.body.removeChild(textArea);
    }

    // Show notification
    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `admin-notification admin-notification-${type}`;
        
        const icon = type === 'success' ? 
            '<i class="fas fa-check-circle"></i>' :
            '<i class="fas fa-times-circle"></i>';
        
        notification.innerHTML = `
            <div class="admin-notification-content">
                <div class="admin-notification-icon">${icon}</div>
                <div class="admin-notification-message">${message}</div>
            </div>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 20px;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            opacity: 0;
            transform: translateX(100%) scale(0.9);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            ${type === 'success' ? 
                'background: linear-gradient(135deg, #10b981 0%, #059669 100%);' : 
                'background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);'
            }
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0) scale(1)';
        }, window.ADMIN_CONSTANTS.DEBOUNCE_DELAY);
        
        // Remove after 4 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%) scale(0.9)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 400);
        }, 4000);
    }


    // Update Email Preview
    updateEmailPreview() {
        const testDataInputs = document.querySelectorAll('input[name^="test_data"]');
        const previewSubject = document.getElementById('preview-subject');
        const previewBody = document.getElementById('preview-body');

        if (previewSubject) {
            // Get the original subject from data attribute or use a default
            const originalSubject = previewSubject.getAttribute('data-original-subject') || '{{ $emailTemplate->subject }}';
            let subject = originalSubject;
            testDataInputs.forEach(input => {
                const variable = input.name.replace('test_data[', '').replace(']', '');
                const value = input.value || '{{' + variable + '}}';
                subject = subject.replace(new RegExp('{{' + variable + '}}', 'g'), value);
            });
            previewSubject.textContent = subject;
        }

        if (previewBody) {
            // Get the original body from data attribute or use a default
            const originalBody = previewBody.getAttribute('data-original-body') || '{!! $emailTemplate->body !!}';
            let body = originalBody;
            testDataInputs.forEach(input => {
                const variable = input.name.replace('test_data[', '').replace(']', '');
                const value = input.value || '{{' + variable + '}}';
                body = body.replace(new RegExp('{{' + variable + '}}', 'g'), value);
            });
            previewBody.innerHTML = body;
        }
    }

    // Preview Test Email
    previewTest() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Test Email Preview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="email-preview-container">
                            <div class="email-preview-header">
                                <h6><strong>Subject:</strong> <span id="modal-preview-subject"></span></h6>
                            </div>
                            <div class="email-preview-content">
                                <div id="modal-preview-body"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        // Update modal preview
        const testDataInputs = document.querySelectorAll('input[name^="test_data"]');
        const modalPreviewSubject = document.getElementById('modal-preview-subject');
        const modalPreviewBody = document.getElementById('modal-preview-body');

        if (modalPreviewSubject) {
            const originalSubject = modalPreviewSubject.getAttribute('data-original-subject') || '{{ $emailTemplate->subject }}';
            let subject = originalSubject;
            testDataInputs.forEach(input => {
                const variable = input.name.replace('test_data[', '').replace(']', '');
                const value = input.value || '{{' + variable + '}}';
                subject = subject.replace(new RegExp('{{' + variable + '}}', 'g'), value);
            });
            modalPreviewSubject.textContent = subject;
        }

        if (modalPreviewBody) {
            const originalBody = modalPreviewBody.getAttribute('data-original-body') || '{!! $emailTemplate->body !!}';
            let body = originalBody;
            testDataInputs.forEach(input => {
                const variable = input.name.replace('test_data[', '').replace(']', '');
                const value = input.value || '{{' + variable + '}}';
                body = body.replace(new RegExp('{{' + variable + '}}', 'g'), value);
            });
            modalPreviewBody.innerHTML = body;
        }
        
        modal.addEventListener('hidden.bs.modal', function() {
            document.body.removeChild(modal);
        });
    }


    // Copy to clipboard functionality
    copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                this.showNotification('Copied to clipboard!', 'success');
            }).catch(() => {
                this.fallbackCopyToClipboard(text);
            });
        } else {
            this.fallbackCopyToClipboard(text);
        }
    }

    // Fallback copy method
    fallbackCopyToClipboard(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            this.showNotification('Copied to clipboard!', 'success');
        } catch {
            this.showNotification('Failed to copy', 'error');
        }
        
        document.body.removeChild(textArea);
    }

    // Initialize Email Template Index Functions
    initEmailTemplateIndex() {
        // Initialize template filtering
        this.initTemplateFiltering();
        
        // Initialize delete confirmations
        this.initDeleteConfirmations();
    }

    // Initialize Template Filtering
    initTemplateFiltering() {
        const searchInput = document.getElementById('searchTemplates');
        const typeFilter = document.getElementById('type-filter');
        const categoryFilter = document.getElementById('category-filter');

        if (searchInput) {
            searchInput.addEventListener('keyup', () => this.filterTemplates());
        }

        if (typeFilter) {
            typeFilter.addEventListener('change', () => this.filterTemplates());
        }

        if (categoryFilter) {
            categoryFilter.addEventListener('change', () => this.filterTemplates());
        }
    }

    // Filter Templates
    filterTemplates() {
        const searchTerm = document.getElementById('searchTemplates')?.value.toLowerCase() || '';
        const typeFilter = document.getElementById('type-filter')?.value || '';
        const categoryFilter = document.getElementById('category-filter')?.value || '';
        
        const templateRows = document.querySelectorAll('.template-row');
        
        templateRows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const subject = row.getAttribute('data-subject') || '';
            const type = row.getAttribute('data-type') || '';
            const category = row.getAttribute('data-category') || '';
            
            const matchesSearch = name.includes(searchTerm) || subject.includes(searchTerm);
            const matchesType = !typeFilter || type === typeFilter;
            const matchesCategory = !categoryFilter || category === categoryFilter;
            
            if (matchesSearch && matchesType && matchesCategory) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Initialize Delete Confirmations
    initDeleteConfirmations() {
        const deleteButtons = document.querySelectorAll('.delete-template-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const confirmMessage = button.getAttribute('data-confirm');
                if (!window.confirm || !confirm(confirmMessage)) {
                    e.preventDefault();
                }
            });
        });
    }

    // Initialize Email Template Test Functions
    initEmailTemplateTest() {
        // Auto-update preview when test data changes
        const testDataInputs = document.querySelectorAll('input[name^="test_data"]');
        testDataInputs.forEach(input => {
            input.addEventListener('input', () => this.updateEmailPreview());
        });
        
        // Initialize preview buttons
        const previewTestBtn = document.getElementById('preview-test-btn');
        const updatePreviewBtn = document.getElementById('update-preview-btn');
        
        if (previewTestBtn) {
            previewTestBtn.addEventListener('click', () => this.previewTest());
        }
        
        if (updatePreviewBtn) {
            updatePreviewBtn.addEventListener('click', () => this.updateEmailPreview());
        }
        
        // Initial preview update
        this.updateEmailPreview();
    }

    // Initialize Email Template Show Functions
    initEmailTemplateShow() {
        // Initialize copy to clipboard functionality
        this.initCopyToClipboard();
    }

    // Initialize Copy to Clipboard
    initCopyToClipboard() {
        const copyButtons = document.querySelectorAll('.copy-btn');
        copyButtons.forEach(button => {
            const text = button.getAttribute('data-text');
            if (text) {
                button.addEventListener('click', () => this.copyToClipboard(text));
            }
        });
    }

    // Initialize Invoice Functions
    initInvoiceFunctions() {
        // Initialize invoice filtering
        this.initInvoiceFiltering();
        
        // Initialize invoice print
        this.initInvoicePrint();
        
        // Initialize invoice form toggles
        this.initInvoiceFormToggles();
    }

    // Initialize Invoice Filtering
    initInvoiceFiltering() {
        const searchInput = document.getElementById('searchInvoices');
        const statusFilter = document.getElementById('status-filter');
        const dateFrom = document.getElementById('date-from');
        const dateTo = document.getElementById('date-to');

        if (searchInput) {
            searchInput.addEventListener('keyup', () => this.filterInvoices());
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.filterInvoices());
        }

        if (dateFrom) {
            dateFrom.addEventListener('change', () => this.filterInvoices());
        }

        if (dateTo) {
            dateTo.addEventListener('change', () => this.filterInvoices());
        }
    }

    // Filter Invoices
    filterInvoices() {
        const searchTerm = document.getElementById('searchInvoices')?.value.toLowerCase() || '';
        const statusFilter = document.getElementById('status-filter')?.value || '';
        // const dateFrom = document.getElementById('date-from')?.value || ''; // Unused variable removed
        // const dateTo = document.getElementById('date-to')?.value || ''; // Unused variable removed
        
        const invoiceRows = document.querySelectorAll('.invoice-row');
        
        invoiceRows.forEach(row => {
            const number = row.getAttribute('data-number') || '';
            const user = row.getAttribute('data-user') || '';
            const status = row.getAttribute('data-status') || '';
            
            const matchesSearch = number.includes(searchTerm) || user.includes(searchTerm);
            const matchesStatus = !statusFilter || status === statusFilter;
            
            // Date filtering logic can be added here if needed
            
            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Initialize Invoice Print
    initInvoicePrint() {
        const printBtn = document.getElementById('print-invoice-btn');
        if (printBtn) {
            printBtn.addEventListener('click', () => this.printInvoice());
        }
    }

    // Print Invoice
    printInvoice() {
        // Create a new window for printing
        const printWindow = window.open('', '_blank');
        
        // Get the invoice data from the page
        const invoiceNumber = document.querySelector('[data-invoice-number]')?.getAttribute('data-invoice-number') || 'N/A';
        const customer = document.querySelector('[data-customer-name]')?.getAttribute('data-customer-name') || 'No Customer';
        const amount = document.querySelector('[data-invoice-amount]')?.getAttribute('data-invoice-amount') || '0';
        const currency = document.querySelector('[data-invoice-currency]')?.getAttribute('data-invoice-currency') || 'USD';
        const status = document.querySelector('[data-invoice-status]')?.getAttribute('data-invoice-status') || 'Pending';
        const created = document.querySelector('[data-invoice-created]')?.getAttribute('data-invoice-created') || 'N/A';
        const due = document.querySelector('[data-invoice-due]')?.getAttribute('data-invoice-due') || 'No Due Date';
        
        // Create print content
        const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Invoice #${invoiceNumber}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .header { text-align: center; margin-bottom: 30px; }
                    .invoice-details { margin-bottom: 30px; }
                    .invoice-details table { width: 100%; border-collapse: collapse; }
                    .invoice-details th, .invoice-details td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                    .amount { text-align: right; font-size: 24px; font-weight: bold; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Invoice #${invoiceNumber}</h1>
                    <p>Status: ${status}</p>
                </div>
                
                <div class="invoice-details">
                    <table>
                        <tr><th>Customer:</th><td>${customer}</td></tr>
                        <tr><th>Created:</th><td>${created}</td></tr>
                        <tr><th>Due Date:</th><td>${due}</td></tr>
                    </table>
                </div>
                
                <div class="amount">
                    Total: ${amount} ${currency}
                </div>
            </body>
            </html>
        `;
        
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.print();
    }

    // Initialize invoice form toggles
    initInvoiceFormToggles() {
        // Toggle paid_at field based on status
        const statusSelect = document.getElementById('status');
        const paidAtGroup = document.getElementById('paid_at_group');
        
        if (statusSelect && paidAtGroup) {
            statusSelect.addEventListener('change', () => {
                if (statusSelect.value === 'paid') {
                    paidAtGroup.classList.remove('hidden-field');
                    paidAtGroup.classList.add('visible-field');
                } else {
                    paidAtGroup.classList.remove('visible-field');
                    paidAtGroup.classList.add('hidden-field');
                }
            });
        }

        // Toggle custom fields based on license selection
        const licenseSelect = document.getElementById('license_id');
        const customFields = document.getElementById('custom_invoice_fields');
        
        if (licenseSelect && customFields) {
            licenseSelect.addEventListener('change', () => {
                if (licenseSelect.value === 'custom') {
                    customFields.classList.remove('hidden-field');
                    customFields.classList.add('visible-field');
                    document.getElementById('custom_invoice_type').required = true;
                    document.getElementById('custom_product_name').required = true;
                } else {
                    customFields.classList.remove('visible-field');
                    customFields.classList.add('hidden-field');
                    document.getElementById('custom_invoice_type').required = false;
                    document.getElementById('custom_product_name').required = false;
                }
                this.toggleExpirationDateField();
            });
        }

        // Initialize invoice create specific functions
        this.initInvoiceCreateFunctions();
    }

    // Initialize Invoice Create Functions
    initInvoiceCreateFunctions() {
        const userSelect = document.getElementById('user_id');
        const licenseSelect = document.getElementById('license_id');
        const customInvoiceTypeSelect = document.getElementById('custom_invoice_type');
        // const expirationDateGroup = document.getElementById('expiration_date_group'); // Unused variable removed
        const amountInput = document.getElementById('amount');
        const currencySelect = document.getElementById('currency');
        const dueDateInput = document.getElementById('due_date');
        // const previewCustomer = document.getElementById('preview-customer'); // Unused variable removed
        // const previewAmount = document.getElementById('preview-amount'); // Unused variable removed
        // const previewStatus = document.getElementById('preview-status'); // Unused variable removed
        // const previewDueDate = document.getElementById('preview-due-date'); // Unused variable removed

        // Show/hide expiration date based on custom invoice type
        if (customInvoiceTypeSelect) {
            customInvoiceTypeSelect.addEventListener('change', () => this.toggleExpirationDateField());
        }

        // Update preview functions
        if (userSelect) {
            userSelect.addEventListener('change', () => this.updateInvoicePreview());
        }
        if (amountInput) {
            amountInput.addEventListener('input', () => this.updateInvoicePreview());
        }
        if (currencySelect) {
            currencySelect.addEventListener('change', () => this.updateInvoicePreview());
        }
        if (dueDateInput) {
            dueDateInput.addEventListener('change', () => this.updateInvoicePreview());
        }

        // Load licenses when user is selected
        if (userSelect && licenseSelect) {
            userSelect.addEventListener('change', () => this.loadUserLicenses());
        }

        // Initial setup
        this.togglePaidAtField();
        this.toggleCustomInvoiceFields();
        this.updateInvoicePreview();
    }

    // Toggle Paid At Field
    togglePaidAtField() {
        const statusSelect = document.getElementById('status');
        const paidAtGroup = document.getElementById('paid_at_group');
        
        if (statusSelect && paidAtGroup) {
            if (statusSelect.value === 'paid') {
                paidAtGroup.classList.remove('hidden-field');
                paidAtGroup.classList.add('visible-field');
            } else {
                paidAtGroup.classList.remove('visible-field');
                paidAtGroup.classList.add('hidden-field');
            }
        }
    }

    // Toggle Custom Invoice Fields
    toggleCustomInvoiceFields() {
        const licenseSelect = document.getElementById('license_id');
        const customFields = document.getElementById('custom_invoice_fields');
        
        if (licenseSelect && customFields) {
            if (licenseSelect.value === 'custom') {
                customFields.classList.remove('hidden-field');
                customFields.classList.add('visible-field');
                document.getElementById('custom_invoice_type').required = true;
                document.getElementById('custom_product_name').required = true;
            } else {
                customFields.classList.remove('visible-field');
                customFields.classList.add('hidden-field');
                document.getElementById('custom_invoice_type').required = false;
                document.getElementById('custom_product_name').required = false;
            }
            this.toggleExpirationDateField();
        }
    }

    // Toggle Expiration Date Field
    toggleExpirationDateField() {
        const licenseSelect = document.getElementById('license_id');
        const customInvoiceTypeSelect = document.getElementById('custom_invoice_type');
        const expirationDateGroup = document.getElementById('expiration_date_group');
        
        if (licenseSelect && customInvoiceTypeSelect && expirationDateGroup) {
            const selectedType = customInvoiceTypeSelect.value;
            if (licenseSelect.value === 'custom' && selectedType !== 'one_time') {
                expirationDateGroup.style.display = 'block';
                const expirationInput = document.getElementById('expiration_date');
                if (expirationInput && !expirationInput.value) {
                    const today = new Date();
                    switch(selectedType) {
                        case 'monthly':
                            today.setMonth(today.getMonth() + 1);
                            break;
                        case 'quarterly':
                            today.setMonth(today.getMonth() + 3);
                            break;
                        case 'semi_annual':
                            today.setMonth(today.getMonth() + 6);
                            break;
                        case 'annual':
                            today.setFullYear(today.getFullYear() + 1);
                            break;
                        case 'custom_recurring':
                            today.setMonth(today.getMonth() + 1);
                            break;
                    }
                    expirationInput.value = today.toISOString().split('T')[0];
                }
            } else {
                expirationDateGroup.style.display = 'none';
            }
        }
    }

    // Update Invoice Preview
    updateInvoicePreview() {
        const userSelect = document.getElementById('user_id');
        const amountInput = document.getElementById('amount');
        const currencySelect = document.getElementById('currency');
        const statusSelect = document.getElementById('status');
        const dueDateInput = document.getElementById('due_date');
        const previewCustomer = document.getElementById('preview-customer');
        const previewAmount = document.getElementById('preview-amount');
        const previewStatus = document.getElementById('preview-status');
        const previewDueDate = document.getElementById('preview-due-date');

        if (userSelect && previewCustomer) {
            if (userSelect.value) {
                const selectedOption = userSelect.options[userSelect.selectedIndex];
                previewCustomer.textContent = selectedOption.text.split(' (')[0];
            } else {
                previewCustomer.textContent = 'Customer Name';
            }
        }

        if (amountInput && currencySelect && previewAmount) {
            if (amountInput.value && currencySelect.value) {
                previewAmount.textContent = `${amountInput.value} ${currencySelect.value}`;
            } else {
                previewAmount.textContent = '$0.00 USD';
            }
        }

        if (statusSelect && previewStatus) {
            if (statusSelect.value) {
                const statusText = statusSelect.options[statusSelect.selectedIndex].text;
                const statusClass = statusSelect.value === 'paid' ? 'success' : 
                                  statusSelect.value === 'overdue' ? 'danger' : 'warning';
                previewStatus.textContent = statusText;
                previewStatus.className = `badge bg-${statusClass} mt-2`;
            }
        }

        if (dueDateInput && previewDueDate) {
            if (dueDateInput.value) {
                const date = new Date(dueDateInput.value);
                previewDueDate.textContent = date.toLocaleDateString('en-US', { 
                    year: 'numeric', month: 'short', day: 'numeric' 
                });
            }
        }
    }

    // Load User Licenses
    loadUserLicenses() {
        const userSelect = document.getElementById('user_id');
        const licenseSelect = document.getElementById('license_id');
        
        if (!userSelect || !licenseSelect) return;

        const userId = userSelect.value;

        if (userId) {
            // Base URL for user licenses API
            const baseMeta = document.querySelector('meta[name="base-url"]');
            const baseUrl = baseMeta ? baseMeta.getAttribute('content') : '';
            const apiUserLicensesBase = (baseUrl.replace(/\/$/, '') || '') + '/admin/api/user';

            fetch(`${apiUserLicensesBase}/${userId}/licenses`, { 
                credentials: 'same-origin', 
                headers: { 'Accept': 'application/json' } 
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Server returned ' + response.status + ' ' + response.statusText);
                    }
                    return response.text();
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);

                        licenseSelect.innerHTML = '<option value="">Select License</option>';
                        licenseSelect.innerHTML += '<option value="custom">Custom Invoice (No License)</option>';

                        data.forEach(license => {
                            const option = document.createElement('option');
                            option.value = license.id;
                            let statusText = license.status;
                            switch(license.status.toLowerCase()) {
                                case 'active':
                                    statusText = 'Active';
                                    break;
                                case 'expired':
                                    statusText = 'Expired';
                                    break;
                                case 'suspended':
                                    statusText = 'Suspended';
                                    break;
                                case 'pending':
                                    statusText = 'Pending';
                                    break;
                            }
                            option.textContent = `${license.product.name} - ${license.license_type} (${statusText})`;
                            licenseSelect.appendChild(option);
                        });

                        this.toggleCustomInvoiceFields();
                    } catch {
                        throw new Error('Invalid JSON response from server');
                    }
                })
                .catch(error => {
                    // Error loading licenses
                    this.showNotification('Error loading licenses. Please make sure you are logged in and the server is reachable.', 'error');
                });
        } else {
            licenseSelect.innerHTML = '<option value="">Select License</option>';
            licenseSelect.innerHTML += '<option value="custom">Custom Invoice (No License)</option>';
            this.toggleCustomInvoiceFields();
        }
    }

    // Initialize KB Functions
    initKBFunctions() {
        // Initialize KB article filtering
        this.initKBArticleFiltering();
        
        // Initialize KB category filtering
        this.initKBCategoryFiltering();
        
        // Initialize serial field toggles
        this.initSerialFieldToggles();
    }

    // Initialize KB Article Filtering
    initKBArticleFiltering() {
        const searchInput = document.getElementById('searchArticles');
        const categoryFilter = document.getElementById('category-filter');
        const statusFilter = document.getElementById('status-filter');

        if (searchInput) {
            searchInput.addEventListener('keyup', () => this.filterArticles());
        }

        if (categoryFilter) {
            categoryFilter.addEventListener('change', () => this.filterArticles());
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.filterArticles());
        }
    }

    // Filter Articles
    filterArticles() {
        const searchTerm = document.getElementById('searchArticles')?.value.toLowerCase() || '';
        const categoryFilter = document.getElementById('category-filter')?.value || '';
        const statusFilter = document.getElementById('status-filter')?.value || '';
        
        const articleRows = document.querySelectorAll('.article-row');
        
        articleRows.forEach(row => {
            const title = row.getAttribute('data-title') || '';
            const category = row.getAttribute('data-category') || '';
            const status = row.getAttribute('data-status') || '';
            
            const matchesSearch = title.includes(searchTerm);
            const matchesCategory = !categoryFilter || category === categoryFilter;
            const matchesStatus = !statusFilter || status === statusFilter;
            
            if (matchesSearch && matchesCategory && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Initialize KB Category Filtering
    initKBCategoryFiltering() {
        const searchInput = document.getElementById('searchCategories');
        const protectionFilter = document.getElementById('protection-filter');

        if (searchInput) {
            searchInput.addEventListener('keyup', () => this.filterKBCategories());
        }

        if (protectionFilter) {
            protectionFilter.addEventListener('change', () => this.filterKBCategories());
        }
    }

    // Filter KB Categories
    filterKBCategories() {
        const searchTerm = document.getElementById('searchCategories')?.value.toLowerCase() || '';
        const protectionFilter = document.getElementById('protection-filter')?.value || '';
        
        const categoryRows = document.querySelectorAll('.category-row');
        
        categoryRows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const protection = row.getAttribute('data-protection') || '';
            
            const matchesSearch = name.includes(searchTerm);
            const matchesProtection = !protectionFilter || protection === protectionFilter;
            
            if (matchesSearch && matchesProtection) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Initialize Serial Field Toggles
    initSerialFieldToggles() {
        const requiresSerialCheckboxes = document.querySelectorAll('input[name="requires_serial"]');
        
        requiresSerialCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const serialFields = checkbox.closest('form')?.querySelector('#serial-fields');
                if (serialFields) {
                    if (checkbox.checked) {
                        serialFields.classList.remove('hidden-field');
                        serialFields.classList.add('visible-field');
                    } else {
                        serialFields.classList.remove('visible-field');
                        serialFields.classList.add('hidden-field');
                    }
                }
            });
        });
    }

    // Initialize All Page Functions
    initAllPageFunctions() {
        // Initialize license functions
        this.initLicenseFunctions();
        
        // Initialize product functions
        this.initProductFunctions();
        
        // Initialize ticket functions
        this.initTicketFunctions();
        
        // Initialize user functions
        this.initUserFunctions();
        
        // Initialize category functions
        this.initCategoryFunctions();
        
        // Initialize ticket category functions
        this.initTicketCategoryFunctions();
    }

    // Initialize License Functions
    initLicenseFunctions() {
        // Initialize license filtering
        this.initLicenseFiltering();
        
        // Initialize license actions
        this.initLicenseActions();
        
        // Initialize progress bars
        this.initProgressBars();
    }

    // Initialize License Filtering
    initLicenseFiltering() {
        const searchInput = document.getElementById('searchLicenses');
        const statusFilter = document.getElementById('status-filter');
        const typeFilter = document.getElementById('type-filter');
        const sortFilter = document.getElementById('sort-filter');

        if (searchInput) {
            searchInput.addEventListener('keyup', () => this.filterLicenses());
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.filterLicenses());
        }

        if (typeFilter) {
            typeFilter.addEventListener('change', () => this.filterLicenses());
        }

        if (sortFilter) {
            sortFilter.addEventListener('change', () => this.filterLicenses());
        }
    }

    // Filter Licenses
    filterLicenses() {
        const searchTerm = document.getElementById('searchLicenses')?.value.toLowerCase() || '';
        const statusFilter = document.getElementById('status-filter')?.value || '';
        const typeFilter = document.getElementById('type-filter')?.value || '';
        
        const licenseRows = document.querySelectorAll('.license-row');
        
        licenseRows.forEach(row => {
            const key = row.getAttribute('data-key') || '';
            const customer = row.getAttribute('data-customer') || '';
            const product = row.getAttribute('data-product') || '';
            const status = row.getAttribute('data-status') || '';
            const type = row.getAttribute('data-type') || '';
            
            const matchesSearch = key.includes(searchTerm) || customer.includes(searchTerm) || product.includes(searchTerm);
            const matchesStatus = !statusFilter || status === statusFilter;
            const matchesType = !typeFilter || type === typeFilter;
            
            if (matchesSearch && matchesStatus && matchesType) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Initialize License Actions
    initLicenseActions() {
        // Initialize copy buttons
        this.initCopyToClipboard();
        
        // Initialize regenerate license key
        const regenerateBtn = document.getElementById('regenerate-license-key-btn');
        if (regenerateBtn) {
            regenerateBtn.addEventListener('click', () => this.regenerateLicenseKey());
        }
        
        // Initialize remove domain buttons
        const removeDomainBtns = document.querySelectorAll('.remove-domain-btn');
        removeDomainBtns.forEach(btn => {
            btn.addEventListener('click', () => this.removeDomain(btn.getAttribute('data-domain-id')));
        });
    }

    // Regenerate License Key
    regenerateLicenseKey() {
        if (window.confirm && confirm('Are you sure you want to regenerate this license key? This action cannot be undone.')) {
            // Add regeneration logic here
            this.showNotification('License key regeneration functionality would be implemented here', 'info');
        }
    }

    // Remove Domain
    removeDomain() {
        if (window.confirm && confirm('Are you sure you want to remove this domain?')) {
            // Add domain removal logic here
            this.showNotification('Domain removal functionality would be implemented here', 'info');
        }
    }

    // Initialize Progress Bars
    initProgressBars() {
        const progressBars = document.querySelectorAll('.progress-bar[data-width]');
        progressBars.forEach(bar => {
            const width = bar.getAttribute('data-width');
            bar.style.width = width + '%';
        });
    }

    // Initialize Product Functions
    initProductFunctions() {
        // Initialize product filtering
        this.initProductFiltering();
    }

    // Initialize Product Filtering
    initProductFiltering() {
        // Add product filtering logic here if needed
    }

    // Initialize Ticket Functions
    initTicketFunctions() {
        // Initialize ticket form toggles
        this.initTicketFormToggles();
        
        // Initialize category preview
        this.initCategoryPreview();
        
        // Initialize ticket filtering
        this.initTicketFiltering();
    }

    // Initialize Ticket Form Toggles
    initTicketFormToggles() {
        const createInvoiceCheckbox = document.getElementById('create_invoice');
        // const licenseInfo = document.getElementById('license-info'); // Unused variable removed
        const invoiceSection = document.getElementById('invoice-section');
        const renewalGroup = document.getElementById('invoice-renewal-group');
        const renewalPeriodGroup = document.getElementById('invoice-renewal-period-group');

        if (createInvoiceCheckbox && invoiceSection) {
            createInvoiceCheckbox.addEventListener('change', () => {
                if (createInvoiceCheckbox.checked) {
                    invoiceSection.classList.remove('hidden-field');
                    invoiceSection.classList.add('visible-field');
                } else {
                    invoiceSection.classList.remove('visible-field');
                    invoiceSection.classList.add('hidden-field');
                }
            });
        }

        // Toggle renewal fields based on invoice type
        const invoiceTypeSelect = document.getElementById('invoice_type');
        if (invoiceTypeSelect && renewalGroup && renewalPeriodGroup) {
            invoiceTypeSelect.addEventListener('change', () => {
                if (invoiceTypeSelect.value === 'renewal') {
                    renewalGroup.classList.remove('hidden-field');
                    renewalGroup.classList.add('visible-field');
                    renewalPeriodGroup.classList.remove('hidden-field');
                    renewalPeriodGroup.classList.add('visible-field');
                } else {
                    renewalGroup.classList.remove('visible-field');
                    renewalGroup.classList.add('hidden-field');
                    renewalPeriodGroup.classList.remove('visible-field');
                    renewalPeriodGroup.classList.add('hidden-field');
                }
            });
        }
    }

    // Initialize User Functions
    initUserFunctions() {
        // Initialize user filtering
        this.initUserFiltering();
    }

    // Initialize User Filtering
    initUserFiltering() {
        const searchInput = document.getElementById('searchUsers');
        const roleFilter = document.getElementById('role-filter');
        const statusFilter = document.getElementById('status-filter');

        if (searchInput) {
            searchInput.addEventListener('keyup', () => this.filterUsers());
        }

        if (roleFilter) {
            roleFilter.addEventListener('change', () => this.filterUsers());
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.filterUsers());
        }
    }

    // Filter Users
    filterUsers() {
        const searchTerm = document.getElementById('searchUsers')?.value.toLowerCase() || '';
        const roleFilter = document.getElementById('role-filter')?.value || '';
        const statusFilter = document.getElementById('status-filter')?.value || '';
        
        const userRows = document.querySelectorAll('.user-row');
        
        userRows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const email = row.getAttribute('data-email') || '';
            const role = row.getAttribute('data-role') || '';
            const status = row.getAttribute('data-status') || '';
            
            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesRole = !roleFilter || role === roleFilter;
            const matchesStatus = !statusFilter || status === statusFilter;
            
            if (matchesSearch && matchesRole && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Initialize Category Functions
    initCategoryFunctions() {
        // Initialize category color preview
        this.initCategoryColorPreview();
        
        // Initialize reset filters
        this.initResetFilters();
    }

    // Initialize Category Color Preview
    initCategoryColorPreview() {
        const colorInputs = document.querySelectorAll('input[name="color"]');
        const previews = document.querySelectorAll('.category-preview');
        const badges = document.querySelectorAll('.category-badge');
        const avatars = document.querySelectorAll('.category-color-avatar');

        colorInputs.forEach(input => {
            input.addEventListener('input', () => {
                const color = input.value;
                
                // Update previews
                previews.forEach(preview => {
                    preview.style.backgroundColor = color;
                    preview.setAttribute('data-color', color);
                });
                
                // Update badges
                badges.forEach(badge => {
                    badge.style.backgroundColor = color;
                    badge.setAttribute('data-color', color);
                });
                
                // Update avatars
                avatars.forEach(avatar => {
                    avatar.style.backgroundColor = color;
                    avatar.setAttribute('data-color', color);
                });
            });
        });

        // Initialize existing colors
        avatars.forEach(avatar => {
            const color = avatar.getAttribute('data-color');
            if (color) {
                avatar.style.backgroundColor = color;
            }
        });

        badges.forEach(badge => {
            const color = badge.getAttribute('data-color');
            if (color) {
                badge.style.backgroundColor = color;
            }
        });
    }

    // Initialize Reset Filters
    initResetFilters() {
        const resetBtn = document.getElementById('reset-filters-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => this.resetFilters());
        }
    }

    // Reset Filters
    resetFilters() {
        // Reset search inputs
        const searchInputs = document.querySelectorAll('input[type="text"]');
        searchInputs.forEach(input => {
            if (input.id.includes('search')) {
                input.value = '';
            }
        });

        // Reset select filters
        const selectFilters = document.querySelectorAll('select');
        selectFilters.forEach(select => {
            if (select.id.includes('filter')) {
                select.selectedIndex = 0;
            }
        });

        // Show all rows
        const rows = document.querySelectorAll('[class*="-row"]');
        rows.forEach(row => {
            row.style.display = '';
        });

        this.showNotification('Filters reset successfully', 'success');
    }

    // Initialize Programming Languages Functions
    initProgrammingLanguagesFunctions() {
        // Initialize programming languages filtering
        this.initProgrammingLanguagesFiltering();
        
        // Initialize license file viewing
        this.initLicenseFileViewing();
        
        // Initialize template viewing
        this.initTemplateViewing();
    }

    // Initialize Programming Languages Filtering
    initProgrammingLanguagesFiltering() {
        const searchInput = document.getElementById('searchLanguages');
        const statusFilter = document.getElementById('status-filter');
        const sortFilter = document.getElementById('sort-filter');

        if (searchInput) {
            searchInput.addEventListener('keyup', () => this.filterProgrammingLanguages());
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.filterProgrammingLanguages());
        }

        if (sortFilter) {
            sortFilter.addEventListener('change', () => this.filterProgrammingLanguages());
        }
    }

    // Filter Programming Languages
    filterProgrammingLanguages() {
        const searchTerm = document.getElementById('searchLanguages')?.value.toLowerCase() || '';
        const statusFilter = document.getElementById('status-filter')?.value || '';
        
        const languageRows = document.querySelectorAll('.language-row');
        
        languageRows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const description = row.getAttribute('data-description') || '';
            const status = row.getAttribute('data-status') || '';
            
            const matchesSearch = name.includes(searchTerm) || description.includes(searchTerm);
            const matchesStatus = !statusFilter || status === statusFilter;
            
            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Initialize License File Viewing
    initLicenseFileViewing() {
        const viewButtons = document.querySelectorAll('.view-license-file-btn');
        viewButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const slug = btn.getAttribute('data-slug');
                const type = btn.getAttribute('data-type');
                this.viewLicenseFile(slug, type);
            });
        });
    }

    // View License File
    viewLicenseFile(slug, type) {
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('licenseFileModal'));
        modal.show();

        // Load file content
        this.loadLicenseFileContent(slug, type);
    }

    // Load License File Content
    loadLicenseFileContent(slug, type) {
        const contentElement = document.getElementById('licenseFileContent');
        const copyBtn = document.querySelector('.copy-btn');
        
        // Show loading
        contentElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        
        // Make AJAX request
        fetch(`/admin/programming-languages/license-file/${slug}?type=${type}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contentElement.innerHTML = `<pre>${data.content}</pre>`;
                if (copyBtn) {
                    copyBtn.setAttribute('data-text', data.content);
                }
            } else {
                contentElement.innerHTML = `<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> ${data.message || 'Error loading file'}</div>`;
            }
        })
        .catch(error => {
            // Error loading file
            contentElement.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> Error loading file</div>';
        });
    }

    // Initialize Template Viewing
    initTemplateViewing() {
        const viewTemplateBtn = document.querySelector('.view-template-btn');
        if (viewTemplateBtn) {
            viewTemplateBtn.addEventListener('click', () => {
                const languageId = viewTemplateBtn.getAttribute('data-language');
                this.viewTemplate(languageId);
            });
        }

        const copyTemplateBtn = document.querySelector('.copy-template-btn');
        if (copyTemplateBtn) {
            copyTemplateBtn.addEventListener('click', () => {
                this.copyTemplateContent();
            });
        }
    }

    // View Template
    viewTemplate(languageId) {
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('templateModal'));
        modal.show();

        // Load template content
        this.loadTemplateContent(languageId);
    }

    // Load Template Content
    loadTemplateContent(languageId) {
        const contentElement = document.querySelector('.template-content');
        
        // Show loading
        contentElement.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                <p class="mt-2 text-muted">Loading template...</p>
            </div>
        `;
        
        // Make AJAX request
        fetch(`/admin/programming-languages/${languageId}/template`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contentElement.innerHTML = `<pre class="bg-light p-3 rounded"><code>${data.content}</code></pre>`;
                this.templateContent = data.content;
            } else {
                contentElement.innerHTML = `<div class="text-danger text-center py-4"><i class="fas fa-exclamation-triangle fa-2x"></i><p class="mt-2">${data.message || 'Error loading template'}</p></div>`;
            }
        })
        .catch(error => {
            // Error loading file
            contentElement.innerHTML = '<div class="text-danger text-center py-4"><i class="fas fa-exclamation-triangle fa-2x"></i><p class="mt-2">Error loading template</p></div>';
        });
    }

    // Copy Template Content
    copyTemplateContent() {
        if (this.templateContent) {
            navigator.clipboard.writeText(this.templateContent).then(() => {
                this.showNotification('Template content copied to clipboard', 'success');
            }).catch(err => {
                // Failed to copy
                this.showNotification('Failed to copy content', 'error');
            });
        } else {
            this.showNotification('No template content to copy', 'warning');
        }
    }

    // Initialize Profile Functions
    initProfileFunctions() {
        // Initialize logout functionality
        this.initLogoutFunction();
    }

    // Initialize Logout Function
    initLogoutFunction() {
        const logoutBtn = document.querySelector('.logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const logoutForm = document.getElementById('logout-form');
                if (logoutForm) {
                    logoutForm.submit();
                }
            });
        }
    }

    // Initialize Settings Functions
    initSettingsFunctions() {
        // Log in development only
        if (typeof window !== 'undefined' && window.console && window.console.log) {
            window.console.log('initSettingsFunctions called');
        }
        
        // Check if already initialized
        if (document.body.dataset.settingsFunctionsInitialized === 'true') {
            // Log in development only
            if (typeof window !== 'undefined' && window.console && window.console.log) {
                window.console.log('Settings functions already initialized');
            }
            return;
        }
        
        // Initialize tabs functionality
        this.initSettingsTabs();
        
        // Initialize API test functionality
        this.initApiTest();
        
        // Initialize color picker synchronization
        this.initColorPickerSync();
        
        // Initialize file upload previews
        this.initFileUploadPreviews();
        
        // Initialize logo preview
        this.initLogoPreview();
        
        // Initialize preloader preview
        this.initPreloaderPreview();
        
        // Mark as initialized
        document.body.dataset.settingsFunctionsInitialized = 'true';
    }

    // Initialize Settings Tabs
    initSettingsTabs() {
        const container = document.querySelector('.admin-settings-page');
        if (!container) return;

        // Avoid double initialization
        if (container.dataset.settingsTabsInit === '1') return;

        const tabButtons = container.querySelectorAll('.admin-tab-btn');
        const tabPanels = container.querySelectorAll('.admin-tab-panel');

        // Initialize: show first panel and hide others
        tabPanels.forEach((panel, index) => {
            if (index === 0) {
                panel.classList.add('active');
                panel.classList.remove('admin-tab-panel-hidden');
                panel.style.display = '';
            } else {
                panel.classList.remove('active');
                panel.classList.add('admin-tab-panel-hidden');
                panel.style.display = 'none';
            }
        });

        tabButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const targetTab = button.getAttribute('data-tab');

                // Remove active class from all buttons and hide all panels
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanels.forEach(panel => {
                    panel.classList.remove('active');
                    panel.classList.add('admin-tab-panel-hidden');
                    panel.style.display = 'none';
                });

                // Activate clicked button
                button.classList.add('active');

                // Resolve target panel in multiple ways
                let targetPanel = container.querySelector(`#${window.CSS.escape(targetTab)}`);
                if (!targetPanel) targetPanel = container.querySelector(`#${window.CSS.escape(targetTab + '-tab')}`);
                if (!targetPanel) targetPanel = container.querySelector(`.admin-tab-panel[data-panel="${targetTab}"]`);

                if (targetPanel) {
                    targetPanel.classList.add('active');
                    targetPanel.classList.remove('admin-tab-panel-hidden');
                    targetPanel.style.display = '';
                }
            });
        });

        container.dataset.settingsTabsInit = '1';
    }

    // Initialize API Test
    initApiTest() {
        console.log('initApiTest called');
        const testBtn = document.getElementById('test-api-btn');
        if (testBtn) {
            // Check if event listener already added
            if (testBtn.dataset.apiTestInitialized === 'true') {
                console.log('API Test button already initialized');
                return;
            }
            
            console.log('API Test button found, adding event listener');
            // const self = this; // Unused variable removed
            testBtn.addEventListener('click', function() {
                console.log('API Test button clicked');
                self.testEnvatoApi();
            });
            
            // Mark as initialized
            testBtn.dataset.apiTestInitialized = 'true';
        } else {
            console.log('API Test button not found');
        }
    }

    // Test Envato API
    async testEnvatoApi() {
        console.log('testEnvatoApi function called');
        const testBtn = document.getElementById('test-api-btn');
        const resultDiv = document.getElementById('api-test-result');
        
        if (!testBtn || !resultDiv) {
            console.log('testBtn or resultDiv not found');
            console.log('testBtn:', testBtn);
            console.log('resultDiv:', resultDiv);
            return;
        }

        const originalText = testBtn.innerHTML;
        testBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Testing...';
        testBtn.disabled = true;

        try {
            const tokenInput = document.getElementById('envato_personal_token');
            const token = tokenInput ? tokenInput.value : '';
            
            if (!token) {
                resultDiv.innerHTML = `
                    <div class="admin-alert admin-alert-warning">
                        <div class="admin-alert-content">
                            <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
                            <div class="admin-alert-text">
                                <h4>Token Required</h4>
                                <p>Please enter your Envato Personal Token first.</p>
                            </div>
                        </div>
                    </div>
                `;
                return;
            }
            
            const response = await fetch(window.location.origin + '/lic/public/admin/settings/test-api', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    token: token
                })
            });
            
            const data = await response.json();

            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="admin-alert admin-alert-success">
                        <div class="admin-alert-content">
                            <i class="fas fa-check-circle admin-alert-icon"></i>
                            <div class="admin-alert-text">
                                <h4>API Test Successful</h4>
                                <p>${data.message}</p>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="admin-alert admin-alert-error">
                        <div class="admin-alert-content">
                            <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
                            <div class="admin-alert-text">
                                <h4>API Test Failed</h4>
                                <p>${data.message}</p>
                            </div>
                        </div>
                    </div>
                `;
            }
        } catch {
            resultDiv.innerHTML = `
                <div class="admin-alert admin-alert-error">
                    <div class="admin-alert-content">
                        <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
                        <div class="admin-alert-text">
                            <h4>API Test Error</h4>
                            <p>An error occurred while testing the API connection.</p>
                        </div>
                    </div>
                </div>
            `;
        } finally {
            testBtn.innerHTML = originalText;
            testBtn.disabled = false;
        }
    }

    // Initialize Color Picker Synchronization
    initColorPickerSync() {
        // Sync color inputs with text inputs
        const colorInputs = document.querySelectorAll('input[type="color"]');
        colorInputs.forEach(colorInput => {
            const textInput = colorInput.parentElement.querySelector('input[type="text"]');
            if (textInput) {
                colorInput.addEventListener('input', () => {
                    textInput.value = colorInput.value;
                });
                
                textInput.addEventListener('input', () => {
                    if (textInput.value.match(/^#[0-9A-F]{6}$/i)) {
                        colorInput.value = textInput.value;
                    }
                });
            }
        });
    }

    // Initialize File Upload Previews
    initFileUploadPreviews() {
        const fileInputs = document.querySelectorAll('input[type="file"][accept="image/*"]');
        fileInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new window.FileReader();
                    reader.onload = (e) => {
                        const preview = input.parentElement.querySelector('.admin-image-preview');
                        if (preview) {
                            preview.src = e.target.result;
                        } else {
                            // Create preview if it doesn't exist
                            const previewDiv = document.createElement('div');
                            previewDiv.className = 'mt-3';
                            previewDiv.innerHTML = `
                                <img src="${e.target.result}" alt="Preview" class="admin-image-preview">
                                <p class="text-muted mt-1">Preview</p>
                            `;
                            input.parentElement.appendChild(previewDiv);
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    }

    // Initialize Logo Preview
    initLogoPreview() {
        const logoPreview = document.getElementById('logo-preview');
        if (!logoPreview) return;

        // Initialize with data attributes
        const initLogoPreview = () => {
            const logoText = document.querySelector('input[name="logo_text"]')?.value || '';
            const showText = document.querySelector('input[name="logo_show_text"]')?.checked || false;
            const textColor = document.querySelector('input[name="logo_text_color"]')?.value || logoPreview.dataset.logoTextColor || '#1f2937';
            const fontSize = document.querySelector('input[name="logo_text_font_size"]')?.value || logoPreview.dataset.logoTextFontSize || '24px';
            const width = document.querySelector('input[name="logo_width"]')?.value || logoPreview.dataset.logoWidth || '150';
            const height = document.querySelector('input[name="logo_height"]')?.value || logoPreview.dataset.logoHeight || '50';

            logoPreview.style.setProperty('--logo-width', width + 'px');
            logoPreview.style.setProperty('--logo-height', height + 'px');
            logoPreview.style.setProperty('--logo-text-color', textColor);
            logoPreview.style.setProperty('--logo-text-font-size', fontSize);

            const textElement = logoPreview.querySelector('.admin-logo-preview-text');
            if (textElement) {
                textElement.textContent = logoText;
                textElement.style.display = showText ? 'block' : 'none';
            }
        };

        const updateLogoPreview = () => {
            const logoText = document.querySelector('input[name="logo_text"]')?.value || '';
            const showText = document.querySelector('input[name="logo_show_text"]')?.checked || false;
            const textColor = document.querySelector('input[name="logo_text_color"]')?.value || '#1f2937';
            const fontSize = document.querySelector('input[name="logo_text_font_size"]')?.value || '24px';
            const width = document.querySelector('input[name="logo_width"]')?.value || '150';
            const height = document.querySelector('input[name="logo_height"]')?.value || '50';

            logoPreview.style.setProperty('--logo-width', width + 'px');
            logoPreview.style.setProperty('--logo-height', height + 'px');
            logoPreview.style.setProperty('--logo-text-color', textColor);
            logoPreview.style.setProperty('--logo-text-font-size', fontSize);

            const textElement = logoPreview.querySelector('.admin-logo-preview-text');
            if (textElement) {
                textElement.textContent = logoText;
                textElement.style.display = showText ? 'block' : 'none';
            }
        };

        // Initialize on page load
        initLogoPreview();

        // Update preview when inputs change
        const inputs = ['logo_text', 'logo_show_text', 'logo_text_color', 'logo_text_font_size', 'logo_width', 'logo_height'];
        inputs.forEach(name => {
            const input = document.querySelector(`input[name="${name}"]`);
            if (input) {
                input.addEventListener('input', updateLogoPreview);
                input.addEventListener('change', updateLogoPreview);
            }
        });
    }

    // Initialize Preloader Preview
    initPreloaderPreview() {
        const previewBtn = document.getElementById('preview-preloader');
        if (!previewBtn) return;

        previewBtn.addEventListener('click', () => {
            // Create a modal or overlay to show preloader preview
            const modal = document.createElement('div');
            modal.className = 'admin-preloader-modal';
            modal.innerHTML = `
                <div class="admin-preloader-overlay">
                    <div class="admin-preloader-content">
                        <div class="admin-preloader-spinner"></div>
                        <p>Loading...</p>
                        <button class="admin-btn admin-btn-secondary mt-3" onclick="this.closest('.admin-preloader-modal').remove()">
                            Close Preview
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Remove modal after 3 seconds
            setTimeout(() => {
                if (modal.parentElement) {
                    modal.remove();
                }
            }, 3000);
        });
    }

    // Initialize Ticket Filtering
    initTicketFiltering() {
        const searchInput = document.getElementById('searchTickets');
        const categoryFilter = document.getElementById('category-filter');
        const statusFilter = document.getElementById('status-filter');
        const priorityFilter = document.getElementById('priority-filter');

        if (searchInput) {
            searchInput.addEventListener('keyup', () => this.filterTickets());
        }

        if (categoryFilter) {
            categoryFilter.addEventListener('change', () => this.filterTickets());
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.filterTickets());
        }

        if (priorityFilter) {
            priorityFilter.addEventListener('change', () => this.filterTickets());
        }
    }

    // Filter Tickets
    filterTickets() {
        const searchTerm = document.getElementById('searchTickets')?.value.toLowerCase() || '';
        const categoryFilter = document.getElementById('category-filter')?.value || '';
        const statusFilter = document.getElementById('status-filter')?.value || '';
        const priorityFilter = document.getElementById('priority-filter')?.value || '';
        
        const ticketRows = document.querySelectorAll('.ticket-row');
        
        ticketRows.forEach(row => {
            const subject = row.getAttribute('data-subject') || '';
            const category = row.getAttribute('data-category') || '';
            const status = row.getAttribute('data-status') || '';
            const priority = row.getAttribute('data-priority') || '';
            
            const matchesSearch = subject.includes(searchTerm);
            const matchesCategory = !categoryFilter || category === categoryFilter;
            const matchesStatus = !statusFilter || status === statusFilter;
            const matchesPriority = !priorityFilter || priority === priorityFilter;
            
            if (matchesSearch && matchesCategory && matchesStatus && matchesPriority) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Initialize Ticket Category Functions
    initTicketCategoryFunctions() {
        // Initialize ticket category filtering
        this.initTicketCategoryFiltering();
        
        // Initialize delete confirmations
        this.initTicketCategoryDeleteConfirmations();
    }

    // Initialize Ticket Category Filtering
    initTicketCategoryFiltering() {
        const searchInput = document.getElementById('searchCategories');

        if (searchInput) {
            searchInput.addEventListener('keyup', () => this.filterTicketCategories());
        }
    }

    // Filter Ticket Categories
    filterTicketCategories() {
        const searchTerm = document.getElementById('searchCategories')?.value.toLowerCase() || '';
        
        const categoryRows = document.querySelectorAll('.category-row');
        
        categoryRows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const status = row.getAttribute('data-status') || '';
            
            const matchesSearch = name.includes(searchTerm);
            const matchesStatus = status === 'active' || searchTerm === '';
            
            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Initialize Ticket Category Delete Confirmations
    initTicketCategoryDeleteConfirmations() {
        const deleteForms = document.querySelectorAll('.delete-category-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', (e) => {
                const confirmMessage = form.getAttribute('data-confirm');
                if (!window.confirm || !confirm(confirmMessage)) {
                    e.preventDefault();
                }
            });
        });
    }

}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Add a small delay to ensure all scripts are loaded
    setTimeout(() => {
        window.adminDashboard = new AdminDashboard();
        // Initialize programming languages edit functions
        initProgrammingLanguagesEditFunctions();
        
        // Direct initialization of API test button as backup
        initApiTestButtonDirectly();
    }, 100);
});

// Direct initialization of API test button as backup
function initApiTestButtonDirectly() {
    const testBtn = document.getElementById('test-api-btn');
    if (testBtn && !testBtn.dataset.apiTestInitialized) {
        // const self = this; // Unused variable removed
        testBtn.addEventListener('click', function() {
            if (window.adminDashboard && window.adminDashboard.testEnvatoApi) {
                window.adminDashboard.testEnvatoApi();
            } else {
                console.error('AdminDashboard not available');
            }
        });
        testBtn.dataset.apiTestInitialized = 'true';
    }
}

// Additional initialization after page load
window.addEventListener('load', function() {
    setTimeout(() => {
        initApiTestButtonDirectly();
    }, 500);
});

// Initialize when tab changes (for settings page)
document.addEventListener('click', function(e) {
    if (e.target && e.target.getAttribute('data-action') === 'show-tab') {
        setTimeout(() => {
            initApiTestButtonDirectly();
        }, window.ADMIN_CONSTANTS.DEBOUNCE_DELAY);
    }
});

// Force initialization every 2 seconds as backup
setInterval(() => {
    const testBtn = document.getElementById('test-api-btn');
    if (testBtn && !testBtn.dataset.apiTestInitialized) {
        initApiTestButtonDirectly();
    }
}, 2000);

// Debug: Log all clicks on the page
document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'test-api-btn') {
        console.log('Click detected on test-api-btn');
    }
});

// Programming Languages Edit Functions

function initProgrammingLanguagesEditFunctions() {
    initProgrammingLanguagesEditTabs();
    initProgrammingLanguagesEditIconPreview();
    initProgrammingLanguagesEditFormValidation();
    initProgrammingLanguagesEditButtons();
}

function initProgrammingLanguagesEditTabs() {
    const tabButtons = document.querySelectorAll('.admin-programming-languages-edit .admin-tab-btn');
    const tabPanels = document.querySelectorAll('.admin-programming-languages-edit .admin-tab-panel');

    tabButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            
            const targetTab = button.getAttribute('data-tab');
            
            // Remove active class from all buttons
            tabButtons.forEach(btn => {
                btn.classList.remove('admin-tab-btn-active');
                btn.setAttribute('aria-selected', 'false');
                btn.setAttribute('tabindex', '-1');
            });
            
            // Add active class to clicked button
            button.classList.add('admin-tab-btn-active');
            button.setAttribute('aria-selected', 'true');
            button.setAttribute('tabindex', '0');
            
            // Hide all panels
            tabPanels.forEach(panel => {
                panel.classList.add('admin-tab-panel-hidden');
                panel.setAttribute('aria-hidden', 'true');
            });
            
            // Show target tab panel
            const targetPanel = document.getElementById(targetTab);
            if (targetPanel) {
                targetPanel.classList.remove('admin-tab-panel-hidden');
                targetPanel.setAttribute('aria-hidden', 'false');
            }
        });
    });
}

function initProgrammingLanguagesEditIconPreview() {
    const iconInput = document.getElementById('icon');
    const iconPreview = document.getElementById('icon-preview');
    
    if (iconInput && iconPreview) {
        iconInput.addEventListener('input', function() {
            const iconClass = this.value.trim();
            if (iconClass) {
                iconPreview.className = iconClass;
            } else {
                iconPreview.className = 'fas fa-code';
            }
        });
    }
}


function initProgrammingLanguagesEditFormValidation() {
    const forms = document.querySelectorAll('.admin-programming-languages-edit .needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

function initProgrammingLanguagesEditButtons() {
    // Load saved template on page load
    loadSavedTemplate();
    
    // Load Template button
    const loadTemplateButtons = document.querySelectorAll('[data-action="load-template"]');
    loadTemplateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const templateName = this.getAttribute('data-template');
            loadTemplate(templateName);
        });
    });

    // Save Template button
    const saveTemplateButtons = document.querySelectorAll('[data-action="save-template"]');
    saveTemplateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const templateName = this.getAttribute('data-template');
            saveTemplate(templateName);
        });
    });

    // Preview Template button
    const previewTemplateButtons = document.querySelectorAll('[data-action="preview-template"]');
    previewTemplateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const templateName = this.getAttribute('data-template');
            previewTemplate(templateName);
        });
    });

    // Validate Templates button
    const validateTemplateButtons = document.querySelectorAll('[data-action="validate-templates"]');
    validateTemplateButtons.forEach(button => {
        button.addEventListener('click', function() {
            validateTemplates();
        });
    });

    // Toggle Code View button
    const toggleCodeViewButtons = document.querySelectorAll('[data-action="toggle-code-view"]');
    toggleCodeViewButtons.forEach(button => {
        button.addEventListener('click', function() {
            toggleCodeView();
        });
    });

    // Refresh Templates button
    const refreshTemplateButtons = document.querySelectorAll('[data-action="refresh-templates"]');
    refreshTemplateButtons.forEach(button => {
        button.addEventListener('click', function() {
            refreshTemplates();
        });
    });

    // View Template button
    const viewTemplateButtons = document.querySelectorAll('[data-action="view-template"]');
    viewTemplateButtons.forEach(button => {
        button.addEventListener('click', function() {
            viewTemplate();
        });
    });

    // Create Template button
    const createTemplateButtons = document.querySelectorAll('[data-action="create-template"]');
    createTemplateButtons.forEach(button => {
        button.addEventListener('click', function() {
            createTemplate();
        });
    });

}

function loadTemplate(templateName) {
    showNotification('Loading template...', 'info');
    
    // Find the template textarea
    const templateTextarea = document.querySelector('textarea[name="license_template"]');
    
    
    
    if (!templateTextarea) {
        // Try alternative selectors
        const altTextarea = document.querySelector('#license_template') || 
                           document.querySelector('textarea[id="license_template"]') ||
                           document.querySelector('textarea.admin-code-editor');
        
        
        
        
        if (altTextarea) {
            // Use the alternative textarea
            loadTemplateContent(altTextarea);
            return;
        }
        
        showNotification('Template editor not found!', 'error');
        return;
    }
    
    
    loadTemplateContent(templateTextarea);
}

function loadTemplateContent(templateTextarea) {
    
    
    
    
    // Get the current programming language ID from the URL or data attribute
    const currentUrl = window.location.pathname;
    
    
    // Try different methods to get the language ID
    let languageId = null;
    
    // Method 1: Extract from URL path
    const urlParts = currentUrl.split('/');
    
    
    // Look for the ID in the URL (should be after 'programming-languages')
    const programmingLanguagesIndex = urlParts.indexOf('programming-languages');
    if (programmingLanguagesIndex !== -1 && urlParts[programmingLanguagesIndex + 1]) {
        languageId = urlParts[programmingLanguagesIndex + 1];
    }
    
    // Method 2: Try to get from data attribute on the page
    if (!languageId) {
        const dataElement = document.querySelector('[data-language-id]');
        if (dataElement) {
            languageId = dataElement.getAttribute('data-language-id');
        }
    }
    
    // Method 3: Try to get from form action or hidden input
    if (!languageId) {
        const form = document.querySelector('form');
        if (form && form.action) {
            const actionUrl = form.action;
            const actionParts = actionUrl.split('/');
            const actionProgrammingLanguagesIndex = actionParts.indexOf('programming-languages');
            if (actionProgrammingLanguagesIndex !== -1 && actionParts[actionProgrammingLanguagesIndex + 1]) {
                languageId = actionParts[actionProgrammingLanguagesIndex + 1];
            }
        }
    }
    
    
    
    if (!languageId || isNaN(languageId)) {
        showNotification('Unable to determine programming language ID!', 'error');
        // Could not determine language ID from URL
        return;
    }
    
    // Make AJAX request to load template from server
    const baseUrl = window.location.origin + window.location.pathname.split('/admin')[0];
    const templateUrl = `${baseUrl}/admin/programming-languages/${languageId}/template-content`;
    
    
    fetch(templateUrl, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        
        if (data.success) {
            templateTextarea.value = data.content;
            
            // Immediately fix text color and visibility
            templateTextarea.style.color = '#1a1a1a';
            templateTextarea.style.backgroundColor = '#ffffff';
            templateTextarea.style.border = '1px solid #d1d5db';
            templateTextarea.style.fontFamily = 'Monaco, Consolas, "Courier New", monospace';
            templateTextarea.style.fontSize = '14px';
            templateTextarea.style.lineHeight = '1.5';
            
            // Force a re-render by temporarily changing and restoring the value
            const originalValue = templateTextarea.value;
            templateTextarea.value = '';
            setTimeout(() => {
                templateTextarea.value = originalValue;
                
                
                // Force focus and blur to trigger re-render
                templateTextarea.focus();
                templateTextarea.blur();
                
                // Force visibility and remove any hiding styles
                templateTextarea.style.display = 'block';
                templateTextarea.style.visibility = 'visible';
                templateTextarea.style.opacity = '1';
                templateTextarea.style.height = 'auto';
                templateTextarea.style.minHeight = '200px';
                
                // Fix text color and background for visibility
                templateTextarea.style.color = '#1a1a1a';
                templateTextarea.style.backgroundColor = '#ffffff';
                templateTextarea.style.border = '1px solid #d1d5db';
                templateTextarea.style.fontFamily = 'Monaco, Consolas, "Courier New", monospace';
                templateTextarea.style.fontSize = '14px';
                templateTextarea.style.lineHeight = '1.5';
                
                // Try to trigger any code editor refresh
                if (window.CodeMirror && templateTextarea.nextSibling && templateTextarea.nextSibling.CodeMirror) {
                    templateTextarea.nextSibling.CodeMirror.refresh();
                }
                
                // Force scroll to top to show content
                templateTextarea.scrollTop = 0;
            }, 10);
            
            // Check if this is a default template (read-only) or custom template (editable)
            const isDefaultTemplate = data.file_path && (data.file_path.includes('default') || 
                                   data.file_path.includes('templates/licenses'));
            
            if (isDefaultTemplate) {
                // Default template - make it read-only
                templateTextarea.readOnly = true;
                templateTextarea.classList.add('readonly-template');
                
                // Apply read-only styling
                templateTextarea.style.color = '#374151';
                templateTextarea.style.backgroundColor = '#f8fafc';
                templateTextarea.style.border = '1px solid #e2e8f0';
                
                showNotification('Default template loaded (Read-only mode)', 'info');
                
                // Add visual indicator
                addReadOnlyIndicator(templateTextarea);
            } else {
                // Custom template - make it editable
                templateTextarea.readOnly = false;
                templateTextarea.classList.remove('readonly-template');
                
                // Apply editable styling
                templateTextarea.style.color = '#1a1a1a';
                templateTextarea.style.backgroundColor = '#ffffff';
                templateTextarea.style.border = '1px solid #d1d5db';
                
                showNotification(`Custom template loaded successfully! (${data.file_size} bytes)`, 'success');
                
                // Remove visual indicator if exists
                removeReadOnlyIndicator(templateTextarea);
            }
            
            // Trigger change event for any listeners
            templateTextarea.dispatchEvent(new window.Event('change'));
            templateTextarea.dispatchEvent(new window.Event('input'));
            
            // Show template info if available
            if (data.last_modified) {
                
                
                
            }
        } else {
            showNotification('Template file not found!', 'warning');
            
            // Load default template as fallback
            loadDefaultTemplate(templateTextarea);
        }
    })
    .catch(error => {
        // Error loading template
        showNotification('Error loading template: ' + error.message, 'error');
        
        // Load default template as fallback
        loadDefaultTemplate(templateTextarea);
    });
}

function loadDefaultTemplate(templateTextarea) {
    // Default template as fallback
    const defaultTemplate = `<?php
// Default License Template
class License {
    public function validate($licenseKey) {
        // License validation logic
        return true;
    }
    
    public function generate($userId) {
        // License generation logic
        return md5($userId . time());
    }
}`;
    
    templateTextarea.value = defaultTemplate;
    templateTextarea.readOnly = true;
    templateTextarea.classList.add('readonly-template');
    templateTextarea.dispatchEvent(new window.Event('change'));
    
    // Add visual indicator
    addReadOnlyIndicator(templateTextarea);
    
    showNotification('Default template loaded as fallback (Read-only mode)', 'info');
}

function addReadOnlyIndicator(templateTextarea) {
    // Remove existing indicator if any
    removeReadOnlyIndicator(templateTextarea);
    
    // Create read-only indicator
    const indicator = document.createElement('div');
    indicator.className = 'readonly-indicator';
    indicator.innerHTML = `
        <div class="readonly-badge">
            <i class="fas fa-lock me-2"></i>
            <span>Read-only Template</span>
        </div>
    `;
    
    // Insert after the textarea
    templateTextarea.parentNode.insertBefore(indicator, templateTextarea.nextSibling);
    
    // Add CSS for the indicator
    if (!document.querySelector('#readonly-indicator-styles')) {
        const style = document.createElement('style');
        style.id = 'readonly-indicator-styles';
        style.textContent = `
            .readonly-indicator {
                position: relative;
                margin-top: 10px;
            }
            
            .readonly-badge {
                display: inline-flex;
                align-items: center;
                background: linear-gradient(135deg, #f59e0b, #f97316);
                color: white;
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 14px;
                font-weight: 600;
                box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
                animation: pulse 2s infinite;
            }
            
            .readonly-badge i {
                font-size: 12px;
            }
            
            .readonly-template {
                background-color: #f8fafc !important;
                border-color: #e2e8f0 !important;
                cursor: not-allowed !important;
                opacity: 0.8;
            }
            
            .readonly-template:focus {
                border-color: #e2e8f0 !important;
                box-shadow: none !important;
            }
            
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }
        `;
        document.head.appendChild(style);
    }
}

function removeReadOnlyIndicator(templateTextarea) {
    const existingIndicator = templateTextarea.parentNode.querySelector('.readonly-indicator');
    if (existingIndicator) {
        existingIndicator.remove();
    }
}

function saveTemplate(templateName) {
    showNotification('Saving template: ' + templateName, 'info');
    
    const templateTextarea = document.querySelector('textarea[name="license_template"]');
    if (templateTextarea && templateTextarea.value.trim()) {
        // Save to localStorage with language ID
        const templateData = {
            name: templateName,
            content: templateTextarea.value,
            timestamp: new Date().toISOString(),
            version: '1.0',
            languageId: getCurrentLanguageId()
        };
        
        try {
            localStorage.setItem('template_' + templateName + '_' + getCurrentLanguageId(), JSON.stringify(templateData));
            showNotification('Template saved successfully!', 'success');
            
            // Update any template list if exists
            updateTemplateList();
        } catch {
            showNotification('Failed to save template: ' + error.message, 'error');
        }
    } else {
        showNotification('No template content to save!', 'warning');
    }
}

function getCurrentLanguageId() {
    const currentUrl = window.location.pathname;
    const urlParts = currentUrl.split('/');
    const programmingLanguagesIndex = urlParts.indexOf('programming-languages');
    if (programmingLanguagesIndex !== -1 && urlParts[programmingLanguagesIndex + 1]) {
        return urlParts[programmingLanguagesIndex + 1];
    }
    
    const dataElement = document.querySelector('[data-language-id]');
    if (dataElement) {
        return dataElement.getAttribute('data-language-id');
    }
    
    return 'unknown';
}

function loadSavedTemplate() {
    const languageId = getCurrentLanguageId();
    const templateKey = 'template_custom_' + languageId;
    
    try {
        const savedTemplate = localStorage.getItem(templateKey);
        if (savedTemplate) {
            const templateData = JSON.parse(savedTemplate);
            const templateTextarea = document.querySelector('textarea[name="license_template"]');
            
            if (templateTextarea && templateData.content) {
                templateTextarea.value = templateData.content;
                templateTextarea.dispatchEvent(new window.Event('change'));
                templateTextarea.dispatchEvent(new window.Event('input'));
                
                
                showNotification('Saved template loaded successfully!', 'success');
            }
        }
    } catch {
        
    }
}

function previewTemplate(templateName) {
    showNotification('Previewing template: ' + templateName, 'info');
    
    const templateTextarea = document.querySelector('textarea[name="license_template"]');
    if (templateTextarea && templateTextarea.value.trim()) {
        // Create preview modal
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-eye me-2"></i>
                            Template Preview: ${templateName}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Template Content:</label>
                            <pre class="bg-light p-3 rounded code-preview"><code>${templateTextarea.value}</code></pre>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Template Info:</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Lines: ${templateTextarea.value.split('\n').length}</small>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Characters: ${templateTextarea.value.length}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="copyTemplateToClipboard()">
                            <i class="fas fa-copy me-2"></i>Copy Template
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Show modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        // Clean up when modal is hidden
        modal.addEventListener('hidden.bs.modal', function() {
            document.body.removeChild(modal);
        });
        
        showNotification('Template preview opened!', 'success');
    } else {
        showNotification('No template content to preview!', 'warning');
    }
}

function validateTemplates() {
    showNotification('Validating templates...', 'info');
    
    const templateTextarea = document.querySelector('textarea[name="license_template"]');
    if (templateTextarea && templateTextarea.value.trim()) {
        const template = templateTextarea.value;
        const validationResults = [];
        
        // Basic PHP syntax validation
        if (template.includes('<?php') || template.includes('<?=')) {
            validationResults.push({ type: 'success', message: 'PHP syntax detected' });
        } else {
            validationResults.push({ type: 'warning', message: 'No PHP tags found' });
        }
        
        // Check for common license template patterns
        const patterns = [
            { pattern: /class\s+\w+/, message: 'Class definition found' },
            { pattern: /function\s+\w+/, message: 'Function definition found' },
            { pattern: /validate|check|verify/i, message: 'Validation function detected' },
            { pattern: /generate|create/i, message: 'Generation function detected' },
            { pattern: /license|key/i, message: 'License-related content found' },
            { pattern: /encrypt|decrypt/i, message: 'Encryption functions found' }
        ];
        
        patterns.forEach(({ pattern, message }) => {
            if (pattern.test(template)) {
                validationResults.push({ type: 'success', message });
            }
        });
        
        // Check for potential issues
        if (template.length < 50) {
            validationResults.push({ type: 'warning', message: 'Template is very short' });
        }
        
        if (!template.includes('return')) {
            validationResults.push({ type: 'warning', message: 'No return statements found' });
        }
        
        // Show validation results
        showValidationResults(validationResults);
        
    } else {
        showNotification('No template content to validate!', 'warning');
    }
}

function showValidationResults(results) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    
    const successCount = results.filter(r => r.type === 'success').length;
    const warningCount = results.filter(r => r.type === 'warning').length;
    const errorCount = results.filter(r => r.type === 'error').length;
    
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>
                        Template Validation Results
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 text-success">${successCount}</div>
                                <small class="text-muted">Success</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 text-warning">${warningCount}</div>
                                <small class="text-muted">Warnings</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 text-danger">${errorCount}</div>
                                <small class="text-muted">Errors</small>
                            </div>
                        </div>
                    </div>
                    <div class="list-group">
                        ${results.map(result => `
                            <div class="list-group-item d-flex align-items-center">
                                <i class="fas fa-${result.type === 'success' ? 'check-circle text-success' : result.type === 'warning' ? 'exclamation-triangle text-warning' : 'times-circle text-danger'} me-3"></i>
                                <span>${result.message}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
    
    showNotification(`Validation completed: ${successCount} success, ${warningCount} warnings, ${errorCount} errors`, 'info');
}

function toggleCodeView() {
    const codeViews = document.querySelectorAll('.code-view');
    codeViews.forEach(view => {
        if (view.style.display === 'none' || !view.style.display) {
            view.style.display = 'block';
        } else {
            view.style.display = 'none';
        }
    });
}

function refreshTemplates() {
    showNotification('Refreshing templates...', 'info');
    
    // Simulate refreshing templates from server
    setTimeout(() => {
        // Update template list if exists
        updateTemplateList();
        
        // Clear current template
        const templateTextarea = document.querySelector('textarea[name="license_template"]');
        if (templateTextarea) {
            templateTextarea.value = '';
            templateTextarea.dispatchEvent(new window.Event('change'));
        }
        
        // Show available templates
        showAvailableTemplates();
        
        showNotification('Templates refreshed successfully!', 'success');
    }, 1000);
}

function updateTemplateList() {
    // Update any template list UI if it exists
    const templateList = document.querySelector('.template-list');
    if (templateList) {
        // Get saved templates from localStorage
        const savedTemplates = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith('template_')) {
                try {
                    const template = JSON.parse(localStorage.getItem(key));
                    savedTemplates.push(template);
                } catch {
                    // Skip invalid templates
                }
            }
        }
        
        if (savedTemplates.length > 0) {
            templateList.innerHTML = savedTemplates.map(template => `
                <div class="template-item p-2 border rounded mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${template.name}</strong>
                            <small class="text-muted d-block">${new Date(template.timestamp).toLocaleString()}</small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-primary me-1" onclick="loadTemplate('${template.name}')">
                                <i class="fas fa-download"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteTemplate('${template.name}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    }
}

function showAvailableTemplates() {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-refresh me-2"></i>
                        Available Templates
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-code fa-3x text-primary mb-3"></i>
                                    <h5>Default Template</h5>
                                    <p class="text-muted">Basic license validation template</p>
                                    <button class="btn btn-primary" onclick="loadTemplate('default'); bootstrap.Modal.getInstance(this.closest('.modal')).hide();">
                                        Load Template
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                                    <h5>Advanced Template</h5>
                                    <p class="text-muted">Advanced encryption and validation</p>
                                    <button class="btn btn-success" onclick="loadTemplate('advanced'); bootstrap.Modal.getInstance(this.closest('.modal')).hide();">
                                        Load Template
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-bolt fa-3x text-warning mb-3"></i>
                                    <h5>Simple Template</h5>
                                    <p class="text-muted">Lightweight and simple validation</p>
                                    <button class="btn btn-warning" onclick="loadTemplate('simple'); bootstrap.Modal.getInstance(this.closest('.modal')).hide();">
                                        Load Template
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
}

function viewTemplate() {
    showNotification('Opening template viewer...', 'info');
    
    // Get saved templates from localStorage
    const savedTemplates = [];
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key.startsWith('template_')) {
            try {
                const template = JSON.parse(localStorage.getItem(key));
                savedTemplates.push(template);
            } catch {
                // Skip invalid templates
            }
        }
    }
    
    if (savedTemplates.length === 0) {
        showNotification('No saved templates found!', 'warning');
        return;
    }
    
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-folder-open me-2"></i>
                        Template Viewer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6>Saved Templates</h6>
                            <div class="list-group">
                                ${savedTemplates.map((template, index) => `
                                    <button class="list-group-item list-group-item-action ${index === 0 ? 'active' : ''}" 
                                            onclick="viewTemplateContent('${template.name}', this)">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">${template.name}</h6>
                                            <small>${new Date(template.timestamp).toLocaleDateString()}</small>
                                        </div>
                                        <p class="mb-1">${template.content.substring(0, 50)}...</p>
                                        <small>Version: ${template.version}</small>
                                    </button>
                                `).join('')}
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6>Template Content</h6>
                            <div id="template-content-viewer">
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-file-code fa-3x mb-3"></i>
                                    <p>Select a template to view its content</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Show first template by default
    if (savedTemplates.length > 0) {
        viewTemplateContent(savedTemplates[0].name, modal.querySelector('.list-group-item'));
    }
    
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
    
    showNotification('Template viewer opened!', 'success');
}

function viewTemplateContent(templateName, buttonElement) {
    // Update active button
    document.querySelectorAll('.list-group-item').forEach(btn => btn.classList.remove('active'));
    buttonElement.classList.add('active');
    
    // Get template content
    const templateData = JSON.parse(localStorage.getItem('template_' + templateName));
    if (templateData) {
        const viewer = document.getElementById('template-content-viewer');
        viewer.innerHTML = `
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6>${templateData.name}</h6>
                    <div>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="loadTemplate('${templateName}'); bootstrap.Modal.getInstance(document.querySelector('.modal')).hide();">
                            <i class="fas fa-download me-1"></i>Load
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteTemplate('${templateName}'); this.closest('.modal').querySelector('.btn-close').click();">
                            <i class="fas fa-trash me-1"></i>Delete
                        </button>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted">Created: ${new Date(templateData.timestamp).toLocaleString()}</small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Version: ${templateData.version}</small>
                    </div>
                </div>
            </div>
            <pre class="bg-light p-3 rounded code-preview"><code>${templateData.content}</code></pre>
        `;
    }
}

function createTemplate() {
    showNotification('Creating new template...', 'info');
    
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>
                        Create New Template
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="create-template-form">
                        <div class="mb-3">
                            <label for="template-name" class="form-label">Template Name</label>
                            <input type="text" class="form-control" id="template-name" placeholder="Enter template name" required>
                        </div>
                        <div class="mb-3">
                            <label for="template-type" class="form-label">Template Type</label>
                            <select class="form-select" id="template-type">
                                <option value="default">Default License Template</option>
                                <option value="advanced">Advanced License Template</option>
                                <option value="simple">Simple License Template</option>
                                <option value="custom">Custom Template</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="template-description" class="form-label">Description</label>
                            <textarea class="form-control" id="template-description" rows="2" placeholder="Enter template description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="template-content" class="form-label">Template Content</label>
                            <textarea class="form-control" id="template-content" rows="10" placeholder="Enter your template code here..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveNewTemplate()">Create Template</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Auto-fill content based on type
    const typeSelect = modal.querySelector('#template-type');
    const contentTextarea = modal.querySelector('#template-content');
    
    typeSelect.addEventListener('change', function() {
        const templates = {
            'default': `<?php
// Default License Template
class License {
    public function validate($licenseKey) {
        // License validation logic
        return true;
    }
    
    public function generate($userId) {
        // License generation logic
        return md5($userId . time());
    }
}`,
            'advanced': `<?php
// Advanced License Template
class AdvancedLicense {
    private $encryptionKey;
    
    public function __construct($key) {
        $this->encryptionKey = $key;
    }
    
    public function validate($licenseKey, $hardwareId = null) {
        // Advanced validation with hardware binding
        $decrypted = $this->decrypt($licenseKey);
        return $decrypted && (!$hardwareId || $decrypted['hardware'] === $hardwareId);
    }
    
    private function decrypt($data) {
        // Decryption logic
        return json_decode(base64_decode($data), true);
    }
}`,
            'simple': `<?php
// Simple License Template
function checkLicense($key) {
    return strlen($key) >= 10 && preg_match('/^[A-Z0-9]+$/', $key);
}

function generateLicense() {
    return strtoupper(substr(md5(uniqid()), 0, 16));
}`
        };
        
        contentTextarea.value = templates[this.value] || '';
    });
    
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
    
    showNotification('Template creator opened!', 'success');
}

function saveNewTemplate() {
    const modal = document.querySelector('.modal');
    const name = modal.querySelector('#template-name').value.trim();
    const type = modal.querySelector('#template-type').value;
    const description = modal.querySelector('#template-description').value.trim();
    const content = modal.querySelector('#template-content').value.trim();
    
    if (!name || !content) {
        showNotification('Please fill in all required fields!', 'warning');
        return;
    }
    
    const templateData = {
        name: name,
        type: type,
        description: description,
        content: content,
        timestamp: new Date().toISOString(),
        version: '1.0'
    };
    
    try {
        localStorage.setItem('template_' + name, JSON.stringify(templateData));
        showNotification('Template created successfully!', 'success');
        
        // Load the template into the editor
        const templateTextarea = document.querySelector('textarea[name="license_template"]');
        if (templateTextarea) {
            templateTextarea.value = content;
            templateTextarea.dispatchEvent(new window.Event('change'));
        }
        
        // Close modal
        bootstrap.Modal.getInstance(modal).hide();
        
    } catch {
        showNotification('Failed to create template: ' + error.message, 'error');
    }
}


// Helper functions
function deleteTemplate(templateName) {
    if (window.confirm && confirm('Are you sure you want to delete this template?')) {
        try {
            localStorage.removeItem('template_' + templateName);
            showNotification('Template deleted successfully!', 'success');
            
            // Update template list if exists
            updateTemplateList();
            
        } catch {
            showNotification('Failed to delete template: ' + error.message, 'error');
        }
    }
}

function copyTemplateToClipboard() {
    const templateTextarea = document.querySelector('textarea[name="license_template"]');
    if (templateTextarea && templateTextarea.value.trim()) {
        navigator.clipboard.writeText(templateTextarea.value).then(() => {
            showNotification('Template copied to clipboard!', 'success');
        }).catch(() => {
            showNotification('Failed to copy to clipboard!', 'error');
        });
    } else {
        showNotification('No template content to copy!', 'warning');
    }
}

// Settings Page Tabs Functions
function initSettingsTabs() {
    const container = document.querySelector('.admin-settings-page');
    if (!container) return;

    // Avoid double initialization
    if (container.dataset.settingsTabsInit === '1') return;

    const tabButtons = container.querySelectorAll('.admin-tab-btn');
    const tabPanels = container.querySelectorAll('.admin-tab-panel');

    // Initialize: show first panel (if any) and hide others
    tabPanels.forEach((panel, index) => {
        if (index === 0) {
            panel.classList.add('active');
            panel.classList.remove('admin-tab-panel-hidden');
            panel.style.display = '';
        } else {
            panel.classList.remove('active');
            panel.classList.add('admin-tab-panel-hidden');
            panel.style.display = 'none';
        }
    });

    tabButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();

            const targetTab = button.getAttribute('data-tab');

            // Remove active class from all buttons and hide all panels
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanels.forEach(panel => {
                panel.classList.remove('active');
                panel.classList.add('admin-tab-panel-hidden');
                panel.style.display = 'none';
            });

            // Add active class to clicked button
            button.classList.add('active');

            // Try multiple ways to find the target panel: id equal to data-tab, id with -tab suffix, or data-panel attribute
            let targetPanel = container.querySelector(`#${window.CSS.escape(targetTab)}`);
            if (!targetPanel) targetPanel = container.querySelector(`#${window.CSS.escape(targetTab + '-tab')}`);
            if (!targetPanel) targetPanel = container.querySelector(`.admin-tab-panel[data-panel="${targetTab}"]`);

            if (targetPanel) {
                targetPanel.classList.add('active');
                // ensure it's visible in case CSS relies on inline styles
                targetPanel.style.display = '';
            }
        });
    });

    // Mark initialized
    container.dataset.settingsTabsInit = '1';
}



// Programming Languages Create Functions
function initProgrammingLanguagesCreateFunctions() {
    initProgrammingLanguagesCreateIconPreview();
    initProgrammingLanguagesCreateFormValidation();
    initProgrammingLanguagesCreateTemplatePreview();
}

// Programming Languages Show Tabs Functions
function initProgrammingLanguagesShowTabs() {
    const tabButtons = document.querySelectorAll('.admin-programming-languages-show .admin-tab-btn');
    const tabPanels = document.querySelectorAll('.admin-programming-languages-show .admin-tab-panel');

    // Initialize: Show first panel and hide others
    tabPanels.forEach((panel, index) => {
        if (index === 0) {
            panel.classList.remove('admin-tab-panel-hidden');
        } else {
            panel.classList.add('admin-tab-panel-hidden');
        }
    });

    tabButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            
            const targetTab = button.getAttribute('data-tab');
            
            // Remove active class from all buttons
            tabButtons.forEach(btn => {
                btn.classList.remove('admin-tab-btn-active');
                btn.setAttribute('aria-selected', 'false');
                btn.setAttribute('tabindex', '-1');
            });
            
            // Add active class to clicked button
            button.classList.add('admin-tab-btn-active');
            button.setAttribute('aria-selected', 'true');
            button.setAttribute('tabindex', '0');
            
            // Hide all panels
            tabPanels.forEach(panel => {
                panel.classList.add('admin-tab-panel-hidden');
                panel.setAttribute('aria-hidden', 'true');
            });
            
            // Show target panel
            const targetPanel = document.getElementById(targetTab);
            if (targetPanel) {
                targetPanel.classList.remove('admin-tab-panel-hidden');
                targetPanel.setAttribute('aria-hidden', 'false');
            }
        });
    });
}

// Programming Languages Index Tabs Functions
function initProgrammingLanguagesIndexTabs() {
    const tabButtons = document.querySelectorAll('.admin-programming-languages-index .admin-tab-btn');
    const tabPanels = document.querySelectorAll('.admin-programming-languages-index .admin-tab-panel');

    // Initialize: Show first panel and hide others
    tabPanels.forEach((panel, index) => {
        if (index === 0) {
            panel.classList.remove('admin-tab-panel-hidden');
        } else {
            panel.classList.add('admin-tab-panel-hidden');
        }
    });

    tabButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            
            const targetTab = button.getAttribute('data-tab');
            
            // Remove active class from all buttons
            tabButtons.forEach(btn => {
                btn.classList.remove('admin-tab-btn-active');
                btn.setAttribute('aria-selected', 'false');
                btn.setAttribute('tabindex', '-1');
            });
            
            // Add active class to clicked button
            button.classList.add('admin-tab-btn-active');
            button.setAttribute('aria-selected', 'true');
            button.setAttribute('tabindex', '0');
            
            // Hide all panels
            tabPanels.forEach(panel => {
                panel.classList.add('admin-tab-panel-hidden');
                panel.setAttribute('aria-hidden', 'true');
            });
            
            // Show target panel
            const targetPanel = document.getElementById(targetTab);
            if (targetPanel) {
                targetPanel.classList.remove('admin-tab-panel-hidden');
                targetPanel.setAttribute('aria-hidden', 'false');
            }
        });
    });
}

// Reports Functions
function initReportsFunctions() {
    // Export functionality
    const exportButtons = document.querySelectorAll('[data-format]');
    exportButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const format = button.getAttribute('data-format');
            exportReport(format);
        });
    });

    // Chart export functionality
    const chartExportButtons = document.querySelectorAll('[data-action="export-chart"]');
    chartExportButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const chart = button.getAttribute('data-chart');
            const format = button.getAttribute('data-format');
            exportChart(chart, format);
        });
    });

    // Refresh functionality
    const refreshButtons = document.querySelectorAll('[data-action="refresh-reports"], [data-action="refresh-activity"]');
    refreshButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            refreshReports();
        });
    });

    // Clear blocked IPs functionality
    const clearBlockedIPsButton = document.querySelector('[data-action="clear-blocked-ips"]');
    if (clearBlockedIPsButton) {
        clearBlockedIPsButton.addEventListener('click', (e) => {
            e.preventDefault();
            clearBlockedIPs();
        });
    }

    // Initialize charts
    initCharts();
}

function exportReport(format) {
    // Show loading state
    const button = document.querySelector(`[data-format="${format}"]`);
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Exporting...';
    button.disabled = true;

    // Simulate export process
    setTimeout(() => {
        // Create download link
        const link = document.createElement('a');
        link.href = `/admin/reports/export?format=${format}`;
        link.download = `reports.${format}`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Reset button
        button.innerHTML = originalText;
        button.disabled = false;

        // Show success message
        showNotification('Report exported successfully!', 'success');
    }, 1000);
}

function exportChart(chartName, format) {
    // Show loading state
    const button = document.querySelector(`[data-chart="${chartName}"]`);
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Exporting...';
    button.disabled = true;

    // Simulate chart export
    setTimeout(() => {
        // Reset button
        button.innerHTML = originalText;
        button.disabled = false;

        // Show success message
        showNotification(`${chartName} chart exported successfully!`, 'success');
    }, 1000);
}

function refreshReports() {
    // Show loading state
    const button = document.querySelector('[data-action="refresh-reports"]');
    if (button) {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
        button.disabled = true;

        // Reload page after short delay
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }
}

function clearBlockedIPs() {
    if (window.confirm && confirm('Are you sure you want to clear all blocked IPs? This action cannot be undone.')) {
        // Show loading state
        const button = document.querySelector('[data-action="clear-blocked-ips"]');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Clearing...';
        button.disabled = true;

        // Simulate clear process
        setTimeout(() => {
            // Reset button
            button.innerHTML = originalText;
            button.disabled = false;

            // Show success message
            showNotification('Blocked IPs cleared successfully!', 'success');
            
            // Reload the page to reflect changes
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }, 1000);
    }
}

function initCharts() {
    // Initialize Chart.js charts if available
    if (typeof Chart !== 'undefined') {
        // System Overview Chart
        const systemOverviewCanvas = document.getElementById('systemOverviewChart');
        if (systemOverviewCanvas) {
            try {
                const systemOverviewData = JSON.parse(systemOverviewCanvas.getAttribute('data-chart-data') || '{}');
                if (systemOverviewData && Object.keys(systemOverviewData).length > 0) {
                    new Chart(systemOverviewCanvas, {
                        type: 'bar',
                        data: systemOverviewData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                }
                            }
                        }
                    });
                } else {
                    showChartFallback(systemOverviewCanvas);
                }
            } catch {
                // Error initializing system overview chart
                showChartFallback(systemOverviewCanvas);
            }
        }

        // License Status Chart
        const licenseStatusCanvas = document.getElementById('licenseStatusChart');
        if (licenseStatusCanvas) {
            try {
                const licenseStatusData = JSON.parse(licenseStatusCanvas.getAttribute('data-chart-data') || '{}');
                if (licenseStatusData && Object.keys(licenseStatusData).length > 0) {
                    new Chart(licenseStatusCanvas, {
                        type: 'doughnut',
                        data: licenseStatusData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true
                                    }
                                }
                            },
                            cutout: '60%'
                        }
                    });
                } else {
                    showChartFallback(licenseStatusCanvas);
                }
            } catch {
                // Error initializing license status chart
                showChartFallback(licenseStatusCanvas);
            }
        }

        // License Type Chart
        const licenseTypeCanvas = document.getElementById('licenseTypeChart');
        if (licenseTypeCanvas) {
            try {
                const licenseTypeData = JSON.parse(licenseTypeCanvas.getAttribute('data-chart-data') || '{}');
                if (licenseTypeData && Object.keys(licenseTypeData).length > 0) {
                    new Chart(licenseTypeCanvas, {
                        type: 'doughnut',
                        data: licenseTypeData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true
                                    }
                                }
                            },
                            cutout: '60%'
                        }
                    });
                } else {
                    showChartFallback(licenseTypeCanvas);
                }
            } catch {
                // Error initializing license type chart
                showChartFallback(licenseTypeCanvas);
            }
        }

        // Monthly Licenses Chart
        const monthlyLicensesCanvas = document.getElementById('monthlyLicensesChart');
        if (monthlyLicensesCanvas) {
            try {
                const monthlyLicensesData = JSON.parse(monthlyLicensesCanvas.getAttribute('data-chart-data') || '{}');
                if (monthlyLicensesData && Object.keys(monthlyLicensesData).length > 0) {
                    new Chart(monthlyLicensesCanvas, {
                        type: 'line',
                        data: monthlyLicensesData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                }
                            }
                        }
                    });
                } else {
                    showChartFallback(monthlyLicensesCanvas);
                }
            } catch {
                // Error initializing monthly licenses chart
                showChartFallback(monthlyLicensesCanvas);
            }
        }

        // API Status Chart
        const apiStatusCanvas = document.getElementById('apiStatusChart');
        if (apiStatusCanvas) {
            try {
                const apiStatusData = JSON.parse(apiStatusCanvas.getAttribute('data-chart-data') || '{}');
                if (apiStatusData && Object.keys(apiStatusData).length > 0) {
                    new Chart(apiStatusCanvas, {
                        type: 'bar',
                        data: apiStatusData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                }
                            }
                        }
                    });
                } else {
                    showChartFallback(apiStatusCanvas);
                }
            } catch {
                // Error initializing API status chart
                showChartFallback(apiStatusCanvas);
            }
        }

        // API Calls Chart
        const apiCallsCanvas = document.getElementById('apiCallsChart');
        if (apiCallsCanvas) {
            try {
                const apiCallsData = JSON.parse(apiCallsCanvas.getAttribute('data-chart-data') || '{}');
                if (apiCallsData && Object.keys(apiCallsData).length > 0) {
                    new Chart(apiCallsCanvas, {
                        type: 'line',
                        data: apiCallsData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                }
                            }
                        }
                    });
                } else {
                    showChartFallback(apiCallsCanvas);
                }
            } catch {
                // Error initializing API calls chart
                showChartFallback(apiCallsCanvas);
            }
        }

        // Invoices Monthly Chart
        const invoicesMonthlyCanvas = document.getElementById('invoicesMonthlyChart');
        if (invoicesMonthlyCanvas) {
            try {
                const invoicesMonthlyData = JSON.parse(invoicesMonthlyCanvas.getAttribute('data-chart-data') || '{}');
                if (invoicesMonthlyData && Object.keys(invoicesMonthlyData).length > 0) {
                    new Chart(invoicesMonthlyCanvas, {
                        type: 'line',
                        data: invoicesMonthlyData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                }
                            }
                        }
                    });
                } else {
                    showChartFallback(invoicesMonthlyCanvas);
                }
            } catch {
                // Error initializing invoices monthly chart
                showChartFallback(invoicesMonthlyCanvas);
            }
        }

        // Monthly Revenue Chart
        const monthlyRevenueCanvas = document.getElementById('monthlyRevenueChart');
        if (monthlyRevenueCanvas) {
            try {
                const monthlyRevenueData = JSON.parse(monthlyRevenueCanvas.getAttribute('data-chart-data') || '{}');
                if (monthlyRevenueData && Object.keys(monthlyRevenueData).length > 0) {
                    new Chart(monthlyRevenueCanvas, {
                        type: 'line',
                        data: monthlyRevenueData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return '$' + value.toLocaleString();
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                }
                            }
                        }
                    });
                } else {
                    showChartFallback(monthlyRevenueCanvas);
                }
            } catch {
                // Error initializing monthly revenue chart
                showChartFallback(monthlyRevenueCanvas);
            }
        }

        // Activity Timeline Chart
        const activityTimelineCanvas = document.getElementById('activityTimelineChart');
        if (activityTimelineCanvas) {
            try {
                const activityTimelineData = JSON.parse(activityTimelineCanvas.getAttribute('data-chart-data') || '{}');
                if (activityTimelineData && Object.keys(activityTimelineData).length > 0) {
                    new Chart(activityTimelineCanvas, {
                        type: 'line',
                        data: activityTimelineData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                }
                            }
                        }
                    });
                } else {
                    showChartFallback(activityTimelineCanvas);
                }
            } catch {
                // Error initializing activity timeline chart
                showChartFallback(activityTimelineCanvas);
            }
        }

        // User Registrations Chart
        const userRegistrationsCanvas = document.getElementById('userRegistrationsChart');
        if (userRegistrationsCanvas) {
            try {
                const userRegistrationsData = JSON.parse(userRegistrationsCanvas.getAttribute('data-chart-data') || '{}');
                if (userRegistrationsData && Object.keys(userRegistrationsData).length > 0) {
                    new Chart(userRegistrationsCanvas, {
                        type: 'line',
                        data: userRegistrationsData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    }
                                }
                            }
                        }
                    });
                } else {
                    showChartFallback(userRegistrationsCanvas);
                }
            } catch {
                // Error initializing user registrations chart
                showChartFallback(userRegistrationsCanvas);
            }
        }
    } else {
        // Chart.js not available, show fallback for all charts
        const chartCanvases = document.querySelectorAll('canvas[id$="Chart"]');
        chartCanvases.forEach(canvas => {
            showChartFallback(canvas);
        });
    }
}

function showChartFallback(canvas) {
    const fallback = canvas.parentElement.querySelector('.chart-fallback');
    if (fallback) {
        fallback.style.display = 'block';
        canvas.style.display = 'none';
    }
}

function initProgrammingLanguagesCreateIconPreview() {
    const iconInput = document.getElementById('icon');
    const iconPreview = document.getElementById('icon-preview');
    
    if (iconInput && iconPreview) {
        iconInput.addEventListener('input', function() {
            const iconClass = this.value.trim();
            if (iconClass) {
                iconPreview.className = iconClass;
            } else {
                iconPreview.className = 'fas fa-code';
            }
        });
    }
}

function initProgrammingLanguagesCreateTemplatePreview() {
    const templateInput = document.getElementById('license_template');
    const templatePreview = document.getElementById('template-preview');
    
    if (templateInput && templatePreview) {
        templateInput.addEventListener('input', function() {
            const template = this.value.trim();
            if (template) {
                templatePreview.textContent = template;
            } else {
                templatePreview.textContent = '{{ trans("app.template_generated_based_language") }}';
            }
        });
    }
}

function initProgrammingLanguagesCreateFormValidation() {
    const form = document.querySelector('.admin-programming-languages-create .needs-validation');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }
}
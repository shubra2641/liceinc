/**
 * Enhanced Admin JavaScript
 * Modern, modular JavaScript with proper error handling and accessibility
 * Envato-compliant with graceful degradation
 */

'use strict';

// ===== CORE MODULE SYSTEM =====

class AdminModule {
    constructor(name, options = {}) {
        this.name = name;
        this.options = { ...this.defaultOptions, ...options };
        this.initialized = false;
        this.elements = new Map();
        this.eventListeners = new Map();
    }

    get defaultOptions() {
        return {
            debug: false,
            autoInit: true,
            selectors: {},
            events: {},
        };
    }

    init() {
        if (this.initialized) return;
        
        try {
            this.log('Initializing module:', this.name);
            this.setupElements();
            this.bindEvents();
            this.initialized = true;
            this.log('Module initialized:', this.name);
        } catch (error) {
            this.handleError('Failed to initialize module', error);
        }
    }

    destroy() {
        this.removeEventListeners();
        this.elements.clear();
        this.initialized = false;
        this.log('Module destroyed:', this.name);
    }

    setupElements() {
        // Override in subclasses
    }

    bindEvents() {
        // Override in subclasses
    }

    removeEventListeners() {
        this.eventListeners.forEach((listener, element) => {
            element.removeEventListener(listener.event, listener.handler);
        });
        this.eventListeners.clear();
    }

    addEventListener(element, event, handler, options = {}) {
        if (!element) return;
        
        element.addEventListener(event, handler, options);
        this.eventListeners.set(element, { event, handler, options });
    }

    querySelector(selector, context = document) {
        return context.querySelector(selector);
    }

    querySelectorAll(selector, context = document) {
        return Array.from(context.querySelectorAll(selector));
    }

    log(...args) {
        if (this.options.debug) {
            console.log(`[${this.name}]`, ...args);
        }
    }

    handleError(message, error) {
        console.error(`[${this.name}] ${message}:`, error);
        
        // Show user-friendly error message
        this.showNotification(
            'An error occurred. Please refresh the page and try again.',
            'error'
        );
    }

    showNotification(message, type = 'info', duration = 5000) {
        const notification = this.createNotification(message, type);
        document.body.appendChild(notification);
        
        // Auto-remove after duration
        setTimeout(() => {
            this.removeNotification(notification);
        }, duration);
    }

    createNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `admin-notification admin-notification-${type}`;
        notification.setAttribute('role', 'alert');
        notification.setAttribute('aria-live', 'polite');
        
        const icon = this.getNotificationIcon(type);
        const closeButton = this.createCloseButton();
        
        notification.innerHTML = `
            <div class="admin-notification-content">
                <div class="admin-notification-icon">${icon}</div>
                <div class="admin-notification-message">${message}</div>
                <button class="admin-notification-close" aria-label="Close notification">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M8 8.707l3.646 3.647.708-.707L8.707 8l3.647-3.646-.707-.708L8 7.293 4.354 3.646l-.707.708L7.293 8l-3.646 3.646.707.708L8 8.707z"/>
                    </svg>
                </button>
            </div>
        `;
        
        // Add close functionality
        const closeBtn = notification.querySelector('.admin-notification-close');
        this.addEventListener(closeBtn, 'click', () => {
            this.removeNotification(notification);
        });
        
        return notification;
    }

    getNotificationIcon(type) {
        const icons = {
            success: '<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
            error: '<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
            warning: '<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
            info: '<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
        };
        
        return icons[type] || icons.info;
    }

    createCloseButton() {
        const button = document.createElement('button');
        button.className = 'admin-notification-close';
        button.setAttribute('aria-label', 'Close notification');
        return button;
    }

    removeNotification(notification) {
        if (notification && notification.parentNode) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    }
}

// ===== ENHANCED DASHBOARD MODULE =====

class EnhancedDashboard extends AdminModule {
    constructor(options = {}) {
        super('EnhancedDashboard', options);
        this.charts = new Map();
        this.widgets = new Map();
    }

    get defaultOptions() {
        return {
            ...super.defaultOptions,
            selectors: {
                sidebar: '.admin-sidebar',
                sidebarToggle: '.admin-sidebar-toggle',
                mainContent: '.admin-main',
                statsCards: '.admin-stat-card',
                charts: '.admin-chart',
                widgets: '.admin-widget',
                notifications: '.admin-notifications',
            },
            events: {
                sidebarToggle: 'click',
                windowResize: 'resize',
                visibilityChange: 'visibilitychange',
            }
        };
    }

    setupElements() {
        this.elements.set('sidebar', this.querySelector(this.options.selectors.sidebar));
        this.elements.set('sidebarToggle', this.querySelector(this.options.selectors.sidebarToggle));
        this.elements.set('mainContent', this.querySelector(this.options.selectors.mainContent));
        this.elements.set('statsCards', this.querySelectorAll(this.options.selectors.statsCards));
        this.elements.set('charts', this.querySelectorAll(this.options.selectors.charts));
        this.elements.set('widgets', this.querySelectorAll(this.options.selectors.widgets));
    }

    bindEvents() {
        // Sidebar toggle
        const sidebarToggle = this.elements.get('sidebarToggle');
        if (sidebarToggle) {
            this.addEventListener(sidebarToggle, 'click', (e) => {
                e.preventDefault();
                this.toggleSidebar();
            });
        }

        // Window resize
        this.addEventListener(window, 'resize', () => {
            this.handleResize();
        });

        // Visibility change (for performance optimization)
        this.addEventListener(document, 'visibilitychange', () => {
            this.handleVisibilityChange();
        });

        // Keyboard shortcuts
        this.addEventListener(document, 'keydown', (e) => {
            this.handleKeyboardShortcuts(e);
        });

        // Initialize stats cards
        this.initStatsCards();
        
        // Initialize charts
        this.initCharts();
        
        // Initialize widgets
        this.initWidgets();
    }

    toggleSidebar() {
        const sidebar = this.elements.get('sidebar');
        if (!sidebar) return;

        const isCollapsed = sidebar.classList.contains('collapsed');
        
        if (isCollapsed) {
            sidebar.classList.remove('collapsed');
            this.elements.get('mainContent')?.classList.remove('sidebar-collapsed');
        } else {
            sidebar.classList.add('collapsed');
            this.elements.get('mainContent')?.classList.add('sidebar-collapsed');
        }

        // Save state
        localStorage.setItem('admin-sidebar-collapsed', !isCollapsed);
        
        // Announce to screen readers
        this.announceToScreenReader(
            isCollapsed ? 'Sidebar expanded' : 'Sidebar collapsed'
        );
    }

    handleResize() {
        const width = window.innerWidth;
        
        // Auto-collapse sidebar on mobile
        if (width < 1024) {
            this.elements.get('sidebar')?.classList.add('collapsed');
        } else {
            // Restore saved state on desktop
            const savedState = localStorage.getItem('admin-sidebar-collapsed');
            if (savedState === 'true') {
                this.elements.get('sidebar')?.classList.add('collapsed');
            }
        }

        // Update charts
        this.charts.forEach(chart => {
            if (chart.resize) {
                chart.resize();
            }
        });
    }

    handleVisibilityChange() {
        if (document.hidden) {
            // Pause animations and updates when tab is not visible
            this.pauseUpdates();
        } else {
            // Resume animations and updates when tab becomes visible
            this.resumeUpdates();
        }
    }

    handleKeyboardShortcuts(e) {
        // Ctrl/Cmd + B: Toggle sidebar
        if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
            e.preventDefault();
            this.toggleSidebar();
        }

        // Escape: Close modals
        if (e.key === 'Escape') {
            this.closeAllModals();
        }
    }

    initStatsCards() {
        const statsCards = this.elements.get('statsCards');
        if (!statsCards) return;

        statsCards.forEach(card => {
            this.animateStatsCard(card);
        });
    }

    animateStatsCard(card) {
        const valueElement = card.querySelector('.admin-stat-value');
        if (!valueElement) return;

        const finalValue = parseInt(valueElement.textContent.replace(/[^\d]/g, ''));
        if (isNaN(finalValue)) return;

        let currentValue = 0;
        const increment = finalValue / 50; // 50 steps
        const duration = 1000; // 1 second
        const stepTime = duration / 50;

        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                currentValue = finalValue;
                clearInterval(timer);
            }
            
            valueElement.textContent = this.formatNumber(Math.floor(currentValue));
        }, stepTime);
    }

    formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    }

    initCharts() {
        const chartElements = this.elements.get('charts');
        if (!chartElements) return;

        chartElements.forEach(chartElement => {
            this.initChart(chartElement);
        });
    }

    initChart(chartElement) {
        // This would integrate with Chart.js or similar
        // For now, we'll just mark it as initialized
        chartElement.setAttribute('data-initialized', 'true');
    }

    initWidgets() {
        const widgets = this.elements.get('widgets');
        if (!widgets) return;

        widgets.forEach(widget => {
            this.initWidget(widget);
        });
    }

    initWidget(widget) {
        // Initialize individual widgets
        widget.setAttribute('data-initialized', 'true');
    }

    pauseUpdates() {
        // Pause any running animations or updates
        this.charts.forEach(chart => {
            if (chart.pause) {
                chart.pause();
            }
        });
    }

    resumeUpdates() {
        // Resume animations and updates
        this.charts.forEach(chart => {
            if (chart.resume) {
                chart.resume();
            }
        });
    }

    closeAllModals() {
        const modals = this.querySelectorAll('.admin-modal-overlay.show');
        modals.forEach(modal => {
            modal.classList.remove('show');
        });
    }

    announceToScreenReader(message) {
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'admin-sr-only';
        announcement.textContent = message;
        
        document.body.appendChild(announcement);
        
        setTimeout(() => {
            announcement.remove();
        }, 1000);
    }
}

// ===== ENHANCED FORM MODULE =====

class EnhancedForm extends AdminModule {
    constructor(options = {}) {
        super('EnhancedForm', options);
        this.forms = new Map();
        this.validators = new Map();
    }

    get defaultOptions() {
        return {
            ...super.defaultOptions,
            selectors: {
                forms: 'form[data-enhanced="true"]',
                inputs: 'input, select, textarea',
                submitButtons: 'button[type="submit"], input[type="submit"]',
                errorContainers: '.admin-form-error',
            },
            events: {
                formSubmit: 'submit',
                inputChange: 'change',
                inputBlur: 'blur',
                inputFocus: 'focus',
            }
        };
    }

    setupElements() {
        const forms = this.querySelectorAll(this.options.selectors.forms);
        forms.forEach(form => {
            this.elements.set(`form-${form.id || Math.random()}`, form);
        });
    }

    bindEvents() {
        this.elements.forEach((form, key) => {
            if (key.startsWith('form-')) {
                this.initForm(form);
            }
        });
    }

    initForm(form) {
        // Add form validation
        this.addEventListener(form, 'submit', (e) => {
            if (!this.validateForm(form)) {
                e.preventDefault();
                return false;
            }
        });

        // Real-time validation
        const inputs = form.querySelectorAll(this.options.selectors.inputs);
        inputs.forEach(input => {
            this.addEventListener(input, 'blur', () => {
                this.validateField(input);
            });

            this.addEventListener(input, 'input', () => {
                this.clearFieldError(input);
            });
        });

        // Auto-save functionality
        if (form.dataset.autoSave === 'true') {
            this.initAutoSave(form);
        }
    }

    validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll(this.options.selectors.inputs);
        
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });

        return isValid;
    }

    validateField(input) {
        const value = input.value.trim();
        const rules = this.getValidationRules(input);
        let isValid = true;
        let errorMessage = '';

        // Required validation
        if (rules.required && !value) {
            isValid = false;
            errorMessage = 'This field is required';
        }

        // Email validation
        if (isValid && rules.email && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
        }

        // Min length validation
        if (isValid && rules.minLength && value.length < rules.minLength) {
            isValid = false;
            errorMessage = `Minimum length is ${rules.minLength} characters`;
        }

        // Max length validation
        if (isValid && rules.maxLength && value.length > rules.maxLength) {
            isValid = false;
            errorMessage = `Maximum length is ${rules.maxLength} characters`;
        }

        // Pattern validation
        if (isValid && rules.pattern && value) {
            const regex = new RegExp(rules.pattern);
            if (!regex.test(value)) {
                isValid = false;
                errorMessage = rules.patternMessage || 'Invalid format';
            }
        }

        // Show/hide error
        if (isValid) {
            this.clearFieldError(input);
        } else {
            this.showFieldError(input, errorMessage);
        }

        return isValid;
    }

    getValidationRules(input) {
        const rules = {};
        
        if (input.hasAttribute('required')) {
            rules.required = true;
        }
        
        if (input.type === 'email') {
            rules.email = true;
        }
        
        if (input.hasAttribute('minlength')) {
            rules.minLength = parseInt(input.getAttribute('minlength'));
        }
        
        if (input.hasAttribute('maxlength')) {
            rules.maxLength = parseInt(input.getAttribute('maxlength'));
        }
        
        if (input.hasAttribute('pattern')) {
            rules.pattern = input.getAttribute('pattern');
            rules.patternMessage = input.getAttribute('data-pattern-message');
        }

        return rules;
    }

    showFieldError(input, message) {
        input.classList.add('error');
        
        let errorContainer = input.parentNode.querySelector('.admin-form-error');
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.className = 'admin-form-error';
            input.parentNode.appendChild(errorContainer);
        }
        
        errorContainer.textContent = message;
        errorContainer.setAttribute('role', 'alert');
    }

    clearFieldError(input) {
        input.classList.remove('error');
        
        const errorContainer = input.parentNode.querySelector('.admin-form-error');
        if (errorContainer) {
            errorContainer.remove();
        }
    }

    initAutoSave(form) {
        const inputs = form.querySelectorAll('input, select, textarea');
        let saveTimeout;

        inputs.forEach(input => {
            this.addEventListener(input, 'input', () => {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(() => {
                    this.autoSave(form);
                }, 2000); // Save after 2 seconds of inactivity
            });
        });
    }

    autoSave(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Show saving indicator
        this.showSavingIndicator(form);

        // Simulate API call
        fetch(form.action || window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(data),
        })
        .then(response => response.json())
        .then(result => {
            this.hideSavingIndicator(form);
            if (result.success) {
                this.showNotification('Changes saved automatically', 'success', 2000);
            }
        })
        .catch(error => {
            this.hideSavingIndicator(form);
            this.handleError('Auto-save failed', error);
        });
    }

    showSavingIndicator(form) {
        const indicator = document.createElement('div');
        indicator.className = 'admin-auto-save-indicator';
        indicator.innerHTML = `
            <div class="admin-spinner"></div>
            <span>Saving...</span>
        `;
        
        form.appendChild(indicator);
    }

    hideSavingIndicator(form) {
        const indicator = form.querySelector('.admin-auto-save-indicator');
        if (indicator) {
            indicator.remove();
        }
    }
}

// ===== ENHANCED TABLE MODULE =====

class EnhancedTable extends AdminModule {
    constructor(options = {}) {
        super('EnhancedTable', options);
        this.tables = new Map();
    }

    get defaultOptions() {
        return {
            ...super.defaultOptions,
            selectors: {
                tables: '.admin-table',
                sortableHeaders: 'th[data-sortable="true"]',
                searchInputs: '.admin-table-search',
                pagination: '.admin-pagination',
            },
            events: {
                headerClick: 'click',
                searchInput: 'input',
                paginationClick: 'click',
            }
        };
    }

    setupElements() {
        const tables = this.querySelectorAll(this.options.selectors.tables);
        tables.forEach(table => {
            this.elements.set(`table-${table.id || Math.random()}`, table);
        });
    }

    bindEvents() {
        this.elements.forEach((table, key) => {
            if (key.startsWith('table-')) {
                this.initTable(table);
            }
        });
    }

    initTable(table) {
        // Initialize sorting
        this.initSorting(table);
        
        // Initialize search
        this.initSearch(table);
        
        // Initialize pagination
        this.initPagination(table);
        
        // Initialize responsive features
        this.initResponsive(table);
    }

    initSorting(table) {
        const headers = table.querySelectorAll(this.options.selectors.sortableHeaders);
        
        headers.forEach(header => {
            this.addEventListener(header, 'click', () => {
                this.sortTable(table, header);
            });
        });
    }

    sortTable(table, header) {
        const column = header.dataset.column;
        const currentSort = header.dataset.sort || 'asc';
        const newSort = currentSort === 'asc' ? 'desc' : 'asc';
        
        // Update header
        header.dataset.sort = newSort;
        
        // Clear other headers
        const otherHeaders = table.querySelectorAll('th[data-sortable="true"]');
        otherHeaders.forEach(h => {
            if (h !== header) {
                h.dataset.sort = '';
            }
        });
        
        // Sort rows
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            const aValue = a.querySelector(`[data-column="${column}"]`)?.textContent || '';
            const bValue = b.querySelector(`[data-column="${column}"]`)?.textContent || '';
            
            if (newSort === 'asc') {
                return aValue.localeCompare(bValue);
            } else {
                return bValue.localeCompare(aValue);
            }
        });
        
        // Re-append sorted rows
        rows.forEach(row => tbody.appendChild(row));
    }

    initSearch(table) {
        const searchInput = table.parentNode.querySelector(this.options.selectors.searchInputs);
        if (!searchInput) return;
        
        this.addEventListener(searchInput, 'input', () => {
            this.filterTable(table, searchInput.value);
        });
    }

    filterTable(table, searchTerm) {
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matches = text.includes(searchTerm.toLowerCase());
            
            row.style.display = matches ? '' : 'none';
        });
    }

    initPagination(table) {
        const pagination = table.parentNode.querySelector(this.options.selectors.pagination);
        if (!pagination) return;
        
        const links = pagination.querySelectorAll('a');
        links.forEach(link => {
            this.addEventListener(link, 'click', (e) => {
                e.preventDefault();
                const page = link.dataset.page;
                this.loadPage(table, page);
            });
        });
    }

    loadPage(table, page) {
        // This would typically make an AJAX request
        // For now, we'll just show a loading state
        this.showTableLoading(table);
        
        setTimeout(() => {
            this.hideTableLoading(table);
        }, 1000);
    }

    showTableLoading(table) {
        const tbody = table.querySelector('tbody');
        tbody.style.opacity = '0.5';
        tbody.style.pointerEvents = 'none';
    }

    hideTableLoading(table) {
        const tbody = table.querySelector('tbody');
        tbody.style.opacity = '';
        tbody.style.pointerEvents = '';
    }

    initResponsive(table) {
        // Add responsive wrapper if needed
        if (!table.parentNode.classList.contains('admin-table-container')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'admin-table-container';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    }
}

// ===== ENHANCED MODAL MODULE =====

class EnhancedModal extends AdminModule {
    constructor(options = {}) {
        super('EnhancedModal', options);
        this.modals = new Map();
    }

    get defaultOptions() {
        return {
            ...super.defaultOptions,
            selectors: {
                modals: '.admin-modal-overlay',
                triggers: '[data-modal-trigger]',
                closeButtons: '.admin-modal-close',
            },
            events: {
                triggerClick: 'click',
                closeClick: 'click',
                overlayClick: 'click',
                escapeKey: 'keydown',
            }
        };
    }

    setupElements() {
        const modals = this.querySelectorAll(this.options.selectors.modals);
        modals.forEach(modal => {
            this.elements.set(`modal-${modal.id || Math.random()}`, modal);
        });
    }

    bindEvents() {
        // Modal triggers
        const triggers = this.querySelectorAll(this.options.selectors.triggers);
        triggers.forEach(trigger => {
            this.addEventListener(trigger, 'click', (e) => {
                e.preventDefault();
                const modalId = trigger.dataset.modalTrigger;
                this.openModal(modalId);
            });
        });

        // Close buttons
        const closeButtons = this.querySelectorAll(this.options.selectors.closeButtons);
        closeButtons.forEach(button => {
            this.addEventListener(button, 'click', (e) => {
                e.preventDefault();
                const modal = button.closest('.admin-modal-overlay');
                this.closeModal(modal);
            });
        });

        // Overlay clicks
        this.elements.forEach((modal, key) => {
            if (key.startsWith('modal-')) {
                this.addEventListener(modal, 'click', (e) => {
                    if (e.target === modal) {
                        this.closeModal(modal);
                    }
                });
            }
        });

        // Escape key
        this.addEventListener(document, 'keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        
        // Focus management
        const focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        if (focusableElements.length > 0) {
            focusableElements[0].focus();
        }

        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }

    closeModal(modal) {
        if (!modal) return;

        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        
        // Restore body scroll
        document.body.style.overflow = '';
    }

    closeAllModals() {
        this.elements.forEach((modal, key) => {
            if (key.startsWith('modal-')) {
                this.closeModal(modal);
            }
        });
    }
}

// ===== MAIN APPLICATION CLASS =====

class AdminApplication {
    constructor() {
        this.modules = new Map();
        this.initialized = false;
    }

    init() {
        if (this.initialized) return;

        try {
            // Check if JavaScript is enabled
            document.documentElement.classList.add('js-enabled');

            // Initialize core modules
            this.initModules();

            // Setup global error handling
            this.setupErrorHandling();

            // Setup performance monitoring
            this.setupPerformanceMonitoring();

            this.initialized = true;
            console.log('Admin application initialized successfully');
        } catch (error) {
            console.error('Failed to initialize admin application:', error);
        }
    }

    initModules() {
        // Initialize dashboard
        this.modules.set('dashboard', new EnhancedDashboard({
            debug: window.location.hostname === 'localhost'
        }));

        // Initialize forms
        this.modules.set('forms', new EnhancedForm({
            debug: window.location.hostname === 'localhost'
        }));

        // Initialize tables
        this.modules.set('tables', new EnhancedTable({
            debug: window.location.hostname === 'localhost'
        }));

        // Initialize modals
        this.modules.set('modals', new EnhancedModal({
            debug: window.location.hostname === 'localhost'
        }));

        // Initialize all modules
        this.modules.forEach(module => {
            module.init();
        });
    }

    setupErrorHandling() {
        window.addEventListener('error', (e) => {
            console.error('Global error:', e.error);
            this.showGlobalError();
        });

        window.addEventListener('unhandledrejection', (e) => {
            console.error('Unhandled promise rejection:', e.reason);
            this.showGlobalError();
        });
    }

    setupPerformanceMonitoring() {
        // Monitor performance
        if ('performance' in window) {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    const perfData = performance.getEntriesByType('navigation')[0];
                    console.log('Page load time:', perfData.loadEventEnd - perfData.loadEventStart, 'ms');
                }, 0);
            });
        }
    }

    showGlobalError() {
        // Show user-friendly error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'admin-global-error';
        errorDiv.innerHTML = `
            <div class="admin-global-error-content">
                <h3>Something went wrong</h3>
                <p>We're sorry, but something unexpected happened. Please refresh the page and try again.</p>
                <button onclick="window.location.reload()" class="admin-btn admin-btn-primary">
                    Refresh Page
                </button>
            </div>
        `;
        
        document.body.appendChild(errorDiv);
    }

    destroy() {
        this.modules.forEach(module => {
            module.destroy();
        });
        this.modules.clear();
        this.initialized = false;
    }
}

// ===== INITIALIZATION =====

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.adminApp = new AdminApplication();
        window.adminApp.init();
    });
} else {
    window.adminApp = new AdminApplication();
    window.adminApp.init();
}

// Graceful degradation for older browsers
if (!window.Promise) {
    console.warn('This application requires a modern browser with Promise support');
    document.body.innerHTML = '<div class="admin-browser-warning">Please upgrade your browser to use this application.</div>';
}

// Export for global access
window.AdminModule = AdminModule;
window.EnhancedDashboard = EnhancedDashboard;
window.EnhancedForm = EnhancedForm;
window.EnhancedTable = EnhancedTable;
window.EnhancedModal = EnhancedModal;
window.AdminApplication = AdminApplication;

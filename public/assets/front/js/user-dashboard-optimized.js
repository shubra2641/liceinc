/* ===== USER DASHBOARD OPTIMIZED JS ===== */
/* Optimized and compressed version - Removed duplicates and unused code */

(function() {
    'use strict';

    // ===== UTILITY FUNCTIONS =====
    const $ = (selector, context = document) => context.querySelector(selector);
    const $$ = (selector, context = document) => context.querySelectorAll(selector);
    
    const showNotification = (message, type = 'info') => {
        const notification = document.createElement('div');
        notification.className = `user-notification user-notification-${type} show`;
        
        // Create DOM elements safely to prevent XSS
        const notificationContent = document.createElement('div');
        notificationContent.className = 'user-notification-content';
        
        const notificationIcon = document.createElement('div');
        notificationIcon.className = 'user-notification-icon';
        const icon = document.createElement('i');
        const iconClass = type === 'success' ? 'check' : type === 'error' ? 'times' : type === 'warning' ? 'exclamation' : 'info';
        icon.className = `fas fa-${iconClass}-circle`;
        notificationIcon.appendChild(icon);
        
        const notificationMessage = document.createElement('div');
        notificationMessage.className = 'user-notification-message';
        notificationMessage.textContent = message; // Safe text content
        
        const closeButton = document.createElement('button');
        closeButton.className = 'user-notification-close';
        closeButton.onclick = () => notification.remove();
        const closeIcon = document.createElement('i');
        closeIcon.className = 'fas fa-times';
        closeButton.appendChild(closeIcon);
        
        notificationContent.appendChild(notificationIcon);
        notificationContent.appendChild(notificationMessage);
        notificationContent.appendChild(closeButton);
        
        notification.appendChild(notificationContent);
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 5000);
    };

    const setButtonLoading = (button, isLoading) => {
        const text = button.querySelector('.button-text, .user-btn-text');
        const spinner = button.querySelector('.button-loading, .user-btn-spinner');
        
        if (isLoading) {
            button.disabled = true;
            if (text) text.style.opacity = '0';
            if (spinner) spinner.style.display = 'inline-block';
        } else {
            button.disabled = false;
            if (text) text.style.opacity = '1';
            if (spinner) spinner.style.display = 'none';
        }
    };

    const validateInput = (input) => {
        const value = input.value.trim();
        const type = input.type;
        const required = input.hasAttribute('required');
        
        if (required && !value) {
            return 'This field is required';
        }
        
        if (type === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            return 'Please enter a valid email address';
        }
        
        if (type === 'password' && value && value.length < 6) {
            return 'Password must be at least 6 characters';
        }
        
        return null;
    };

    const showInputError = (input, message) => {
        const inputGroup = input.closest('.form-field-group, .form-group');
        if (!inputGroup) return;
        
        const existingError = inputGroup.querySelector('.form-error, .user-form-error');
        if (existingError) existingError.remove();
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        
        inputGroup.appendChild(errorDiv);
        input.classList.add('form-input-error');
    };

    const clearInputError = (input) => {
        const inputGroup = input.closest('.form-field-group, .form-group');
        if (!inputGroup) return;
        
        const existingError = inputGroup.querySelector('.form-error, .user-form-error');
        if (existingError) existingError.remove();
        
        input.classList.remove('form-input-error');
    };

    // ===== INITIALIZATION FUNCTIONS =====
    const initializeDashboard = () => {
        initializeTables();
        initializeForms();
        initializeTabs();
        initializeCopyButtons();
        initializeFilters();
        initializeMobileMenu();
        initializeProfileTabs();
        initializeLicenseStatus();
        initializeHashScrolling();
        initializeTableOfContents();
        initializeArticleFeatures();
    };

    const initializeAuth = () => {
        initializePasswordToggles();
        initializeFormValidation();
        initializeFormLoading();
        initializeFormAnimations();
    };

    const initializePasswordToggles = () => {
        const toggles = $$('[data-password-toggle]');
        toggles.forEach(toggle => {
            const input = $(toggle.dataset.passwordToggle);
            const showIcon = $(toggle.dataset.showIcon);
            const hideIcon = $(toggle.dataset.hideIcon);
            
            if (input && showIcon && hideIcon) {
                toggle.addEventListener('click', () => togglePasswordVisibility(input, showIcon, hideIcon));
            }
        });
    };

    const togglePasswordVisibility = (input, showIcon, hideIcon) => {
        if (input.type === 'password') {
            input.type = 'text';
            showIcon.style.display = 'none';
            hideIcon.style.display = 'inline';
        } else {
            input.type = 'password';
            showIcon.style.display = 'inline';
            hideIcon.style.display = 'none';
        }
    };

    const initializeFormValidation = () => {
        const forms = $$('.user-form, .register-form, .login-form');
        forms.forEach(form => {
            const inputs = $$('input, textarea, select', form);
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    const error = validateInput(input);
                    if (error) showInputError(input, error);
                    else clearInputError(input);
                });
                
                input.addEventListener('input', () => {
                    if (input.classList.contains('form-input-error')) {
                        const error = validateInput(input);
                        if (!error) clearInputError(input);
                    }
                });
            });
            
            form.addEventListener('submit', (e) => {
                const inputs = $$('input[required], textarea[required], select[required]', form);
                let isValid = true;
                
                inputs.forEach(input => {
                    const error = validateInput(input);
                    if (error) {
                        showInputError(input, error);
                        isValid = false;
                    }
                });
                
                if (!isValid) e.preventDefault();
            });
        });
    };

    const initializeFormLoading = () => {
        const forms = $$('.user-form, .register-form, .login-form');
        forms.forEach(form => {
            form.addEventListener('submit', () => {
                const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
                if (submitBtn) setButtonLoading(submitBtn, true);
            });
        });
    };

    const initializeFormAnimations = () => {
        const cards = $$('.user-card, .user-stat-card, .user-action-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    };

    const initializeTables = () => {
        const containers = $$('.table-container');
        containers.forEach(addScrollIndicator);
    };

    const addScrollIndicator = (container) => {
        const table = $('.user-table', container);
        if (!table) return;

        const checkScrollable = () => {
            const isScrollable = table.scrollWidth > container.clientWidth;
            container.classList.toggle('scrollable', isScrollable);
        };

        checkScrollable();
        window.addEventListener('resize', checkScrollable);
    };

    const initializeForms = () => {
        const forms = $$('form');
        forms.forEach(addFormValidation);
    };

    const addFormValidation = (form) => {
        const inputs = $$('input, textarea, select', form);
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                const error = validateInput(input);
                if (error) showInputError(input, error);
                else clearInputError(input);
            });
        });
    };

    const initializeTabs = () => {
        const tabButtons = $$('.tab-button');
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.dataset.tab;
                if (tabId) showTab(tabId);
            });
        });
    };

    const showTab = (tabId) => {
        const tabs = $$('.tab-content');
        const buttons = $$('.tab-button');
        
        tabs.forEach(tab => tab.classList.remove('active'));
        buttons.forEach(btn => btn.classList.remove('active'));
        
        const activeTab = $(`#${tabId}`);
        const activeButton = $(`[data-tab="${tabId}"]`);
        
        if (activeTab) activeTab.classList.add('active');
        if (activeButton) activeButton.classList.add('active');
    };

    const initializeCopyButtons = () => {
        const copyButtons = $$('.copy-btn, [data-copy]');
        copyButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const text = button.dataset.copy || button.textContent.trim();
                copyToClipboard(text, button);
            });
        });
    };

    const copyToClipboard = (text, button) => {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                showCopySuccess(button);
            }).catch(() => {
                fallbackCopyTextToClipboard(text, button);
            });
        } else {
            fallbackCopyTextToClipboard(text, button);
        }
    };

    const fallbackCopyTextToClipboard = (text, button) => {
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
            showCopySuccess(button);
        } catch (err) {
            showCopyError(button);
        }
        
        document.body.removeChild(textArea);
    };

    const showCopySuccess = (button) => {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        button.style.background = '#10b981';
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.style.background = '';
        }, 2000);
    };

    const showCopyError = (button) => {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-times"></i> Failed';
        button.style.background = '#ef4444';
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.style.background = '';
        }, 2000);
    };

    const initializeFilters = () => {
        initializeLicenseFilters();
        initializeInvoiceFilters();
        initializeTicketFilters();
    };

    const initializeLicenseFilters = () => {
        const statusFilter = $('#licenseStatusFilter');
        const searchInput = $('#licenseSearchInput');
        
        if (statusFilter && searchInput) {
            const filterFunction = () => filterLicenseTable(statusFilter, searchInput);
            statusFilter.addEventListener('change', filterFunction);
            searchInput.addEventListener('input', filterFunction);
        }
    };

    const filterLicenseTable = (statusFilter, searchInput) => {
        const table = $('.user-table');
        if (!table) return;
        
        const rows = $$('tbody tr', table);
        const statusValue = statusFilter.value.toLowerCase();
        const searchValue = searchInput.value.toLowerCase();
        
        rows.forEach(row => {
            const status = row.querySelector('.license-status-badge')?.textContent.toLowerCase() || '';
            const text = row.textContent.toLowerCase();
            
            const statusMatch = !statusValue || status.includes(statusValue);
            const searchMatch = !searchValue || text.includes(searchValue);
            
            row.style.display = statusMatch && searchMatch ? '' : 'none';
        });
    };

    const initializeInvoiceFilters = () => {
        const statusFilter = $('#invoiceStatusFilter');
        const searchInput = $('#invoiceSearchInput');
        
        if (statusFilter && searchInput) {
            const filterFunction = () => filterInvoiceTable(statusFilter, searchInput);
            statusFilter.addEventListener('change', filterFunction);
            searchInput.addEventListener('input', filterFunction);
        }
    };

    const filterInvoiceTable = (statusFilter, searchInput) => {
        const table = $('.user-table');
        if (!table) return;
        
        const rows = $$('tbody tr', table);
        const statusValue = statusFilter.value.toLowerCase();
        const searchValue = searchInput.value.toLowerCase();
        
        rows.forEach(row => {
            const status = row.querySelector('.invoice-status-badge')?.textContent.toLowerCase() || '';
            const text = row.textContent.toLowerCase();
            
            const statusMatch = !statusValue || status.includes(statusValue);
            const searchMatch = !searchValue || text.includes(searchValue);
            
            row.style.display = statusMatch && searchMatch ? '' : 'none';
        });
    };

    const initializeTicketFilters = () => {
        const statusFilter = $('#ticketStatusFilter');
        const priorityFilter = $('#ticketPriorityFilter');
        const searchInput = $('#ticketSearchInput');
        
        if (statusFilter && priorityFilter && searchInput) {
            const filterFunction = () => filterTicketTable(statusFilter, priorityFilter, searchInput);
            statusFilter.addEventListener('change', filterFunction);
            priorityFilter.addEventListener('change', filterFunction);
            searchInput.addEventListener('input', filterFunction);
        }
    };

    const filterTicketTable = (statusFilter, priorityFilter, searchInput) => {
        const table = $('.user-table');
        if (!table) return;
        
        const rows = $$('tbody tr', table);
        const statusValue = statusFilter.value.toLowerCase();
        const priorityValue = priorityFilter.value.toLowerCase();
        const searchValue = searchInput.value.toLowerCase();
        
        rows.forEach(row => {
            const status = row.querySelector('.ticket-status-badge')?.textContent.toLowerCase() || '';
            const priority = row.querySelector('.ticket-priority-badge')?.textContent.toLowerCase() || '';
            const text = row.textContent.toLowerCase();
            
            const statusMatch = !statusValue || status.includes(statusValue);
            const priorityMatch = !priorityValue || priority.includes(priorityValue);
            const searchMatch = !searchValue || text.includes(searchValue);
            
            row.style.display = statusMatch && priorityMatch && searchMatch ? '' : 'none';
        });
    };

    const initializeTicketForm = () => {
        const form = $('#ticketForm');
        if (!form) return;
        
        const submitBtn = $('button[type="submit"]', form);
        if (submitBtn) {
            form.addEventListener('submit', () => {
                setButtonLoading(submitBtn, true);
            });
        }
    };

    const initializeHashScrolling = () => {
        const hash = window.location.hash;
        if (hash) {
            const element = $(hash);
            if (element) {
                setTimeout(() => element.scrollIntoView({ behavior: 'smooth' }), 100);
            }
        }
    };

    const generateTableOfContents = () => {
        const content = $('.article-content, .user-article-content');
        if (!content) return;
        
        const headings = $$('h1, h2, h3, h4, h5, h6', content);
        if (headings.length === 0) return;
        
        const toc = $('.article-toc-content, .user-toc');
        if (!toc) return;
        
        const tocList = document.createElement('ul');
        tocList.className = 'toc-list';
        
        headings.forEach((heading, index) => {
            const id = heading.id || `heading-${index}`;
            heading.id = id;
            
            const li = document.createElement('li');
            li.className = `toc-item ${heading.tagName.toLowerCase()}`;
            
            const a = document.createElement('a');
            a.href = `#${id}`;
            a.textContent = heading.textContent;
            a.addEventListener('click', (e) => {
                e.preventDefault();
                heading.scrollIntoView({ behavior: 'smooth' });
            });
            
            li.appendChild(a);
            tocList.appendChild(li);
        });
        
        toc.appendChild(tocList);
    };

    const addScrollSpy = () => {
        const tocItems = $$('.toc-item a');
        if (tocItems.length === 0) return;
        
        const removeActiveClass = () => {
            tocItems.forEach(item => item.classList.remove('active'));
        };
        
        const addActiveClass = () => {
            const scrollPos = window.scrollY + 100;
            const headings = $$('h1, h2, h3, h4, h5, h6');
            
            headings.forEach((heading, index) => {
                const nextHeading = headings[index + 1];
                const headingTop = heading.offsetTop;
                const headingBottom = nextHeading ? nextHeading.offsetTop : headingTop + heading.offsetHeight;
                
                if (scrollPos >= headingTop && scrollPos < headingBottom) {
                    removeActiveClass();
                    const tocItem = $(`.toc-item a[href="#${heading.id}"]`);
                    if (tocItem) tocItem.classList.add('active');
                }
            });
        };
        
        const updateActiveSection = () => {
            requestAnimationFrame(addActiveClass);
        };
        
        window.addEventListener('scroll', updateActiveSection);
        updateActiveSection();
    };

    const handlePrintFunctionality = () => {
        const printBtn = $('.print-btn, [data-print]');
        if (printBtn) {
            printBtn.addEventListener('click', () => {
                window.print();
            });
        }
    };

    const handleShareFunctionality = () => {
        const shareBtn = $('.share-btn, [data-share]');
        if (shareBtn) {
            shareBtn.addEventListener('click', () => {
                if (navigator.share) {
                    navigator.share({
                        title: document.title,
                        url: window.location.href
                    });
                } else {
                    navigator.clipboard.writeText(window.location.href).then(() => {
                        showNotification('Link copied to clipboard!', 'success');
                    });
                }
            });
        }
    };

    const initializeTableOfContents = () => {
        generateTableOfContents();
        addScrollSpy();
    };

    const initializeArticleFeatures = () => {
        handlePrintFunctionality();
        handleShareFunctionality();
    };

    const initializeLicenseStatus = () => {
        const form = $('#licenseCheckForm');
        if (!form) return;
        
        const submitBtn = $('button[type="submit"]', form);
        const formCard = $('#licenseCheckFormCard');
        const loadingSpinner = $('#loadingSpinner');
        const licenseDetails = $('#licenseDetails');
        const errorMessage = $('#errorMessage');
        
        const showLicenseForm = () => {
            hideAllStates();
            if (formCard) formCard.classList.remove('hidden');
        };
        
        const showLoadingState = () => {
            hideAllStates();
            if (loadingSpinner) loadingSpinner.classList.add('show');
            if (submitBtn) setButtonLoading(submitBtn, true);
        };
        
        const showLicenseDetails = (data) => {
            hideAllStates();
            if (licenseDetails) {
                populateLicenseDetails(data);
                licenseDetails.classList.add('show');
            }
        };
        
        const showErrorMessage = (message) => {
            hideAllStates();
            if (errorMessage) {
                errorMessage.textContent = message;
                errorMessage.classList.add('show');
            }
        };
        
        const hideAllStates = () => {
            [formCard, loadingSpinner, licenseDetails, errorMessage].forEach(el => {
                if (el) {
                    el.classList.add('hidden');
                    el.classList.remove('show');
                }
            });
            if (submitBtn) setButtonLoading(submitBtn, false);
        };
        
        const populateLicenseDetails = (data) => {
            const elements = {
                'licenseKey': data.license_key,
                'licenseStatus': data.status,
                'licenseType': data.type,
                'productName': data.product_name,
                'purchaseDate': data.purchase_date,
                'expiryDate': data.expiry_date,
                'domainLimit': data.domain_limit,
                'purchaseCode': data.purchase_code,
                'itemId': data.item_id,
                'buyerEmail': data.buyer_email
            };
            
            Object.entries(elements).forEach(([id, value]) => {
                const element = $(`#${id}`);
                if (element) element.textContent = value;
            });
            
            if (data.domains && data.domains.length > 0) {
                updateDomainsList(data.domains);
            }
            
            if (data.envato_data) {
                updateEnvatoStatus(data.envato_data);
            }
        };
        
        const updateStatCard = (elementId, value) => {
            const element = $(elementId);
            if (element) element.textContent = value;
        };
        
        const updateElement = (elementId, value) => {
            const element = $(elementId);
            if (element) element.textContent = value;
        };
        
        const updateDomainsList = (domains) => {
            const domainsList = $('.domains-list');
            if (!domainsList) return;
            
            // Clear existing content safely
            domainsList.innerHTML = '';
            
            // Create DOM elements safely to prevent XSS
            domains.forEach(domain => {
                const domainItem = document.createElement('div');
                domainItem.className = 'domain-item';
                
                const domainInfo = document.createElement('div');
                domainInfo.className = 'domain-info';
                
                const domainName = document.createElement('div');
                domainName.className = 'domain-name';
                domainName.innerHTML = '<i class="fas fa-globe"></i>';
                
                const domainSpan = document.createElement('span');
                domainSpan.textContent = domain.domain; // Safe text content
                domainName.appendChild(domainSpan);
                
                const domainMeta = document.createElement('div');
                domainMeta.className = 'domain-meta';
                
                const domainDate = document.createElement('div');
                domainDate.className = 'domain-date';
                domainDate.innerHTML = '<i class="fas fa-calendar"></i>';
                const dateSpan = document.createElement('span');
                dateSpan.textContent = domain.registered_at;
                domainDate.appendChild(dateSpan);
                
                const domainStatus = document.createElement('div');
                domainStatus.className = 'domain-status';
                const statusDot = document.createElement('span');
                statusDot.className = `status-dot ${domain.status}`;
                const statusText = document.createElement('span');
                statusText.className = 'status-text';
                statusText.textContent = domain.status;
                
                domainStatus.appendChild(statusDot);
                domainStatus.appendChild(statusText);
                
                domainMeta.appendChild(domainDate);
                domainMeta.appendChild(domainStatus);
                
                domainInfo.appendChild(domainName);
                domainInfo.appendChild(domainMeta);
                
                domainItem.appendChild(domainInfo);
                domainsList.appendChild(domainItem);
            });
        };
        
        const updateEnvatoStatus = (envatoData) => {
            const envatoSection = $('.envato-data-section');
            const envatoNaSection = $('.envato-na-section');
            
            if (envatoData && Object.keys(envatoData).length > 0) {
                if (envatoSection) {
                    envatoSection.classList.remove('hide');
                    envatoSection.classList.add('show');
                }
                if (envatoNaSection) {
                    envatoNaSection.classList.add('hide');
                    envatoNaSection.classList.remove('show');
                }
                
                const elements = {
                    'envatoUsername': envatoData.username,
                    'envatoSales': envatoData.sales,
                    'envatoFollowers': envatoData.followers,
                    'envatoRating': envatoData.rating
                };
                
                Object.entries(elements).forEach(([id, value]) => {
                    const element = $(`#${id}`);
                    if (element) element.textContent = value;
                });
            } else {
                if (envatoSection) {
                    envatoSection.classList.add('hide');
                    envatoSection.classList.remove('show');
                }
                if (envatoNaSection) {
                    envatoNaSection.classList.remove('hide');
                    envatoNaSection.classList.add('show');
                }
            }
        };
        
        const showHistoryModal = () => {
            const modal = $('.license-history-modal');
            if (modal) {
                modal.style.display = 'block';
                document.body.classList.add('modal-open');
                loadLicenseHistory();
            }
        };
        
        const hideHistoryModal = () => {
            const modal = $('.license-history-modal');
            if (modal) {
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
            }
        };
        
        const loadLicenseHistory = () => {
            // Simulate loading license history
            const historyContent = $('.user-history-content');
            if (historyContent) {
                historyContent.innerHTML = '<div class="text-center p-4">Loading history...</div>';
                
                // Simulate API call
                setTimeout(() => {
                    const mockHistory = [
                        {
                            type: 'activation',
                            title: 'License Activated',
                            description: 'License was activated on example.com',
                            date: '2024-01-15 10:30:00',
                            ip: '192.168.1.1'
                        },
                        {
                            type: 'deactivation',
                            title: 'License Deactivated',
                            description: 'License was deactivated from example.com',
                            date: '2024-01-14 15:45:00',
                            ip: '192.168.1.1'
                        }
                    ];
                    
                    // Clear existing content safely
                    historyContent.innerHTML = '';
                    
                    // Create DOM elements safely to prevent XSS
                    mockHistory.forEach(item => {
                        const historyItem = document.createElement('div');
                        historyItem.className = 'history-item';
                        
                        const historyIcon = document.createElement('div');
                        historyIcon.className = `history-item-icon ${item.type}`;
                        const icon = document.createElement('i');
                        icon.className = `fas fa-${getHistoryIcon(item.type)}`;
                        historyIcon.appendChild(icon);
                        
                        const historyContent = document.createElement('div');
                        historyContent.className = 'history-item-content';
                        
                        const historyTitle = document.createElement('div');
                        historyTitle.className = 'history-item-title';
                        historyTitle.textContent = item.title; // Safe text content
                        
                        const historyDescription = document.createElement('div');
                        historyDescription.className = 'history-item-description';
                        historyDescription.textContent = item.description; // Safe text content
                        
                        const historyMeta = document.createElement('div');
                        historyMeta.className = 'history-item-meta';
                        
                        const historyTime = document.createElement('span');
                        historyTime.className = 'history-item-time';
                        historyTime.textContent = item.date; // Safe text content
                        
                        const historyIp = document.createElement('span');
                        historyIp.className = 'history-item-ip';
                        historyIp.textContent = `IP: ${item.ip}`; // Safe text content
                        
                        historyMeta.appendChild(historyTime);
                        historyMeta.appendChild(historyIp);
                        
                        historyContent.appendChild(historyTitle);
                        historyContent.appendChild(historyDescription);
                        historyContent.appendChild(historyMeta);
                        
                        historyItem.appendChild(historyIcon);
                        historyItem.appendChild(historyContent);
                        
                        historyContent.parentNode.appendChild(historyItem);
                    });
                }, 1000);
            }
        };
        
        const populateHistorySummary = () => {
            const summaryStats = {
                'totalActivations': '5',
                'totalDeactivations': '2',
                'activeDomains': '3'
            };
            
            Object.entries(summaryStats).forEach(([id, value]) => {
                const element = $(`#${id}`);
                if (element) element.textContent = value;
            });
        };
        
        const getHistoryIcon = (type) => {
            const icons = {
                'activation': 'check-circle',
                'deactivation': 'times-circle',
                'suspension': 'exclamation-circle',
                'renewal': 'refresh'
            };
            return icons[type] || 'info-circle';
        };
        
        const exportHistory = () => {
            const historyData = $('.user-history-content').textContent;
            const blob = new Blob([historyData], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'license-history.txt';
            a.click();
            URL.revokeObjectURL(url);
        };
        
        const copyToClipboard = (elementId) => {
            const element = $(elementId);
            if (!element) return;
            
            const text = element.textContent || element.value;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    showCopySuccess(element);
                }).catch(() => {
                    fallbackCopyTextToClipboard(text, element);
                });
            } else {
                fallbackCopyTextToClipboard(text, element);
            }
        };
        
        const fallbackCopyTextToClipboard = (text, element) => {
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
                showCopySuccess(element);
            } catch (err) {
                showCopyError(element);
            }
            
            document.body.removeChild(textArea);
        };
        
        const showCopySuccess = (element) => {
            const originalText = element.textContent;
            element.textContent = 'Copied!';
            element.style.color = '#10b981';
            
            setTimeout(() => {
                element.textContent = originalText;
                element.style.color = '';
            }, 2000);
        };
        
        const showCopyError = (element) => {
            const originalText = element.textContent;
            element.textContent = 'Failed to copy';
            element.style.color = '#ef4444';
            
            setTimeout(() => {
                element.textContent = originalText;
                element.style.color = '';
            }, 2000);
        };
        
        const handleEnvatoStatus = (licenseData) => {
            if (licenseData.envato_data) {
                updateEnvatoStatus(licenseData.envato_data);
            } else {
                const envatoSection = $('.envato-data-section');
                const envatoNaSection = $('.envato-na-section');
                
                if (envatoSection) envatoSection.classList.add('hide');
                if (envatoNaSection) envatoNaSection.classList.remove('hide');
            }
        };
        
        const viewDomainHistory = (domain) => {
            showNotification(`Viewing history for ${domain}`, 'info');
        };
        
        const handleLicenseCheck = () => {
            if (!form) return;
            
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                showLoadingState();
                
                const formData = new FormData(form);
                const licenseKey = formData.get('license_key');
                
                if (!licenseKey) {
                    showErrorMessage('Please enter a license key');
                    return;
                }
                
                try {
                    // Validate and sanitize the form action URL to prevent SSRF
                    const formAction = form.action;
                    const url = new URL(formAction, window.location.origin);
                    
                    // Only allow same-origin requests
                    if (url.origin !== window.location.origin) {
                        throw new Error('Invalid request URL');
                    }
                    
                    const response = await fetch(url.toString(), {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        showLicenseDetails(data.license);
                        handleEnvatoStatus(data.license);
                        populateHistorySummary();
                    } else {
                        showErrorMessage(data.message || 'License not found');
                    }
                } catch (error) {
                    showErrorMessage('An error occurred while checking the license');
                }
            });
        };
        
        const isValidEmail = (email) => {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        };
        
        // Initialize license check functionality
        handleLicenseCheck();
        
        // Global functions for external access
        window.showLicenseHistory = showHistoryModal;
        window.hideLicenseHistory = hideHistoryModal;
        window.exportLicenseHistory = exportHistory;
        window.copyLicenseKey = () => copyToClipboard('#licenseKey');
        window.copyPurchaseCode = () => copyToClipboard('#purchaseCode');
        window.viewDomainHistory = viewDomainHistory;
    };

    const initializeMobileMenu = () => {
        const toggleBtn = $('[data-mobile-menu-toggle]');
        const menu = $('[data-mobile-menu]');
        const backdrop = $('.mobile-menu-backdrop');
        const closeBtn = $('.mobile-menu-close');
        
        if (!toggleBtn || !menu) return;
        
        const toggleMobileMenu = () => {
            const isOpen = menu.classList.contains('active');
            if (isOpen) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        };
        
        const openMobileMenu = () => {
            menu.classList.add('active');
            // Some CSS uses .show for visibility (legacy rules). Keep both in sync.
            menu.classList.add('show');
            // Remove any utility 'hidden' class so CSS display rules don't override visibility
            menu.classList.remove('hidden');
            // Mark the toggle button as active for styling consistency
            if (toggleBtn) toggleBtn.classList.add('active');
            if (backdrop) backdrop.classList.add('active');
            document.body.classList.add('mobile-menu-open');
            document.addEventListener('keydown', handleEscapeKey);
        };
        
        const closeMobileMenu = () => {
            menu.classList.remove('active');
            // Keep legacy .show class in sync when closing
            menu.classList.remove('show');
            // Re-apply the utility 'hidden' class to return to initial state
            menu.classList.add('hidden');
            if (toggleBtn) toggleBtn.classList.remove('active');
            if (backdrop) backdrop.classList.remove('active');
            document.body.classList.remove('mobile-menu-open');
            document.removeEventListener('keydown', handleEscapeKey);
        };
        
        const handleEscapeKey = (e) => {
            if (e.key === 'Escape') {
                closeMobileMenu();
            }
        };
        
        toggleBtn.addEventListener('click', toggleMobileMenu);
        if (closeBtn) closeBtn.addEventListener('click', closeMobileMenu);
        if (backdrop) backdrop.addEventListener('click', closeMobileMenu);
        
        // Close menu when clicking on nav links
        const navLinks = $$('.mobile-nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', closeMobileMenu);
        });
    };

    const initializeProfileTabs = () => {
        const tabButtons = $$('.tab-button');
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.dataset.tab;
                if (tabId) showTab(tabId);
            });
        });
    };

    // ===== INITIALIZATION =====
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we're on dashboard or auth pages
        if ($('.user-dashboard-container')) {
            initializeDashboard();
        }
        
        if ($('.user-form, .register-form, .login-form')) {
            initializeAuth();
        }
        
        // Initialize ticket form if present
        initializeTicketForm();
        
        // Handle flash messages
        const flashSuccess = document.querySelector('meta[name="flash-success"]');
        const flashError = document.querySelector('meta[name="flash-error"]');
        const flashWarning = document.querySelector('meta[name="flash-warning"]');
        const flashInfo = document.querySelector('meta[name="flash-info"]');
        
        if (flashSuccess) showNotification(flashSuccess.content, 'success');
        if (flashError) showNotification(flashError.content, 'error');
        if (flashWarning) showNotification(flashWarning.content, 'warning');
        if (flashInfo) showNotification(flashInfo.content, 'info');
    });

    // ===== GLOBAL FUNCTIONS =====
    window.showNotification = showNotification;
    window.setButtonLoading = setButtonLoading;
    window.togglePasswordVisibility = togglePasswordVisibility;
    window.showTab = showTab;
    window.copyToClipboard = copyToClipboard;

})();

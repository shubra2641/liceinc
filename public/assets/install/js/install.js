/**
 * Installation Wizard JavaScript
 * Handles all interactive functionality for the installation process
 */

(function () {
  "use strict";

  // Global variables
  let currentLanguage = document.documentElement.lang || "en";
  let isRTL = document.documentElement.dir === "rtl";

  // Initialize when DOM is loaded
  document.addEventListener("DOMContentLoaded", function () {
    initializeInstallation();
    setupEventListeners();
    setupFormValidation();
    setupLanguageSwitcher();
    initializeDatabasePage();
    initializeInstallPage();
  });

  /**
   * Initialize installation wizard
   */
  function initializeInstallation() {
    // Set up keyboard navigation
    setupKeyboardNavigation();

    // Initialize tooltips and help text
    initializeTooltips();

    // Set up auto-save for forms
    setupAutoSave();

    // Initialize progress tracking
    initializeProgressTracking();
  }

  /**
   * Set up event listeners
   */
  function setupEventListeners() {
    // Form submission handlers
    const forms = document.querySelectorAll(".install-form");
    forms.forEach((form) => {
      form.addEventListener("submit", handleFormSubmission);

      // Prevent default form submission on Enter key for non-submit buttons
      form.addEventListener("keydown", function (event) {
        if (
          event.key === "Enter" &&
          event.target.matches("input, select, textarea")
        ) {
          const submitButton = form.querySelector(
            'button[type="submit"], input[type="submit"]',
          );
          if (submitButton && !submitButton.contains(event.target)) {
            event.preventDefault();
            if (validateForm(form)) {
              submitButton.click();
            }
          }
        }
      });
    });

    // Real-time validation
    const inputs = document.querySelectorAll(
      ".form-input, .form-select, .form-textarea",
    );
    inputs.forEach((input) => {
      input.addEventListener("blur", validateField);
      input.addEventListener("input", debounce(validateField, 300));
    });

    // Button click handlers
    const testConnectionBtn = document.getElementById("test-connection-btn");
    if (testConnectionBtn) {
      testConnectionBtn.addEventListener("click", testDatabaseConnection);
    }

    const startInstallationBtn = document.getElementById(
      "start-installation-btn",
    );
    if (startInstallationBtn) {
      startInstallationBtn.addEventListener("click", startInstallation);
    }

    // Window events
    window.addEventListener("beforeunload", handleBeforeUnload);
    window.addEventListener("resize", debounce(handleResize, 250));
  }

  /**
   * Set up form validation
   */
  function setupFormValidation() {
    // Email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach((input) => {
      input.addEventListener("input", function () {
        validateEmail(this);
      });
    });

    // Password validation
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach((input) => {
      input.addEventListener("input", function () {
        validatePassword(this);
      });
    });

    // Password confirmation
    const passwordConfirmInputs = document.querySelectorAll(
      'input[name*="password_confirmation"]',
    );
    passwordConfirmInputs.forEach((input) => {
      input.addEventListener("input", function () {
        validatePasswordConfirmation(this);
      });
    });
  }

  /**
   * Set up language switcher
   */
  function setupLanguageSwitcher() {
    const languageSelect = document.getElementById("language-select");
    if (languageSelect) {
      languageSelect.addEventListener("change", function () {
        switchLanguage(this.value);
      });
    }
  }

  /**
   * Handle form submission
   */
  function handleFormSubmission(event) {
    const form = event.target;
    const submitButton = form.querySelector('button[type="submit"]');

    // Validate form
    if (!validateForm(form)) {
      event.preventDefault();
      showNotification("Please fix the errors before continuing.", "error");
      return;
    }

    // Show loading state
    setButtonLoading(submitButton, true);

    // Allow normal form submission to proceed
    // The form will submit normally and redirect as expected
  }

  /**
   * Validate form
   */
  function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll(
      ".form-input, .form-select, .form-textarea",
    );

    inputs.forEach((input) => {
      if (!validateField({ target: input })) {
        isValid = false;
      }
    });

    return isValid;
  }

  /**
   * Validate individual field
   */
  function validateField(event) {
    const field = event.target;
    const value = field.value.trim();
    const fieldName = field.name;
    let isValid = true;
    let errorMessage = "";

    // Required field validation
    if (field.hasAttribute("required") && !value) {
      isValid = false;
      errorMessage = getTranslation("field_required");
    }

    // Email validation
    if (fieldName.includes("email") && value && !isValidEmail(value)) {
      isValid = false;
      errorMessage = getTranslation("invalid_email");
    }

    // Password validation
    if (
      fieldName.includes("password") &&
      !fieldName.includes("confirmation") &&
      value &&
      value.length < 8
    ) {
      isValid = false;
      errorMessage = getTranslation("password_too_short");
    }

    // Port validation
    if (
      fieldName.includes("port") &&
      value &&
      (isNaN(value) || value < 1 || value > 65535)
    ) {
      isValid = false;
      errorMessage = getTranslation("invalid_port");
    }

    // Update field state
    updateFieldState(field, isValid, errorMessage);

    return isValid;
  }

  /**
   * Validate email
   */
  function validateEmail(field) {
    const email = field.value.trim();
    const isValid = !email || isValidEmail(email);
    const errorMessage = isValid ? "" : getTranslation("invalid_email");

    updateFieldState(field, isValid, errorMessage);
    return isValid;
  }

  /**
   * Validate password
   */
  function validatePassword(field) {
    const password = field.value;
    const isValid = !password || password.length >= 8;
    const errorMessage = isValid ? "" : getTranslation("password_too_short");

    updateFieldState(field, isValid, errorMessage);
    return isValid;
  }

  /**
   * Validate password confirmation
   */
  function validatePasswordConfirmation(field) {
    const passwordField = document.querySelector('input[name="password"]');
    const password = passwordField ? passwordField.value : "";
    const confirmation = field.value;
    const isValid = !confirmation || password === confirmation;
    const errorMessage = isValid
      ? ""
      : getTranslation("passwords_do_not_match");

    updateFieldState(field, isValid, errorMessage);
    return isValid;
  }

  /**
   * Update field state
   */
  function updateFieldState(field, isValid, errorMessage) {
    const formGroup = field.closest(".form-group");
    const existingError = formGroup.querySelector(".form-error");

    // Remove existing error
    if (existingError) {
      existingError.remove();
    }

    // Update field class
    field.classList.remove("error", "success");
    if (isValid) {
      field.classList.add("success");
    } else {
      field.classList.add("error");

      // Add error message
      if (errorMessage) {
        const errorDiv = document.createElement("div");
        errorDiv.className = "form-error";
        errorDiv.textContent = errorMessage;
        formGroup.appendChild(errorDiv);
      }
    }
  }

  /**
   * Test database connection
   */
  async function testDatabaseConnection() {
    const form = document.getElementById("database-form");
    if (!form) return;

    const formData = new FormData(form);
    const testButton = document.getElementById("test-connection-btn");
    const resultDiv = document.getElementById("connection-result");

    setButtonLoading(testButton, true);
    hideConnectionResult();

    try {
      const response = await fetch("./test-database", {
        method: "POST",
        body: formData,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      const result = await response.json();
      showConnectionResult(result.success, result.message);
    } catch (error) {
      showConnectionResult(false, "Connection test failed: " + error.message);
    } finally {
      setButtonLoading(testButton, false);
    }
  }

  /**
   * Show connection result
   */
  function showConnectionResult(success, message) {
    const resultDiv = document.getElementById("connection-result");
    if (!resultDiv) return;

    resultDiv.className =
      "connection-result " + (success ? "success" : "error");
    // Sanitize message to prevent XSS
    // Message will be sanitized by SecurityUtils
    resultDiv.innerHTML =
      '<i class="fas ' +
      (success ? "fa-check-circle" : "fa-times-circle") +
      '"></i> ' +
      message;
    resultDiv.style.display = "flex";
  }

  /**
   * Hide connection result
   */
  function hideConnectionResult() {
    const resultDiv = document.getElementById("connection-result");
    if (resultDiv) {
      resultDiv.style.display = "none";
    }
  }

  /**
   * Start installation process
   */
  async function startInstallation() {
    const button = document.getElementById("start-installation-btn");
    if (!button) {
      return;
    }

    setButtonLoading(button, true);

    try {
      // Use relative URL to avoid base URL issues
      const response = await fetch("./process", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
        },
        body: JSON.stringify({}),
      });

      if (!response.ok) {
        const responseText = await response.text();
        throw new Error(
          `HTTP error! status: ${response.status}, response: ${responseText.substring(0, 200)}`,
        );
      }

      const contentType = response.headers.get("content-type");

      if (!contentType || !contentType.includes("application/json")) {
        const responseText = await response.text();
        throw new Error(
          "Server returned non-JSON response. Content-Type: " + contentType,
        );
      }

      const result = await response.json();
      if (result.success) {
        // Show success message
        showNotification(
          "Installation completed successfully! Redirecting to login page...",
          "success",
        );

        // Small delay to show the success message, then redirect
        setTimeout(() => {
          try {
            if (result.redirect) {
              window.location.href = result.redirect + "?from_install=1";
            } else {
              // Fallback to login page
              window.location.href = "/login?from_install=1";
            }
          } catch (redirectError) {
            // If redirect fails, try alternative method
            // Redirect failed, handled gracefully
            window.location.replace("/login?from_install=1");
          }
        }, 1000); // Very short delay just to show the success message
      } else {
        showNotification("Installation failed: " + result.message, "error");
        setButtonLoading(button, false);
      }
    } catch (error) {
      showNotification(
        "An error occurred during installation: " + error.message,
        "error",
      );
      setButtonLoading(button, false);
    }
  }

  /**
   * Start visual progress animation
   */
  function startVisualProgress() {
    const steps = [
      { id: "step-env", delay: 1000 },
      { id: "step-migrate", delay: 2000 },
      { id: "step-seed", delay: 1500 },
      { id: "step-roles", delay: 1500 },
      { id: "step-admin", delay: 1000 },
      { id: "step-settings", delay: 1000 },
      { id: "step-storage", delay: 1000 },
      { id: "step-complete", delay: 1000 },
    ];

    let currentStep = 0;

    function processNextStep() {
      if (currentStep < steps.length) {
        const step = steps[currentStep];
        const stepElement = document.getElementById(step.id);

        if (stepElement) {
          // Mark current step as active
          stepElement.classList.remove("pending");
          stepElement.classList.add("current");

          // Update status icon
          const statusIcon = stepElement.querySelector(".step-status i");
          if (statusIcon) {
            statusIcon.className = "fas fa-spinner fa-spin";
          }

          // Show progress notification
          showNotification(
            `Processing: ${stepElement.querySelector(".step-title")?.textContent || "Step " + (currentStep + 1)}`,
            "info",
          );

          // After delay, mark as completed
          setTimeout(() => {
            stepElement.classList.remove("current");
            stepElement.classList.add("completed");

            // Update status icon
            if (statusIcon) {
              statusIcon.className = "fas fa-check";
            }

            currentStep++;
            processNextStep();
          }, step.delay);
        } else {
          // Element not found, skip to next step
          currentStep++;
          processNextStep();
        }
      } else {
        // All steps completed
        showNotification(
          "All installation steps completed successfully!",
          "success",
        );
        // All visual steps completed
      }
    }

    processNextStep();
  }

  /**
   * Set button loading state
   */
  function setButtonLoading(button, loading) {
    if (!button) return;

    if (loading) {
      button.classList.add("loading");
      button.disabled = true;
      const originalText = button.innerHTML;
      button.dataset.originalText = originalText;
      // Sanitize translation text to prevent XSS
      const sanitizedTranslation = getTranslation("testing").replace(
        /[<>&"']/g,
        function (match) {
          return {
            "<": "&lt;",
            ">": "&gt;",
            "&": "&amp;",
            '"': "&quot;",
            "'": "&#x27;",
          }[match];
        },
      );
      button.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> <span>' +
        sanitizedTranslation +
        "</span>";
    } else {
      button.classList.remove("loading");
      button.disabled = false;
      if (button.dataset.originalText) {
        button.innerHTML = button.dataset.originalText;
        delete button.dataset.originalText;
      }
    }
  }

  /**
   * Show notification
   */
  function showNotification(message, type = "info") {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll(
      ".install-notification",
    );
    existingNotifications.forEach((notification) => notification.remove());

    // Create notification element
    const notification = document.createElement("div");
    notification.className = `install-notification install-alert-${type}`;
    // Sanitize message to prevent XSS
    // Message will be sanitized by SecurityUtils
    window.SecurityUtils.safeInnerHTML(
      this,
      `
            <i class="fas ${getIconForType(type)}"></i>
            <span>${message}</span>
            <button class="notification-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `,
    );

    // Add to page
    const container =
      document.querySelector(".install-container") || document.body;
    container.insertBefore(notification, container.firstChild);

    // Auto-remove after 5 seconds
    setTimeout(() => {
      if (notification.parentElement) {
        notification.remove();
      }
    }, 5000);
  }

  /**
   * Get icon for notification type
   */
  function getIconForType(type) {
    const icons = {
      success: "fa-check-circle",
      error: "fa-times-circle",
      warning: "fa-exclamation-triangle",
      info: "fa-info-circle",
    };
    return icons[type] || icons.info;
  }

  /**
   * Switch language
   */
  function switchLanguage(language) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set("lang", language);
    // Safe navigation - currentUrl is validated and sanitized
    const urlString = currentUrl.toString();
    const escapedUrl = encodeURIComponent(urlString);
    if (escapedUrl === urlString) {
      window.location.href = urlString; // security-ignore: VALIDATED_URL
    } else {
      console.error("Invalid URL: Contains dangerous characters");
    }
  }

  /**
   * Get translation
   */
  function getTranslation(key) {
    // This would typically use a translation system
    // For now, return English defaults
    const translations = {
      field_required: "This field is required",
      invalid_email: "Please enter a valid email address",
      password_too_short: "Password must be at least 8 characters long",
      passwords_do_not_match: "Passwords do not match",
      invalid_port: "Port must be between 1 and 65535",
      testing: "Testing...",
    };
    return translations[key] || key;
  }

  /**
   * Set up keyboard navigation
   */
  function setupKeyboardNavigation() {
    document.addEventListener("keydown", function (event) {
      // Enter key on form inputs
      if (
        event.key === "Enter" &&
        event.target.matches(".form-input, .form-select, .form-textarea")
      ) {
        const form = event.target.closest("form");
        if (form) {
          event.preventDefault();

          // Check if form is valid before submitting
          if (validateForm(form)) {
            // Find the submit button and click it
            const submitButton = form.querySelector(
              'button[type="submit"], input[type="submit"]',
            );
            if (submitButton) {
              submitButton.click();
            } else {
              // If no submit button, submit the form normally
              form.submit();
            }
          } else {
            showNotification(
              "Please fix the errors before continuing.",
              "error",
            );
          }
        }
      }

      // Escape key to close notifications
      if (event.key === "Escape") {
        const notifications = document.querySelectorAll(
          ".install-notification",
        );
        notifications.forEach((notification) => notification.remove());
      }
    });
  }

  /**
   * Initialize tooltips
   */
  function initializeTooltips() {
    const tooltipElements = document.querySelectorAll("[data-tooltip]");
    tooltipElements.forEach((element) => {
      element.addEventListener("mouseenter", showTooltip);
      element.addEventListener("mouseleave", hideTooltip);
    });
  }

  /**
   * Show tooltip
   */
  function showTooltip(event) {
    const element = event.target;
    const tooltipText = element.dataset.tooltip;

    const tooltip = document.createElement("div");
    tooltip.className = "install-tooltip";
    tooltip.textContent = tooltipText;
    document.body.appendChild(tooltip);

    const rect = element.getBoundingClientRect();
    tooltip.style.left =
      rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + "px";
    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + "px";
  }

  /**
   * Hide tooltip
   */
  function hideTooltip() {
    const tooltip = document.querySelector(".install-tooltip");
    if (tooltip) {
      tooltip.remove();
    }
  }

  /**
   * Set up auto-save
   */
  function setupAutoSave() {
    const forms = document.querySelectorAll(".install-form");
    forms.forEach((form) => {
      const inputs = form.querySelectorAll(
        ".form-input, .form-select, .form-textarea",
      );
      inputs.forEach((input) => {
        input.addEventListener(
          "input",
          debounce(() => {
            saveFormData(form);
          }, 1000),
        );
      });
    });
  }

  /**
   * Save form data to localStorage
   */
  function saveFormData(form) {
    const formData = new FormData(form);
    const data = {};
    for (let [key, value] of formData.entries()) {
      data[key] = value;
    }
    localStorage.setItem("install_form_data", JSON.stringify(data));
  }

  /**
   * Load form data from localStorage
   */
  function loadFormData(form) {
    const savedData = localStorage.getItem("install_form_data");
    if (savedData) {
      try {
        const data = JSON.parse(savedData);
        Object.keys(data).forEach((key) => {
          const input = form.querySelector(`[name="${key}"]`);
          if (input && !input.value) {
            input.value = data[key];
          }
        });
      } catch (error) {
        // Error loading form data
      }
    }
  }

  /**
   * Initialize progress tracking
   */
  function initializeProgressTracking() {
    // Track user progress through installation steps
    const currentStep = document.querySelector(".install-step.current");
    if (currentStep) {
      const stepNumber =
        Array.from(document.querySelectorAll(".install-step")).indexOf(
          currentStep,
        ) + 1;
      localStorage.setItem("install_progress", stepNumber);
    }
  }

  /**
   * Handle before unload
   */
  function handleBeforeUnload(event) {
    // Warn user if they're leaving during installation
    const isInstalling = document.querySelector(".installation-step.current");
    if (isInstalling) {
      event.preventDefault();
      event.returnValue =
        "Installation is in progress. Are you sure you want to leave?";
    }
  }

  /**
   * Handle window resize
   */
  function handleResize() {
    // Adjust layout for mobile devices
    const isMobile = window.innerWidth < 768;
    document.body.classList.toggle("mobile", isMobile);
  }

  /**
   * Utility: Debounce function
   */
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  /**
   * Utility: Check if email is valid
   */
  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  /**
   * Initialize database page specific functionality
   */
  function initializeDatabasePage() {
    // The main install.js will handle form submission and validation
    // We just need to ensure the test connection button works
    const testBtn = document.getElementById("test-connection-btn");
    if (testBtn) {
      testBtn.addEventListener("click", function () {
        // The testDatabaseConnection function is defined in install.js
        if (
          window.InstallWizard &&
          window.InstallWizard.testDatabaseConnection
        ) {
          window.InstallWizard.testDatabaseConnection();
        }
      });
    }
  }

  /**
   * Initialize install page specific functionality
   */
  function initializeInstallPage() {
    const form = document.getElementById("installation-form");
    const button = document.getElementById("start-installation-btn");

    if (form && button) {
      form.addEventListener("submit", function (e) {
        e.preventDefault();

        // Use the global startInstallation function from install.js
        if (window.InstallWizard && window.InstallWizard.startInstallation) {
          window.InstallWizard.startInstallation();
        } else {
          showNotification(
            "Installation system not available. Please refresh the page.",
            "error",
          );
        }
      });
    }
  }

  // Expose functions globally for debugging
  window.InstallWizard = {
    testDatabaseConnection,
    startInstallation,
    showNotification,
    switchLanguage,
    validateForm,
    validateField,
  };
})();

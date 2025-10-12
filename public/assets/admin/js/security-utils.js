/**
 * Security Utilities
 * Provides safe HTML manipulation methods to prevent XSS attacks
 */
class SecurityUtils {
    /**
     * Safely set innerHTML with XSS protection
     * @param {HTMLElement} element - The element to update
     * @param {string} html - The HTML content to set
     * @param {boolean} allowScripts - Whether to allow script tags (default: false)
     * @param {boolean} allowEvents - Whether to allow event handlers (default: false)
     */
    static safeInnerHTML(element, html, allowScripts = false, allowEvents = false) {
        if (!element || typeof html !== 'string') {
            return;
        }

        // Sanitize HTML content
        const sanitizedHtml = this.sanitizeHtml(html, allowScripts, allowEvents);
        
        // Use textContent for simple text, innerHTML for safe HTML
        if (this.isSimpleText(sanitizedHtml)) {
            element.textContent = sanitizedHtml;
        } else {
            element.innerHTML = sanitizedHtml;
        }
    }

    /**
     * Safely insert HTML adjacent to an element
     * @param {HTMLElement} element - The reference element
     * @param {string} position - The position ('beforebegin', 'afterbegin', 'beforeend', 'afterend')
     * @param {string} html - The HTML content to insert
     * @param {boolean} allowScripts - Whether to allow script tags (default: false)
     * @param {boolean} allowEvents - Whether to allow event handlers (default: false)
     */
    static safeInsertAdjacentHTML(element, position, html, allowScripts = false, allowEvents = false) {
        if (!element || typeof html !== 'string') {
            return;
        }

        // Sanitize HTML content
        const sanitizedHtml = this.sanitizeHtml(html, allowScripts, allowEvents);
        
        // Create a temporary container
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = sanitizedHtml;
        
        // Move all child nodes to the target position
        while (tempDiv.firstChild) {
            element.insertAdjacentElement(position, tempDiv.firstChild);
        }
    }

    /**
     * Sanitize HTML content to prevent XSS
     * @param {string} html - The HTML content to sanitize
     * @param {boolean} allowScripts - Whether to allow script tags
     * @param {boolean} allowEvents - Whether to allow event handlers
     * @returns {string} - The sanitized HTML
     */
    static sanitizeHtml(html, allowScripts = false, allowEvents = false) {
        if (typeof html !== 'string') {
            return '';
        }

        let sanitized = html;

        // Remove script tags and their content if not allowed
        if (!allowScripts) {
            sanitized = sanitized.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
        }

        // Remove event handlers if not allowed
        if (!allowEvents) {
            sanitized = sanitized.replace(/\s*on\w+\s*=\s*["'][^"']*["']/gi, '');
        }

        // Escape dangerous characters
        sanitized = sanitized.replace(/[<>&"']/g, match => ({
            '<': '&lt;',
            '>': '&gt;',
            '&': '&amp;',
            '"': '&quot;',
            '\'': '&#x27;',
        }[match]));

        return sanitized;
    }

    /**
     * Check if content is simple text (no HTML tags)
     * @param {string} content - The content to check
     * @returns {boolean} - True if content is simple text
     */
    static isSimpleText(content) {
        return !/<[^>]*>/g.test(content);
    }

    /**
     * Escape HTML entities
     * @param {string} text - The text to escape
     * @returns {string} - The escaped text
     */
    static escapeHtml(text) {
        if (typeof text !== 'string') {
            return '';
        }

        return text.replace(/[<>&"']/g, match => ({
            '<': '&lt;',
            '>': '&gt;',
            '&': '&amp;',
            '"': '&quot;',
            '\'': '&#x27;',
        }[match]));
    }

    /**
     * Create a safe text node
     * @param {string} text - The text content
     * @returns {Text} - A safe text node
     */
    static createTextNode(text) {
        return document.createTextNode(this.escapeHtml(text));
    }

    /**
     * Safely set text content
     * @param {HTMLElement} element - The element to update
     * @param {string} text - The text content to set
     */
    static safeTextContent(element, text) {
        if (!element || typeof text !== 'string') {
            return;
        }

        element.textContent = this.escapeHtml(text);
    }
}

// Make SecurityUtils available globally
window.SecurityUtils = SecurityUtils;

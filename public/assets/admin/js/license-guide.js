/**
 * License Verification Guide JavaScript
 * External JS file for license guide page
 */

// Constants to avoid magic numbers
const FEEDBACK_DURATION = 2000;
const TOOLTIP_DURATION = 1000;
const CODE_BLOCK_MAX_HEIGHT = 300;
// Array index constants (removed - using direct index access for security)

// Copy to clipboard functionality
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent;
    
    // Create a temporary textarea element
    const textarea = document.createElement('textarea');
    textarea.value = text;
    document.body.appendChild(textarea);
    
    // Select and copy the text
    textarea.select();
    document.execCommand('copy');
    
    // Remove the temporary element
    document.body.removeChild(textarea);
    
    // Show success feedback
    const button = document.querySelector(`[onclick="copyToClipboard('${elementId}')"]`);
    if (button) {
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        button.classList.add('copy-button-success');
        
        setTimeout(() => {
            button.textContent = originalText;
            button.classList.remove('copy-button-success');
        }, FEEDBACK_DURATION);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add click event listeners to all copy buttons
    const copyButtons = document.querySelectorAll('[onclick*="copyToClipboard"]');
    copyButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const onclickAttr = this.getAttribute('onclick');
            const match = onclickAttr.match(/'([^']+)'/);
            if (match && match.length > 1) {
                const elementId = match[1];
                // Validate elementId to prevent injection
                if (elementId && /^[a-zA-Z0-9_-]+$/.test(elementId)) {
                    copyToClipboard(elementId);
                }
            }
        });
    });
    
    // Add smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add syntax highlighting for code blocks (basic)
    const codeBlocks = document.querySelectorAll('.guide-code-content code');
    codeBlocks.forEach(block => {
        // Basic syntax highlighting for common keywords
        let content = block.textContent;
        
        // Highlight PHP keywords
        content = content.replace(/\b(<?php|<?=|\$[a-zA-Z_][a-zA-Z0-9_]*|function|class|public|private|protected|return|if|else|foreach|for|while|try|catch|throw|new|use|namespace|require|include)\b/g, 
            '<span class="syntax-keyword">$1</span>');
        
        // Highlight strings
        content = content.replace(/(['"])([^'"]*)\1/g, 
            '<span class="syntax-string">$1$2$1</span>');
        
        // Highlight comments
        content = content.replace(/(\/\/.*$|\/\*[\s\S]*?\*\/)/gm, 
            '<span class="syntax-comment">$1</span>');
        
        block.innerHTML = content;
    });
    
    // Add tooltips for API methods
    const apiMethods = document.querySelectorAll('.guide-api-method');
    apiMethods.forEach(method => {
        method.setAttribute('title', 'Click to copy URL');
        method.style.cursor = 'pointer';
        
        method.addEventListener('click', function() {
            const url = this.querySelector('.guide-api-method-url').textContent;
            navigator.clipboard.writeText(url).then(() => {
                // Show temporary feedback
                this.classList.add('copied');
                setTimeout(() => {
                    this.classList.remove('copied');
                }, TOOLTIP_DURATION);
            });
        });
    });
    
    // Add expand/collapse functionality for long code blocks
    const longCodeBlocks = document.querySelectorAll('.guide-code-content');
    longCodeBlocks.forEach(block => {
        if (block.scrollHeight > CODE_BLOCK_MAX_HEIGHT) {
            const wrapper = block.closest('.guide-code-block');
            const header = wrapper.querySelector('.guide-code-header');
            
            // Add expand/collapse button
            const expandBtn = document.createElement('button');
            expandBtn.className = 'guide-code-expand';
            expandBtn.textContent = 'Show More';
            
            header.appendChild(expandBtn);
            
            // Set initial state
            block.style.maxHeight = `${CODE_BLOCK_MAX_HEIGHT}px`;
            block.style.overflow = 'hidden';
            
            // Toggle functionality
            expandBtn.addEventListener('click', function() {
                if (block.style.maxHeight === `${CODE_BLOCK_MAX_HEIGHT}px`) {
                    block.style.maxHeight = 'none';
                    this.textContent = 'Show Less';
                } else {
                    block.style.maxHeight = `${CODE_BLOCK_MAX_HEIGHT}px`;
                    this.textContent = 'Show More';
                }
            });
        }
    });
});

// License Status JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Handle copy to clipboard
    document.querySelectorAll('[data-copy-target]').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-copy-target');
            copyToClipboard(targetId);
        });
    });

    // Handle domain history
    document.querySelectorAll('[data-domain]').forEach(button => {
        button.addEventListener('click', function() {
            const domain = this.getAttribute('data-domain');
            viewDomainHistory(domain);
        });
    });
});

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        navigator.clipboard.writeText(element.textContent).then(function() {
            // Show success message
        }).catch(function(err) {
            // Copy failed
        });
    }
}

function viewDomainHistory(domain) {
    // Implementation for viewing domain history
}

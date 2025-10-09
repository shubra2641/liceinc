// Layout JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Handle logout
    document.querySelectorAll('[data-action="logout"]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            logout();
        });
    });

    // Handle clear cache
    document.querySelectorAll('[data-action="clear-cache"]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            clearCache();
        });
    });
});

function logout() {
    document.getElementById('logout-form').submit();
}

function clearCache() {
    // Implementation for clearing cache
}

// Admin Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('admin-menu-toggle');
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('admin-sidebar-overlay');
    const layout = document.querySelector('.admin-layout');
    
    if (menuToggle && sidebar && overlay && layout) {
        // Get direction from layout
        const isRTL = layout.getAttribute('dir') === 'rtl';
        
        // Toggle sidebar
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
        });
        
        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 1024) {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                }
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
        });
        
        // Apply RTL/LTR specific styles
        function applyDirectionStyles() {
            if (window.innerWidth < 1024) {
                if (isRTL) {
                    // RTL: Sidebar slides from right
                    sidebar.style.right = '0';
                    sidebar.style.left = 'auto';
                    sidebar.style.transform = 'translateX(100%)';
                } else {
                    // LTR: Sidebar slides from left
                    sidebar.style.left = '0';
                    sidebar.style.right = 'auto';
                    sidebar.style.transform = 'translateX(-100%)';
                }
            }
        }
        
        // Apply styles on load
        applyDirectionStyles();
        
        // Apply styles on resize
        window.addEventListener('resize', applyDirectionStyles);
    }
    
    // Handle logout forms
    const logoutForms = document.querySelectorAll('form[action*="logout"]');
    logoutForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (window.confirm && confirm('Are you sure you want to logout?')) {
                this.submit();
            }
        });
    });
    
    // Handle logout buttons
    const logoutButtons = document.querySelectorAll('[data-action="logout"], [data-logout]');
    logoutButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = document.getElementById('logout-form') || document.getElementById('topbar-logout-form');
            if (form && window.confirm && confirm('Are you sure you want to logout?')) {
                form.submit();
            }
        });
    });
});

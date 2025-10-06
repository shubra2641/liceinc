// Admin Sidebar Fix for Desktop
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('admin-sidebar');
    const main = document.querySelector('.admin-main');
    const layout = document.querySelector('.admin-layout');
    
    // Force topbar visibility function
    function forceTopbarVisibility() {
        const topbar = document.querySelector('.admin-topbar');
        if (topbar) {
            topbar.style.display = 'flex';
            topbar.style.visibility = 'visible';
            topbar.style.opacity = '1';
            topbar.style.position = 'sticky';
            topbar.style.top = '0';
            topbar.style.zIndex = '1000';
            topbar.style.width = '100%';
            topbar.style.height = '64px';
            topbar.style.background = 'white';
            topbar.style.borderBottom = '1px solid #e2e8f0';
            topbar.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
            topbar.style.margin = '0';
            topbar.style.padding = '0';
            topbar.style.left = '0';
            topbar.style.right = '0';
            topbar.style.boxSizing = 'border-box';
        }
        
        const topbarContent = document.querySelector('.admin-topbar-content');
        if (topbarContent) {
            topbarContent.style.display = 'flex';
            topbarContent.style.alignItems = 'center';
            topbarContent.style.justifyContent = 'space-between';
            topbarContent.style.width = '100%';
            topbarContent.style.height = '100%';
            topbarContent.style.padding = '0 1rem';
        }
    }
    
    if (sidebar && main && layout) {
        // Get direction from layout
        const isRTL = layout.getAttribute('dir') === 'rtl';
        
        // Force sidebar to be visible on desktop
        function checkScreenSize() {
            if (window.innerWidth >= 1024) {
                // Desktop behavior
                sidebar.style.position = 'fixed';
                sidebar.style.transform = 'translateX(0)';
                sidebar.style.width = '280px';
                sidebar.style.minWidth = '280px';
                sidebar.style.height = '100vh';
                sidebar.style.flexShrink = '0';
                sidebar.style.display = 'flex';
                sidebar.style.flexDirection = 'column';
                sidebar.style.zIndex = '1';
                sidebar.style.top = '0';
                
                if (isRTL) {
                    // RTL: Sidebar on right
                    sidebar.style.right = '0';
                    sidebar.style.left = 'auto';
                    main.style.marginRight = '280px';
                    main.style.marginLeft = '0';
                } else {
                    // LTR: Sidebar on left
                    sidebar.style.left = '0';
                    sidebar.style.right = 'auto';
                    main.style.marginLeft = '280px';
                    main.style.marginRight = '0';
                }
                
                main.style.flex = '1';
                main.style.width = 'calc(100% - 280px)';
                main.style.minWidth = '0';
                main.style.display = 'flex';
                main.style.flexDirection = 'column';
                main.style.height = '100vh';
                main.style.overflowY = 'auto';
                
                // Force topbar to be visible
                const topbar = document.querySelector('.admin-topbar');
                if (topbar) {
                    topbar.style.display = 'flex';
                    topbar.style.visibility = 'visible';
                    topbar.style.opacity = '1';
                    topbar.style.position = 'sticky';
                    topbar.style.top = '0';
                    topbar.style.zIndex = '1000';
                    topbar.style.width = '100%';
                    topbar.style.height = '64px';
                    topbar.style.background = 'white';
                    topbar.style.borderBottom = '1px solid #e2e8f0';
                    topbar.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
                }
            } else {
                // Mobile behavior
                sidebar.style.position = 'fixed';
                sidebar.style.width = '280px';
                sidebar.style.maxWidth = '80vw';
                sidebar.style.height = '100vh';
                sidebar.style.flexShrink = 'auto';
                sidebar.style.display = 'flex';
                sidebar.style.flexDirection = 'column';
                sidebar.style.zIndex = '50';
                
                if (isRTL) {
                    // RTL: Sidebar slides from right
                    sidebar.style.right = '0';
                    sidebar.style.left = 'auto';
                    sidebar.style.transform = 'translateX(100%)';
                    main.style.marginRight = '0';
                    main.style.marginLeft = 'auto';
                } else {
                    // LTR: Sidebar slides from left
                    sidebar.style.left = '0';
                    sidebar.style.right = 'auto';
                    sidebar.style.transform = 'translateX(-100%)';
                    main.style.marginLeft = '0';
                    main.style.marginRight = 'auto';
                }
                
                main.style.flex = '1';
                main.style.width = '100%';
                main.style.minWidth = 'auto';
                main.style.display = 'flex';
                main.style.flexDirection = 'column';
            }
        }
        
        // Check on load
        checkScreenSize();
        
        // Check on resize
        window.addEventListener('resize', checkScreenSize);
        
        // Force check after a short delay
        setTimeout(checkScreenSize, 100);
        
        // Force topbar visibility immediately
        forceTopbarVisibility();
        
        // Force topbar visibility on page load
        window.addEventListener('load', forceTopbarVisibility);
        
        // Force topbar visibility on DOM content loaded
        document.addEventListener('DOMContentLoaded', forceTopbarVisibility);
        
        // Force topbar visibility on page show (back/forward navigation)
        window.addEventListener('pageshow', forceTopbarVisibility);
        
        // Force topbar visibility on page focus
        window.addEventListener('focus', forceTopbarVisibility);
    }
});

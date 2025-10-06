// Admin Mobile Menu Toggle
document.addEventListener("DOMContentLoaded", function () {
  const menuToggle = document.getElementById("admin-menu-toggle");
  const sidebar = document.getElementById("admin-sidebar");
  const overlay = document.getElementById("admin-sidebar-overlay");
  const layout = document.querySelector(".admin-layout");

  // Apply RTL/LTR specific styles
  function applyDirectionStyles() {
    if (!sidebar || !layout) return;

    const isRTL = layout.getAttribute("dir") === "rtl";

    if (window.innerWidth < 1024) {
      if (isRTL) {
        // RTL: Sidebar slides from right
        sidebar.style.right = "0";
        sidebar.style.left = "auto";
        sidebar.style.transform = "translateX(100%)";
      } else {
        // LTR: Sidebar slides from left
        sidebar.style.left = "0";
        sidebar.style.right = "auto";
        sidebar.style.transform = "translateX(-100%)";
      }
    }
  }

  if (menuToggle && sidebar && overlay && layout) {
    // Apply styles on load
    applyDirectionStyles();

    // Apply styles on resize
    window.addEventListener("resize", applyDirectionStyles);
  }

  // Handle logout forms
  const logoutForms = document.querySelectorAll('form[action*="logout"]');
  logoutForms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      if (window.confirm && confirm("Are you sure you want to logout?")) {
        this.submit();
      }
    });
  });

  // Handle logout buttons
  const logoutButtons = document.querySelectorAll(
    '[data-action="logout"], [data-logout]',
  );
  logoutButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      const form =
        document.getElementById("logout-form") ||
        document.getElementById("topbar-logout-form");
      if (
        form &&
        window.confirm &&
        confirm("Are you sure you want to logout?")
      ) {
        form.submit();
      }
    });
  });
});

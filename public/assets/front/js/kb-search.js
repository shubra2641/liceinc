/**
 * Knowledge Base Search Functionality
 * Handles search result interactions and card click events
 */

document.addEventListener('DOMContentLoaded', () => {
  initializeSearchResults();
});

/**
 * Initialize search result interactions
 */
function initializeSearchResults() {
  // Ensure search result links work properly
  const searchResults = document.querySelectorAll('.user-kb-article-card');

  searchResults.forEach(card => {
    const searchType = card.getAttribute('data-search-type');
    const slug = card.getAttribute('data-slug');
    const hasAccess = !card.classList.contains('kb-result-locked');

    // Add click handler for the entire card if it has access
    if (hasAccess) {
      card.style.cursor = 'pointer';
      card.addEventListener('click', e => {
        // Don't trigger if clicking on a button or link
        if (e.target.closest('button, a')) {
          return;
        }

        let url = '';
        if (searchType === 'article') {
          url = getArticleUrl(slug);
        } else if (searchType === 'category') {
          url = getCategoryUrl(slug);
        }

        if (url) {
          // Validate URL to prevent XSS and ensure it's safe
          try {
            // Use SecurityUtils for safe navigation
            window.SecurityUtils.safeNavigate(url);
          } catch (e) {
            console.error('Invalid URL format:', e);
          }
        }
      });
    }
  });

  // Add hover effects for clickable cards
  const clickableCards = document.querySelectorAll(
    '.user-kb-article-card:not(.kb-result-locked)',
  );
  clickableCards.forEach(card => {
    card.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-2px)';
      this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
    });

    card.addEventListener('mouseleave', function() {
      this.style.transform = 'translateY(0)';
      this.style.boxShadow = '';
    });
  });
}

/**
 * Get article URL
 */
function getArticleUrl(slug) {
  // Get base URL from current location
  const baseUrl = window.location.origin;
  return `${baseUrl}/kb/article/${slug}`;
}

/**
 * Get category URL
 */
function getCategoryUrl(slug) {
  // Get base URL from current location
  const baseUrl = window.location.origin;
  return `${baseUrl}/kb/category/${slug}`;
}

/**
 * Initialize search form enhancements
 */
function initializeSearchForm() {
  const searchForm = document.querySelector('.user-kb-search-form');
  const searchInput = document.querySelector('#search-input');

  if (searchForm && searchInput) {
    // Add search suggestions or autocomplete if needed
    searchInput.addEventListener('input', function() {
      // Implement search suggestions here if needed
      console.log('Search input changed:', this.value);
    });

    // Add keyboard shortcuts
    searchInput.addEventListener('keydown', e => {
      if (e.key === 'Enter') {
        e.preventDefault();
        searchForm.submit();
      }
    });
  }
}

/**
 * Initialize sort functionality
 */
function initializeSortFunctionality() {
  const sortSelect = document.getElementById('sortSelect');

  if (sortSelect) {
    sortSelect.addEventListener('change', function() {
      const currentUrl = new URL(window.location);
      currentUrl.searchParams.set('sort', this.value);
      // Safe navigation - currentUrl is validated and sanitized
      const urlString = currentUrl.toString();
      const escapedUrl = encodeURIComponent(urlString);
      if (escapedUrl === urlString) {
                window.location.href = SecurityUtils.escapeUrl(urlString);
      } else {
        console.error('Invalid URL: Contains dangerous characters');
      }
    });
  }
}

/**
 * Initialize pagination enhancements
 */
function initializePagination() {
  const paginationLinks = document.querySelectorAll('.pagination-link');

  paginationLinks.forEach(link => {
    link.addEventListener('click', e => {
      // Add loading state if needed
      const container = document.querySelector('.user-kb-articles-grid');
      if (container) {
        container.style.opacity = '0.7';
      }
    });
  });
}

/**
 * Initialize accessibility features
 */
function initializeAccessibility() {
  // Add keyboard navigation for search results
  const searchResults = document.querySelectorAll('.user-kb-article-card');

  searchResults.forEach((card, index) => {
    card.setAttribute('tabindex', '0');
    card.setAttribute('role', 'button');

    card.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        this.click();
      }
    });
  });
}

/**
 * Initialize search analytics (if needed)
 */
function initializeSearchAnalytics() {
  const searchForm = document.querySelector('.user-kb-search-form');

  if (searchForm) {
    searchForm.addEventListener('submit', () => {
      const searchInput = document.querySelector('#search-input');
      if (searchInput && searchInput.value.trim()) {
        // Track search queries if analytics is enabled
        console.log('Search query:', searchInput.value.trim());
      }
    });
  }
}

// Initialize all functionality when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  initializeSearchResults();
  initializeSearchForm();
  initializeSortFunctionality();
  initializePagination();
  initializeAccessibility();
  initializeSearchAnalytics();
});

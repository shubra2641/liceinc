// Simple sanitization utilities for admin panel
// Escapes HTML special chars and optionally allows a tiny whitelist of tags.
(function(global) {
  function escapeHtml(str) {
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }
  function basicSanitize(html) {
    if (!html || typeof html !== 'string') {
      return '';
    }
    // Remove script/style tags entirely
    html = html
      .replace(/<\/(script|style)>/gi, '')
      .replace(/<script[^>]*>[\s\S]*?<\/script>/gi, '')
      .replace(/<style[^>]*>[\s\S]*?<\/style>/gi, '');
    // Optionally allow <b><i><strong><em><u><p><br><ul><ol><li><a href="...">
    return html.replace(/<([^\s>/]+)([^>]*)>/g, (match, tag, attrs) => {
      tag = tag.toLowerCase();
      const allowed = [
        'b',
        'i',
        'strong',
        'em',
        'u',
        'p',
        'br',
        'ul',
        'ol',
        'li',
        'a',
        'span',
        'div',
      ];
      if (!allowed.includes(tag)) {
        return '';
      }
      if (tag === 'a') {
        // keep only href attribute safe
        const hrefMatch =
          attrs.match(/href\s*=\s*"([^"]*)"/i) ||
          attrs.match(/href\s*=\s*'([^']*)'/i);
        if (hrefMatch) {
          const [, url] = hrefMatch;
          if (/^javascript:/i.test(url)) {
            return '<a>';
          } // strip dangerous
          return `<a href="${escapeHtml(url)}" rel="noopener noreferrer">`;
        }
        return '<a>';
      }
      return `<${tag}>`;
    });
  }

  global.SecuritySanitize = { escapeHtml, basicSanitize };
})(window);

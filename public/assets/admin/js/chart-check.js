/* Chart.js presence & fallback loader
 * - Keeps layout blade clean (no inline script)
 * - Attempts secondary CDN if primary failed
 * - Logs a concise console message only when needed
 */
/* global window document console setTimeout */
(function() {
  if (typeof window === 'undefined') return;
  function loadFallback() {
    if (document.getElementById('chartjs-fallback')) return;
    var s = document.createElement('script');
    s.id = 'chartjs-fallback';
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js';
    s.crossOrigin = 'anonymous';
    s.referrerPolicy = 'no-referrer';
    s.onload = function() {
      if (typeof window.Chart !== 'undefined') {
        console.info('[Chart.js] Fallback CDN loaded successfully.');
      } else {
        console.error('[Chart.js] Fallback failed; charts will show fallback UI.');
      }
    };
    s.onerror = function() {
      console.error('[Chart.js] Could not load fallback CDN; charts disabled.');
    };
    document.head.appendChild(s);
  }

  // Defer check slightly to allow primary CDN script to parse
  if (typeof window.Chart === 'undefined') {
    setTimeout(function() {
      if (typeof window.Chart === 'undefined') {
        console.warn('[Chart.js] Primary CDN not loaded, attempting fallback.');
        loadFallback();
      }
    }, 150); // small delay
  }
})();

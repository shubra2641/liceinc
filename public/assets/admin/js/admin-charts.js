/**
 * Admin Dashboard Charts and Statistics
 * Interactive charts and data visualization for the admin dashboard
 */
/* global window document fetch URL setInterval setTimeout console Chart MutationObserver Blob bootstrap alert AdminCharts module */

// Assume Chart.js and Bootstrap are loaded globally

if (typeof window.AdminCharts === 'undefined') {
  class AdminCharts {
    constructor() {
      this.charts = {};
      // Track charts currently loading to avoid race conditions causing duplicate instantiation
      this._loadingCharts = {};
      // Get base URL from meta tag or use current origin
      const meta = document.querySelector('meta[name="base-url"]');
      let baseUrl = window.location.origin;
      if (meta && meta.content) {
        try {
          baseUrl = meta.content.replace(/\/+$/g, '');
        } catch (e) {
          baseUrl = window.location.origin;
        }
      }
      this.baseUrl = baseUrl;

      this.buildUrl = (path = '') => {
        const cleanPath = (path || '').toString().replace(/^\/+/, '');
        const fullPath = cleanPath ? `/${cleanPath}` : '';
        return `${this.baseUrl}${fullPath}`;
      };

      this.apiFetch = async(path, options = {}) => {
        const primaryUrl = this.buildUrl(path);
        if (!this.isValidUrl(primaryUrl)) {
          throw new Error('Invalid URL: SSRF protection activated');
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const headers = { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
        if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;
        Object.assign(headers, options.headers || {});
        const opts = Object.assign({ credentials: 'same-origin', headers, method: options.method || 'GET' }, options);
        const tryParseJson = async resp => {
          const ct = resp.headers.get('content-type') || '';
          if (!resp.ok) {
            if (resp.status === 401) {
              throw new Error('Authentication required. Please refresh the page and log in again.');
            } else if (resp.status === 403) {
              throw new Error('Access denied. You do not have permission to access this data.');
            } else if (resp.status === 404) {
              throw new Error('Data endpoint not found.');
            } else if (resp.status >= 500) {
              throw new Error('Server error occurred while fetching data.');
            }
            throw new Error(`HTTP ${resp.status}`);
          }
          if (!ct.includes('application/json')) {
            throw new Error(`Unexpected content-type: ${ct || 'unknown'}`);
          }
          return resp.json();
        };
        try {
          if (!this.isValidUrl(primaryUrl)) {
            throw new Error('Invalid URL: SSRF protection activated');
          }
          const resp = await fetch(primaryUrl, opts);
          return await tryParseJson(resp);
        } catch (e) {
          if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            // Debug fetch failure
          }
          throw e;
        }
      };

      this.init();
    }

    // Common utility methods to reduce duplication
    getCommonColors() {
      return {
        primary: 'rgba(59, 130, 246, 0.8)',
        success: 'rgba(16, 185, 129, 0.8)',
        warning: 'rgba(245, 158, 11, 0.8)',
        danger: 'rgba(239, 68, 68, 0.8)',
        purple: 'rgba(139, 92, 246, 0.8)',
        primarySolid: 'rgb(59, 130, 246)',
        successSolid: 'rgb(16, 185, 129)',
        warningSolid: 'rgb(245, 158, 11)',
        dangerSolid: 'rgb(239, 68, 68)',
        purpleSolid: 'rgb(139, 92, 246)'
      };
    }

    getCommonTooltipConfig() {
      return {
        backgroundColor: 'rgba(0, 0, 0, 0.8)',
        titleColor: '#fff',
        bodyColor: '#fff',
        cornerRadius: 8
      };
    }

    getCommonScaleConfig() {
      return {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0, 0, 0, 0.1)' },
          ticks: { font: { size: 12, weight: '500' } }
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 12, weight: '500' } }
        }
      };
    }

    getCommonAnimationConfig(duration = 1200) {
      return {
        duration,
        easing: 'easeInOutQuart'
      };
    }

    createOrReplaceChart(chartId, ctx, config) {
      if (this.charts[chartId]) {
        this.charts[chartId].destroy();
      }
      this.charts[chartId] = new Chart(ctx, config);
    }

    handleChartError(chartId, error, fallbackData) {
      console.warn(`${chartId} API failed:`, error);
      return fallbackData;
    }

    createSystemOverviewChart() {
      const ctx = document.getElementById('systemOverviewChart');
      if (!ctx) return;
      if (this._loadingCharts.systemOverview) return; // already in progress
      this._loadingCharts.systemOverview = true;

      const debugLog = (typeof window !== 'undefined' && window.debugLog) ? window.debugLog : function() {};
      const colors = this.getCommonColors();

      const buildConfig = (labels, datasetValues) => ({
        type: 'doughnut',
        data: {
          labels,
          datasets: [
            {
              data: datasetValues,
              backgroundColor: [colors.primary, colors.danger, colors.warning, colors.success],
              borderColor: [colors.primarySolid, colors.dangerSolid, colors.warningSolid, colors.successSolid],
              borderWidth: 2,
              hoverOffset: 8,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'right',
              labels: { usePointStyle: true, padding: 20, font: { size: 12, weight: '500' } },
            },
            tooltip: {
              ...this.getCommonTooltipConfig(),
              callbacks: {
                label: function(context) {
                  const label = context.label || '';
                  const value = context.parsed || 0;
                  return `${label}: ${value.toLocaleString()}`;
                },
              },
            },
          },
          animation: this.getCommonAnimationConfig(),
          cutout: '60%',
        },
      });

      this.apiFetch('/admin/dashboard/system-overview')
        .then(apiData => {
          this.createOrReplaceChart('systemOverview', ctx, buildConfig(apiData.labels, apiData.data));
          return true;
        })
        .catch(err => {
          if (typeof debugLog === 'function') debugLog('System overview API failed:', err);
          this.createOrReplaceChart('systemOverview', ctx, buildConfig(['Active', 'Expired', 'Suspended', 'Pending'], [0,0,0,0]));
          return true;
        })
        .finally(() => { this._loadingCharts.systemOverview = false; });
    }

    createLicenseDistributionChart() {
      const ctx = document.getElementById('licenseDistributionChart');
      if (!ctx) return;

      const colors = this.getCommonColors();
      const buildConfig = (labels, data) => ({
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: 'License Count',
            data,
            backgroundColor: [colors.primary, colors.success, colors.warning, colors.purple],
            borderColor: [colors.primarySolid, colors.successSolid, colors.warningSolid, colors.purpleSolid],
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: this.getCommonScaleConfig(),
          plugins: {
            legend: { display: false },
            tooltip: {
              ...this.getCommonTooltipConfig(),
              callbacks: {
                label: function(context) {
                  return `Count: ${context.parsed.y}`;
                },
              },
            },
          },
          animation: {
            ...this.getCommonAnimationConfig(2000),
            delay: function(context) {
              return context.dataIndex * 200;
            },
          },
          onHover: (event, activeElements) => {
            event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
          },
        },
      });

      this.apiFetch('/admin/dashboard/license-distribution')
        .then(apiData => {
          this.createOrReplaceChart('licenseDistribution', ctx, buildConfig(apiData.labels, apiData.data));
          return true;
        })
        .catch(error => {
          this.handleChartError('License distribution', error);
          const fallbackData = { labels: ['Regular', 'Extended'], data: [0, 0] };
          this.createOrReplaceChart('licenseDistribution', ctx, buildConfig(fallbackData.labels, fallbackData.data));

          // Show user-friendly message only in development
          if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            if (window.adminDashboard && window.adminDashboard.showToast) {
              window.adminDashboard.showToast('Using fallback data for License Distribution chart', 'info', 3000);
            }
          }
          return true;
        });
    }

    createRevenueChart() {
      const ctx = document.getElementById('revenueChart');
      if (!ctx) return;

      // Check if chart already exists and destroy it
      if (Chart.getChart(ctx)) {
        Chart.getChart(ctx).destroy();
      }

      // Fetch real data from API
      this.fetchRevenueData('monthly');

      // Store chart context for updates
      this.revenueChartCtx = ctx;
    }

    fetchRevenueData(period = 'monthly') {
      const colors = this.getCommonColors();
      
      const buildConfig = (labels, data) => ({
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: 'Revenue ($)',
            data,
            borderColor: colors.primarySolid,
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: colors.primarySolid,
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8,
            pointHoverBackgroundColor: colors.primarySolid,
            pointHoverBorderColor: '#fff',
            pointHoverBorderWidth: 3,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            ...this.getCommonScaleConfig(),
            y: {
              ...this.getCommonScaleConfig().y,
              ticks: {
                ...this.getCommonScaleConfig().y.ticks,
                callback: function(value) {
                  return `$${value.toLocaleString()}`;
                },
              },
            },
          },
          plugins: {
            legend: { display: false },
            tooltip: {
              ...this.getCommonTooltipConfig(),
              callbacks: {
                label: function(context) {
                  return `Revenue: $${context.parsed.y.toLocaleString()}`;
                },
              },
            },
          },
          interaction: {
            intersect: false,
            mode: 'index',
          },
          animation: this.getCommonAnimationConfig(2000),
        },
      });

      this.apiFetch(`/admin/dashboard/revenue?period=${encodeURIComponent(period)}`)
        .then(apiData => {
          // Check if canvas still exists
          if (!this.revenueChartCtx || !document.contains(this.revenueChartCtx)) {
            return;
          }

          this.createOrReplaceChart('revenue', this.revenueChartCtx, buildConfig(apiData.labels, apiData.data));
          return true;
        })
        .catch(error => {
          this.handleChartError('Revenue', error);
          
          // Check if canvas still exists
          if (!this.revenueChartCtx || !document.contains(this.revenueChartCtx)) {
            return;
          }

          const fallbackData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            data: [0, 0, 0, 0, 0, 0],
          };

          this.createOrReplaceChart('revenue', this.revenueChartCtx, buildConfig(fallbackData.labels, fallbackData.data));
        });
    }

    createActivityTimelineChart() {
      const ctx = document.getElementById('activityTimelineChart');
      if (!ctx) return;
      if (this._loadingCharts.activityTimeline) return;
      this._loadingCharts.activityTimeline = true;
      
      const debugLog = (typeof window !== 'undefined' && window.debugLog) ? window.debugLog : function() {};
      const colors = this.getCommonColors();

      const buildConfig = (labels, datasetValues) => ({
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: 'Active Users',
            data: datasetValues,
            borderColor: colors.successSolid,
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: colors.successSolid,
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: this.getCommonScaleConfig(),
          plugins: {
            legend: {
              position: 'top',
              labels: { usePointStyle: true, padding: 20, font: { size: 12, weight: '500' } },
            },
            tooltip: {
              ...this.getCommonTooltipConfig(),
              mode: 'index',
              intersect: false,
            },
          },
          interaction: { intersect: false, mode: 'index' },
          animation: this.getCommonAnimationConfig(),
        },
      });

      this.apiFetch('/admin/dashboard/activity-timeline')
        .then(apiData => {
          this.createOrReplaceChart('activityTimeline', ctx, buildConfig(apiData.labels, apiData.data));
          return true;
        })
        .catch(err => {
          if (typeof debugLog === 'function') debugLog('Activity timeline API failed:', err);
          this.createOrReplaceChart('activityTimeline', ctx, buildConfig(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], [0,0,0,0,0,0,0]));
          return true;
        })
        .finally(() => { this._loadingCharts.activityTimeline = false; });
    }

    setupRealTimeUpdates() {
      // Periodically refresh charts with real data (every 30 seconds)
      setInterval(() => {
        this.updateChartData();
      }, 30000);
    }

    updateChartData() {
      // Refresh system overview data
      if (
        this.charts.systemOverview &&
        this.charts.systemOverview.canvas &&
        document.contains(this.charts.systemOverview.canvas)
      ) {
        this.apiFetch('/admin/dashboard/system-overview')
          .then(apiData => {
            this.charts.systemOverview.data.labels = apiData.labels;
            this.charts.systemOverview.data.datasets[0].data = apiData.data;
            this.charts.systemOverview.update('active');
            return true;
          })
          .catch(err => {
            // System overview refresh failed silently
            console.warn('System overview refresh failed:', err);
          });
      }

      // Refresh license distribution data
      if (
        this.charts.licenseDistribution &&
        this.charts.licenseDistribution.canvas &&
        document.contains(this.charts.licenseDistribution.canvas)
      ) {
        this.apiFetch('/admin/dashboard/license-distribution')
          .then(apiData => {
            this.charts.licenseDistribution.data.labels = apiData.labels;
            this.charts.licenseDistribution.data.datasets[0].data =
              apiData.data;
            this.charts.licenseDistribution.update('active');
            return true;
          })
          .catch(err => {
            // License distribution refresh failed - keep existing data
            // Don't update the chart to avoid disrupting user experience
            console.warn('License distribution refresh failed:', err);
          });
      }

      // Refresh revenue chart respecting current period
      const periodSelector = document.querySelector(
        '[data-action="change-chart-period"]',
      );
      const period = periodSelector ? periodSelector.value : 'monthly';
      if (this.fetchRevenueData) {
        this.fetchRevenueData(period);
      }

      // Refresh activity timeline data
      if (
        this.charts.activityTimeline &&
        this.charts.activityTimeline.canvas &&
        document.contains(this.charts.activityTimeline.canvas)
      ) {
        this.apiFetch('/admin/dashboard/activity-timeline')
          .then(apiData => {
            this.charts.activityTimeline.data.labels = apiData.labels;
            this.charts.activityTimeline.data.datasets[0].data = apiData.data;
            this.charts.activityTimeline.update('active');
            return true;
          })
          .catch(err => {
            // Activity timeline refresh failed - keep existing data
            // Don't update the chart to avoid disrupting user experience
            console.warn('Activity timeline refresh failed:', err);
          });
      }

      // Refresh invoices monthly chart if present
      if (
        this.charts.invoicesMonthly &&
        this.charts.invoicesMonthly.canvas &&
        document.contains(this.charts.invoicesMonthly.canvas)
      ) {
        try {
          const node = document.getElementById('invoicesMonthlyChart');
          if (node && node.dataset && node.dataset.chartData) {
            const apiData = JSON.parse(node.dataset.chartData);
            this.charts.invoicesMonthly.data.labels = apiData.labels || [];
            this.charts.invoicesMonthly.data.datasets[0].data =
              apiData.data || [];
            this.charts.invoicesMonthly.update('active');
          }
        } catch (e) {
          // ignore JSON parse / update errors
        }
      }

      // Refresh API requests chart if present
      if (
        this.charts.apiRequests &&
        this.charts.apiRequests.canvas &&
        document.contains(this.charts.apiRequests.canvas)
      ) {
        const periodSelector = document.querySelector(
          '[data-action="change-api-period"]',
        );
        const period = periodSelector ? periodSelector.value : 'daily';
        this.apiFetch(`/admin/dashboard/api-requests?period=${period}`)
          .then(apiData => {
            this.charts.apiRequests.data.labels = apiData.labels;
            this.charts.apiRequests.data.datasets = apiData.datasets;
            this.charts.apiRequests.update('active');
            return true;
          })
          .catch(err => {
            // API requests refresh failed - keep existing data
            console.warn('API requests refresh failed:', err);
          });
      }

      // Refresh API performance chart if present
      if (
        this.charts.apiPerformance &&
        this.charts.apiPerformance.canvas &&
        document.contains(this.charts.apiPerformance.canvas)
      ) {
        this.apiFetch('/admin/dashboard/api-performance')
          .then(apiData => {
            this.charts.apiPerformance.data.datasets[0].data = [
              apiData.today.success,
              apiData.yesterday.success,
            ];
            this.charts.apiPerformance.data.datasets[1].data = [
              apiData.today.failed,
              apiData.yesterday.failed,
            ];
            this.charts.apiPerformance.update('active');
            return true;
          })
          .catch(err => {
            // API performance refresh failed - keep existing data
            console.warn('API performance refresh failed:', err);
          });
      }
    }

    createInvoicesMonthlyChart() {
      const ctxNode = document.getElementById('invoicesMonthlyChart');
      if (!ctxNode) return;

      // Check if chart already exists and destroy it
      if (Chart.getChart(ctxNode)) {
        Chart.getChart(ctxNode).destroy();
      }

      let chartData = { labels: [], data: [] };
      try {
        if (ctxNode.dataset && ctxNode.dataset.chartData) {
          chartData = JSON.parse(ctxNode.dataset.chartData || '{}');
        }
      } catch (e) {
        chartData = { labels: [], data: [] };
      }

      const ctx = ctxNode.getContext('2d');
      const colors = this.getCommonColors();
      
      const data = {
        labels: chartData.labels || [],
        datasets: [{
          label: 'Invoices ($)',
          data: chartData.data || [],
          borderColor: colors.primarySolid,
          backgroundColor: 'rgba(59, 130, 246, 0.08)',
          borderWidth: 2,
          fill: true,
          tension: 0.3,
          pointBackgroundColor: colors.primarySolid,
        }],
      };

      const config = {
        type: 'line',
        data,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  return `$${value.toLocaleString()}`;
                },
              },
            },
            x: { grid: { display: false } },
          },
          plugins: {
            legend: { display: false },
            tooltip: {
              ...this.getCommonTooltipConfig(),
              callbacks: {
                label: function(context) {
                  return `Amount: $${context.parsed.y.toLocaleString()}`;
                },
              },
            },
          },
        },
      };

      this.createOrReplaceChart('invoicesMonthly', ctx, config);
    }

    setupChartInteractions() {
      // Handle period selector changes
      const periodSelector = document.querySelector(
        '[data-action="change-chart-period"]',
      );
      if (periodSelector) {
        periodSelector.addEventListener('change', e => {
          this.changeChartPeriod(e.target.value);
        });
      }

      // Add click handlers for charts
      document.addEventListener('click', e => {
        if (e.target.closest('.admin-chart-container')) {
          const chartContainer = e.target.closest('.admin-chart-container');
          const canvas = chartContainer.querySelector('canvas');

          if (canvas && canvas.chart) {
            const { chart } = canvas;
            const elements = chart.getElementsAtEventForMode(
              e,
              'nearest',
              { intersect: true },
              false,
            );

            if (elements.length > 0) {
              const [element] = elements;
              const { datasetIndex, index } = element;
              const label = chart.data.labels[index];
              const value = chart.data.datasets[datasetIndex].data[index];

              this.showChartDetail(chart, label, value, datasetIndex);
            }
          }
        }
      });
    }

    changeChartPeriod(period) {
      if (this.fetchRevenueData) {
        this.fetchRevenueData(period);
      } else if (window.adminDashboard) {
        // Here you would typically fetch new data from the server
        window.adminDashboard.showToast(
          `Chart period changed to ${period}`,
          'info',
          2000,
        );
      }
    }

    showChartDetail(chart, label, value, datasetIndex) {
      const dataset = chart.data.datasets[datasetIndex];
      const detail = {
        title: dataset.label || 'Chart Detail',
        label,
        value,
        color: dataset.borderColor || dataset.backgroundColor,
      };

      // Show toast notification with details
      if (window.adminDashboard) {
        window.adminDashboard.showToast(
          `${detail.title}: ${detail.label} - ${detail.value}`,
          'info',
          3000,
        );
      }
    }

    // Method to update chart theme (for dark mode)
    updateChartTheme(isDark) {
      const textColor = isDark ? '#f9fafb' : '#374151';
      const gridColor = isDark ?
        'rgba(255, 255, 255, 0.1)' :
        'rgba(0, 0, 0, 0.1)';

      Object.values(this.charts).forEach(chart => {
        if (chart.options.scales) {
          // Update axis colors
          Object.values(chart.options.scales).forEach(scale => {
            if (scale.ticks) {
              scale.ticks.color = textColor;
            }
            if (scale.grid) {
              scale.grid.color = gridColor;
            }
          });
        }

        // Update legend colors
        if (chart.options.plugins.legend) {
          chart.options.plugins.legend.labels.color = textColor;
        }

        chart.update();
      });
    }

    // Method to export chart data
    exportChartData(chartId, format = 'csv') {
      const chart = this.charts[chartId];
      if (!chart) {
        return;
      }

      const { data } = chart;
      let exportData = '';

      if (format === 'csv') {
        // CSV header
        exportData =
          `Label,${data.datasets.map(ds => ds.label).join(',')}\n`;

        // CSV data
        data.labels.forEach((label, index) => {
          exportData += `${label},`;
          data.datasets.forEach(dataset => {
            exportData += `${dataset.data[index]},`;
          });
          exportData = `${exportData.slice(0, -1)}\n`;
        });
      } else if (format === 'json') {
        exportData = JSON.stringify(data, null, 2);
      }

      // Download file
      const blob = new Blob([exportData], { type: 'text/plain' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `chart-data-${chartId}.${format}`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    }

    // Method to resize all charts
    resizeCharts() {
      Object.values(this.charts).forEach(chart => {
        chart.resize();
      });
    }

    // Method to destroy all charts
    destroy() {
      Object.values(this.charts).forEach(chart => {
        chart.destroy();
      });
      this.charts = {};
    }

    // Dashboard-specific chart methods
    createApiRequestsChart() {
      const ctx = document.getElementById('apiRequestsChart');
      if (!ctx) return;

      // Check if chart already exists and destroy it
      if (Chart.getChart(ctx)) {
        Chart.getChart(ctx).destroy();
      }

      const colors = this.getCommonColors();
      const config = {
        type: 'line',
        data: { labels: [], datasets: [] },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'top' },
            title: { display: true, text: 'API Requests Over Time' },
          },
          scales: { y: { beginAtZero: true } },
        },
      };

      this.createOrReplaceChart('apiRequests', ctx, config);

      // Load API requests data
      const loadApiRequestsData = (period = 'daily') => {
        this.apiFetch(`/admin/dashboard/api-requests?period=${period}`)
          .then(data => {
            this.charts.apiRequests.data.labels = data.labels;
            this.charts.apiRequests.data.datasets = data.datasets;
            this.charts.apiRequests.update();
            return true;
          })
          .catch(error => {
            this.handleChartError('API requests', error);
            // Use fallback data
            this.charts.apiRequests.data.labels = ['No Data'];
            this.charts.apiRequests.data.datasets = [{
              label: 'API Requests',
              data: [0],
              borderColor: colors.primarySolid,
              backgroundColor: 'rgba(59, 130, 246, 0.1)',
              tension: 0.1,
            }];
            this.charts.apiRequests.update();
            return true;
          });
      };

      // Load initial data
      loadApiRequestsData();

      // Handle period change
      document.addEventListener('change', e => {
        if (e.target.matches('[data-action="change-api-period"]')) {
          loadApiRequestsData(e.target.value);
        }
      });
    }

    createApiPerformanceChart() {
      const ctx = document.getElementById('apiPerformanceChart');
      if (!ctx) return;

      // Check if chart already exists and destroy it
      if (Chart.getChart(ctx)) {
        Chart.getChart(ctx).destroy();
      }

      const colors = this.getCommonColors();
      const config = {
        type: 'bar',
        data: {
          labels: ['Today', 'Yesterday'],
          datasets: [
            {
              label: 'Successful',
              data: [0, 0],
              backgroundColor: colors.success,
              borderColor: colors.successSolid,
              borderWidth: 1,
            },
            {
              label: 'Failed',
              data: [0, 0],
              backgroundColor: colors.danger,
              borderColor: colors.dangerSolid,
              borderWidth: 1,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'top' },
            title: { display: true, text: 'API Performance Comparison' },
          },
          scales: { y: { beginAtZero: true } },
        },
      };

      this.createOrReplaceChart('apiPerformance', ctx, config);

      // Load API performance data
      this.apiFetch('/admin/dashboard/api-performance')
        .then(data => {
          this.charts.apiPerformance.data.datasets[0].data = [data.today.success, data.yesterday.success];
          this.charts.apiPerformance.data.datasets[1].data = [data.today.failed, data.yesterday.failed];
          this.charts.apiPerformance.update();
          return true;
        })
        .catch(error => {
          this.handleChartError('API performance', error);
          // Keep default data (0, 0) for both datasets
        });
    }
  }

  // expose to window to prevent redefinition
  window.AdminCharts = AdminCharts;
}

// Initialize charts when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  // Check if Chart.js is loaded and AdminCharts is available
  if (
    typeof Chart !== 'undefined' &&
    typeof window.AdminCharts !== 'undefined' &&
    typeof window.adminCharts === 'undefined'
  ) {
    try {
      window.adminCharts = new window.AdminCharts();

      // Listen for dark mode changes
      const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
          if (
            mutation.type === 'attributes' &&
            mutation.attributeName === 'class'
          ) {
            const isDark =
              document.documentElement.classList.contains('dark-mode');
            if (window.adminCharts) {
              window.adminCharts.updateChartTheme(isDark);
            }
          }
        });
      });

      observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
      });
    } catch (e) {
      // Silently ignore initialization errors to avoid breaking page
      // AdminCharts init failed
    }
  }

  // Initialize reports charts if on reports page
  if (
    document.getElementById('monthlyRevenueChart') ||
    document.getElementById('invoicesMonthlyChart')
  ) {
    // Add loading states to chart containers
    const chartContainers = document.querySelectorAll('.chart-container');
    chartContainers.forEach(container => {
      container.classList.add('loading');
    });

    // Initialize reports charts
    initReportsCharts();

    // Remove loading states after charts are initialized
    setTimeout(() => {
      chartContainers.forEach(container => {
        container.classList.remove('loading');
      });
    }, 1000);

    // Add refresh functionality
    const refreshBtn = document.querySelector(
      '[data-action="refresh-reports"]',
    );
    if (refreshBtn) {
      refreshBtn.addEventListener('click', () => {
        // Add loading states
        chartContainers.forEach(container => {
          container.classList.add('loading');
        });

        // Reload page after a short delay
        setTimeout(() => {
          window.location.reload();
        }, 500);
      });
    }
  }
});

// Export for CommonJS environments if available (Node/testing)
try {
  if (typeof module !== 'undefined' && module && module.exports) {
  module.exports = AdminCharts; // Optional CommonJS export for testing environments
  }
} catch (e) {
  // Silently ignore if module export not permitted in environment
}

// Safely attach to window for browser usage
if (typeof window !== 'undefined') {
  window.AdminCharts = AdminCharts;
}

/**
 * Initialize Reports Page Charts
 */
function initReportsCharts() {
  // Check if Chart.js is available
  if (typeof Chart === 'undefined') {
    return;
  }

  // Common chart configuration
  const getCommonReportConfig = (title, yTitle, yCallback) => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: true, position: 'top' },
      tooltip: {
        callbacks: {
          label: function(context) {
            return `${context.dataset.label}: ${yCallback ? yCallback(context.parsed.y) : context.parsed.y.toLocaleString()}`;
          },
        },
      },
    },
    scales: {
      x: { title: { display: true, text: 'Last 3 Months' } },
      y: {
        beginAtZero: true,
        title: { display: true, text: yTitle },
        ticks: { callback: yCallback },
      },
    },
  });

  // Initialize Monthly Revenue Chart
  const monthlyRevenueCanvas = document.getElementById('monthlyRevenueChart');
  if (monthlyRevenueCanvas) {
    if (Chart.getChart(monthlyRevenueCanvas)) {
      Chart.getChart(monthlyRevenueCanvas).destroy();
    }

    const chartData = JSON.parse(monthlyRevenueCanvas.getAttribute('data-chart-data') || '{}');
    if (chartData.labels && chartData.datasets) {
      try {
        new Chart(monthlyRevenueCanvas, {
          type: 'line',
          data: chartData,
          options: getCommonReportConfig('Monthly Revenue', 'Revenue ($)', value => `$${value.toLocaleString()}`),
        });
      } catch (error) {
        monthlyRevenueCanvas.parentElement.classList.add('error');
      }
    } else {
      monthlyRevenueCanvas.parentElement.classList.add('error');
    }
  }

  // Initialize Monthly Licenses Chart
  const monthlyLicensesCanvas = document.getElementById('monthlyLicensesChart');
  if (monthlyLicensesCanvas) {
    if (Chart.getChart(monthlyLicensesCanvas)) {
      Chart.getChart(monthlyLicensesCanvas).destroy();
    }

    const chartData = JSON.parse(monthlyLicensesCanvas.getAttribute('data-chart-data') || '{}');
    if (chartData.labels && chartData.datasets) {
      try {
        new Chart(monthlyLicensesCanvas, {
          type: 'line',
          data: chartData,
          options: getCommonReportConfig('Monthly Licenses', 'Number of Licenses', value => `${value} licenses`),
        });
      } catch (error) {
        monthlyLicensesCanvas.parentElement.classList.add('error');
      }
    } else {
      monthlyLicensesCanvas.parentElement.classList.add('error');
    }
  }

  // Initialize User Registrations Chart
  const userRegistrationsCanvas = document.getElementById('userRegistrationsChart');
  if (userRegistrationsCanvas) {
    if (Chart.getChart(userRegistrationsCanvas)) {
      Chart.getChart(userRegistrationsCanvas).destroy();
    }

    const chartData = JSON.parse(userRegistrationsCanvas.getAttribute('data-chart-data') || '{}');
    if (chartData.labels && chartData.datasets) {
      try {
        new Chart(userRegistrationsCanvas, {
          type: 'line',
          data: chartData,
          options: getCommonReportConfig('User Registrations', 'Number of Users', value => `${value} users`),
        });
      } catch (error) {
        userRegistrationsCanvas.parentElement.classList.add('error');
      }
    } else {
      userRegistrationsCanvas.parentElement.classList.add('error');
    }
  }

  // Initialize System Overview Chart
  const systemOverviewCanvas = document.getElementById('systemOverviewChart');
  if (systemOverviewCanvas) {
    if (Chart.getChart(systemOverviewCanvas)) {
      Chart.getChart(systemOverviewCanvas).destroy();
    }

    const chartData = JSON.parse(systemOverviewCanvas.getAttribute('data-chart-data') || '{}');
    if (chartData.labels && chartData.datasets) {
      try {
        new Chart(systemOverviewCanvas, {
          type: 'doughnut',
          data: chartData,
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: true, position: 'bottom' } },
          },
        });
      } catch (error) {
        systemOverviewCanvas.parentElement.classList.add('error');
      }
    } else {
      systemOverviewCanvas.parentElement.classList.add('error');
    }
  }

  // Initialize License Type Chart
  const licenseTypeCanvas = document.getElementById('licenseTypeChart');
  if (licenseTypeCanvas) {
    const chartData = JSON.parse(licenseTypeCanvas.getAttribute('data-chart-data') || '{}');
    if (chartData.labels && chartData.datasets) {
      new Chart(licenseTypeCanvas, {
        type: 'pie',
        data: chartData,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: true, position: 'bottom' } },
        },
      });
    }
  }

  // Initialize Activity Timeline Chart
  const activityTimelineCanvas = document.getElementById('activityTimelineChart');
  if (activityTimelineCanvas) {
    const chartData = JSON.parse(activityTimelineCanvas.getAttribute('data-chart-data') || '{}');
    if (chartData.labels && chartData.datasets) {
      new Chart(activityTimelineCanvas, {
        type: 'bar',
        data: chartData,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: true, position: 'top' } },
          scales: { y: { beginAtZero: true } },
        },
      });
    }
  }

  // Initialize Invoices Monthly Chart
  const invoicesMonthlyCanvas = document.getElementById('invoicesMonthlyChart');
  if (invoicesMonthlyCanvas) {
    if (Chart.getChart(invoicesMonthlyCanvas)) {
      Chart.getChart(invoicesMonthlyCanvas).destroy();
    }

    const chartData = JSON.parse(invoicesMonthlyCanvas.getAttribute('data-chart-data') || '{}');
    if (chartData.labels && chartData.datasets) {
      try {
        new Chart(invoicesMonthlyCanvas, {
          type: 'line',
          data: chartData,
          options: getCommonReportConfig('Invoices Monthly', 'Invoice Amount ($)', value => `$${value.toLocaleString()}`),
        });
      } catch (error) {
        invoicesMonthlyCanvas.parentElement.classList.add('error');
      }
    } else {
      invoicesMonthlyCanvas.parentElement.classList.add('error');
    }
  }
}

// Enhanced Logs Page JavaScript
document.addEventListener('DOMContentLoaded', () => {
  // View log details modal
  document.addEventListener('click', function(e) {
    if (e.target.closest('[data-action="view-log-details"]')) {
      const { logId } = e.target.closest('[data-action="view-log-details"]').dataset;
      const modalContent = document.getElementById('logDetailsContent');
      if (modalContent) {
        // Sanitize logId to prevent XSS
        const sanitizedLogId = logId.replace(/[<>&"']/g, match => ({
          '<': '&lt;',
          '>': '&gt;',
          '&': '&amp;',
          '"': '&quot;',
          '\'': '&#x27;',
        }[match]));
        window.SecurityUtils.safeInnerHTML(
          this,
          `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Log ID:</strong> ${sanitizedLogId}</p>
                            <p><strong>Date:</strong> ${new Date().toLocaleString()}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <span class="badge bg-success">Success</span></p>
                            <p><strong>IP Address:</strong> 127.0.0.1</p>
                        </div>
                    </div>
                    <hr>
                    <h6>Detailed Information</h6>
                    <p>Detailed log information will be displayed here</p>
                `,
        );
      }
      const modal = new bootstrap.Modal(
        document.getElementById('logDetailsModal'),
      );
      modal.show();
    }
  });

  // Export functionality
  document.addEventListener('click', e => {
    if (e.target.closest('[data-action="export-logs"]')) {
      alert('Export functionality coming soon');
    }
  });
});

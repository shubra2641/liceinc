/**
 * Admin Dashboard Charts and Statistics - Zero Duplication Version
 * Unified chart system with complete elimination of code duplication
 */
/* global window document fetch URL setInterval setTimeout console Chart MutationObserver Blob bootstrap alert AdminCharts module */

if (typeof window.AdminCharts === 'undefined') {
  class AdminCharts {
    constructor() {
      this.charts = {};
      this._loadingCharts = {};
      this.baseUrl = this._getBaseUrl();
      this._init();
    }

    // ===== CORE CONFIGURATION =====
    _getBaseUrl() {
      const meta = document.querySelector('meta[name="base-url"]');
      let baseUrl = window.location.origin;
      if (meta && meta.content) {
        try {
          baseUrl = meta.content.replace(/\/+$/g, '');
        } catch (e) {
          baseUrl = window.location.origin;
        }
      }
      return baseUrl;
    }

    _buildUrl(path = '') {
      const cleanPath = (path || '').toString().replace(/^\/+/, '');
      const fullPath = cleanPath ? `/${cleanPath}` : '';
      return `${this.baseUrl}${fullPath}`;
    }

    _isValidUrl(url) {
      try {
        const urlObj = new URL(url);
        return urlObj.protocol === 'http:' || urlObj.protocol === 'https:';
      } catch {
        return false;
      }
    }

    // ===== UNIFIED CHART SYSTEM =====
    _getChartConfig(type, data, options = {}) {
      const baseConfig = {
        type,
        data,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          ...options
        }
      };
      return baseConfig;
    }

    _getCommonColors() {
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

    _getCommonOptions() {
      return {
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleColor: '#fff',
          bodyColor: '#fff',
          cornerRadius: 8
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: { color: 'rgba(0, 0, 0, 0.1)' },
            ticks: { font: { size: 12, weight: '500' } }
          },
          x: {
            grid: { display: false },
            ticks: { font: { size: 12, weight: '500' } }
          }
        },
        animation: {
          duration: 1200,
          easing: 'easeInOutQuart'
        }
      };
    }

    // ===== UNIFIED CHART CREATION =====
    _createChart(chartId, ctx, config) {
      if (!this._validateChartId(chartId)) return false;
      
      if (this.charts[chartId]) {
        this.charts[chartId].destroy();
      }
      
      this.charts[chartId] = new Chart(ctx, config);
      return true;
    }

    _validateChartId(chartId) {
      if (!chartId || typeof chartId !== 'string' || !/^[a-zA-Z][a-zA-Z0-9]*$/.test(chartId)) {
        console.error('Invalid chartId:', chartId);
        return false;
      }
      return true;
    }

    // ===== UNIFIED API HANDLING =====
    async _apiFetch(path, options = {}) {
      const primaryUrl = this._buildUrl(path);
      if (!this._isValidUrl(primaryUrl)) {
        throw new Error('Invalid URL: SSRF protection activated');
      }
      
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      const headers = { 
        Accept: 'application/json', 
        'Content-Type': 'application/json', 
        'X-Requested-With': 'XMLHttpRequest' 
      };
      if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;
      Object.assign(headers, options.headers || {});
      
      const opts = Object.assign({ 
        credentials: 'same-origin', 
        headers, 
        method: options.method || 'GET' 
      }, options);
      
      try {
        const resp = await fetch(primaryUrl, opts);
        return await this._parseResponse(resp);
      } catch (e) {
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
          // Debug fetch failure
        }
        throw e;
      }
    }

    async _parseResponse(resp) {
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
    }

    // ===== UNIFIED ERROR HANDLING =====
    _handleChartError(chartId, error, fallbackData) {
      console.warn(`${chartId} API failed:`, error);
      return fallbackData;
    }

    // ===== CHART TYPE SPECIFIC CONFIGURATIONS =====
    _getDoughnutConfig(labels, data, colors) {
      return this._getChartConfig('doughnut', {
        labels,
        datasets: [{
          data,
          backgroundColor: colors.slice(0, data.length),
          borderColor: colors.map(c => c.replace('0.8', '1')),
          borderWidth: 2,
          hoverOffset: 8,
        }]
      }, {
        plugins: {
          legend: {
            position: 'right',
            labels: { usePointStyle: true, padding: 20, font: { size: 12, weight: '500' } }
          },
          tooltip: this._getCommonOptions().tooltip
        },
        animation: this._getCommonOptions().animation,
        cutout: '60%'
      });
    }

    _getBarConfig(labels, data, colors, label = 'Count') {
      return this._getChartConfig('bar', {
        labels,
        datasets: [{
          label,
          data,
          backgroundColor: colors.slice(0, data.length),
          borderColor: colors.map(c => c.replace('0.8', '1')).slice(0, data.length),
          borderWidth: 2,
          borderRadius: 8,
          borderSkipped: false,
        }]
      }, {
        scales: this._getCommonOptions().scales,
        plugins: {
          legend: { display: false },
          tooltip: {
            ...this._getCommonOptions().tooltip,
            callbacks: {
              label: function(context) {
                return `Count: ${context.parsed.y}`;
              }
            }
          }
        },
        animation: {
          ...this._getCommonOptions().animation,
          delay: function(context) {
            return context.dataIndex * 200;
          }
        },
        onHover: (event, activeElements) => {
          event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
        }
      });
    }

    _getLineConfig(labels, data, colors, label = 'Data', options = {}) {
      return this._getChartConfig('line', {
        labels,
        datasets: [{
          label,
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
          ...options
        }]
      }, {
        scales: {
          ...this._getCommonOptions().scales,
          y: {
            ...this._getCommonOptions().scales.y,
            ticks: {
              ...this._getCommonOptions().scales.y.ticks,
              callback: options.yCallback || function(value) {
                return value.toLocaleString();
              }
            }
          }
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            ...this._getCommonOptions().tooltip,
            callbacks: {
              label: function(context) {
                return `${label}: ${context.parsed.y.toLocaleString()}`;
              }
            }
          }
        },
        interaction: {
          intersect: false,
          mode: 'index'
        },
        animation: this._getCommonOptions().animation
      });
    }

    // ===== UNIFIED CHART CREATION METHODS =====
    _createChartFromAPI(chartId, ctx, apiPath, configBuilder, fallbackData) {
      if (this._loadingCharts[chartId]) return;
      this._loadingCharts[chartId] = true;

      this._apiFetch(apiPath)
        .then(apiData => {
          const config = configBuilder(apiData.labels, apiData.data);
          this._createChart(chartId, ctx, config);
          return true;
        })
        .catch(err => {
          console.warn(`${chartId} API failed:`, err);
          const config = configBuilder(fallbackData.labels, fallbackData.data);
          this._createChart(chartId, ctx, config);
          return false;
        })
        .finally(() => {
          this._loadingCharts[chartId] = false;
        });
    }

    _init() {
      // Initialize all charts
      this.createSystemOverviewChart();
      this.createLicenseDistributionChart();
      this.createRevenueChart();
      this.createActivityTimelineChart();
      this.createInvoicesMonthlyChart();
      this.createApiRequestsChart();
      this.createApiPerformanceChart();
      this.setupRealTimeUpdates();
      this.setupChartInteractions();
    }

    // ===== CHART CREATION METHODS (ZERO DUPLICATION) =====
    createSystemOverviewChart() {
      const ctx = document.getElementById('systemOverviewChart');
      if (!ctx || this._loadingCharts.systemOverview) return;

      const colors = this._getCommonColors();
      const colorArray = [colors.primary, colors.danger, colors.warning, colors.success];
      
      const configBuilder = (labels, data) => this._getDoughnutConfig(labels, data, colorArray);
      const fallbackData = { labels: ['Active', 'Expired', 'Suspended', 'Pending'], data: [0,0,0,0] };
      
      this._createChartFromAPI('systemOverview', ctx, '/admin/dashboard/system-overview', configBuilder, fallbackData);
    }

    createLicenseDistributionChart() {
      const ctx = document.getElementById('licenseDistributionChart');
      if (!ctx) return;

      const colors = this._getCommonColors();
      const colorArray = [colors.primary, colors.success, colors.warning, colors.purple];
      
      const configBuilder = (labels, data) => this._getBarConfig(labels, data, colorArray, 'License Count');
      const fallbackData = { labels: ['Regular', 'Extended'], data: [0, 0] };
      
      this._apiFetch('/admin/dashboard/license-distribution')
        .then(apiData => {
          const config = configBuilder(apiData.labels, apiData.data);
          this._createChart('licenseDistribution', ctx, config);
        })
        .catch(error => {
          this._handleChartError('License distribution', error);
          const config = configBuilder(fallbackData.labels, fallbackData.data);
          this._createChart('licenseDistribution', ctx, config);
        });
    }

    createRevenueChart() {
      const ctx = document.getElementById('revenueChart');
      if (!ctx) return;

      if (Chart.getChart(ctx)) {
        Chart.getChart(ctx).destroy();
      }

      this.revenueChartCtx = ctx;
      this.fetchRevenueData('monthly');
    }

    fetchRevenueData(period = 'monthly') {
      const colors = this._getCommonColors();
      
      const configBuilder = (labels, data) => this._getLineConfig(
        labels, 
        data, 
        colors, 
        'Revenue ($)', 
        { yCallback: value => `$${value.toLocaleString()}` }
      );
      
      const fallbackData = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        data: [0, 0, 0, 0, 0, 0]
      };

      this._apiFetch(`/admin/dashboard/revenue?period=${encodeURIComponent(period)}`)
        .then(apiData => {
          if (!this.revenueChartCtx || !document.contains(this.revenueChartCtx)) {
            return false;
          }
          const config = configBuilder(apiData.labels, apiData.data);
          this._createChart('revenue', this.revenueChartCtx, config);
          return true;
        })
        .catch(error => {
          this._handleChartError('Revenue', error);
          if (!this.revenueChartCtx || !document.contains(this.revenueChartCtx)) {
            return false;
          }
          const config = configBuilder(fallbackData.labels, fallbackData.data);
          this._createChart('revenue', this.revenueChartCtx, config);
          return false;
        });
    }

    createActivityTimelineChart() {
      const ctx = document.getElementById('activityTimelineChart');
      if (!ctx || this._loadingCharts.activityTimeline) return;
      
      const colors = this._getCommonColors();
      const configBuilder = (labels, data) => this._getLineConfig(
        labels, 
        data, 
        colors, 
        'Active Users',
        { 
          borderColor: colors.successSolid,
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          pointBackgroundColor: colors.successSolid
        }
      );
      
      const fallbackData = { labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], data: [0,0,0,0,0,0,0] };
      
      this._createChartFromAPI('activityTimeline', ctx, '/admin/dashboard/activity-timeline', configBuilder, fallbackData);
    }

    createInvoicesMonthlyChart() {
      const ctxNode = document.getElementById('invoicesMonthlyChart');
      if (!ctxNode) return;

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
      const colors = this._getCommonColors();
      
      const config = this._getLineConfig(
        chartData.labels || [],
        chartData.data || [],
        colors,
        'Invoices ($)',
        { yCallback: value => `$${value.toLocaleString()}` }
      );

      this._createChart('invoicesMonthly', ctx, config);
    }

    createApiRequestsChart() {
      const ctx = document.getElementById('apiRequestsChart');
      if (!ctx) return;

      if (Chart.getChart(ctx)) {
        Chart.getChart(ctx).destroy();
      }

      const config = this._getChartConfig('line', {
        labels: [],
        datasets: []
      }, {
        plugins: {
          legend: { position: 'top' },
          title: { display: true, text: 'API Requests Over Time' }
        },
        scales: { y: { beginAtZero: true } }
      });

      this._createChart('apiRequests', ctx, config);

      const loadApiRequestsData = (period = 'daily') => {
        this._apiFetch(`/admin/dashboard/api-requests?period=${period}`)
          .then(data => {
            this.charts.apiRequests.data.labels = data.labels;
            this.charts.apiRequests.data.datasets = data.datasets;
            this.charts.apiRequests.update();
          })
          .catch(error => {
            this._handleChartError('API requests', error);
            this.charts.apiRequests.data.labels = ['No Data'];
            this.charts.apiRequests.data.datasets = [{
              label: 'API Requests',
              data: [0],
              borderColor: this._getCommonColors().primarySolid,
              backgroundColor: 'rgba(59, 130, 246, 0.1)',
              tension: 0.1,
            }];
            this.charts.apiRequests.update();
          });
      };

      loadApiRequestsData();

      document.addEventListener('change', e => {
        if (e.target.matches('[data-action="change-api-period"]')) {
          loadApiRequestsData(e.target.value);
        }
      });
    }

    createApiPerformanceChart() {
      const ctx = document.getElementById('apiPerformanceChart');
      if (!ctx) return;

      if (Chart.getChart(ctx)) {
        Chart.getChart(ctx).destroy();
      }

      const colors = this._getCommonColors();
      const config = this._getChartConfig('bar', {
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
          }
        ]
      }, {
        plugins: {
          legend: { position: 'top' },
          title: { display: true, text: 'API Performance Comparison' }
        },
        scales: { y: { beginAtZero: true } }
      });

      this._createChart('apiPerformance', ctx, config);

      this._apiFetch('/admin/dashboard/api-performance')
        .then(data => {
          this.charts.apiPerformance.data.datasets[0].data = [data.today.success, data.yesterday.success];
          this.charts.apiPerformance.data.datasets[1].data = [data.today.failed, data.yesterday.failed];
          this.charts.apiPerformance.update();
        })
        .catch(error => {
          this._handleChartError('API performance', error);
        });
    }

    // ===== UTILITY METHODS =====
    setupRealTimeUpdates() {
      setInterval(() => {
        this.updateChartData();
      }, 30000);
    }

    updateChartData() {
      const updateChart = (chartId, apiPath, updateFn) => {
        if (this.charts[chartId] && this.charts[chartId].canvas && document.contains(this.charts[chartId].canvas)) {
          this._apiFetch(apiPath)
            .then(apiData => {
              updateFn(apiData);
              this.charts[chartId].update('active');
            })
            .catch(err => {
              console.warn(`${chartId} refresh failed:`, err);
            });
        }
      };

      updateChart('systemOverview', '/admin/dashboard/system-overview', (apiData) => {
        this.charts.systemOverview.data.labels = apiData.labels;
        this.charts.systemOverview.data.datasets[0].data = apiData.data;
      });

      updateChart('licenseDistribution', '/admin/dashboard/license-distribution', (apiData) => {
        this.charts.licenseDistribution.data.labels = apiData.labels;
        this.charts.licenseDistribution.data.datasets[0].data = apiData.data;
      });

      updateChart('activityTimeline', '/admin/dashboard/activity-timeline', (apiData) => {
        this.charts.activityTimeline.data.labels = apiData.labels;
        this.charts.activityTimeline.data.datasets[0].data = apiData.data;
      });

      const periodSelector = document.querySelector('[data-action="change-chart-period"]');
      const period = periodSelector ? periodSelector.value : 'monthly';
      if (this.fetchRevenueData) {
        this.fetchRevenueData(period);
      }
    }

    setupChartInteractions() {
      const periodSelector = document.querySelector('[data-action="change-chart-period"]');
      if (periodSelector) {
        periodSelector.addEventListener('change', e => {
          this.changeChartPeriod(e.target.value);
        });
      }

      document.addEventListener('click', e => {
        if (e.target.closest('.admin-chart-container')) {
          const chartContainer = e.target.closest('.admin-chart-container');
          const canvas = chartContainer.querySelector('canvas');

          if (canvas && canvas.chart) {
            const { chart } = canvas;
            const elements = chart.getElementsAtEventForMode(e, 'nearest', { intersect: true }, false);

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
        window.adminDashboard.showToast(`Chart period changed to ${period}`, 'info', 2000);
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

      if (window.adminDashboard) {
        window.adminDashboard.showToast(`${detail.title}: ${detail.label} - ${detail.value}`, 'info', 3000);
      }
    }

    updateChartTheme(isDark) {
      const textColor = isDark ? '#f9fafb' : '#374151';
      const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

      Object.values(this.charts).forEach(chart => {
        if (chart.options.scales) {
          Object.values(chart.options.scales).forEach(scale => {
            if (scale.ticks) scale.ticks.color = textColor;
            if (scale.grid) scale.grid.color = gridColor;
          });
        }
        if (chart.options.plugins.legend) {
          chart.options.plugins.legend.labels.color = textColor;
        }
        chart.update();
      });
    }

    exportChartData(chartId, format = 'csv') {
      const chart = this.charts[chartId];
      if (!chart) return;

      const { data } = chart;
      let exportData = '';

      if (format === 'csv') {
        exportData = `Label,${data.datasets.map(ds => ds.label).join(',')}\n`;
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

    resizeCharts() {
      Object.values(this.charts).forEach(chart => {
        chart.resize();
      });
    }

    destroy() {
      Object.values(this.charts).forEach(chart => {
        chart.destroy();
      });
      this.charts = {};
    }
  }

  // ===== GLOBAL INITIALIZATION =====
  window.AdminCharts = AdminCharts;
}

// ===== DOM READY INITIALIZATION =====
document.addEventListener('DOMContentLoaded', () => {
  if (typeof Chart !== 'undefined' && typeof window.AdminCharts !== 'undefined' && typeof window.adminCharts === 'undefined') {
    try {
      window.adminCharts = new window.AdminCharts();

      // Dark mode observer
      const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
          if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
            const isDark = document.documentElement.classList.contains('dark-mode');
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
      console.warn('AdminCharts initialization failed:', e);
    }
  }

  // Initialize reports charts
  if (document.getElementById('monthlyRevenueChart') || document.getElementById('invoicesMonthlyChart')) {
    initReportsCharts();
  }
});

// ===== REPORTS CHARTS INITIALIZATION =====
function initReportsCharts() {
  if (typeof Chart === 'undefined') return;

  const createReportChart = (canvasId, chartType, options = {}) => {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    if (Chart.getChart(canvas)) {
      Chart.getChart(canvas).destroy();
    }

    const chartData = JSON.parse(canvas.getAttribute('data-chart-data') || '{}');
    if (!chartData.labels || !chartData.datasets) {
      canvas.parentElement.classList.add('error');
      return;
    }

    try {
      new Chart(canvas, {
        type: chartType,
        data: chartData,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          ...options
        }
      });
    } catch (error) {
      canvas.parentElement.classList.add('error');
    }
  };

  // Initialize all report charts
  createReportChart('monthlyRevenueChart', 'line', {
    plugins: {
      legend: { display: true, position: 'top' },
      tooltip: {
        callbacks: {
          label: function(context) {
            return `${context.dataset.label}: $${context.parsed.y.toLocaleString()}`;
          }
        }
      }
    },
    scales: {
      x: { title: { display: true, text: 'Last 3 Months' } },
      y: {
        beginAtZero: true,
        title: { display: true, text: 'Revenue ($)' },
        ticks: { callback: value => `$${value.toLocaleString()}` }
      }
    }
  });

  createReportChart('monthlyLicensesChart', 'line', {
    plugins: {
      legend: { display: true, position: 'top' },
      tooltip: {
        callbacks: {
          label: function(context) {
            return `${context.dataset.label}: ${context.parsed.y} licenses`;
          }
        }
      }
    },
    scales: {
      x: { title: { display: true, text: 'Last 3 Months' } },
      y: {
        beginAtZero: true,
        title: { display: true, text: 'Number of Licenses' }
      }
    }
  });

  createReportChart('userRegistrationsChart', 'line', {
    plugins: {
      legend: { display: true, position: 'top' },
      tooltip: {
        callbacks: {
          label: function(context) {
            return `${context.dataset.label}: ${context.parsed.y} users`;
          }
        }
      }
    },
    scales: {
      x: { title: { display: true, text: 'Last 3 Months' } },
      y: {
        beginAtZero: true,
        title: { display: true, text: 'Number of Users' }
      }
    }
  });

  createReportChart('systemOverviewChart', 'doughnut', {
    plugins: { legend: { display: true, position: 'bottom' } }
  });

  createReportChart('licenseTypeChart', 'pie', {
    plugins: { legend: { display: true, position: 'bottom' } }
  });

  createReportChart('activityTimelineChart', 'bar', {
    plugins: { legend: { display: true, position: 'top' } },
    scales: { y: { beginAtZero: true } }
  });

  createReportChart('invoicesMonthlyChart', 'line', {
    plugins: {
      legend: { display: true, position: 'top' },
      tooltip: {
        callbacks: {
          label: function(context) {
            return `${context.dataset.label}: $${context.parsed.y.toLocaleString()}`;
          }
        }
      }
    },
    scales: {
      x: { title: { display: true, text: 'Last 3 Months' } },
      y: {
        beginAtZero: true,
        title: { display: true, text: 'Invoice Amount ($)' },
        ticks: { callback: value => `$${value.toLocaleString()}` }
      }
    }
  });
}

// ===== LOGS PAGE FUNCTIONALITY =====
document.addEventListener('DOMContentLoaded', () => {
  // View log details modal
  document.addEventListener('click', function(e) {
    if (e.target.closest('[data-action="view-log-details"]')) {
      const { logId } = e.target.closest('[data-action="view-log-details"]').dataset;
      const modalContent = document.getElementById('logDetailsContent');
      if (modalContent) {
        const sanitizedLogId = logId.replace(/[<>&"']/g, match => ({
          '<': '&lt;',
          '>': '&gt;',
          '&': '&amp;',
          '"': '&quot;',
          '\'': '&#x27;',
        }[match]));
        
        modalContent.innerHTML = `
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
        `;
      }
      
      const modal = new bootstrap.Modal(document.getElementById('logDetailsModal'));
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

// ===== MODULE EXPORTS =====
try {
  if (typeof module !== 'undefined' && module && module.exports) {
    module.exports = AdminCharts;
  }
} catch (e) {
  // Silently ignore if module export not permitted
}

if (typeof window !== 'undefined') {
  window.AdminCharts = AdminCharts;
}
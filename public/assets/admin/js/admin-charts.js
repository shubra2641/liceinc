/**
 * Admin Dashboard Charts - Simplified and Secure
 */

document.addEventListener('DOMContentLoaded', function() {
  // Simple utility functions
  const Utils = {
    get: (selector) => document.querySelector(selector),
    getAll: (selector) => document.querySelectorAll(selector),
    safeText: (el, text) => el && (el.textContent = text),
    escapeHTML: (text) => {
      if (typeof text !== 'string') return text;
      return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
  };

  // Chart colors
  const colors = {
    primary: 'rgba(59, 130, 246, 0.8)',
    success: 'rgba(16, 185, 129, 0.8)',
    warning: 'rgba(245, 158, 11, 0.8)',
    danger: 'rgba(239, 68, 68, 0.8)',
    purple: 'rgba(139, 92, 246, 0.8)'
  };

  // Chart manager
  class ChartManager {
    constructor() {
      this.charts = {};
      this.init();
    }

    init() {
      this.setupCharts();
      this.setupEventListeners();
    }

    setupCharts() {
      // Revenue chart
      this.createRevenueChart();
      // Sales chart
      this.createSalesChart();
      // Users chart
      this.createUsersChart();
      // Products chart
      this.createProductsChart();
    }

    setupEventListeners() {
      // Refresh button
      const refreshBtn = Utils.get('#refresh-charts');
      if (refreshBtn) {
        refreshBtn.addEventListener('click', () => this.refreshAllCharts());
      }

      // Date range selector
      const dateRange = Utils.get('#date-range');
      if (dateRange) {
        dateRange.addEventListener('change', () => this.updateCharts());
      }
    }

    async createRevenueChart() {
      const ctx = Utils.get('#revenue-chart');
      if (!ctx) return;

      try {
        const data = await this.fetchChartData('revenue');
        this.createChart(ctx, 'line', data, 'Revenue');
      } catch (error) {
        this.showError('revenue-chart', 'Failed to load revenue data');
      }
    }

    async createSalesChart() {
      const ctx = Utils.get('#sales-chart');
      if (!ctx) return;

      try {
        const data = await this.fetchChartData('sales');
        this.createChart(ctx, 'bar', data, 'Sales');
      } catch (error) {
        this.showError('sales-chart', 'Failed to load sales data');
      }
    }

    async createUsersChart() {
      const ctx = Utils.get('#users-chart');
      if (!ctx) return;

      try {
        const data = await this.fetchChartData('users');
        this.createChart(ctx, 'doughnut', data, 'Users');
      } catch (error) {
        this.showError('users-chart', 'Failed to load users data');
      }
    }

    async createProductsChart() {
      const ctx = Utils.get('#products-chart');
      if (!ctx) return;

      try {
        const data = await this.fetchChartData('products');
        this.createChart(ctx, 'pie', data, 'Products');
      } catch (error) {
        this.showError('products-chart', 'Failed to load products data');
      }
    }

    createChart(ctx, type, data, label) {
      if (!ctx || !Chart) return;

      // Destroy existing chart
      const existingChart = Chart.getChart(ctx);
      if (existingChart) {
        existingChart.destroy();
      }

      const config = this.getChartConfig(type, data, label);
      const chart = new Chart(ctx, config);
      this.charts[ctx.id] = chart;
    }

    getChartConfig(type, data, label) {
      const baseConfig = {
        type: type,
        data: {
          labels: data.labels || [],
          datasets: [{
            label: label,
            data: data.values || [],
            backgroundColor: this.getColors(data.values?.length || 0),
            borderColor: this.getColors(data.values?.length || 0, 1),
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                usePointStyle: true,
                padding: 20
              }
            },
            tooltip: {
              backgroundColor: 'rgba(0, 0, 0, 0.8)',
              titleColor: '#fff',
              bodyColor: '#fff',
              cornerRadius: 8
            }
          },
          scales: type !== 'doughnut' && type !== 'pie' ? {
            y: {
              beginAtZero: true,
              grid: { color: 'rgba(0, 0, 0, 0.1)' },
              ticks: { font: { size: 12 } }
            },
            x: {
              grid: { display: false },
              ticks: { font: { size: 12 } }
            }
          } : {},
          animation: {
            duration: 1200,
            easing: 'easeInOutQuart'
          }
        }
      };

      if (type === 'doughnut' || type === 'pie') {
        baseConfig.options.plugins.legend.position = 'right';
        if (type === 'doughnut') {
          baseConfig.options.cutout = '60%';
        }
      }

      return baseConfig;
    }

    getColors(count, alpha = 0.8) {
      const colorArray = Object.values(colors);
      const result = [];
      for (let i = 0; i < count; i++) {
        result.push(colorArray[i % colorArray.length].replace('0.8', alpha.toString()));
      }
      return result;
    }

    async fetchChartData(type) {
      const response = await fetch(`/api/admin/charts/${type}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': Utils.get('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      return await response.json();
    }

    showError(chartId, message) {
      const container = Utils.get(`#${chartId}`);
      if (container) {
        Utils.safeText(container, message);
        Utils.addClass(container, 'error');
      }
    }

    async refreshAllCharts() {
      const refreshBtn = Utils.get('#refresh-charts');
      if (refreshBtn) {
        Utils.addClass(refreshBtn, 'loading');
        Utils.safeText(refreshBtn, 'Loading...');
      }

      try {
        await Promise.all([
          this.createRevenueChart(),
          this.createSalesChart(),
          this.createUsersChart(),
          this.createProductsChart()
        ]);
      } catch (error) {
        console.error('Error refreshing charts:', error);
      } finally {
        if (refreshBtn) {
          Utils.removeClass(refreshBtn, 'loading');
          Utils.safeText(refreshBtn, 'Refresh');
        }
      }
    }

    async updateCharts() {
      await this.refreshAllCharts();
    }
  }

  // Statistics cards
  class StatsManager {
    constructor() {
      this.init();
    }

    init() {
      this.loadStats();
      this.setupEventListeners();
    }

    setupEventListeners() {
      const refreshBtn = Utils.get('#refresh-stats');
      if (refreshBtn) {
        refreshBtn.addEventListener('click', () => this.loadStats());
      }
    }

    async loadStats() {
      try {
        const response = await fetch('/api/admin/stats', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': Utils.get('meta[name="csrf-token"]')?.getAttribute('content') || ''
          }
        });

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        this.updateStatsCards(data);
      } catch (error) {
        console.error('Error loading stats:', error);
        this.showStatsError();
      }
    }

    updateStatsCards(data) {
      const stats = [
        { id: 'total-users', value: data.totalUsers || 0 },
        { id: 'total-products', value: data.totalProducts || 0 },
        { id: 'total-sales', value: data.totalSales || 0 },
        { id: 'total-revenue', value: data.totalRevenue || 0 }
      ];

      stats.forEach(stat => {
        const element = Utils.get(`#${stat.id}`);
        if (element) {
          Utils.safeText(element, stat.value);
        }
      });
    }

    showStatsError() {
      const statsCards = Utils.getAll('.stats-card');
      statsCards.forEach(card => {
        Utils.addClass(card, 'error');
        const valueElement = card.querySelector('.stats-value');
        if (valueElement) {
          Utils.safeText(valueElement, 'Error');
        }
      });
    }
  }

  // Initialize everything
  if (typeof Chart !== 'undefined') {
    new ChartManager();
  }
  new StatsManager();

  // Expose to global scope
  window.AdminCharts = {
    ChartManager,
    StatsManager
  };
});
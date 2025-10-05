/**
 * Admin Dashboard Charts and Statistics
 * Interactive charts and data visualization for the admin dashboard
 */

if (typeof window.AdminCharts === 'undefined') {
    class AdminCharts {
        constructor() {
            this.charts = {};
            // Get base URL from meta tag or use current origin
            // NOTE: use the full meta content (preserve possible subdirectory) instead of .origin
            const meta = document.querySelector('meta[name="base-url"]');
            let baseUrl = window.location.origin;
            if (meta && meta.content) {
                try {
                    // Keep the full URL as provided by the server (trim trailing slash)
                    baseUrl = meta.content.replace(/\/+$|\s+$/g, '');
                } catch (e) {
                    baseUrl = window.location.origin;
                }
            }
            this.baseUrl = baseUrl;
            
            this.buildUrl = (path = '') => {
                // Remove leading slashes and build full URL
                const cleanPath = (path || '').toString().replace(/^\/+/, '');
                const fullPath = cleanPath ? `/${cleanPath}` : '';
                const finalUrl = `${this.baseUrl}${fullPath}`;
                // URL building for API calls
                return finalUrl;
            };

            // Unified fetch with proper URL building and authentication
            this.apiFetch = async (path, options = {}) => {
                const primaryUrl = this.buildUrl(path);
                
                // Get CSRF token from meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                // Prepare headers with authentication
                const headers = {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                };
                
                // Add CSRF token if available
                if (csrfToken) {
                    headers['X-CSRF-TOKEN'] = csrfToken;
                }
                
                // Merge with any additional headers
                Object.assign(headers, options.headers || {});
                
                const opts = Object.assign({ 
                    credentials: 'same-origin', 
                    headers,
                    method: options.method || 'GET'
                }, options);

                const tryParseJson = async (resp) => {
                    const ct = resp.headers.get('content-type') || '';
                    if (!resp.ok) {
                        // Handle authentication errors gracefully
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
                    const resp = await fetch(primaryUrl, opts);
                    return await tryParseJson(resp);
                } catch (e) {
                    // Log the error for debugging (only in development)
                    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                        // AdminCharts: Failed to fetch data from primary URL, using fallback
                    }
                    throw e;
                }
            };

            this.init();
        }

        init() {
            this.setupCharts();
            this.setupRealTimeUpdates();
            this.setupChartInteractions();
        }

        setupCharts() {
            // System Overview Chart
            this.createSystemOverviewChart();

            // License Distribution Chart
            this.createLicenseDistributionChart();

            // Revenue Chart
            this.createRevenueChart();

            // Activity Timeline Chart
            this.createActivityTimelineChart();

            // Invoices Monthly Chart
            this.createInvoicesMonthlyChart();

            // Dashboard-specific charts
            this.createApiRequestsChart();
            this.createApiPerformanceChart();
        }

        createSystemOverviewChart() {
            const ctx = document.getElementById('systemOverviewChart');
            if (!ctx) return;
            
            // Check if chart already exists and destroy it
            if (Chart.getChart(ctx)) {
                Chart.getChart(ctx).destroy();
            }
            
            this.apiFetch('/admin/dashboard/system-overview')
                .then(apiData => {
                    const data = {
                        labels: apiData.labels,
                        datasets: [{
                            data: apiData.data,
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(239, 68, 68, 0.8)',
                                'rgba(245, 158, 11, 0.8)',
                                'rgba(16, 185, 129, 0.8)'
                            ],
                            borderColor: [
                                'rgb(59, 130, 246)',
                                'rgb(239, 68, 68)',
                                'rgb(245, 158, 11)',
                                'rgb(16, 185, 129)'
                            ],
                            borderWidth: 2,
                            hoverOffset: 8
                        }]
                    };

                    const config = {
                        type: 'doughnut',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true,
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: '#fff',
                                    bodyColor: '#fff',
                                    cornerRadius: 8,
                                    displayColors: true,
                                    callbacks: {
                                        label: function (context) {
                                            const label = context.label || '';
                                            const value = context.parsed || 0;
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = Math.round((value / total) * 100);
                                            return `${label}: ${value} (${percentage}%)`;
                                        }
                                    }
                                }
                            },
                            animation: {
                                animateScale: true,
                                animateRotate: true,
                                duration: 2000,
                                easing: 'easeInOutQuart'
                            }
                        }
                    };

                    this.charts.systemOverview = new Chart(ctx, config);
                })
                .catch(error => {
                    // Use fallback data if API fails
                    const fallbackData = {
                        labels: ['Active Licenses', 'Expired Licenses', 'Pending Requests', 'Total Products'],
                        data: [0, 0, 0, 0]
                    };
                    
                    // Check if chart already exists and destroy it
                    if (Chart.getChart(ctx)) {
                        Chart.getChart(ctx).destroy();
                    }
                    
                    const data = {
                        labels: fallbackData.labels,
                        datasets: [{
                            data: fallbackData.data,
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(239, 68, 68, 0.8)',
                                'rgba(245, 158, 11, 0.8)',
                                'rgba(16, 185, 129, 0.8)'
                            ],
                            borderColor: [
                                'rgb(59, 130, 246)',
                                'rgb(239, 68, 68)',
                                'rgb(245, 158, 11)',
                                'rgb(16, 185, 129)'
                            ],
                            borderWidth: 2,
                            hoverOffset: 8
                        }]
                    };

                    const config = {
                        type: 'doughnut',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    };

                    this.charts.systemOverview = new Chart(ctx, config);
                });
        }

        createLicenseDistributionChart() {
            const ctx = document.getElementById('licenseDistributionChart');
            if (!ctx) return;
            
            // Check if chart already exists and destroy it
            if (Chart.getChart(ctx)) {
                Chart.getChart(ctx).destroy();
            }
            
            this.apiFetch('/admin/dashboard/license-distribution')
                .then(apiData => {
                    const data = {
                        labels: apiData.labels,
                        datasets: [{
                            label: 'License Count',
                            data: apiData.data,
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.6)',
                                'rgba(16, 185, 129, 0.6)',
                                'rgba(245, 158, 11, 0.6)',
                                'rgba(139, 92, 246, 0.6)'
                            ],
                            borderColor: [
                                'rgb(59, 130, 246)',
                                'rgb(16, 185, 129)',
                                'rgb(245, 158, 11)',
                                'rgb(139, 92, 246)'
                            ],
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                        }]
                    };

                    const config = {
                        type: 'bar',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: '#fff',
                                    bodyColor: '#fff',
                                    cornerRadius: 8,
                                    callbacks: {
                                        label: function (context) {
                                            return `Count: ${context.parsed.y}`;
                                        }
                                    }
                                }
                            },
                            animation: {
                                duration: 2000,
                                easing: 'easeInOutQuart',
                                delay: function (context) {
                                    return context.dataIndex * 200;
                                }
                            },
                            onHover: (event, activeElements) => {
                                event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
                            }
                        }
                    };

                    this.charts.licenseDistribution = new Chart(ctx, config);
                })
                .catch(error => {
                    // Use fallback data when API fails
                    const fallbackData = {
                        labels: ['Regular', 'Extended'],
                        data: [0, 0]
                    };
                    
                    const data = {
                        labels: fallbackData.labels,
                        datasets: [{
                            label: 'License Count',
                            data: fallbackData.data,
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.6)',
                                'rgba(16, 185, 129, 0.6)'
                            ],
                            borderColor: [
                                'rgb(59, 130, 246)',
                                'rgb(16, 185, 129)'
                            ],
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                        }]
                    };

                    const config = {
                        type: 'bar',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: '#fff',
                                    bodyColor: '#fff',
                                    cornerRadius: 8,
                                    callbacks: {
                                        label: function (context) {
                                            return `Count: ${context.parsed.y}`;
                                        }
                                    }
                                }
                            },
                            animation: {
                                duration: 1000,
                                easing: 'easeInOutQuart'
                            }
                        }
                    };

                    this.charts.licenseDistribution = new Chart(ctx, config);
                    
                    // Show user-friendly message only in development
                    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                        if (window.adminDashboard && window.adminDashboard.showToast) {
                            window.adminDashboard.showToast('Using fallback data for License Distribution chart', 'info', 3000);
                        }
                    }
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
            this.apiFetch(`/admin/dashboard/revenue?period=${encodeURIComponent(period)}`)
                .then(apiData => {
                    const data = {
                        labels: apiData.labels,
                        datasets: [{
                            label: 'Revenue ($)',
                            data: apiData.data,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            pointHoverBackgroundColor: 'rgb(59, 130, 246)',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 3
                        }]
                    };

                    const config = {
                        type: 'line',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        },
                                        callback: function (value) {
                                            return '$' + value.toLocaleString();
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: '#fff',
                                    bodyColor: '#fff',
                                    cornerRadius: 8,
                                    callbacks: {
                                        label: function (context) {
                                            return `Revenue: $${context.parsed.y.toLocaleString()}`;
                                        }
                                    }
                                }
                            },
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            animation: {
                                duration: 2000,
                                easing: 'easeInOutQuart'
                            }
                        }
                    };

                    // Destroy existing chart if it exists
                    // Check if canvas still exists
                    if (!this.revenueChartCtx || !document.contains(this.revenueChartCtx)) {
                        return;
                    }

                    if (this.charts.revenue) {
                        this.charts.revenue.destroy();
                    }

                    this.charts.revenue = new Chart(this.revenueChartCtx, config);
                })
                .catch(error => {
                    // Use fallback data when API fails
                    const fallbackData = {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        data: [0, 0, 0, 0, 0, 0]
                    };
                    
                    const data = {
                        labels: fallbackData.labels,
                        datasets: [{
                            label: 'Revenue ($)',
                            data: fallbackData.data,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8
                        }]
                    };

                    const config = {
                        type: 'line',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        },
                                        callback: function (value) {
                                            return '$' + value.toLocaleString();
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: '#fff',
                                    bodyColor: '#fff',
                                    cornerRadius: 8,
                                    callbacks: {
                                        label: function (context) {
                                            return `Revenue: $${context.parsed.y.toLocaleString()}`;
                                        }
                                    }
                                }
                            },
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            animation: {
                                duration: 1000,
                                easing: 'easeInOutQuart'
                            }
                        }
                    };

                    // Check if canvas still exists
                    if (!this.revenueChartCtx || !document.contains(this.revenueChartCtx)) {
                        return;
                    }

                    if (this.charts.revenue) {
                        this.charts.revenue.destroy();
                    }

                    this.charts.revenue = new Chart(this.revenueChartCtx, config);
                });
        }

        createActivityTimelineChart() {
            const ctx = document.getElementById('activityTimelineChart');
            if (!ctx) return;

            // Check if chart already exists and destroy it
            if (Chart.getChart(ctx)) {
                Chart.getChart(ctx).destroy();
            }

            // Fetch real data from API
            this.apiFetch('/admin/dashboard/activity-timeline')
                .then(apiData => {
                    const data = {
                        labels: apiData.labels,
                        datasets: [{
                            label: 'Active Users',
                            data: apiData.data,
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgb(16, 185, 129)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8
                        }]
                    };

                    const config = {
                        type: 'line',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20,
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: '#fff',
                                    bodyColor: '#fff',
                                    cornerRadius: 8,
                                    mode: 'index',
                                    intersect: false
                                }
                            },
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            animation: {
                                duration: 2000,
                                easing: 'easeInOutQuart'
                            }
                        }
                    };

                    this.charts.activityTimeline = new Chart(ctx, config);
                })
                .catch(error => {
                    // Use fallback data if API fails
                    const fallbackData = {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        data: [0, 0, 0, 0, 0, 0, 0]
                    };
                    
                    // Check if chart already exists and destroy it
                    if (Chart.getChart(ctx)) {
                        Chart.getChart(ctx).destroy();
                    }
                    
                    const data = {
                        labels: fallbackData.labels,
                        datasets: [{
                            label: 'Daily Activity',
                            data: fallbackData.data,
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgb(16, 185, 129)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8
                        }]
                    };

                    const config = {
                        type: 'line',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)'
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20,
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleColor: '#fff',
                                    bodyColor: '#fff',
                                    cornerRadius: 8,
                                    mode: 'index',
                                    intersect: false
                                }
                            },
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            animation: {
                                duration: 1000,
                                easing: 'easeInOutQuart'
                            }
                        }
                    };

                    this.charts.activityTimeline = new Chart(ctx, config);
                });
        }

        setupRealTimeUpdates() {
            // Periodically refresh charts with real data (every 30 seconds)
            setInterval(() => {
                this.updateChartData();
            }, 30000);
        }

        updateChartData() {
            // Refresh system overview data
            if (this.charts.systemOverview && this.charts.systemOverview.canvas && document.contains(this.charts.systemOverview.canvas)) {
                this.apiFetch('/admin/dashboard/system-overview')
                    .then(apiData => {
                        this.charts.systemOverview.data.labels = apiData.labels;
                        this.charts.systemOverview.data.datasets[0].data = apiData.data;
                        this.charts.systemOverview.update('active');
                    })
                    .catch(err => {
                        // System overview refresh failed silently
                    });
            }

            // Refresh license distribution data
            if (this.charts.licenseDistribution && this.charts.licenseDistribution.canvas && document.contains(this.charts.licenseDistribution.canvas)) {
                this.apiFetch('/admin/dashboard/license-distribution')
                    .then(apiData => {
                        this.charts.licenseDistribution.data.labels = apiData.labels;
                        this.charts.licenseDistribution.data.datasets[0].data = apiData.data;
                        this.charts.licenseDistribution.update('active');
                    })
                    .catch(err => {
                        // License distribution refresh failed - keep existing data
                        // Don't update the chart to avoid disrupting user experience
                    });
            }

            // Refresh revenue chart respecting current period
            const periodSelector = document.querySelector('[data-action="change-chart-period"]');
            const period = periodSelector ? periodSelector.value : 'monthly';
            if (this.fetchRevenueData) {
                this.fetchRevenueData(period);
            }

            // Refresh activity timeline data
            if (this.charts.activityTimeline && this.charts.activityTimeline.canvas && document.contains(this.charts.activityTimeline.canvas)) {
                this.apiFetch('/admin/dashboard/activity-timeline')
                    .then(apiData => {
                        this.charts.activityTimeline.data.labels = apiData.labels;
                        this.charts.activityTimeline.data.datasets[0].data = apiData.data;
                        this.charts.activityTimeline.update('active');
                    })
                    .catch(err => {
                        // Activity timeline refresh failed - keep existing data
                        // Don't update the chart to avoid disrupting user experience
                    });
            }

            // Refresh invoices monthly chart if present
            if (this.charts.invoicesMonthly && this.charts.invoicesMonthly.canvas && document.contains(this.charts.invoicesMonthly.canvas)) {
                try {
                    const node = document.getElementById('invoicesMonthlyChart');
                    if (node && node.dataset && node.dataset.chartData) {
                        const apiData = JSON.parse(node.dataset.chartData);
                        this.charts.invoicesMonthly.data.labels = apiData.labels || [];
                        this.charts.invoicesMonthly.data.datasets[0].data = apiData.data || [];
                        this.charts.invoicesMonthly.update('active');
                    }
                } catch (e) {
                    // ignore JSON parse / update errors
                }
            }

            // Refresh API requests chart if present
            if (this.charts.apiRequests && this.charts.apiRequests.canvas && document.contains(this.charts.apiRequests.canvas)) {
                const periodSelector = document.querySelector('[data-action="change-api-period"]');
                const period = periodSelector ? periodSelector.value : 'daily';
                this.apiFetch(`/admin/dashboard/api-requests?period=${period}`)
                    .then(apiData => {
                        this.charts.apiRequests.data.labels = apiData.labels;
                        this.charts.apiRequests.data.datasets = apiData.datasets;
                        this.charts.apiRequests.update('active');
                    })
                    .catch(err => {
                        // API requests refresh failed - keep existing data
                    });
            }

            // Refresh API performance chart if present
            if (this.charts.apiPerformance && this.charts.apiPerformance.canvas && document.contains(this.charts.apiPerformance.canvas)) {
                this.apiFetch('/admin/dashboard/api-performance')
                    .then(apiData => {
                        this.charts.apiPerformance.data.datasets[0].data = [apiData.today.success, apiData.yesterday.success];
                        this.charts.apiPerformance.data.datasets[1].data = [apiData.today.failed, apiData.yesterday.failed];
                        this.charts.apiPerformance.update('active');
                    })
                    .catch(err => {
                        // API performance refresh failed - keep existing data
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
            const data = {
                labels: chartData.labels || [],
                datasets: [{
                    label: 'Invoices ($)',
                    data: chartData.data || [],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.08)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: 'rgb(59, 130, 246)'
                }]
            };

            const config = {
                type: 'line',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) { return '$' + value.toLocaleString(); }
                            }
                        },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return `Amount: $${context.parsed.y.toLocaleString()}`;
                                }
                            }
                        }
                    }
                }
            };

            // Destroy existing if present
            if (this.charts.invoicesMonthly) {
                this.charts.invoicesMonthly.destroy();
            }

            this.charts.invoicesMonthly = new Chart(ctx, config);
        }

        setupChartInteractions() {
            // Handle period selector changes
            const periodSelector = document.querySelector('[data-action="change-chart-period"]');
            if (periodSelector) {
                periodSelector.addEventListener('change', (e) => {
                    this.changeChartPeriod(e.target.value);
                });
            }

            // Add click handlers for charts
            document.addEventListener('click', (e) => {
                if (e.target.closest('.admin-chart-container')) {
                    const chartContainer = e.target.closest('.admin-chart-container');
                    const canvas = chartContainer.querySelector('canvas');

                    if (canvas && canvas.chart) {
                        const chart = canvas.chart;
                        const elements = chart.getElementsAtEventForMode(e, 'nearest', { intersect: true }, false);

                        if (elements.length > 0) {
                            const element = elements[0];
                            const datasetIndex = element.datasetIndex;
                            const index = element.index;
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
            } else {
                
                // Here you would typically fetch new data from the server
                if (window.adminDashboard) {
                    window.adminDashboard.showToast(`Chart period changed to ${period}`, 'info', 2000);
                }
            }
        }

        showChartDetail(chart, label, value, datasetIndex) {
            const dataset = chart.data.datasets[datasetIndex];
            const detail = {
                title: dataset.label || 'Chart Detail',
                label: label,
                value: value,
                color: dataset.borderColor || dataset.backgroundColor
            };

            // Show toast notification with details
            if (window.adminDashboard) {
                window.adminDashboard.showToast(
                    `${detail.title}: ${detail.label} - ${detail.value}`,
                    'info',
                    3000
                );
            }
        }

        // Method to update chart theme (for dark mode)
        updateChartTheme(isDark) {
            const textColor = isDark ? '#f9fafb' : '#374151';
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

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
            if (!chart) return;

            const data = chart.data;
            let exportData = '';

            if (format === 'csv') {
                // CSV header
                exportData = 'Label,' + data.datasets.map(ds => ds.label).join(',') + '\n';

                // CSV data
                data.labels.forEach((label, index) => {
                    exportData += label + ',';
                    data.datasets.forEach(dataset => {
                        exportData += dataset.data[index] + ',';
                    });
                    exportData = exportData.slice(0, -1) + '\n';
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

            const apiRequestsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'API Requests Over Time'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Store chart reference
            this.charts.apiRequests = apiRequestsChart;

            // Load API requests data
            const loadApiRequestsData = (period = 'daily') => {
                this.apiFetch(`/admin/dashboard/api-requests?period=${period}`)
                    .then(data => {
                        apiRequestsChart.data.labels = data.labels;
                        apiRequestsChart.data.datasets = data.datasets;
                        apiRequestsChart.update();
                    })
                    .catch(error => {
                        console.error('Error loading API requests data:', error);
                        // Use fallback data
                        apiRequestsChart.data.labels = ['No Data'];
                        apiRequestsChart.data.datasets = [{
                            label: 'API Requests',
                            data: [0],
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.1
                        }];
                        apiRequestsChart.update();
                    });
            };

            // Load initial data
            loadApiRequestsData();

            // Handle period change
            document.addEventListener('change', function(e) {
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

            const apiPerformanceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Today', 'Yesterday'],
                    datasets: [
                        {
                            label: 'Successful',
                            data: [0, 0],
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Failed',
                            data: [0, 0],
                            backgroundColor: 'rgba(239, 68, 68, 0.8)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'API Performance Comparison'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Store chart reference
            this.charts.apiPerformance = apiPerformanceChart;

            // Load API performance data
            this.apiFetch('/admin/dashboard/api-performance')
                .then(data => {
                    apiPerformanceChart.data.datasets[0].data = [data.today.success, data.yesterday.success];
                    apiPerformanceChart.data.datasets[1].data = [data.today.failed, data.yesterday.failed];
                    apiPerformanceChart.update();
                })
                .catch(error => {
                    console.error('Error loading API performance data:', error);
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
    if (typeof Chart !== 'undefined' && typeof window.AdminCharts !== 'undefined' && typeof window.adminCharts === 'undefined') {
        try {
            window.adminCharts = new window.AdminCharts();

            // Listen for dark mode changes
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
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
                attributeFilter: ['class']
            });
        } catch (e) {
            // Silently ignore initialization errors to avoid breaking page
            // AdminCharts init failed
        }
    }
    
    // Initialize reports charts if on reports page
    if (document.getElementById('monthlyRevenueChart') || document.getElementById('invoicesMonthlyChart')) {
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
        const refreshBtn = document.querySelector('[data-action="refresh-reports"]');
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

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdminCharts;
}

/**
 * Initialize Reports Page Charts
 */
function initReportsCharts() {
    // Check if Chart.js is available
    if (typeof Chart === 'undefined') {
        // Chart.js is not loaded. Charts will not be initialized.
        return;
    }

    // Initialize Monthly Revenue Chart
    const monthlyRevenueCanvas = document.getElementById('monthlyRevenueChart');
    if (monthlyRevenueCanvas) {
        // Check if chart already exists
        if (Chart.getChart(monthlyRevenueCanvas)) {
            Chart.getChart(monthlyRevenueCanvas).destroy();
        }
        
        const chartData = JSON.parse(monthlyRevenueCanvas.getAttribute('data-chart-data') || '{}');
        if (chartData.labels && chartData.datasets) {
            try {
                new Chart(monthlyRevenueCanvas, {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Last 3 Months'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Revenue ($)'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
                // Monthly Revenue Chart initialized successfully
            } catch (error) {
                // Error initializing Monthly Revenue Chart handled gracefully
                monthlyRevenueCanvas.parentElement.classList.add('error');
            }
        } else {
            // Monthly Revenue Chart: No data available
            monthlyRevenueCanvas.parentElement.classList.add('error');
        }
    }

    // Initialize Monthly Licenses Chart
    const monthlyLicensesCanvas = document.getElementById('monthlyLicensesChart');
    if (monthlyLicensesCanvas) {
        // Check if chart already exists
        if (Chart.getChart(monthlyLicensesCanvas)) {
            Chart.getChart(monthlyLicensesCanvas).destroy();
        }
        
        const chartData = JSON.parse(monthlyLicensesCanvas.getAttribute('data-chart-data') || '{}');
        if (chartData.labels && chartData.datasets) {
            try {
                new Chart(monthlyLicensesCanvas, {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.y + ' licenses';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Last 3 Months'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Number of Licenses'
                                }
                            }
                        }
                    }
                });
                // Monthly Licenses Chart initialized successfully
            } catch (error) {
                // Error initializing Monthly Licenses Chart handled gracefully
                monthlyLicensesCanvas.parentElement.classList.add('error');
            }
        } else {
            // Monthly Licenses Chart: No data available
            monthlyLicensesCanvas.parentElement.classList.add('error');
        }
    }

    // Initialize User Registrations Chart
    const userRegistrationsCanvas = document.getElementById('userRegistrationsChart');
    if (userRegistrationsCanvas) {
        // Check if chart already exists
        if (Chart.getChart(userRegistrationsCanvas)) {
            Chart.getChart(userRegistrationsCanvas).destroy();
        }
        
        const chartData = JSON.parse(userRegistrationsCanvas.getAttribute('data-chart-data') || '{}');
        if (chartData.labels && chartData.datasets) {
            try {
                new Chart(userRegistrationsCanvas, {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.y + ' users';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Last 3 Months'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Number of Users'
                                }
                            }
                        }
                    }
                });
                // User Registrations Chart initialized successfully
            } catch (error) {
                // Error initializing User Registrations Chart handled gracefully
                userRegistrationsCanvas.parentElement.classList.add('error');
            }
        } else {
            // User Registrations Chart: No data available
            userRegistrationsCanvas.parentElement.classList.add('error');
        }
    }

    // Initialize System Overview Chart
    const systemOverviewCanvas = document.getElementById('systemOverviewChart');
    if (systemOverviewCanvas) {
        // Check if chart already exists
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
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        }
                    }
                });
                // System Overview Chart initialized successfully
            } catch (error) {
                // Error initializing System Overview Chart handled gracefully
                systemOverviewCanvas.parentElement.classList.add('error');
            }
        } else {
            // System Overview Chart: No data available
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
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                }
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
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }

    // Initialize Invoices Monthly Chart
    const invoicesMonthlyCanvas = document.getElementById('invoicesMonthlyChart');
    if (invoicesMonthlyCanvas) {
        // Check if chart already exists
        if (Chart.getChart(invoicesMonthlyCanvas)) {
            Chart.getChart(invoicesMonthlyCanvas).destroy();
        }
        
        const chartData = JSON.parse(invoicesMonthlyCanvas.getAttribute('data-chart-data') || '{}');
        if (chartData.labels && chartData.datasets) {
            try {
                new Chart(invoicesMonthlyCanvas, {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Last 3 Months'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Invoice Amount ($)'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
                // Invoices Monthly Chart initialized successfully
            } catch (error) {
                // Error initializing Invoices Monthly Chart handled gracefully
                invoicesMonthlyCanvas.parentElement.classList.add('error');
            }
        } else {
            // Invoices Monthly Chart: No data available
            invoicesMonthlyCanvas.parentElement.classList.add('error');
        }
    }
}

// Enhanced Logs Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // View log details modal
    document.addEventListener('click', function(e) {
        if (e.target.closest('[data-action="view-log-details"]')) {
            const logId = e.target.closest('[data-action="view-log-details"]').dataset.logId;
            const modalContent = document.getElementById('logDetailsContent');
            if (modalContent) {
                modalContent.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Log ID:</strong> ${logId}</p>
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
    document.addEventListener('click', function(e) {
        if (e.target.closest('[data-action="export-logs"]')) {
            alert('Export functionality coming soon');
        }
    });
});
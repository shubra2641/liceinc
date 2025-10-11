<?php

declare(strict_types=1);

return [
    // Page Titles
    'title' => 'License Verification Logs',
    'subtitle' => 'Monitor and track all license verification attempts',
    'details_title' => 'License Verification Log Details',

    // Statistics
    'statistics' => 'Statistics',
    'total_attempts' => 'Total Attempts',
    'successful_attempts' => 'Successful',
    'failed_attempts' => 'Failed',
    'recent_failed_attempts' => 'Recent Failed (24h)',
    'unique_domains' => 'Unique Domains',
    'unique_ips' => 'Unique IPs',

    // Table Headers
    'id' => 'ID',
    'purchase_code' => 'Purchase Code',
    'domain' => 'Domain',
    'ip_address' => 'IP Address',
    'status' => 'Status',
    'source' => 'Source',
    'message' => 'Message',
    'date' => 'Date',
    'actions' => 'Actions',

    // Status Values
    'status_success' => 'Success',
    'status_failed' => 'Failed',
    'status_error' => 'Error',

    // Source Values
    'source_install' => 'Installation',
    'source_api' => 'API',
    'source_admin' => 'Admin',
    'source_test' => 'Test',

    // Filters
    'filters' => 'Filters',
    'all_status' => 'All Status',
    'all_sources' => 'All Sources',
    'date_from' => 'Date From',
    'date_to' => 'Date To',
    'apply_filters' => 'Apply Filters',
    'clear_filters' => 'Clear Filters',

    // Actions
    'view_details' => 'View Details',
    'export_csv' => 'Export CSV',
    'back_to_logs' => 'Back to Logs',

    // Details Page
    'basic_information' => 'Basic Information',
    'response_information' => 'Response Information',
    'user_agent_information' => 'User Agent Information',
    'response_data' => 'Response Data',
    'security_information' => 'Security Information',

    // Details Fields
    'purchase_code_hash' => 'Purchase Code Hash',
    'masked_purchase_code' => 'Masked Purchase Code',
    'is_valid' => 'Is Valid',
    'valid' => 'Valid',
    'invalid' => 'Invalid',
    'response_message' => 'Response Message',
    'error_details' => 'Error Details',
    'verified_at' => 'Verified At',
    'created_at' => 'Created At',
    'user_agent' => 'User Agent',

    // Security Analysis
    'ip_address_analysis' => 'IP Address Analysis',
    'verification_context' => 'Verification Context',
    'ip_type' => 'Type',
    'ipv4' => 'IPv4',
    'ipv6' => 'IPv6',
    'unknown' => 'Unknown',
    'result' => 'Result',
    'successful' => 'Successful',
    'failed' => 'Failed',

    // Alerts and Messages
    'suspicious_activity_detected' => 'Suspicious Activity Detected!',
    'suspicious_activity_description' => 'Multiple failed verification attempts detected ' .
        'from the following IP addresses:',
    'failed_attempts_from' => 'failed attempts',
    'last_attempt' => 'last',
    'no_logs_found' => 'No verification logs found',

    // Export
    'export_filename' => 'license_verification_logs',
    'export_success' => 'Export completed successfully',

    // Time Formats
    'time_ago' => 'ago',
    'just_now' => 'Just now',
    'minute_ago' => 'minute ago',
    'minutes_ago' => 'minutes ago',
    'hour_ago' => 'hour ago',
    'hours_ago' => 'hours ago',
    'day_ago' => 'day ago',
    'days_ago' => 'days ago',

    // Badge Classes
    'badge_success' => 'Success',
    'badge_danger' => 'Failed',
    'badge_warning' => 'Error',
    'badge_primary' => 'Installation',
    'badge_info' => 'API',
    'badge_secondary' => 'Admin',

    // Empty States
    'empty_logs_title' => 'No Logs Found',
    'empty_logs_description' => 'No license verification logs match your current filters.',
    'empty_logs_action' => 'Clear Filters',

    // Loading States
    'loading' => 'Loading...',
    'refreshing' => 'Refreshing...',

    // Error Messages
    'error_loading_logs' => 'Error loading logs',
    'error_loading_details' => 'Error loading log details',
    'error_exporting' => 'Error exporting logs',

    // Success Messages
    'logs_loaded' => 'Logs loaded successfully',
    'details_loaded' => 'Log details loaded successfully',
    'export_completed' => 'Export completed successfully',

    // Tooltips
    'tooltip_view_details' => 'View detailed information about this verification attempt',
    'tooltip_export_csv' => 'Export all logs to CSV file',
    'tooltip_refresh' => 'Refresh the logs list',
    'tooltip_filter' => 'Filter logs by various criteria',

    // Pagination
    'showing_results' => 'Showing :from to :to of :total results',
    'previous_page' => 'Previous',
    'next_page' => 'Next',
    'first_page' => 'First',
    'last_page' => 'Last',

    // Cleanup
    'cleanup_old_logs' => 'Clean Old Logs',
    'cleanup_description' => 'Remove logs older than specified days',
    'cleanup_success' => 'Successfully cleaned :count old log entries',
    'cleanup_confirm' => 'Are you sure you want to clean old logs? This action cannot be undone.',

    // Real-time Updates
    'real_time_updates' => 'Real-time Updates',
    'auto_refresh' => 'Auto Refresh',
    'refresh_interval' => 'Refresh every 30 seconds',
];

<?php

declare(strict_types=1);

return [
    // Page Titles
    'title' => 'लाइसेंस सत्यापन लॉग',
    'subtitle' => 'सभी लाइसेंस सत्यापन प्रयासों की निगरानी और ट्रैकिंग',
    'details_title' => 'लाइसेंस सत्यापन लॉग विवरण',

    // Statistics
    'statistics' => 'आंकड़े',
    'total_attempts' => 'कुल प्रयास',
    'successful_attempts' => 'सफल',
    'failed_attempts' => 'असफल',
    'recent_failed_attempts' => 'हाल के असफल (24 घंटे)',
    'unique_domains' => 'अद्वितीय डोमेन',
    'unique_ips' => 'अद्वितीय IP',

    // Table Headers
    'id' => 'आईडी',
    'purchase_code' => 'खरीद कोड',
    'domain' => 'डोमेन',
    'ip_address' => 'IP पता',
    'status' => 'स्थिति',
    'source' => 'स्रोत',
    'message' => 'संदेश',
    'date' => 'तारीख',
    'actions' => 'कार्य',

    // Status Values
    'status_success' => 'सफल',
    'status_failed' => 'असफल',
    'status_error' => 'त्रुटि',

    // Source Values
    'source_install' => 'स्थापना',
    'source_api' => 'API',
    'source_admin' => 'एडमिन',
    'source_test' => 'परीक्षण',

    // Filters
    'filters' => 'फिल्टर',
    'all_status' => 'सभी स्थिति',
    'all_sources' => 'सभी स्रोत',
    'date_from' => 'तारीख से',
    'date_to' => 'तारीख तक',
    'apply_filters' => 'फिल्टर लागू करें',
    'clear_filters' => 'फिल्टर साफ करें',

    // Actions
    'view_details' => 'विवरण देखें',
    'export_csv' => 'CSV निर्यात करें',
    'back_to_logs' => 'लॉग पर वापस जाएं',

    // Details Page
    'basic_information' => 'मूल जानकारी',
    'response_information' => 'प्रतिक्रिया जानकारी',
    'user_agent_information' => 'उपयोगकर्ता एजेंट जानकारी',
    'response_data' => 'प्रतिक्रिया डेटा',
    'security_information' => 'सुरक्षा जानकारी',

    // Details Fields
    'purchase_code_hash' => 'खरीद कोड हैश',
    'masked_purchase_code' => 'मुखौटा खरीद कोड',
    'is_valid' => 'मान्य है',
    'valid' => 'मान्य',
    'invalid' => 'अमान्य',
    'response_message' => 'प्रतिक्रिया संदेश',
    'error_details' => 'त्रुटि विवरण',
    'verified_at' => 'सत्यापित किया गया',
    'created_at' => 'बनाया गया',
    'user_agent' => 'उपयोगकर्ता एजेंट',

    // Security Analysis
    'ip_address_analysis' => 'IP पता विश्लेषण',
    'verification_context' => 'सत्यापन संदर्भ',
    'ip_type' => 'प्रकार',
    'ipv4' => 'IPv4',
    'ipv6' => 'IPv6',
    'unknown' => 'अज्ञात',
    'result' => 'परिणाम',
    'successful' => 'सफल',
    'failed' => 'असफल',

    // Alerts and Messages
    'suspicious_activity_detected' => 'संदिग्ध गतिविधि का पता चला!',
    'suspicious_activity_description' => 'निम्नलिखित IP पतों से कई असफल सत्यापन प्रयासों का पता चला:',
    'failed_attempts_from' => 'असफल प्रयास',
    'last_attempt' => 'अंतिम',
    'no_logs_found' => 'कोई सत्यापन लॉग नहीं मिले',

    // Export
    'export_filename' => 'लाइसेंस_सत्यापन_लॉग',
    'export_success' => 'निर्यात सफलतापूर्वक पूरा हुआ',

    // Time Formats
    'time_ago' => 'पहले',
    'just_now' => 'अभी',
    'minute_ago' => 'मिनट पहले',
    'minutes_ago' => 'मिनट पहले',
    'hour_ago' => 'घंटा पहले',
    'hours_ago' => 'घंटे पहले',
    'day_ago' => 'दिन पहले',
    'days_ago' => 'दिन पहले',

    // Badge Classes
    'badge_success' => 'सफल',
    'badge_danger' => 'असफल',
    'badge_warning' => 'त्रुटि',
    'badge_primary' => 'स्थापना',
    'badge_info' => 'API',
    'badge_secondary' => 'एडमिन',

    // Empty States
    'empty_logs_title' => 'कोई लॉग नहीं मिले',
    'empty_logs_description' => 'आपके वर्तमान फिल्टर से मेल खाने वाले कोई लाइसेंस सत्यापन लॉग नहीं मिले।',
    'empty_logs_action' => 'फिल्टर साफ करें',

    // Loading States
    'loading' => 'लोड हो रहा है...',
    'refreshing' => 'रिफ्रेश हो रहा है...',

    // Error Messages
    'error_loading_logs' => 'लॉग लोड करने में त्रुटि',
    'error_loading_details' => 'लॉग विवरण लोड करने में त्रुटि',
    'error_exporting' => 'लॉग निर्यात करने में त्रुटि',

    // Success Messages
    'logs_loaded' => 'लॉग सफलतापूर्वक लोड हुए',
    'details_loaded' => 'लॉग विवरण सफलतापूर्वक लोड हुए',
    'export_completed' => 'निर्यात सफलतापूर्वक पूरा हुआ',

    // Tooltips
    'tooltip_view_details' => 'इस सत्यापन प्रयास के बारे में विस्तृत जानकारी देखें',
    'tooltip_export_csv' => 'सभी लॉग को CSV फ़ाइल में निर्यात करें',
    'tooltip_refresh' => 'लॉग सूची को रिफ्रेश करें',
    'tooltip_filter' => 'विभिन्न मानदंडों के अनुसार लॉग फिल्टर करें',

    // Pagination
    'showing_results' => ':total परिणामों में से :from से :to दिखा रहे हैं',
    'previous_page' => 'पिछला',
    'next_page' => 'अगला',
    'first_page' => 'पहला',
    'last_page' => 'अंतिम',

    // Cleanup
    'cleanup_old_logs' => 'पुराने लॉग साफ करें',
    'cleanup_description' => 'निर्दिष्ट दिनों से पुराने लॉग हटाएं',
    'cleanup_success' => ':count पुराने लॉग प्रविष्टियां सफलतापूर्वक साफ की गईं',
    'cleanup_confirm' => 'क्या आप वाकई पुराने लॉग साफ करना चाहते हैं? इस क्रिया को पूर्ववत नहीं किया जा सकता।',

    // Real-time Updates
    'real_time_updates' => 'रियल-टाइम अपडेट',
    'auto_refresh' => 'ऑटो रिफ्रेश',
    'refresh_interval' => 'हर 30 सेकंड में रिफ्रेश करें',
];

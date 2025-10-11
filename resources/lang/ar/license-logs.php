<?php

return [
    // Page Titles
    'title' => 'سجلات التحقق من الترخيص',
    'subtitle' => 'مراقبة وتتبع جميع محاولات التحقق من الترخيص',
    'details_title' => 'تفاصيل سجل التحقق من الترخيص',

    // Statistics
    'statistics' => 'الإحصائيات',
    'total_attempts' => 'إجمالي المحاولات',
    'successful_attempts' => 'نجحت',
    'failed_attempts' => 'فشلت',
    'recent_failed_attempts' => 'فشلت مؤخراً (24 ساعة)',
    'unique_domains' => 'النطاقات الفريدة',
    'unique_ips' => 'عناوين IP الفريدة',

    // Table Headers
    'id' => 'المعرف',
    'purchase_code' => 'كود الشراء',
    'domain' => 'النطاق',
    'ip_address' => 'عنوان IP',
    'status' => 'الحالة',
    'source' => 'المصدر',
    'message' => 'الرسالة',
    'date' => 'التاريخ',
    'actions' => 'الإجراءات',

    // Status Values
    'status_success' => 'نجح',
    'status_failed' => 'فشل',
    'status_error' => 'خطأ',

    // Source Values
    'source_install' => 'التثبيت',
    'source_api' => 'واجهة برمجة التطبيقات',
    'source_admin' => 'الإدارة',
    'source_test' => 'اختبار',

    // Filters
    'filters' => 'الفلاتر',
    'all_status' => 'جميع الحالات',
    'all_sources' => 'جميع المصادر',
    'date_from' => 'من تاريخ',
    'date_to' => 'إلى تاريخ',
    'apply_filters' => 'تطبيق الفلاتر',
    'clear_filters' => 'مسح الفلاتر',

    // Actions
    'view_details' => 'عرض التفاصيل',
    'export_csv' => 'تصدير CSV',
    'back_to_logs' => 'العودة للسجلات',

    // Details Page
    'basic_information' => 'المعلومات الأساسية',
    'response_information' => 'معلومات الاستجابة',
    'user_agent_information' => 'معلومات المتصفح',
    'response_data' => 'بيانات الاستجابة',
    'security_information' => 'معلومات الأمان',

    // Details Fields
    'purchase_code_hash' => 'تشفير كود الشراء',
    'masked_purchase_code' => 'كود الشراء المخفي',
    'is_valid' => 'صالح',
    'valid' => 'صالح',
    'invalid' => 'غير صالح',
    'response_message' => 'رسالة الاستجابة',
    'error_details' => 'تفاصيل الخطأ',
    'verified_at' => 'تم التحقق في',
    'created_at' => 'تم الإنشاء في',
    'user_agent' => 'معلومات المتصفح',

    // Security Analysis
    'ip_address_analysis' => 'تحليل عنوان IP',
    'verification_context' => 'سياق التحقق',
    'ip_type' => 'النوع',
    'ipv4' => 'IPv4',
    'ipv6' => 'IPv6',
    'unknown' => 'غير معروف',
    'result' => 'النتيجة',
    'successful' => 'نجح',
    'failed' => 'فشل',

    // Alerts and Messages
    'suspicious_activity_detected' => 'تم اكتشاف نشاط مشبوه!',
    'suspicious_activity_description' => 'تم اكتشاف محاولات متعددة فاشلة للتحقق من العناوين التالية:',
    'failed_attempts_from' => 'محاولات فاشلة من',
    'last_attempt' => 'آخر محاولة',
    'no_logs_found' => 'لم يتم العثور على سجلات تحقق',

    // Export
    'export_filename' => 'سجلات_التحقق_من_الترخيص',
    'export_success' => 'تم التصدير بنجاح',

    // Time Formats
    'time_ago' => 'مضت',
    'just_now' => 'الآن',
    'minute_ago' => 'منذ دقيقة',
    'minutes_ago' => 'منذ دقائق',
    'hour_ago' => 'منذ ساعة',
    'hours_ago' => 'منذ ساعات',
    'day_ago' => 'منذ يوم',
    'days_ago' => 'منذ أيام',

    // Badge Classes
    'badge_success' => 'نجح',
    'badge_danger' => 'فشل',
    'badge_warning' => 'خطأ',
    'badge_primary' => 'تثبيت',
    'badge_info' => 'واجهة برمجة التطبيقات',
    'badge_secondary' => 'إدارة',

    // Empty States
    'empty_logs_title' => 'لا توجد سجلات',
    'empty_logs_description' => 'لا توجد سجلات تحقق من الترخيص تطابق الفلاتر الحالية.',
    'empty_logs_action' => 'مسح الفلاتر',

    // Loading States
    'loading' => 'جاري التحميل...',
    'refreshing' => 'جاري التحديث...',

    // Error Messages
    'error_loading_logs' => 'خطأ في تحميل السجلات',
    'error_loading_details' => 'خطأ في تحميل تفاصيل السجل',
    'error_exporting' => 'خطأ في تصدير السجلات',

    // Success Messages
    'logs_loaded' => 'تم تحميل السجلات بنجاح',
    'details_loaded' => 'تم تحميل تفاصيل السجل بنجاح',
    'export_completed' => 'تم التصدير بنجاح',

    // Tooltips
    'tooltip_view_details' => 'عرض معلومات مفصلة حول محاولة التحقق هذه',
    'tooltip_export_csv' => 'تصدير جميع السجلات إلى ملف CSV',
    'tooltip_refresh' => 'تحديث قائمة السجلات',
    'tooltip_filter' => 'تصفية السجلات حسب معايير مختلفة',

    // Pagination
    'showing_results' => 'عرض :from إلى :to من :total نتيجة',
    'previous_page' => 'السابق',
    'next_page' => 'التالي',
    'first_page' => 'الأول',
    'last_page' => 'الأخير',

    // Cleanup
    'cleanup_old_logs' => 'تنظيف السجلات القديمة',
    'cleanup_description' => 'إزالة السجلات الأقدم من الأيام المحددة',
    'cleanup_success' => 'تم تنظيف :count سجل قديم بنجاح',
    'cleanup_confirm' => 'هل أنت متأكد من أنك تريد تنظيف السجلات القديمة؟ لا يمكن التراجع عن هذا الإجراء.',

    // Real-time Updates
    'real_time_updates' => 'التحديثات في الوقت الفعلي',
    'auto_refresh' => 'تحديث تلقائي',
    'refresh_interval' => 'تحديث كل 30 ثانية',
];

<?php

return [
    // Page Title and Header
    'title' => 'دليل التحقق من الترخيص - للمطورين',
    'page_title' => 'دليل التحقق من الترخيص',
    'page_subtitle' => 'دليل شامل للمطورين حول كيفية تنفيذ التحقق من الترخيص',
    'view_logs' => 'عرض السجلات',

    // Overview Section
    'overview' => 'نظرة عامة',
    'overview_description' => 'يشرح هذا الدليل كيفية تنفيذ التحقق من الترخيص في تطبيقاتك باستخدام نظام التحقق الآمن من الترخيص. يوفر النظام التحقق في الوقت الفعلي ضد خادم الترخيص الخاص بنا لضمان أن التراخيص الصالحة فقط يمكنها استخدام برنامجك.',

    'secure_verification' => 'التحقق الآمن',
    'secure_verification_desc' => 'التحقق في الوقت الفعلي ضد خادم الترخيص الآمن',

    'domain_protection' => 'حماية النطاق',
    'domain_protection_desc' => 'التحقق من الترخيص بناءً على النطاق لمنع الاستخدام غير المصرح به',

    'caching_support' => 'دعم التخزين المؤقت',
    'caching_support_desc' => 'التخزين المؤقت المدمج لتقليل عبء الخادم وتحسين الأداء',

    // Installation Section
    'installation_setup' => 'التثبيت والإعداد',
    'step_1_title' => 'الخطوة 1: تحميل ملف التحقق من الترخيص',
    'step_1_description' => 'قم بتحميل ملف التحقق من الترخيص من لوحة الإدارة أو اتصل بالدعم للحصول على أحدث إصدار.',
    'download_command' => 'أمر التحميل',

    'step_2_title' => 'الخطوة 2: رفع الملف على الخادم',
    'step_2_description' => 'قم برفع ملف التحقق على خادمك. لأسباب أمنية، نوصي بوضعه في مجلد غير قابل للوصول عبر الويب.',
    'directory_structure' => 'هيكل المجلدات الموصى به',

    'step_3_title' => 'الخطوة 3: تكوين التطبيق',
    'step_3_description' => 'أضف التحقق من الترخيص إلى كود التهيئة أو البداية لتطبيقك.',
    'basic_implementation' => 'التنفيذ الأساسي',

    // API Reference Section
    'api_reference' => 'مرجع API',
    'license_verifier_class' => 'فئة LicenseVerifier',
    'verify_license_method' => 'verifyLicense(string $purchaseCode, ?string $domain = null): array',
    'verify_license_description' => 'يتحقق من الترخيص ضد خادم الترخيص الخاص بنا.',
    'parameters' => 'المعاملات:',
    'purchase_code_param' => '$purchaseCode (string, مطلوب) - كود الشراء من Envato',
    'domain_param' => '$domain (string, اختياري) - النطاق للتحقق منه (افتراضي: النطاق الحالي)',
    'returns' => 'الإرجاع:',
    'response_format' => 'تنسيق الاستجابة',

    'cache_license_method' => 'cacheLicenseResult(string $purchaseCode, array $result, int $minutes = 60): void',
    'cache_license_description' => 'يخزن نتيجة التحقق من الترخيص مؤقتاً لتقليل عبء الخادم.',

    'get_cached_method' => 'getCachedLicenseResult(string $purchaseCode): ?array',
    'get_cached_description' => 'استرجاع نتيجة التحقق من الترخيص المخزنة مؤقتاً.',

    'clear_cache_method' => 'clearLicenseCache(string $purchaseCode): void',
    'clear_cache_description' => 'مسح نتيجة التحقق من الترخيص المخزنة مؤقتاً.',

    // Error Codes Section
    'error_codes' => 'أكواد الخطأ',
    'error_codes_description' => 'قد يتم إرجاع أكواد الخطأ التالية عند فشل التحقق من الترخيص:',

    'invalid_format' => 'تنسيق كود الشراء غير صحيح',
    'invalid_format_desc' => 'تنسيق كود الشراء غير صحيح. يجب أن يكون بالتنسيق: XXXX-XXXX-XXXX-XXXX',

    'license_suspended' => 'الترخيص معلق',
    'license_suspended_desc' => 'تم تعليق الترخيص. اتصل بالدعم للحصول على المساعدة.',

    'invalid_purchase_code' => 'كود الشراء غير صحيح',
    'invalid_purchase_code_desc' => 'كود الشراء غير صحيح أو غير موجود في نظامنا.',

    'license_not_found' => 'الترخيص غير موجود',
    'license_not_found_desc' => 'لا يمكن العثور على الترخيص في قاعدة البيانات.',

    'license_expired' => 'الترخيص منتهي الصلاحية',
    'license_expired_desc' => 'انتهت صلاحية الترخيص ويحتاج إلى تجديد.',

    'domain_unauthorized' => 'النطاق غير مصرح به',
    'domain_unauthorized_desc' => 'النطاق الحالي غير مصرح به لهذا الترخيص.',

    'rate_limit' => 'تجاوز حد المحاولات',
    'rate_limit_desc' => 'محاولات تحقق كثيرة جداً. يرجى المحاولة مرة أخرى لاحقاً.',

    'network_error' => 'خطأ في الشبكة',
    'network_error_desc' => 'غير قادر على الاتصال بخادم الترخيص. تحقق من اتصال الإنترنت.',

    // Examples Section
    'implementation_examples' => 'أمثلة التنفيذ',
    'laravel_integration' => 'تكامل Laravel',
    'laravel_middleware' => 'Laravel Middleware',
    'wordpress_integration' => 'تكامل WordPress',
    'wordpress_plugin' => 'إضافة WordPress',
    'standalone_php' => 'تطبيق PHP مستقل',
    'standalone_implementation' => 'تنفيذ مستقل',

    // Best Practices Section
    'best_practices' => 'أفضل الممارسات',
    'security' => 'الأمان',
    'security_tips' => [
        'لا تعرض كود الشراء في كود العميل أبداً',
        'احفظ مدقق الترخيص في مجلد غير قابل للوصول عبر الويب',
        'استخدم HTTPS لجميع طلبات التحقق من الترخيص',
        'نفذ معالجة أخطاء مناسبة لتجنب تسريب المعلومات',
    ],

    'performance' => 'الأداء',
    'performance_tips' => [
        'استخدم التخزين المؤقت لتقليل عبء الخادم',
        'نفذ فترات سماح للعمل دون اتصال لتحسين تجربة المستخدم',
        'خزن التحققات الناجحة لمدة 24 ساعة',
        'خزن التحققات الفاشلة لمدة ساعة واحدة لمنع الإساءة',
    ],

    'user_experience' => 'تجربة المستخدم',
    'user_experience_tips' => [
        'قدم رسائل خطأ واضحة للمستخدمين',
        'نفذ آليات إعادة المحاولة لأخطاء الشبكة',
        'اعرض تعليمات مفيدة لمشاكل الترخيص',
        'قدم معلومات الاتصال للدعم',
    ],

    'implementation' => 'التنفيذ',
    'implementation_tips' => [
        'تحقق من الترخيص عند بدء التطبيق',
        'نفذ إعادة التحقق الدورية',
        'سجل جميع محاولات التحقق للتشخيص',
        'تعامل مع جميع سيناريوهات الخطأ المحتملة بأمان',
    ],

    // Support Section
    'support_resources' => 'الدعم والموارد',
    'documentation' => 'التوثيق',
    'documentation_desc' => 'توثيق API كامل وأدلة التكامل',
    'view_docs' => 'عرض التوثيق',

    'community_support' => 'دعم المجتمع',
    'community_support_desc' => 'احصل على المساعدة من مجتمع المطورين',
    'join_community' => 'انضم للمجتمع',

    'technical_support' => 'الدعم التقني',
    'technical_support_desc' => 'دعم مباشر من فريقنا التقني',
    'create_ticket' => 'إنشاء تذكرة',

    'github_repository' => 'مستودع GitHub',
    'github_repository_desc' => 'الكود المصدري وأمثلة التنفيذ',
    'view_github' => 'عرض على GitHub',
];

<?php

declare(strict_types=1);

return [
    // Page Title and Header
    'title' => 'लाइसेंस सत्यापन गाइड - डेवलपर्स के लिए',
    'page_title' => 'लाइसेंस सत्यापन गाइड',
    'page_subtitle' => 'लाइसेंस सत्यापन को कैसे लागू करें इसके लिए डेवलपर्स के लिए पूर्ण गाइड',
    'view_logs' => 'लॉग देखें',

    // Overview Section
    'overview' => 'अवलोकन',
    'overview_description' => 'यह गाइड बताता है कि हमारे सुरक्षित लाइसेंस सत्यापन सिस्टम का उपयोग करके'.
        'अपने एप्लिकेशन में लाइसेंस सत्यापन कैसे लागू करें।'.
        'सिस्टम यह सुनिश्चित करने के लिए हमारे लाइसेंस सर्वर के खिलाफ रियल-टाइम सत्यापन प्रदान करता है'.
        'कि केवल वैध लाइसेंस ही आपके सॉफ़्टवेयर का उपयोग कर सकते हैं।',

    'secure_verification' => 'सुरक्षित सत्यापन',
    'secure_verification_desc' => 'हमारे सुरक्षित लाइसेंस सर्वर के खिलाफ रियल-टाइम सत्यापन',

    'domain_protection' => 'डोमेन सुरक्षा',
    'domain_protection_desc' => 'अनधिकृत उपयोग को रोकने के लिए डोमेन-आधारित लाइसेंस सत्यापन',

    'caching_support' => 'कैशिंग सहायता',
    'caching_support_desc' => 'सर्वर लोड कम करने और प्रदर्शन बेहतर बनाने के लिए अंतर्निहित कैशिंग',

    // Installation Section
    'installation_setup' => 'स्थापना और सेटअप',
    'step_1_title' => 'चरण 1: लाइसेंस सत्यापन फ़ाइल डाउनलोड करें',
    'step_1_description' => 'अपने एडमिन पैनल से लाइसेंस सत्यापन फ़ाइल डाउनलोड करें'.
        'या नवीनतम संस्करण प्राप्त करने के लिए सहायता से संपर्क करें।',
    'download_command' => 'डाउनलोड कमांड',

    'step_2_title' => 'चरण 2: अपने सर्वर पर अपलोड करें',
    'step_2_description' => 'सत्यापन फ़ाइल को अपने सर्वर पर अपलोड करें।'.
        'सुरक्षा कारणों से, हम इसे गैर-वेब सुलभ निर्देशिका में रखने की सलाह देते हैं।',
    'directory_structure' => 'अनुशंसित निर्देशिका संरचना',

    'step_3_title' => 'चरण 3: अपना एप्लिकेशन कॉन्फ़िगर करें',
    'step_3_description' => 'लाइसेंस सत्यापन को अपने एप्लिकेशन के बूटस्ट्रैप या प्रारंभिक कोड में जोड़ें।',
    'basic_implementation' => 'मूल कार्यान्वयन',

    // API Reference Section
    'api_reference' => 'API संदर्भ',
    'license_verifier_class' => 'LicenseVerifier क्लास',
    'verify_license_method' => 'verifyLicense(string $purchaseCode, ?string $domain = null): array',
    'verify_license_description' => 'हमारे लाइसेंस सर्वर के खिलाफ लाइसेंस सत्यापित करता है।',
    'parameters' => 'पैरामीटर:',
    'purchase_code_param' => '$purchaseCode (string, आवश्यक) - Envato खरीद कोड',
    'domain_param' => '$domain (string, वैकल्पिक) - सत्यापन के लिए डोमेन (डिफ़ॉल्ट: वर्तमान डोमेन)',
    'returns' => 'रिटर्न:',
    'response_format' => 'प्रतिक्रिया प्रारूप',

    'cache_license_method' => 'cacheLicenseResult(string $purchaseCode, array $result, int $minutes = 60): void',
    'cache_license_description' => 'सर्वर लोड कम करने के लिए लाइसेंस सत्यापन परिणाम को कैश करता है।',

    'get_cached_method' => 'getCachedLicenseResult(string $purchaseCode): ?array',
    'get_cached_description' => 'कैश किए गए लाइसेंस सत्यापन परिणाम को पुनः प्राप्त करता है।',

    'clear_cache_method' => 'clearLicenseCache(string $purchaseCode): void',
    'clear_cache_description' => 'कैश किए गए लाइसेंस सत्यापन परिणाम को साफ़ करता है।',

    // Error Codes Section
    'error_codes' => 'त्रुटि कोड',
    'error_codes_description' => 'लाइसेंस सत्यापन विफल होने पर निम्नलिखित त्रुटि कोड लौटाए जा सकते हैं:',

    'invalid_format' => 'अमान्य खरीद कोड प्रारूप',
    'invalid_format_desc' => 'खरीद कोड प्रारूप गलत है। प्रारूप में होना चाहिए: XXXX-XXXX-XXXX-XXXX',

    'license_suspended' => 'लाइसेंस निलंबित',
    'license_suspended_desc' => 'लाइसेंस निलंबित कर दिया गया है। सहायता के लिए संपर्क करें।',

    'invalid_purchase_code' => 'अमान्य खरीद कोड',
    'invalid_purchase_code_desc' => 'खरीद कोड मान्य नहीं है या हमारे सिस्टम में मौजूद नहीं है।',

    'license_not_found' => 'लाइसेंस नहीं मिला',
    'license_not_found_desc' => 'डेटाबेस में लाइसेंस नहीं मिल सका।',

    'license_expired' => 'लाइसेंस समाप्त',
    'license_expired_desc' => 'लाइसेंस समाप्त हो गया है और इसे नवीनीकृत करने की आवश्यकता है।',

    'domain_unauthorized' => 'डोमेन अधिकृत नहीं',
    'domain_unauthorized_desc' => 'वर्तमान डोमेन इस लाइसेंस के लिए अधिकृत नहीं है।',

    'rate_limit' => 'दर सीमा पार',
    'rate_limit_desc' => 'बहुत अधिक सत्यापन प्रयास। कृपया बाद में पुनः प्रयास करें।',

    'network_error' => 'नेटवर्क त्रुटि',
    'network_error_desc' => 'लाइसेंस सर्वर से कनेक्ट नहीं हो सका। अपना इंटरनेट कनेक्शन जांचें।',

    // Examples Section
    'implementation_examples' => 'कार्यान्वयन उदाहरण',
    'laravel_integration' => 'Laravel एकीकरण',
    'laravel_middleware' => 'Laravel मिडलवेयर',
    'wordpress_integration' => 'WordPress एकीकरण',
    'wordpress_plugin' => 'WordPress प्लगइन',
    'standalone_php' => 'स्टैंडअलोन PHP एप्लिकेशन',
    'standalone_implementation' => 'स्टैंडअलोन कार्यान्वयन',

    // Best Practices Section
    'best_practices' => 'सर्वोत्तम अभ्यास',
    'security' => 'सुरक्षा',
    'security_tips' => [
        'कभी भी क्लाइंट-साइड कोड में अपना खरीद कोड एक्सपोज़ न करें',
        'लाइसेंस वेरिफायर को गैर-वेब सुलभ निर्देशिका में स्टोर करें',
        'सभी लाइसेंस सत्यापन अनुरोधों के लिए HTTPS का उपयोग करें',
        'जानकारी रिसाव से बचने के लिए उचित त्रुटि हैंडलिंग लागू करें',
    ],

    'performance' => 'प्रदर्शन',
    'performance_tips' => [
        'सर्वर लोड कम करने के लिए कैशिंग का उपयोग करें',
        'बेहतर उपयोगकर्ता अनुभव के लिए ऑफ़लाइन ग्रेस पीरियड लागू करें',
        'सफल सत्यापन को 24 घंटे के लिए कैश करें',
        'दुरुपयोग को रोकने के लिए विफल सत्यापन को 1 घंटे के लिए कैश करें',
    ],

    'user_experience' => 'उपयोगकर्ता अनुभव',
    'user_experience_tips' => [
        'उपयोगकर्ताओं को स्पष्ट त्रुटि संदेश प्रदान करें',
        'नेटवर्क विफलताओं के लिए पुनः प्रयास तंत्र लागू करें',
        'लाइसेंस समस्याओं के लिए सहायक निर्देश दिखाएं',
        'सहायता के लिए संपर्क जानकारी प्रदान करें',
    ],

    'implementation' => 'कार्यान्वयन',
    'implementation_tips' => [
        'एप्लिकेशन स्टार्टअप पर लाइसेंस सत्यापित करें',
        'आवधिक पुनः सत्यापन लागू करें',
        'डिबगिंग के लिए सभी सत्यापन प्रयासों को लॉग करें',
        'सभी संभावित त्रुटि परिदृश्यों को सुरक्षित रूप से हैंडल करें',
    ],

    // Support Section
    'support_resources' => 'सहायता और संसाधन',
    'documentation' => 'प्रलेखन',
    'documentation_desc' => 'पूर्ण API प्रलेखन और एकीकरण गाइड',
    'view_docs' => 'डॉक्स देखें',

    'community_support' => 'समुदाय सहायता',
    'community_support_desc' => 'हमारे डेवलपर समुदाय से सहायता प्राप्त करें',
    'join_community' => 'समुदाय में शामिल हों',

    'technical_support' => 'तकनीकी सहायता',
    'technical_support_desc' => 'हमारी तकनीकी टीम से प्रत्यक्ष सहायता',
    'create_ticket' => 'टिकट बनाएं',

    'github_repository' => 'GitHub रिपॉजिटरी',
    'github_repository_desc' => 'स्रोत कोड और कार्यान्वयन उदाहरण',
    'view_github' => 'GitHub पर देखें',
];

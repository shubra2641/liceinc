<?php

/**
 * Navigation Helper Functions with enhanced security.
 *
 * This     /**
     * Generate breadcrumbs for the current route with enhanced security.
     *
     * Creates a breadcrumb navigation structur    /**
     * Get available languages from the lang directory with enhanced security.
     *
     * Scans the language directory and returns available languages with proper
     * error handling and security measures.
     *
     * @return array Array of language information with code, name, flag, and native name
     *
     * @version 1.0.6
     */

use App\Helpers\SecureFileHelper;

if (! function_exists('is_active_route')) {
    /**
     * Check if the current route matches the given route name with enhanced security.
     *
     * Validates and checks if the current route matches the specified route name
     * with proper input sanitization and security measures.
     *
     * @param  string  $routeName  The route name to check against
     *
     * @return bool True if the route matches, false otherwise
     *
     * @throws InvalidArgumentException When route name is invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    function is_active_route(string $routeName): bool
    {
        if (empty($routeName)) {
            throw new InvalidArgumentException('Route name cannot be empty');
        }
        $sanitizedRouteName = htmlspecialchars(trim($routeName), ENT_QUOTES, 'UTF-8');
        return request()->routeIs($sanitizedRouteName);
    }
}
if (! function_exists('is_active_route_pattern')) {
    /**
     * Check if the current route matches the given pattern with enhanced security.
     *
     * Validates and checks if the current route matches the specified pattern
     * with proper input sanitization and security measures.
     *
     * @param  string  $pattern  The route pattern to check against
     *
     * @return bool True if the route matches the pattern, false otherwise
     *
     * @throws InvalidArgumentException When pattern is invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    function is_active_route_pattern(string $pattern): bool
    {
        if (empty($pattern)) {
            throw new InvalidArgumentException('Route pattern cannot be empty');
        }
        $sanitizedPattern = htmlspecialchars(trim($pattern), ENT_QUOTES, 'UTF-8');
        return request()->routeIs($sanitizedPattern);
    }
}
if (! function_exists('get_breadcrumbs')) {
    /**
     * Generate breadcrumbs for the current route with enhanced security.
     *
     * Creates a breadcrumb navigation structure based on the current route
     * with proper input sanitization and error handling.
     *
     * @return array Array of breadcrumb items with name, url, and active status
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    /** @return array<int, array<string, mixed>> */
    function get_breadcrumbs(): array
    {
        try {
            $route = request()->route();
            $breadcrumbs = [];
            if ($route && $route->getName()) {
                $routeName = $route->getName();
                $segments = explode('.', $routeName);
                $currentPath = '';
                foreach ($segments as $segment) {
                    // Ensure spacing around concatenation and assignment per PSR-12
                    $currentPath = $currentPath . ($currentPath ? '.' : '') . $segment;
                    try {
                        $breadcrumbs[] = [
                            'name' => htmlspecialchars(
                                ucfirst(str_replace(['_', '-'], ' ', $segment)),
                                ENT_QUOTES,
                                'UTF-8',
                            ),
                            'url' => route($currentPath),
                            'active' => $currentPath === $routeName,
                        ];
                    } catch (Exception $e) {
                        // Skip invalid routes
                        continue;
                    }
                }
            }
            return $breadcrumbs;
        } catch (Exception $e) {
            return [];
        }
    }
}
if (! function_exists('get_navigation_tree')) {
    /**
     * Get navigation tree structure with enhanced security.
     *
     * Returns a structured navigation tree for the admin panel with proper
     * sanitization and security measures.
     *
     * @return array Array of navigation items with name, route, icon, and children
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    /** @return array<int, array<string, mixed>> */
    function get_navigation_tree(): array
    {
        return [
            [
                'name' => 'Dashboard',
                'route' => 'admin.dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'children' => [],
            ],
            [
                'name' => 'Products',
                'route' => 'admin.products.index',
                'icon' => 'fas fa-box',
                'children' => [
                    ['name' => 'All Products', 'route' => 'admin.products.index'],
                    ['name' => 'Add Product', 'route' => 'admin.products.create'],
                    ['name' => 'Categories', 'route' => 'admin.product-categories.index'],
                ],
            ],
            [
                'name' => 'Licenses',
                'route' => 'admin.licenses.index',
                'icon' => 'fas fa-key',
                'children' => [
                    ['name' => 'All Licenses', 'route' => 'admin.licenses.index'],
                    ['name' => 'License Logs', 'route' => 'admin.license-logs.index'],
                ],
            ],
            [
                'name' => 'Customers',
                'route' => 'admin.customers.index',
                'icon' => 'fas fa-users',
                'children' => [],
            ],
            [
                'name' => 'Support',
                'route' => 'admin.tickets.index',
                'icon' => 'fas fa-headset',
                'children' => [
                    ['name' => 'All Tickets', 'route' => 'admin.tickets.index'],
                    ['name' => 'Categories', 'route' => 'admin.ticket-categories.index'],
                ],
            ],
            [
                'name' => 'Knowledge Base',
                'route' => 'admin.kb.index',
                'icon' => 'fas fa-book',
                'children' => [
                    ['name' => 'Articles', 'route' => 'admin.kb.index'],
                    ['name' => 'Categories', 'route' => 'admin.kb-categories.index'],
                ],
            ],
            [
                'name' => 'Settings',
                'route' => 'admin.settings.index',
                'icon' => 'fas fa-cog',
                'children' => [],
            ],
        ];
    }
}
if (function_exists('get_available_languages') === false) {
    /**
     * Get available languages from the lang directory with enhanced security.
     *
     * Scans the language directory and returns available languages with proper
     * error handling and security measures.
     *
     * @return array Array of language information with code, name, flag, and native name
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    /** @return array<int, array<string, string>> */
    function get_available_languages(): array
    {
        try {
            $languages = [];
            $langPath = resource_path('lang');
            if (SecureFileHelper::isDirectory($langPath) === false) {
                return $languages;
            }
            $directories = array_diff(scandir($langPath), ['.', '..']);
            foreach ($directories as $dir) {
                if (SecureFileHelper::isDirectory($langPath . DIRECTORY_SEPARATOR . $dir)) {
                    $sanitizedCode = htmlspecialchars(trim($dir), ENT_QUOTES, 'UTF-8');
                    if (! empty($sanitizedCode)) {
                        $languages[] = [
                            'code' => $sanitizedCode,
                            'name' => get_language_name($sanitizedCode),
                            'flag' => get_language_flag($sanitizedCode),
                            'native_name' => get_language_native_name($sanitizedCode),
                        ];
                    }
                }
            }
            return $languages;
        } catch (Exception $e) {
            return [];
        }
    }
}
if (! function_exists('get_language_name')) {
    /**
     * Get language name by code with enhanced security.
     *
     * Returns the English name for a given language code with proper
     * input validation and security measures.
     *
     * @param  string  $code  The language code to get the name for
     *
     * @return string The English name of the language
     *
     * @throws InvalidArgumentException When language code is invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    function get_language_name(string $code): string
    {
        $names = [
            'en' => 'English',
            'ar' => 'Arabic',
            'fr' => 'French',
            'es' => 'Spanish',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
            'zh' => 'Chinese',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'tr' => 'Turkish',
            'nl' => 'Dutch',
            'sv' => 'Swedish',
            'no' => 'Norwegian',
            'da' => 'Danish',
            'fi' => 'Finnish',
            'pl' => 'Polish',
            'cs' => 'Czech',
            'hu' => 'Hungarian',
            'ro' => 'Romanian',
            'bg' => 'Bulgarian',
            'hr' => 'Croatian',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'et' => 'Estonian',
            'lv' => 'Latvian',
            'lt' => 'Lithuanian',
            'el' => 'Greek',
            'he' => 'Hebrew',
            'th' => 'Thai',
            'vi' => 'Vietnamese',
            'id' => 'Indonesian',
            'ms' => 'Malay',
            'tl' => 'Filipino',
            'hi' => 'Hindi',
            'bn' => 'Bengali',
            'ta' => 'Tamil',
            'te' => 'Telugu',
            'ml' => 'Malayalam',
            'kn' => 'Kannada',
            'gu' => 'Gujarati',
            'pa' => 'Punjabi',
            'mr' => 'Marathi',
            'ne' => 'Nepali',
            'si' => 'Sinhala',
            'my' => 'Burmese',
            'km' => 'Khmer',
            'lo' => 'Lao',
            'ka' => 'Georgian',
            'am' => 'Amharic',
            'sw' => 'Swahili',
            'zu' => 'Zulu',
            'af' => 'Afrikaans',
            'sq' => 'Albanian',
            'az' => 'Azerbaijani',
            'be' => 'Belarusian',
            'bs' => 'Bosnian',
            'ca' => 'Catalan',
            'cy' => 'Welsh',
            'eu' => 'Basque',
            'fa' => 'Persian',
            'gl' => 'Galician',
            'is' => 'Icelandic',
            'mk' => 'Macedonian',
            'mt' => 'Maltese',
            'sr' => 'Serbian',
            'uk' => 'Ukrainian',
            'ur' => 'Urdu',
            'uz' => 'Uzbek',
            'kk' => 'Kazakh',
            'ky' => 'Kyrgyz',
            'tg' => 'Tajik',
            'mn' => 'Mongolian',
            'bo' => 'Tibetan',
            'dz' => 'Dzongkha',
            'or' => 'Odia',
            'as' => 'Assamese',
            'mni' => 'Manipuri',
            'kok' => 'Konkani',
            'mai' => 'Maithili',
            'sat' => 'Santali',
            'brx' => 'Bodo',
            'gom' => 'Goan Konkani',
            'ks' => 'Kashmiri',
            'sd' => 'Sindhi',
            'doi' => 'Dogri',
            'mni-Mtei' => 'Meitei',
            'lus' => 'Mizo',
            'njo' => 'Ao',
            'njz' => 'Nyishi',
            'grt' => 'Garo',
            'kha' => 'Khasi',
            'mni-Beng' => 'Manipuri (Bengali)',
            'mni-Latn' => 'Manipuri (Latin)',
        ];
        if (empty($code)) {
            throw new InvalidArgumentException('Language code cannot be empty');
        }
        $sanitizedCode = htmlspecialchars(trim($code), ENT_QUOTES, 'UTF-8');
        return $names[$sanitizedCode] ?? ucfirst($sanitizedCode);
    }
}
if (! function_exists('get_language_flag')) {
    /**
     * Get language flag emoji by code with enhanced security.
     *
     * Returns the flag emoji for a given language code with proper
     * input validation and security measures.
     *
     * @param  string  $code  The language code to get the flag for
     *
     * @return string The flag emoji for the language
     *
     * @throws InvalidArgumentException When language code is invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    function get_language_flag(string $code): string
    {
        $flags = [
            'en' => '🇺🇸',
            'ar' => '🇸🇦',
            'fr' => '🇫🇷',
            'es' => '🇪🇸',
            'de' => '🇩🇪',
            'it' => '🇮🇹',
            'pt' => '🇵🇹',
            'ru' => '🇷🇺',
            'zh' => '🇨🇳',
            'ja' => '🇯🇵',
            'ko' => '🇰🇷',
            'tr' => '🇹🇷',
            'nl' => '🇳🇱',
            'sv' => '🇸🇪',
            'no' => '🇳🇴',
            'da' => '🇩🇰',
            'fi' => '🇫🇮',
            'pl' => '🇵🇱',
            'cs' => '🇨🇿',
            'hu' => '🇭🇺',
            'ro' => '🇷🇴',
            'bg' => '🇧🇬',
            'hr' => '🇭🇷',
            'sk' => '🇸🇰',
            'sl' => '🇸🇮',
            'et' => '🇪🇪',
            'lv' => '🇱🇻',
            'lt' => '🇱🇹',
            'el' => '🇬🇷',
            'he' => '🇮🇱',
            'th' => '🇹🇭',
            'vi' => '🇻🇳',
            'id' => '🇮🇩',
            'ms' => '🇲🇾',
            'tl' => '🇵🇭',
            'hi' => '🇮🇳',
            'bn' => '🇧🇩',
            'ta' => '🇮🇳',
            'te' => '🇮🇳',
            'ml' => '🇮🇳',
            'kn' => '🇮🇳',
            'gu' => '🇮🇳',
            'pa' => '🇮🇳',
            'mr' => '🇮🇳',
            'ne' => '🇳🇵',
            'si' => '🇱🇰',
            'my' => '🇲🇲',
            'km' => '🇰🇭',
            'lo' => '🇱🇦',
            'ka' => '🇬🇪',
            'am' => '🇪🇹',
            'sw' => '🇹🇿',
            'zu' => '🇿🇦',
            'af' => '🇿🇦',
            'sq' => '🇦🇱',
            'az' => '🇦🇿',
            'be' => '🇧🇾',
            'bs' => '🇧🇦',
            'ca' => '🇪🇸',
            'cy' => '🇬🇧',
            'eu' => '🇪🇸',
            'fa' => '🇮🇷',
            'gl' => '🇪🇸',
            'is' => '🇮🇸',
            'mk' => '🇲🇰',
            'mt' => '🇲🇹',
            'sr' => '🇷🇸',
            'uk' => '🇺🇦',
            'ur' => '🇵🇰',
            'uz' => '🇺🇿',
            'kk' => '🇰🇿',
            'ky' => '🇰🇬',
            'tg' => '🇹🇯',
            'mn' => '🇲🇳',
            'bo' => '🇨🇳',
            'dz' => '🇧🇹',
            'or' => '🇮🇳',
            'as' => '🇮🇳',
            'mni' => '🇮🇳',
            'kok' => '🇮🇳',
            'mai' => '🇮🇳',
            'sat' => '🇮🇳',
            'brx' => '🇮🇳',
            'gom' => '🇮🇳',
            'ks' => '🇮🇳',
            'sd' => '🇵🇰',
            'doi' => '🇮🇳',
            'mni-Mtei' => '🇮🇳',
            'lus' => '🇮🇳',
            'njo' => '🇮🇳',
            'njz' => '🇮🇳',
            'grt' => '🇮🇳',
            'kha' => '🇮🇳',
            'mni-Beng' => '🇮🇳',
            'mni-Latn' => '🇮🇳',
        ];
        if (empty($code)) {
            throw new InvalidArgumentException('Language code cannot be empty');
        }
        $sanitizedCode = htmlspecialchars(trim($code), ENT_QUOTES, 'UTF-8');
        return $flags[$sanitizedCode] ?? '🌐';
    }
}
if (! function_exists('get_language_native_name')) {
    /**
     * Get language native name by code with enhanced security.
     *
     * Returns the native name for a given language code with proper
     * input validation and security measures.
     *
     * @param  string  $code  The language code to get the native name for
     *
     * @return string The native name of the language
     *
     * @throws InvalidArgumentException When language code is invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    function get_language_native_name(string $code): string
    {
        $nativeNames = [
            'en' => 'English',
            'ar' => 'العربية',
            'fr' => 'Français',
            'es' => 'Español',
            'de' => 'Deutsch',
            'it' => 'Italiano',
            'pt' => 'Português',
            'ru' => 'Русский',
            'zh' => '中文',
            'ja' => '日本語',
            'ko' => '한국어',
            'tr' => 'Türkçe',
            'nl' => 'Nederlands',
            'sv' => 'Svenska',
            'no' => 'Norsk',
            'da' => 'Dansk',
            'fi' => 'Suomi',
            'pl' => 'Polski',
            'cs' => 'Čeština',
            'hu' => 'Magyar',
            'ro' => 'Română',
            'bg' => 'Български',
            'hr' => 'Hrvatski',
            'sk' => 'Slovenčina',
            'sl' => 'Slovenščina',
            'et' => 'Eesti',
            'lv' => 'Latviešu',
            'lt' => 'Lietuvių',
            'el' => 'Ελληνικά',
            'he' => 'עברית',
            'th' => 'ไทย',
            'vi' => 'Tiếng Việt',
            'id' => 'Bahasa Indonesia',
            'ms' => 'Bahasa Melayu',
            'tl' => 'Filipino',
            'hi' => 'हिन्दी',
            'bn' => 'বাংলা',
            'ta' => 'தமிழ்',
            'te' => 'తెలుగు',
            'ml' => 'മലയാളം',
            'kn' => 'ಕನ್ನಡ',
            'gu' => 'ગુજરાતી',
            'pa' => 'ਪੰਜਾਬੀ',
            'mr' => 'मराठी',
            'ne' => 'नेपाली',
            'si' => 'සිංහල',
            'my' => 'မြန်မာ',
            'km' => 'ខ្មែរ',
            'lo' => 'ລາວ',
            'ka' => 'ქართული',
            'am' => 'አማርኛ',
            'sw' => 'Kiswahili',
            'zu' => 'IsiZulu',
            'af' => 'Afrikaans',
            'sq' => 'Shqip',
            'az' => 'Azərbaycan',
            'be' => 'Беларуская',
            'bs' => 'Bosanski',
            'ca' => 'Català',
            'cy' => 'Cymraeg',
            'eu' => 'Euskera',
            'fa' => 'فارسی',
            'gl' => 'Galego',
            'is' => 'Íslenska',
            'mk' => 'Македонски',
            'mt' => 'Malti',
            'sr' => 'Српски',
            'uk' => 'Українська',
            'ur' => 'اردو',
            'uz' => 'Oʻzbek',
            'kk' => 'Қазақ',
            'ky' => 'Кыргызча',
            'tg' => 'Тоҷикӣ',
            'mn' => 'Монгол',
            'bo' => 'བོད་ཡིག',
            'dz' => 'རྫོང་ཁ',
            'or' => 'ଓଡ଼ିଆ',
            'as' => 'অসমীয়া',
            'mni' => 'ꯃꯤꯇꯩꯂꯣꯟ',
            'kok' => 'कोंकणी',
            'mai' => 'मैथिली',
            'sat' => 'ᱥᱟᱱᱛᱟᱲᱤ',
            'brx' => 'बड़ो',
            'gom' => 'कोंकणी',
            'ks' => 'کٲشُر',
            'sd' => 'سنڌي',
            'doi' => 'डोगरी',
            'mni-Mtei' => 'ꯃꯤꯇꯩꯂꯣꯟ',
            'lus' => 'Mizo',
            'njo' => 'Ao',
            'njz' => 'Nyishi',
            'grt' => 'Garo',
            'kha' => 'Khasi',
            'mni-Beng' => 'মণিপুরী',
            'mni-Latn' => 'Manipuri',
        ];
        if (empty($code)) {
            throw new InvalidArgumentException('Language code cannot be empty');
        }
        $sanitizedCode = htmlspecialchars(trim($code), ENT_QUOTES, 'UTF-8');
        return $nativeNames[$sanitizedCode] ?? get_language_name($sanitizedCode);
    }
}

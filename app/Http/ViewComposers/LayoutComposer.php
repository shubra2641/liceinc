<?php
namespace App\Http\ViewComposers;
use App\Http\Controllers\LanguageController;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
/**
 * Layout Composer with enhanced security and performance.
 *
 * This view composer binds layout data to views including site settings,
 * SEO configuration, and other global layout variables with comprehensive
 * security measures and performance optimization.
 *
 * Features:
 * - Site settings and configuration binding
 * - SEO settings management and validation
 * - Performance optimization with caching
 * - Comprehensive error handling and logging
 * - Enhanced security measures for data binding
 * - Input validation and sanitization
 * - Clean and maintainable code structure
 *
 *
 * @example
 * // Register in AppServiceProvider
 * View::composer('layouts.*', LayoutComposer::class);
 *
 * // Use in Blade template
 * {{ $siteName }} - {{ $siteSeoTitle }}
 */
class LayoutComposer
{
    /**
     * Cache key prefix for layout data.
     */
    private const CACHE_PREFIX = 'layout_composer_';
    /**
     * Cache duration in minutes.
     */
    private const CACHE_DURATION = 60;
    /**
     * Bind data to the view with enhanced security and performance.
     *
     * Composes layout data including site settings, SEO configuration,
     * and other global variables with proper error handling and caching.
     *
     * @param  View  $view  The view instance to bind data to
     *
     * @throws \Exception When data binding fails
     *
     * @example
     * $composer = new LayoutComposer();
     * $composer->compose($view);
     */
    public function compose(View $view): void
    {
        try {
            $layoutData = $this->getLayoutData();
            $view->with($layoutData);
        } catch (\Exception $e) {
            Log::error('Layout composer failed to bind data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'view_name' => $view->getName(),
            ]);
            // Provide fallback data to prevent view errors
            $view->with($this->getFallbackData());
        }
    }
    /**
     * Get layout data with caching and error handling.
     *
     * Retrieves all layout-related data including site settings and SEO
     * configuration with performance optimization through caching.
     *
     * @return array<string, mixed> Array of layout data
     *
     * @throws \Exception When data retrieval fails
     */
    private function getLayoutData(): array
    {
        return Cache::remember(
            self::CACHE_PREFIX.'layout_data',
            self::CACHE_DURATION,
            function () {
                return [
                    'siteName' => $this->getSiteName(),
                    'siteLogo' => $this->getSiteLogo(),
                    'siteSeoTitle' => $this->getSiteSeoTitle(),
                    'siteSeoDescription' => $this->getSiteSeoDescription(),
                    'ogImage' => $this->getOgImage(),
                    'kbSeoTitle' => $this->getKbSeoTitle(),
                    'kbSeoDescription' => $this->getKbSeoDescription(),
                    'ticketsSeoTitle' => $this->getTicketsSeoTitle(),
                    'ticketsSeoDescription' => $this->getTicketsSeoDescription(),
                    'availableLanguages' => $this->getAvailableLanguages(),
                    'currentLocale' => $this->getCurrentLocale(),
                    'currentLanguage' => $this->getCurrentLanguage(),
                    'otherLanguage' => $this->getOtherLanguage(),
                    'preloaderSettings' => $this->getPreloaderSettings(),
                ];
            },
        );
    }
    /**
     * Get site name with validation and fallback.
     *
     * @return string The site name
     */
    private function getSiteName(): string
    {
        $siteName = Setting::get('site_name', config('app.name', 'Laravel'));
        return $this->sanitizeOutput($siteName);
    }
    /**
     * Get site logo with validation.
     *
     * @return string|null The site logo URL or null
     */
    private function getSiteLogo(): ?string
    {
        $siteLogo = Setting::get('site_logo', null);
        return $siteLogo ? $this->sanitizeOutput($siteLogo) : null;
    }
    /**
     * Get site SEO title with validation.
     *
     * @return string|null The site SEO title or null
     */
    private function getSiteSeoTitle(): ?string
    {
        $seoTitle = Setting::get('seo_site_title', null);
        return $seoTitle ? $this->sanitizeOutput($seoTitle) : null;
    }
    /**
     * Get site SEO description with validation.
     *
     * @return string|null The site SEO description or null
     */
    private function getSiteSeoDescription(): ?string
    {
        $seoDescription = Setting::get('seo_site_description', null);
        return $seoDescription ? $this->sanitizeOutput($seoDescription) : null;
    }
    /**
     * Get Open Graph image with validation.
     *
     * @return string|null The Open Graph image URL or null
     */
    private function getOgImage(): ?string
    {
        $ogImage = Setting::get('seo_og_image', null);
        return $ogImage ? $this->sanitizeOutput($ogImage) : null;
    }
    /**
     * Get Knowledge Base SEO title with validation.
     *
     * @return string|null The KB SEO title or null
     */
    private function getKbSeoTitle(): ?string
    {
        $kbSeoTitle = Setting::get('seo_kb_title', null);
        return $kbSeoTitle ? $this->sanitizeOutput($kbSeoTitle) : null;
    }
    /**
     * Get Knowledge Base SEO description with validation.
     *
     * @return string|null The KB SEO description or null
     */
    private function getKbSeoDescription(): ?string
    {
        $kbSeoDescription = Setting::get('seo_kb_description', null);
        return $kbSeoDescription ? $this->sanitizeOutput($kbSeoDescription) : null;
    }
    /**
     * Get Tickets SEO title with validation.
     *
     * @return string|null The Tickets SEO title or null
     */
    private function getTicketsSeoTitle(): ?string
    {
        $ticketsSeoTitle = Setting::get('seo_tickets_title', null);
        return $ticketsSeoTitle ? $this->sanitizeOutput($ticketsSeoTitle) : null;
    }
    /**
     * Get Tickets SEO description with validation.
     *
     * @return string|null The Tickets SEO description or null
     */
    private function getTicketsSeoDescription(): ?string
    {
        $ticketsSeoDescription = Setting::get('seo_tickets_description', null);
        return $ticketsSeoDescription ? $this->sanitizeOutput($ticketsSeoDescription) : null;
    }
    /**
     * Get available languages with metadata.
     *
     * @return array The available languages
     */
    private function getAvailableLanguages(): array
    {
        return LanguageController::getAvailableLanguagesWithMetadata();
    }
    /**
     * Get current locale.
     *
     * @return string The current locale
     */
    private function getCurrentLocale(): string
    {
        return app()->getLocale();
    }
    /**
     * Get current language metadata.
     *
     * @return array|null The current language metadata
     */
    private function getCurrentLanguage(): ?array
    {
        $currentLocale = $this->getCurrentLocale();
        $availableLanguages = $this->getAvailableLanguages();
        return collect($availableLanguages)->firstWhere('code', $currentLocale);
    }
    private function getOtherLanguage(): ?array
    {
        $availableLanguages = $this->getAvailableLanguages();
        $currentLocale = $this->getCurrentLocale();
        return collect($availableLanguages)->firstWhere('code', '!=', $currentLocale);
    }
    /**
     * Get preloader settings.
     *
     * @return array The preloader settings
     */
    private function getPreloaderSettings(): array
    {
        $settings = Setting::first();
        return [
            'preloaderEnabled' => $settings->preloader_enabled ?? true,
            'preloaderType' => $settings->preloader_type ?? 'spinner',
            'preloaderColor' => $settings->preloader_color ?? '#3b82f6',
            'preloaderBgColor' => $settings->preloader_background_color ?? '#ffffff',
            'preloaderDuration' => $settings->preloader_duration ?? 2000,
            'preloaderText' => trans('app.Loading...'),
            'siteLogo' => $settings->site_logo ?? null,
            'logoText' => $settings->logo_text ?? config('app.name'),
            'logoShowText' => $settings->logo_show_text ?? true,
        ];
    }
    /**
     * Get fallback data for error scenarios.
     *
     * Provides minimal fallback data when the main data retrieval fails
     * to prevent view rendering errors.
     *
     * @return array<string, mixed> Array of fallback data
     */
    private function getFallbackData(): array
    {
        return [
            'siteName' => config('app.name', 'Laravel'),
            'siteLogo' => null,
            'siteSeoTitle' => null,
            'siteSeoDescription' => null,
            'ogImage' => null,
            'kbSeoTitle' => null,
            'kbSeoDescription' => null,
            'ticketsSeoTitle' => null,
            'ticketsSeoDescription' => null,
            'availableLanguages' => [
                ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'flag' => '🇺🇸'],
                ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'flag' => '🇸🇦'],
            ],
            'currentLocale' => 'en',
                'currentLanguage' => ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'flag' => '🇺🇸'],
                'otherLanguage' => ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'flag' => '🇸🇦'],
        ];
    }
    /**
     * Clear layout composer cache.
     *
     * Clears the cached layout data to force refresh on next request.
     */
    public static function clearCache(): void
    {
        try {
            Cache::forget(self::CACHE_PREFIX.'layout_data');
        } catch (\Exception $e) {
            Log::error('Failed to clear layout composer cache', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
    /**
     * Sanitize output to prevent XSS attacks.
     *
     * @param  string  $output  The output to sanitize
     *
     * @return string The sanitized output
     */
    private function sanitizeOutput(string $output): string
    {
        return htmlspecialchars(trim($output), ENT_QUOTES, 'UTF-8');
    }
}

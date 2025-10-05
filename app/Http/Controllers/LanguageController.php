<?php
namespace App\Http\Controllers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
/**
 * Language Controller.
 *
 * Handles switching the application language with validation and security.
 */
class LanguageController extends Controller
{
    /**
     * Switch application language.
     *
     * @param  string  $locale  Requested locale code
     *
     * @return RedirectResponse Redirects back with status message
     *
     * @throws \Exception On unexpected failure
     */
    public function switch(string $locale): RedirectResponse
    {
        try {
            $sanitizedLocale = htmlspecialchars(trim($locale), ENT_QUOTES, 'UTF-8');
            if (! preg_match('/^[a-z]{2}(_[A-Z]{2})?$/', $sanitizedLocale)) {
                Log::warning('Invalid locale format attempted', [
                    'locale' => $sanitizedLocale,
                ]);
                return back()->with('error', 'Invalid language format.');
            }
            $availableLanguages = $this->getAvailableLanguages();
            if (! in_array($sanitizedLocale, $availableLanguages, true)) {
                Log::warning('Unsupported locale attempted', [
                    'locale' => $sanitizedLocale,
                    'available_languages' => $availableLanguages,
                ]);
                return back()->with('error', 'Language not supported.');
            }
            session(['locale' => $sanitizedLocale]);
            return back();
        } catch (\Exception $e) {
            Log::error('Language switch error', [
                'error' => $e->getMessage(),
                'locale' => $locale,
            ]);
            return back()->with('error', 'Failed to switch language. Please try again.');
        }
    }
    /**
     * Get available languages from resources/lang directory.
     *
     * @return array<string>
     */
    private function getAvailableLanguages(): array
    {
        $available = [];
        $langPath = resource_path('lang');
        if (Storage::disk('local')->exists($langPath)) {
            $directories = array_diff(scandir($langPath), ['.', '..']);
            foreach ($directories as $dir) {
                if (Storage::disk('local')->exists($langPath.DIRECTORY_SEPARATOR.$dir)) {
                    $available[] = $dir;
                }
            }
        }
        if (empty($available)) {
            $available = ['en', 'ar'];
        }
        return $available;
    }
    /**
     * Get available languages with metadata for views.
     *
     * @return array The available languages with metadata
     */
    public static function getAvailableLanguagesWithMetadata(): array
    {
        $languages = [];
        $langPath = resource_path('lang');
        if (is_dir($langPath)) {
            $directories = array_diff(scandir($langPath), ['.', '..']);
            foreach ($directories as $dir) {
                if (is_dir($langPath.DIRECTORY_SEPARATOR.$dir)) {
                    $languages[] = [
                        'code' => $dir,
                        'name' => ucfirst($dir),
                        'native_name' => ucfirst($dir),
                        'flag' => $dir === 'ar' ? '🇸🇦' : '🇺🇸',
                    ];
                }
            }
        }
        if (empty($languages)) {
            $languages = [
                ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'flag' => '🇺🇸'],
                ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'flag' => '🇸🇦'],
            ];
        }
        return $languages;
    }
}

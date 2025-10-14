<?php

namespace App\Http\ViewComposers;

use App\Http\Controllers\LanguageController;
use App\Models\Setting;
use Illuminate\View\View;

class LayoutComposer
{
    private ?object $settings = null;

    public function compose(View $view): void
    {
        $this->settings = $this->getSettings();
        $languages = $this->getLanguages();

        $view->with([
            'siteName' => $this->settings?->site_name ?? config('app.name', 'Laravel'),
            'siteLogo' => $this->settings?->site_logo,
            'siteSeoTitle' => $this->settings?->seo_site_title,
            'siteSeoDescription' => $this->settings?->seo_site_description,
            'ogImage' => $this->settings?->seo_og_image,
            'availableLanguages' => $languages,
            'currentLocale' => app()->getLocale(),
            'currentLanguage' => collect($languages)->firstWhere('code', app()->getLocale()),
            'otherLanguage' => collect($languages)->firstWhere('code', '!=', app()->getLocale()),
            'preloaderSettings' => $this->getPreloaderSettings(),
        ]);
    }

    private function getSettings(): ?object
    {
        try {
            return Setting::first();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getLanguages(): array
    {
        try {
            return LanguageController::getAvailableLanguagesWithMetadata();
        } catch (\Exception $e) {
            return [
                ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'flag' => '🇺🇸'],
                ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'flag' => '🇸🇦'],
            ];
        }
    }

    private function getPreloaderSettings(): array
    {
        return [
            'preloaderEnabled' => $this->settings?->preloader_enabled ?? true,
            'preloaderType' => $this->settings?->preloader_type ?? 'spinner',
            'preloaderColor' => $this->settings?->preloader_color ?? '#3b82f6',
            'preloaderBgColor' => $this->settings?->preloader_background_color ?? '#ffffff',
            'preloaderDuration' => $this->settings?->preloader_duration ?? 2000,
            'preloaderMinDuration' => $this->settings?->preloader_min_duration ?? 0,
            'preloaderText' => trans('app.Loading...'),
            'siteLogo' => $this->settings?->site_logo,
            'logoText' => $this->settings?->logo_text ?? config('app.name'),
            'logoShowText' => $this->settings?->logo_show_text ?? true,
        ];
    }
}

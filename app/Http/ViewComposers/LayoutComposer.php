<?php

namespace App\Http\ViewComposers;

use App\Http\Controllers\LanguageController;
use App\Models\Setting;
use Illuminate\View\View;

class LayoutComposer
{
    public function compose(View $view): void
    {
        // Debug: Log that composer is being called
        \Log::info('LayoutComposer called for view: ' . $view->getName());
        
        $view->with([
            'siteName' => $this->getSiteName(),
            'siteLogo' => $this->getSiteLogo(),
            'siteSeoTitle' => $this->getSiteSeoTitle(),
            'siteSeoDescription' => $this->getSiteSeoDescription(),
            'ogImage' => $this->getOgImage(),
            'availableLanguages' => $this->getAvailableLanguages(),
            'currentLocale' => app()->getLocale(),
            'currentLanguage' => $this->getCurrentLanguage(),
            'otherLanguage' => $this->getOtherLanguage(),
            'preloaderSettings' => $this->getPreloaderSettings(),
        ]);
    }

    private function getSiteName(): string
    {
        try {
            $setting = Setting::first();
            return $setting?->site_name ?? config('app.name', 'Laravel');
        } catch (\Exception $e) {
            return config('app.name', 'Laravel');
        }
    }

    private function getSiteLogo(): ?string
    {
        try {
            $setting = Setting::first();
            return $setting?->site_logo;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getSiteSeoTitle(): ?string
    {
        try {
            $setting = Setting::first();
            return $setting?->seo_site_title;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getSiteSeoDescription(): ?string
    {
        try {
            $setting = Setting::first();
            return $setting?->seo_site_description;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getOgImage(): ?string
    {
        try {
            $setting = Setting::first();
            return $setting?->seo_og_image;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getAvailableLanguages(): array
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
    private function getCurrentLanguage(): ?array
    {
        try {
            $languages = $this->getAvailableLanguages();
            return collect($languages)->firstWhere('code', app()->getLocale());
        } catch (\Exception $e) {
            return ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'flag' => '🇺🇸'];
        }
    }

    private function getOtherLanguage(): ?array
    {
        try {
            $languages = $this->getAvailableLanguages();
            return collect($languages)->firstWhere('code', '!=', app()->getLocale());
        } catch (\Exception $e) {
            return ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'flag' => '🇸🇦'];
        }
    }

    private function getPreloaderSettings(): array
    {
        try {
            $settings = Setting::first();
            return [
                'preloaderEnabled' => $settings?->preloader_enabled ?? true,
                'preloaderType' => $settings?->preloader_type ?? 'spinner',
                'preloaderColor' => $settings?->preloader_color ?? '#3b82f6',
                'preloaderBgColor' => $settings?->preloader_background_color ?? '#ffffff',
                'preloaderDuration' => $settings?->preloader_duration ?? 2000,
                'preloaderMinDuration' => $settings?->preloader_min_duration ?? 0,
                'preloaderText' => trans('app.Loading...'),
                'siteLogo' => $settings?->site_logo ?? null,
                'logoText' => $settings?->logo_text ?? config('app.name'),
                'logoShowText' => $settings?->logo_show_text ?? true,
            ];
        } catch (\Exception $e) {
            return [
                'preloaderEnabled' => true,
                'preloaderType' => 'spinner',
                'preloaderColor' => '#3b82f6',
                'preloaderBgColor' => '#ffffff',
                'preloaderDuration' => 2000,
                'preloaderMinDuration' => 0,
                'preloaderText' => 'Loading...',
                'siteLogo' => null,
                'logoText' => config('app.name'),
                'logoShowText' => true,
            ];
        }
    }
}

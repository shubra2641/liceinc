<?php

namespace App\Http\ViewComposers;

use App\Http\Controllers\LanguageController;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class LayoutComposer
{
    public function compose(View $view): void
    {
        $view->with($this->getData());
    }

    private function getData(): array
    {
        return Cache::remember('layout_data', 60, function () {
            return [
                'siteName' => Setting::get('site_name', config('app.name', 'Laravel')),
                'siteLogo' => Setting::get('site_logo'),
                'siteSeoTitle' => Setting::get('seo_site_title'),
                'siteSeoDescription' => Setting::get('seo_site_description'),
                'ogImage' => Setting::get('seo_og_image'),
                'availableLanguages' => LanguageController::getAvailableLanguagesWithMetadata(),
                'currentLocale' => app()->getLocale(),
                'currentLanguage' => $this->getCurrentLanguage(),
                'otherLanguage' => $this->getOtherLanguage(),
                'preloaderSettings' => $this->getPreloaderSettings(),
            ];
        });
    }
    private function getCurrentLanguage(): ?array
    {
        $languages = LanguageController::getAvailableLanguagesWithMetadata();
        return collect($languages)->firstWhere('code', app()->getLocale());
    }

    private function getOtherLanguage(): ?array
    {
        $languages = LanguageController::getAvailableLanguagesWithMetadata();
        return collect($languages)->firstWhere('code', '!=', app()->getLocale());
    }

    private function getPreloaderSettings(): array
    {
        $settings = Setting::first();
        return [
            'preloaderEnabled' => $settings->preloader_enabled ?? true,
            'preloaderType' => $settings->preloader_type ?? 'spinner',
            'preloaderColor' => $settings->preloader_color ?? '#3b82f6',
            'preloaderBgColor' => $settings->preloader_background_color ?? '#ffffff',
            'preloaderDuration' => $settings->preloader_duration ?? 2000,
            'preloaderMinDuration' => $settings->preloader_min_duration ?? 0,
            'preloaderText' => trans('app.Loading...'),
            'siteLogo' => $settings->site_logo ?? null,
            'logoText' => $settings->logo_text ?? config('app.name'),
            'logoShowText' => $settings->logo_show_text ?? true,
        ];
    }
}

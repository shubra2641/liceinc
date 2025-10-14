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
        $view->with($this->getLayoutData());
    }
    private function getLayoutData(): array
    {
        return Cache::remember('layout_data', 60, function () {
            return [
                'siteName' => $this->getSiteName(),
                'siteLogo' => $this->getSiteLogo(),
                'siteSeoTitle' => $this->getSiteSeoTitle(),
                'siteSeoDescription' => $this->getSiteSeoDescription(),
                'ogImage' => $this->getOgImage(),
                'availableLanguages' => $this->getAvailableLanguages(),
                'currentLocale' => $this->getCurrentLocale(),
                'currentLanguage' => $this->getCurrentLanguage(),
                'otherLanguage' => $this->getOtherLanguage(),
                'preloaderSettings' => $this->getPreloaderSettings(),
            ];
        });
    }
    private function getSiteName(): string
    {
        return Setting::get('site_name', config('app.name', 'Laravel'));
    }

    private function getSiteLogo(): ?string
    {
        return Setting::get('site_logo');
    }

    private function getSiteSeoTitle(): ?string
    {
        return Setting::get('seo_site_title');
    }

    private function getSiteSeoDescription(): ?string
    {
        return Setting::get('seo_site_description');
    }

    private function getOgImage(): ?string
    {
        return Setting::get('seo_og_image');
    }
    private function getAvailableLanguages(): array
    {
        return LanguageController::getAvailableLanguagesWithMetadata();
    }

    private function getCurrentLocale(): string
    {
        return app()->getLocale();
    }

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

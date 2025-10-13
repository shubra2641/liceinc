<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Setting;

class SettingHelper
{
    /**
     * Safely update or create a setting without creating duplicates
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string $version
     *
     * @return Setting
     */
    public static function updateOrCreateSetting(string $key, $value, string $type = 'Lic general', string $version = '1.0.5'): Setting
    {
        // Find the FIRST (oldest) setting with this key
        $setting = Setting::where('key', $key)->orderBy('id', 'asc')->first();
        
        if ($setting) {
            // Update existing setting
            $setting->update([
                'value' => $value,
                'type' => $type,
                'version' => $version,
                'last_updated_at' => now()
            ]);
            
            return $setting;
        } else {
            // Create new setting
            return Setting::create([
                'key' => $key,
                'value' => $value,
                'type' => $type,
                'version' => $version,
                'last_updated_at' => now()
            ]);
        }
    }
    
    /**
     * Get a setting value safely
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public static function getSetting(string $key, $default = null)
    {
        $setting = Setting::where('key', $key)->orderBy('id', 'asc')->first();
        
        return $setting ? $setting->value : $default;
    }
    
    /**
     * Clean duplicate settings for a specific key
     *
     * @param string $key
     *
     * @return int Number of deleted duplicates
     */
    public static function cleanDuplicates(string $key): int
    {
        $settings = Setting::where('key', $key)->orderBy('id', 'asc')->get();
        
        if ($settings->count() <= 1) {
            return 0;
        }
        
        // Keep the FIRST (oldest) one
        $first = $settings->first();
        $duplicates = $settings->skip(1);
        
        $deletedCount = $duplicates->count();
        
        // Delete duplicates
        $duplicates->each(function($setting) {
            $setting->delete();
        });
        
        return $deletedCount;
    }
    
    /**
     * Clean all duplicate settings
     *
     * @return array Summary of cleaned duplicates
     */
    public static function cleanAllDuplicates(): array
    {
        $summary = [];
        
        // Get all unique keys
        $keys = Setting::distinct()->pluck('key');
        
        foreach ($keys as $key) {
            $deletedCount = self::cleanDuplicates($key);
            if ($deletedCount > 0) {
                $summary[$key] = $deletedCount;
            }
        }
        
        return $summary;
    }
}

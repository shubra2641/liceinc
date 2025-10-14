<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardCacheService
{
    public function clearAllCaches(): array
    {
        try {
            DB::beginTransaction();
            
            $this->clearApplicationCaches();
            $this->clearLicenseSpecificCaches();
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'All caches cleared successfully!'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cache clearing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to clear caches. Please try again.'
            ];
        }
    }

    private function clearApplicationCaches(): void
    {
        // Clear application cache
        Artisan::call('cache:clear');
        
        // Clear config cache
        Artisan::call('config:clear');
        
        // Clear route cache
        Artisan::call('route:clear');
        
        // Clear view cache
        Artisan::call('view:clear');
        
        // Clear compiled classes
        Artisan::call('clear-compiled');
    }

    private function clearLicenseSpecificCaches(): void
    {
        // Clear all cache keys
        Cache::flush();
    }
}

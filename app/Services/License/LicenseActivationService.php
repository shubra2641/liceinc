<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\License;
use App\Models\LicenseDomain;
use App\Services\LicenseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * License Activation Service
 * 
 * Handles license activation logic
 */
class LicenseActivationService
{
    public function __construct(
        private LicenseService $licenseService
    ) {
    }

    /**
     * Activate license
     */
    public function activateLicense(string $licenseKey, string $domain, array $additionalData = []): array
    {
        try {
            DB::beginTransaction();
            
            $license = $this->findLicenseByKey($licenseKey);
            
            if (!$license) {
                return $this->createErrorResponse('INVALID_LICENSE', 'License key not found');
            }

            if (!$this->canActivateLicense($license)) {
                return $this->createErrorResponse('ACTIVATION_NOT_ALLOWED', 'License cannot be activated');
            }

            $domainRecord = $this->createOrUpdateDomain($license, $domain, $additionalData);
            $this->updateLicenseActivation($license, $domainRecord);
            
            DB::commit();
            
            return $this->createSuccessResponse($license, $domainRecord);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('License activation failed', [
                'license_key' => $licenseKey,
                'domain' => $domain,
                'error' => $e->getMessage()
            ]);
            
            return $this->createErrorResponse('ACTIVATION_FAILED', 'License activation failed');
        }
    }

    /**
     * Find license by key
     */
    private function findLicenseByKey(string $licenseKey): ?License
    {
        return License::where('license_key', $licenseKey)
            ->with('product', 'user', 'domains')
            ->first();
    }

    /**
     * Check if license can be activated
     */
    private function canActivateLicense(License $license): bool
    {
        if ($license->status !== 'active') {
            return false;
        }

        if ($license->license_expires_at && $license->license_expires_at < now()) {
            return false;
        }

        return true;
    }

    /**
     * Create or update domain record
     */
    private function createOrUpdateDomain(License $license, string $domain, array $additionalData): LicenseDomain
    {
        $domainRecord = LicenseDomain::where('license_id', $license->id)
            ->where('domain', $domain)
            ->first();

        if ($domainRecord) {
            $domainRecord->update([
                'is_active' => true,
                'activated_at' => now(),
                'additional_data' => $additionalData,
            ]);
        } else {
            $domainRecord = LicenseDomain::create([
                'license_id' => $license->id,
                'domain' => $domain,
                'is_active' => true,
                'activated_at' => now(),
                'additional_data' => $additionalData,
            ]);
        }

        return $domainRecord;
    }

    /**
     * Update license activation
     */
    private function updateLicenseActivation(License $license, LicenseDomain $domainRecord): void
    {
        $license->update([
            'last_activated_at' => now(),
            'activation_count' => $license->activation_count + 1,
        ]);
    }

    /**
     * Create success response
     */
    private function createSuccessResponse(License $license, LicenseDomain $domainRecord): array
    {
        return [
            'status' => 'success',
            'license_key' => $license->license_key,
            'domain' => $domainRecord->domain,
            'activated_at' => $domainRecord->activated_at->toISOString(),
            'product_id' => $license->product_id,
            'product_name' => $license->product->name,
            'license_type' => $license->license_type,
            'expires_at' => $license->license_expires_at?->toISOString(),
        ];
    }

    /**
     * Create error response
     */
    private function createErrorResponse(string $code, string $message): array
    {
        return [
            'status' => 'error',
            'code' => $code,
            'message' => $message,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\License;
use App\Services\License\Strategies\LicenseVerificationStrategy;
use App\Services\License\Strategies\StandardLicenseVerificationStrategy;
use App\Services\License\Strategies\ExtendedLicenseVerificationStrategy;
use Illuminate\Support\Facades\Log;

/**
 * License Verification Manager
 * 
 * Manages license verification using strategy pattern
 */
class LicenseVerificationManager
{
    private array $strategies = [];

    public function __construct()
    {
        $this->registerStrategies();
    }

    /**
     * Verify license using appropriate strategy
     */
    public function verifyLicense(License $license, array $data): array
    {
        try {
            $strategy = $this->getStrategy($license->license_type);
            return $strategy->verify($license, $data);
        } catch (\Exception $e) {
            Log::error('License verification failed', [
                'license_id' => $license->id,
                'license_type' => $license->license_type,
                'error' => $e->getMessage()
            ]);

            return [
                'status' => 'error',
                'code' => 'VERIFICATION_FAILED',
                'message' => 'License verification failed'
            ];
        }
    }

    /**
     * Register verification strategies
     */
    private function registerStrategies(): void
    {
        $this->strategies = [
            new StandardLicenseVerificationStrategy(),
            new ExtendedLicenseVerificationStrategy(),
        ];
    }

    /**
     * Get appropriate strategy for license type
     */
    private function getStrategy(string $licenseType): LicenseVerificationStrategy
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->canHandle($licenseType)) {
                return $strategy;
            }
        }

        // Default to standard strategy
        return new StandardLicenseVerificationStrategy();
    }
}

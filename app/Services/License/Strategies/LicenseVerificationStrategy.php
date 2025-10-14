<?php

declare(strict_types=1);

namespace App\Services\License\Strategies;

use App\Models\License;
use App\Models\LicenseDomain;

/**
 * License Verification Strategy Interface
 */
interface LicenseVerificationStrategy
{
    /**
     * Verify license
     */
    public function verify(License $license, array $data): array;

    /**
     * Check if strategy can handle the license type
     */
    public function canHandle(string $licenseType): bool;
}

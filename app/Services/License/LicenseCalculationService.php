<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\Product;
use Carbon\Carbon;

/**
 * License Calculation Service - Handles license and support expiry calculations.
 */
class LicenseCalculationService
{
    /**
     * Calculate license expiry date.
     */
    public function calculateLicenseExpiry(Product $product): Carbon
    {
        $licenseDuration = $product->license_duration ?? 365; // Default 1 year
        return now()->addDays($licenseDuration);
    }

    /**
     * Calculate support expiry date.
     */
    public function calculateSupportExpiry(Product $product): Carbon
    {
        $supportDuration = $product->support_duration ?? 365; // Default 1 year
        return now()->addDays($supportDuration);
    }

    /**
     * Calculate remaining license days.
     */
    public function calculateRemainingLicenseDays(Carbon $expiryDate): int
    {
        $now = now();
        if ($expiryDate->isPast()) {
            return 0;
        }

        return $now->diffInDays($expiryDate, false);
    }

    /**
     * Calculate remaining support days.
     */
    public function calculateRemainingSupportDays(Carbon $supportExpiryDate): int
    {
        $now = now();
        if ($supportExpiryDate->isPast()) {
            return 0;
        }

        return $now->diffInDays($supportExpiryDate, false);
    }

    /**
     * Check if license is expired.
     */
    public function isLicenseExpired(Carbon $expiryDate): bool
    {
        return $expiryDate->isPast();
    }

    /**
     * Check if support is expired.
     */
    public function isSupportExpired(Carbon $supportExpiryDate): bool
    {
        return $supportExpiryDate->isPast();
    }

    /**
     * Get license status based on expiry.
     */
    public function getLicenseStatus(Carbon $expiryDate): string
    {
        if ($this->isLicenseExpired($expiryDate)) {
            return 'expired';
        }

        $remainingDays = $this->calculateRemainingLicenseDays($expiryDate);

        if ($remainingDays <= 30) {
            return 'expiring_soon';
        }

        return 'active';
    }

    /**
     * Get support status based on expiry.
     */
    public function getSupportStatus(Carbon $supportExpiryDate): string
    {
        if ($this->isSupportExpired($supportExpiryDate)) {
            return 'expired';
        }

        $remainingDays = $this->calculateRemainingSupportDays($supportExpiryDate);

        if ($remainingDays <= 30) {
            return 'expiring_soon';
        }

        return 'active';
    }
}

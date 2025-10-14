<?php

declare(strict_types=1);

namespace App\Services\Invoice;

use App\Models\License;
use App\Models\Product;
use App\Models\User;

/**
 * Invoice Validation Service
 * 
 * Handles validation for invoice operations
 */
class InvoiceValidationService
{
    /**
     * Validate license for renewal
     */
    public function validateLicenseForRenewal(License $license): bool
    {
        // Check if license is active
        if ($license->status !== 'active') {
            return false;
        }

        // Check if license has expiration date
        if (!$license->license_expires_at) {
            return false;
        }

        // Check if license is not already expired
        if ($license->license_expires_at < now()) {
            return false;
        }

        // Check if product supports renewal
        if (!$this->productSupportsRenewal($license->product)) {
            return false;
        }

        return true;
    }

    /**
     * Check if product supports renewal
     */
    private function productSupportsRenewal(Product $product): bool
    {
        // Check if product has renewal price
        if (!$product->renewal_price && !$product->price) {
            return false;
        }

        // Check if product is active
        if (!$product->is_active) {
            return false;
        }

        return true;
    }

    /**
     * Validate user for invoice
     */
    public function validateUserForInvoice(User $user): bool
    {
        // Check if user is active
        if (!$user->is_active) {
            return false;
        }

        // Check if user has valid email
        if (!$user->email || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return true;
    }

    /**
     * Validate invoice data
     */
    public function validateInvoiceData(array $data): bool
    {
        $requiredFields = ['user_id', 'type', 'amount', 'currency'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }

        // Validate amount
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            return false;
        }

        // Validate currency
        if (!in_array($data['currency'], ['USD', 'EUR', 'GBP'])) {
            return false;
        }

        return true;
    }

    /**
     * Check if license already has pending renewal invoice
     */
    public function hasPendingRenewalInvoice(License $license): bool
    {
        return $license->invoices()
            ->where('type', 'renewal')
            ->where('status', 'pending')
            ->exists();
    }
}

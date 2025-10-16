<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use InvalidArgumentException;

/**
 * Invoice Validation Helper
 * 
 * Handles all validation logic for InvoiceService to reduce complexity.
 */
class InvoiceValidationHelper
{
    /**
     * Validate invoice parameters.
     */
    public function validateInvoiceParameters(License $license, string $paymentStatus): void
    {
        $this->validateLicense($license);
        $this->validatePaymentStatus($paymentStatus);
    }

    /**
     * Validate payment status.
     */
    public function validatePaymentStatus(string $status): void
    {
        $validStatuses = ['paid', 'pending', 'overdue', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            throw new InvalidArgumentException(
                'Invalid payment status: ' . $status
            );
        }
    }

    /**
     * Validate license.
     */
    public function validateLicense(License $license): void
    {
        if (!$license->exists) {
            throw new InvalidArgumentException('License does not exist');
        }
        if (!$license->user_id) {
            throw new InvalidArgumentException('License must have a user');
        }
        if (!$license->product) {
            throw new InvalidArgumentException('License must have a product');
        }
    }

    /**
     * Validate invoice.
     */
    public function validateInvoice(Invoice $invoice): void
    {
        if (!$invoice->exists) {
            throw new InvalidArgumentException('Invoice does not exist');
        }
    }

    /**
     * Validate payment invoice parameters.
     */
    public function validatePaymentInvoiceParameters(
        User $user,
        License $license,
        Product $product,
        float $amount,
        string $currency,
        string $gateway,
    ): void {
        $this->validateUser($user);
        $this->validateLicense($license);
        $this->validateProduct($product);
        $this->validateAmount($amount);
        $this->validateCurrency($currency);
        $this->validateGateway($gateway);
    }

    /**
     * Validate user.
     */
    public function validateUser(User $user): void
    {
        if (!$user->exists) {
            throw new InvalidArgumentException('User does not exist');
        }
    }

    /**
     * Validate product.
     */
    public function validateProduct(Product $product): void
    {
        if (!$product->exists) {
            throw new InvalidArgumentException('Product does not exist');
        }
    }

    /**
     * Validate amount.
     */
    public function validateAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than 0');
        }
    }

    /**
     * Validate currency.
     */
    public function validateCurrency(string $currency): void
    {
        if (empty($currency)) {
            throw new InvalidArgumentException('Currency cannot be empty');
        }
    }

    /**
     * Validate gateway.
     */
    public function validateGateway(string $gateway): void
    {
        if (empty($gateway)) {
            throw new InvalidArgumentException('Gateway cannot be empty');
        }
    }
}

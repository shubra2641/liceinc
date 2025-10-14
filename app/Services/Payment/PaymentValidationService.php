<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Payment Validation Service
 * 
 * Handles all payment validation logic
 */
class PaymentValidationService
{
    /**
     * Validate payment request
     */
    public function validatePaymentRequest(array $data, string $paymentMethod): array
    {
        $rules = $this->getValidationRules($paymentMethod);
        $validator = Validator::make($data, $rules);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $validator->validated();
    }

    /**
     * Validate invoice for payment
     */
    public function validateInvoiceForPayment(Invoice $invoice): void
    {
        if ($invoice->status === 'paid') {
            throw new \InvalidArgumentException('Invoice is already paid');
        }
        
        if ($invoice->status === 'cancelled') {
            throw new \InvalidArgumentException('Invoice is cancelled');
        }
        
        if ($invoice->total_amount <= 0) {
            throw new \InvalidArgumentException('Invalid invoice amount');
        }
    }

    /**
     * Validate payment settings
     */
    public function validatePaymentSettings(string $paymentMethod): PaymentSetting
    {
        $setting = PaymentSetting::where('key', $paymentMethod . '_enabled')->first();
        
        if (!$setting || !$setting->value) {
            throw new \InvalidArgumentException("Payment method {$paymentMethod} is not enabled");
        }
        
        return $setting;
    }

    /**
     * Get validation rules for payment method
     */
    private function getValidationRules(string $paymentMethod): array
    {
        $baseRules = [
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
        ];

        return match ($paymentMethod) {
            'stripe' => array_merge($baseRules, [
                'stripe_token' => 'required|string',
            ]),
            'paypal' => array_merge($baseRules, [
                'payment_id' => 'sometimes|string',
                'payer_id' => 'sometimes|string',
            ]),
            'bank_transfer' => array_merge($baseRules, [
                'bank_details' => 'required|array',
                'bank_details.account_number' => 'required|string',
                'bank_details.routing_number' => 'required|string',
            ]),
            default => $baseRules,
        };
    }

    /**
     * Validate payment amount
     */
    public function validatePaymentAmount(Invoice $invoice, float $amount): void
    {
        $tolerance = 0.01; // 1 cent tolerance
        $difference = abs($invoice->total_amount - $amount);
        
        if ($difference > $tolerance) {
            throw new \InvalidArgumentException(
                "Payment amount mismatch. Expected: {$invoice->total_amount}, Received: {$amount}"
            );
        }
    }

    /**
     * Validate currency
     */
    public function validateCurrency(Invoice $invoice, string $currency): void
    {
        if (strtoupper($invoice->currency) !== strtoupper($currency)) {
            throw new \InvalidArgumentException(
                "Currency mismatch. Expected: {$invoice->currency}, Received: {$currency}"
            );
        }
    }
}

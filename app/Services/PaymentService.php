<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Payment\PaymentService as NewPaymentService;
use App\Services\Request as ServiceRequest;

/**
 * Legacy Payment Service Wrapper.
 * 
 * This class maintains backward compatibility while delegating
 * all operations to the new modular PaymentService implementation.
 * 
 * @deprecated Use App\Services\Payment\PaymentService directly
 */
class PaymentService
{
    private NewPaymentService $newPaymentService;

    public function __construct(NewPaymentService $newPaymentService)
    {
        $this->newPaymentService = $newPaymentService;
    }

    /**
     * Process payment with the specified gateway.
     * 
     * @param array $orderData Order data
     * @param string $gateway Payment gateway
     * @return array Payment result
     */
    public function processPayment(array $orderData, string $gateway): array
    {
        return $this->newPaymentService->processPayment($orderData, $gateway);
    }

    /**
     * Verify payment with gateway.
     * 
     * @param string $gateway Payment gateway
     * @param string $transactionId Transaction ID
     * @return array Verification result
     */
    public function verifyPayment(string $gateway, string $transactionId): array
    {
        return $this->newPaymentService->verifyPayment($gateway, $transactionId);
    }

    /**
     * Create license and invoice for payment.
     * 
     * @param array $orderData Order data
     * @param string $gateway Payment gateway
     * @param string|null $transactionId Transaction ID
     * @return array Creation result
     */
    public function createLicenseAndInvoice(array $orderData, string $gateway, ?string $transactionId = null): array
    {
        return $this->newPaymentService->createLicenseAndInvoice($orderData, $gateway, $transactionId);
    }

    /**
     * Handle webhook from payment gateway.
     * 
     * @param ServiceRequest $request Webhook request
     * @param string $gateway Payment gateway
     * @return array Webhook result
     */
    public function handleWebhook(ServiceRequest $request, string $gateway): array
    {
        return $this->newPaymentService->handleWebhook($request, $gateway);
    }
}
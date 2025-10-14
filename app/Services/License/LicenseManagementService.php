<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Services\Email\EmailFacade;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LicenseManagementService
{
    public function __construct(
        private EmailFacade $emailService,
        private InvoiceService $invoiceService
    ) {
    }

    public function createLicense(array $validated): License
    {
        $product = $this->getProduct($validated['product_id']);
        $validated = $this->processLicenseData($validated, $product);
        
        $license = License::create($validated);
        
        $this->createInitialInvoice($license, $validated);
        $this->sendLicenseNotifications($license);
        
        return $license;
    }

    public function updateLicense(License $license, array $validated): License
    {
        $product = $this->getProduct($validated['product_id']);
        $validated = $this->processLicenseData($validated, $product);
        
        $license->update($validated);
        
        return $license;
    }

    public function deleteLicense(License $license): void
    {
        $license->delete();
    }

    public function toggleLicenseStatus(License $license): void
    {
        $license->update([
            'status' => $license->status === 'active' ? 'inactive' : 'active',
        ]);
    }

    public function generateLicenseKey(): string
    {
        $maxAttempts = 10;
        $attempts = 0;

        do {
            $licenseKey = 'LIC-' . strtoupper(Str::random(12));
            $attempts++;

            if ($attempts > $maxAttempts) {
                throw new \Exception('Failed to generate unique license key after ' . $maxAttempts . ' attempts');
            }
        } while (License::where('license_key', $licenseKey)->exists());

        return $licenseKey;
    }

    private function getProduct(int $productId): Product
    {
        $product = Product::find($productId);
        if (!$product) {
            throw new \Exception('Product not found.');
        }
        return $product;
    }

    private function processLicenseData(array $validated, Product $product): array
    {
        // Inherit license type from product if not specified
        if (empty($validated['license_type'])) {
            $validated['license_type'] = $product->license_type ?? 'single';
        }

        // Set max_domains based on license type
        if (empty($validated['max_domains'])) {
            $validated['max_domains'] = $this->getMaxDomainsForLicenseType($validated['license_type']);
        }

        // Set default values
        $validated['status'] = $validated['status'] ?? 'active';

        // Calculate license expiration date based on product duration
        if (empty($validated['license_expires_at'])) {
            $validated['license_expires_at'] = $this->calculateLicenseExpiration($product);
        }

        // Calculate support expiration date based on product support days
        if (empty($validated['support_expires_at'])) {
            $validated['support_expires_at'] = $this->calculateSupportExpiration($product);
        }

        // Generate license key if not provided
        if (empty($validated['license_key'])) {
            $validated['license_key'] = $this->generateLicenseKey();
        }

        return $validated;
    }

    private function getMaxDomainsForLicenseType(string $licenseType): int
    {
        return match ($licenseType) {
            'single' => 1,
            'multi' => 5,
            'developer' => 10,
            'extended' => 20,
            default => 1,
        };
    }

    private function calculateLicenseExpiration(Product $product): ?\DateTimeInterface
    {
        if ($product->duration_days) {
            return now()->addDays(
                is_numeric($product->duration_days) ? (int)$product->duration_days : 0
            );
        }
        return null;
    }

    private function calculateSupportExpiration(Product $product): ?\DateTimeInterface
    {
        if ($product->support_days) {
            return now()->addDays(
                is_numeric($product->support_days) ? (int)$product->support_days : 0
            );
        }
        return null;
    }

    private function createInitialInvoice(License $license, array $validated): void
    {
        $invoice = $this->invoiceService->createInitialInvoice(
            $license,
            is_string($validated['invoice_payment_status'] ?? null)
                ? $validated['invoice_payment_status']
                : 'pending',
            ($validated['invoice_due_date'] ?? null) instanceof \DateTimeInterface
                ? $validated['invoice_due_date']
                : null,
        );
    }

    private function sendLicenseNotifications(License $license): void
    {
        try {
            // Send notification to user
            if ($license->user) {
                $this->emailService->sendLicenseCreated($license, $license->user);
            }

            // Send notification to admin only if user exists
            if ($license->user) {
                $this->emailService->sendAdminLicenseCreated([
                    'license_key' => $license->license_key,
                    'product_name' => $license->product->name ?? 'Unknown Product',
                    'customer_name' => $license->user->name,
                    'customer_email' => $license->user->email,
                ]);
            }
        } catch (\Exception $e) {
            // Log email errors but don't fail license creation
            Log::warning('Email notification failed during license creation', [
                'error' => $e->getMessage(),
                'license_id' => $license->id,
            ]);
        }
    }
}

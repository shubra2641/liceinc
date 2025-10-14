<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LicenseRegisterRequest;
use App\Http\Requests\Api\LicenseStatusRequest;
use App\Http\Requests\Api\LicenseVerifyRequest;
use App\Models\License;
use App\Models\Product;
use App\Services\EnvatoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * License API Controller - Ultra Simplified
 */
class LicenseApiController extends Controller
{
    public function __construct(private EnvatoService $envatoService) {}

    /**
     * Verify license
     */
    public function verify(LicenseVerifyRequest $request): JsonResponse
    {
        return $this->safeExecute(function() use ($request) {
            $data = $request->validated();
            $product = $this->getProduct($data['product_slug']);
            $license = $this->getOrCreateLicense($product, $data['purchase_code'], $data['domain'] ?? null);
            
            if (!$this->isActive($license)) {
                return $this->error('License inactive', 'LICENSE_INACTIVE', 403);
            }
            
            if ($data['domain'] && !$this->handleDomain($license, $data['domain'])) {
                return $this->error('Domain limit reached', 'DOMAIN_LIMIT', 403);
            }
            
            $this->log($license, $data['domain'] ?? null);
            return $this->success($license, $data['domain'] ?? null);
        });
    }

    /**
     * Register license
     */
    public function register(LicenseRegisterRequest $request): JsonResponse
    {
        return $this->safeExecute(function() use ($request) {
            $data = $request->validated();
            $product = $this->getProduct($data['product_slug']);
            
            if ($this->licenseExists($data['purchase_code'], $product->id)) {
                return response()->json(['success' => true, 'message' => 'License exists']);
            }
            
            $license = $this->createLicense($product, $data['purchase_code'], $data['domain'] ?? null);
            $this->log($license, $data['domain'] ?? null);
            
            return response()->json(['success' => true, 'message' => 'License registered', 'license_id' => $license->id]);
        });
    }

    /**
     * Get license status
     */
    public function status(LicenseStatusRequest $request): JsonResponse
    {
        return $this->safeExecute(function() use ($request) {
            $data = $request->validated();
            $product = $this->getProduct($data['product_slug']);
            $license = $this->getLicense($data['license_key'], $product->id);
            
            $isActive = $this->isActive($license);
            if ($isActive) $this->log($license, null);
            
            return response()->json([
                'valid' => $isActive,
                'license' => $this->getLicenseData($license),
                'product' => $this->getProductData($product),
            ]);
        });
    }

    /**
     * Safe execution with error handling
     */
    private function safeExecute(callable $callback): JsonResponse
    {
        try {
            DB::beginTransaction();
            $result = $callback();
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('License API error: ' . $e->getMessage());
            return $this->error('Operation failed', 'INTERNAL_ERROR', 500);
        }
    }

    /**
     * Get product by slug
     */
    private function getProduct(string $slug): Product
    {
        $product = Product::where('slug', $slug)->first();
        if (!$product) {
            throw new \Exception('Product not found');
        }
        return $product;
    }

    /**
     * Get or create license
     */
    private function getOrCreateLicense(Product $product, string $purchaseCode, ?string $domain): License
    {
        $license = License::where('purchase_code', $purchaseCode)->where('product_id', $product->id)->first();
        
        if (!$license) {
            $envatoData = $this->envatoService->verifyPurchase($purchaseCode);
            if (!$envatoData || $envatoData['item']['id'] != $product->envato_item_id) {
                throw new \Exception('License not found');
            }
            $license = $this->createLicense($product, $purchaseCode, $domain);
        }
        
        return $license;
    }

    /**
     * Get license by key
     */
    private function getLicense(string $licenseKey, int $productId): License
    {
        $license = License::where('license_key', $licenseKey)->where('product_id', $productId)->first();
        if (!$license) {
            throw new \Exception('License not found');
        }
        return $license;
    }

    /**
     * Check if license exists
     */
    private function licenseExists(string $purchaseCode, int $productId): bool
    {
        return License::where('purchase_code', $purchaseCode)->where('product_id', $productId)->exists();
    }

    /**
     * Check if license is active
     */
    private function isActive(License $license): bool
    {
        return $license->status === 'active' && (!$license->license_expires_at || $license->license_expires_at > now());
    }

    /**
     * Handle domain verification
     */
    private function handleDomain(License $license, string $domain): bool
    {
        $autoRegister = \App\Helpers\ConfigHelper::getSetting('license_auto_register_domains', false);
        $isTest = config('app.env') === 'local' || config('app.debug');
        
        if ($autoRegister || $isTest) {
            try {
                $this->registerDomain($license, $domain);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
        
        return $this->verifyDomain($license, $domain);
    }

    /**
     * Verify domain
     */
    private function verifyDomain(License $license, string $domain): bool
    {
        $clean = $this->cleanDomain($domain);
        $domains = $license->domains()->where('status', 'active')->get();
        
        if ($domains->isEmpty()) {
            try {
                $this->checkLimit($license, $domain);
                $this->registerDomain($license, $domain);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
        
        foreach ($domains as $d) {
            $authDomain = $this->cleanDomain($d->domain ?? '');
            if ($authDomain === $clean) {
                $d->update(['last_used_at' => now()]);
                return true;
            }
            if (str_starts_with($authDomain, '*.')) {
                $pattern = str_replace('*.', '', $authDomain);
                if (str_ends_with($clean, $pattern)) {
                    $d->update(['last_used_at' => now()]);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Register domain
     */
    private function registerDomain(License $license, string $domain): void
    {
        $clean = $this->cleanDomain($domain);
        $existing = $license->domains()->where('domain', $clean)->first();
        
        if ($existing) {
            $existing->update(['last_used_at' => now()]);
        } else {
            $this->checkLimit($license, $clean);
            $license->domains()->create([
                'domain' => $clean,
                'status' => 'active',
                'added_at' => now(),
                'last_used_at' => now(),
            ]);
        }
    }

    /**
     * Check domain limit
     */
    private function checkLimit(License $license, string $domain): void
    {
        if ($license->hasReachedDomainLimit()) {
            $max = $license->max_domains ?? 1;
            throw new \Exception("Domain limit reached ({$max}): {$domain}");
        }
    }

    /**
     * Clean domain
     */
    private function cleanDomain(string $domain): string
    {
        $domain = preg_replace('/^https?:\/\//', '', $domain) ?? $domain;
        return preg_replace('/^www\./', '', $domain) ?? $domain;
    }

    /**
     * Create license
     */
    private function createLicense(Product $product, string $purchaseCode, ?string $domain): License
    {
        $maxDomains = match ($product->license_type ?? 'single') {
            'single' => 1, 'multi' => 5, 'developer' => 10, 'extended' => 3, default => 1
        };
        
        $license = License::create([
            'product_id' => $product->id,
            'purchase_code' => $purchaseCode,
            'license_key' => $this->generateKey(),
            'license_type' => $product->license_type ?? 'single',
            'max_domains' => $maxDomains,
            'support_expires_at' => now()->addDays($product->support_days ?? 365),
            'license_expires_at' => $product->license_type === 'extended' ? now()->addYear() : null,
            'status' => 'active',
        ]);
        
        if ($domain) {
            $license->domains()->create(['domain' => $domain, 'status' => 'active']);
        }
        
        return $license;
    }

    /**
     * Generate license key
     */
    private function generateKey(): string
    {
        return strtoupper(
            substr(md5(uniqid((string)mt_rand(), true)), 0, 8) . '-' .
            substr(md5(uniqid((string)mt_rand(), true)), 0, 8) . '-' .
            substr(md5(uniqid((string)mt_rand(), true)), 0, 8) . '-' .
            substr(md5(uniqid((string)mt_rand(), true)), 0, 8)
        );
    }

    /**
     * Log verification
     */
    private function log(License $license, ?string $domain): void
    {
        \App\Services\LicenseVerificationLogger::log(
            purchaseCode: $license->purchase_code,
            domain: $domain ?? 'unknown',
            isValid: true,
            message: 'License verified successfully',
            source: 'api',
            responseData: ['method' => 'api_verification']
        );
    }

    /**
     * Get license data
     */
    private function getLicenseData(License $license): array
    {
        return [
            'id' => $license->id,
            'type' => $license->license_type,
            'expires_at' => $license->license_expires_at?->toISOString(),
            'support_expires_at' => $license->support_expires_at?->toISOString(),
            'status' => $license->status,
        ];
    }

    /**
     * Get product data
     */
    private function getProductData(Product $product): array
    {
        return [
            'name' => htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'),
            'version' => htmlspecialchars($product->version, ENT_QUOTES, 'UTF-8'),
        ];
    }

    /**
     * Success response
     */
    private function success(License $license, ?string $domain): JsonResponse
    {
                return response()->json([
            'valid' => true,
            'message' => 'License verified successfully',
            'data' => [
                'license_id' => $license->id,
                'license_type' => $license->license_type,
                'max_domains' => $license->max_domains ?? 1,
                'current_domains' => $license->active_domains_count,
                'remaining_domains' => $license->remaining_domains,
                    'expires_at' => $license->license_expires_at?->toISOString(),
                    'support_expires_at' => $license->support_expires_at?->toISOString(),
                    'status' => $license->status,
                ],
        ]);
    }

    /**
     * Error response
     */
    private function error(string $message, string $code, int $status = 400): JsonResponse
    {
            return response()->json([
                'valid' => false,
            'message' => $message,
            'error_code' => $code,
        ], $status);
    }
}
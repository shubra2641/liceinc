<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\ProgrammingLanguage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * License Generator Service - Simplified
 */
class LicenseGeneratorService
{
    /**
     * Generate license verification file for a product
     */
    public function generateLicenseFile(Product $product): string
    {
        try {
            if (!$product->id) {
                throw new \InvalidArgumentException('Invalid product provided');
            }

            $product->refresh();
            $language = $product->programmingLanguage;
            if (!$language) {
                throw new \Exception('Programming language not found for product: ' . $product->id);
            }

            // Delete old files
            $this->deleteOldLicenseFiles($product);

            // Generate new file
            $template = $this->getLicenseTemplate($language);
            $fileContent = $this->compileTemplate($template, $product);
            $fileName = $this->generateFileName($product, $language);
            $filePath = $this->saveLicenseFile($fileContent, $fileName, $product);

            // Update product with new file path
            $product->update(['integration_file_path' => $filePath]);
            return $filePath;
        } catch (\Exception $e) {
            Log::error('Error generating license file', [
                'product_id' => $product->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete old license files for a product
     */
    private function deleteOldLicenseFiles(Product $product): void
    {
        try {
            if (!$product->id) {
                return;
            }

            $productDir = "licenses/{$product->id}";
            if (!Storage::disk('public')->exists($productDir)) {
                return;
            }

            $files = Storage::disk('public')->files($productDir);
            foreach ($files as $file) {
                Storage::disk('public')->delete($file);
            }
        } catch (\Exception $e) {
            Log::error('Error deleting old license files', [
                'product_id' => $product->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get license template for programming language
     */
    private function getLicenseTemplate(ProgrammingLanguage $language): string
    {
        try {
            if (!$language->slug) {
                throw new \InvalidArgumentException('Invalid programming language provided');
            }

            $templatePath = resource_path("templates/licenses/{$language->slug}.blade.php");

            if (!file_exists($templatePath)) {
                $this->createDefaultTemplate($language);
            }

            $content = file_get_contents($templatePath);
            if ($content === false) {
                throw new \Exception('Failed to read template file: ' . $templatePath);
            }

            return $content;
        } catch (\Exception $e) {
            Log::error('Error getting license template', [
                'language_slug' => $language->slug ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Compile template with product data
     */
    private function compileTemplate(string $template, Product $product): string
    {
        try {
            if (empty($template)) {
                throw new \InvalidArgumentException('Template content cannot be empty');
            }

            if (!$product->id) {
                throw new \InvalidArgumentException('Invalid product for template compilation');
            }

            // Build API URL
            $apiDomain = rtrim(config('app.url', ''), '/');
            $verificationEndpoint = config('license.verification_endpoint', '/api/license/verify');
            $licenseApiUrl = $apiDomain . '/' . ltrim($verificationEndpoint, '/');

            // Prepare data
            $data = [
                'product' => htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'),
                'product_slug' => htmlspecialchars($product->slug, ENT_QUOTES, 'UTF-8'),
                'license_api_url' => htmlspecialchars($licenseApiUrl, ENT_QUOTES, 'UTF-8'),
                'verification_key' => $this->generateVerificationKey($product),
                'api_token' => $this->getApiToken(),
                'envato_token' => $this->getEnvatoToken(),
                'envato_api_base' => config('envato.api_base'),
                'date' => now()->format('Y-m-d H:i:s'),
            ];

            // Simple template compilation
            foreach ($data as $key => $value) {
                $template = str_replace("{{{$key}}}", (string)$value, $template);
                $template = str_replace("{{$key}}", (string)$value, $template);
            }

            return $template;
        } catch (\Exception $e) {
            Log::error('Error compiling license template', [
                'product_id' => $product->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate verification key for product
     */
    private function generateVerificationKey(Product $product): string
    {
        try {
            if (!$product->id || !$product->slug) {
                throw new \InvalidArgumentException('Invalid product data for key generation');
            }

            $appKey = config('app.key');
            if (empty($appKey)) {
                throw new \Exception('Application key not configured');
            }

            $keyData = (string)$product->id . $product->slug . $appKey;
            return hash('sha256', $keyData);
        } catch (\Exception $e) {
            Log::error('Error generating verification key', [
                'product_id' => $product->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate file name for license file
     */
    private function generateFileName(Product $product, ProgrammingLanguage $language): string
    {
        try {
            if (!$product->slug || !$language->slug) {
                throw new \InvalidArgumentException('Invalid product or language for filename generation');
            }

            $extension = $this->getFileExtensionForLanguage($language->slug);
            $timestamp = now()->format('Y-m-d_H-i-s');
            $sanitizedSlug = preg_replace('/[^a-zA-Z0-9_-]/', '', $product->slug);

            return "license-{$sanitizedSlug}-{$timestamp}.{$extension}";
        } catch (\Exception $e) {
            Log::error('Error generating filename', [
                'product_slug' => $product->slug ?? 'unknown',
                'language_slug' => $language->slug ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get file extension for programming language
     */
    private function getFileExtensionForLanguage(string $languageSlug): string
    {
        $extensions = [
            'php' => 'php',
            'laravel' => 'php',
            'javascript' => 'js',
            'python' => 'py',
            'java' => 'java',
            'csharp' => 'cs',
            'cpp' => 'cpp',
            'wordpress' => 'php',
            'react' => 'js',
            'angular' => 'ts',
            'nodejs' => 'js',
            'vuejs' => 'js',
            'go' => 'go',
            'swift' => 'swift',
            'typescript' => 'ts',
            'kotlin' => 'kt',
            'c' => 'c',
            'html-css' => 'html',
            'flask' => 'py',
            'django' => 'py',
            'expressjs' => 'js',
            'ruby-on-rails' => 'rb',
            'spring-boot' => 'java',
            'symfony' => 'php',
            'aspnet' => 'cs',
            'html' => 'html',
            'ruby' => 'rb',
        ];

        return $extensions[$languageSlug] ?? 'php';
    }

    /**
     * Save license file to storage
     */
    private function saveLicenseFile(string $content, string $fileName, Product $product): string
    {
        try {
            if (empty($content) || empty($fileName) || !$product->id) {
                throw new \InvalidArgumentException('Invalid data for file saving');
            }

            $sanitizedFileName = preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
            $path = "licenses/{$product->id}/{$sanitizedFileName}";

            Storage::disk('public')->put($path, $content);
            return $path;
        } catch (\Exception $e) {
            Log::error('Error saving license file', [
                'product_id' => $product->id ?? 'unknown',
                'filename' => $fileName,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get API token
     */
    private function getApiToken(): string
    {
        $token = \App\Helpers\ConfigHelper::getSetting('license_api_token', '', 'LICENSE_API_TOKEN');
        return is_string($token) ? $token : '';
    }

    /**
     * Get Envato token
     */
    private function getEnvatoToken(): string
    {
        $token = \App\Helpers\ConfigHelper::getSetting('envato_personal_token', '', 'ENVATO_PERSONAL_TOKEN');
        return is_string($token) ? $token : '';
    }

    /**
     * Create default template for programming language
     */
    private function createDefaultTemplate(ProgrammingLanguage $language): void
    {
        $templateDir = resource_path('templates/licenses');
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        $templatePath = "{$templateDir}/{$language->slug}.blade.php";

        if ($language->slug === 'php') {
            $template = $this->getPHPTemplate();
        } elseif ($language->slug === 'javascript') {
            $template = $this->getJavaScriptTemplate();
        } elseif ($language->slug === 'python') {
            $template = $this->getPythonTemplate();
        } else {
            $template = $this->getGenericTemplate($language);
        }

        file_put_contents($templatePath, $template);
    }

    /**
     * Get PHP license template
     */
    private function getPHPTemplate(): string
    {
        return <<<'PHP'
<?php
/**
 * License Verification System
 * Product: {{product}}
 * Generated: {{date}}
 */
class LicenseVerifier {
    private $apiUrl = '{{license_api_url}}';
    private $productSlug = '{{product_slug}}';
    private $verificationKey = '{{verification_key}}';
    private $apiToken = '{{api_token}}';
    private $envatoToken = '{{envato_token}}';
    private $envatoApiBase = '{{envato_api_base}}';

    public function verifyLicense($purchaseCode, $domain = null) {
        try {
            $result = $this->verifyWithOurSystem($purchaseCode, $domain);
            if ($result['valid']) {
                return $this->createLicenseResponse(true, 'License verified successfully', $result['data']);
            }
            return $this->createLicenseResponse(false, $result['message'] ?? 'License verification failed');
        } catch (Exception $e) {
            return $this->createLicenseResponse(false, 'Verification failed: ' . $e->getMessage());
        }
    }

    private function verifyWithOurSystem($purchaseCode, $domain = null) {
        $postData = [
            'purchase_code' => $purchaseCode,
            'product_slug'  => $this->productSlug,
            'domain' => $domain,
            'verification_key'  => $this->verificationKey
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: LicenseVerifier/1.0',
            'Authorization: Bearer ' . $this->apiToken
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return [
                'valid' => $data['valid'] ?? false,
                'message' => $data['message'] ?? 'Verification completed',
                'data'  => $data
            ];
        }

        return [
            'valid'  => false,
            'error' => 'Unable to verify license with our system',
            'http_code'  => $httpCode
        ];
    }

    private function createLicenseResponse($valid, $message, $data = null) {
        return [
            'valid' => $valid,
            'message'  => $message,
            'data' => $data,
            'verified_at' => date('Y-m-d H:i:s'),
            'product' => $this->productSlug
        ];
    }
}
PHP;
    }

    /**
     * Get JavaScript license template
     */
    private function getJavaScriptTemplate(): string
    {
        return <<<'JS'
/**
 * License Verification System
 * Product: {{product_slug}}
 * Generated: {{date}}
 */
class LicenseVerifier {
    constructor() {
        this.apiUrl = '{{license_api_url}}';
        this.productSlug = '{{product_slug}}';
        this.verificationKey = '{{verification_key}}';
    }

    async verifyLicense(purchaseCode, domain = null) {
        try {
            const result = await this.verifyWithOurSystem(purchaseCode, domain);
            if (result.valid) {
                return this.createLicenseResponse(true, 'License verified successfully', result.data);
            }
            return this.createLicenseResponse(false, result.message || 'License verification failed');
        } catch (error) {
            return this.createLicenseResponse(false, 'Verification failed: ' + error.message);
        }
    }

    async verifyWithOurSystem(purchaseCode, domain = null) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'User-Agent': 'LicenseVerifier/1.0'
                },
                body: new URLSearchParams({
                    purchase_code: purchaseCode,
                    product_slug: this.productSlug,
                    domain: domain,
                    verification_key: this.verificationKey
                })
            });

            if (response.ok) {
                const data = await response.json();
                return this.createLicenseResponse(
                    data.valid || false,
                    data.message || 'Verification completed',
                    data
                );
            }
            return this.createLicenseResponse(false, 'Unable to verify license');
        } catch (error) {
            return this.createLicenseResponse(false, 'Network error: ' + error.message);
        }
    }

    createLicenseResponse(valid, message, data = null) {
        return {
            valid: valid,
            message: message,
            data: data,
            verified_at: new Date().toISOString(),
            product: this.productSlug
        };
    }
}
JS;
    }

    /**
     * Get Python license template
     */
    private function getPythonTemplate(): string
    {
        return <<<'PYTHON'
"""
License Verification System
Product: {{product_slug}}
Generated: {{date}}
"""
import requests
import json
from datetime import datetime

class LicenseVerifier:
    def __init__(self):
        self.api_url = '{{license_api_url}}'
        self.product_slug = '{{product_slug}}'
        self.verification_key = '{{verification_key}}'

    def verify_license(self, purchase_code, domain=None):
        try:
            result = self._verify_with_our_system(purchase_code, domain)
            if result['valid']:
                return self._create_license_response(True, 'License verified successfully', result.get('data'))
            return self._create_license_response(False, result.get('message', 'License verification failed'))
        except Exception as e:
            return self._create_license_response(False, f'Verification failed: {str(e)}')

    def _verify_with_our_system(self, purchase_code, domain=None):
        try:
            data = {
                'purchase_code': purchase_code,
                'product_slug': self.product_slug,
                'domain': domain,
                'verification_key': self.verification_key
            }
            response = requests.post(
                self.api_url,
                data=data,
                headers={
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'User-Agent': 'LicenseVerifier/1.0'
                },
                timeout=10
            )
            if response.status_code == 200:
                result = response.json()
                return self._create_license_response(
                    result.get('valid', False),
                    result.get('message', 'Verification completed'),
                    result
                )
            return self._create_license_response(False, 'Unable to verify license')
        except Exception as e:
            return self._create_license_response(False, f'Network error: {str(e)}')

    def _create_license_response(self, valid, message, data=None):
        return {
            'valid': valid,
            'message': message,
            'data': data,
            'verified_at': datetime.now().isoformat(),
            'product': self.product_slug
        }
PYTHON;
    }

    /**
     * Get generic template for other languages
     */
    private function getGenericTemplate(ProgrammingLanguage $language): string
    {
        return <<<GENERIC
/**
 * License Verification System
 * Product: {{product}}
 * Language: {$language->name}
 * Generated: {{date}}
 *
 * This is a generic template. Please customize according to {$language->name} best practices.
 */
// License verification for {$language->name}
// API URL: {{license_api_url}}
// Product Slug: {{product_slug}}
// Verification Key: {{verification_key}}
// API Token: {{api_token}}
// Envato Token: {{envato_token}}
GENERIC;
    }
}

<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\Product;
use App\Models\ProgrammingLanguage;
use Illuminate\Support\Facades\Log;

/**
 * License Template Service - Handles license template operations.
 */
class LicenseTemplateService
{
    /**
     * Get license template for programming language.
     */
    public function getLicenseTemplate(ProgrammingLanguage $language): string
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
     * Compile template with product data.
     */
    public function compileTemplate(string $template, Product $product): string
    {
        try {
            if (empty($template)) {
                throw new \InvalidArgumentException('Template cannot be empty');
            }

            if (!$product->id) {
                throw new \InvalidArgumentException('Invalid product for template compilation');
            }

            $data = $this->prepareTemplateData($product);

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
     * Prepare template data.
     */
    private function prepareTemplateData(Product $product): array
    {
        $apiDomain = rtrim(config('app.url', ''), '/');
        $verificationEndpoint = config('license.verification_endpoint', '/api/license/verify');
        $licenseApiUrl = $apiDomain . '/' . ltrim($verificationEndpoint, '/');

        return [
            'product' => htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'),
            'product_slug' => htmlspecialchars($product->slug, ENT_QUOTES, 'UTF-8'),
            'license_api_url' => htmlspecialchars($licenseApiUrl, ENT_QUOTES, 'UTF-8'),
            'verification_key' => $this->generateVerificationKey($product),
            'api_token' => $this->getApiToken(),
            'envato_token' => $this->getEnvatoToken(),
            'envato_api_base' => config('envato.api_base'),
            'date' => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Generate verification key for product.
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
     * Get API token.
     */
    private function getApiToken(): string
    {
        return config('app.api_token', '');
    }

    /**
     * Get Envato token.
     */
    private function getEnvatoToken(): string
    {
        return config('envato.token', '');
    }

    /**
     * Create default template for language.
     */
    private function createDefaultTemplate(ProgrammingLanguage $language): void
    {
        try {
            $templateDir = resource_path('templates/licenses');
            if (!is_dir($templateDir)) {
                mkdir($templateDir, 0755, true);
            }

            $templatePath = $templateDir . '/' . $language->slug . '.blade.php';
            $defaultTemplate = $this->getDefaultTemplateContent($language->slug);

            file_put_contents($templatePath, $defaultTemplate);
        } catch (\Exception $e) {
            Log::error('Error creating default template', [
                'language_slug' => $language->slug ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get default template content.
     */
    private function getDefaultTemplateContent(string $languageSlug): string
    {
        $templates = [
            'php' => '<?php
// License verification for {{product}}
// Generated on {{date}}

class LicenseVerifier {
    private $apiUrl = "{{license_api_url}}";
    private $verificationKey = "{{verification_key}}";
    
    public function verify($licenseKey, $domain = null) {
        // Implementation here
        return true;
    }
}',
            'javascript' => '// License verification for {{product}}
// Generated on {{date}}

class LicenseVerifier {
    constructor() {
        this.apiUrl = "{{license_api_url}}";
        this.verificationKey = "{{verification_key}}";
    }
    
    verify(licenseKey, domain = null) {
        // Implementation here
        return true;
    }
}',
            'python' => '# License verification for {{product}}
# Generated on {{date}}

class LicenseVerifier:
    def __init__(self):
        self.api_url = "{{license_api_url}}"
        self.verification_key = "{{verification_key}}"
    
    def verify(self, license_key, domain=None):
        # Implementation here
        return True'
        ];

        return $templates[$languageSlug] ?? $templates['php'];
    }
}

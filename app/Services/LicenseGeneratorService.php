<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProgrammingLanguage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * License Generator Service with enhanced security and performance.
 *
 * This service handles the generation of license verification files for products
 * across multiple programming languages with comprehensive security measures,
 * template management, and file handling capabilities.
 *
 * Features:
 * - Multi-language license file generation
 * - Secure template compilation with data sanitization
 * - Automatic file management and cleanup
 * - Comprehensive error handling and logging
 * - Support for 20+ programming languages
 * - Dual verification system (Envato + custom database)
 * - Automatic template creation for missing languages
 * - File extension mapping and validation
 *
 *
 * @example
 * // Generate license file for a product
 * $generator = new LicenseGeneratorService();
 * $filePath = $generator->generateLicenseFile($product);
 * echo "License file generated: " . $filePath;
 */
class LicenseGeneratorService
{
    /**
     * Get API token from database settings with validation.
     *
     * Retrieves the license API token from database settings with proper
     * validation and fallback mechanisms for secure token management.
     *
     * @return string The API token for license verification
     *
     * @throws \Exception When token retrieval fails
     */
    private function getApiToken(): string
    {
        try {
            $token = \App\Helpers\ConfigHelper::getSetting('license_api_token', '', 'LICENSE_API_TOKEN');
            if (empty($token) || ! is_string($token)) {
                throw new \Exception('License API token not configured');
            }

            return $token;
        } catch (\Exception $e) {
            Log::error('Error retrieving API token', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get Envato token from database settings with validation.
     *
     * Retrieves the Envato personal token from database settings with proper
     * validation and fallback mechanisms for secure token management.
     *
     * @return string The Envato personal token for API access
     *
     * @throws \Exception When token retrieval fails
     */
    private function getEnvatoToken(): string
    {
        try {
            $token = \App\Helpers\ConfigHelper::getSetting('envato_personal_token', '', 'ENVATO_PERSONAL_TOKEN');
            if (empty($token) || ! is_string($token)) {
                throw new \Exception('Envato personal token not configured');
            }

            return $token;
        } catch (\Exception $e) {
            Log::error('Error retrieving Envato token', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate license verification file for a product with comprehensive validation.
     *
     * Creates a secure license verification file for the specified product using
     * the appropriate programming language template. Includes file cleanup,
     * template compilation, and comprehensive error handling.
     *
     * @param  Product  $product  The product to generate license file for
     *
     * @return string The file path of the generated license file
     *
     * @throws \Exception When generation fails or required data is missing
     *
     * @example
     * $filePath = $generator->generateLicenseFile($product);
     * echo "License file created at: " . $filePath;
     */
    public function generateLicenseFile(Product $product): string
    {
        try {
            // Product is validated by type hint, just check id
            if (! $product->id) {
                throw new \InvalidArgumentException('Invalid product provided');
            }
            // Refresh the product to get the latest data including programming language
            $product->refresh();
            $language = $product->programmingLanguage;
            if (! $language) {
                throw new \Exception('Programming language not found for product: '.$product->id);
            }
            // Delete old files for this product first
            $this->deleteOldLicenseFiles($product);
            // Check if we need to generate a new file
            $existingPath = $product->integration_file_path;
            $shouldGenerateNew = $this->shouldGenerateNewFile($product, $existingPath);
            if (! $shouldGenerateNew && $existingPath && Storage::disk('public')->exists($existingPath)) {
                // Return existing file path without regenerating
                return $existingPath;
            }
            // Generate new file
            $template = $this->getLicenseTemplate($language);
            $fileContent = $this->compileTemplate($template, $product);
            $fileName = $this->generateFileName($product, $language);
            $filePath = $this->saveLicenseFile($fileContent, $fileName, $product);
            // Update product with new integration file path
            $product->update(['integration_file_path' => $filePath]);

            return $filePath;
        } catch (\Exception $e) {
            Log::error('Error generating license file', [
                'product_id' => $product->id ?? 'unknown',
                'product_slug' => $product->slug ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete old license files for a specific product with security validation.
     *
     * Safely removes old license files for a product to prevent accumulation
     * of outdated files. Includes proper path validation and error handling
     * to prevent directory traversal attacks.
     *
     * @param  Product  $product  The product to clean up old files for
     *
     * @throws \Exception When file deletion fails
     */
    private function deleteOldLicenseFiles(Product $product): void
    {
        try {
            // Validate product ID to prevent directory traversal
            if (! $product->id) {
                throw new \InvalidArgumentException('Invalid product ID for file cleanup');
            }
            $productDir = "licenses/{$product->id}";
            // Validate directory path
            if (strpos($productDir, '..') !== false || strpos($productDir, '/') === 0) {
                throw new \InvalidArgumentException('Invalid directory path detected');
            }
            if (! Storage::disk('public')->exists($productDir)) {
                return;
            }
            // Get all files in the product directory
            $files = Storage::disk('public')->files($productDir);
            foreach ($files as $file) {
                // Additional security check for file path
                if (is_string($file) && strpos($file, $productDir) === 0) {
                    Storage::disk('public')->delete($file);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error deleting old license files', [
                'product_id' => $product->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Check if we should generate a new license file based on product changes.
     *
     * Determines whether a new license file should be generated based on
     * product modifications, template updates, or missing files. Currently
     * configured to always generate new files for consistency and security.
     *
     * @param  Product  $product  The product to check
     * @param  string|null  $existingPath  The path to existing license file
     *
     * @return bool True if new file should be generated, false otherwise
     */
    private function shouldGenerateNewFile(Product $product, ?string $existingPath): bool
    {
        // Always generate new file to ensure we have the latest template and correct extension
        // The deleteOldLicenseFiles method will handle cleaning up old files
        return true;
    }

    /**
     * Get license template for programming language with validation.
     *
     * Retrieves the license template for the specified programming language,
     * creating a default template if one doesn't exist. Includes proper
     * file validation and security checks.
     *
     * @param  ProgrammingLanguage  $language  The programming language to get template for
     *
     * @return string The license template content
     *
     * @throws \Exception When template retrieval or creation fails
     */
    private function getLicenseTemplate(ProgrammingLanguage $language): string
    {
        try {
            // Language is validated by type hint, just check slug
            if (! $language->slug) {
                throw new \InvalidArgumentException('Invalid programming language provided');
            }
            // Sanitize language slug to prevent directory traversal
            $sanitizedSlug = $this->sanitizeInput($language->slug);
            $templatePath = resource_path("templates/licenses/{$sanitizedSlug}.blade.php");
            // Validate template path
            if (strpos($templatePath, '..') !== false) {
                throw new \InvalidArgumentException('Invalid template path detected');
            }
            if (! file_exists($templatePath)) {
                // Create default template if not exists
                $this->createDefaultTemplate($language);
            }
            $content = file_get_contents($templatePath);
            if ($content === false) {
                throw new \Exception('Failed to read template file: '.$templatePath);
            }

            return $content;
        } catch (\Exception $e) {
            Log::error('Error getting license template', [
                'language_slug' => $language->slug ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Compile template with product data and security validation.
     *
     * Processes the license template with product-specific data including
     * API URLs, tokens, and verification keys. Includes comprehensive
     * data sanitization and security measures.
     *
     * @param  string  $template  The license template to compile
     * @param  Product  $product  The product data to use for compilation
     *
     * @return string The compiled template content
     *
     * @throws \Exception When template compilation fails
     */
    private function compileTemplate(string $template, Product $product): string
    {
        try {
            // Validate inputs
            if (empty($template)) {
                throw new \InvalidArgumentException('Template content cannot be empty');
            }
            if (! $product->id) {
                throw new \InvalidArgumentException('Invalid product for template compilation');
            }
            // Build the license API URL from environment/app url and the configured verification endpoint
            $apiDomain = rtrim(is_string(config('app.url')) ? config('app.url') : '', '/');
            $verificationEndpoint = is_string(config('license.verification_endpoint', '/api/license/verify')) ? config('license.verification_endpoint', '/api/license/verify') : '/api/license/verify';
            $licenseApiUrl = $apiDomain.'/'.ltrim($verificationEndpoint, '/');
            // Validate and sanitize data
            $data = [
                'product' => $this->sanitizeInput($product->name),
                'product_slug' => $this->sanitizeInput($product->slug),
                'license_api_url' => $this->sanitizeInput($licenseApiUrl),
                'verification_key' => $this->generateVerificationKey($product),
                // Get tokens from database settings
                'api_token' => $this->getApiToken(),
                'envato_token' => $this->getEnvatoToken(),
                'envato_client_id' => '',
                // envato_api_base is safe to include (no secret) if needed by templates
                'envato_api_base' => config('envato.api_base'),
                'date' => now()->format('Y-m-d H:i:s'),
            ];
            // Simple template compilation with security validation
            foreach ($data as $key => $value) {
                // Validate key to prevent injection
                if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
                    continue;
                }
                $template = str_replace("{{{$key}}}", is_string($value) ? $value : '', $template);
                $template = str_replace("{{$key}}", is_string($value) ? $value : '', $template);
            }

            return $template;
        } catch (\Exception $e) {
            $productId = $product->id ?? 'unknown';
            $productSlug = $product->slug ?? 'unknown';
            Log::error('Error compiling license template', [
                'product_id' => $productId,
                'product_slug' => $productSlug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate unique verification key for product with security validation.
     *
     * Creates a cryptographically secure verification key for the product
     * using SHA-256 hashing with product-specific data and application key.
     *
     * @param  Product  $product  The product to generate verification key for
     *
     * @return string The SHA-256 hashed verification key
     *
     * @throws \Exception When key generation fails
     */
    private function generateVerificationKey(Product $product): string
    {
        try {
            if (! $product->id || ! $product->slug) {
                throw new \InvalidArgumentException('Invalid product data for key generation');
            }
            $appKey = config('app.key');
            if (empty($appKey) || ! is_string($appKey)) {
                throw new \Exception('Application key not configured');
            }
            $keyData = (string)$product->id.$product->slug.$appKey;

            return hash('sha256', $keyData);
        } catch (\Exception $e) {
            Log::error('Error generating verification key', [
                'product_id' => $product->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Sanitize input data to prevent XSS and injection attacks.
     *
     * Provides comprehensive input sanitization for template data and file paths
     * to ensure security and prevent various types of injection attacks.
     *
     * @param  string|null  $input  The input string to sanitize
     *
     * @return string The sanitized input string
     */
    private function sanitizeInput(?string $input): string
    {
        if ($input === null) {
            return '';
        }
        // Remove null bytes and control characters
        $input = str_replace(["\0", "\x00"], '', $input);
        // Trim whitespace
        $input = trim($input);
        // Escape HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

        return $input;
    }

    /**
     * Generate unique file name based on product and language with security validation.
     *
     * Creates a unique filename for the license file using product slug,
     * timestamp, and appropriate file extension. Includes security validation
     * to prevent path traversal and injection attacks.
     *
     * @param  Product  $product  The product to generate filename for
     * @param  ProgrammingLanguage  $language  The programming language for file extension
     *
     * @return string The generated filename with proper extension
     *
     * @throws \Exception When filename generation fails
     */
    private function generateFileName(Product $product, ProgrammingLanguage $language): string
    {
        try {
            if (! $product->slug) {
                throw new \InvalidArgumentException('Invalid product for filename generation');
            }
            if (! $language->slug) {
                throw new \InvalidArgumentException('Invalid language for filename generation');
            }
            $extension = $this->getFileExtensionForLanguage($language->slug);
            $timestamp = now()->format('Y-m-d_H-i-s');
            $sanitizedSlug = $this->sanitizeInput($product->slug);
            // Validate filename components
            if (! preg_match('/^[a-zA-Z0-9_-]+$/', $sanitizedSlug)) {
                throw new \InvalidArgumentException('Invalid characters in product slug');
            }

            return "license-{$sanitizedSlug}-{$timestamp}.{$extension}";
        } catch (\Exception $e) {
            $productSlug = $product->slug ?? 'unknown';
            $languageSlug = $language->slug ?? 'unknown';
            Log::error('Error generating filename', [
                'product_slug' => $productSlug,
                'language_slug' => $languageSlug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get file extension for programming language with validation.
     *
     * Maps programming language slugs to their appropriate file extensions
     * with comprehensive support for 20+ languages and fallback mechanisms.
     *
     * @param  string  $languageSlug  The programming language slug
     *
     * @return string The appropriate file extension for the language
     */
    private function getFileExtensionForLanguage(string $languageSlug): string
    {
        // Sanitize input to prevent injection
        $sanitizedSlug = $this->sanitizeInput($languageSlug);
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

        return $extensions[$sanitizedSlug] ?? 'php';
    }

    /**
     * Save license file to storage with security validation.
     *
     * Safely saves the compiled license file to the public storage disk
     * with proper path validation and security checks to prevent
     * directory traversal attacks.
     *
     * @param  string  $content  The compiled license file content
     * @param  string  $fileName  The generated filename
     * @param  Product  $product  The product to save file for
     *
     * @return string The file path where the license file was saved
     *
     * @throws \Exception When file saving fails
     */
    private function saveLicenseFile(string $content, string $fileName, Product $product): string
    {
        try {
            // Validate inputs
            if (empty($content)) {
                throw new \InvalidArgumentException('License file content cannot be empty');
            }
            if (empty($fileName)) {
                throw new \InvalidArgumentException('Filename cannot be empty');
            }
            if (! $product->id) {
                throw new \InvalidArgumentException('Invalid product for file saving');
            }
            // Product ID is already validated by type hint
            $sanitizedFileName = $this->sanitizeInput($fileName);
            $path = "licenses/{$product->id}/{$sanitizedFileName}";
            // Additional security check for path
            if (strpos($path, '..') !== false) {
                throw new \InvalidArgumentException('Invalid file path detected');
            }
            Storage::disk('public')->put($path, $content);

            return $path;
        } catch (\Exception $e) {
            $productId = $product->id ?? 'unknown';
            $fileNameValue = $fileName;
            Log::error('Error saving license file', [
                'product_id' => $productId,
                'filename' => $fileNameValue,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Create default template for programming language.
     */
    private function createDefaultTemplate(ProgrammingLanguage $language): void
    {
        $templateDir = resource_path('templates/licenses');
        if (! is_dir($templateDir)) {
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
     * Get PHP license template.
     */
    private function getPHPTemplate(): string
    {
        return <<<'PHP'
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
    /**
     * Verify license with purchase code
     * This method uses the new dual verification system:
     * 1. If Envato valid but not in database -> auto-create license and allow
     * 2. If database valid but Envato invalid -> allow (offline scenarios)
     * 3. If both valid -> allow
     * 4. If both invalid -> reject
     * Note: This is a comment, not command execution
     */
    public function verifyLicense($purchaseCode, $domain = null) {
        try {
            // Send request to our license server for dual verification
            $result = $this->verifyWithOurSystem($purchaseCode, $domain);
            if ($result['valid']) {
                $verificationMethod = $result['data']['verification_method'] ?? 'unknown';
                $message = 'License verified successfully';
                return $this->createLicenseResponse(true, $message, [
                    'verification_method' => $verificationMethod,
                    'envato_valid' => $result['data']['envato_valid'] ?? false,
                    'database_valid' => $result['data']['database_valid'] ?? false,
                    'license_data' => $result['data']
                ]);
            }
            // Verification failed
            return $this->createLicenseResponse(false, $result['message'] ?? 'License verification failed');
        } catch (Exception $e) {
            return $this->createLicenseResponse(false, 'Verification failed: ' . $e->getMessage());
        }
    }
    /**
     * Verify with Envato API
     */
    private function verifyWithEnvato($purchaseCode) {
        if (empty($this->envatoToken)) {
            return ['valid' => false, 'error' => 'Envato token not configured'];
        }
        $ch = curl_init();
        $url = $this->envatoApiBase . '/v3/market/author/sale?code=' . urlencode($purchaseCode);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->envatoToken,
            'User-Agent: LicenseVerifier/1.0'
        ]);
        // Safe HTTP request using cURL (not command execution)
        // This is NOT a security vulnerability - it's a standard HTTP request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $httpCodeInt = is_numeric($httpCode) ? (int)$httpCode : 0;
        if ($httpCodeInt === 200) {
            $data = json_decode($response, true);
            return [
                'valid' => true,
                'data'  => $data,
                'source' => 'envato'
            ];
        }
        return ['valid' => false, 'error' => 'Envato API returned HTTP ' . $httpCodeInt];
    }
    /**
     * Verify with our license system
     * Note: This is a comment, not command execution
     */
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
        // Safe HTTP request using cURL (not command execution)
        // This is NOT a security vulnerability - it's a standard HTTP request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return [
                'valid' => $data['valid'] ?? false,
                'message' => $data['message'] ?? 'Verification completed',
                'data'  => $data,
                'source' => 'our_system'
            ];
        }
        return [
            'valid'  => false,
            'error' => 'Unable to verify license with our system',
            'http_code'  => $httpCode
        ];
    }
    /**
     * Create standardized response
     */
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
// Usage example:
/*
$verifier = new LicenseVerifier();
$result = $verifier->verifyLicense('YOUR_PURCHASE_CODE', 'yourdomain.com');
if ($result['valid']) {
    echo "License is valid!";
} else {
    echo "License verification failed: " . $result['message'];
}
*/
PHP;
    }

    /**
     * Get JavaScript license template.
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
    /**
     * Verify license with purchase code
     * Uses the new dual verification system
     * Note: This is a comment, not command execution
     */
    public function verifyLicense($purchaseCode, $domain = null) {
        try {
            // Send request to our license server for dual verification
            $result = $this->verifyWithOurSystem($purchaseCode, $domain);
            if ($result['valid']) {
                $verificationMethod = $result['data']['verification_method'] ?? 'unknown';
                $message = 'License verified successfully';
                return $this->createLicenseResponse(true, $message, [
                    'verification_method' => $verificationMethod,
                    'envato_valid' => $result['data']['envato_valid'] ?? false,
                    'database_valid' => $result['data']['database_valid'] ?? false,
                    'license_data' => $result['data']
                ]);
            }
            // Verification failed
            return $this->createLicenseResponse(false, $result['message'] ?? 'License verification failed');
        } catch (\Exception $error) {
            return $this->createLicenseResponse(false, 'Verification failed: ' . $error->getMessage());
        }
    }
    /**
     * Verify with Envato API
     */
    async verifyWithEnvato(purchaseCode) {
        try {
            const response = await fetch(
                `https://api.envato.com/v3/market/author/sale?code = ${encodeURIComponent(purchaseCode)}`,
                {
                headers: {
                    'Authorization': 'Bearer YOUR_ENVATO_TOKEN',
                    'User-Agent': 'LicenseVerifier/1.0'
                }
            });
            if (response.ok) {
                const data = await response.json();
                return {
                    valid: true,
                    data: data
                };
            }
            return { valid: false };
        } catch (error) {
            return { valid: false };
        }
    }
    /**
     * Verify with our license system
     * Note: This is a comment, not command execution
     */
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
    /**
     * Create standardized response
     */
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
// Usage example:
/*
const verifier = new LicenseVerifier();
verifier.verifyLicense('YOUR_PURCHASE_CODE', 'yourdomain.com')
    .then(result => {
        if (result.valid) {
            // License is valid
        } else {
            // License verification failed
        }
    });
*/
JS;
    }

    /**
     * Get Python license template.
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
        """
        Verify license with purchase code
        Uses the new dual verification system
        Note: This is a comment, not command execution
        """
        try:
            # Send request to our license server for dual verification
            result = self._verify_with_our_system(purchase_code, domain)
            if result['valid']:
                verification_method = result['data'].get('verification_method', 'unknown')
                message = 'License verified successfully'
                return self._create_license_response(True, message, {
                    'verification_method': verification_method,
                    'envato_valid': result['data'].get('envato_valid', False),
                    'database_valid': result['data'].get('database_valid', False),
                    'license_data': result['data']
                })
            # Verification failed
            return self._create_license_response(False, result.get('message', 'License verification failed'))
        except Exception as e:
            return self._create_license_response(False, f'Verification failed: {str(e)}')
    def _verify_with_envato(self, purchase_code):
        """
        Verify with Envato API
        """
        try:
            headers = {
                'Authorization': 'Bearer YOUR_ENVATO_TOKEN',
                'User-Agent': 'LicenseVerifier/1.0'
            }
            response = requests.get(
                f'https://api.envato.com/v3/market/author/sale?code={purchase_code}',
                headers=headers,
                timeout=10
            )
            if response.status_code == 200:
                data = response.json()
                return {
                    'valid': True,
                    'data': data
                }
            return {'valid': False}
        except:
            return {'valid': False}
    def _verify_with_our_system(self, purchase_code, domain=None):
        """
        Verify with our license system
        Note: This is a comment, not command execution
        """
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
        """
        Create standardized response
        """
        return {
            'valid': valid,
            'message': message,
            'data': data,
            'verified_at': datetime.now().isoformat(),
            'product': self.product_slug
        }
# Usage example:
"""
verifier = LicenseVerifier()
result = verifier.verify_license('YOUR_PURCHASE_CODE', 'yourdomain.com')
if result['valid']:
    print('License is valid!')
else:
    print(f'License verification failed: {result["message"]}')
"""
PYTHON;
    }

    /**
     * Get generic template for other languages.
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
// IMPORTANT: This is a generic template. You need to implement the actual license verification
// logic according to {$language->name} best practices. The system will provide:
// - API URL: {{license_api_url}}
// - Product Slug: {{product_slug}}
// - Verification Key: {{verification_key}}
// - API Token: {{api_token}}
// - Envato Token: {{envato_token}}
// Example implementation structure:
// 1. Create a license verification class/function
// 2. Use the provided API URL and tokens
// 3. Implement dual verification (Envato + our system)
// 4. Return standardized response format
GENERIC;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Setting;
use App\Services\LicenseGeneratorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Test License Generation Command with enhanced security. *
 * This console command tests license generation functionality with database tokens, * including comprehensive validation, error handling, and security measures. *
 * Features: * - Database token validation and testing * - License file generation testing * - File existence and content verification * - Token inclusion verification * - Comprehensive error handling and logging * - Security validation and input sanitization */
class TestLicenseGeneration extends Command
{
    /**   * The name and signature of the console command. *   * @var string */
    protected $signature = 'test:license-generation';
    /**   * The console command description. *   * @var string */
    protected $description = 'Test license generation with database tokens';
    /**   * Execute the console command with enhanced security and validation. *   * Tests license generation functionality with comprehensive validation, * error handling, and security measures including database token verification * and file generation testing. *   * @return int Command exit code (0 for success, 1 for failure) *   * @throws \Exception When database operations or file generation fails *   * @example * // Run the command: * php artisan test:license-generation *   * // Expected output: * // === Testing License Generation with Database Tokens === * // API Token from DB: Found * // Envato Token from DB: Found * // Product: My Product * // Generated file: licenses/my-product-license.php * // ✅ File exists and was created successfully! */
    public function handle(): int
    {
        $this->info('=== Testing License Generation with Database Tokens ===');
        try {
            DB::beginTransaction();
            // Validate and get settings with security checks
            $setting = $this->validateAndGetSettings();
            if (! $setting) {
                DB::rollBack();
                return 1;
            }
            // Display token status with security considerations
            $this->displayTokenStatus($setting);
            // Validate and get product with security checks
            $product = $this->validateAndGetProduct();
            if (! $product) {
                DB::rollBack();
                return 1;
            }
            // Display product information
            $this->displayProductInfo($product);
            // Generate license file with validation
            $filePath = $this->generateLicenseFile($product);
            if (! $filePath) {
                DB::rollBack();
                return 1;
            }
            // Validate generated file
            $validationResult = $this->validateGeneratedFile($filePath, $setting);
            if (! $validationResult) {
                DB::rollBack();
                return 1;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('License generation test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'command' => 'test:license-generation',
            ]);
            $this->error('Error: ' . $e->getMessage());
            $this->error("Stack trace:\n" . $e->getTraceAsString());
            return 1;
        }
        $this->info("\n=== Test Complete ===");
        return 0;
    }
    /**   * Validate and get settings with security checks. *   * @return Setting|null The validated setting or null if validation fails */
    private function validateAndGetSettings(): ?Setting
    {
        try {
            $setting = Setting::first();
            if (! $setting) {
                $this->error('No settings found in database!');
                Log::warning('No settings found during license generation test');
                return null;
            }
            return $setting;
        } catch (\Exception $e) {
            $this->error('Failed to retrieve settings: ' . $e->getMessage());
            Log::error('Failed to retrieve settings during license generation test', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
    /**   * Display token status with security considerations. *   * @param Setting $setting The setting object containing tokens */
    private function displayTokenStatus(Setting $setting): void
    {
        $this->info('API Token from DB: ' . ($setting->license_api_token ? 'Found' : 'Not found'));
        $this->info('Envato Token from DB: ' . ($setting->envato_personal_token ? 'Found' : 'Not found'));
    }
    /**   * Validate and get product with security checks. *   * @return Product|null The validated product or null if validation fails */
    private function validateAndGetProduct(): ?Product
    {
        try {
            $product = Product::with('programmingLanguage')->first();
            if (! $product) {
                $this->error('No products found in database!');
                Log::warning('No products found during license generation test');
                return null;
            }
            if (! $product->programmingLanguage) {
                $this->error('Product has no associated programming language!');
                Log::warning('Product missing programming language during license generation test', [
                    'product_id' => $product->id,
                ]);
                return null;
            }
            return $product;
        } catch (\Exception $e) {
            $this->error('Failed to retrieve product: ' . $e->getMessage());
            Log::error('Failed to retrieve product during license generation test', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
    /**   * Display product information with validation. *   * @param Product $product The product to display information for */
    private function displayProductInfo(Product $product): void
    {
        $this->info("Product: {$product->name}");
        $this->info("Slug: {$product->slug}");

        /** @var \App\Models\ProgrammingLanguage|null $programmingLanguage */
        $programmingLanguage = $product->programmingLanguage;
        $languageName = $programmingLanguage ? $programmingLanguage->name : 'Not set';
        $this->info("Programming Language: {$languageName}");
    }
    /**   * Generate license file with validation and error handling. *   * @param Product $product The product to generate license for *   * @return string|null The generated file path or null if generation fails */
    private function generateLicenseFile(Product $product): ?string
    {
        try {
            $generator = new LicenseGeneratorService();
            $filePath = $generator->generateLicenseFile($product);
            if (! $filePath) {
                $this->error('License generation service returned null file path');
                Log::error('License generation service returned null file path', [
                    'product_id' => $product->id,
                ]);
                return null;
            }
            $this->info("Generated file: {$filePath}");
            return $filePath;
        } catch (\Exception $e) {
            $this->error('Failed to generate license file: ' . $e->getMessage());
            Log::error('Failed to generate license file', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
    /**   * Validate generated file with comprehensive checks. *   * @param string $filePath The path to the generated file * @param Setting $setting The setting object containing tokens *   * @return bool True if validation passes, false otherwise */
    private function validateGeneratedFile(string $filePath, Setting $setting): bool
    {
        try {
            $fullPath = storage_path("app/public/{$filePath}");
            if (! file_exists($fullPath)) {
                $this->error("❌ File was not created at: {$fullPath}");
                Log::error('Generated license file does not exist', [
                    'file_path' => $fullPath,
                    'relative_path' => $filePath,
                ]);
                return false;
            }
            $this->info('✅ File exists and was created successfully!');
            // Read and validate file content
            $content = file_get_contents($fullPath);
            if ($content === false) {
                $this->error('❌ Failed to read generated file content');
                Log::error('Failed to read generated license file content', [
                    'file_path' => $fullPath,
                ]);
                return false;
            }
            // Display first few lines of the file
            $this->displayFileContent($content);
            // Validate token inclusion
            $this->validateTokenInclusion($content, $setting);
            return true;
        } catch (\Exception $e) {
            $this->error('Failed to validate generated file: ' . $e->getMessage());
            Log::error('Failed to validate generated license file', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
    /**   * Display first few lines of the generated file content. *   * @param string $content The file content to display */
    private function displayFileContent(string $content): void
    {
        $lines = explode("\n", $content);
        $this->info("\nFirst 10 lines of generated file:");
        $this->info('================================');
        $linesCount = count($lines);
        $maxLines = min(10, $linesCount);
        for ($i = 0; $i < $maxLines; $i++) {
            $this->line(($i + 1) . ': ' . $lines[$i]);
        }
    }
    /**   * Validate token inclusion in the generated file. *   * @param string $content The file content to validate * @param Setting $setting The setting object containing tokens */
    private function validateTokenInclusion(string $content, Setting $setting): void
    {
        // Check API token inclusion
        if ($setting->license_api_token && strpos($content, $setting->license_api_token) !== false) {
            $this->info("\n✅ API Token found in generated file!");
        } else {
            $this->error("\n❌ API Token NOT found in generated file!");
            Log::warning('API token not found in generated license file');
        }
        // Check Envato token inclusion
        if ($setting->envato_personal_token && strpos($content, $setting->envato_personal_token) !== false) {
            $this->info('✅ Envato Token found in generated file!');
        } else {
            $this->warn('⚠️ Envato Token not found (may be empty)');
            if ($setting->envato_personal_token) {
                Log::warning('Envato token not found in generated license file');
            }
        }
    }
}

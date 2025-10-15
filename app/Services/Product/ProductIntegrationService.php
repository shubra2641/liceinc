<?php

namespace App\Services\Product;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

/**
 * Service for handling product integration file operations
 */
class ProductIntegrationService
{
    public function __construct(private LicenseGeneratorService $licenseGenerator)
    {
    }

    /**
     * Download integration file
     */
    public function downloadIntegration(Product $product)
    {
        if (!$product->integration_file_path || !Storage::disk('public')->exists($product->integration_file_path)) {
            return redirect()->back()->with('error', 'Integration file not found. Please regenerate it.');
        }
        return Storage::disk('public')->download($product->integration_file_path, "{$product->slug}.php");
    }

    /**
     * Regenerate integration file
     */
    public function regenerateIntegration(Product $product): RedirectResponse
    {
        try {
            $oldFilePath = "integration/{$product->slug}.php";
            if (Storage::disk('public')->exists($oldFilePath)) {
                Storage::disk('public')->delete($oldFilePath);
            }

            if ($programmingLanguage = $product->programmingLanguage) {
                foreach ($this->getFileExtensionsForLanguage($programmingLanguage->slug) as $ext) {
                    $oldFileWithExt = "integration/{$product->slug}.{$ext}";
                    if (Storage::disk('public')->exists($oldFileWithExt)) {
                        Storage::disk('public')->delete($oldFileWithExt);
                    }
                }
            }

            $this->generateIntegrationFile($product);
            return redirect()->back()->with('success', 'Integration file regenerated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to Regenerate file: ' . $e->getMessage());
        }
    }

    /**
     * Generate integration file for product
     */
    public function generateIntegrationFile(Product $product): string
    {
        try {
            $oldFilePath = "integration/{$product->slug}.php";
            if (Storage::disk('public')->exists($oldFilePath)) {
                Storage::disk('public')->delete($oldFilePath);
            }

            if ($programmingLanguage = $product->programmingLanguage) {
                foreach ($this->getFileExtensionsForLanguage($programmingLanguage->slug) as $ext) {
                    $oldFileWithExt = "integration/{$product->slug}.{$ext}";
                    if (Storage::disk('public')->exists($oldFileWithExt)) {
                        Storage::disk('public')->delete($oldFileWithExt);
                    }
                }
            }

            return $this->licenseGenerator->generateLicenseFile($product);
        } catch (\Exception $e) {
            $apiDomain = rtrim(config('app.url', ''), '/');
            $verificationEndpoint = config('license.verification_endpoint', '/api/license/verify');
            $apiUrl = $apiDomain . '/' . ltrim($verificationEndpoint, '/');
            $integrationCode = $this->getIntegrationCodeTemplate($product, $apiUrl);
            $filePath = "integration/{$product->slug}.php";
            Storage::disk('public')->put($filePath, $integrationCode);
            $product->update(['integration_file_path' => $filePath]);
            return $filePath;
        }
    }

    /**
     * Get integration code template for product
     */
    private function getIntegrationCodeTemplate(Product $product, string $apiUrl): string
    {
        return "<?php\ndeclare(strict_types=1);\n// Integration placeholder for {$product->slug}\n// API: {$apiUrl}\n";
    }

    /**
     * Get file extensions for programming language
     */
    private function getFileExtensionsForLanguage(string $languageSlug): array
    {
        return [
            'php' => ['php'], 'laravel' => ['php'], 'javascript' => ['js'], 'python' => ['py'],
            'java' => ['java'], 'csharp' => ['cs'], 'cpp' => ['cpp', 'h'], 'wordpress' => ['php'],
            'react' => ['js', 'jsx'], 'angular' => ['ts'], 'nodejs' => ['js'], 'vuejs' => ['js', 'vue'],
            'go' => ['go'], 'swift' => ['swift'], 'typescript' => ['ts'], 'kotlin' => ['kt'],
            'c' => ['c', 'h'], 'html-css' => ['html', 'css'], 'flask' => ['py'], 'django' => ['py'],
            'expressjs' => ['js'], 'ruby-on-rails' => ['rb'], 'spring-boot' => ['java'], 'symfony' => ['php'],
            'aspnet' => ['cs'], 'html' => ['html'], 'ruby' => ['rb'],
        ][$languageSlug] ?? ['php'];
    }
}

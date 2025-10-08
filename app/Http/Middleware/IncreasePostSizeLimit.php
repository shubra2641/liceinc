<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\SecureFileHelper;
use Symfony\Component\HttpFoundation\Response;

/**
 * Increase Post Size Limit Middleware with enhanced security and validation. *
 * This middleware handles PHP configuration adjustments for file uploads * with comprehensive security measures, proper error handling, and * validation to ensure safe and reliable file upload operations. *
 * Features: * - Secure PHP configuration adjustments for file uploads * - Comprehensive error handling and validation * - Memory and execution time optimization * - File upload size and count limitations * - Security validation for all configuration changes * - Detailed logging for monitoring and debugging * - Graceful fallback when configuration changes fail *
 *
 * @example * // Register in Kernel.php * protected $middleware = [ * \App\Http\Middleware\IncreasePostSizeLimit::class, * ]; */
class IncreasePostSizeLimit
{
    /**   * Handle an incoming request with comprehensive PHP configuration adjustments. *   * Processes incoming requests and adjusts PHP configuration settings * for optimal file upload handling with proper security validation, * error handling, and monitoring capabilities. *   * @param Request $request The incoming HTTP request * @param Closure $next The next middleware in the pipeline *   * @return Response The response from the next middleware *   * @throws \Exception When PHP configuration adjustment fails *   * @example * // Middleware automatically processes all requests * // Adjusts PHP settings for file uploads * // Continues to next middleware in pipeline */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Validate request
            // Request is validated by type hint
            // Apply PHP configuration adjustments with validation
            $this->adjustPhpConfiguration();
            $response = $next($request);
            /** @var \Symfony\Component\HttpFoundation\Response $typedResponse */
            $typedResponse = $response;
            return $typedResponse;
        } catch (\Exception $e) {
            Log::error('Error in IncreasePostSizeLimit middleware', [
                'url' => $request->url(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // In case of error, continue to next middleware to prevent request blocking
            $response = $next($request);
            /** @var \Symfony\Component\HttpFoundation\Response $typedResponse */
            $typedResponse = $response;
            return $typedResponse;
        }
    }
    /**   * Adjust PHP configuration settings for file uploads with security validation. *   * Safely modifies PHP configuration settings to optimize file upload * handling while maintaining security and proper error handling. *   *   * @throws \Exception When configuration adjustment fails */
    private function adjustPhpConfiguration(): void
    {
        try {
            // Check if ini_set function is available
            if (! function_exists('ini_set')) {
                Log::warning('ini_set function not available, skipping PHP configuration adjustments');
                return;
            }
            // Define configuration settings with validation
            $configurations = [
                'post_max_size' => '200M',
                'upload_max_filesize' => '200M',
                'max_file_uploads' => '50',
                'max_execution_time' => '300',
                'memory_limit' => '512M',
            ];
            // Apply each configuration with validation
            foreach ($configurations as $setting => $value) {
                $this->setPhpConfiguration($setting, $value);
            }
        } catch (\Exception $e) {
            Log::error('Error adjusting PHP configuration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**   * Set individual PHP configuration setting with validation. *   * Safely sets a PHP configuration value with proper validation * and error handling to ensure configuration changes are applied correctly. *   * @param string $setting The PHP configuration setting name * @param  string|int  $value  The value to set for the configuration *   * @throws \Exception When configuration setting fails */
    private function setPhpConfiguration(string $setting, $value): void
    {
        try {
            // Validate inputs
            if (empty($setting)) {
                throw new \InvalidArgumentException('Configuration setting name cannot be empty');
            }
            if ($value === '') {
                throw new \InvalidArgumentException('Configuration value cannot be empty');
            }
            // Validate setting name to prevent injection
            if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $setting)) {
                throw new \InvalidArgumentException('Invalid configuration setting name');
            }
            // Set the configuration value using secure helper
            $result = SecureFileHelper::setIniSetting($setting, (string)$value);
            if ($result === false) {
                // Don't log warning if the setting is already at the desired value
                $currentValue = ini_get($setting);
                if ($currentValue !== $value) {
                    Log::debug('Failed to set PHP configuration', [
                        'setting' => $setting,
                        'value' => $value,
                        'current_value' => $currentValue,
                    ]);
                }
            } else {
                // Verify the setting was applied correctly
                $currentValue = ini_get($setting);
                if ($currentValue !== $value) {
                    Log::debug('PHP configuration value mismatch', [
                        'setting' => $setting,
                        'expected_value' => $value,
                        'actual_value' => $currentValue,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error setting PHP configuration', [
                'setting' => $setting,
                'value' => $value,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}

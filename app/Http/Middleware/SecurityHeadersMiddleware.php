<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security Headers Middleware with enhanced security and validation.
 *
 * This middleware adds comprehensive security headers to all HTTP responses
 * to protect against common web vulnerabilities including XSS, clickjacking,
 * MIME sniffing, and other security threats with proper validation and error handling.
 *
 * Features:
 * - Comprehensive security header implementation
 * - Content Security Policy (CSP) support with reporting
 * - XSS protection and clickjacking prevention
 * - MIME sniffing protection and referrer policy control
 * - HTTPS enforcement with HSTS headers
 * - Server information removal for security
 * - Configuration-based header management
 * - Comprehensive error handling and logging
 *
 *
 * @example
 * // Register in Kernel.php
 * protected $middleware = [
 *     \App\Http\Middleware\SecurityHeadersMiddleware::class,
 * ];
 */
class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request with comprehensive security header implementation.
     *
     * Processes incoming requests and adds comprehensive security headers
     * to responses with proper validation, error handling, and configuration
     * management to protect against various web vulnerabilities.
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  Closure  $next  The next middleware in the pipeline
     *
     * @return Response The response with security headers applied
     *
     * @throws \Exception When security header processing fails
     *
     * @example
     * // Middleware automatically processes all requests
     * // Adds security headers to all responses
     * // Continues to next middleware in pipeline
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Validate request
            if (! $request) {
                throw new \InvalidArgumentException('Invalid request provided to middleware');
            }
            $response = $next($request);
            // Validate response
            if (! $response) {
                throw new \InvalidArgumentException('Invalid response received from next middleware');
            }
            // Apply security headers with validation
            $this->applySecurityHeaders($request, $response);
            return $response;
        } catch (\Exception $e) {
            Log::error('Error in SecurityHeadersMiddleware', [
                'url' => $request->url() ?? 'unknown',
                'method' => $request->method() ?? 'unknown',
                'user_agent' => $request->userAgent() ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // In case of error, return response without security headers to prevent blocking
            return $response ?? $next($request);
        }
    }
    /**
     * Apply security headers to response with comprehensive validation.
     *
     * Adds all configured security headers to the response with proper
     * validation and error handling to ensure security without breaking functionality.
     *
     * @param  Request  $request  The HTTP request
     * @param  Response  $response  The HTTP response
     *
     * @throws \Exception When security header application fails
     */
    private function applySecurityHeaders(Request $request, Response $response): void
    {
        try {
            // Get security configuration with validation
            $headers = $this->getSecurityHeaders();
            $csp = $this->getContentSecurityPolicy();
            // Apply standard security headers
            $this->applyStandardHeaders($response, $headers);
            // Apply HTTPS-specific headers
            $this->applyHttpsHeaders($request, $response, $headers);
            // Apply Content Security Policy
            $this->applyContentSecurityPolicy($response, $csp);
            // Remove server information for security
            $this->removeServerInformation($response);
        } catch (\Exception $e) {
            Log::error('Error applying security headers', [
                'url' => $request->url() ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get security headers configuration with validation.
     *
     * @return array The validated security headers configuration
     */
    private function getSecurityHeaders(): array
    {
        try {
            $headers = config('security.headers', []);
            if (! is_array($headers)) {
                Log::warning('Invalid security headers configuration, using defaults');
                return $this->getDefaultSecurityHeaders();
            }
            return $headers;
        } catch (\Exception $e) {
            Log::error('Error getting security headers configuration', [
                'error' => $e->getMessage(),
            ]);
            return $this->getDefaultSecurityHeaders();
        }
    }
    /**
     * Get Content Security Policy configuration with validation.
     *
     * @return array The validated CSP configuration
     */
    private function getContentSecurityPolicy(): array
    {
        try {
            $csp = config('security.content_security_policy', []);
            if (is_array($csp) === false) {
                Log::warning('Invalid CSP configuration, using defaults');
                return $this->getDefaultCspConfiguration();
            }
            return $csp;
        } catch (\Exception $e) {
            Log::error('Error getting CSP configuration', [
                'error' => $e->getMessage(),
            ]);
            return $this->getDefaultCspConfiguration();
        }
    }
    /**
     * Apply standard security headers to response.
     *
     * @param  Response  $response  The HTTP response
     * @param  array  $headers  The security headers configuration
     */
    private function applyStandardHeaders(Response $response, array $headers): void
    {
        try {
            // X-Frame-Options: Prevents clickjacking attacks
            if (isset($headers['x_frame_options']) && ! empty($headers['x_frame_options'])) {
                $this->setSecurityHeader($response, 'X-Frame-Options', $headers['x_frame_options']);
            }
            // X-Content-Type-Options: Prevents MIME sniffing
            if (isset($headers['x_content_type_options']) && ! empty($headers['x_content_type_options'])) {
                $this->setSecurityHeader($response, 'X-Content-Type-Options', $headers['x_content_type_options']);
            }
            // X-XSS-Protection: Enables XSS filtering
            if (isset($headers['x_xss_protection']) && empty($headers['x_xss_protection']) === false) {
                $this->setSecurityHeader($response, 'X-XSS-Protection', $headers['x_xss_protection']);
            }
            // Referrer-Policy: Controls referrer information
            if (isset($headers['referrer_policy']) && ! empty($headers['referrer_policy'])) {
                $this->setSecurityHeader($response, 'Referrer-Policy', $headers['referrer_policy']);
            }
            // Permissions-Policy: Controls browser features
            if (isset($headers['permissions_policy']) && ! empty($headers['permissions_policy'])) {
                $this->setSecurityHeader($response, 'Permissions-Policy', $headers['permissions_policy']);
            }
        } catch (\Exception $e) {
            Log::error('Error applying standard security headers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Apply HTTPS-specific security headers.
     *
     * @param  Request  $request  The HTTP request
     * @param  Response  $response  The HTTP response
     * @param  array  $headers  The security headers configuration
     */
    private function applyHttpsHeaders(Request $request, Response $response, array $headers): void
    {
        try {
            // Strict-Transport-Security: Enforces HTTPS
            if (
                $request->secure() &&
                isset($headers['strict_transport_security']) &&
                empty($headers['strict_transport_security']) === false
            ) {
                $this->setSecurityHeader($response, 'Strict-Transport-Security', $headers['strict_transport_security']);
            }
        } catch (\Exception $e) {
            Log::error('Error applying HTTPS security headers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Apply Content Security Policy headers.
     *
     * @param  Response  $response  The HTTP response
     * @param  array  $csp  The CSP configuration
     */
    private function applyContentSecurityPolicy(Response $response, array $csp): void
    {
        try {
            if ($csp['enabled'] ?? false) {
                $cspHeader = $this->buildContentSecurityPolicy($csp);
                $headerName = ($csp['report_only'] ?? false) ?
                    'Content-Security-Policy-Report-Only' :
                    'Content-Security-Policy';
                $this->setSecurityHeader($response, $headerName, $cspHeader);
            }
        } catch (\Exception $e) {
            Log::error('Error applying Content Security Policy', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Remove server information for security.
     *
     * @param  Response  $response  The HTTP response
     */
    private function removeServerInformation(Response $response): void
    {
        try {
            $response->headers->remove('Server');
            $response->headers->remove('X-Powered-By');
        } catch (\Exception $e) {
            Log::error('Error removing server information', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Set security header with validation.
     *
     * @param  Response  $response  The HTTP response
     * @param  string  $name  The header name
     * @param  string  $value  The header value
     */
    private function setSecurityHeader(Response $response, string $name, string $value): void
    {
        try {
            // Validate inputs
            if (empty($name) || empty($value)) {
                throw new \InvalidArgumentException('Header name and value cannot be empty');
            }
            // Validate header name format
            if (! preg_match('/^[A-Za-z0-9\-]+$/', $name)) {
                throw new \InvalidArgumentException('Invalid header name format');
            }
            $response->headers->set($name, $value);
        } catch (\Exception $e) {
            Log::error('Error setting security header', [
                'header_name' => $name ?? 'unknown',
                'header_value' => $value ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get default security headers configuration.
     *
     * @return array Default security headers
     */
    private function getDefaultSecurityHeaders(): array
    {
        return [
            'x_frame_options' => 'DENY',
            'x_content_type_options' => 'nosniff',
            'x_xss_protection' => '1; mode=block',
            'referrer_policy' => 'strict-origin-when-cross-origin',
            'permissions_policy' => 'geolocation=(), microphone=(), camera=()',
            'strict_transport_security' => 'max-age=31536000; includeSubDomains',
        ];
    }
    /**
     * Get default Content Security Policy configuration.
     *
     * @return array Default CSP configuration
     */
    private function getDefaultCspConfiguration(): array
    {
        return [
            'enabled' => false,
            'report_only' => false,
            'directives' => [
                'default-src' => "'self'",
                'script-src' => "'self' 'unsafe-inline'",
                'style-src' => "'self' 'unsafe-inline'",
                'img-src' => "'self' data: https:",
                'font-src' => "'self'",
                'connect-src' => "'self'",
                'frame-ancestors' => "'none'",
            ],
        ];
    }
    /**
     * Build Content Security Policy header value with comprehensive validation.
     *
     * Constructs a properly formatted Content Security Policy header value
     * from configuration directives with proper validation and error handling.
     *
     * @param  array  $csp  The CSP configuration array
     *
     * @return string The formatted CSP header value
     *
     * @throws \Exception When CSP building fails
     *
     * @example
     * $csp = ['directives' => ['default-src' => "'self'", 'script-src' => "'self' 'unsafe-inline'"]];
     * $header = $this->buildContentSecurityPolicy($csp);
     * // Returns: "default-src 'self'; script-src 'self' 'unsafe-inline'"
     */
    private function buildContentSecurityPolicy(array $csp): string
    {
        try {
            // Validate CSP configuration
            if (! is_array($csp)) {
                throw new \InvalidArgumentException('CSP configuration must be an array');
            }
            $directives = $csp['directives'] ?? [];
            if (! is_array($directives)) {
                throw new \InvalidArgumentException('CSP directives must be an array');
            }
            $policy = [];
            foreach ($directives as $directive => $value) {
                // Validate directive name
                if (empty($directive) || ! is_string($directive)) {
                    Log::warning('Invalid CSP directive name', ['directive' => $directive]);
                    continue;
                }
                // Validate directive value
                if (empty($value) || ! is_string($value)) {
                    Log::warning('Invalid CSP directive value', [
                        'directive' => $directive,
                        'value' => $value,
                    ]);
                    continue;
                }
                // Validate directive name format
                if (! preg_match('/^[a-zA-Z0-9\-]+$/', $directive)) {
                    Log::warning('Invalid CSP directive name format', ['directive' => $directive]);
                    continue;
                }
                $policy[] = $directive . ' ' . $value;
            }
            if (empty($policy)) {
                Log::warning('No valid CSP directives found, using default policy');
                return "default-src 'self'";
            }
            return implode('; ', $policy);
        } catch (\Exception $e) {
            Log::error('Error building Content Security Policy', [
                'csp_config' => $csp ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
